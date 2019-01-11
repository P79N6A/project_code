<?php

namespace app\models\policy;

use Yii;
use yii\helpers\ArrayHelper;
use app\modules\api\common\baofoo\CBaofooAuth;
/**
 * This is the model class for table "zhan_policy".
 *
 * @property integer $id
 * @property integer $aid
 * @property string $req_id
 * @property string $client_id
 * @property string $sumInsured
 * @property string $premium
 * @property integer $remit_status
 * @property string $identityid
 * @property string $user_mobile
 * @property string $user_name
 * @property integer $relation
 * @property string $benifitName
 * @property string $benifitCertiType
 * @property string $benifitCertiNo
 * @property string $policyBeginDate
 * @property string $policyEndDate
 * @property string $create_time
 * @property string $modify_time
 * @property string $remit_time
 * @property string $rsp_status
 * @property string $rsp_status_text
 * @property string $query_time
 * @property integer $query_num
 * @property string $callbackurl
 * @property integer $version
 */
class ZhanPolicy extends \app\models\BaseModel 
{
    const STATUS_INIT = 0; // 初始
    const STATUS_REQING_REMIT = 1; // 请求中
    const STATUS_DOING = 3; // 处理中
    const STATUS_APPLY = 4; // 出单申请中
    const STATUS_SUCCESS = 6; // 成功
    const STATUS_CANCEL_DOING = 7; // 退保锁定中
    const STATUS_CANCEL = 8; // 退保
    const STATUS_CHECKFAIL = 9; // 核保失败
    const STATUS_FAILURE = 11; // 失败
    const STATUS_QUERY_MAX = 13; // 查询达上限
    const MAX_QUERY_NUM = 7; // 最大查询次数
    const PAY_INIT =0;//未支付
    const PAY_DOING =1;//支付中
    const PAY_SUCCESS = 6;//支付成功
    const PAY_FAILURE = 11;//支付失败
    const RATE_LESS = 0.003;//保险期间小于等于3月 0.3%
    const RATE_MORE = 0.005;//保险期间大于3月 0.5%
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'zhan_policy';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'identityid', 'user_mobile', 'user_name', 'benifitName', 'benifitCertiType', 'benifitCertiNo','policyDate', 'create_time', 'modify_time', 'apply_time', 'policy_time', 'query_time'], 'required'],
            [['aid', 'fund','remit_status', 'pay_status','relation', 'query_num', 'policyDate','version'], 'integer'],
            [['sumInsured', 'premium'], 'number'],
            [['create_time', 'modify_time', 'apply_time', 'policy_time', 'query_time'], 'safe'],
            [['req_id', 'client_id', 'user_mobile'], 'string', 'max' => 30],
            [['identityid', 'user_name', 'benifitName', 'benifitCertiNo'], 'string', 'max' => 20],
            [['benifitCertiType'], 'string', 'max' => 10],
            [['rsp_status', 'applyNo', 'policyNo', 'orderId'], 'string', 'max' => 50],
            [['rsp_status_text', 'callbackurl','ePolicyUrl'], 'string', 'max' => 255]
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
            'fund' => 'Fund',
            'req_id' => 'Req ID',
            'client_id' => 'Client ID',
            'sumInsured' => 'Sum Insured',
            'premium' => 'Premium',
            'remit_status' => 'Remit Status',
            'identityid' => 'Identityid',
            'user_mobile' => 'User Mobile',
            'user_name' => 'User Name',
            'relation' => 'Relation',
            'benifitName' => 'Benifit Name',
            'benifitCertiType' => 'Benifit Certi Type',
            'benifitCertiNo' => 'Benifit Certi No',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
            'apply_time' => 'Apply Time',
            'policy_time' => 'Policy Time',
            'rsp_status' => 'Rsp Status',
            'rsp_status_text' => 'Rsp Status Text',
            'query_time' => 'Query Time',
            'query_num' => 'Query Num',
            'callbackurl' => 'Callbackurl',
            'applyNo' => 'Apply No',
            'policyNo' => 'Policy No',
            'orderId' => 'Order ID',
            'version' => 'Version',
        ];
    }
    public function optimisticLock() {
        return "version";
    }
    public static function getFund(){
        return [
            '1'=>'花生米富',
            '2'=>'玖富',
            '3'=>'联交所',
            '4'=>'金联储',
            '5'=>'小诺',
            '6'=>'微神马',
            '10'=>'银行存管'
        ];
    }
    public static function getRemitStatus() {
        return [
            static::STATUS_INIT => '初始',
            static::STATUS_REQING_REMIT => '请求中',
            static::STATUS_DOING => '受理中',
            static::STATUS_SUCCESS => '成功',
            static::STATUS_FAILURE => '支付失败',
            static::STATUS_QUERY_MAX => '查询次数超限'];
    }
    public static function getPayStatus() {
        return [
            static::PAY_INIT => '初始',
            static::PAY_DOING => '支付中',
            static::PAY_SUCCESS => '成功',
            static::PAY_FAILURE => '失败',
            
        ];
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
            'req_id'=>'请求号',
            // 'fund'=>'资金方',
            'premium'=>'保费',
            'identityid'=>'身份证',
            'user_mobile'=>'手机号',
            'user_name'=>'姓名',
            'benifitName'=>'受益人姓名',
            'benifitCertiType'=>'受益人证件类型',
            'benifitCertiNo'=>'受益人证件号',
            'policyDate'=>'保险期间',
            'callbackurl'=>'客户端回调地址'
        ];

        return $verifyEmptyData;

    }
    //保存数据
    public function saveData($postData)
    { 
        if (!is_array($postData) || empty($postData)) {
            return false;
        }
        $combinData = $this->getData($postData);
        $combinData['client_id'] = $this->getClientId();
        $remitReq = $this->getDataByReqid($combinData['aid'],$combinData['req_id']);
        if ($remitReq) {
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
        return $res;
    }
    /**
     * 生成clientid
     * */
    public function getClientId() {
        $time = date('YmdHis', time());
        $str = rand(1000, 9999);
        $clientId = "P" . $time . $str;
        $where = ['client_id' => $clientId];
        $ret = static::find()->where($where)->count();
        if ($ret > 0) {
            $clientId = $this->getClientId();
        }
        return $clientId;
    }
    private function getData($postdata)
    {
        $nowtime = date('Y-m-d H:i:s');
        $postdata['remit_status']     = self::STATUS_INIT;
        $postdata['pay_status']       = self::PAY_INIT;
        $postdata['rsp_status']       = '';
        $postdata['rsp_status_text']  = '';          
        $postdata['create_time']      = $nowtime;
        $postdata['modify_time']      = $nowtime;
        $postdata['apply_time']       = '0000-00-00 00:00:00';
        $postdata['policy_time']      = '0000-00-00 00:00:00';
        $postdata['query_time']       = $nowtime;
        $postdata['query_num']        = 0;
        $postdata['relation']         = 1;// 与投保人关系  1本人
        $postdata['sumInsured']          = $this->getSumInsured($postdata);//计算保额
        return $postdata;
    }
    /**
	 * 相同订单号是否重复提交
	 * */
	public function getDataByReqid($aid,$reqId) {
		$where = ['aid' => $aid, 'req_id' => $reqId];
		$ret = static::find()->where($where)->limit(1)->one();
		return $ret;
    }
    //查询
    public function getInitData($limit=100)
    {
        $where = [
            'AND', 
            ['remit_status' => static::STATUS_INIT],
            ['>', 'create_time', date('Y-m-d H:i:00', strtotime('-7 days'))],
            ['<', 'create_time', date('Y-m-d H:i:00')]
        ];
        $data = static::find()->where($where)->orderBy('create_time ASC')->offset(0)->limit($limit)->all();
        return $data;
    }
    /**
     * 锁定请求接口的状态
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
     * 单条锁定正在请求接口的状态
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
    //查询核保通过的单子
    public function getPayInitData($limit=100)
    {
        $where = [
            'AND', 
            ['remit_status' => static::STATUS_DOING,'pay_status'=>static::PAY_INIT],
            ['>', 'create_time', date('Y-m-d H:i:00', strtotime('-7 days'))],
            ['<', 'create_time', date('Y-m-d H:i:00')]
        ];
        $data = static::find()->where($where)->orderBy('create_time ASC')->offset(0)->limit($limit)->all();
        return $data;
    }
    /**
     * 锁定状态
     */
    public function lockPay($ids) {
        if (!is_array($ids) || empty($ids)) 
        {
            return 0;
        }
        $ups = static::updateAll(['pay_status' => static::PAY_DOING], ['id' => $ids]);
        return $ups;
    }
    /**
     * 单条锁定状态
     */
    public function lockOnePay(){
        try{
            $this->pay_status = static::PAY_DOING;
            $result = $this->save();
        }catch(\Exception $e){
            $result = false;
        }
        return $result;
    }
    /**
     * 修改支付状态为初始状态
     */
    public function toPayInit(){
        $this->pay_status = static::PAY_INIT;
        $this->modify_time = date('Y-m-d H:i:s');
        return $this->save();
    }
    /**
     * Undocumented function
     * 查询核保成功以及支付成功的保单
     * @param integer $limit
     * @return void
     */
    public function getDoingData($limit=50)
    {
        $where = [
            'AND', 
            ['remit_status' => static::STATUS_DOING, 'pay_status' => static::PAY_SUCCESS],
            ['>', 'query_time', date('Y-m-d H:i:00', strtotime('-7 days'))],
            ['<', 'query_time', date('Y-m-d H:i:00')] 
        ];
        $data = static::find()->where($where)->limit($limit)->all();
        return $data;
    }
    /**
     * 锁定状态
     */
    public function lockApply($ids) {
        if (!is_array($ids) || empty($ids)) 
        {
            return 0;
        }
        $ups = static::updateAll(['remit_status' => static::STATUS_APPLY], ['id' => $ids]);
        return $ups;
    }
    /**
     * 单条锁定状态
     */
    public function lockOneApply(){
        try{
            $this->remit_status = static::STATUS_APPLY;
            $result = $this->save();
        }catch(\Exception $e){
            $result = false;
        }
        return $result;
    }
    /**
     * 修改保单状态为处理中
     */
    public function saveToDoing($errCode,$errMsg,$applyNo=''){
        //是否已经处理过了
        if (in_array($this->remit_status, [static::STATUS_SUCCESS,static::STATUS_CHECKFAIL, static::STATUS_FAILURE])) {
            return false;
        }
        $remit_status = static::STATUS_DOING;
        if(!empty($applyNo)){
            $this->applyNo = $applyNo;
            $this->apply_time = date('Y-m-d H:i:s');
        }else{
            // 累加查询次数
            $this->query_num++;
            if ($this->query_num < static::MAX_QUERY_NUM) {
                // 未超通知限制的时候, 计算下次查询时间间隔
                $this->query_time = $this->acQueryTime($this->query_num, $this->query_time);
            } else {
                // 超出查询次数限制. 将查询中的变更为超限状态
                $remit_status = static::STATUS_QUERY_MAX; // 转人工处理
            }   
        }           
        $this->modify_time = date('Y-m-d H:i:s');
        $this->remit_status = $remit_status;
        $this->rsp_status = (string)$errCode;
        $this->rsp_status_text = $errMsg;
        return $this->save();
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
     * 出单成功
     * @param [type] $policyNo
     * @param [type] $ePolicyUrl
     * @param [type] $rsp_status
     * @param [type] $rsp_status_text
     * @return void
     */
    public function saveToSuccess($policyNo,$ePolicyUrl,$rsp_status,$rsp_status_text){
        $nowTime = date('Y-m-d H:i:s');
        $this->remit_status = static::STATUS_SUCCESS;
        $this->modify_time = $nowTime;
        $this->policy_time = $nowTime;
        $this->policyNo = $policyNo;
        $this->ePolicyUrl = $ePolicyUrl;
        $this->rsp_status = (string)$rsp_status;
        $this->rsp_status_text = $rsp_status_text;
        return $this->save();
    }
    /**
     * 保存remit_status状态为失败
     * @param $rsp_status
     * @param $rsp_status_text
     * @return bool
     */
    public function saveToFail($rsp_status, $rsp_status_text) {
        
        if (in_array($this->remit_status, [static::STATUS_SUCCESS,static::STATUS_CHECKFAIL, static::STATUS_FAILURE])) {
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
     * 保存remit_status状态为核保失败
     * @param $rsp_status
     * @param $rsp_status_text
     * @return bool
     */
    public function saveToCheckFail($rsp_status, $rsp_status_text) {
        
        if (in_array($this->remit_status, [static::STATUS_SUCCESS,static::STATUS_CHECKFAIL, static::STATUS_FAILURE])) {
            return false;
        }
        $this->remit_status = static::STATUS_CHECKFAIL;
        $this->rsp_status = (string)$rsp_status;
        $this->rsp_status_text = $rsp_status_text;
        $this->modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }
    /**
     * Undocumented function
     * 计算保额
     * @param [type] $postdata
     * @return void
     */
    private function getSumInsured($postdata){
        $premium = ArrayHelper::getValue($postdata,'premium',0);//保费
        $policyDate = ArrayHelper::getValue($postdata,'policyDate',0);//保险期间
        $endDate = date('Y-m-d H:i:s',strtotime('+'.$policyDate.' day'));
        $policyEndDate = date('Y-m-d H:i:s',strtotime('+3 months'));
        $sumInsured = 0;
        if($endDate<=$policyEndDate){
            $rate = self::RATE_LESS;
        }else{
            $rate = self::RATE_MORE;
        }
        $sumInsured = ceil($premium/$rate);
        return $sumInsured;

    }

    //更新投保订单
    public function upPolicy($reqId,$orderId,$payState){
        if (empty($reqId) ||  empty($payState) ){
            return false;
        }
        $order_where = [
            'client_id'=>$reqId,
        ];
        $orderInfo = self::find()->where($order_where) ->one();
        if (empty($orderInfo)){
            return false;
        }
        if($orderInfo->pay_status == self::PAY_SUCCESS || $orderInfo->pay_status == self::PAY_FAILURE){//终态不做修改
            return false;
        }
        $orderInfo->orderId = $orderId;
        $orderInfo->pay_status = $payState;
        $res = $orderInfo->save();
        return $res;
    }
    public function getPolicyById($id){
        $data  = static::findOne($id);
        return $data;
    }
    /**
     * Undocumented function
     * 查询成功保单
     * @param integer $limit
     * @return void
     */
    public function getSuccessData($limit=50)
    {
        $where = [
            'AND', 
            ['remit_status' => static::STATUS_SUCCESS],
            ['>', 'create_time', date('Y-m-d H:i:00', strtotime('-7 days'))],
            ['<', 'create_time', date('Y-m-d H:i:00')] 
        ];
        $data = static::find()->where($where)->limit($limit)->all();
        return $data;
    }
    /**
     * 锁定状态
     */
    public function lockCancel($ids) {
        if (!is_array($ids) || empty($ids)) 
        {
            return 0;
        }
        $ups = static::updateAll(['remit_status' => static::STATUS_CANCEL_DOING], ['id' => $ids]);
        return $ups;
    }
    /**
     * 单条锁定状态
     */
    public function lockOneCancel(){
        try{
            $this->remit_status = static::STATUS_CANCEL_DOING;
            $result = $this->save();
        }catch(\Exception $e){
            $result = false;
        }
        return $result;
    }
    /**
     * 保存remit_status状态为退保
     * @return bool
     */
    public function saveToCancel() {
        $this->remit_status = static::STATUS_CANCEL;
        $this->modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }
    /**
     * 保存结果
     * @param $rsp_status
     * @param $rsp_status_text
     * @return bool
     */
    public function saveResult($rsp_status, $rsp_status_text) {
        $this->rsp_status = (string)$rsp_status;
        $this->rsp_status_text = $rsp_status_text;
        $this->modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }
    /**
     * Undocumented function
     * 获取待支付资金方金额
     * @param string $fund
     * @return void
     */
    public static function getPaySum($fund=''){
        $where = [
            'remit_status'=>static::STATUS_DOING,
            'pay_status'=>static::PAY_INIT
        ];
        if(!empty($fund)){
            $where['fund']=$fund;
        }
        $data = static::find()->select('sum(premium) as all_premium')->where($where)->asArray()->one();
        return empty($data['all_premium'])?0:$data['all_premium'];
    }
    /**
     * Undocumented function
     * 获取众安保险宝付通道余额
     * @param string $channel_id
     * @return void
     */
    public static function getBfBalance($channel_id = '114'){
        $balance_res = (new CBaofooAuth) ->getBalance($channel_id);
        $res_code = ArrayHelper::getValue($balance_res,'res_code');
        $res_data = ArrayHelper::getValue($balance_res,'res_data');
        if($res_code==0){
            return $res_data;
        }else{
            return 0;
        }
    }
    /**
     * Undocumented function
     * 查询 核保成功的所有单号 及!=9
     * @param [type] $bill_date
     * @return void
     */
    public function getPolicyBill($bill_date){
        $endDate = date('Y-m-d',strtotime('+1 day',strtotime($bill_date)));
        $sql = "select a.client_id,a.policyNo as p_policyNo,b.policyNo,a.premium,b.premium as policy_premium,a.aid,a.user_name,a.user_mobile,a.fund,a.orderId,a.remit_status,a.pay_status,a.rsp_status_text from zhan_policy as a left join policy_bill as b on a.client_id=b.channelOrderNo where a.remit_status!=9 and a.create_time>'{$bill_date}' and a.create_time<'{$endDate}'";
        $data = Yii::$app->getDb()->createCommand($sql)->queryAll();
        return $data;
    }
    /**
     * Undocumented function
     * 根据商户订单号查询
     * @param [type] $client_id
     * @return void
     */
	public function getDataByClientId($client_id) {
        if(empty($client_id)) return false;
		$where = ['client_id' => $client_id];
		$ret = static::find()->where($where)->limit(1)->one();
		return $ret;
    }
}
