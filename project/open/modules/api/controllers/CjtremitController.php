<?php

/**
 * 畅捷出款服务接口  错误码
 */

namespace app\modules\api\controllers;

use app\common\Logger;
use app\models\remit\Setting;
use app\models\cjt\CjtRemit;
// use app\modules\api\common\changjie\CjtApi;
use app\modules\api\common\ApiController;
use app\common\NsqProducer;

class CjtremitController extends ApiController {

    /**
     * 服务id号
     */
    protected $server_id = 13;
    protected $remitModel;
    protected $bankName;

    public function init() {
        parent::init();
        $this->remitModel = new CjtRemit();
        $this->bankName =  include __DIR__ . "/../common/changjie/config/bankConfig.php";
    }

    // public function actionTest(){
    //     $subData = new CjtApi('prod117');
    //     $orderInfo = [
    //         'id' => 1,
    //         'client_id' => '124325565423368',
    //         'bankname' => '兴业银行',
    //         'cardno' => '6222000200115850703',
    //         'name' => '张菲',
    //         'bank_name' => '中关村支行',//支行消息
    //         'amount' => '10',
    //     ];
    //     $bodyInfo = $subData->getBodyPayment($orderInfo);
    //     $trx_code = 'G10002';//代付
    //     $xml_str = $subData->getXmlParam($bodyInfo,$trx_code,$orderInfo['client_id']);

    //     print_r($xml_str);exit;
    //     return $xml_str;
    // }

    // public function actionQuery(){
    //     $subData = new CjtApi('prod117');
    //     $req_sn = '124325565423368';
    //     $bodyInfo = $subData->getQueryBody($req_sn);
    //     $trx_code = 'G20001';//查询
    //     $qery_id = time();
    //     $xml_str = $subData->getXmlParam($bodyInfo,$trx_code,$qery_id);

    //     print_r($xml_str);exit;
    //     return $xml_str;
    // }
    /**
     * 路由首页
     */

    public function actionIndex() {//借记卡
        $card_type = 1;//借记卡
        $this->saveBankRemit($card_type);
    }

    public function actionCredit() {//信用卡
        $card_type = 2;//借记卡
        $this->saveBankRemit($card_type);
    }


    public function saveBankRemit($card_type) {
        //1  参数验证
        $postData = $this->reqData;
        Logger::dayLog('cjt', $postData);
        $aid = $this->appData['id'];
        //业务参数
        $channel_id         = $postData['channel_id'];
        $req_id             = isset($postData['req_id']) ? $postData['req_id'] : '';
        $settle_amount      = isset($postData['settle_amount']) ? $postData['settle_amount'] : '';
        $remit_type         = isset($postData['remit_type']) ? $postData['remit_type'] : '';
        $user_mobile        = isset($postData['user_mobile']) ? $postData['user_mobile'] : '';
        $identityid         = isset($postData['identityid']) ? $postData['identityid'] : '';
        $guest_account_name = isset($postData['guest_account_name']) ? $postData['guest_account_name'] : '';
        $guest_account_bank = isset($postData['guest_account_bank']) ? $postData['guest_account_bank'] : '';
        $guest_account      = isset($postData['guest_account']) ? $postData['guest_account'] : '';
        $callbackurl        = isset($postData['callbackurl']) ? $postData['callbackurl'] : '';
        $postData['account_type'] = 0;//账号类型：0对私；1对公
        $postData['card_type'] = $card_type;
        
        $postData['guest_account_bank'] = isset($this->bankName[$guest_account_bank]) ? $this->bankName[$guest_account_bank]  :  $guest_account_bank;

        if (empty($req_id) || empty($settle_amount) || empty($remit_type) || empty($user_mobile) || empty($identityid) || empty($guest_account_name) || empty($guest_account_bank) || empty($guest_account) || empty($callbackurl) || empty($channel_id)) {
            Logger::dayLog('cjremit/error', $postData);
            return $this->resp(16001, "参数信息不完整");
        }
        //2 策略验证，出款最大金额不能>50000
        if ($settle_amount > 100000) {
            Logger::dayLog('cjremit/error', $postData);
            return $this->resp(16002, "出款金额超限");
        }
        if ($settle_amount <= 0) {
            return $this->resp(16004, "出款金额必须大于0");
        }

        // 判断是否超限
        $oM = new Setting();
        $isDayMax = $oM->isDayMax($aid, $settle_amount, 'cjremit');
        if ($isDayMax) {
            return $this->resp(16005, "单日出款超限");
        }

        //3 获取 应用id 保存数据
        $postData['aid'] = $aid;
        $resultData = $this->remitModel->saveRemitData($postData);
        if (!$resultData) {
            Logger::dayLog(
                    'cjremit/error', 'SaveRemitData', 'remit 数据保存失败', '提交数据', $postData, '错误原因', $this->remitModel->errinfo
            );
            return $this->resp(16003, $this->remitModel->errinfo);
        }
        //加入消息队列
        $nsq  = new NsqProducer();
        $saveRes = $nsq ->addNsq('cjremit',$postData);
        if(!$saveRes){
            return $this->resp(16006, "数据加入队列失败");
        }
        Logger::dayLog('cjremit/nsq', $postData);
        return $this->resp(0, $resultData);
    }

}
