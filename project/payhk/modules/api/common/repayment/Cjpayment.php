<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/29
 * Time: 15:23
 */
namespace app\modules\api\common\repayment;
use app\common\Logger;
use app\common\Common;
use app\models\Payorder;
use app\models\repayment\PayAlipayOrder;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

class Cjpayment
{
    protected $server_id = 100;
    public $__config = '';
    private $__returninfo = '';
    public function __construct($cfg=162)
    {
        $oRepayConfig = new RepayConfig();
        try {
            $this->__config = $oRepayConfig->getConfig(SYSTEM_PROD, $cfg);
        }catch (\Exception $e){
            //throw new \Exception($e->getMessage());
        }
        $this->__returninfo = $oRepayConfig->returnInfo();

    }
    //支付宝H5
    //萍乡支付宝支付接口
    public function createOrder($data_set, $source = 0)
    {

        Logger::dayLog("cj/createorder", "params", $data_set);
        //格式数据
        $data_set = $this->sendFormat($data_set);
        $data_set['source'] = $source;
        //验证参数
        $verify_state = $this->verifyParams($data_set);
        if ($verify_state !== '0000'){
            return $this->returnMsg($verify_state);
        }
        //记录数据
        $ret = $this->saveOrder($data_set, PayAlipayOrder::TYPE_ONE);//记录子表数据
        if ($ret !== '0000'){
            return $this->returnMsg($ret);
        }
        $result = $this->getUrl($data_set);//请求支付宝返回的信息
        $url = $result['PayInfo'];//获取调取支付宝URL
        if(empty($url))   {
            return false;
        }
        //更新子表第三方订单号
        $oPayAlipayOrder = new PayAlipayOrder();
        $order_info = $oPayAlipayOrder->getOrder(ArrayHelper::getValue($data_set, 'orderid', 0));
        $update_data = [
            "other_orderid"    => ArrayHelper::getValue($result, 'InnerTradeNo', ''), //平台订单号
            'modify_time'      =>date('Y-m-d H:i:s'),//最后更新时间
        ];
        $ret = $order_info->updateOrder($update_data);
        if (!$ret){
            return $this->returnMsg('0008');
        }
        return $this->successMsg(['msg'=>$url]);

    }
    /**
    *请求第三方支付获取支付URL
     */
    public function getUrl($data_set){

        $data = array();
        // 基本参数
        $data['Service'] = 'mag_ali_wap_pay';//请求接口名称
        $data['Version'] = ArrayHelper::getValue($this->__config, 'Version', '');//版本号
        $data['PartnerId'] = ArrayHelper::getValue($this->__config, 'PartnerId', '');//商户号
        $data['InputCharset'] = ArrayHelper::getValue($this->__config, 'InputCharset', '');//编码
        $data['TradeDate'] = date('Ymd');//交易日期
        $data['TradeTime'] = date('His');//交易时间
        $data['SignType']  = ArrayHelper::getValue($this->__config, 'SignType', '');
        $data['ReturnUrl'] = ArrayHelper::getValue($data_set, 'callbackurl', '');// 处理完请求后，当前页面自动跳转到商户网站里指定页面的http路径
        $data['Memo'] = '备注';
        $data['OutTradeNo'] = ArrayHelper::getValue($data_set, 'orderid', ''); //订单号
        $data['MchId']  = ArrayHelper::getValue($this->__config, 'PartnerId', '');//商户号
        $data['TradeType'] = 11;//交易类型（即时 11 担保 12）
        $data['TradeAmount'] = ArrayHelper::getValue($data_set, 'orderMoney', '')/100; //订单金额--订单金额，大于 0 的数字，保留 2 位小数
        $data['GoodsName'] = ArrayHelper::getValue($data_set, 'productname', '');//商品名称
        $data['Subject'] = ArrayHelper::getValue($data_set, 'productdesc', '');//订单标题
        $data['OrderStartTime'] = date('YmdHis');//订单起始提交时间
        $data['NotifyUrl']  =ArrayHelper::getValue($this->__config, 'NotifyUrl', '');//异步回调地址
        $data['SpbillCreateIp'] = ArrayHelper::getValue($data_set, 'userip', '');//ip
        $data['Sign'] = $this->rsaSign($data);
        $query = http_build_query($data);
        //生产地址：
        $url = ArrayHelper::getValue($this->__config, 'url', ''). $query;//请求URL获取支付路径
        $back = $this->httpGet_a($url);//支付宝返回的json信息
        $form_data = json_decode($back,true);//转成数组   支付宝返回的信息
        if(ArrayHelper::getValue($form_data, 'AcceptStatus', '') == 'S'){    //如果成功返回否则
            Logger::dayLog("cj/createorder", "zfb_back",$back);
            return $form_data;
        }
        Logger::dayLog("cj/createorder", "zfb_back",$back);
        return false;

    }
    //验签
    public  function rsaSign($args){
        $args=array_filter($args);//过滤掉空值
        ksort($args);
        $query  =   '';
        foreach($args as $k=>$v){
            if($k=='SignType'){
                continue;
            }
            if($query){
                $query  .=  '&'.$k.'='.$v;
            }else{
                $query  =  $k.'='.$v;
            }
        }
        //这地方不能用 http_build_query  否则会urlencode
        $private_key=ArrayHelper::getValue($this->__config, 'private_key', '');//私钥
        $pkeyid = openssl_get_privatekey($private_key);
        openssl_sign($query, $sign, $pkeyid);
        openssl_free_key($pkeyid);
        $sign = base64_encode($sign);
        return $sign;

    }
    //url
    function httpGet_a($order_url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $order_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($ch);
        curl_close($ch);
        return $json;
    }



