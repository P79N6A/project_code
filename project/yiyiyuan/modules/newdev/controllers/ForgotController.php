<?php

namespace app\modules\newdev\controllers;

use app\commonapi\Apidepository;
use app\models\news\Payaccount;
use app\models\news\User;
use Yii;

class ForgotController extends NewdevController {
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [];
    }

    public function actionIndex() {
        $userId = $this->get('userid');
        $from = $this->get('from');
        $userInfo = User::findOne($userId);
        if(!$userInfo){
            exit('非法访问');
        }
        return $this->render('index',[
            'userInfo' => $userInfo,
            'from' => $from,
            'csrf' => $this->getCsrf(),
        ]);
    }

    /**
     * 获取重置密码验证码
     * @return bool
     */
    public function actionGetsms(){
        $mobile = $this->post('mobile');
        if(!$mobile){
            $resultArr = array('ret' => '1','msg'=>"获取失败");
            echo json_encode($resultArr);
            exit;
        }
        $apiDep = new Apidepository();
        $params = [
            'from' => 1,
            'channel' => '000002',
            'mobile' => $mobile,
            'srvTxCode' => 'passwordResetPlus',
        ];
        $codeRes = $apiDep->sendmsg($params);
        if(!$codeRes){
            $resultArr = array('ret' => '1','msg'=>"获取失败");
            echo json_encode($resultArr);
            exit;
        }
        $resultArr = array('ret' => '0','msg'=>"获取成功",'data'=>$codeRes);
        echo json_encode($resultArr);
        exit;
    }

    /**
     * 重置密码
     * @return bool
     */
    public function actionPwdresetplus(){
        $postData = $this->post();
        $userInfo = (new User())->getUserinfoByMobile($postData['mobile']);
        if(empty($postData) || !$postData['srvAuthCode']){
            $resultArr = array('ret' => '1','msg'=>"请获取验证码");
            echo json_encode($resultArr);exit;
        }
        if(!$postData['code']){
            $resultArr = array('ret' => '2','msg'=>"请输入验证码");
            echo json_encode($resultArr);exit;
        }
        $payAccountModel = new Payaccount();
        $payAccount = $payAccountModel->getPaysuccessByUserId($userInfo->user_id, 2, 1);
        if(!$payAccount){
            $resultArr = array('ret' => '3','msg'=>"您未开户");
            echo json_encode($resultArr);exit;
        }
        switch ($postData['from']){
            case 'app':
                $retUrl = Yii::$app->request->hostInfo . '/new/depositoryapi/distribute?user_id=' . $userInfo->user_id;
                break;
            case 'weixin':
                $retUrl = Yii::$app->request->hostInfo.'/new/loan';
                break;
            case 'auth':
                $retUrl = Yii::$app->request->hostInfo . '/new/depositorynew?user_id=' . $userInfo->user_id;
                break;
            default:
                $retUrl = Yii::$app->request->hostInfo.'/new/loan';
                break;
        }
        $apiDep = new Apidepository();
        $params = [
            'channel' => '000002',
            'accountId' => $payAccount->accountId,
            'idType' => '01',
            'idNo' => $userInfo->identity,
            'name' => $userInfo->realname,
            'mobile' => $userInfo->mobile,
            'from' => 1,
            'lastSrvAuthCode' => $postData['srvAuthCode'],
            'smsCode' => $postData['code'],
            'retUrl' => $retUrl,
            'notifyUrl' => Yii::$app->request->hostInfo.'/new/getsetpassnotify/resetpwd',
        ];
        $codeRes = $apiDep->pwdresetplus($params);
        if(empty($codeRes)){
            $resultArr = array('ret' => '1','msg'=>"重置失败");
            echo json_encode($resultArr);
            exit;
        }
        $resultArr = array('ret' => '0','msg'=>"重置成功",'data'=>$codeRes);
        echo json_encode($resultArr);
        exit;
    }

    /**
     * 获取csrf
     * @return string
     */
    private function getCsrf() {
        $csrf = Yii::$app->request->getCsrfToken();
        return $csrf;
    }

}
