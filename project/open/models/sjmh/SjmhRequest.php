<?php

namespace app\models\sjmh;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\BaseModel;
/**
 * This is the model class for table "xhh_sjmh_request".
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
class SjmhRequest extends BaseModel
{
    // 抓取状态
    const STATUS_INIT = 0; // 初始  只请求未抓取
    const STATUS_DOING = 1; // 抓取中
    const STATUS_SUCCESS = 2; // 成功
    const STATUS_RETRY = 3; // 重试
    const STATUS_AUTHORIZE = 4; // 授权成功的成功
    const STATUS_FAILURE = 11; // 通知失败

    private $notifyMap;
    public function init(){
        $this->notifyMap = [
            'STATUS_INIT' => static::STATUS_INIT,
            'STATUS_DOING' => static::STATUS_DOING,
            'STATUS_SUCCESS' => static::STATUS_SUCCESS,
            'STATUS_RETRY' => static::STATUS_RETRY,
            'STATUS_AUTHORIZE' => static::STATUS_AUTHORIZE,
            'STATUS_FAILURE' => static::STATUS_FAILURE,
        ];
    }
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xhh_sjmh_request';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'source','request_status','create_time','aid','modify_time'], 'required'],
            [['create_time','modify_time'], 'safe'],
            [['request_status', 'user_id','aid','source'], 'integer'],
            [['task_id','name'], 'string', 'max' => 50],
            [['callback_url'], 'string', 'max' => 100],
            [['reason'], 'string', 'max' => 200],
            [['mobile'], 'string', 'max' => 20],
            [['idcard'], 'string', 'max' => 30],
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
            'task_id' => 'Task ID',
            'aid' => '应用id',
            'source' => '数据源类型 she_bao：社保，gjj：公积金，chsi：学信',
            'request_status' => '请求状态 0：默认，1抓取中，2成功，11失败',
            'create_time' => '创建时间',
            'modify_time' => '更新时间',
            'callback_url' => '回调url',
            'reason' => '抓取返回的基本信息',
            'name' => '姓名',
            'mobile' => '手机号',
            'idcard' => '身份证号',
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
        if (!is_array($postData) || empty($postData)) {
            return $this->returnError(false, '不能为空');
        }
        $nowTime = date('Y-m-d H:i:s');
        #$postData['task_id'] = '1';
        $postData['aid'] = ArrayHelper::getValue($postData,'aid',0);
        $postData['create_time'] = $nowTime;
        $postData['modify_time'] = $nowTime;
        $postData['request_status'] = static::STATUS_INIT;
        $error = $this->chkAttributes($postData);
        if ($error) {
            return $this->returnError(false, $error);
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
     * */
    public function oneSave($request_status,$data){
        if(empty($data) || empty($request_status)){
            return false;
        }
        $id = ArrayHelper::getValue($data,'request_id');
        $res = $this->getOne($id);
        if($request_status == static::STATUS_AUTHORIZE ){
            if(ArrayHelper::getValue($res,'user_id') != ArrayHelper::getValue($data,'user_id')){
                return false;
            }
            if(ArrayHelper::getValue($res,'source') != ArrayHelper::getValue($data,'source')){
                return false;
            }
            $res->task_id = ArrayHelper::getValue($data,'task_id');
            $res->callback_url = ArrayHelper::getValue($data,'callback_url');
        }elseif($request_status == static::STATUS_SUCCESS ){
            $res->reason = ArrayHelper::getValue($data,'reason');
            $res->name = ArrayHelper::getValue($data,'name');
            $res->mobile = ArrayHelper::getValue($data,'mobile');
            $res->idcard = ArrayHelper::getValue($data,'idcard');
        }elseif($request_status == static::STATUS_FAILURE){
            $res->reason = ArrayHelper::getValue($data,'reason');
        }
        $res->request_status = $request_status;
        $res->modify_time = date('Y-m-d H:i:s');
        $result = $res->update();
        return $result;
    }

    /*
     * 根据id 查询单条语句
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
    public function isRepeatQuery($user_id,$type,$number = 90){
        /*
         * true  表示已经验证过
         * false  表示 需要重新验证
         * */
        $data = static::find()->where(['and',['user_id'=>$user_id,'source'=>$type],['!=','request_status',static::STATUS_INIT]])->orderBy('create_time desc')->one();
        if(empty($data)){
            return false;
        }
        //状态
        $result = false;
        $request_status = ArrayHelper::getValue($data,'request_status');
        $create_time = strtotime($data['modify_time']);
        if($request_status == static::STATUS_SUCCESS){
            $nowTime = time();
            if(($create_time+(86400*$number))<$nowTime){
                $result = false;
            }else{
                $result = $data;
            }
        }elseif($request_status == static::STATUS_AUTHORIZE || $request_status == static::STATUS_DOING || $request_status == static::STATUS_RETRY){
            $result = $data;
        }elseif($request_status == static::STATUS_FAILURE){
            $result = false;
        }
        return $result ;
    }


    /**
     * 获取状态为初始的记录
     * @return []
     */
    public function getSjmhRequestList($limit=200) {
		$startTime = time()-604800; // 60*60*24*7 一周内
		$endTime = time()-300; // 60*5 五分钟内
        $startTime = date('Y-m-d H:i:00', $startTime);
        $endTime = date('Y-m-d H:i:00', $endTime);
        $where = ['AND',
            ['request_status' => [static::STATUS_AUTHORIZE, static::STATUS_RETRY]],
            ['>=', 'create_time', $startTime],
            ['<', 'create_time', $endTime],
        ];
        $dataList = self::find()->where($where)->limit($limit)->all();
        if (!$dataList) {
            return null;
        }
        return $dataList;
    }


    /**
     * 请求限制   5分钟内超过10次 请10分钟以后在来请求
     */
    public function restriction($user_id){
        $time = time();
        $new_time = date('Y-m-d H:i:s',$time);
        $five_minutes_ago = date('Y-m-d H:i:s',$time-300);
        $where = [
            'and','request_status=0',
            [
              'and','user_id ='.$user_id,
                ['and',
                    ['<=','create_time',$new_time],
                    ['>','create_time',$five_minutes_ago]
                ]
            ]
        ];
        $num = self::find()->where($where)->count();
        return $num;
    }
}