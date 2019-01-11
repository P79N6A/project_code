<?php

/**
 * 融宝信用卡出款服务接口 
 */

namespace app\modules\api\controllers;

use app\common\Logger;
use app\models\remit\Setting;
use app\models\rbcredit\RbCreditRemit;
use app\modules\api\common\ApiController;
use yii\helpers\ArrayHelper;

class RbcreditController extends ApiController {

    /**
     * 服务id号
     */
    protected $server_id = 25;
    protected $remitModel;
    protected $bankCode;

    public function init() {
        parent::init();
        $this->remitModel = new RbCreditRemit();
        $this->bankCode =  include __DIR__ . "/../common/rbcredit/config/bankCode.php";
    }

    /**
     * 路由首页
     */
    public function actionIndex() {
        //1  参数验证
        $postData = $this->reqData;
        // $postData = [
        //     'req_id'=>'20160519054422780467',
        //     'settle_amount'=>'1.00',
        //     'remit_type'=>'1',
        //     'user_mobile'=>'13301370812',
        //     'identityid'=>'421122198605204212',
        //     'guest_account_name'=>'韩咏国',
        //     'guest_account_bank'=>'兴业银行',
        //     'guest_account'=>'622908328848991211',
        //     'guest_account_province'=>'北京市',
        //     'guest_account_city'=>'北京市',
        //     'callbackurl'=>'http://yyy.xianhuahua.com/dev/notify/remitbackurl'
        // ];
        Logger::dayLog('Rbcredit', $postData);
        $aid = $this->appData['id'];
        //$aid = 4;
        //业务参数
        $req_id             = ArrayHelper::getValue($postData,'req_id');
        $settle_amount      = ArrayHelper::getValue($postData,'settle_amount','');
        $remit_type         = ArrayHelper::getValue($postData,'remit_type');
        $user_mobile        = ArrayHelper::getValue($postData,'user_mobile','');
        $identityid         = ArrayHelper::getValue($postData,'identityid','');
        $guest_account_name = ArrayHelper::getValue($postData,'guest_account_name','');
        $guest_account_bank = ArrayHelper::getValue($postData,'guest_account_bank','');
        $guest_account      = ArrayHelper::getValue($postData,'guest_account','');
        $callbackurl        = ArrayHelper::getValue($postData,'callbackurl','');
        $bankNames = array_keys($this->bankCode);
        if(!in_array($guest_account_bank,$bankNames)){
            return $this->resp(25001, "不支持该银行");
        }
        $postData['guest_account_bank_code'] = $this->bankCode[$guest_account_bank];
        if (empty($req_id) || empty($settle_amount) || empty($remit_type) || empty($user_mobile) || empty($identityid) || empty($guest_account_name) || empty($guest_account_bank) || empty($guest_account) || empty($callbackurl)) {
            return $this->resp(25002, "参数信息不完整");
        }
        //2 策略验证，出款最大金额不能>50000
        if ($settle_amount > 50000) {
            return $this->resp(25003, "出款金额超限");
        }
        if ($settle_amount <= 0) {
            return $this->resp(25004, "出款金额必须大于0");
        }
        // 判断是否超限
        $oM = new Setting();
        $isDayMax = $oM->isDayMax($aid, $settle_amount, 'rbcredit');
        if ($isDayMax) {
            return $this->resp(25005, "单日出款超限");
        }
        //3 获取 应用id 保存数据
        $postData['aid'] = $aid;
        $resultData = $this->remitModel->saveRemitData($postData);
        if (!$resultData) {
            Logger::dayLog(
                    'rbcredit/error', 'SaveRemitData', 'remit数据保存失败', '提交数据', $postData, '错误原因', $this->remitModel->errinfo
            );
            return $this->resp(25006, $this->remitModel->errinfo);
        }
        return $this->resp(0, $resultData);
    }
}
