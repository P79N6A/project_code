<?php
namespace app\modules\api\controllers\controllers311;

use Yii;
use app\modules\api\common\ApiController;
use app\models\news\Function_control;
use app\commonapi\Logger;

class PaymethodController extends ApiController{

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $version = Yii::$app->request->post('version');
        $label = 1;
        if(!empty(Yii::$app->request->post('type'))){
            $label = Yii::$app->request->post('type');
        }
        if (empty($version)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }
        if($label == 1){
            $alipay_request_url = "/new/alipay";
        }else{
            $alipay_request_url = "/new/loan";
        }
        $function_control_model = new Function_control();
        $wechatpay_info = $function_control_model->find()->where(['type'=>1,'system'=>2,'label'=>$label])->orderBy("id desc")->one();
        $alipay_info = $function_control_model->find()->where(['type'=>2,'system'=>2,'label'=>$label])->orderBy("id desc")->one();
        $offline_info = $function_control_model->find()->where(['type'=>4,'system'=>2,'label'=>$label])->orderBy("id desc")->one();

        $payArray = array (
            array (
                "is_open" => (!empty($wechatpay_info) && $wechatpay_info['status'] == 1) ? 1 : 2,//1:开启，2：关闭
                "is_support" => 1,//1:支持，2：不支持
                "repayment_type" => "wechatpay",
                "request_url" => ""
            ),
            array (
                "is_open" => (!empty($alipay_info) && $alipay_info['status'] == 1) ? 1 : 2,//1:开启，2:关闭
                "is_support" => 1,//1:支持，2：不支持
                "repayment_type" => "alipay",
                "request_url" => $alipay_request_url
            ),
            array (//线下还款
                "is_open" => (!empty($offline_info) && $offline_info['status'] == 1) ? 1 : 2,//1:开启，2：关闭
                "is_support" => 1,//1:支持，2：不支持
                "repayment_type" => "offline",
                "request_url" => ""
            ),
        );

        if (!empty($payArray)) {
            $array['repayment_type_list'] = $payArray;
            $array = $this->returnBack('0000', $array);
            echo $array;
            exit;
        } else {
            $array = $this->returnBack('99999');
            echo $array;
            exit;
        }
    }

}