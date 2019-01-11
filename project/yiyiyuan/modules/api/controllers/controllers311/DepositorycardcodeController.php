<?php
namespace app\modules\api\controllers\controllers311;

use app\commonapi\Apidepository;
use app\models\news\Sms_depository;
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
class DepositorycardcodeController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');
        $bank_id = Yii::$app->request->post('bank_id');
        $type = Yii::$app->request->post('type', 1);
        if (empty($version) || empty($user_id) || empty($bank_id)) {
            exit($this->returnBack('99994'));
        }
        $bankInfo = User_bank::findOne($bank_id);
        if (empty($bankInfo)) {
            exit($this->returnBack('10043'));
        }
        if ($bankInfo->type != 0) {
            exit($this->returnBack('10094'));
        }
        $userInfo = User::findOne($user_id);
        if (!$userInfo) {
            exit($this->returnBack('10001'));
        }
        //短信次数
        $smsCount = (new Sms_depository())->getSmsCount($userInfo->mobile, 2);
        if ($smsCount >= 6) {
            exit($this->returnBack('10003'));
        }
        $result = $this->sendBindCode($userInfo, $bankInfo);
        if (!$result) {
            exit($this->returnBack('10095'));
        }
        (new Sms_depository())->addList(['recive_mobile' => $userInfo->mobile, 'sms_type' => 2]);
        $array['srv_auth_code'] = $result;
        exit($this->returnBack('0000', $array));
    }

    private function sendBindCode($user, $userbank)
    {
        $condition = [
            'channel' => '000002',
            'mobile' => $user->mobile,
            'from' => 1,
            'reqType' => strval(1),
            'srvTxCode' => 'cardBindPlus',
            'cardNo' => $userbank->card,
        ];
        $depositoryApi = new Apidepository();
        $result = $depositoryApi->sendmsg($condition);
        return $result;
    }
}
