<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/29
 * Time: 15:23
 */
namespace app\modules\api\common\repayment;
use app\common\Logger;
use app\models\Payorder;
use app\models\repayment\PayAlipayOrder;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

class Repayment
{
    protected $server_id = 100;
    public $__config = '';
    private $__returninfo = '';
    public function __construct($cfg=139)
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
    //3.4.3  商户 H5 、APP  支付宝订单接口
    public function createOrder($data_set, $source = 0)
    {

        Logger::dayLog("repay/createorder", "params", $data_set);
        //格式数据
        $data_set = $this->sendFormat($data_set);
        $data_set['source'] = $source;

        //$data_set = ["merchantOutOrderNo"=>(string)1268754, "orderMoney" => 0.01];
        Logger::dayLog("repay/createorder", "format", json_encode($data_set));

        /*
        $data_set = [
            'merchantOutOrderNo' => 'Y12061647028979664',
            'payorder_id'        => '1268864',
            'source'             => 0,
            'channel_id'         => 1,
            'orderMoney'         => 1,
        ];
        */


        //验证参数
        $verify_state = $this->verifyParams($data_set);
        if ($verify_state !== '0000'){
            return $this->returnMsg($verify_state);
        }

        //记录数据
        $ret = $this->saveOrder($data_set, PayAlipayOrder::TYPE_ONE);
        if ($ret !== '0000'){
            return $this->returnMsg($ret);
        }
        $key = ArrayHelper::getValue($this->__config, 'pay_key', '');
        $data = [
            //配置文件参数
            'merid'                 => ArrayHelper::getValue($this->__config, 'pay_merid', ''),
            'noncestr'              => ArrayHelper::getValue($this->__config, 'noncestr', ''), //随机参数 --长度不大于 32 位
            'notifyUrl'             => ArrayHelper::getValue($this->__config, 'notifyUrl', ''), //商户的通知地址
            //需要传入参数
            'merchantOutOrderNo'    => ArrayHelper::getValue($data_set, 'merchantOutOrderNo', ''), //商户订单号
            'orderMoney'            => ArrayHelper::getValue($data_set, 'orderMoney', ''), //订单金额--订单金额，大于 0 的数字，保留 2 位小数
            'orderTime'             => date('YmdHis'), //订单时间
        ];
        $signstr = 'merchantOutOrderNo='.$data['merchantOutOrderNo'].'&merid='.$data['merid'].'&noncestr='.$data['noncestr'].'&notifyUrl='.$data['notifyUrl'].'&orderMoney='.$data['orderMoney'].'&orderTime='.$data['orderTime'];
        $urlstr = ArrayHelper::getValue($this->__config, 'pay_url_h5', '').$signstr;
        $signstr.= '&key='.$key;
        $data['sign'] = md5($signstr);
        $urlstr.='&sign='.$data['sign'];
        $urlstr = urlencode($urlstr);
        /*
        $url_data = [
            'IOS' => ArrayHelper::getValue($this->__config, 'iosAlipaysUrl', '').$urlstr,
            'Android' => ArrayHelper::getValue($this->__config, 'AndroidAlipaysUrl', '').$urlstr,
        ];
        */
        $url = ArrayHelper::getValue($this->__config, 'AndroidAlipaysUrl', '').$urlstr;
        if ($source == 6){
            $url = ArrayHelper::getValue($this->__config, 'iosAlipaysUrl', '').$urlstr;
        }

        /*
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')||strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){
            $url = ArrayHelper::getValue($this->__config, 'iosAlipaysUrl', '').$urlstr;
        }else if(strpos($_SERVER['HTTP_USER_AGENT'], 'Android')){
            $url = ArrayHelper::getValue($this->__config, 'AndroidAlipaysUrl', '').$urlstr;
        }else{
            $url = ArrayHelper::getValue($this->__config, 'AndroidAlipaysUrl', '').$urlstr;
        }
        */
        Logger::dayLog("repay/createorder", "url", $url);
        /*
        header('location:'.$url);
        exit;

        $aliPayURL = ArrayHelper::getValue($this->__config, 'pay_url_h5', '') . $url;
        $str = <<<STR
            <script src="http://jh.yizhibank.com/js/callalipay.js"></script>
            <script type="text/javascript">
            var aliPay = "${aliPayURL}";
            callappjs.callAlipay(aliPay);
            </script>
STR;
        echo $str;exit;
*/
        //用于兼容有米花支付
        if (in_array($source, [1, 2])){
            $url = $urlstr;
        }
        return $this->successMsg(['msg'=>$url]);
    }