    ////查询订单

    //  支付结果查询接口 $order_id订单号
    function queryOrder($order_id)
    {
        if (empty($order_id)){
            return $this->returnMsg('0001');
        }
        $data = array();
        // 基本参数
        $data['Service'] = 'nmg_api_query_trade';//请求接口名称
        $data['Version'] = ArrayHelper::getValue($this->__config, 'Version', '');//版本号
        $data['PartnerId'] = ArrayHelper::getValue($this->__config, 'PartnerId', '');//商户号
        $data['InputCharset'] = ArrayHelper::getValue($this->__config, 'InputCharset', '');//编码
        $data['TradeDate'] = date('Ymd');//交易日期
        $data['TradeTime'] = date('His');//交易时间
        $data['SignType']  = ArrayHelper::getValue($this->__config, 'SignType', '');
        $data['TrxId'] = $order_id;//查询单号及我们自己的订单号
        $data['OrderTrxId'] = $order_id;//我们自己的订单号
        $data['TradeType'] = 'pay_order';//原业务订单类型
        //$data['Extension'] = $order;//扩展字段
        $data['Sign'] = $this->rsaSign($data);
        $query = http_build_query($data);
        //生产地址：
        $url = ArrayHelper::getValue($this->__config, 'query_url', ''). $query;//拼接请求url
        $jsonstr = $this->httpGet_a($url);
        $json = json_decode($jsonstr,true);
        if (empty($jsonstr)){
            Logger::dayLog("cj/runquery", "data", $data);
            return false;
        }
        Logger::dayLog("cj/runquery", "查询接口返回信息",$json);
        //如果订单号不存在
        if (empty($json['TrxId'])){
            $json['TrxId'] = $order_id;
        }
        //修改状态
        $this->updateOrder($json);
        //发送通知
        $this->sendNotify($json);
        return $this->successMsg(['msg'=>$jsonstr]);
    }

    /**
     * 通知
     * @param $data_set
     * @return bool
     */
    private function sendNotify($data_set)
    {
        if (empty($data_set)){
            return '0001';
        }
        $oPayorder = new Payorder();
        $payorder_info = $oPayorder->getOrderId(ArrayHelper::getValue($data_set, 'TrxId'));
        if (empty($payorder_info)){
            return '0007';
        }
        $oStatus = ArrayHelper::getValue($payorder_info, 'status');
        if (in_array($oStatus, [Payorder::STATUS_PAYOK, Payorder::STATUS_PAYFAIL])){
            return '0001';
        }

        $payResult = ArrayHelper::getValue($data_set, 'Status');
        //查询接口只返回成功故这里只判断是否成功
        if($payResult=='S'){
            //$status=2;
            $status = Payorder::STATUS_PAYOK;//状态为2成功
        }
        if($payResult=='F'){
            $status = Payorder::STATUS_PAYFAIL;//状态为4处理中
        }

        if (isset($status)){
            //修改状态
            $payResult = ArrayHelper::getValue($data_set, 'RetMsg', '');
            $other_orderid = ArrayHelper::getValue($data_set, 'OrderTrxid', '');
            $res_code = ArrayHelper::getValue($data_set, 'RetMsg  ', '');
            $res_msg = ArrayHelper::getValue($data_set, 'RetMsg', '');
            $payorder_info->saveStatus($status, $other_orderid, $res_code, $res_msg);
            //通知
            $ret = $payorder_info->clientNotify();
            if (!$ret){
                return '0009';
            }
        }

        return '0000';
    }

