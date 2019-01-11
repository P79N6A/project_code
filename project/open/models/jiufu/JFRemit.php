<?php

namespace app\models\jiufu;
use app\modules\api\common\jiufu\JFMap;

/**
 * 玖富出款
 *
 */
class JFRemit extends \app\models\BaseModel {
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

    const MAX_QUERY_NUM = 96; // 最大查询次数
    private $oMap;
    public function init() {
        parent::init();
        $this->oMap = new JFMap;
    }
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'jf_remit';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['req_id', 'aid', 'identityid', 'user_mobile', 'guest_account_name', 'guest_account_bank', 'guest_account', 'guest_account_province', 'guest_account_city', 'guest_account_bank_branch', 'customer_sex', 'bank_code', 'city_code', 'time_limit', 'loan_purpose', 'tip', 'callbackurl', 'create_time', 'modify_time', 'query_time'], 'required'],
            [['aid', 'remit_status', 'query_num', 'version'], 'integer'],
            [['settle_amount'], 'number'],
            [['tip'], 'string'],
            [['create_time', 'modify_time', 'remit_time', 'query_time', 'auth_time'], 'safe'],
            [['req_id'], 'string', 'max' => 40],
            [['client_id', 'guest_account'], 'string', 'max' => 30],
            [['rsp_status'], 'string', 'max' => 50],
            [['rsp_status_text', 'callbackurl'], 'string', 'max' => 255],
            [['product_id', 'order_id', 'order_status', 'identityid', 'bank_code', 'city_code', 'loan_purpose'], 'string', 'max' => 20],
            [['user_mobile', 'guest_account_name', 'guest_account_bank'], 'string', 'max' => 60],
            [['guest_account_province', 'guest_account_city', 'guest_account_bank_branch'], 'string', 'max' => 150],
            [['customer_sex', 'time_limit'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '结算记录ID',
            'req_id' => '请求ID(业务)',
            'aid' => '应用id',
            'product_id' => '玖富产品',
            'client_id' => '玖富请求号,序列号',
            'order_id' => '玖富订单号,流水号',
            'settle_amount' => '[必填]结算金额',
            'remit_status' => '打款状态:0:初始化;1:出款请求中;3:受理中;4:查询请求中;6:成功;11:失败;12:无响应(预留)',
            'rsp_status' => '响应状态',
            'rsp_status_text' => '响应结果',
            'order_status' => '订单状态',
            'identityid' => '用户身份证',
            'user_mobile' => '用户手机',
            'guest_account_name' => '帐号名称(持卡人姓名)',
            'guest_account_bank' => '[收款]开户行名称',
            'guest_account' => '[必填]银行账号',
            'guest_account_province' => '[收款]银行所属省',
            'guest_account_city' => '[收款]银行所属市',
            'guest_account_bank_branch' => '[收款]银行所属支行',
            'bank_code' => '银行编码',
            'city_code' => '城市/县编码',
            'time_limit' => '借款期限',
            'loan_purpose' => '借款用途',
            'tip' => '附加字段:住宅/联系人/map',
            'callbackurl' => '异步通知回调url',
            'create_time' => '创建时间',
            'modify_time' => '更新时间',
            'remit_time' => '出款时间',
            'auth_time' => '审核时间',
            'query_time' => '下次查询时间',
            'query_num' => '查询次数',
            'version' => '乐观锁',
        ];
    }
    public function getStatus() {
        return [
            static::STATUS_INIT => '初始',
            static::STATUS_REQING_REMIT => '出款请求中',
            static::STATUS_DOING => '受理中',
            static::STATUS_REQING_QUERY => '查询请求中',
            static::STATUS_SUCCESS => '成功',
            static::STATUS_FAILURE => '支付失败',
            static::STATUS_HTTP_NOT_200 => '无响应',
            static::STATUS_QUERY_MAX => '查询次数超限',

            static::STATUS_PAYING => '支付结果确认',
            static::STATUS_REQING_PAY => '支付结果确认中',
        ];

    }
    /**
     * 根据请求reqid获取
     * @param  str $req_id
     * @return obj
     */
    public function getByReqId($req_id) {
        return static::find()->where(['req_id' => $req_id])->limit(1)->one();
    }
    /**
     * 根据请求 order_id 获取
     * @param  str $order_id
     * @return obj
     */
    public function getByOrderId($order_id) {
        if(empty($order_id)){
            return null;
        }
        return static::find()->where(['order_id' => $order_id])->limit(1)->one();
    }
    /**
     * 保存数据
     * @param [] $data
     * @return []
     */
    public function saveData($data) {
        if (!is_array($data) || empty($data)) {
            return false;
        }
        //保存数据
        $time = date("Y-m-d H:i:s");
        //$query_time = date('Y-m-d', time() + 86400) . ' 08:00:00';
        $data = [
            'req_id' => $data['req_id'],
            'aid' => $data['aid'],
            'product_id' => $data['product_id'],
            'client_id' => $data['client_id'],
            'order_id' => '',
            'settle_amount' => $data['settle_amount'],
            'remit_status' => static::STATUS_INIT,
            'rsp_status' => '',
            'rsp_status_text' => '',
            'order_status' => '',
            'identityid' => $data['identityid'],
            'user_mobile' => $data['user_mobile'],
            'guest_account_name' => $data['guest_account_name'],
            'guest_account_bank' => $data['guest_account_bank'],
            'guest_account' => $data['guest_account'],
            'guest_account_province' => $data['guest_account_province'],
            'guest_account_city' => $data['guest_account_city'],
            'guest_account_bank_branch' => $data['guest_account_bank_branch'],
            'customer_sex' => (string) $data['customer_sex'],
            'bank_code' => (string) $data['bank_code'],
            'city_code' => (string) $data['city_code'],
            'time_limit' => $data['time_limit'],
            'loan_purpose' => $data['loan_purpose'],
            'tip' => $data['tip'],
            'callbackurl' => $data['callbackurl'],
            'create_time' => $time,
            'modify_time' => $time,
            'remit_time' => '0000-00-00 00:00:00',
            'auth_time' => '0000-00-00 00:00:00',
            'query_time' => $time,
            'query_num' => 0,
            'version' => 0,
        ];
        $errors = $this->chkAttributes($data);
        if ($errors) {
            return false;
        }

        return $this->save();
    }
    /**
     * 接口状态变更, 用于处理开始->(待运营审核)F0225之间的状态变更
     * 只修改订单状态, 不修改出款状态
     * @param $rsp_status 接口响应状态 若不为0, 则表示失败
     * @param $rsp_status_text 接口响应结果
     * @param $order_status 订单状态
     * @param $order_id 订单号
     * @return bool
     */
    public function saveRspStatus($rsp_status, $rsp_status_text, $order_status = '', $order_id = '') {
        if (!is_string($rsp_status_text)) {
            $rsp_status_text = json_encode($rsp_status_text);
        }
        $rsp_status_text = (string) $rsp_status_text;
        $rsp_status_text = str_replace("调用录单接口发生异常，异常原因或类型为：执行产品服务","",$rsp_status_text);
        $rsp_status_text = substr($rsp_status_text, 0, 100);
        $this->rsp_status_text = $rsp_status_text;
        $this->rsp_status = (string) $rsp_status;

        if ($order_status) {
            $this->order_status = $order_status;
        }
        if ($order_id) {
            $this->order_id = $order_id;
        }

        $this->modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }
    /**
     * 出款状态变更:查询接口
     * 从(待运营审核)F0225->F0243(放款成功)
     */
    public function saveToDoing() {
        //1. 是否已经处理过了
        if (in_array($this->remit_status, [static::STATUS_SUCCESS, static::STATUS_FAILURE])) {
            return false;
        }
        $this->query_time = date('Y-m-d H:i:s');
        $this->query_num = 0;
        $this->modify_time = date('Y-m-d H:i:s');
        $this->remit_status = static::STATUS_DOING;

        $result = $this->save();
        return $result;
    }
    /**
     * 出款状态变更:支付查询接口, 用于查询F0243(放款成功)之后中的状态
     */
    public function saveToPaying($order_status) {
        //1. 是否已经处理过了
        if (in_array($this->remit_status, [static::STATUS_SUCCESS, static::STATUS_FAILURE])) {
            return false;
        }
        $time = strtotime( date('Y-m-d') ) + rand(0, 3600) * 10; //T+1 均分到上午10点前
        $this->order_status = $order_status;
        $this->query_time = date('Y-m-d H:i:s', $time);
        $this->query_num = 0;
        $this->modify_time = date('Y-m-d H:i:s');
        $this->auth_time = date('Y-m-d H:i:s');
        $this->remit_status = static::STATUS_PAYING;

        $result = $this->save();
        return $result;
    }
    /**
     * 保存出款状态, 主要用于提交成功后
     */
    public function saveToSuccess() {
        //1. 是否已经处理过了
        if (in_array($this->remit_status, [static::STATUS_SUCCESS, static::STATUS_FAILURE])) {
            return false;
        }
        $this->remit_status = static::STATUS_SUCCESS;
        $this->modify_time = date('Y-m-d H:i:s');
        $this->remit_time = date('Y-m-d H:i:s');

        $result = $this->save();
        return $result;
    }
    /**
     * 保存出款状态, 主要用于失败后
     */
    public function saveToFailed() {
        //1. 是否已经处理过了
        if (in_array($this->remit_status, [static::STATUS_SUCCESS, static::STATUS_FAILURE])) {
            return false;
        }
        $this->modify_time = date('Y-m-d H:i:s');
        $this->remit_status = static::STATUS_FAILURE;

        $result = $this->save();
        return $result;
    }
    public function nextInitTime(){
        $this->query_time = date('Y-m-d H:i:s', time() + 600);
        $this->remit_status = static::STATUS_INIT;
        return $this->save();
    }
    /**
     * 属于初始状态 remit_status = 0 的状态码
     *
     * @return []
     */
    public function getInitOrderStatus(){
        return ['F0220', 'F0222', 'F0223', 'F0225','F0230','F0231','F0243'];
    }
    public function getAllInitOrderStatus(){
        return ['F0206', 'F0281','F0220', 'F0222', 'F0223', 'F0225','F0230','F0231','F0243'];    
    }
    public function saveInitOrderStatus($order_status){
        if(!in_array($order_status, $this->getInitOrderStatus())){
            return false;
        }
        $this->order_status = $order_status;
        $this->modify_time = date('Y-m-d H:i:s');
        return $this->save();
    }
    /**
     * 更新下次查询时间
     */
    public function nextQueryTime() {
        $query_num = $this->query_num;
        $query_time = $this->query_time;

        // 累加的分钟
        /*$addMinutes = [
        0 => 30,
        1 => 60,
        2 => 60,
        3 => 60,
        4 => 60,
        5 => 60,
        6 => 60,
        7 => 60,
        8 => 60,
        9 => 60,
        ];

        // 不在上述时,不改变
        if (isset($addMinutes[$query_num])) {
        // 累加时间
        $time = ($query_time == '0000-00-00 00:00:00') ? time() : strtotime($query_time);
        $t = $time + $addMinutes[$query_num] * 60;
        $this-> query_time = date('Y-m-d H:i:s', $t);
        }*/

        // 累加时间
        $time = ($this->query_time == '0000-00-00 00:00:00') ? time() : strtotime($this->query_time);
        $t = $time + 3600; // 固定1小时
        $this->query_time = date('Y-m-d H:i:s', $t);

        // 计算累计
        $this->query_num++;
        if ($this->query_num >= static::MAX_QUERY_NUM) {
            $this->remit_status = static::STATUS_QUERY_MAX; // 转人工处理
        } else {
            $this->remit_status = static::STATUS_DOING;
        }
        $this->modify_time = date('Y-m-d H:i:s');
        return $this->save();
    }
    /**
     * 更新下次支付查询时间
     */
    public function nextPayTime() {
        // 累加时间
        $time = ($this->query_time == '0000-00-00 00:00:00') ? time() : strtotime($this->query_time);
        $t = $time + 3600; // 固定1小时
        $this->query_time = date('Y-m-d H:i:s', $t);

        // 计算累计
        $this->query_num++;
        if ($this->query_num >= static::MAX_QUERY_NUM) {
            $this->remit_status = static::STATUS_QUERY_MAX; // 转人工处理
        } else {
            $this->remit_status = static::STATUS_PAYING;
        }
        $this->modify_time = date('Y-m-d H:i:s');
        return $this->save();
    }
    /**
     * 生成唯一标识
     * @param  int $aid     客户应用id
     * @param  int $user_id 客户端唯一id
     * @return str          唯一id
     */
    public function getClientId($aid, $user_id) {
        return 'L' . $aid . 'N' . $user_id;
    }
    /**
     * 获取性别编码
     * @return str
     */
    public function getCustomerSex($sex) {
        return $sex == 1 ? 'N0201' : 'N0202';
    }

    /**
     * 获取银行编码
     * @return [type] [description]
     */
    public function getBankCode($bank_name) {
        return $this->oMap->getBankCode($bank_name);
    }
    /**
     * 获取城市编码
     * @return str
     */
    public function getCityCode($county_id, $city_id, $province_id) {
        $oCity = new JFCityMap;
        $jf_id = $oCity->getCityCode($county_id, $city_id, $province_id);
        return $jf_id;
    }
    /**
     * 获取目的信息
     * @return str
     */
    public function getPurpose($purpose) {
        return $this->oMap->getPurpose($purpose);
    }
    public function optimisticLock() {
        return "version";
    }
    /**
     * 获取需要确认出款的数据
     */
    public function getInitData($limit = 100) {
        $where = ['AND', ['remit_status' => static::STATUS_INIT, 'order_status' => $this->getAllInitOrderStatus()],
            ['>', 'query_time', date('Y-m-d H:i:00', strtotime('-7 day'))],
            ['<', 'query_time', date('Y-m-d H:i:00')]
        ];
        $data = static::find()->where($where)->orderBy('create_time ASC')->offset(0)->limit($limit)->all();
        return $data;
    }
    /**
     * 锁定正在出款接口的状态
     */
    public function lockRemit($ids) {
        if (!is_array($ids) || empty($ids)) {
            return 0;
        }
        $field = ['remit_status' => static::STATUS_REQING_REMIT];
        $where = ['id' => $ids, 'remit_status' => static::STATUS_INIT, 'order_status' =>  $this->getAllInitOrderStatus()];
        $ups = static::updateAll($field, $where);
        return $ups;
    }
    public function lockOneRemit(){
        try{
            $this->remit_status =  static::STATUS_REQING_REMIT;
            return $this->save();
        }catch(\Exception $e){
            return false;
        }
    }
    /**
     * 查询: 获取正在处理中的数据
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
     * 查询: 锁定正在查询接口的状态
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
    /**
     * 支付结果: 获取正在支付中接口的状态
     */
    public function getPayQueryData($limit) {
        $where = ['AND', ['remit_status' => static::STATUS_PAYING],
            ['>','create_time',"2017-09-26"],
            ['>', 'query_time', date('Y-m-d H:i:00', strtotime('-10 day'))],
            ['<', 'query_time', date('Y-m-d H:i:00')]];
        // 按查询时间排序
        $data = static::find()->where($where)->orderBy('query_time ASC')->offset(0)->limit($limit)->all();
        return $data;
    }
    /**
     * 支付结果: 锁定正在支付中接口的状态
     */
    public function lockPayQuery($ids) {
        if (!is_array($ids) || empty($ids)) {
            return 0;
        }
        $ups = static::updateAll(['remit_status' => static::STATUS_REQING_PAY], ['id' => $ids]);
        return $ups;
    }
    public function lockOnePayQuery(){
        try{
            $this->remit_status =  static::STATUS_REQING_PAY;
            return $this->save();
        }catch(\Exception $e){
            return false;
        }
    }
}
