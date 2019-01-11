<?php
namespace app\modules\api\controllers\controllers310;

use app\commonapi\Apidepository;
use app\models\news\Sms_depository;
use app\models\news\User;
use app\modules\api\common\ApiController;
use Yii;

class DepositoryopencodeController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $version = Yii::$app->request->post('version');
        $userId = Yii::$app->request->post('user_id');
        if (empty($version) || empty($userId)) {
            exit($this->returnBack('99994'));
        }
        $userInfo = User::findOne($userId);
        if (empty($userInfo)) {
            exit($this->returnBack('10001'));
        }
        //短信次数
        $smsCount = (new Sms_depository())->getSmsCount($userInfo->mobile, 1);
        if ($smsCount >= 6) {
            exit($this->returnBack('10003'));
        }
        $codeRes = $this->sendCode($userInfo->mobile);
        if (!$codeRes) {
            exit($this->returnBack('10105'));
        }
        (new Sms_depository())->addList(['recive_mobile' => $userInfo->mobile, 'sms_type' => 1]);
        $array['srv_auth_code'] = $codeRes;
        exit($this->returnBack('0000', $array));
    }

    /**
     * 获取验证码
     * @param $mobile 手机号
     * @param $type 1:开户 2：绑卡
     * @return mixed
     */
    private function sendCode($mobile)
    {
        $condition['srvTxCode'] = 'accountOpenPlus';
        $condition['from'] = 1;
        $condition['channel'] = '000002';
        $condition['mobile'] = $mobile;
        $depositoryApi = new Apidepository();
        return $depositoryApi->sendmsg($condition);
    }
}
