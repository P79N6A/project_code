<?php
namespace app\modules\api\controllers\controllers314;

use app\commonapi\Apidepository;
use app\commonapi\Common;
use app\commonapi\Keywords;
use app\models\news\Payaccount;
use app\models\news\User;
use app\models\news\User_bank;
use app\models\news\User_loan;
use app\modules\api\common\ApiController;
use Yii;

/**
 * 默认卡设置
 * 1、用户是否是存管内
 * 1.1、不在存管内，直接更新默认卡
 * 1.2、在存管内
 * 1.2.1、返回需要验证码
 */
class ChangedefaultController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');
        $bank_id = Yii::$app->request->post('bank_id');
        $type = Yii::$app->request->post('type',1);//1首页调用 2银行卡列表页调用

        if (empty($version) || empty($user_id)) {
            exit($this->returnBack('99994'));
        }
        //新存管绑卡（跳转页面）开关
        if (Keywords::isOpenBank() == 1) {
            $this->newBank($user_id, $bank_id, $type);
        } else {
            $this->oldBank($user_id, $bank_id);
        }
    }

    private function newBank($user_id, $bank_id, $type)
    {
        $userObj = User::findOne($user_id);
        if (empty($userObj)) {
            exit($this->returnBack('10001'));
        }

        $payAccountModel = new Payaccount();
        $payAccountObj = $payAccountModel->getPaysuccessByUserId($userObj->user_id, 2, 1);
//        if (empty($payAccountObj) || empty($payAccountObj->accountId)) {
//            exit($this->returnBack('10099'));
//        }
//        if (!empty($payAccountObj) && !empty($payAccountObj->card)) {
//            exit($this->returnBack('10120'));
//        }
        $url = Yii::$app->request->hostInfo . '/borrow/custody/list?user_id=' . $userObj->user_id;
        if($type == 2){
            $url = Yii::$app->request->hostInfo . '/borrow/custody/list?user_id=' . $userObj->user_id . '&type=7';
        }
        exit($this->returnBack('0000', ['url' => $url]));
    }

    private function oldBank($user_id, $bank_id)
    {
        if (empty($bank_id)) {
            exit($this->returnBack('99994'));
        }
        $bankModel = new User_bank();
        $bankInfo = User_bank::findOne($bank_id);
        if (empty($bankInfo)) {
            exit($this->returnBack('10043'));
        }
        if ($bankInfo->user_id != $user_id) {
            exit($this->returnBack('10044'));
        }
        if ($bankInfo->type != 0) {
            exit($this->returnBack('10094'));
        }
        $userInfo = User::findOne($user_id);

        $payAccount = new Payaccount();
        $isOpen = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 1);
        if (!$isOpen) {
            $result = $bankModel->updateDefaultBank($user_id, $bank_id);
            if (!$result) {
                exit($this->returnBack('99999'));
            } else {
                $array['needcode'] = '2';
                exit($this->returnBack('0000', $array));
            }
        }
        if (empty($isOpen->card)) {
            $array['needcode'] = '1';
            exit($this->returnBack('0000', $array));
        }
        $card = User_bank::find()->where(['user_id' => $user_id, 'card' => $isOpen->card, 'status' => 1])->one();
        if ($card) {
            $loan = User_loan::find()->where(['user_id' => $user_id, 'bank_id' => $card->id, 'status' => [5, 6, 9, 11, 12, 13]])->one();
            if (!empty($loan)) {
                exit($this->returnBack('10096'));
            }
        }

        $array['needcode'] = '1';
        exit($this->returnBack('0000', $array));
    }

}
