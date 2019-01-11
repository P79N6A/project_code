<?php

/**
 * 标的状态变更
 * 小诺出款异步回调地址
 */

namespace app\controllers;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\models\xn\XnRemit;
use app\models\xn\XnClientNotify;
use app\modules\api\common\xn\XnApi;
use app\modules\api\common\xn\CxnRemit;
use Yii;

class XnbidbackController extends BaseController {
    private $XnApi;
    private $param=[];
    protected $params;
    protected $sign;
    protected $t;
    protected $XRemit;
    public function init() {
        //parent::init(); 千万不要执行父类的验证方法
        $is_prod = SYSTEM_PROD;
        //$is_prod = true;
        $env = $is_prod ? 'prod' : 'dev';
        $this->XnApi = new XnApi($env); 
        $this->XRemit = new XnRemit();
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
        Logger::dayLog('xn/bidback', '请求数据', $poststr);
        //$poststr = 'params=cdlnZenTjGBuWEP4cf6S6ayhF2HchGFXcYDOce2HIwUVdMMJY0KkCZqo1YmTzbnn8gmAhQSjmgKKd9jO4OcNWJ5VnAUeP28DKpzTEpxCTOaVp21Cn%2FrRCiUq%2FGJlGXOb5rP3fFc0oZH9bj8vyDXvBOFE%2F9MgerzpOmHXt%2BTt4fubOmu2xwCWVnWS8PhrWV1muR7Gv8KlM0xhcANP7FGkgXYHZFNLPtTDcFGz0ZCuKWiNV4ZF2muiiBoJmW08h9VLleSjyYKpjF0%3D&sign=edd181283abb413923338bd3768a8051&ts=1508313124401';
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
        //Logger::dayLog('xn/bidback', '请求数据', $this->param);
        $params = ArrayHelper::getValue($this->param,'params');
        if(empty($params)){
            return $this->returnErrors(1,'params is empty');
        }
        $sign = ArrayHelper::getValue($this->param,'sign');
        if(empty($sign))
        {
            return $this->returnErrors(2,'sign is empty');
        }
        $ts = ArrayHelper::getValue($this->param,'ts');
        if(empty($ts))
        {
            return $this->returnErrors(3,'ts is empty');
        }
        $params = urldecode($params);
        //进行验签
        $signStatus = $this->XnApi->verify($params,$ts,$sign);
        if(!$signStatus){
            Logger::dayLog('xn/bidback', '验签失败', $this->param,$signStatus);
            return $this->returnErrors(4,'sign is error');
        }
        //数据处理
        $changeStatus = $this->getStatusChange($params);
        if($changeStatus){
            return $this->returnErrors(0,'success');
        }else{
            return $this->returnErrors(5,'faile');
        }
    } 
    /**
     * Undocumented function
     * 数据处理
     * @param [type] $post
     * @return void
     */
    private function getStatusChange($post){  

        // 解密
        try{
            $postdata = $this->XnApi->dodecrypt($post);
        }catch(\Exception $e){
            Logger::dayLog('xn/bidback','dodecrypt', '解密失败' ,'base64', base64_encode($post));
            return false;
        }
        Logger::dayLog('xn/bidback', '更新状态请求数据',$postdata);
        $data = json_decode($postdata,true);
        $head = ArrayHelper::getValue($data, "head");
        $body = ArrayHelper::getValue($data, "body");
        $body = json_decode($body,true);
        $bid_no = ArrayHelper::getValue($body,'bid_no','');
        $bid_status = ArrayHelper::getValue($body,'bid_status','');
        $bid_remark = ArrayHelper::getValue($body,'bid_remark','');
        if(empty($bid_no)){
            Logger::dayLog('xn/bidback','请求号为空',$data);
            return false;
        }
        //从数据库中获取纪录
        $oXnRemit = (new XnRemit)->getByClientId($bid_no);
        if( empty($oXnRemit) ){
            Logger::dayLog('xn/bidback','没找到查询请求号',$bid_no,$data);
            return false;
        }
        //先更新小诺rsp_status和rsp_status_text
        $result = $oXnRemit->saveRspStatus($bid_status,$bid_remark);
        if(!$result){
            Logger::dayLog('xn/bidback','saveRspStatus','更新小诺状态失败',$data);
            return false;
        }
        //检测是否是终态
        if( in_array($oXnRemit -> remit_status, [XnRemit::STATUS_SUCCESS,XnRemit::STATUS_FAILURE])){
            return true;
        }
        if($bid_status=='-1'){
            //审核不通过
            $result = $oXnRemit->saveToFail($bid_status,$bid_remark);
            if(!$result){
                Logger::dayLog('xn/bidback','saveToFail','更新状态失败',$data);
                return false;
            }
        }else if($bid_status==4){
            //满标放款
            $result = $oXnRemit->saveToSuccess();
            if(!$result){
                Logger::dayLog('xn/bidback','saveToSuccess','更新状态失败',$data);
                return false;
            }
        }
       //加入到通知列表中
       $result = (new CxnRemit())->InputNotify($oXnRemit);
       if (!$result) {
            Logger::dayLog('xn/bidback','InputNotify','通知客户端失败',$data,$oXnRemit->id);
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
