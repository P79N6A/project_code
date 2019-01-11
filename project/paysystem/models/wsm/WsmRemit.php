<?php

namespace app\models\wsm;

use app\common\Logger;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "wsm_remit".
 *
 * @property string $id
 * @property string $req_id
 * @property string $client_id
 * @property string $realname
 * @property string $identityid
 * @property string $user_mobile
 * @property string $guest_account
 * @property string $settle_amount
 * @property string $favorite_contacts
 * @property string $risk management
 * @property string $payment_details
 * @property string $tip
 * @property integer $extract_status
 * @property string $callbackurl
 * @property integer $remit_status
 * @property string $rsp_status
 * @property string $rsp_status_text
 * @property string $create_time
 * @property string $modify_time
 * @property string $remit_time
 * @property string $query_time
 * @property integer $query_num
 * @property string $version
 */
class WsmRemit extends \app\models\BaseModel
{
    // 支付状态 0:初始化;1:出款请求中;3:受理中;4:查询请求中;6:成功;11:失败;12:无响应(预留)
    const STATUS_INIT = 0;
    const STATUS_REQING_REMIT = 1; // 出款请求中
    const STATUS_DOING = 3; // 受理中
    const STATUS_REQING_QUERY = 4; // 查询请求中
    const STATUS_SUCCESS = 6; // 成功
    const STATUS_PAYING = 7; // 支付结果确认
    const STATUS_REQING_PAY = 8; // 支付结果确认中
    const STATUS_FAILURE = 11; // 支付失败
    const STATUS_HTTP_NOT_200 = 12; // 无响应
    const STATUS_QUERY_MAX = 13; // 查询达上限

    const MAX_QUERY_NUM = 48; // 最大查询次数

