<?php
namespace app\modules\api\controllers\controllers312;

use app\commonapi\Crypt3Des;
use Yii;
use app\models\news\User;
use app\models\news\User_password;
use app\modules\api\common\ApiController;

class LoginController extends ApiController{
	
	public $enableCsrfValidation = false;
	
	public function actionIndex(){
		$version = Yii::$app->request->post('version');
		$mobile = Yii::$app->request->post('mobile');
		$login_password = Yii::$app->request->post('login_password');
		$device_tokens = Yii::$app->request->post('device_tokens');
		$device_type = Yii::$app->request->post('device_type');
		if( empty($version) || empty($mobile) || empty($login_password)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
		}
		
		$user = new User();
		$userinfo = $user->getUserinfoByMobile($mobile);
		if(empty($userinfo)){
            $array = $this->returnBack('10001');
            echo $array;
            exit;
        }
        //用户未注册
        if ($userinfo->user_type == 4) {
            $array = $this->returnBack('10055');
            echo $array;
            exit;
        }

        $userpassword = (new User_password())->getUserPassword($userinfo->user_id);
        //未设置密码
        if(empty($userpassword) || empty($userpassword->login_password)){
            $array = $this->returnBack('10012');
            echo $array;
            exit;
        }
        //密码错误
        if($userpassword->login_password != rawurldecode($login_password)){
            $array = $this->returnBack('10013');
            echo $array;
            exit;
        }
        //更新用户表中最后登录时间和登录位置
        $user_array = array(
            'last_login_time' => date('Y-m-d H:i:s'),
            'last_login_type' => 'app'
        );
        $result = $userinfo->update_user($user_array);
        //更新password表中的设备信息
        $uppass_condition = [
            'device_tokens' => $device_tokens,
            'device_type' => $device_type,
        ];
        $ret = $userpassword->update_password($uppass_condition);

        $is_pay_password = !empty($userpassword->pay_password) ? 'YES' : 'NO';
        $array['mobile'] = $mobile;
        $array['user_id'] = $userinfo->user_id;
        $array['is_paypassword'] = $is_pay_password;

        $array = $this->returnBack('0000', $array);
        echo $array;
        exit;
	}
}
