<?php
namespace app\modules\api\controllers\controllers310;

use app\models\news\User_loan;
use app\models\news\User;
use app\modules\api\common\ApiController;
use Yii;

class AccelerationController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $user_id = Yii::$app->request->post('user_id');

        if (empty($user_id)) {
            exit($this->returnBack('99994'));
        }

        $fuser = User::findOne($user_id);
        if (empty($fuser)) {
            exit($this->returnBack('10001'));
        }
       $status= (new User_loan())->getUserInfoByTime($user_id);
        if($status){
            $arrray['auth_click_status']=1;//显示可点击
        }else{
            $arrray['auth_click_status']=2;//不显示（置灰色）
        }
        exit($this->returnBack('0000',$arrray));
    }

}
