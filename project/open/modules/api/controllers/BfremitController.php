<?php

/**
 * baofoo出款服务接口  错误码
 */

namespace app\modules\api\controllers;

use app\common\Logger;
use app\models\remit\Setting;
use app\models\baofoo\BfRemit;
use app\modules\api\common\ApiController;

class BfremitController extends ApiController {

    /**
     * 服务id号
     */
    protected $server_id = 23;
    protected $remitModel;
    protected $bankName;

    public function init() {
        parent::init();
        $this->remitModel = new BfRemit();
        $this->bankName =  include __DIR__ . "/../common/baofoo/config/bankConfig.php";
    }

    /**
     * 路由首页
     */
    public function actionIndex() {
        //1  参数验证
        $postData = $this->reqData;
        Logger::dayLog('baofoo', $postData);
        $aid = $this->appData['id'];
        
        if(!isset($postData['channel_id'])){
            if($aid == 1){//一亿元
                $postData['channel_id'] = 114;
            }elseif($aid == 4){
                $postData['channel_id'] = 113;
            }
        }
        //业务参数
        $channel_id = $postData['channel_id'];
        $req_id = isset($postData['req_id']) ? $postData['req_id'] : '';
        $settle_amount = isset($postData['settle_amount']) ? $postData['settle_amount'] : '';
        $remit_type = isset($postData['remit_type']) ? $postData['remit_type'] : '';
        $user_mobile = isset($postData['user_mobile']) ? $postData['user_mobile'] : '';
        $identityid = isset($postData['identityid']) ? $postData['identityid'] : '';
        $guest_account_name = isset($postData['guest_account_name']) ? $postData['guest_account_name'] : '';
        $guest_account = isset($postData['guest_account']) ? $postData['guest_account'] : '';
        $callbackurl = isset($postData['callbackurl']) ? $postData['callbackurl'] : '';
        $account_type = 0; //账号类型：0对私；1对公
        $postData['account_type'] = $account_type;
        // if(!$this->bankName[$guest_account_bank]){
        //     return $this->resp(16009, "银行名称格式不正确");
        // }
        $guest_account_bank = $postData['guest_account_bank'];
        $postData['guest_account_bank'] = isset($this->bankName[$guest_account_bank]) ? $this->bankName[$guest_account_bank]  :  $guest_account_bank;

        if (empty($req_id) || empty($settle_amount) || empty($remit_type) || empty($user_mobile) || empty($identityid) || empty($guest_account_name) || empty($guest_account_bank) || empty($guest_account) || empty($callbackurl) || empty($channel_id)) {
            Logger::dayLog('bfremit/error', $postData);
            return $this->resp(16001, "参数信息不完整");
        }
        //2 策略验证，出款最大金额不能>50000
        if ($settle_amount > 50000) {
            Logger::dayLog('bfremit/error', $postData);
            return $this->resp(16002, "出款金额超限");
        }
        if ($settle_amount <= 0) {
            return $this->resp(16004, "出款金额必须大于0");
        }

        // 判断是否超限
        $oM = new Setting();
        $isDayMax = $oM->isDayMax($aid, $settle_amount, 'bfremit');
        if ($isDayMax) {
            return $this->resp(16005, "单日出款超限");
        }

        //3 获取 应用id 保存数据
        $postData['aid'] = $aid;

        $resultData = $this->remitModel->saveRemitData($postData);
        if (!$resultData) {
            Logger::dayLog(
                    'bfremit/error', 'actionSaveRemitData', 'remit 数据保存失败', '提交数据', $postData, '错误原因', $this->remitModel->errinfo
            );
            return $this->resp(16003, $this->remitModel->errinfo);
        }

        return $this->resp(0, $resultData);
    }

}
