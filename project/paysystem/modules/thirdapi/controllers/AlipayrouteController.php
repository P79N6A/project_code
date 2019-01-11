<?php
namespace app\modules\thirdapi\controllers;

use app\modules\thirdapi\common\ApiController;
use app\common\Logger;
use app\models\Payorder;
use app\models\AlipayOrder;
use app\models\AlipayRule;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use app\modules\thirdapi\common\alipay\AlipayServer;
class AlipayrouteController extends ApiController
{
    protected $server_id = 100;

    protected $aid;
    /**
     * 路由首页
     */
     public function actionPay() {
        //参数验证
        $postData = $this->reqData;
        //获取 应用id
        $postData['aid'] = $this->appData['id'];
        //因为生产环境和测试共用一个帐号. 现将非生产唯一identityid加个前缀
        $identityid = (string) $postData['identityid'];
        if (!SYSTEM_PROD) {
            $postData['identityid'] = "T" . $identityid;
        }
        // 如果为黑名单用户，则拒绝访问
        $ip = $postData['userip'];
        $ret = (new AlipayRule)->getAlipayRule($postData);
        if ($res['res_code'] != '0000') {
            return $this->resp($res['res_code'], $res['res_data']);
        }
        $accountInfo = $ret['res_data'];
        $oPayorder =  new Payorder;
        //保存主订单
        $result = $oPayorder->saveAliOrder($postData, $accountInfo);
        if (!$result) {
            return $this->resp('10103', $oPayorder->errinfo);
        }
        //保存子订单
        $alipayOrder = new AlipayOrder;
        $result = $alipayOrder->createOrder($oPayorder,$accountInfo);
        if (!$result) {
            return $this->resp('10104', $alipayOrder->errinfo);
        }
        //获取支付宝支付链接
        $alipay = new AlipayServer;
        $aliPayURL = $alipay->getAlipayUrl($alipayOrder,$accountInfo);
        if(empty($aliPayURL)){
            return $this->resp('10105','获取支付宝支付链接失败');
        }
        //js唤起支付请求
        return $this->render('/alipay/index',[
            'aliPayURL' =>$aliPayURL
        ]);
    }
   
    public function actionTest(){
        $is_yq = false;
        $orderId = 'alipay'.rand(10000,99999);
        $amount = '0.01';
        $alipay = new \app\modules\thirdapi\common\alipay\AlipayApi($is_yq);
        $aliPayURL = $alipay->getAlipayUrl($orderId,$amount);
        //var_dump($aliPayURL);die;
        $this->getView()->title = '支付中';
        return $this->render('/alipay/index',[
            'aliPayURL' =>$aliPayURL
        ]);
    }
}
