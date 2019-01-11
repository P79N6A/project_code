<?php

namespace app\modules\seven\controllers;

use app\commonapi\Logger;
use app\models\news\User;
use app\models\news\User_loan;
use app\modules\sysloan\common\ApiController;

class UserloanController extends ApiController {

    public $enableCsrfValidation = false;

    /*
     * 获取用户在一亿元是否有进行中的借款
     */

    //@TODO  提示语
    public function actionIndex() {
        $required   = ['identity'];  //必传参数
        $httpParams = $this->post();  //获取参数
        $verify     = $this->BeforeVerify($required, $httpParams);
        $userInfo   = (new User)->getUserinfoByIdentity($httpParams['identity']);
        if (!empty($userInfo)) {
            $loanInfo = User_loan::find()->where(['user_id' => $userInfo->user_id])->orderBy("loan_id desc")->one();
            if (!empty($loanInfo)) {

                if (in_array($loanInfo->status, [5, 6, 9, 11, 12, 13])) {
                    $result = ['req_code' => '1000', 'req_msg' => '今日借款已满，明天再来吧'];
                    exit(json_encode($result));
                }

                if (in_array($loanInfo->status, [3, 7])) {
                    $expire = time() - strtotime($loanInfo->create_time);
                    if ($expire < 86400) {
                        $result = ['req_code' => '1001', 'req_msg' => '今日借款已满，明天再来吧'];
                        exit(json_encode($result));
                    }
                }
            }
        }
        $result = ['req_code' => '0000', 'req_msg' => '可发起借款'];
        exit(json_encode($result));
    }

}
