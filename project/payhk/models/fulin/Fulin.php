<?php

namespace app\models\fulin;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\BaseModel;
use app\common\Logger;
/**
 * This is the model class for table "xhh_fulin".
 *
 *

 */
class Fulin extends \app\models\BaseModel
{
    // 抓取状态
    const STATUS_INIT = 0; // 初始  只请求未抓取
    const STATUS_DOING = 1; // 抓取中
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
        return 'xhh_fulin';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name','status','create_time','mobile','idcardno'], 'required'],
            [['create_time','modify_time'], 'safe'],
           // [['status', 'user_id','aid','channel_id'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['msg'], 'string', 'max' => 2000],
            [['idcardno','mobile','score'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'score' =>'Score',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
            'msg' => 'Msg',
            'name' => 'Name',
            'mobile' => 'Mobile',
            'idcardno' => 'Idcardno',
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
     * @param id //请求数据
     * @param $rst //
     * */
    public function oneSave($id,$rst){
        if(empty($id) || empty($rst)){
            return false;
        }
        $str = json_encode($rst,JSON_UNESCAPED_UNICODE);//第二个参数汉子不转译
        $res = $this->getOne($id);
        $res->status = self::STATUS_SUCCESS;
        $res->modify_time = date('Y-m-d H:i:s');
        $res->msg = $str;
        $res->score = ArrayHelper::getValue($rst,'score');
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
     * $number 为有效天数  默认为30天
     * @inheritdoc
     */
    public function isRepeatQuery($idCardno,$number = 30){
        /*
         *
         * true  表示 需要重新验证
         * */
        $data = static::find()->where(['and',['idcardno'=>$idCardno],['=','status',static::STATUS_SUCCESS]])->orderBy('create_time desc')->one();
        if(empty($data)){
            return true;
        }
        //状态
        $create_time = strtotime($data['create_time']);
        $nowTime = time();
        if(($create_time+(86400*$number))<$nowTime){
            $result = true;
        }else{
            $result = ArrayHelper::getValue($data,'msg');
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

            ['=','status',static::STATUS_INIT]

        )->asArray()->all();
        return $data;

    }


}