<?php

/**
 * 小诺还款异步回调地址
 */

namespace app\controllers;

use app\common\Logger;
use app\models\xn\XnRemit;
use app\models\xn\XnBill;
use yii\helpers\ArrayHelper;
use app\modules\api\common\xn\XnApi;
use Yii;

class XnpaybackController extends BaseController {
    private $XnApi;
    private $param=[];
    protected $params;
    protected $sign;
    protected $t;
    public function init() {
        //parent::init(); 千万不要执行父类的验证方法
        $is_prod = SYSTEM_PROD;
        //$is_prod = true;
        $env = $is_prod ? 'prod' : 'dev';
        $this->XnApi = new XnApi($env); 
    }
    public function beforeAction($action) {
        if (in_array($action->id, ['notify'])) {
            // 局部关闭csrf验证
            $action->controller->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }
    public function actionNotify() {
        $poststr = file_get_contents("php://input", 'r');
        //$poststr = "params=cdlnZenTjGABwtwvKk2tK6%2FZgVwjUicl97%2FGU5P7jzOKRTipmnMYPkFQGS1KZeCuCRJIYBcVf0cmlcsKV1X0smkTrkcKTjpWyqQKlb1fHXJMQy46YlPd30TfGh3p3jF6yaL8wpudcCXWf0P9q2t18KRrrz6e5vPQ9C14RCD8clnzL73zpKAo0Lnu9%2FHWgviW2o3iosC5vphghxTH%2FhSCPV7YzO03lu4kJxj8dvPRuUW0j1JkjTd%2BJ3MXbIXzKgGV5MNiiJlKutk%3D&sign=94aad224a012d8c701566a59010185fc&ts=1508227596755";
        Logger::dayLog('xn/payback', '还款异步通知请求数据', $poststr);
        if( empty($poststr) ){
            return $this->returnErrors(1,'data is empty');
        }
        $poststr = trim($poststr, '&');
        
        //拆分参数
        $paramarr = explode('&', $poststr);
        if (!empty($paramarr)) {
            foreach ($paramarr as $val) {
                $strtemp = explode('=', $val);
                $this->param[$strtemp[0]] = $strtemp[1];
            }
        }
        
        //Logger::dayLog('xn/payback', '请求数据', $this->param);
        $params = ArrayHelper::getValue($this->param,'params');
        if(!isset($params)){
            return $this->returnErrors(1,'params is empty');
        }
        $sign = ArrayHelper::getValue($this->param,'sign');
        if(!isset($sign))
        {
            return $this->returnErrors(2,'sign is empty');
        }
        $ts = ArrayHelper::getValue($this->param,'ts');
        if(!isset($ts))
        {
            return $this->returnErrors(3,'ts is empty');
        }
        $params = urldecode($params);
        /*$this->sign = $this->param['sign'];
        $this->t = $this->param['ts'];*/
        $signStatus = $this->XnApi->verify($params,$ts,$sign);
        if(!$signStatus){
            return $this->returnErrors(4,'sign is error');
        }

        $changeStatus = $this->getStatusChange($params);
        if($changeStatus){
            return $this->returnErrors(0,'success');
        }else{
            return $this->returnErrors(5,'fail');
        }
    } 

    private function getStatusChange($post){  

        // 解密
        try{
            $postdata = $this->XnApi->dodecrypt($post);
        }catch(\Exception $e){
            Logger::dayLog('xn/payback','info', '解密失败' ,'base64', base64_encode($post));
            return false;
        }
        Logger::dayLog('xn/payback', '更新还款状态请求数据',$postdata);
        $data = json_decode($postdata,true);
        $head = ArrayHelper::getValue($data, "head");
        $body = ArrayHelper::getValue($data, "body");
        $body = json_decode($body,true);

        // 从数据库中获取纪录
        $bid_no = ArrayHelper::getValue($body,'bid_no','');
        $bill_status = ArrayHelper::getValue($body,'bill_status','');
        $repay_ret = ArrayHelper::getValue($body,'repay_ret');
        $oBill = (new XnBill)->getByOrderid($bid_no);
        if( empty($oBill) ){
            Logger::dayLog('xn/payback','info', '没找到查询请求号','bid_no',$bid_no);
            return false;
        }
        
        //检测状态是否曾经处理过了
        if( in_array($oBill->status, [XnBill::BILL_STATUS_SUCCESS,XnBill::BILL_STATUS_AREADY])){
            return $this->returnErrors('0','success');
        }
        $res = $oBill ->updateBillStatus($bill_status,$repay_ret);
        if (!$res) {
            Logger::dayLog('xn/payback','updateBillStatus', '更新账单状态失败', $body);
            return FALSE;
        }
        return true;

    }

    public function returnErrors($code,$msg){
        $arr = array(
            'code'=>$code,
            'msg' =>$msg
        );
        return json_encode($arr);
    }
}
