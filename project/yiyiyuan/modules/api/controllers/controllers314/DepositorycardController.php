<?php
namespace app\modules\api\controllers\controllers314;

use app\commonapi\Apidepository;
use app\commonapi\Logger;
use app\models\news\Payaccount;
use app\models\news\User;
use app\models\news\User_bank;
use app\modules\api\common\ApiController;
use Yii;

class DepositorycardController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $version = Yii::$app->request->post('version');
        $userId = Yii::$app->request->post('user_id');
        $bankId = Yii::$app->request->post('bank_id');
        $srvAuthCode = Yii::$app->request->post('srv_auth_code');
        $code = Yii::$app->request->post('code');
        if (empty($version) || empty($userId) || empty($bankId) || empty($srvAuthCode) || empty($code)) {
            $array['status'] = '2';
            exit($this->returnBack('99994', $array));
        }
        $userInfo = User::findOne($userId);
        if (empty($userInfo)) {
            $array['status'] = '2';
            exit($this->returnBack('10001', $array));
        }
        $bankInfo = User_bank::findOne($bankId);
        if (empty($bankInfo)) {
            $array['status'] = '2';
            exit($this->returnBack('10043', $array));
        }
        $payAccount = new Payaccount();
        $isAccount = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 1);
        if (!$isAccount) {
            $array['status'] = '2';
            exit($this->returnBack('10099', $array));
        }
        $apiDep = new Apidepository();
        $params = [
            'channel' => '000002',
            'from' => 1,
            'accountId' => $isAccount->accountId,//存管平台分配的账号
            'idType' => '01',
            'idNo' => $userInfo->identity,
            'name' => $userInfo->realname,
            'mobile' => $userInfo->mobile,
            'cardNo' => $bankInfo->card,
            'lastSrvAuthCode' => $srvAuthCode,
            'smsCode' => $code,
        ];
        $ret_open = $apiDep->binding($params);
        if (!$ret_open) {
            $array['status'] = '2';
            exit($this->returnBack('10104', $array));
        }
        $up_res = $isAccount->update_list(['card' => (string)$bankInfo->id]);
        if (!$up_res) {
            Logger::dayLog('depository/depositorycard', 'pay_account表操作失败', 'user_id->' . $userInfo->user_id, $bankInfo->id);
            $array['status'] = '2';
            exit($this->returnBack('10104', $array));
        }
        $bankModel = new User_bank();
        $default_result = $bankModel->updateDefaultBank($userInfo->user_id, $bankInfo->id);
        if (!$default_result) {
            Logger::dayLog('depository/depositorycard', 'user_bank表操作失败', 'user_id->' . $userInfo->user_id, $bankInfo->id);
            $array['status'] = '2';
            exit($this->returnBack('10104', $array));
        }
        $array['status'] = '1';
        exit($this->returnBack('0000', $array));
    }
}