    /**
     * 更新状态
     * @param $data_set
     * @return bool
     */
    public function updateOrder($data_set)
    {
        if (empty($data_set)){
            return '0001';
        }
        $oPayAlipayOrder = new PayAlipayOrder();
        $oPayorder = new Payorder();
        $payResult = ArrayHelper::getValue($data_set, 'Status', '');//S：成功；P：处理中；F：失败
        if($payResult=='S'){
            //$status=2;//成功
            $status = Payorder::STATUS_PAYOK;//状态为2成功
        }
        if($payResult=='P'){
            //$status=4;//处理中
            $status = Payorder::STATUS_DOING;//状态为4处理中
        }
        if($payResult=='F'){
           // $status=11;//失败
            $status = Payorder::STATUS_PAYFAIL;//状态为11失败
        }
        $order_info = $oPayAlipayOrder->getOrder(ArrayHelper::getValue($data_set, 'TrxId', 0));//子表信息
        $pay_order = $oPayorder->getOrderId(ArrayHelper::getValue($data_set, 'TrxId', 0));//主表信息

        if (empty($order_info)){
            return '0007';
        }
        //子表终态修改
        $update_data = [
            //"error_msg"           => ArrayHelper::getValue($data_set, 'msg', ''),  //订单的详细信息
            "status"              => $status,
            //"other_orderid"       => ArrayHelper::getValue($data_set, 'Trxid', ''), //平台订单号
            'error_code'          => ArrayHelper::getValue($data_set, 'RetCode', ''), //支付结果
            'modify_time'         =>date('Y-m-d H:i:s'),//最后更新时间
        ];
        //主表终态修改
        $data = [
            "status"              => $status,
            "other_orderid"       => ArrayHelper::getValue($data_set, 'OrderTrxid', ''), //第三方订单号
            'modify_time'         =>date('Y-m-d H:i:s'),//最后更新时间
        ];

        //订单的详细信息
        if (!empty($data_set['RetMsg'])) {
            $update_data['error_msg'] = $data_set['RetMsg'];
        }
        $ret = $order_info->updateOrder($update_data);
        if (!$ret){
            return '0008';
        }
        return '0000';
    }

    /**
     * 更新状态 回调函数更新
     * @param $data_set
     * @return bool
     */
    public function updateOrders($data_set)
    {
        if (empty($data_set)){
            return false;
        }
        $oPayAlipayOrder = new PayAlipayOrder();
        $order_info = $oPayAlipayOrder->getOrder(ArrayHelper::getValue($data_set, 'outer_trade_no', 0));
        if (empty($order_info)){
            return false;
        }
        $status = ArrayHelper::getValue($order_info, 'status', 0);
        $payResult = ArrayHelper::getValue($data_set, 'trade_status', '');//
        if($payResult == 'TRADE_SUCCESS'){ //只返回成功第三方
           // $status=2;     
            $status = Payorder::STATUS_PAYOK;//状态为2成功
        }
        $update_data = [
            "status"              => $status,
            "other_orderid"       => ArrayHelper::getValue($data_set, 'inner_trade_no', ''), //平台订单号
            'error_code'          => ArrayHelper::getValue($data_set, 'trade_status', ''), //支付结果
            'modify_time'         =>date('Y-m-d H:i:s'),//最后更新时间
        ];
        $ret = $order_info->updateOrder($update_data);
        if (!$ret){
            return false;
        }
        return true;
    }

    /**
     * 验证参为数
     * @param $data_set
     * @return int|string
     */
    private function verifyParams($data_set)
    {
        //判断配置文件是否存在
        if (empty($this->__config)){
            return '0012';
        }
        if (empty($data_set)){
            return '0001';
        }

        $verify_data = [
            'orderid'        => '0002', //
            //'notifyUrl'    => '0003',
            'payorder_id'    => '0013',
            'orderMoney'     => '0004',
            'aid'            => '0005',
        ];

        //判断是否存在
        foreach($verify_data as $key=>$value){

            if (empty($data_set[$key])){
              return $value;

            }
        }

        return '0000';
    }

    /**
     * 保存数据
     * @param $data_set
     * @param $type
     * @return bool|false|null
     */
    private function saveOrder($data_set, $type)
    {

        if (empty($data_set) || empty($type)){
            return "0001";
        }
        $oPayAlipayOrder = new PayAlipayOrder();
        $orderid = ArrayHelper::getValue($data_set, 'orderid', 0);
        //查看是否存在数据
        $order_info = $oPayAlipayOrder->getOrder($orderid);
        if (!empty($order_info)){
            return "0001";
        }
        $save_data = [
            'cli_orderid'	    => ArrayHelper::getValue($data_set, 'orderid', 0),//订单号
            'payorder_id'		=> ArrayHelper::getValue($data_set, 'payorder_id', 0), //商户订单号',
            'aid'		        => ArrayHelper::getValue($data_set, 'aid', 1), //应用id',
            'channel_id'		=> ArrayHelper::getValue($data_set, 'channel_id', 0), //通道id',
            'amount'		    => ArrayHelper::getValue($data_set, 'orderMoney', 0), //交易金额(单位：分)',
            //'callbackurl'		=> ArrayHelper::getValue($data_set, 'notifyUrl', ''), //异步通知回调url',
            'status'		    => 0, //0:默认;2:成功;4处理中;11:失败',
            'type'              => $type,
        ];

        $ret = $oPayAlipayOrder->saveOrder($save_data);
        if (!$ret){
            return "0005";
        }
        return '0000';

    }

