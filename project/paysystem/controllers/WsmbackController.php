<?php
/**
 * 微神马回调
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/17
 * Time: 11:41
 * http://paysystem.com/weishenma/wsmback/notify
 */
namespace app\controllers;

use app\common\Logger;
use app\models\remit\RemitNotify;
use app\models\wsm\WsmRemit;
use app\models\wsm\WsmRemitNotify;
use app\modules\api\common\ApiController;
use app\modules\api\common\CRemitNotify;
use app\modules\api\common\wsm\CWSMRemit;
use app\modules\api\common\wsm\WSMApi;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class WsmbackController
 * @package app\controllers
 * 测试地址：http://paytest.xianhuahua.com/wsmback/notify
 * 地址：http::paysystem.com/wsmback/notify
 */
class WsmbackController extends ApiController
{
    public function init() {
        //parent::init(); 千万不要执行父类的验证方法
    }

    public function beforeAction($action) {
        if (in_array($action->id, ['notify'])) {
            // 局部关闭csrf验证
            $action->controller->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    public function actionNotify() {
        $getData = file_get_contents("php://input");
        Logger::dayLog('wsm/wsmback', 'content',$getData);
        $satet = $this->logicalProcessing($getData);
        Logger::dayLog('wsm/wsmback', 'info',json_encode($satet));
        return "success";

    }

    /**
     * 逻辑处理
     * @param $getData
     * @return array
     */
    private function logicalProcessing($getData)
    {
        $wsm_api = new WSMApi();
        //1.解析数据
        $result = json_decode($getData, true);
        if (empty($result) || empty($result['shddh'])){
            return ['code' => 1030015, 'msg' => '接收到的参数为空！'];
        }
        $verify_sign = $wsm_api->verifySign($result);
        if (!$verify_sign){
            return ['code' => 1030016, 'msg' => '验签失败！'];
        }
        
        //2.查找订单
        $wsm_remit_object = new WsmRemit();
        $orderInfo = $wsm_remit_object->getOrder($result['shddh']);
        if (empty($orderInfo)){
            return ['code' => 1030017, 'msg' => $wsm_remit_object->errinfo];
        }

        //3.更新订单状态
        $update_state = $this->updateWsmRemit($orderInfo, $result);
        if ($update_state['code'] =! 200){
            return $update_state;
        }
        //4.插入一条通知记录
        $oCWSMRemit = new CWSMRemit();
        $notify_stata = $oCWSMRemit->InputNotify($result, $orderInfo);

        return $notify_stata;
    }

    /**
     * 更新订单
     * @param $orderInfo
     * @param $result
     * @return array
     */
    private function updateWsmRemit($orderInfo, $result)
    {

        $update_bill_state = false;
        if (strtolower(ArrayHelper::getValue($result,'state', '')) == 'error'){
            $wsm_api_object = new WSMApi();
            if ($wsm_api_object->isFailCode(ArrayHelper::getValue($result,'errorcode', ''))) {
                $update_bill_state = $orderInfo->saveToFail((string)ArrayHelper::getValue($result, 'errorcode', 0), ArrayHelper::getValue($result, 'msg', ''));
            }else{
                Logger::dayLog('wsm/wsmback', 'fail',$orderInfo->req_id);
            }
        }elseif (strtolower(ArrayHelper::getValue($result,'state', '')) == 'success'){
            $update_bill_state = $orderInfo->saveToSuccess();
        }else{
            return ['code' => 1030018, 'msg' => "状态不合法！"];
        }
        if (!$update_bill_state){
            Logger::dayLog('wsm/wsmback', 'error',$result);
            return ['code' => 1030019, 'msg' => '订单更新失败！'];
        }
        return ['code' => 200, 'msg' => ''];
    }

    
    

}