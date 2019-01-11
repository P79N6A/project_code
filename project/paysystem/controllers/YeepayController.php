<?php
/**
 * 易宝一键支付回调接口 内部错误码范围2800-2899
 * 易宝投资通回调接口 内部错误码范围2900-2999
 */
namespace app\controllers;

use app\common\Crypt3Des;
use app\common\Logger;
use app\models\App;
use app\models\Payorder;
use app\models\yeepay\YpQuickOrder;
use app\models\yeepay\YpTztOrder;
use app\modules\api\common\ApiController;
use app\modules\api\common\yeepay\CYeepaytzt;
use app\modules\api\common\yeepay\YeepayQuick;
use app\modules\api\common\yeepay\YeepayTzt;
use Yii;
use yii\helpers\ArrayHelper;

class YeepayController extends ApiController {
    /**
     * 易宝投资通
     */
    private $yeepay;

    public function init() {

    }

    public function beforeAction($action) {
        if (in_array($action->id, ['tztcallurl', 'quickcallurl'])) {
            // 局部关闭csrf验证
            $action->controller->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }
    /**
     * 显示结果信息
     * @param $res_code 错误码0 正确  | >0错误
     * @param $res_data      结果   | 错误原因
     */
    protected function showMessage($res_code, $res_data, $type = 'json', $redirect = null) {
        switch ($type) {
        case 'json':
            return json_encode([
                'res_code' => $res_code,
                'res_data' => $res_data,
            ]);
            break;
        default:
            return $this->render('/pay/showmessage', [
                'res_code' => $res_code,
                'res_data' => $res_data,
            ]);
            break;
        }
    }

    /**
     * 获取订单
     */
    private function getTztOrder($id) {
        if (empty($id)) {
            return $this->returnError(null, "订单号不存在");
        }
        $oTzt = (new YpTztOrder)->getById($id);
        if (!$oTzt) {
            return $this->returnError(null, "未找到订单信息");
        }
        if ($oTzt->status == Payorder::STATUS_PAYOK) {
            return $this->returnError(null, "此订单已经完成，不必重复提交");
        }
        return $oTzt;
    }
    /**
     * 显示易宝请求绑卡链接地址
     * xhhorderid
     */
    public function actionPayurl() {
        //1 验证参数是否正确
        $cryid = $this->get('xhhorderid');
        $xhhorderid = Crypt3Des::decrypt($cryid, Yii::$app->params['trideskey']);

        //2  解析数据
        if (!isset($xhhorderid) || !$xhhorderid) {
            return $this->showMessage(2111, "订单不合法或信息不完整", '');
        }

        //3  获取是否存在该订单
        $oTzt = $this->getTztOrder($xhhorderid);
        if (!$oTzt) {
            return $this->showMessage(2112, $this->errinfo, '');
        }

        //4 输出
        $this->layout = false;
        return $this->render('/pay/payurl', [
            'oPayorder' => $oTzt->payorder,
            'xhhorderid' => $cryid,
            'smsurl' => "/yeepay/getsmscode",
        ]);
    }

    /**
     * 判断 是绑定还是 支付
     * 绑定：自己发短信
     * 未绑定：请求绑定，易宝返回验证码
     */
    public function actionGetsmscode() {
        //1 验证参数是否正确
        $cryid = $this->post('xhhorderid');
        $xhhorderid = Crypt3Des::decrypt($cryid, Yii::$app->params['trideskey']);

        if (!isset($xhhorderid) || !$xhhorderid) {
            return $this->showMessage(2120, "信息不完整");
        }

        //2 获取是否存在该订单
        $oTzt = $this->getTztOrder($xhhorderid);
        if (!$oTzt) {
            return $this->showMessage(2121, $this->errinfo);
        }

        if ($oTzt->status != Payorder::STATUS_BIND) {
            return $this->showMessage(2122, "此订单状态错误!无法完成操作");
        }

        return $this->requestSms($oTzt->payorder);
        /**
         * 返回结果格式
         * [
         *  xhhorderid,
         *  nexturl,
         *  requestid[可选]
         * ]
         */
    }
    /**
     * 发送短信程序
     */
    private function requestSms($oPayorder) {
        //1 保存短信验证码
        if ($oPayorder->status != Payorder::STATUS_BIND) {
            return $this->showMessage(2123, "支付的银行卡必须是绑定的");
        }

        // 2 发送短信
        $result = $oPayorder->requestSms();
        if (!$result) {
            return $this->showMessage(2122, $oPayorder->errinfo);
        }

        //2 返回结果
        return $this->showMessage(0, [
            'isbind' => false,
            'nexturl' => Yii::$app->request->hostInfo . '/yeepay/paycomfirm',
        ]);
    }
    /**
     * 输入验证码确定并支付
     * @param xhhorderid
     * @param validatecode
     *
     * 成功回调客户端
     */
    public function actionPaycomfirm() {
        //1  参数验证
        $xhhorderid = $this->post('xhhorderid');
        $validatecode = $this->post('validatecode');
        $xhhorderid = Crypt3Des::decrypt($xhhorderid, Yii::$app->params['trideskey']);
        if (empty($xhhorderid)) {
            return $this->showMessage(1, "$xhhorderid未找到");
        }
        if (empty($validatecode)) {
            return $this->showMessage(1, "smscode未找到");
        }

        //2  获取是否存在该订单
        $oTzt = $this->getTztOrder($xhhorderid);
        if (!$oTzt) {
            return $this->showMessage(1, $this->errinfo);
        }

        //3 检测是不是是未绑定状态，否则不允许绑定
        if ($oTzt->status != Payorder::STATUS_BIND) {
            return $this->showMessage(null, "此卡未绑定，无法操作");
        }

        //4  获取主订单, 短信验证码检测
        $oPayorder = $oTzt->payorder;
        if (!$oPayorder) {
            return $this->showMessage(140204, "主订单异常,请联系相关人员");
        }
        if ($validatecode != $oPayorder->smscode) {
            return $this->showMessage(140305, "验证码错误");
        }

        //5 调用支付接口
        $oCYeepaytzt = new CYeepaytzt;
        $status = $oCYeepaytzt->pay($oTzt);

        //6. 只有支付中, 支付成功, 支付失败三种状态有效 
        if (in_array($status, [Payorder::STATUS_PAYOK, Payorder::STATUS_PAYFAIL, Payorder::STATUS_DOING])) {
            $url = $oPayorder->clientBackurl();
            Logger::dayLog('yeepay/paygeturl', 'orderid', $xhhorderid, 'URL', $url);
            return $this->showMessage(0, [
                'callbackurl' => $url,
            ]);
        }else {
            return $this->showMessage(140308, "订单处理失败");
        }
    }

    /**
     * 投资通异步回调接口:只有异步，前台是自己的，不在这儿
     */
    public function actionTztcallurl($cfg = '') {
        //1 数据获取
        Logger::dayLog('yeepay/tzt_notify', 'GET', $this->get(), 'POST', $this->post());
        $data = $this->getParam('data');
        $encryptkey = $this->getParam('encryptkey');

        if (!SYSTEM_PROD && defined('SYSTEM_LOCAL') && SYSTEM_LOCAL) {
            $yeepayData = $this->testTzt();
        } else {
            $yeepayData = [];
            if ($cfg) {
                //解析数据
                $oYeepayQuick = new YeepayTzt($cfg);
                $yeepayData = $oYeepayQuick->callback($data, $encryptkey);
            }
            Logger::dayLog('yeepay/tzt_notify', "解密后", $cfg, $encryptkey, $yeepayData);
        }

        //3 无响应时不处理
        if (empty($yeepayData)) {
            Logger::dayLog('yeepay/tzt_notify', '无响应', $cfg);
            exit;
        }
        if (!is_array($yeepayData)) {
            Logger::dayLog('yeepay/tzt_notify', '订单支付失败', $cfg, $yeepayData);
            exit;
        }

        //5 根据易宝返回的订单号检查本数据库是否存在
        $oTzt = (new YpTztOrder)->getByCliOrderId($yeepayData['orderid']);
        if (empty($oTzt)) {
            Logger::dayLog(
                'yeepaycallback/tzt',
                '数据库中无orderid', $yeepayData
            );
            exit;
        }

        //6  检测本地已经是支付成功状态了，则没必要再处理一次
        if (!$oTzt->is_finished()) {
            // 获取异步状态对应关系
            // 易宝状态有   0:支付失败; 1:成功; 2:已撤消;
            $status = $oTzt->asyncStatus($yeepayData['status']);
            if (Payorder::STATUS_PAYOK == $status) {
                // 成功处理逻辑
                $yborderid = (string) $yeepayData['yborderid'];
                $result = $oTzt->savePaySuccess($yborderid);

            } elseif (Payorder::STATUS_PAYFAIL == $status) {
                // 失败处理逻辑
                $error_code = ArrayHelper::getValue($yeepayData, 'errorcode', '');
                $error_msg = ArrayHelper::getValue($yeepayData, 'errormsg', '');
                $yborderid = ArrayHelper::getValue($yeepayData, 'yborderid', '');
                $result = $oTzt->savePayFail($error_code, $error_msg,$yborderid);

            } else {
                // 异步通知仅会出现成功和失败
                exit;
            }
        }

        //7 通知客户端
        $result = $oTzt->payorder->clientNotify();
        if ($result) {
            echo 'SUCCESS';
            exit;
        }
    }
    /**
     * 一健支付回调:
     * 只有支付成功易宝才会回调: 切记切记. 一键支付文档里有写,这与投资通是不同的
     * post 易宝后台异步回调
     * get  用户手动点击回调
     * http://182.92.80.211:8093/yeepay/quickcallurl/prod102?data=0sEMzX6bJhJGkHTjRpGl9gRr9pGe6HRVLLUrYI3rRrAS1j7ri0%2FOiy%2BPW1lUiGRjASau97JhYvaD4VZZ6zFSzy0hLL6tbehLPhBsDuJFH2FPnrISK2aRE%2FtqdXYAGdwtDgCxoiL45f3V6olVEsGPMArHvjHz0WTk8750oPk35i8w4a%2B4z80kT2ru7rXNCjGOPg6Nzj1B%2FzwEtKrFM7xrOklfcb4hZjcqQvgAAY2HgYqU87Sm6qEaIPzIPvC6Vo1gyqkqiiexhWHHQhC25GnoW6zTe7vXc8UKHJm9lPhLuF2XqFY4K7djxrGl%2FAni%2FomQ2eWN7GTIO9mVEE2XiiktammWVlV7L4qhmGKLy%2BPDb4I%2FzDMb6TGWHP5xJalkwsGKv2aTKkv6SGg4u9vjsW6802HiaQ%2Bnv53%2Fpv0NGWpcs0CvKKyT8MXVTTTQ2eJKssk%2BtgYK4hgqQslpQ70MgW%2BSBBdbWYe8jISnRiPx9B9h5CAmm%2BhaYC4usLNKY1A1xp0B&encryptkey=dwsQ5emfdG%2FwUjZWwrBXjH%2F8T1X4dJtibQYJ3GvuL6nV2qlRDLRI0y3AIZ66zdjwqatPFDzf%2FtmRlW1j7bbmU9hktaPTYNIBcxeTX6PWOYdSDb0T0Dpq1QpZ3NwfwFcW%2B2tkyRjTbarlWIHzano4%2Bz571H7UrvUIhw4qESWO5gA%3D
     */
    public function actionQuickcallurl($cfg = '') {
        //1 数据获取
        $isPost = Yii::$app->request->isPost; //OST表示易宝后台异步调用
        $data = $this->getParam('data');
        $encryptkey = $this->getParam('encryptkey');
        $yeepayData = [];
        if ($cfg) {
            //解析数据
            $oYeepayQuick = new YeepayQuick($cfg);
            $yeepayData = $oYeepayQuick->callback($data, $encryptkey);
        }

        Logger::dayLog(
            'yeepay/quick_notify',
            'ispost', $isPost, $cfg,
            'data', $data, $encryptkey,
            $yeepayData
        );

        // 无响应时不处理
        if (empty($yeepayData)) {
            exit;
        }
        if (!is_array($yeepayData)) {
            exit;
        }

        //4 根据易宝返回的订单号检查本数据库是否存在
        $oQuick = (new YpQuickOrder)->getByCliOrderId($yeepayData['orderid']);
        if (empty($oQuick)) {
            Logger::dayLog('yeepay/quick_notify', 'orderid not found', $yeepayData['orderid']);
            exit;
        }
        //5  若状态已经更新成功了，则无需要再更新
        if ($oQuick->status != Payorder::STATUS_PAYOK) {
            // == 1表示支付成功
            if ($yeepayData['status'] == 1) {
                $result = $oQuick->savePaySuccess($yeepayData['yborderid']);
                if (!$result) {
                    Logger::dayLog('yeepay/quick_notify', 'savePaySuccess/保存失败', $yeepayData['orderid']);
                    exit;
                }
            }
        }

        //6 回调客户端
        if ($isPost) {
            $result = $oQuick->payorder->clientNotify();
            if ($result) {
                echo 'SUCCESS';
                exit;
            }
        } else {
            $url = $oQuick->payorder->clientBackurl();
            if ($url) {
                header("Location:{$url}");
            }
        }
    }
    private function testTzt() {
        $success = false;
        if ($success) {
            return array(
                'amount' => 202800,
                'card_last' => '5160',
                'card_top' => '621790',
                'identityid' => '1_1568762-6217905160',
                'merchantaccount' => '10012471228',
                'orderid' => '1_1446537619',
                'status' => 1,
                'yborderid' => 'TZNC802f3ce78e5d452fb52108eec271587c',
            );
        } else {
            return array('amount' => 100000,
                'card_last' => '4020',
                'card_top' => '621226',
                'errorcode' => '600102',
                'errormsg' => '可用余额不足，请更换其他银行卡重新支付。',
                'identityid' => '1_3477893-6212264020',
                'merchantaccount' => '10012471228',
                'orderid' => '1_1446537619',
                'status' => 0,
                'yborderid' => 'TZNC2ab3ecc4e64341239253866d26b5dac0');
        }

    }
}
