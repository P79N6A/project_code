<?php

namespace app\models\xn;

use Yii;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use app\models\xn\XnBill;
use app\models\ZFLimit;
use app\models\BaseModel;

/**
 * This is the model class for table "st_request".
 *
 * @property string $id
 * @property string $request_id
 * @property string $user_id
 * @property integer $from
 * @property string $loan_id
 * @property string $create_time
 * @property string $modify_time
 */
class XnRemit extends BaseModel
{

    // 通知状态
    const STATUS_INIT = 0; // 初始
    const STATUS_REQING_REMIT = 1; // 出款请求中
    const STATUS_DOING = 3; // 处理中
    const STATUS_SUCCESS = 6; // 成功
    const STATUS_FINISH = 7; // 已结清
    const STATUS_FAILURE = 11; // 支付失败
    const STATUS_NOTIFY_MAX = 13; // 通知达上限
    const MAX_NOTIFY = 7; // 最大查询次数
    const PAY_STATUS_INIT =0; //未还
    const PAY_STATUS_DOING = 1;  //还款中
    const PAY_STATUS_SUCCESS = 2; //已还
    const PAY_STATUS_FAILURE = 11; //未还
    const BILL_INIT = 0;//拉取账单初始
    const BILL_LOCK = 2;//拉取账单锁定
    const BILL_SUCCESS = 1;//拉取账单成功
    const AGREEMENT_INIT = 0;//拉取协议初始
    const AGREEMENT_LOCK = 1; //拉取协议锁定
    const AGREEMENT_SUCCESS = 2; //成功拉取协议
    const AGREEMENT_FAILURE = 3; //拉取失败协议

    const BIND_AIR = 1500;//借款利率 
    const REPAYMENT_TYPE = 10; //一次性还本付息
    const PRO_ID = 10018; //产品id
    const DAY_TOP_MONEY = 2500000;//每天限额
    const TOTAL_MONEY = 10000000;//总资产
    const STARTTIME = '09:00:00';//开始时间(每天)
    const ENDTIME = '16:00:00';//结束时间(每天)
    const SOURCEID = 5; //小诺资方

    const STATUS_QUERY_MAX = 13; // 查询达上限
    