    public $errinfo;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'wsm_remit';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['req_id', 'aid', 'client_id', 'realname', 'identityid', 'user_mobile', 'guest_account', 'favorite_contacts', 'risk_management', 'payment_details', 'tip', 'create_time'], 'required'],
            [['aid', 'extract_status', 'remit_status', 'query_num', 'version'], 'integer'],
            [['settle_amount'], 'number'],
            [['favorite_contacts', 'risk_management', 'payment_details', 'tip'], 'string'],
            [['create_time', 'modify_time', 'remit_time', 'query_time'], 'safe'],
            [['req_id'], 'string', 'max' => 40],
            [['client_id', 'rsp_status'], 'string', 'max' => 50],
            [['realname', 'user_mobile'], 'string', 'max' => 48],
            [['identityid', 'guest_account'], 'string', 'max' => 88],
            [['callbackurl', 'rsp_status_text'], 'string', 'max' => 255],
            [['req_id'], 'unique'],
            [['client_id'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'req_id' => 'Req ID',
            'client_id' => 'Client ID',
            'realname' => 'Realname',
            'identityid' => 'Identityid',
            'user_mobile' => 'User Mobile',
            'guest_account' => 'Guest Account',
            'settle_amount' => 'Settle Amount',
            'favorite_contacts' => 'Favorite Contacts',
            'risk_management' => 'Risk Management',
            'payment_details' => 'Payment Details',
            'tip' => 'Tip',
            'extract_status' => 'Extract Status',
            'callbackurl' => 'Callbackurl',
            'remit_status' => 'Remit Status',
            'rsp_status' => 'Rsp Status',
            'rsp_status_text' => 'Rsp Status Text',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
            'remit_time' => 'Remit Time',
            'query_time' => 'Query Time',
            'query_num' => 'Query Num',
            'version' => 'Version',
        ];
    }
    public function optimisticLock() {
        return "version";
    }

    /**
     * 保存订单数据
     * @param array $data
     * @return bool
     */
    public function saveOrder(array $data)
    {
        if (!is_array($data) || empty($data)) {
            return $this->returnError(false, "参数不能为空！");
        }
        $create_time = date("Y-m-d H:i:s", time());
        //[资产平台系统的商户订单]商户订单号
        //$shddh = 'wsm_' . date("YmdHis", time()).mt_rand(100,1000);//todo model
        $data_set = [
            'req_id' => ArrayHelper::getValue($data, 'req_id', ''), //请求ID(业务)',
            'client_id' => ArrayHelper::getValue($data, 'req_id', ''), //[资产平台系统的商户订单]商户订单号
            'aid' => ArrayHelper::getValue($data, 'aid', '1'), //[资产平台系统的商户订单]商户订单号
            'realname' => ArrayHelper::getValue($data, 'realname', ''),//借款人姓名
            'identityid' => ArrayHelper::getValue($data, 'identityid', ''),//身份证号码
            'user_mobile' => ArrayHelper::getValue($data, 'user_mobile', ''), //手机号,
            'guest_account' => ArrayHelper::getValue($data, 'guest_account', ''),//卡号',
            'settle_amount' => ArrayHelper::getValue($data, 'settle_amount', ''), //申请金额（元）',
            'favorite_contacts' => ArrayHelper::getValue($data, 'favorite_contacts', ''), //[json]联系人1姓名:联系人1电话:联系人1关系',
            'risk_management' => ArrayHelper::getValue($data, 'risk_management', ''), //[json]风控信息',
            'payment_details' => ArrayHelper::getValue($data, 'payment_details', ''), //[json]支付明细',
            'tip' => ArrayHelper::getValue($data, 'tip', ''), //附加字段',
            'callbackurl' => ArrayHelper::getValue($data, 'callbackurl', ''), //异步通知回调url',
            'order_status' => 0, //微神马订单状态:0:初始化;1:成功;2:失败',
            'create_time' => $create_time, //创建时间',
            'modify_time' => $create_time, //更新时间',
            'remit_time' => '0000-00-00', //出款时间',
            'query_time'  => $create_time, //下次查询时间',
            'query_num' => 0, //查询次数',
            'version' => 0, //乐观锁',
        ];
        $errors = $this->chkAttributes($data_set);
        if ($errors) {
            return $this->returnError(false, json_encode($errors));
        }
        $ret = $this->save();
        if (!$ret){
            return $this->returnError(false, "进件记录失败");
        }
        return $this->returnError(true, 200);
    }

    /**
     * 获取需要确认出款的数据
     * @param int $limit
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getInitData($limit = 100) {
        $where = ['AND', ['remit_status' => static::STATUS_INIT],
            ['>', 'query_time', date('Y-m-d H:i:00', strtotime('-7 day'))],
            ['<', 'query_time', date('Y-m-d H:i:00')]
        ];
        $data = static::find()->where($where)->orderBy('create_time ASC')->offset(0)->limit($limit)->all();
        return $data;
    }

    /**
     * 锁定正在出款接口的状态
     * @param $ids
     * @return int
     */
    public function lockRemit($ids) {
        if (!is_array($ids) || empty($ids)) {
            return 0;
        }
        $field = ['remit_status' => static::STATUS_REQING_REMIT];
        $where = ['id' => $ids, 'remit_status' => static::STATUS_INIT];
        $ups = static::updateAll($field, $where);
        return $ups;
    }

    /**
     * 锁定账单状态
     * @return bool
     */
    public function lockOneRemit(){
        try{
            $this->remit_status =  static::STATUS_REQING_REMIT;
            return $this->save();
        }catch(\Exception $e){
            return false;
        }
    }

    /**
     * 受理中
     * lockDoingRemit
     * @return bool
     */
    public function saveToDoing(){
        try{
            //1. 是否已经处理过了
            if (in_array($this->remit_status, [static::STATUS_SUCCESS, static::STATUS_FAILURE])) {
                return false;
            }
            $this->query_time = date('Y-m-d H:i:s');
            $this->query_num = 0;
            $this->modify_time = date('Y-m-d H:i:s');
            $this->remit_status = static::STATUS_DOING;
            return $this->save();
        }catch(\Exception $e){
            return false;
        }
    }

    /**
     * 更新账单
     * @param $data_set
     * @return bool
     */
    public function updateBill($data_set)
    {
        if (empty($data_set) || !is_array($data_set)){
            return false;
        }
        foreach($data_set as $key=>$value){
            $this->$key=$value;
        }
        $this->query_num = $this->query_num+1;
        $this->modify_time = date("Y-m-d H:i:s", time());
        $this->query_time = date("Y-m-d H:i:s", time());
        $ret = $this->save();
        if ($ret) {
            return true;
        }
        return false;
    }

    /**
     * 查询: 获取正在处理中的数据
     * @param $limit
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getDoingData($limit) {
        $where = ['AND', ['remit_status' => static::STATUS_DOING],
            ['>', 'query_time', date('Y-m-d H:i:00', strtotime('-7 day'))],
            ['<', 'query_time', date('Y-m-d H:i:00')]];
        // 按查询时间排序
        $data = static::find()->where($where)->orderBy('query_time ASC')->offset(0)->limit($limit)->all();
        return $data;
    }

    /**
     * 查询: 获取正在处理中的数据
     * @param $limit
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getLoanData($limit) {
        $where = ['AND', ['remit_status' => static::STATUS_SUCCESS, 'extract_status' => static::STATUS_INIT],
            ['>', 'query_time', date('Y-m-d H:i:00', strtotime('-7 day'))],
            ['<', 'query_time', date('Y-m-d H:i:00')]];
        // 按查询时间排序
        $data = static::find()->where($where)->orderBy('query_time ASC')->offset(0)->limit($limit)->all();
        return $data;
    }

    /**
     * 查询: 锁定正在查询接口的状态
     * @param $ids
     * @return int
     */
    public function lockQuery($ids) {
        if (!is_array($ids) || empty($ids)) {
            return 0;
        }
        $ups = static::updateAll(['remit_status' => static::STATUS_REQING_QUERY], ['id' => $ids]);
        return $ups;
    }

    public function lockOneQuery(){
        try{
            $this->remit_status =  static::STATUS_REQING_QUERY;
            return $this->save();
        }catch(\Exception $e){
            return false;
        }
    }

    public function extractStatusQuery(){
        try{
            $this->extract_status = static::STATUS_REQING_REMIT;
            $this->query_num++;
            return $this->save();
        }catch(\Exception $e){
            return false;
        }
    }

    public function extractStatusFail()
    {
        try{
            $this->query_time = $this->acQueryTime($this->query_num, $this->query_time);
            $this->extract_status = (string)static::STATUS_INIT;
            $this->query_num++;
            return $this->save();
        }catch(\Exception $e){
            return false;
        }
    }

    /**
     * 保存remit_status状态为成功
     * @return bool
     */
    public function saveToSuccess()
    {
        // 终态时更新出款时间
        if (in_array($this->remit_status, [WsmRemit::STATUS_SUCCESS, WsmRemit::STATUS_FAILURE])) {
            return false;
        }
        $this->remit_time = date('Y-m-d H:i:s');
        $this->remit_status = WsmRemit::STATUS_SUCCESS;
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

        if (in_array($this->remit_status, [WsmRemit::STATUS_SUCCESS, WsmRemit::STATUS_FAILURE])) {
            return false;
        }
        $this->remit_time = date('Y-m-d H:i:s');
        $this->remit_status = WsmRemit::STATUS_FAILURE;
        $this->rsp_status = $rsp_status;
        $this->rsp_status_text = $rsp_status_text;
        $this->modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }

    /**
     * 更新下次查询时间
     * @return bool
     */
    public function saveNextQuery() {
        // 当是出款中时, 更新下次的查询时间
        // 累加查询次数
        $this->query_num++;
        if ($this->query_num < WsmRemit::MAX_QUERY_NUM) {
            // 未超通知限制的时候, 计算下次查询时间间隔
            $this->query_time = $this->acQueryTime($this->query_num, $this->query_time);
            $this->remit_status = WsmRemit::STATUS_DOING;
        } else {
            // 超出查询次数限制. 将查询中的变更为超限状态
            $this->remit_status =WsmRemit::STATUS_QUERY_MAX; // 转人工处理
        }
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
            6 => 1560];

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
     * 查询: 锁定正在查询接口的状态
     * @param $ids
     * @return int
     */
    public function lockExtractQuery($ids) {
        if (!is_array($ids) || empty($ids)) {
            return 0;
        }
        $ups = static::updateAll(['extract_status' => static::STATUS_REQING_QUERY], ['id' => $ids]);
        return $ups;
    }

    /**
     * 查找一条数据
     * @param $client_id
     * @return array|bool|null|\yii\db\ActiveRecord
     */
    public function getWsmRemitOne($client_id)
    {
        if (empty($client_id)){
            return false;
        }
        $data_info = self::find()->where(['client_id' => $client_id])->one();
        if (empty($data_info)){
            return false;
        }
        return $data_info;
    }

    /**
     * 查找req_id一条数据
     * @param $req_id
     * @return array|bool|null|\yii\db\ActiveRecord
     */
    public function getWsmRemitReqIdOne($req_id)
    {
        if (empty($req_id)){
            return false;
        }
        $data_info = self::find()->where(['req_id' => $req_id])->one();
        if (empty($data_info)){
            return false;
        }
        return $data_info;
    }

    /**
     * 获取当前总额
     */
    public function getDayTotalMoney()
    {
        $sum_where = [
            'and',
            ['>=','create_time', date("Y-m-d 00:00:00", time())],
            ['<','create_time', date("Y-m-d 00:00:00", strtotime("+1 day"))],
            ['!=','remit_status', WsmRemit::STATUS_FAILURE]
        ];
        $wsm_remit_sum = self::find()->where($sum_where)->sum('settle_amount');
        if (empty($wsm_remit_sum)){
            return 0;
        }
        return $wsm_remit_sum;
    }
    /**
     * 获取总额
     */
    public function getTotalMoney()
    {
        $sum_where = [
            'and',
            ['!=','remit_status', WsmRemit::STATUS_FAILURE]
        ];
        $wsm_remit_sum = self::find()->where($sum_where)->sum('settle_amount');
        if (empty($wsm_remit_sum)){
            return 0;
        }
        return $wsm_remit_sum;
    }


    public function getOrder($client_id)
    {
        if (empty($client_id)){
            return $this->returnError(null, "订单不存在！");
        }
        $order_where = [
            'and',
            ['client_id'=>$client_id],
            ['not in', 'remit_status', [WsmRemit::STATUS_SUCCESS, WsmRemit::STATUS_FAILURE]],
        ];
        $orderInfo = self::find()->where($order_where) -> one();
        if (empty($orderInfo)){
            return $this->returnError(null, '订单不存在！');
        }
        return $orderInfo;
    }

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

    public static function getStatus(){
        return [
            0=>'初始化',
            1=>'出款请求中',
            3=>'受理中',
            4=>'查询请求中',
            6=>'成功',
            11=>'失败',
            12=>'无响应',
            13=>'查询超限'
        ];
    }

    public static function getExtractStatus()
    {
        return [
            0=>'初始化',
            1=>'拉取过',
            4=>'拉取锁定',
        ];
    }

    public function getRemitOne($client_id)
    {
        return self::find()->where(['client_id' => $client_id])->one();
    }
}