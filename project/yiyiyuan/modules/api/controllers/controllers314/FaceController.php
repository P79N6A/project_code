<?php
namespace app\modules\api\controllers\controllers314;

use app\commonapi\Apihttp;
use app\commonapi\ImageHandler;
use app\commonapi\Logger;
use app\models\news\Information_logs;
use app\models\news\User;
use app\models\news\User_password;
use app\models\news\User_loan_extend;
use app\models\news\Video_auth;
use app\modules\api\common\ApiController;
use Yii;

class FaceController extends ApiController
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
        $user->update_user(array('pic_identity' => $identity_url, 'pic_up_time' => date('Y-m-d H:i:s')));
        $password->update_password(array('pic_url' => $identity_url));
        $iden_url = ImageHandler::getUrl($password->iden_url);
        $identity_url = ImageHandler::getUrl($identity_url);
        //调用人脸识别接口
        $postdata = array(
            'identity' => $user->identity,
            'pic_identity' => $iden_url,
            'identity_url' => $identity_url
        );
        $openApi = new Apihttp;
        $result = $openApi->faceValid($postdata);
        if ($result['res_code'] != '0000') {
            exit($this->returnBack('10008'));
        }
        $password->update_password(array('score' => $result['res_msg']['score']));

        Logger::errorLog(print_r($result, true), 'fas', 'api');
        $infoModel = new Information_logs();
        $results['rsp_code'] = '0000';
        $results['result'] = $result;
        $rep_msg = '';
        if ($result['res_msg']['score'] >= 60 && $user->status != 5) {
            $array['score'] = 1;
            $array['face_status'] = 1;//成功
            $this->updateUser($user);
            $array['is_back'] = 1;
            $rep_msg = '认证成功';
            $type = 0;
            $times = $infoModel->save_idenlogs($user, 2, $results, 1, $type);
        } else if ($user->status == 5) {
            $array['score'] = 0;
            $array['face_status'] = 2;//失败
            $array['is_back'] = 1;
            $rep_msg = '认证失败';
            $type = 4;
            $times = $infoModel->save_idenlogs($user, 2, $results, 1, $type);
        } else {
            $array['score'] = 0;
            $array['is_back'] = 0;
            $rep_msg = '认证失败，请重新采集';
            $type = 4;
            $times = $infoModel->save_idenlogs($user, 2, $results, 1, $type);
            if($times>=3 && $times<5){
                $array['face_status'] = 3; //失败且可以人工认证(失败超3次)
            }else if($times>=5){
                $array['face_status'] = 4; //失败且只能人工认证（失败超5次）
            }else{
                $array['face_status'] = 2;//失败
            }
        }

        if ($times >= 5) {
            $array['is_back'] = 1;
            $rep_msg = '视频认证失败次数过多，请联系微信客服-先花一亿元';
        }
        exit($this->returnBack('0000',$array,$rep_msg));
    }

    private function updateUser($user)
    {
        $user->status = 3; // 审核通过
        $user->verify_time = date('Y-m-d H:i:s');
        //修改未认证用户借款信息
        $up_outmoney_result = (new User_loan_extend())->updateOutMoneyStatus($user->user_id);
        $user->save();
    }
}
