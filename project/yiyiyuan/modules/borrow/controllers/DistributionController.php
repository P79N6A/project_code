<?php
namespace app\modules\borrow\controllers;

use app\commonapi\Logger;
use app\models\news\User;
use app\commonapi\ApiSign;
use Yii;



class DistributionController extends BorrowController
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [];
    }
    
    public function actionIndex() {
        $user_token = $this->get('user_token', '');
        $sign = $this->get('sign', '');
        $url = $this->get('url', '');
        $sign_data = [
            'user_token' => $user_token,
            'url' => $url,
        ];
        Logger::dayLog('distribution/index', '跳转地址分发', $user_token,$sign,$url);
        $sign_json = json_encode($sign_data, JSON_UNESCAPED_UNICODE);
        $sign_result = (new ApiSign())->verifyData($sign_json, $sign);
        if (empty($sign_result)) {
            exit('验签失败');
        }

        if (empty($user_token) || empty($sign)) {
            exit('参数错误');
        }

        $o_user = (new User())->getUserinfoByMobile($user_token);
        if (empty($o_user)) {
            exit('用户信息不全');
        }
         Yii::$app->newDev->login($o_user, 1);
        $data = [
            'last_login_time' => date('Y-m-d H:i:s'),
            'last_login_type' => 1
        ];
        $o_user->update_user($data);
        $url = !empty($url) ? $url : '/borrow/loan';
        
        return $this->redirect($url);
    }
    
}