    /**
     * 请求http
     * @param $url
     * @param $data
     * @return bool
     */
    private function sendHttp($url, $data)
    {
        Logger::dayLog('repay/send', 'sendData', $url.$data);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        $ret = curl_exec($ch);
        curl_close($ch);
        Logger::dayLog('repay/send', 'returnData', $ret);
        if (empty($ret)){
            return false;
        }
        $result = json_decode($ret, true);
        if (empty($result)){
            return false;
        }
        return $result;
    }

    /**
     * 错误信息
     * @param string $code
     * @return mixed
     */
    public function returnMsg($code = '0001', $is_json = false)
    {
        $msg = ArrayHelper::getValue($this->__returninfo, $code, '未知错误');
        if ($is_json){
            return json_encode(['res_code'=>$code, 'res_data' => $msg]);
        }
        return ['res_code'=>$this->server_id.$code, 'res_data' => $msg];
    }

    public function successMsg($data)
    {
        $code = ArrayHelper::getValue($data, 'code', '0000');
        $msg = ArrayHelper::getValue($data, 'msg', 'null');
        return ['res_code'=>$code, 'res_data'=> $msg];
    }

    private function sendFormat($data_set)
    {
        $data_set = [
            'orderid' => (string)empty($data_set->orderid) ? 0 : $data_set->orderid,//订单号
            'payorder_id'        => (string)empty($data_set->id) ? 0 : $data_set->id,//商户订单号
            'channel_id'         => (int)empty($data_set->channel_id) ? 0 : $data_set->channel_id,
            'aid'                =>(int)empty($data_set->aid) ? 0 : $data_set->aid,
            'productname'        => empty($data_set->productname) ? 0 : $data_set->productname,//商品名称
            'productdesc'        => empty($data_set->productdesc) ? 0 : $data_set->productdesc,//订单标题
            'orderMoney'         => empty($data_set->amount) ? 0 : $data_set->amount,//金额
            'callbackurl'       =>  empty($data_set->callbackurl) ? 0 : $data_set->callbackurl,//回显地址
            'userip'            =>  empty($data_set->userip) ? 0 : $data_set->userip,//IP地址
        ];
        return $data_set;
    }

    //通过订单获取商户号
    public function getKey($order_id)
    {
        if (empty($order_id)){
            return false;
        }
        $oPayorder = new Payorder();
        $data_info = $oPayorder->getOrderId($order_id);
        $merid_data = [
            '139' => 'yft2017111700002', //139	微信	微信 小小黛	yft2017111700002	1
            '140' => 'yft2017111700002', //	支付宝	支付宝小小黛	yft2017111700002	1
            '141' => 'yft2017120400002', //	微信	微信 天津有信	yft2017120400002	1
            '142' => 'yft2017120400002', //	支付宝	支付宝 天津有信	yft2017120400002	1
            '161' => 'yft2018052800009', //	支付宝	支付宝 萍乡一麻袋支付	yft2018052800009	1
            '162' => '200001540109', //	畅捷支付宝 萍乡支付	
            '166' => '200001160185', //	畅捷支付宝 天津有信
        ];

        $channel_id = ArrayHelper::getValue($data_info, 'channel_id', '');
        return empty($merid_data[$channel_id]) ? '' : $merid_data[$channel_id];
    }
    //通过订单号获取订单信息
    public function getOrder($order_id){

        $oPayorder = new Payorder();
        $data_info = $oPayorder->getOrderId($order_id);
        return $data_info;
    }
    //定时查询
    public function runMinute($start_time, $end_time){
        $success = 0;
        $oPayAlipayOrder = new PayAlipayOrder();
        $data_set = $oPayAlipayOrder->getCjOrder($start_time, $end_time, 200);
        if (!empty($data_set)){
            foreach($data_set as $oEachOrder){
                $channelId = $oEachOrder->channel_id;
                $oRepayment = new Cjpayment($channelId);
                $result = $oRepayment->queryOrder(ArrayHelper::getValue($oEachOrder, 'cli_orderid', 0));
                if($result){
                    $success++;
                }
            }
            return $success;
        }
        return $success;
    }

}