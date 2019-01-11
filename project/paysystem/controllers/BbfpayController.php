<?php
/**
 * @desc 邦宝付快捷支付
 */
namespace app\controllers;
use app\common\Logger;
use app\models\bangbf\BangbfOrder;
use app\modules\api\common\bangbf\CBangbfQuick;
use Yii;

class BbfpayController extends BaseController {

    public $layout = false;
    private $oCBbf;

    /**
     * 初始化
     */
    public function init() {
        parent::init();
        $env = SYSTEM_PROD ? 'prod' : 'dev';
        $this->oCBbf = new CBangbfQuick($env);
    }
    public function beforeAction($action) {
        if (in_array($action->id, ['backpay','returnurl'])) {
            // 局部关闭csrf验证
            $action->controller->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }
    /**
     * 支付链接地址
     * @return html
     */
    public function actionPayurl() {
        //1 验证参数是否正确
        $cryid = $this->get('xhhorderid', '');
        $bbf_id = (new BangbfOrder())->decryptId($cryid);
        if (!$bbf_id) {
            return $this->showMessage(140101, "订单不合法或信息不完整", '');
        }
        //2  获取是否存在该订单
        $oBbfOrder = (new BangbfOrder)->getByCliOrderId($bbf_id);
        if (!$oBbfOrder) {
            return $this->showMessage(140102, '此订单不存在');
        }
        //3. 组合数据
        $res = $this->oCBbf->pay($oBbfOrder);
        if ($res['res_code'] != 0) {
            return $this->showMessage($res['res_code'], $res['res_data']);
        }
        echo $res['res_data']['html_code'];
        exit;
    }

    /**
     * 支付异步通知接口
     */
    public function actionBackpay() {
        //1. 纪录日志并获取参数
        $postdata = file_get_contents("php://input");
        Logger::dayLog('bbf', 'bbfpay/backpay', $postdata);
        if(empty($postdata)){
            return $this->showMessage('1110001', '回调参数缺失');
        }
        $result = $this->oCBbf->backauthpay($postdata);
        if(!$result){
            Logger::dayLog('bbf', 'backpay/backauthpay', $this->oCBbf->errinfo);
            return $this->showMessage(1110002, '支付失败');
        }
        //5 异步通知客户端
        $result = $this->oCBbf->clientNotify($this->oCBbf->oOrder);
        if (!$result) {
            return $this->showMessage(140412, '支付失败');
        }
        echo 'SUCCESS';die;

    }
}