    const MAX_QUERY_NUM = 20; // 最大查询次数
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xn_remit';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'identityid', 'user_mobile', 'guest_account_name', 'guest_account_bank', 'guest_account', 'bank_code', 'tip', 'time_limit', 'create_time', 'modify_time', 'remit_time','query_time','callbackurl'], 'required'],
            [['aid', 'remit_status', 'query_num', 'bill_status', 'agreement_status', 'version'], 'integer'],
            [['settle_amount'], 'number'],
            [['tip'], 'string'],
            [['create_time', 'modify_time', 'remit_time', 'query_time'], 'safe'],
            [['req_id', 'client_id', 'guest_account'], 'string', 'max' => 30],
            [['identityid', 'guest_account_name', 'bank_code'], 'string', 'max' => 20],
            [['user_mobile', 'guest_account_bank'], 'string', 'max' => 60],
            [['time_limit'], 'string', 'max' => 10],
            [['rsp_status'], 'string', 'max' => 50],
            [['rsp_status_text','callbackurl'], 'string', 'max' => 255]
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
            'req_id' => 'Req ID',
            'client_id' => 'Client ID',
            'settle_amount' => 'Settle Amount',
            'remit_status' => 'Remit Status',
            'identityid' => 'Identityid',
            'user_mobile' => 'User Mobile',
            'guest_account_name' => 'Guest Account Name',
            'guest_account_bank' => 'Guest Account Bank',
            'guest_account' => 'Guest Account',
            'bank_code' => 'Bank Code',
            'tip' => 'Tip',
            'time_limit' => 'Time Limit',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
            'remit_time' => 'Remit Time',
            'rsp_status' => 'Rsp Status',
            'rsp_status_text' => 'Rsp Status Text',
            'query_time' => 'Query Time',
            'query_num' => 'Query Num',
            'bill_status' => 'Bill Status',
            'agreement_status' => 'Agreement Status',
            'version' => 'Version',
        ];
    }
    public function optimisticLock() {
        return "version";
    }
    public static function getRemitStatus() {
        return [
            static::STATUS_INIT => '初始',
            static::STATUS_REQING_REMIT => '出款请求中',
            static::STATUS_DOING => '受理中',
            static::STATUS_SUCCESS => '成功',
            static::STATUS_FAILURE => '支付失败',
            static::STATUS_QUERY_MAX => '查询次数超限'];
    }
    public static function getBillStatus() {
        return [
            static::BILL_INIT => '初始',
            static::BILL_LOCK => '锁定中',
            static::BILL_SUCCESS => '成功'];
    }
    public static function getAgreementStatus() {
        return [
            static::AGREEMENT_INIT => '初始',
            static::AGREEMENT_LOCK => '锁定中',
            static::AGREEMENT_SUCCESS => '成功'];
    }
    //保存数据
    public function saveRemitData($postData)
    { 
        if (!is_array($postData) || empty($postData)) {
            return false;
        }
        $combinData = $this->getData($postData);
        $combinData['client_id'] = $this->getClientId();
        $remitReq = $this->getRemitByReqid($combinData['aid'],$combinData['req_id']);
        if ($remitReq > 0) {
            return $this->returnError(null, "订单重复提交");
        }      
        $error = $this->chkAttributes($combinData);
        if ($error) {
            return $this->returnError(null,implode('|', $error));
        }
        $res = $this->save();
        if (!$res) {
            return $this->returnError(null,implode('|', $this->errors));
        }
        $returndata = [
            'bidNum'=>$combinData['req_id'],
            'user_mobile'=>$combinData['user_mobile'],
            'name'=>$combinData['guest_account_name'],
            'bankCard'=>$combinData['guest_account'],
            'status' => $combinData['remit_status'],
            'client_id'=>$combinData['client_id']
        ];
        return $returndata;
    }

    
    /**
	 * 相同订单号是否重复提交
	 * */
	public function getRemitByReqid($aid,$reqId) {
		$where = ['aid' => $aid, 'req_id' => $reqId];
		$ret = static::find()->where($where)->count();
		return $ret;
    }
    //上标接口 查询
    public function getInitData($start_time,$end_time,$limit=50)
    {
        $start_time = date('Y-m-d H:i:00', strtotime($start_time));
        $end_time = date('Y-m-d H:i:00', strtotime($end_time));
        $where = ['AND',
            ['remit_status' => [static::STATUS_INIT]],
            ['<', 'create_time', $end_time],
            ['>', 'create_time', $start_time],
        ];
        $data = static::find()->where($where)->orderBy('create_time ASC')->offset(0)->limit($limit)->all();
        return $data;
    }

    /**
     * 锁定上标请求接口的状态
     */
    public function lockRemit($ids) {
        if (!is_array($ids) || empty($ids)) 
        {
            return 0;
        }
        $ups = static::updateAll(['remit_status' => static::STATUS_REQING_REMIT], ['id' => $ids]);
        return $ups;
    }
    /**
     * 单条锁定正在出款接口的状态
     */
    public function lockOneRemit(){
        try{
            $this->remit_status = static::STATUS_REQING_REMIT;
            $result = $this->save();
        }catch(\Exception $e){
            $result = false;
        }
        return $result;
    }
    /**
     * 锁定拉取账单请求接口的状态
     */
    public function lockBill($ids) {
        if (!is_array($ids) || empty($ids)) 
        {
            return 0;
        }
        $ups = static::updateAll(['bill_status' => static::BILL_LOCK], ['id' => $ids]);
        return $ups;
    }
    /**
     * 单条锁定拉取账单接口的状态
     */
    public function lockOneBill(){
        try{
            $this->bill_status = static::BILL_LOCK;
            $result = $this->save();
        }catch(\Exception $e){
            $result = false;
        }
        return $result;
    }
    /**
     * 锁定拉取协议请求接口的状态
     */
    public function lockAgreement($ids) {
        if (!is_array($ids) || empty($ids)) 
        {
            return 0;
        }
        $ups = static::updateAll(['agreement_status' => static::AGREEMENT_LOCK], ['id' => $ids]);
        return $ups;
    }
    /**
     * 单条锁定拉取账单接口的状态
     */
    public function lockOneAgreement(){
        try{
            $this->agreement_status = static::AGREEMENT_LOCK;
            $result = $this->save();
        }catch(\Exception $e){
            $result = false;
        }
        return $result;
    }

    /**
     * Undocumented function
     * 查询需拉取账单的订单
     * @param integer $limit
     * @return void
     */
    public function getListLoan($limit=50)
    {
        $where = [
            'AND', 
            ['remit_status' => static::STATUS_SUCCESS, 'bill_status' => static::BILL_INIT],
            ['>', 'query_time', date('Y-m-d H:i:00', strtotime('-7 days'))],
            ['<', 'query_time', date('Y-m-d H:i:00')]
        ];
        $data = static::find()->where($where)->limit($limit)->all();
        return $data;
    }
    /**
     * Undocumented function
     * 标的状态回调结果
     * @param [type] $rsp_status
     * @param [type] $rsp_status_text
     * @return void
     */
    public function saveRspStatus($rsp_status, $rsp_status_text) {
        $this->rsp_status = (string)$rsp_status;
        $this->rsp_status_text = $rsp_status_text;
        $this->modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }

    /**
     * Undocumented function
     * 拉取协议 拉取账单成功之后再拉取协议 查询-2hour
     * @param integer $limit
     * @return void
     */
    public function getAgreementList($limit=50)
    {
        $start_time = date('Y-m-d H:i:00', strtotime('-7 days'));
        $end_time = date('Y-m-d H:i:00');
        $where = ['AND',
            ['remit_status' =>static::STATUS_SUCCESS],
            ['bill_status' =>static::BILL_SUCCESS],
            ['agreement_status'=>static::AGREEMENT_INIT],
            ['>', 'query_time', $start_time],
            ['<', 'query_time', $end_time],
        ];
        $data = self::find()->where($where)->limit($limit)->all();
        return $data;
    }

    //获取状态映射
    public function getStatus($status)
    {
        if(empty($status)){
            return false;
        }
        $sta = array(
            "1"=>self::STATUS_DOING,
            "-1"=>self::PAY_STATUS_FAILURE,
            "4"=>self::STATUS_SUCCESS,
            "-4"=>self::PAY_STATUS_FAILURE,
            "-5"=>self::PAY_STATUS_FAILURE,
            "5"=>self::STATUS_FINISH //已结清
        );
        return $sta[$status]?$sta[$status]:'';
    }

    //每天出款额度
    public function getDayTopMoney()
    {    
        $start_time = date("Y-m-d");  
        $end_time = date("Y-m-d",strtotime('+1 day'));  
        $where = ['AND', 
            ['!=' , 'remit_status' , static::STATUS_FAILURE],
            ['>', 'create_time', $start_time],
            ['<', 'create_time', $end_time],
        ];

        $data = self::find()->select(["SUM(xn_remit.settle_amount) as total"])->where($where)->asArray()->one();
        if( empty($data['total']) ){
            return 0;
        }
        return $data['total'];
    }
    //总资产额度
    public function getTotalMoney()
    {
        $where = ['!=' , 'remit_status' , static::STATUS_FAILURE];
        $data = self::find()->select(["SUM(xn_remit.settle_amount) as total"])->where($where)->asArray()->one();
        if( empty($data['total']) ){
            return 0;
        }
        return $data['total'];
    }

     

    /**
     * 生成clientid
     * */
    public function getClientId() {
        $time = date('YmdHis', time());
        $str = rand(1000, 9999);
        $clientId = "R" . $time . $str;
        $where = ['client_id' => $clientId];
        $ret = static::find()->where($where)->count();
        if ($ret > 0) {
            $clientId = $this->getClientId();
        }
        return $clientId;
    }


    private function getData($data)
    {
        $nowtime = date('Y-m-d H:i:s');
        $tip = array(
            'liveAddrDetail'    =>ArrayHelper::getValue($data,'liveAddrDetail',''),
            'company'           =>ArrayHelper::getValue($data,'company',''),
            'companyPhone'      =>ArrayHelper::getValue($data,'companyPhone',''),
            'isRepeatLoan'      =>ArrayHelper::getValue($data,'isRepeatLoan',''),
            'marryType'         =>ArrayHelper::getValue($data,'marryType',''),
            'hukouAddrDetail'   =>ArrayHelper::getValue($data,'hukouAddrDetail'),
            'emergencyContactName1'      =>ArrayHelper::getValue($data,'emergencyContactName1'),
            'emergencyContactRelation1'  =>ArrayHelper::getValue($data,'emergencyContactRelation1'),
            'emergencyContactPhone1'     =>ArrayHelper::getValue($data,'emergencyContactPhone1'),
            'gpsInfo'           =>ArrayHelper::getValue($data,'gpsInfo'),
            'equipmentNum'      =>ArrayHelper::getValue($data,'equipmentNum'),
            'loanIp'            =>ArrayHelper::getValue($data,'loanIp'),
            'faceRecognition'   =>ArrayHelper::getValue($data,'faceRecognition'),
            'loan_purpose'      =>ArrayHelper::getValue($data,'loanPurpose'),
            'loanPurposeDesc'   =>ArrayHelper::getValue($data,'loanPurposeDesc'),
            'customer_sex'      =>ArrayHelper::getValue($data,'sex'),
        );
        $arr = [
            'aid'               =>ArrayHelper::getValue($data,'aid'),
            'req_id'            =>ArrayHelper::getValue($data,'bidNum'),
            'settle_amount'     =>ArrayHelper::getValue($data,'loanAmount'),          
            'remit_status'      =>self::STATUS_INIT,
            'identityid'        =>ArrayHelper::getValue($data,'idNumber'),
            'user_mobile'       =>ArrayHelper::getValue($data,'bankMobile'),
            'guest_account_name'=>ArrayHelper::getValue($data,'name'),
            'guest_account_bank'=>ArrayHelper::getValue($data,'bankName'),
            'guest_account'     =>ArrayHelper::getValue($data,'bankCard'),
            'bank_code'         =>ArrayHelper::getValue($data,'bank_code'),           
            'tip'               => json_encode($tip),
            'time_limit'        =>ArrayHelper::getValue($data,'loanPeriod'),
            'rsp_status'        =>'',
            'rsp_status_text'   =>'',          
            'create_time'       => $nowtime,
            'modify_time'       => $nowtime,
            'remit_time'        => '0000-00-00 00:00:00',
            'query_time'        => $nowtime,
            'query_num'         => 0,
            'callbackurl'       => ArrayHelper::getValue($data,'callbackurl'),
        ];
        return $arr;
    }

    /**
     * 根据编号获取纪录
     * @param $partner_trade_no
     * @return object
     */
    public  function getByClientId($client_id){
        if(!$client_id){
            return null;
        }
        return static::find() -> where(["client_id"=>$client_id]) ->one();
    }
   /**
    * Undocumented function
    * 判断传入字段是否为空
    * @param [type] $postData
    * @return void
    */
   public  function getVerifyEmptyData($postData){
        if(empty($postData) || !is_array($postData)){
            return $this->returnError(null,'传入参数错误');
        }
        $requiredFields = $this->getRequiredField();
        foreach ($requiredFields as $key => $value) {
            if(empty($postData[$key])){
                return $this->returnError(false,'传入参数'.$key.'不能为空');
            }
           
        }
        return true;
    }
    /**
     * Undocumented function
     * 获取必须字段
     * @return void
     */
    private function getRequiredField(){
        $verifyEmptyData=[
            'name'=>'姓名',
            'idNumber'=>'身份证',
            'tel'=>'手机号',
            'bankCard'=>'卡号',
            'bankName'=>'银行开户名称',
            'sex'=>'性别',
            'bidNum'=>'订单号',
            'loanPurposeDesc'=>'借款用途描述',
            'loanAmount'=>'借款金额',
            'loanPurpose'=>'借款用途',
            'bankMobile'=>'银行预留手机号',
            'liveAddrDetail'=>'居住地',
            'company'=>'工作单位',
            'companyPhone'=>'单位电话',
            'isRepeatLoan'=>'是否复借',
            'marryType'=>'婚姻状况',
            'hukouAddrDetail'=>'户籍地址',
            'emergencyContactName1'=>'紧急联系人姓名',
            'emergencyContactRelation1'=>'紧急联系人关系',
            'emergencyContactPhone1'=>'紧急联系人电话',
            'gpsInfo'=>'gps',
            'equipmentNum'=>'设备号',
            'loanIp'=>'设备IP',
            'applyTime'=>'申请时间',
            'faceRecognition'=>'人脸识别',
        ];

        return $verifyEmptyData;

    }
    /**
     * 判断是否频繁请求
     * @param  [type]  $identityid [description]
     * @return boolean             [description]
     */
    public function isOften($identityid){
        if (empty($identityid)) {
            return true;
        }
        // 判断五分钟内调用次数
        $where = [
            'and',
            ['identityid' => $identityid],
            ['>=','create_time',date('Y-m-d H:i:s',time() - 60*5)],
            ['<=','create_time',date('Y-m-d H:i:s')]
        ];
        $times = static::find()->where($where)->count();
        if ($times >= 10) {
            return true;
        }

        // 判断半小时内调用次数
        $where = [
            'and',
            ['identityid' => $identityid],
            ['>=','create_time',date('Y-m-d H:i:s',time() - 60*30)],
            ['<=','create_time',date('Y-m-d H:i:s')]
        ];
        $times = static::find()->where($where)->count();
        if ($times >= 50) {
            return true;
        }

        return false;
    }
    /**
     * 受理中
     * lockDoingRemit
     * @return bool
     */
    public function saveToDoing(){
        //是否已经处理过了
        if (in_array($this->remit_status, [static::STATUS_SUCCESS, static::STATUS_FAILURE])) {
            return false;
        }       
        $this->modify_time = date('Y-m-d H:i:s');
        $this->remit_status = static::STATUS_DOING;
        return $this->save();
    }
    /**
     * 保存remit_status状态为成功
     * @return bool
     */
    public function saveToSuccess()
    {
        // 终态时更新出款时间
        if (in_array($this->remit_status, [static::STATUS_SUCCESS, static::STATUS_FAILURE])) {
            return false;
        }
        $this->query_time = date('Y-m-d H:i:s');
        $this->query_num = 0;
        $this->remit_time = date('Y-m-d H:i:s');
        $this->remit_status = static::STATUS_SUCCESS;
        $this->modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }

    /**
     * 保存remit_status状态为失败
     * @param $rsp_status
     * @param $rsp_status_text
     * @return bool
     */
    public function saveToFail($rsp_status, $rsp_status_text) {

        if (in_array($this->remit_status, [static::STATUS_SUCCESS, static::STATUS_FAILURE])) {
            return false;
        }
        $this->remit_status = static::STATUS_FAILURE;
        $this->rsp_status = (string)$rsp_status;
        $this->rsp_status_text = $rsp_status_text;
        $this->modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }
    /**
     * Undocumented function
     * 更新出款账单拉取状态
     * @param [type] $bill_status
     * @return void
     */
    public function updateRemitBill($bill_status){
        // 累加查询次数
        $this->query_num++;
        if ($this->query_num < static::MAX_QUERY_NUM) {
            // 未超通知限制的时候, 计算下次查询时间间隔
            $this->query_time = $this->acQueryTime($this->query_num, $this->query_time);
        } else {
            // 超出查询次数限制. 将查询中的变更为超限状态
            if ($bill_status == static::BILL_INIT) {
                $bill_status = static::STATUS_QUERY_MAX; // 转人工处理
            }
        }
        $this->modify_time = date('Y-m-d H:i:s');
        $this->bill_status = $bill_status;
        return $this->save();
    }
    /**
     * Undocumented function
     * 协议拉取状态
     * @param [type] $agreement_status
     * @return void
     */
    public function saveAgreementStatus($agreement_status){
        // 累加查询次数
        $this->query_num++;
        if ($this->query_num < static::MAX_QUERY_NUM) {
            // 未超通知限制的时候, 计算下次查询时间间隔
            $this->query_time = $this->acQueryTime($this->query_num, $this->query_time);
        } else {
            // 超出查询次数限制. 将查询中的变更为超限状态
            if ($agreement_status == static::AGREEMENT_INIT) {
                $agreement_status = static::STATUS_QUERY_MAX; // 转人工处理
            }
        }
        $this->agreement_status = $agreement_status;
        $this->modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }
    /**
     * 计算下次查询时间
     * @param int $query_num 当前次数
     * @param str $query_time 当前时间
     * @return str 下次查询时间
     */
    public function acQueryTime($query_num, $query_time) {
        // 累加的分钟
        $addMinutes = [
            1 => 10,
            2 => 30,
            3 => 60,
            4 => 120,
            5 => 240,
            6 => 480];

        // 不在上述时,不改变
        if (!isset($addMinutes[$query_num])) {
            return $query_time;
        }

        // 累加时间
        $time = ($query_time == '0000-00-00 00:00:00') ? time() : strtotime($query_time);
        $t = $time + $addMinutes[$query_num] * 60;
        return date('Y-m-d H:i:s', $t);
    }
    /**
     * Undocumented function
     * 后台更新数据
     * @param [type] $data
     * @return void
     */
    public function updateData($data)
    {
        $error = $this->chkAttributes($data);
        if ($error) {
            return $this->returnError(null, current($error));
        }
        $result = $this->save();
        if (!$result) {
            return $this->returnError(null, '保存失败');
        } else {
            return $result;
        }
    }

    public function getRemitOne($client_id)
    {
        return self::find()->where(['client_id' => $client_id])->one();
    }
}
