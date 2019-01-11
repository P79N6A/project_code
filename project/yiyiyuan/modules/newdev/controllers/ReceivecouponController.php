<?php

namespace app\modules\newdev\controllers;

use app\common\ApiClientCrypt;
use app\commonapi\ErrorCode;
use app\commonapi\Logger;
use app\models\news\Coupon_list;
use app\models\news\User;
use Yii;

class ReceivecouponController extends NewdevController {

    public $enableCsrfValidation = false;

    public function behaviors() {
        return [];
    }

    public function actionIndex() {
        $openApi = new ApiClientCrypt;
        $data = $this->post('data');
        $parr = $openApi->parseReturnData($data);
        Logger::dayLog('receivecoupon', $parr);
        $required = ['mobile', 'title', 'day', 'val'];
        $this->verify($required, $parr['res_data']);
        $this->addCoupon($parr['res_data']);
    }

    private function addCoupon($parr){
        $userModel = new User();
        $userInfo = $userModel->getUserinfoByMobile($parr['mobile']);
        if(empty($userInfo)){
            $data_msg = ['res_code' => '10001', 'res_msg' => '未找到用户信息'];
            exit(json_encode($data_msg));
        }
        $couponModel = new Coupon_list();
        $res = $couponModel->sendCoupon($userInfo->user_id,$parr['title'],4,$parr['day'],$parr['val']);
        if(!$res){
            $data_msg = ['res_code' => '10002', 'res_msg' => '添加优惠券失败'];
            exit(json_encode($data_msg));
        }
        $data_msg = ['res_code' => '0000', 'res_msg' => 'SUCCESS'];
        exit(json_encode($data_msg));
    }

    /**
     * 只判断参数是否必传
     * @param array $required
     * @param type $httpParams
     */
    public function verify($required = [], $httpParams = [])
    {
        $errorCodeModel = new ErrorCode();
        if (empty($httpParams) || !is_array($httpParams) || !is_array($required)) {
            $array = $errorCodeModel->geterrorcode('99994');
            exit(json_encode($array));
        }
        foreach ($required as $key => $val) {
            if (!isset($httpParams[$val]) || $httpParams[$val] == '' || $httpParams[$val] == NULL) {
                $msg = $val . '不能为空';
                $array = $errorCodeModel->geterrorcode('99994', $msg);
                exit(json_encode($array));
            }
        }
    }
}
