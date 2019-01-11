<?php

namespace app\modules\sysloan\controllers;

use app\models\news\Favorite_contacts;
use app\models\news\User_extend;
use app\modules\sysloan\common\ApiController;
use app\models\news\User_password;
use app\commonapi\Logger;
use app\models\news\User;
use Yii;

class GetuseridenurlController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $required = ['user_id'];  //必传参数
        $httpParams = $this->post();  //获取参数
        Logger::dayLog('user_request/user_id', 'user_id --- '. json_encode($httpParams));
        $verify = $this->BeforeVerify($required, $httpParams);
//        $httpParams['user_id'] = 79770442;
        $oUser = User::findOne($httpParams['user_id']);
        if (empty($oUser)) {
            $array = ['rsp_code' => '10001', 'rsp_desc' => $this->errorback('10001')];
            return json_encode($array);
        }
        $data = $this->getUserInfo($oUser);
        if($data['rsp_code'] != '0000'){
            $array = ['rsp_code' => $data['rsp_code'], 'rsp_desc' => $this->errorback($data['rsp_code'])];
            return json_encode($array);
        }
        return json_encode($data);
    }

    private function getUserInfo($oUser){
        $oUserPassword = (new User_password())->getUserPassword($oUser->user_id);
        if (empty($oUserPassword)) {
            $array['rsp_code'] = 10002;
            return $array;
        }
        $oUserExtend = (new User_extend())->getUserExtend($oUser->user_id);
        if (empty($oUserExtend)) {
            $array['rsp_code'] = 10002;
            return $array;
        }
        $oFavorite = (new Favorite_contacts())->getFavoriteByUserId($oUser->user_id);
        if (empty($oFavorite)) {
            $array['rsp_code'] = 10003;
            return $array;
        }
        $res = $this->formatIdentity($oUser->identity);
        $array = [
            'rsp_code' => '0000',
            'user_name' => $oUser->realname,//借款人
            'email' => $oUserExtend->email,//邮箱
            'sex' => $res['sex'],//性别
            'birth_day' => $res['identity'],//出生日期
            'nation' => $oUserPassword->nation,//民族
            'iden_address' => $oUserPassword->iden_address,//联系地址
            'relatives_name' => $oFavorite->relatives_name,//紧急联系人姓名
            'phone' => $oFavorite->phone,//紧急联系人电话
            'iden_url' => empty($oUserPassword->iden_url)?'':Yii::$app->params['img_url'].$oUserPassword->iden_url,//身份证正面url
        ];
        return $array;
    }

    /**
     * @abstract 错误提示信息
     *
     * */
    public function errorback($error_code) {
        $array_error_code = array(
            '10001' => '用户未注册',
            '10002' => '用户未完善资料',
            '10003' => '未找到联系人信息',
        );
        return $array_error_code[$error_code];
    }


    /*
     * 身份证号分段，获取性别
     * @param string $identity 身份证号
     * @return array
     */
    public static function formatIdentity($identity = ''){
        $length = strlen($identity);

        if($length == 18){
            $sex = substr($identity,-2,1)%2;
            $temp = substr($identity, 6,8);
        }else if($length == 15){
            $sex = substr($identity,-1,1)%2;
            $temp = substr($identity, 6,6);
        }

        $result['sex'] = isset($sex) ? ($sex == 1) ? "男" : "女" : '';
        $result['identity'] = isset($temp) ? $temp : '';

        return $result;
    }
}
