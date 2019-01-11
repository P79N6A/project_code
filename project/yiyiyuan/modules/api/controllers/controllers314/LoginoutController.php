<?php
namespace app\modules\api\controllers\controllers314;

use app\commonapi\Crypt3Des;
use Yii;
use app\models\news\User;
use app\models\news\User_password;
use app\modules\api\common\ApiController;

class LoginoutController extends ApiController{
	
	public $enableCsrfValidation = false;
	
	public function actionIndex(){
		$version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');
		if( empty($version) || empty($user_id)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
		}
        $userInfo = User::findIdentity($user_id);
        if (!$userInfo) {
            exit('用户信息不存在');
        }
        Yii::$app->newDev->logout();
        $array = $this->returnBack('0000');
        echo $array;
        exit;
	}
}
