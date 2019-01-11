<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "st_antifraud_notify".
 *
 * @property integer $id
 * @property integer $remit_id
 * @property integer $remit_status
 * @property integer $notify_num
 * @property integer $notify_status
 * @property string $notify_time
 * @property string $reason
 * @property string $create_time
 */
class StNotify extends BaseModel
{
    // 通知状态
    const STATUS_INIT = 0; // 初始
    const STATUS_DOING = 1; // 通知中
    const STATUS_SUCCESS = 2; // 成功
    const STATUS_RETRY = 3; // 重试
    const STATUS_FAILURE = 11; // 失败
    const STATUS_NOTIFY_MAX = 13; // 通知达上限

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

    /**
     * @inheritdoc
     */
    public static function tableName() 
    { 
        return 'st_notify'; 
    } 

    /** 
     * @inheritdoc 
     */ 
    public function rules() 
    { 
        return [
            [['st_req_id', 'notify_time', 'create_time'], 'required'],
            [['st_req_id', 'notify_num', 'from', 'notify_status'], 'integer'],
            [['notify_time', 'create_time'], 'safe'],
            [['reason'], 'string', 'max' => 20]
        ]; 
    } 

    /** 
     * @inheritdoc 
     */ 
    public function attributeLabels() 
    { 
        return [ 
            'id' => 'ID',
            'st_req_id' => 'strategyrequest表id',
            'notify_num' => '通知次数: 上限7次',
            'from' => '决策来源',
            'notify_status' => '通知状态:0:初始; 1:通知中; 2:通知成功; 3:重试; 11:通知失败',
            'notify_time' => '下次通知时间',
            'reason' => '通知失败原因:例如没有回调地址',
            'create_time' => '创建时间',
        ]; 
    } 

    /**
     * 表关联关系
     */
    public function getRequest() {
        return $this->hasOne(Request::className(), ['request_id' => 'st_req_id']);
    }

    /**
     * 获取状态为初始和重试的记录
     * @param $start_time 精确到分
     * @param $end_time  精确到分
     * @return []
     */
    public function getClientNotifyList($start_time, $end_time, $limit=1000) {
        $start_time = date('Y-m-d H:i:00', strtotime($start_time));
        $end_time = date('Y-m-d H:i:00', strtotime($end_time));
        $where = ['AND',
            ['notify_status' => [static::STATUS_INIT, static::STATUS_RETRY]],
            ['>=', 'notify_time', $start_time],
            ['<', 'notify_time', $end_time],
            // ['from' => $from],
        ];
        $dataList = self::find()->where($where)->limit($limit)->all();
        if (!$dataList) {
            return null;
        }
        return $dataList;
    }

    public function getClientNotifyHappy($start_time, $end_time, $limit=1000) {
        $start_time = date('Y-m-d H:i:00', strtotime($start_time));
        $end_time = date('Y-m-d H:i:00', strtotime($end_time));
        $where = ['AND',
            [self::tableName().'.notify_status' => [static::STATUS_INIT, static::STATUS_RETRY]],
            ['=', StrategyRequest::tableName().'.aid', 16],
            ['=', StrategyRequest::tableName().'.status', 2],
            ['>=', self::tableName().'.notify_time', $start_time],
            ['<', self::tableName().'.notify_time', $end_time],

            // ['from' => $from],
        ];
        $dataList = self::find()
                    ->innerJoin(StrategyRequest::tableName(), self::tableName().".st_req_id = ".StrategyRequest::tableName().".id")
                    ->select(self::tableName().".st_req_id,".self::tableName().".id")
                    ->where($where)
                    ->limit($limit)->all();
        if (!$dataList) {
            return null;
        }
        return $dataList;
    }



    /**
     * 获取字符串形式状态
     * @param  string $status_str 
     * @return int | []
     */
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
    public function saveData($request_id, $from) {
        $dayTime = date('Y-m-d H:i:s');
        $row = [
            'st_req_id' => $request_id,
            'notify_num' => 0,
            'from' => $from,
            'notify_status' => 0,
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
     * 锁定正在通知接口的状态
     */
    public function lockNotify($ids) {
        if (!is_array($ids) || empty($ids)) {
            return 0;
        }
        $ups = static::updateAll(['notify_status' => static::STATUS_DOING], ['id' => $ids]);
        return $ups;
    }
    /**
     * 回写响应结果
     * $this 操作数据
     * @param $notify_status 通知状态
     * @return bool
     */
    public function saveNotifyStatus($notify_status, $reason = '') {
        // 当是出款中时, 更新下次的查询时间
        $this->refresh();
        if ($notify_status != static::STATUS_INIT ){
            // 累加通知次数
            $this->notify_num++;
            if($this->notify_num < static::MAX_NOTIFY){
                // 未超通知限制, 计算下次查询时间间隔
                $this->notify_time = $this->acNotifyTime($this->notify_num, $this->notify_time);
            }else{
                // 超出重试次数限制. 将重试中的变更为超限状态
                if($notify_status == static::STATUS_RETRY){
                    $notify_status = static::STATUS_NOTIFY_MAX;
                }
            }
        }
        // var_dump($notify_status);die;
        $this->notify_status = $notify_status;
        $reason = substr($reason, 0, 20);
        $this->reason = $reason;
        $result = $this->save();
        return $result;
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
}
