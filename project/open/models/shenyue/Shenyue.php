<?php

namespace app\models\shenyue;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\BaseModel;
use app\common\Logger;
/**
 * This is the model class for table "xhh_shenyue".
 *
 *

 */
class Shenyue extends BaseModel
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
        return 'xhh_shenyue';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name','status','create_time','mobile'], 'required'],
            [['create_time','modify_time'], 'safe'],
            [[ 'user_id'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['data_url'], 'string', 'max' => 2000],
            [['idcard','mobile','aid','loan_id','source'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'aid' => 'Aid',
            'loan_id' => 'Loan Id',
            'user_id' => 'User Id',
            'source' =>'Scource',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
            'data_url' => 'Data Url',
            'name' => 'Name',
            'mobile' => 'Mobile',
            'idcard' => 'Idcard',
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
        //$str = json_encode($rst,JSON_UNESCAPED_UNICODE);//第二个参数汉子不转译
        $res = $this->getOne($id);
        $res->status = self::STATUS_SUCCESS;
        $res->modify_time = date('Y-m-d H:i:s');
        $res->data_url = ArrayHelper::getValue($rst,'url');
        $result = $res->save();
        //var_dump($res->getErrors());die;
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
    public function isRepeatQuery($idCardno,$source,$number = 30){
        /*
         *
         * true  表示 需要重新验证
         * */
        $data = static::find()->where(['and',['idcard'=>$idCardno],['=','status',static::STATUS_SUCCESS],['=','source',$source]])->orderBy('create_time desc')->one();
        if(empty($data)){
            return true;
        }
        //状态
        $create_time = strtotime($data['create_time']);
        $nowTime = time();
        if(($create_time+(86400*$number))<$nowTime){
            $result = true;
        }else{
            $result = ['url'=>ArrayHelper::getValue($data,'data_url')];
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