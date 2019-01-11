<?php
namespace app\modules\mihuahua\controllers;

use app\models\news\Payaccount;
use app\models\news\User;
use app\modules\mihuahua\common\ApiController;
use Yii;

class ChkopenController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $required = ['identity'];//必传参数
        $this->BeforeVerify($required, $this->data);

        $userObj = (new User())->getUserinfoByIdentity($this->data['identity']);
        if (empty($userObj)) {
            $this->codeReback('10001', '', '');
        }
        $payAccountModel = new Payaccount();
        $payAccountObj = $payAccountModel->getPaystatusByUserId($userObj->user_id, 2, 1);

        if (empty($payAccountObj)) {
            $this->codeReback('0000', '2', $userObj->mobile);
        }
        $this->codeReback('0000', '1', $userObj->mobile);
    }

    private function codeReback($code, $mark, $phone)
    {
        $result = $this->errorreback($code);
        $result['data'] = [
            'mark' => $mark,
            'phone' => $phone
        ];
        exit(json_encode($result));
    }
}
