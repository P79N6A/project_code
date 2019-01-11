<?php

namespace app\models;
use app\common\Logger;
use app\models\ClientNotify;
use Yii;
/**
 * 支付总表
 *
 */
class Payorder extends BaseModel
{
    // 支付状态
    const STATUS_INIT = 0;
    const STATUS_NOBIND = 1;
    const STATUS_PAYOK = 2;
    const STATUS_PREDO = 3; // 未处理
    const STATUS_DOING = 4; // 处理中
    const STATUS_CANCEL = 5; // 已撤消
    const STATUS_WILLPAY = 6; // 未支付
    const STATUS_BIND = 8;
    const STATUS_PAYFAIL = 11;
    const STATUS_SENDSMS = 14;//重获验证码
    const STATUS_OTHER = 99;

    // 支付方式
    const PAY_TZT = 101; // 投资通
    const PAY_QUICK = 102; // 一键支付
    const PAY_CHANPAY = 103; //畅捷支付
    const PAY_LIANLIAN = 104; //连连支付
    const PAY_RONGBAO = 105; //融宝支付
    const PAY_BAOFOO = 106; //宝付代扣
    const PAY_BFAUTH = 107; //宝付认证支付
    const PAY_LIANAUTH = 108; //连连认证支付
    const PAY_YJF = 111; //易极付支付
    const PAY_CJQUICK = 128; //畅捷天津有信快捷支付
	const PAY_CJXY = 177; //畅捷萍乡海桐协议支付

    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * 获取状态
     */
    public static function getStatus() {
        return [
            self::STATUS_INIT => '初始',
            self::STATUS_NOBIND => '未绑卡',
            self::STATUS_BIND => '已绑卡',
            self::STATUS_PAYOK => '成功',
            self::STATUS_PREDO => '未处理', // 未处理
            self::STATUS_DOING => '处理中', // 处理中
            self::STATUS_CANCEL => '已撤消', // 已撤消
            self::STATUS_PAYFAIL => '失败',
            self::STATUS_OTHER => '未知',
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%payorder}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'business_id', 'channel_id', 'identityid', 'orderid', 'bankname', 'cardno', 'card_type', 'idcard', 'name', 'phone', 'callbackurl', 'userip',], 'required'],
            [['aid', 'business_id', 'channel_id', 'card_type', 'productcatalog', 'amount', 'orderexpdate', 'status', 'client_status'], 'integer'],
            [['create_time', 'modify_time', 'pay_time'], 'safe'],
            [['identityid', 'other_orderid', 'bankname', 'cardno', 'productname', 'res_code'], 'string', 'max' => 50],
            [['orderid'], 'string', 'max' => 30],
            [['idcard', 'name', 'phone', 'userip', 'smscode'], 'string', 'max' => 20],
            [['productdesc', 'res_msg', 'callbackurl'], 'string', 'max' => 200],
            [['business_id', 'orderid'], 'unique', 'targetAttribute' => ['business_id', 'orderid'], 'message' => 'The combination of Business ID and Orderid has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
          return [
            'id' => '主键',
            'aid' => '商户应用id',
            'business_id' => '业务id',
            'channel_id' => '支付通道',
            'identityid' => '商户生成的用户唯一用户id',
            'orderid' => '商户生成的唯一绑卡请求号，最长',
            'other_orderid' => '第三方支付订单',
            'bankname' => '银行名称(标准化)',
            'cardno' => '银行卡号',
            'card_type' => '1:借记卡; 2:信用卡',
            'idcard' => '身份证号',
            'name' => '姓名',
            'phone' => '银行留存电话',
            'productcatalog' => '商品类别码',
            'productname' => '商品名称',
            'productdesc' => '商品描述',
            'amount' => '交易金额：元',
            'orderexpdate' => '订单有效期',
            'create_time' => '(内部)创建时间',
            'modify_time' => '(内部)最后修改时间',
            'pay_time' => '支付时间',
            'status' => '0:初始状态,未处理; 1:未绑卡;  2:已支付; 11:支付失败',
            'res_code' => '响应码(标准化)',
            'res_msg' => '响应原因(标准化)',
            'callbackurl' => '回调地址',
            'userip' => '用户id地址',
            'smscode' => '短信验证码',
            'client_status' => '客户端状态',
        ];
    }


    public function getApp(){
        return $this->hasOne(App::className(),['id' => 'aid']);
    }
    public function getChannel(){
        return $this->hasOne(Channel::className(),['id' => 'channel_id']);
    }
    public function getBusiness(){
        return $this->hasOne(Business::className(),['id' => 'business_id']);
    }
    public function getNotify(){
        return $this->hasOne(ClientNotify::className(),['payorder_id' => 'id']);
    }


    public function getId(){
        return $this->id;
    }


    public function getByOrder($orderId,$aid){
        if (!$orderId) {
            return null;
        }
        $where = [
            'aid' => $aid,
            'orderid' => $orderId
        ];
        return static::find()->where($where)->one();
    }


    public function getByBusinessId($businessId,$aid){
        if (!$businessId) {
            return null;
        }
        $where = [
            'aid' => $aid,
            'business_id' => $businessId
        ];
        return static::find()->where($where)->all();
    }


    public function getByChannelId($channelId,$aid){
        if (!$channelId) {
            return null;
        }
        $where = [
            'aid' => $aid,
            'channel_id' => $channelId
        ];
        return static::find()->where($where)->all();
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
     * 获得当日指定卡号在指定通道下充值的笔数和金额
     * @param  [type] $channel_id [description]
     * @param  [type] $cardno     [description]
     * @return [type]             [description]
     */
    public function getQuota($channel_id,$cardno,$amount){
        if (empty($channel_id) || empty($cardno)) {
            return false;
        }
        $where = [
            'and',
            ['channel_id' => $channel_id,'cardno' => $cardno],
            ['>=','create_time',date('Y-m-d')],
            ['<=','create_time',date('Y-m-d 23:59:59')]
        ];
        $data = static::find()->select('sum(amount) amount,count(1) count')->where($where)->asArray()->all();
        return $data;
    }


    /**
     * 是否存在订单
     */
    public function existsOrder($orderid, $aid) {
        if (!$orderid) {
            return null;
        }
        $condition = [
            'orderid' => $orderid,
            'aid' => $aid,
        ];
        return static::find()->where($condition)->count() > 0;
    }
    /**
     * 保存数据
     */
    public function saveOrder($postData,$bank) {
        //1 数据验证
        if (!is_array($postData) || empty($postData) || empty($bank)) {
            return $this->returnError(false, "数据不能为空");
        }
        if (empty($postData['orderid'])) {
            return $this->returnError(false, "订单不能为空");
        }
        if (empty($postData['aid'])) {
            return $this->returnError(false, "应用id不能为空");
        }

        //2  保存数据
        $time = date('Y-m-d H:i:s');
        $oBusiness = (new Business())->findByCode($postData['business_code']);
        if (empty($oBusiness)) {
            return $this->returnError(false, "支付路由未找到");
        }
        $data = [
            'aid' => $postData['aid'],
            'business_id' => $oBusiness->id,
            'channel_id' => $bank->channel_id,
            'user_id' => $postData['identityid'],
            'identityid' => $postData['identityid'],
            'orderid' => $postData['orderid'],
            'other_orderid' => '',
            'bankname' => $bank->std_bankname,
            'cardno' => $postData['cardno'],
            'card_type' => $postData['card_type'],
            'idcard' => $postData['idcard'],
            'name' => $postData['username'],
            'phone' => isset($postData['phone']) ? $postData['phone'] : '',

            'productcatalog' => $postData['productcatalog'],
            'productname' => $postData['productname'],
            'productdesc' => $postData['productdesc'],
            'amount' => $postData['amount'],
            'orderexpdate' => $postData['orderexpdate'],
            'create_time' => $time,
            'modify_time' => $time,
            'pay_time' => '0000-00-00 00:00:00',
            'status' => self::STATUS_INIT,
            'callbackurl' => $postData['callbackurl'],
            'userip' => $postData['userip'],
            'smscode' => '',
            'client_status' => 0,
        ];


        //3  是否存在订单
        $orderM = $this->getByOrder($postData['orderid'], $postData['aid']);
        if ($orderM) {
            if ($orderM->status != 0) {
                return $this->returnError(false, "此订单已经存在");
            }

            // 检测是否与db存在的一致, 可能会多次提交
            if ($this->chkEQ($data, $orderM->attributes)) {
                return $this->returnError(false, "请不要重复提交");
            }
        }

        //4  字段检测
        if ($errors = $this->chkAttributes($data)) {
            //var_dump($data);die;
            Logger::dayLog("payorder/error", "error:", json_encode($errors));
            return $this->returnError(false, "保存失败");
        }

        //5  保存数据
        $result = $this->save();
        if (!$result) {
            return $this->returnError(false, "保存失败");
        }

        return true;
    }
    /**
     * 可能存在两次提交订单信息，所以得检查订单信息是否一致
     */
    private function chkEQ($newData, $oldData) {
        $cmps = [
            'aid',
            'identityid',
            'business_id',
            'channel_id',
            'orderid',
            'cardno',
            'idcard',
            'name',
            'phone',
            'productcatalog',
            'productname',
            'productdesc',
            'amount',
            'orderexpdate',
            'status',
        ];
        $result = true;
        foreach ($cmps as $k) {
            if ($oldData[$k] != $oldData[$k]) {
                $result = false;
                break;
            }
        }
        return $result;
    }
    /**
     * 易宝获取回调地址
     */
    public function getCallbackurl($pay_type) {
        switch ($pay_type) {
        case 101: // 投资通
            $callbackurl = Yii::$app->params['tztpaycallbackurl'];
            break;
        case 102: // 易宝一键支付
            $callbackurl = Yii::$app->params['callbackurl'];
            break;
        default:
            $callbackurl = '';
            break;
        }

        return $callbackurl;
    }

    /**
     * 保存订单
     * @param  int $status
     * @param  string $res_msg
     * @param  string $other_orderid 支付流水号
     * @return bool
     */
    public function saveStatus($status, $other_orderid = '', $res_code = "", $res_msg = '' ) {
        //查询第三方接口返回状态码对应标准错误码
        if($res_code){
            $res_code = (string) $res_code;
            $res_code = substr($res_code, 0, 50);
            $errorInfo = (new StdError())->getStdError($this->channel_id,$res_code);
            if(!empty($errorInfo)){
                $this->res_code = $errorInfo['res_code'];
                $this->res_msg = $errorInfo['res_msg'];
            }else{
                $this->res_code = $res_code;
                if ($res_msg) {
                    $res_msg = (string) $res_msg;
                    $res_msg = substr($res_msg, 0, 200);
                    $this->res_msg = $res_msg;
                }
            }
        }
        if ($other_orderid) {
            $this->other_orderid = $other_orderid;
        }
        $this->status = $status;

        $time =  date('Y-m-d H:i:s');

        if( in_array($this->status, [self::STATUS_PAYOK, self::STATUS_PAYFAIL] )){
            $this->pay_time = $time;
        }
        $this->modify_time = $time;
        $result = $this->save();
        return $result;
    }
    /**
     * 返回客户端的状态
     * @param int $status
     * @return int
     */
    public function returnClientStatus($status) {
        if (in_array($status, [Payorder::STATUS_PAYOK, Payorder::STATUS_PAYFAIL])) {
            return $status;
        } else {
            return Payorder::STATUS_DOING;
        }
    }
    /**
     * 返回客户端响应结果
     * @return  []
     */
    public function clientData() {
        return [
            'pay_type' => $this->channel_id,
            'status' => $this->returnClientStatus($this->status),
            'orderid' => $this->orderid,
            'yborderid' => $this->other_orderid,
            'amount' => $this->amount,
            'res_code' => $this->res_code,
            'res_msg' => $this->res_msg,
        ];
    }
    /**
     * POST 异步通知客户端
     * @return bool
     */
    private function clientPost($callbackurl, $data, $aid) {
        Logger::dayLog('payorder/clientPost', $callbackurl,$data,$aid);
        //1 加密
        $res_data = App::model()->encryptData($aid, $data);
        $postData = ['res_data' => $res_data, 'res_code' => 0];

        //2 post提交
        $oCurl = new \app\common\Curl;
        $res = $oCurl->post($callbackurl, $postData);
        Logger::dayLog('payorder/clientPost', 'post', "客户响应|{$res}|", $callbackurl, $data);
        Logger::dayLog('payorder/clientPost', 'Sign', $postData);
        //3 解析结果
        $res = strtoupper($res);
        return $res == 'SUCCESS';
    }
    /**
     * GET 页面回调链接
     */
    private function clientGet($callbackurl, $data, $aid) {
        //1 加密
        $res_data = App::model()->encryptData($aid, $data);

        //2 组成url
        $link = strpos($callbackurl, "?") === false ? '?' : '&';
        $url = $callbackurl . $link . 'res_code=0&res_data=' . rawurlencode($res_data);
        Logger::dayLog('payorder/clientGet',  $url, 'data:', $data);
        return $url;
    }
    /**
     * POST 异步通知客户端:并仅通知最终结果, 即(成功|失败)
     * @return bool
     */
    public function clientNotify() {
        $isNotify = $this->doClientNotify();
        if ($isNotify) {
            $status = ClientNotify::STATUS_SUCCESS;
        }else{
            $status = ClientNotify::STATUS_INIT;
        }
        $result = (new ClientNotify)->saveData($this->id, $status);
        return $isNotify;
    }
    /**
     * 仅通知
     * @return [type] [description]
     */
    public function doClientNotify(){
        // 已经通知过了
        /*if ($this->client_status == 1) {
        return true;
        }*/
        if (!in_array($this->status, [static::STATUS_PAYOK, static::STATUS_PAYFAIL])) {
            return false;
        }
        // 更新通知状态
        $data = $this->clientData();
        $result = $this->clientPost($this->callbackurl, $data, $this->aid);
        if ($result) {
            $this->client_status = 1;
            $this->modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        }
        return $this->client_status == 1;
    }
    /**
     * GET 回调通知客户端 url
     * @return url
     */
    public function clientBackurl() {
        $data = $this->clientData();
        $url = $this->clientGet($this->callbackurl, $data, $this->aid);
        return $url;
    }


    /**
     * 发短信
     * @return [type] [description]
     */
    public function requestSms(){
        $smscode = rand(100000, 999999);

        $this->modify_time = date('Y-m-d H:i:s');
        $this->smscode = (string) $smscode;
        $result = $this->save();

        if (!$result) {
            Logger::dayLog(
                'pay/requestsms',
                'error', '短信保存失败',
                'smscode', $smscode,
                '错误原因', $this->errors
            );
            return $this->returnError(false, "短信保存失败");
        }
        //2 发送短信
        //if (!(defined('SYSTEM_LOCAL') && SYSTEM_LOCAL)) {//生产才可以发
            $paysms = new PaySms;
            $result = $paysms->sendSms(
                $this->phone,
                $smscode,
                $this->amount,
                $this->aid
            );
            if (!$result) {
                $errorInfo = $paysms->errinfo?$paysms->errinfo:'请稍后重试或您联系客服';
                return $this->returnError(false,$errorInfo);
            }
        // }
        return true;
    }
    /**
     * 保存数据
     */
    public function saveAliOrder($postData,$accountInfo) {
        //1 数据验证
        if (!is_array($postData) || empty($postData) || empty($accountInfo)){
            return $this->returnError(false, "数据不能为空");
        }
        if (empty($postData['orderid'])) {
            return $this->returnError(false, "订单不能为空");
        }
        if (empty($postData['aid'])) {
            return $this->returnError(false, "应用id不能为空");
        }

        //2  保存数据
        $time = date('Y-m-d H:i:s');
        $data = [
            'aid' => $postData['aid'],
            'business_id' => $accountInfo->business_id,
            'channel_id' => 0,
            'user_id' => $postData['identityid'],
            'identityid' => $postData['identityid'],
            'orderid' => $postData['orderid'],
            'other_orderid' => '',
            'bankname' => '',
            'cardno' => '',
            'card_type' => 0,
            'idcard' => '',
            'name' => $postData['username'],
            'phone' => isset($postData['phone']) ? $postData['phone'] : '',

            'productcatalog' => $postData['productcatalog'],
            'productname' => $postData['productname'],
            'productdesc' => $postData['productdesc'],
            'amount' => $postData['amount'],
            'orderexpdate' => $postData['orderexpdate'],
            'create_time' => $time,
            'modify_time' => $time,
            'pay_time' => '0000-00-00 00:00:00',
            'status' => self::STATUS_INIT,
            'callbackurl' => $postData['callbackurl'],
            'userip' => $postData['userip'],
            'smscode' => '',
            'client_status' => 0,
        ];


        //3  是否存在订单
        $orderM = $this->getByOrder($postData['orderid'], $postData['aid']);
        if ($orderM) {
            if ($orderM->status != 0) {
                return $this->returnError(false, "此订单已经存在");
            }

            // 检测是否与db存在的一致, 可能会多次提交
            if ($this->chkEQ($data, $orderM->attributes)) {
                return $this->returnError(false, "请不要重复提交");
            }
        }

        //4  字段检测
        if ($errors = $this->chkAttributes($data)) {
            return $this->returnError(false, "保存失败");
        }

        //5  保存数据
        $result = $this->save();
        if (!$result) {
            return $this->returnError(false, "保存失败");
        }

        return true;
    }

    public function getOrderId($orderid)
    {
        if (empty($orderid)){
            return false;
        }
        return self::find()->where(['orderid'=>$orderid])->one();
    }
    /**
     *  业务查询订单返回结果
     */
    public function getPayorder($cardno,$identityid){
        if(empty($cardno) && empty($identityid)){
            return false;
        }
        $where = [];
        if(!empty($cardno)){
            $where['cardno'] = $cardno;
        }
        if(!empty($identityid)){
            $where['identityid'] =$identityid;
        }
        return self::find()->where($where)->orderBy('id desc')->limit(10)->asArray()->all();
    }

    public function editStatusByPerson($status) {
        $this->status = $status;
        $time =  date('Y-m-d H:i:s');
        if( in_array($this->status, [self::STATUS_PAYOK, self::STATUS_PAYFAIL] )){
            $this->pay_time = $time;
        }
        $this->modify_time = $time;
        $result = $this->save();
        return $result;
    }
}
