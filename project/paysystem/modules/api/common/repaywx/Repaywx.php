<?php
/**
 * EBJ  聚合支付开发文档 微信 公众号
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/5
 * Time: 17:40
 */

namespace app\modules\api\common\repaywx;


use app\common\Http;
use app\common\Logger;
use app\models\Payorder;
use app\models\repayment\PayAlipayOrder;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

class Repaywx
{
    protected $server_id = 100;
    public $__config = '';
    private $__returninfo = '';

    public function __construct($cfg=149)
    {
        $oConfig = new Config();
        try {
            $this->__config = $oConfig->getConfig($cfg);
        }catch (\Exception $e){
            //throw new \Exception($e->getMessage());
        }
        $this->__returninfo = $oConfig->returnInfo();
    }

    /**
     * 请求支付
     * @param $data_set
     * @return array
     */
    public function createwxOrder($data_set)
    {
        Logger::dayLog("repaywx/createwxOrder", "接收参数：", $data_set);
        //1. 格式数据
        $data_set = $this->sendFormat($data_set);
        //$data_set = ["merchantOutOrderNo"=>'1268754'.time(), "orderMoney" => 0.01, 'payorder_id'=>'1222'];
        Logger::dayLog("repaywx/createwxOrder", "格式参数：", json_encode($data_set));

        //2.验证参数
        $verify_state = $this->verifyParams($data_set);
        if ($verify_state !== '0000'){
            return $this->returnMsg($verify_state);
        }

        //3.记录数据
        $ret = $this->saveOrder($data_set, PayAlipayOrder::TYPE_FIVE);
        if ($ret !== '0000'){
            return $this->returnMsg($ret);
        }

        //4.请求参数
        $arr = [
            //配置文件参数
            'merid'                 => ArrayHelper::getValue($this->__config, 'merid', ''),
            'noncestr'              => ArrayHelper::getValue($this->__config, 'noncestr', ''), //随机参数 --长度不大于 32 位
            //'notifyUrl'             => ArrayHelper::getValue($this->__config, 'notifyUrl', ''), //商户的通知地址
            //需要传入参数
            'merchantOutOrderNo'    => ArrayHelper::getValue($data_set, 'merchantOutOrderNo', ''), //商户订单号
            'orderMoney'            => ArrayHelper::getValue($data_set, 'orderMoney', ''), //订单金额--订单金额，大于 0 的数字，保留 2 位小数
            'orderTime'             => date('YmdHis'), //订单时间
        ];
        $sign = $this->verificationSign($arr);

        $params = "?merchantOutOrderNo=" . $arr['merchantOutOrderNo'] . "&merid=" . $arr['merid'] . "&noncestr=" . $arr['noncestr'] . "&orderMoney=" . $arr['orderMoney'] . "&orderTime=" . $arr['orderTime'] . "&sign=" . $sign;
        $wxPayURL = ArrayHelper::getValue($this->__config, 'request_url') . $params;
        Logger::dayLog("repaywx/createwxOrder", "返回的url：", $wxPayURL);
        return $this->successMsg(['msg'=>$wxPayURL]);
    }

    /**
     * 查询接口
     * @param $order_id
     * @return array
     */
    public function queryOrder($order_id)
    {
        if (empty($order_id)){
            return $this->returnMsg('0001');
        }
        $key = $this->getMerid($order_id);
        //$key = "321ef04116cf4140a713bbbc19c72883";
        if (empty($key)){
            return $this->returnMsg('0001');
        }
        $pay_merid = $this->getKey($order_id);
        //$pay_merid = '101100117';
        if (empty($pay_merid)){
            return $this->returnMsg('0001');
        }

        $arr = [
            //配置文件参数
            'merid'                 => $pay_merid, //商户号
            'noncestr'              => ArrayHelper::getValue($this->__config, 'noncestr' ,''), //随机参数
            //需要传入参数
            'merchantOutOrderNo'    => $order_id, //商户订单号
        ];
        $sign = $this->verificationSign($arr);
        $arr['sign'] = $sign;
        $result = $this->sendHttpRequest($arr, ArrayHelper::getValue($this->__config, 'query_url'));

        if (empty($result)){
            return $this->returnMsg('0006');
        }
        $result_data = json_decode($result, true);
        Logger::dayLog("repaywx/queryOrder", "返回数据：", json_encode($result_data));
        //如果订单号不存在
        if (empty($result_data['merchantOutOrderNo'])){
            $result_data['merchantOutOrderNo'] = $order_id;
        }
        //验签
        if (!empty($result_data['code']) && $result_data['code'] == 2000){
            return $this->returnMsg('0006');
        }
        //修改状态
        $this->updateOrder($result_data);
        //发送通知
        $this->sendNotify($result_data);
        return $this->successMsg(['msg'=>$result]);

    }

