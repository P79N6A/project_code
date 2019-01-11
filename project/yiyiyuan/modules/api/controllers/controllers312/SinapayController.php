<?php

namespace app\modules\api\controllers\controllers312;

use app\common\ApiClientCrypt;
use app\commonapi\Logger;
use app\models\news\Payaccount;
use app\models\news\User;
use app\models\news\User_loan;
use app\models\news\User_loan_extend;
use app\modules\api\common\ApiController;
use Yii;

class SinapayController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');
        $source = Yii::$app->request->post('source');

        if (empty($version) || empty($user_id) || empty($source)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }
        $user = User::findOne($user_id);
        if (empty($user)) {
            $array = $this->returnBack('10001');
            echo $array;
            exit;
        }
        $type = $source == 1 ? 'android' : 'ios';
        $url = Yii::$app->request->hostInfo . '/dev/loan/sinapaybackurl?type=' . $type;
        $postData = [
            'user_id' => $user_id,
            'passwordurl' => $url, // 回调
            'op' => 'set_pay_password', //设置:set_pay_password | 修改:modify_pay_password | 找回 find_pay_password
        ];

        $openApi = new ApiClientCrypt;
        $res = $openApi->sent('sinapay/paypassword', $postData);
        $result = $openApi->parseResponse($res); //''
//        print_r($result);exit;
        Logger::errorLog(print_r($result, true), 'sinajihuo', 'api');
        if ($result['res_code'] == 0) {
            $array['redirect_url'] = $result['res_data']['redirect_url'];
            $array = $this->returnBack('0000', $array);
            echo $array;
            exit;
        } elseif ($result['res_code'] == 150104) {
            $payStatus = new Payaccount();
            $condition = array(
                'user_id' => $user_id,
                'type' => 1,
                'step' => 2,
                'activate_result' => 1,
            );
            $result = $payStatus->addList($condition);
            $loan = User_loan::find()->where(['user_id' => $user_id, 'status' => 6])->one();
            $user_extend = User_loan_extend::find()->where(['loan_id' => $loan->loan_id])->one();
            if (!empty($user_extend)) {
                $user_extend->updateUserLoanSubsidiary(array('outmoney' => 1));
            } else {
                (new User_loan_extend())->addList(array('user_id' => $loan->user_id, 'loan_id' => $loan->loan_id, 'outmoney' => 1, 'payment_channel' => 1));
            }
            $array = $this->returnBack('10078');
            echo $array;
            exit;
        } else {
            $array = $this->returnBack('10077');
            echo $array;
            exit;
        }
    }
}
