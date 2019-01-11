<?php

namespace app\modules\api\controllers\controllers310;

use Yii;
use app\models\news\Idfa;
use app\modules\api\common\ApiController;

/**
 * 微信公众号支付
 */
class IdfaController extends ApiController {

    public $enableCsrfValidation = false;

    /**
     * 查询是否存在idfa,没有则添加
     */
    public function actionIndex() {
        $idfa = Yii::$app->request->post('idfa');
        $idfa_model = new Idfa();
        if (empty($idfa)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }

        if(substr($idfa, 0, 7) == "00000000"){
            $array = $this->returnBack('10086');
            echo $array;
            exit;
        }
        $idfa_ino = Idfa::find()->where(['idfa' => $idfa])->one();
        if($idfa_ino){
            $array = $this->returnBack('10086');
            echo $array;
            exit;
        }else{
            $idfa_model->idfa = $idfa;
            $res = $idfa_model->save();
            if($res){
                $array = $this->returnBack('0000');
            } else {
                $array = $this->returnBack('10042');
            }
            echo $array;
            exit;
        }
    }

    /*
     * 查询是否存在idfa
     */

    public function actionCheckidfa() {
        $idfa = Yii::$app->request->get('idfa');
        if (empty($idfa)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }
        $idfa_info = Idfa::find()->where(['idfa' => $idfa])->one();
        if(empty($idfa_info)){
            $array = $this->returnBack('10087');
            echo $array;
            exit;
        }else{
            $array = $this->returnBack('10086');
            echo $array;
            exit;
        }
        
    }
}