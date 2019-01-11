<?php
namespace app\modules\api\controllers\controllers310;

use app\commonapi\Logger;
use app\models\news\Click_count;
use app\modules\api\common\ApiController;
use Yii;

class LabelcountController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $version = Yii::$app->request->post('version');
        $label = Yii::$app->request->post('label');

        if (empty($version) || empty($label)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }
        //ocr
        $condition = array("field"=>$label);
        $click_count_model = new Click_count();
        $click_count_result = $click_count_model->save_clickcount($condition);
        if($click_count_result){
            $array['rsp_msg'] = '成功';
        }else{
            $array['rsp_msg'] = '失败';
        }
        $array = $this->returnBack('0000', '', $array['rsp_msg']);
        echo $array;
        exit;
    }

}


