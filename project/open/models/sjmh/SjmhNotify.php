<?php

namespace app\models\sjmh;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\BaseModel;

/**
 * This is the model class for table "xhh_sjmh_notify".
 *
 * @property integer $id
 * @property integer $request_id
 * @property integer $request_status
 * @property integer $notify_num
 * @property integer $notify_status
 * @property string $notify_time
 * @property string $create_time
 * @property string $reason
 */

class SjmhNotify extends BaseModel{

    // 通知状态
    const STATUS_INIT = 0; // 初始
    const STATUS_DOING = 1; // 通知中
    const STATUS_SUCCESS = 2; // 成功
    const STATUS_RETRY = 3; // 重试
    const STATUS_FAILURE = 11; // 通知失败
    const STATUS_NOTIFY_MAX = 5; // 通知达上限

    const MAX_NOTIFY = 7; // 最大查询次数

    private $notifyMap;
    public function init(){
        $this->notifyMap = [
            'STATUS_INIT' => static::STATUS_INIT,
            'STATUS_DOING' => static::STATUS_DOING,
            'STATUS_SUCCESS' => static::STATUS_SUCCESS,
            'STATUS_RETRY' => static::STATUS_RETRY,
            'STATUS_FAILURE' => static::STATUS_FAILURE,
            'STATUS_NOTIFY_MAX' => static::STATUS_NOTIFY_MAX,
        ];
    }

    public static function tableName()
    {
        return 'xhh_sjmh_notify';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['request_id','request_status','notify_num','notify_status','create_time'], 'required'],
            [['create_time','notify_time'], 'safe'],
            [['reason'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'request_status' => '任务id请求结果：2成功，11失败',
            'create_time' => '创建时间',
            'request_id' => '请求表ID',
            'notify_num' => '请求上次数，上限7',
            'notify_status' => '通知状态:0:初始; 1:通知中; 2:通知成功; 3:重试; 4:通知失败； 5：通知上限',
            'notify_time' => '下次通知时间',
            'reason' => '客户响应结果',
        ];
    }
    /*public function optimisticLock() {
        return "version";
    }*/

    public function gStatus($status_str=null){
        if($status_str){
            return $this->notifyMap[$status_str];
        }else{
            return $this->notifyMap;
        }
    }

    /**
     * 保存数据到db库中
     * @param $data
     * @return bool
     */
    public function saveData($requestid, $request_status) {
        $re = $this->getOne($requestid,'request_id');
        $dayTime = date('Y-m-d H:i:s');
        if($re){
            $re->request_status = 0;
            $re->notify_num = 0;
            $re->notify_status = $request_status;
            $re->reason = '';
            $re->notify_time = $dayTime;
            $re->create_time = $dayTime;
            return  $re->update();
        }
        $row = [
            'request_id' => $requestid,
            'request_status' => $request_status,
            'notify_num' => 0,
            'notify_status' => 0,
            'reason' => '',
            'notify_time' => $dayTime,
            'create_time' => $dayTime,
        ];
        $error = $this->chkAttributes($row);
        if ($error) {
            return $this->returnError(false, current($error));
        }
        $res = $this->save();
        return $res;
    }


    /**
     * 获取状态为初始和重试的记录
     * @param $start_time 精确到分
     * @param $end_time  精确到分
     * @return []
     */
    public function getSjmhNotifyList($start_time, $end_time, $limit=200) {
        // $start_time = date('Y-m-d H:i:00', strtotime($start_time));
        $start_time = date('Y-m-d H:i:00', strtotime('-3 day'));
        $end_time = date('Y-m-d H:i:00', strtotime($end_time));
        $where = ['AND',
            ['notify_status' => [static::STATUS_INIT, static::STATUS_RETRY]],
            ['>=', 'notify_time', $start_time],
            ['<', 'notify_time', $end_time],
        ];
        $dataList = self::find()->where($where)->limit($limit)->all();
        if (!$dataList) {
            return null;
        }
        return $dataList;
    }



    /**
     * 回写响应结果
     * $this 操作数据
     * @param $notify_status 通知状态
     * @return bool
     */
    public function saveOneNotifyStatus($data,$notify_status,$reason){

        $re = $this->getOne($data['id']);
        $notify_num = $re['notify_num'];
        $notify_time = $re['notify_time'];
        //$re->refresh();
        if ($notify_status != static::STATUS_INIT ){
            // 累加通知次数
            //$re->notify_num++;
            $notify_num++;
            if($notify_num < static::MAX_NOTIFY){
                // 未超通知限制, 计算下次查询时间间隔
                $notify_time = $this->acNotifyTime($notify_num, $notify_time);
            }else{
                // 超出重试次数限制. 将重试中的变更为超限状态
                if($notify_status == static::STATUS_RETRY){
                    $notify_status = static::STATUS_NOTIFY_MAX;
                }
            }
        }

        $re->notify_time = $notify_time;
        $re->notify_num = $notify_num;
        $re->notify_status = $notify_status;
        $reason = substr($reason, 0, 20);
        $re->reason = $reason;
        $result = $re->update();
        return $result;
    }

    /**
     * 锁定正在通知的状态
     */
    public function lockNotify($ids) {
        if (!is_array($ids) || empty($ids)) {
            return 0;
        }
        $ups = static::updateAll(['notify_status' => static::STATUS_DOING], ['id' => $ids]);
        return $ups;
    }

    /**
     * 计算下次查询时间
     * @param int $notify_num 当前次数
     * @param str $notify_time 当前时间
     * @return str 下次查询时间
     */
    public function acNotifyTime($notify_num, $notify_time) {
        // 累加的分钟
        $addMinutes = [
            1 => 5,
            2 => 30,
            3 => 89,
            4 => 233,
            5 => 610,
            6 => 1560,
        ];

        // 不在上述时,不改变
        if (!isset($addMinutes[$notify_num])) {
            return $notify_time;
        }

        // 累加时间
        $time = ($notify_time == '0000-00-00 00:00:00') ? time() : strtotime($notify_time);
        $t = $time + $addMinutes[$notify_num] * 60;
        return date('Y-m-d H:i:s', $t);
    }


    //根据关键字查询单条数据
    public function getOne($id,$column='id' ){
        if(empty($id)){
            return false;
        }
        $result = self::find()->where(['=' , $column , $id] )->one();
        return $result;
    }



}