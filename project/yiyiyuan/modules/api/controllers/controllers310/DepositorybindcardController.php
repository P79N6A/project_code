<?php
namespace app\modules\api\controllers\controllers310;

use app\commonapi\Apidepository;
use app\commonapi\Keywords;
use app\commonapi\Logger;
use app\models\news\Payaccount;
use app\models\news\User;
use app\models\news\User_bank;
use app\modules\api\common\ApiController;
use Yii;

/**
 * 默认卡设置
 * 1、有没有默认卡
 * 1.1有默认，解除默认卡
 * 1.1.1、原始默认卡是否是存管内
 * 1.1.2、是存管内，解除绑定存管内的卡
 * 1.2不是存管内用户，设置当前卡为默认卡
 * 1.2.1是存管内用户，需要弹层输入短信验证码
 */
class DepositorybindcardController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');
        $bank_id = Yii::$app->request->post('bank_id');
        $srvAauthCode = Yii::$app->request->post('srv_auth_code');
        $smsCode = Yii::$app->request->post('sms_code');
        $type = Yii::$app->request->post('type', 1);
        if (empty($version) || empty($user_id) || empty($bank_id) || empty($srvAauthCode) || empty($smsCode)) {
            $array['status'] = '2';
            exit($this->returnBack('99994', $array));
        }

        $bankInfo = User_bank::findOne($bank_id);
        if (empty($bankInfo)) {
            $array['status'] = '2';
            exit($this->returnBack('10043', $array));
        }
        if ($bankInfo->type != 0) {
            $array['status'] = '2';
            exit($this->returnBack('10094', $array));
        }
        $userInfo = User::findOne($user_id);

        $payAccount = new Payaccount();
        $isOpen = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 1);
        if (!$isOpen) {
            $array['status'] = '2';
            exit($this->returnBack('10095', $array, $type == 1 ? $this->geterrorcode($array['rsp_code']) : ''));
        }
        $result = $this->bindcard($userInfo, $bankInfo, $isOpen, $srvAauthCode, $smsCode);
        if (!$result) {
            Logger::dayLog('depository/depositorybindcard', '存管绑卡失败', $user_id, $bank_id);
            $array['status'] = '2';
            exit($this->returnBack('0000', $array));
        }
        $re = $isOpen->setCard((string)$bankInfo->id);
        if (!$re) {
            Logger::dayLog('depository/depositorybindcard', 'payaccount表更新失败', $user_id, $bank_id);
            $array['status'] = '2';
            exit($this->returnBack('0000', $array));
        }
        $bankModel = new User_bank();
        $default_result = $bankModel->updateDefaultBank($user_id, $bank_id);
        if (!$default_result) {
            Logger::dayLog('depository/depositorybindcard', 'user_bank更新失败', $user_id, $bank_id);
            $array['status'] = '2';
            exit($this->returnBack('0000', $array));
        }
        $array['status'] = '1';
        exit($this->returnBack('0000', $array));
    }

    private function bindcard($user, $userbank, $payaccount, $srvAauthCode, $smsCode)
    {
        $params = [
            'channel' => '000002',
            'from' => 1,
            'accountId' => $payaccount->accountId, //存管平台分配的账号
            'idType' => '01',
            'idNo' => $user->identity,
            'name' => $user->realname,
            'mobile' => $user->mobile,
            'cardNo' => $userbank->card,
            'lastSrvAuthCode' => $srvAauthCode,
            'smsCode' => $smsCode,
        ];
        $depositoryApi = new Apidepository();
        $result = $depositoryApi->binding($params);
        return $result;
    }


}