    //支付宝扫码
    //3.5.2  商户 PC  发起订单接口
    public function createPcOrder($data_set)
    {
        //格式数据
        $data_set = $this->sendFormat($data_set);
        //验证参数
        $verify_state = $this->verifyParams($data_set);
        if ($verify_state !== '0000'){
            return $this->returnMsg($verify_state);
        }
        //记录数据
        $ret = $this->saveOrder($data_set, PayAlipayOrder::TYPE_TWO);
        if ($ret !== '0000'){
            return $this->returnMsg($ret);
        }
        $key = ArrayHelper::getValue($this->__config, 'pay_key', '');
        $data = [
            //配置文件参数
            'merid'         => ArrayHelper::getValue($this->__config, 'pay_merid', ''), //商户 id
            'noncestr'      => ArrayHelper::getValue($this->__config, 'noncestr', ''),//随机参数
            'notifyUrl'             => ArrayHelper::getValue($this->__config, 'notifyUrl', ''), //商户的通知地址
            //需要传入参数
            'merchantOutOrderNo'    => ArrayHelper::getValue($data_set, 'merchantOutOrderNo', ''), //merchantOutOrderNo
            'orderMoney'            => ArrayHelper::getValue($data_set, 'orderMoney', ''), //订单金额
            'orderTime'             => date('YmdHis'), //订单时间
        ];
        $signstr = 'merchantOutOrderNo='.$data['merchantOutOrderNo'].'&merid='.$data['merid'].'&noncestr='.$data['noncestr'].'&notifyUrl='.$data['notifyUrl'].'&orderMoney='.$data['orderMoney'].'&orderTime='.$data['orderTime'];
        $signstr.= '&key='.$key;
        $data['sign'] = md5($signstr);
        $url = ArrayHelper::getValue($this->__config, 'pay_url_public', ''); //'http://jh.yizhibank.com/api/createPcOrder';
        $result = $this->sendHttp($url, $data);
        if (empty($result)){
            return $this->returnMsg('0006');
        }
        //$result = json_decode($ret, true);
        //格式数据url地址
        if (!empty($result['url'])){
            $result['url'] = ArrayHelper::getValue($this->__config, 'img_url', '').$result['url'];
        }
        return $this->successMsg(['msg'=>$result]);
        /*
        $temp = json_decode(curl_exec($ch));
        curl_close($ch);
        $imgurl = $temp->url;
        // var_dump($temp);
        // die;
        $str = '<img src="http://mobile.qq.com/qrcode?url='.$imgurl.'"/>';
        echo $str;
        */
    }