    /**
     * 格式数据
     * @param $data_set
     * @return array
     */
    private function sendFormat($data_set)
    {
        $data_set = [
            'merchantOutOrderNo' => (string)empty($data_set->orderid) ? 0 : $data_set->orderid,
            'payorder_id'        => (string)empty($data_set->id) ? 0 : $data_set->id,
            'channel_id'         => (int)empty($data_set->channel_id) ? 0 : $data_set->channel_id,
            //'notifyUrl'          => '',
            'orderMoney'         => empty($data_set->amount) ? 0 : $data_set->amount / 100,
        ];
        return $data_set;
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
            'merchantOutOrderNo'    => '0002', //
            //'notifyUrl'             => '0003',
            'payorder_id'           => '0013',
            'orderMoney'            => '0004',
        ];
        foreach($verify_data as $key=>$value){
            if (empty($data_set[$key])){
                return $value;
            }
        }
        return '0000';
    }

    /**
     * 错误信息
     * @param string $code
     * @param bool $is_json
     * @return array
     */
    public function returnMsg($code = '0001', $is_json = false)
    {
        $msg = ArrayHelper::getValue($this->__returninfo, $code, '未知错误');
        if ($is_json){
            return json_encode(['res_code'=>$code, 'res_data' => $msg]);
        }
        return ['res_code'=>$this->server_id.$code, 'res_data' => $msg];
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
        $merchantOutOrderNo = ArrayHelper::getValue($data_set, 'merchantOutOrderNo', 0);
        //查看是否存在数据
        $order_info = $oPayAlipayOrder->getOrder($merchantOutOrderNo);
        if (empty($order_info)){
            $save_data = [
                'cli_orderid'	    	=> $merchantOutOrderNo,
                'payorder_id'		=> ArrayHelper::getValue($data_set, 'payorder_id', 0), //商户订单号',
                'aid'		        => ArrayHelper::getValue($data_set, 'aid', 1), //应用id',
                'channel_id'		=> ArrayHelper::getValue($data_set, 'channel_id', 0), //通道id',
                'amount'		    => ArrayHelper::getValue($data_set, 'orderMoney', 0), //交易金额(单位：分)',
                'callbackurl'		=> ArrayHelper::getValue($data_set, 'notifyUrl', ''), //异步通知回调url',
                'status'		    => 0, //0:默认;2:成功;4处理中;11:失败',
                'type'              => $type,
            ];
            $ret = $oPayAlipayOrder->saveOrder($save_data);
            if (!$ret){
                return "0005";
            }
        }
        return '0000';
    }

    /**
     * 生成签名
     * @param $array_notify
     * @return string
     */
    private function verificationSign($array_notify)
    {
        $paramkey = array_keys($array_notify);
        sort($paramkey);
        $signstr = '';
        foreach ($paramkey as $key => $val) {
            $signstr .= '&' . $paramkey[$key] . "=" . $array_notify[$val];
        }
        $signstr = substr($signstr, 1);
        //益倍嘉支付分配的密匙
        $key = ArrayHelper::getValue($this->__config, 'key');

        $sign = md5($signstr . '&key=' . $key);
        return $sign;
    }

    /**
     * 返回参数
     * @param $data
     * @return array
     */
    public function successMsg($data)
    {
        $code = ArrayHelper::getValue($data, 'code', '0000');
        $msg = ArrayHelper::getValue($data, 'msg', 'null');
        return ['res_code'=>$code, 'res_data'=> $msg];
    }

    //通过订单获取商户订单号
    public function getMerid($order_id)
    {
        if (empty($order_id)){
            return false;
        }
        $oPayorder = new Payorder();
        $data_info = $oPayorder->getOrderId($order_id);
        $merid_data = [
            '149' => ArrayHelper::getValue($this->__config, 'merid'),
            '155' => ArrayHelper::getValue($this->__config, 'merid'),
        ];

        $channel_id = ArrayHelper::getValue($data_info, 'channel_id', '');
        return empty($merid_data[$channel_id]) ? '' : $merid_data[$channel_id];
    }

    //通过订单获取商户订单号
    public function getKey($order_id)
    {
        if (empty($order_id)){
            return false;
        }
        $oPayorder = new Payorder();
        $data_info = $oPayorder->getOrderId($order_id);
        $merid_data = [
            '149'       => ArrayHelper::getValue($this->__config, 'key'),
            '155'       => ArrayHelper::getValue($this->__config, 'key'),
        ];

        $channel_id = ArrayHelper::getValue($data_info, 'channel_id', '');
        return empty($merid_data[$channel_id]) ? '' : $merid_data[$channel_id];
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
        $payResult = ArrayHelper::getValue($data_set, 'payResult', '');
        $order_info = $oPayAlipayOrder->getOrder(ArrayHelper::getValue($data_set, 'merchantOutOrderNo', 0));
        if (empty($order_info)){
            return '0007';
        }
        $update_data = [
            "error_msg"           => json_encode($data_set),  //订单的详细信息
            "status"              => $payResult ? 2 : 11,
            "other_orderid"       => ArrayHelper::getValue($data_set, 'orderNo', ''), //平台订单号
            'error_code'          => (string)$payResult, //支付结果
        ];
        //订单的详细信息
        if (!empty($data_set['msg'])) {
            $update_data['error_msg'] = $data_set['msg'];
        }
        $ret = $order_info->updateOrder($update_data);
        if (!$ret){
            return '0008';
        }
        return '0000';
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
        $payorder_info = $oPayorder->getOrderId(ArrayHelper::getValue($data_set, 'merchantOutOrderNo'));
        if (empty($payorder_info)){
            return '0007';
        }
        if (!in_array($payorder_info->status, ['2', '11'])){
            //修改状态
            $payResult = ArrayHelper::getValue($data_set, 'payResult', '');
            $status = $payResult == 1  ? 2 : 11;
            $other_orderid = ArrayHelper::getValue($data_set, 'orderNo', '');
            $res_code = ArrayHelper::getValue($data_set, 'payResult  ', '');
            $res_msg = ArrayHelper::getValue($data_set, 'msg', '');
            $payorder_info->saveStatus($status, $other_orderid, $res_code, $res_msg);
            //通知
            $ret = $payorder_info -> clientNotify();
            if (!$ret){
                return '0009';
            }
        }

        return '0000';
    }

    /**
     * 发送请求
     * @param $params
     * @param $url
     * @return mixed
     */
    function sendHttpRequest($params, $url) {
        $opts = $this -> getRequestParamString($params);
        $ch   = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //不验证HOST
        curl_setopt($ch, CURLOPT_SSLVERSION, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-type:application/x-www-form-urlencoded;charset=UTF-8'
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $opts);

        /**
         * 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
         */
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // 运行cURL，请求网页
        $html = curl_exec($ch);
        // close cURL resource, and free up system resources
        curl_close($ch);
        return $html;
    }

    /**
     * 组装报文
     *
     * @param unknown_type $params
     * @return string
     */
    function getRequestParamString($params) {
        $params_str = '';
        foreach ($params as $key => $value) {
            $params_str .= ($key . '=' . (!isset($value) ? '' : urlencode($value)) . '&');
        }
        return substr($params_str, 0, strlen($params_str) - 1);
    }
}