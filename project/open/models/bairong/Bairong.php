<?php

namespace app\models\bairong;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\BaseModel;
/**
 * This is the model class for table "xhh_baiduhc_request".
 * @property integer $id
 * @property integer $user_id
 * @property integer $task_id
 * @property integer $aid
 * @property integer $source
 * @property integer $request_status
 * @property integer $create_time
 * @property integer $modify_time
 * @property integer $callback_url
 * @property integer $reason
 * @property integer $name
 * @property integer $mobile
 * @property integer $idcard
 *
 *

 */
class Bairong extends BaseModel
{
    // 抓取状态
    const STATUS_INIT = 0; // 初始  只请求未抓取
    const STATUS_SUCCESS = 2; // 成功
    const STATUS_FAILURE = 11; // 通知失败

    private $notifyMap;
    public function init(){

    }
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xhh_bairong_request';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id','status','create_time','aid','name','cell','idcard','loan_id'], 'required'],
            [['create_time','modify_time'], 'safe'],
           // [['status', 'user_id','aid','channel_id'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['reason','swift_number'], 'string', 'max' => 2000],
            [['idcard','cell'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'loan_id' => 'Loan ID',
            'aid' => 'Aid',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
            'reason' => 'Reason',
            'name' => 'Name',
            'cell' => 'Cobile',
            'idcard' => 'Idcard',
            'swift_number' => 'Swift Number',
        ];
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
    /*public function optimisticLock() {
        return "version";
    }*/
    /**
     * Undocumented function
     * 保存数据
     * @param [type] $postdata
     * @return void
     */
    public function saveData($postData){
        if (empty($postData)) {
            return $this->returnError(false, '不能为空');
        }
        $nowTime = date('Y-m-d H:i:s');
        $postData['status'] = static::STATUS_INIT;
        $postData['create_time'] = $nowTime;
        $error = $this->chkAttributes($postData);
        if ($error) {
            return false;
        }
        $result = $this->save();
        if($result){
            return $this->id;
        }
        return false;
    }

    //更新请求状态
    public function saveRequestStatus($request_status){
        $this->refresh();
        $nowTime = date('Y-m-d H:i:s');
        $this->request_status = $request_status;
        $this->modify_time = $nowTime;
        $result = $this->save();
        return $result;
    }

    /*
     *  更新单条数据的状态
     * @param $id
     * @param $rst //百融返回信息
     * */
    public function oneSave($id,$rst){
        if(empty($id) || empty($rst)){
            return false;
        }
        $json = json_decode($rst,true);
        $res = $this->getOne($id);
        $zero = $json['code'];
        if($zero=='00' || $zero=='100002'){
            $res->status = self::STATUS_SUCCESS;
        }else{
            $res->status = self::STATUS_FAILURE;
        }
        $res->modify_time = date('Y-m-d H:i:s');
        $res->reason = $rst;
        $res->swift_number  = $json['swift_number'];
        $result = $res->update();
        return $result;
    }

    /*
     * 根据id查询单条语句
     * */
    public function getOne($id,$column='id'){
        if(empty($id)){
            return false;
        }
        $result = self::find()->where(['=' , $column , $id] )->one();
        return $result;
    }

    /**
     * 锁定正在抓取的状态
     */
    public function lockStatus($ids) {
        if (!is_array($ids) || empty($ids)) {
            return 0;
        }
        $ups = static::updateAll(['request_status' => static::STATUS_DOING], ['id' => $ids]);
        return $ups;
    }

    /**
     * 查询此用户是否请求成功过
     * $number 为有效天数  默认为90天
     * @inheritdoc
     */
    public function isRepeatQuery($identity,$phone,$number = 90){
        /*
         *
         * true  表示 需要重新验证
         * */
        $data = static::find()->where(['and',['idcard'=>$identity,'cell'=>$phone],['=','status',static::STATUS_SUCCESS]])->orderBy('create_time desc')->one();
        if(empty($data)){
            return true;
        }
        //状态
        $create_time = strtotime($data['create_time']);
        $nowTime = time();
        if(($create_time+(86400*$number))<$nowTime){
            $result = true;
        }else{
            $result = ArrayHelper::getValue($data,'reason');
        }

        return $result ;
    }

    /**
     *
     * 获取当前表所有信息
     * $data
     *
     */
     public function getData(){

        $data =  static::find()->where(

            ['=','status',static::STATUS_SUCCESS]

        )->asArray()->all();
        return $data;

     }

}