    //微信扫码、公众号支付
    //3.6.2  商户 PC/ 公众号 发起订单接口
    function createwxOrder($data_set)
    {
        Logger::dayLog("repay/createwxOrder", "params", $data_set);
        //格式数据
        $data_set = $this->sendFormat($data_set);
        /*
        $data_set = [
            'merchantOutOrderNo' => 'Y12061647028979661',
            'payorder_id'       => '1268865',
            'orderMoney'         =>  1,
        ];
        */
        Logger::dayLog("repay/createwxOrder", "format", json_encode($data_set));
        //验证参数
        $verify_state = $this->verifyParams($data_set);
        if ($verify_state !== '0000'){
            return $this->returnMsg($verify_state);
        }
        //记录数据
        $ret = $this->saveOrder($data_set, PayAlipayOrder::TYPE_THREE);
        if ($ret !== '0000'){
            return $this->returnMsg($ret);
        }
        $key = ArrayHelper::getValue($this->__config, 'fast_key', '');
        $data = [
            //配置文件参数
            'merid'                 => ArrayHelper::getValue($this->__config, 'fast_merid', ''), //商户号
            'noncestr'              => ArrayHelper::getValue($this->__config, 'noncestr', ''), //随机参数
            'notifyUrl'             => ArrayHelper::getValue($this->__config, 'notifyUrl', ''), //商户的通知地址
            //需要传入参数
            'merchantOutOrderNo'    => ArrayHelper::getValue($data_set, 'merchantOutOrderNo', ''), //商 户 订 单号
            'orderMoney'            => ArrayHelper::getValue($data_set, 'orderMoney', ''), //orderMoney
            'orderTime'             => date('YmdHis'),  //orderTime
        ];

        $signstr = 'merchantOutOrderNo='.$data['merchantOutOrderNo'].'&merid='.$data['merid'].'&noncestr='.$data['noncestr'].'&notifyUrl='.$data['notifyUrl'].'&orderMoney='.$data['orderMoney'].'&orderTime='.$data['orderTime'];
        $signstr.= '&key='.$key;
        $data['sign'] = md5($signstr);
        $url = ArrayHelper::getValue($this->__config, 'wx_url', '');
        $result = $this->sendHttp($url, $data);
        if (empty($result)){
            return $this->returnMsg('0006');
        }
        //格式数据url地址
        if (!empty($result['url'])){
            $result['url'] = ArrayHelper::getValue($this->__config, 'img_url', '').$result['url'];
        }
        $res = $result;
        if (empty($result['code'])){
            $res = ['msg'=>$result];
        }
        return $this->successMsg($res);
        /*
        $temp = json_decode(curl_exec($ch));
        curl_close($ch);
        $imgurl = $temp->url;
        // var_dump($temp);
        // die;
        $str = '<img src="http://mobile.qq.com/qrcode?url='.$imgurl.'"/>';
                          
        echo $str;
        */
    }

