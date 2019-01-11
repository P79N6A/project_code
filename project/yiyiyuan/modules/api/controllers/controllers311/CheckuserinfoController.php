<?php
namespace app\modules\api\controllers\controllers311;

use app\commonapi\Http;
use app\commonapi\ImageHandler;
use app\models\news\Black_list;
use app\models\news\Guide;
use app\models\news\Information_logs;
use app\models\news\Operation_log;
use app\models\news\User;
use app\models\news\User_extend;
use app\models\news\User_history_info;
use app\models\news\User_password;
use app\commonapi\Logger;
use app\modules\api\common\ApiController;
use Yii;

class CheckuserinfoController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');
        $company = Yii::$app->request->post('company');
        $email = Yii::$app->request->post('email');
        if (empty($version) || empty($user_id) || empty($company) || empty($email)) {
            exit($this->returnBack('99994'));
        }
        $user = User::findOne($user_id);
        if (empty($user)) {
            exit($this->returnBack('10001'));
        }
        if ($user->status == 5) {
            exit($this->returnBack('10023'));
        }
        $pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/";
        if(!preg_match($pattern,$email)){
            exit($this->returnBack('10079'));
        }
        //更新身份信息
        $result= $this->updateIdentity($user,$company,$email);
        Logger::dayLog('2.6.5/checkuserinfo_error', "post_data", Yii::$app->request->post());

        exit($this->returnBack('0000',$result));
    }

    /**
     * 判断是否在黑名单内
     * 在，返回false
     * 不在，返回true
     * @param $user
     * @param $identity
     * @return bool
     */
    private function isBlack($user, $identity)
    {
        $black = new Black_list();
        if ($black->getInBlack($identity)) {
            $result = $user->setBlack();
            if ($result) {
                return false;
            }
        }
        return true;
    }

    private function errorreback($code)
    {
        $array['rsp_code'] = $code;
        $array['rsp_msg'] = $this->geterrorcode($code);
        $array['is_back'] = 0;
        return $array;
    }

    private function updateIdentity($user,$company,$email)
    {
        //先保存其他信息，再做身份验证
        $realname = Yii::$app->request->post('realname');
        $identity = Yii::$app->request->post('identity');
        $nation = Yii::$app->request->post('nation');
        $iden_address = Yii::$app->request->post('iden_address');
        $profession = Yii::$app->request->post('profession');
        $income = Yii::$app->request->post('income');
        if(empty($user->realname) || empty($user->identity)){
            if (empty($realname) || empty($identity) ) {
                $array['is_back'] = 0;
                exit($this->returnBack('99994', $array));
            }
        }else{
            $identity=$user->identity;
        }
        if (!Http::checkIdenCard($identity)) {
            $array['is_back'] = 0;
            exit($this->returnBack('10009', $array));
        }
        //记录三要素导流
        (new Guide())->addGuide(['user_id' => $user->user_id, 'mobile' => $user->mobile, 'identity' => $identity, 'realname' => $realname]);
        $opLogModel = new Operation_log();
        $op_condition = $opLogModel->getOperationCondition($user, 1);
        $passModel = new User_password();
        $pass = $passModel->getUserPassword($user->user_id);
        $path = ImageHandler::getUrl($pass->iden_url);
        $array_income = array(
            '1' => '2000以下',
            '2' => '2000-2999',
            '3' => '3000-3999',
            '4' => '4000-4999',
            '5' => '5000以上',
        );
        $extend_condition = array(
            'user_id' => $user->user_id,
            'company' => $company,
            'email'=> $email,
            'profession' => $profession,
            'income' => $array_income[$income],
        );
        $canUpdate = true;
        if ($user->status == 3 || ($user->identity_valid == 2 && !empty($pass) && !empty($pass->iden_url) && @fopen($path, 'r'))) {
            $this->updateInformation($user, $extend_condition);
            $canUpdate = false;
            $array['is_back'] = 1;
            exit($this->returnBack('0000', $array));
        } else if ($user->identity_valid == 2 && $user->identity != $identity) {
            $canUpdate = false;
            $this->updateInformation($user, $extend_condition);
            $logArray = $this->errorreback('10075');
            $informationModel = new Information_logs();
            $num = $informationModel->save_idenlogs($user, 1, $logArray, 1, 1);
            $array['is_back'] = 0;
            if ($num >= 1) {
                $array['is_back'] = 1;
            }
            exit($this->returnBack('10075', $array));
        }

        $userModel = new User();
        $userIdentity = $userModel->getUserinfoByIdentity($identity);
        if (!empty($userIdentity) && $userIdentity->user_id != $user->user_id) {
            $canUpdate = false;
            $this->updateInformation($user, $extend_condition);
            $logArray = $this->errorreback('10076');
            $informationModel = new Information_logs();
            $num = $informationModel->save_idenlogs($user, 1, $logArray, 1, 2);
            $array['is_back'] = 0;
            if ($num >= 1) {
                $array['is_back'] = 1;
            }
            exit($this->returnBack('10075', $array));
        }
        //计算用户的出生年份
        $birthday_year = intval(substr($identity, 6, 4));
        $this->updateInformation($user, $extend_condition);
        $transaction = Yii::$app->db->beginTransaction();
        $pic_self = '/yiyiyuan/identity/' . date('Y/m/d') . '/pic_self_' . $user->user_id . '.jpg';
        $condition = array(
            'identity' => $identity,
            'identity_valid' => 2,
            'birth_year' => $birthday_year,
            'realname' => $realname,
            'pic_self'=>$pic_self,
        );
        $marks = $user->identity_valid == 1 ? 1 : 0;

        if ($marks == 1) {
            $result = $user->update_user($condition);
            if (!$result) {
                $transaction->rollBack();
                $array['is_back'] = 0;
                exit($this->returnBack('10018', $array));
            }
        }

        $iden_url = '/yiyiyuan/identity/' . date('Y/m/d') . '/pic_identity_' . $user->user_id . '.jpg';
        $pass->update_password(array('iden_address' => $iden_address, 'iden_url' => $iden_url, 'nation' => $nation));
        if ($this->isBlack($user, $identity)) {
            $transaction->commit();
            if($canUpdate){
                $regrule = $user->getRegrule($user, 4);
                $xindiao = \app\commonapi\Keywords::xindiao();
                if ($regrule == 1 && (empty($xindiao) || !in_array($user->mobile, $xindiao))) {
                    $user->setBlack();
                }
            }
            $array['iden_url'] = $iden_url;
            $array['iden_url2'] = $pic_self;
            $informationModel = new Information_logs();
            $num = $informationModel->save_idenlogs($user, 1, $array, 1, 0);
            $array['is_back'] = 1;
            exit($this->returnBack('0000', $array));
        }
        $op_result = $opLogModel->save_operlog($op_condition);
        $transaction->commit();
        $array['iden_url'] = $iden_url;
        $array['iden_url2'] = $pic_self;
        $informationModel = new Information_logs();
        $num = $informationModel->save_idenlogs($user, 1, $array, 1, 0);
        $array['is_back'] = 1;
        return $array;
    }

    private function updateInformation($user, $extend_condition)
    {
        $user_extend = User_extend::getUserExtend($user->user_id);
        if (empty($user_extend)) {
            $extendModel = new User_extend();
            $extendModel->save_extend($extend_condition);
        } else {
            $user_extend->update_extend($extend_condition);
        }
    }

}
