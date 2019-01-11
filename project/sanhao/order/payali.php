<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/16
 * Time: 11:26
 */
require_once(dirname(dirname(__FILE__))."/include/phpqrcode/phpqrcode.php");
$getData = $_GET;

payScanCode($getData['pay_id'], $getData['price']);

/**
 * 支付宝扫码支付
 * @param $merchantOutOrderNo
 * @param $orderMoney
 */
function payScanCode($merchantOutOrderNo, $orderMoney)
{
    $key = "SSCuvu5uvAKf8FA3scFgcNNll2BOyiJU";
    $server_name = "https://".$_SERVER['SERVER_NAME'];
    $data = [
        //配置文件参数
        'merid'         		=> "yft2017120400002", //商户 id
        'noncestr'      		=> "12345678910abcdef",//随机参数
        'notifyUrl'             => $server_name."/repayback.php", //商户的通知地址
        //需要传入参数
        'merchantOutOrderNo'    => $merchantOutOrderNo, //merchantOutOrderNo
        'orderMoney'            => $orderMoney, //订单金额
        'orderTime'             => date('YmdHis'), //订单时间
    ];
    $signstr = 'merchantOutOrderNo='.$data['merchantOutOrderNo'].'&merid='.$data['merid'].'&noncestr='.$data['noncestr'].'&notifyUrl='.$data['notifyUrl'].'&orderMoney='.$data['orderMoney'].'&orderTime='.$data['orderTime'];
    $signstr.= '&key='.$key;
    $data['sign'] = md5($signstr);
    $url = "http://jh.yizhibank.com/api/createPcOrder";
    $result = sendHttp($url, $data);
    if (!empty($result['code'])){
        echo $result['msg'];
        exit;
    }
    //定义纠错级别
    $errorLevel = "L";
    //定义生成图片宽度和高度;默认为3
    $size = "4";
    //生成网址类型
    $url=$result['url'];
    QRcode::png($url, false, $errorLevel, $size);


}
/**
 * 请求http
 * @param $url
 * @param $data
 * @return bool
 */
function sendHttp($url, $data)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    $ret = curl_exec($ch);
    curl_close($ch);
    if (empty($ret)){
        return false;
    }
    $result = json_decode($ret, true);
    if (empty($result)){
        return false;
    }
    return $result;
}