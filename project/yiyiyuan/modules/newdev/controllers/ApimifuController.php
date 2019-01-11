<?php

namespace app\modules\newdev\controllers;

use app\commonapi\Common;
use app\models\dev\User_loan_flows;
use app\models\news\Account;
use app\models\news\Bankbill;
use app\models\news\BehaviorRecord;
use app\models\news\Common as Common2;
use app\models\news\Coupon_list;
use app\models\news\Coupon_use;
use app\models\news\User;
use app\models\news\User_auth;
use app\models\news\User_bank;
use app\models\news\User_loan;
use app\models\news\User_loan_extend;
use app\models\news\User_remit_list;
use app\models\news\User_wx;
use app\models\news\White_list;
use Yii;

class ApimifuController extends NewdevController {

    /**
     * 只有登陆帐号才可以访问
     * 子类直接继承
     */
    public function beforeAction($action) {
        return TRUE;
    }

    /* 借款首页 */

    public function actionIndex() {
        $mobile = $this->get('mobile');
        if (empty($mobile)) {
            return json_encode(array('rsp_code'=>'10001','rsp_msg'=>'非法请求!'));
        }
        //获取担保信息
        $user = User::find()->where(['mobile' => $mobile])->one();
        if(empty($user)){
            return json_encode(array('rsp_code'=>'10001','rsp_msg'=>'非法请求!'));
        }
        $userPassword = $user->password;
        $iden_url = '';
        if(!empty($userPassword) && !empty($userPassword->iden_url)){
            $iden_url = $userPassword->iden_url;
        }
        $authModel = new User_auth();
        $authUserIds = $authModel->getAuthByUserId($user->user_id);
        $count = count($authUserIds);
        $array = [
            'pic_identity' => $user->pic_identity,
            'iden_url' => $iden_url,
            'count' => $count
        ];
        $array['gua'] = [];
        foreach ($authUserIds as $val) {
            $fuser = User::findOne($val->from_user_id);
            if(empty($fuser)){
                continue;
            }
            if (!empty($fuser->openid)) {
                $userwx = User_wx::find()->where(['openid' => $fuser->openid])->one();
                $head = empty($userwx)? '': $userwx->head;
                $nickname =  empty($userwx)? '': $userwx->nickname;
            } else {
                continue;
            }
            $authed = User_auth::find()->where(['user_id' => $user->user_id, 'from_user_id' => $val->from_user_id, 'is_up' => 2])->one();
            $array['gua'][] = [
                'head' => $head,
                'nickname' => $nickname,
                'create_time' => $authed->create_time
            ];
        }
        return json_encode($array);
    }

    /**
     * 一亿元用户总数
     */
    public function actionUsernum(){
        $userNum = User::find()->count();
        return json_encode(array('rsp_code'=>'0000','rsp_msg'=>'请求成功!','userNum' => $userNum));
    }

}
