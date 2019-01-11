<?php
namespace app\modules\api\controllers\controllers311;

use app\models\news\User;
use app\models\news\User_password;
use app\modules\api\common\ApiController;
use Yii;

class ArtificialvideoController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');
        $identity_url = Yii::$app->request->post('identity_url');

        if (empty($version) || empty($user_id) || empty($identity_url)) {
            exit($this->returnBack('99994'));
        }

        $user = User::find()->where(['user_id' => $user_id])->one();
        $password = User_password::find()->where(['user_id' => $user_id])->one();
        if (empty($user->identity) && (!isset($password) || empty($password->iden_url))) {
            exit($this->returnBack('10074'));
        }
        $condition=[
            'pic_identity' => $identity_url,
            'pic_up_time' => date('Y-m-d H:i:s'),
            'status'=>2,
        ];
        $UpdateUser=$user->update_user($condition);
        $UpdateUserPassword=$password->update_password(['pic_url' => $identity_url]);
        if(!$UpdateUser || !$UpdateUserPassword){
            exit($this->returnBack('99988'));
        }
        exit($this->returnBack('0000'));
    }

}