    //发起快捷支付订单
    //3.7.2  商户 PC 、手机端发起订单接口
    public function createQuickOrder($data_set)
    {
        //格式数据
        $data_set = $this->sendFormat($data_set);
        //验证参数
        $verify_state = $this->verifyParams($data_set);
        if ($verify_state !== '0000'){
            return $this->returnMsg($verify_state);
        }
        //记录数据
        $ret = $this->saveOrder($data_set, PayAlipayOrder::TYPE_FOUR);
        if ($ret !== '0000'){
            return $this->returnMsg($ret);
        }
        $key = ArrayHelper::getValue($this->__config, 'fast_key', '');
        $data = [
            //配置文件参数
            'merid'                 => ArrayHelper::getValue($this->__config, 'fast_merid', ''), //商户号
            'noncestr'              => ArrayHelper::getValue($this->__config, 'noncestr', ''), //随机参数
            'notifyUrl'             => ArrayHelper::getValue($this->__config, 'notifyUrl', ''), //商户的通知地址
            //需要传入参数
            'merchantOutOrderNo'    => ArrayHelper::getValue($data_set, 'merchantOutOrderNo', ''), //商户订单号
            'orderMoney'            => ArrayHelper::getValue($data_set, 'orderMoney', ''),  //订单金额
            'orderTime'             => date('YmdHis'), //订单时间
        ];

        $signstr = 'merchantOutOrderNo='.$data['merchantOutOrderNo'].'&merid='.$data['merid'].'&noncestr='.$data['noncestr'].'&notifyUrl='.$data['notifyUrl'].'&orderMoney='.$data['orderMoney'].'&orderTime='.$data['orderTime'];
        $signstr.= '&key='.$key;
        $data['sign'] = md5($signstr);
        //$url = 'http://jh.yizhibank.com/api/createQuickOrder ';
        $url = ArrayHelper::getValue($this->__config, 'fast_pay_url', '');
        $str =  "<form id='pay_form' method='POST' action=".$url.">";

        foreach($data as $key=>$value){
            $str.="<input type='hidden' id='".$key."' name='".$key."' value='".$value."' />";
        }
        $str.="</form><script>function submit() {
            document.getElementById('pay_form').submit();
          }window.onload = submit;</script>";
        echo $str;
    }

    ////查询订单
    //3.9  支付结果查询接口
    function queryOrder($order_id)
    {
        if (empty($order_id)){
            return $this->returnMsg('0001');
        }
        $key = $this->getMerid($order_id);
        if (empty($key)){
            return $this->returnMsg('0001');
        }
        $pay_merid = $this->getKey($order_id);
        if (empty($pay_merid)){
            return $this->returnMsg('0001');
        }
        //$key = ArrayHelper::getValue($this->__config, 'pay_key' ,''); //
        $data = [
            //配置文件参数
            'merid'                 => $pay_merid, //商户号
            'noncestr'              => ArrayHelper::getValue($this->__config, 'noncestr' ,''), //随机参数
            //需要传入参数
            'merchantOutOrderNo'    => $order_id, //商户订单号
        ];
        $signstr = 'merchantOutOrderNo='.$data['merchantOutOrderNo'].'&merid='.$data['merid'].'&noncestr='.$data['noncestr'];
        $signstr.= '&key='.$key;
        $data['sign'] = md5($signstr);

        $url = ArrayHelper::getValue($this->__config, 'query_url', '');
        $ch = curl_init();//打开
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER,0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);
        curl_close($ch);
        if (empty($result)){
            return $this->returnMsg('0006');
        }
        $result_data = json_decode($result, true);
        Logger::dayLog("repay/runquery", "id", json_encode($result_data));
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
            //"error_msg"           => ArrayHelper::getValue($data_set, 'msg', ''),  //订单的详细信息
            "status"              => (int)$payResult == 1 ? 2 : 11,
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
            'merchantOutOrderNo' => (string)empty($data_set->orderid) ? 0 : $data_set->orderid,
            'payorder_id'        => (string)empty($data_set->id) ? 0 : $data_set->id,
            'channel_id'         => (int)empty($data_set->channel_id) ? 0 : $data_set->channel_id,
            'aid'                =>(int)empty($data_set->aid) ? 0 : $data_set->aid,
            //'notifyUrl'          => '',
            'orderMoney'         => empty($data_set->amount) ? 0 : $data_set->amount / 100,
        ];
        return $data_set;
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
            '139' => 'bKo3AsKX9fw9cSpYR7o6QfusqwtwhsZ1', //139	微信	微信 小小黛	yft2017111700002	1
            '140' => 'bKo3AsKX9fw9cSpYR7o6QfusqwtwhsZ1', //	支付宝	支付宝小小黛	yft2017111700002	1
            '141' => 'SSCuvu5uvAKf8FA3scFgcNNll2BOyiJU', //	微信	微信 天津有信	yft2017120400002	1
            '142' => 'SSCuvu5uvAKf8FA3scFgcNNll2BOyiJU', //	支付宝	支付宝 天津有信	yft2017120400002	1
            '161' => 'HOEWfmH3Rt6DpjrRGCIJciyFTPoXr72i', //	支付宝	支付宝 萍乡一麻袋支付	yft2018052800009	1
            '170' => 'XnGtWQfQGQhD2mKZnpEskCoTZbkU5HF6', //	支付宝	支付宝 萍乡一麻袋支付	yft2018052800009	1
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
            '139' => 'yft2017111700002', //139	微信	微信 小小黛	yft2017111700002	1
            '140' => 'yft2017111700002', //	支付宝	支付宝小小黛	yft2017111700002	1
            '141' => 'yft2017120400002', //	微信	微信 天津有信	yft2017120400002	1
            '142' => 'yft2017120400002', //	支付宝	支付宝 天津有信	yft2017120400002	1
            '161' => 'yft2018052800009', //	支付宝	支付宝 萍乡一麻袋支付	yft2018052800009	1
            '170' => 'yft2018061400010', //	支付宝	支付宝 萍乡一麻袋支付	yft2018052800009	1
        ];

        $channel_id = ArrayHelper::getValue($data_info, 'channel_id', '');
        return empty($merid_data[$channel_id]) ? '' : $merid_data[$channel_id];
    }
}