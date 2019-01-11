<?php
namespace app\modules\api\controllers\controllers314;

use app\commonapi\Apidepository;
use app\commonapi\Logger;
use app\models\news\Payaccount;
use app\models\news\User;
use app\models\news\User_bank;
use app\modules\api\common\ApiController;
use Yii;

class DepositoryopenController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');
        $bank_id = Yii::$app->request->post('bank_id');
        $srvAuthCode = Yii::$app->request->post('srv_auth_code');
        $code = Yii::$app->request->post('code');

        if (empty($version) || empty($user_id) || empty($bank_id) || empty($srvAuthCode) || empty($code)) {
            $array['status'] = '2';
            exit($this->returnBack('99994', $array));
        }
        $userInfo = User::findOne($user_id);
        if (empty($userInfo)) {
            $array['status'] = '2';
            exit($this->returnBack('10001', $array));
        }
        $bankInfo = User_bank::findOne($bank_id);
        if (empty($bankInfo)) {
            $array['status'] = '2';
            exit($this->returnBack('10043', $array));
        }
        //判断用户是否存管开户
        $payAccount = new Payaccount();
        $isAccount = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 1);
        if (empty($isAccount)) {
            $this->doopen($userInfo, $bankInfo, $srvAuthCode, $code);
        }
        $array['status'] = '1';
        exit($this->returnBack('0000', $array));
    }

    /**
     * 短信存管开户
     */
    private function doopen($userInfo, $bankInfo, $srvAuthCode, $code)
    {
        $apiDep = new Apidepository();
        $params = [
            'channel' => '000002',
            'idType' => '01',
            'idNo' => $userInfo->identity,
            'name' => $userInfo->realname,
            'mobile' => $userInfo->mobile,
            'cardNo' => $bankInfo->card,
            'acctUse' => '00000',
            'lastSrvAuthCode' => $srvAuthCode,
            'smsCode' => $code,
            'from' => '1',
        ];
        $ret_open = $apiDep->openplus($params);
        $payAccount = new Payaccount();
        $condition = [
            "user_id" => $userInfo->user_id,
            'type' => 2,
            'step' => 1,
        ];
        if (!$ret_open) {
            //判断用户是否开户成功
            $acc = $payAccount->getPaysuccessByUserId($userInfo->user_id);
            if ($acc) {
                $array['status'] = '2';
                exit($this->returnBack('10111', $array));
            }
            $condition['activate_result'] = 0;//失败
            $payAccount->add_list($condition);
            $array['status'] = '2';
            exit($this->returnBack('0000', $array));
        }
        //开户成功
        $condition['activate_result'] = 1;
        $condition['accountId'] = $ret_open["accountId"];
        $condition['card'] = (string)$bankInfo->id;
        $addRes = $payAccount->add_list($condition);
        if (!$addRes) {
            Logger::dayLog('depository/depositoryopen', 'pay_account表操作失败', 'user_id->' . $userInfo->user_id, $condition);
            $array['status'] = '2';
            exit($this->returnBack('0000', $array));
        }
        $userBankModel = new User_bank();
        $userBankModel->updateDefaultBank($userInfo->user_id, $bankInfo->id);
        $array['status'] = '1';
        exit($this->returnBack('0000', $array));
    }
}
