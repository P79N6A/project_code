<?php

/**
 * 百度磐石金融系统风险接口查询
 */

namespace app\controllers;

use app\common\Logger;
use Yii;
use app\modules\api\common\baidurisk\RiskApi;
use app\models\BdRisk;
use yii\helpers\ArrayHelper;
class BdriskController extends BaseController {
    public function init() {
        //parent::init(); 千万不要执行父类的验证方法
    }
    public function beforeAction($action) {
        if (in_array($action->id, ['index'])) {
            // 局部关闭csrf验证
            $action->controller->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }
    public function actionIndex() {
        //1 数据获取
        $postdata = Yii::$app->request->post();
        // $postdata = [
        //     'name'=>'刘鑫',
        //     'idcard'=>'371329198812012710',
        //     'phone'=>'18201510539'

        // ];
        Logger::dayLog('baidurisk', 'index',$postdata);
         // 无响应时不处理
        if (empty($postdata)) {
            return json_encode(['retCode'=>'-1','retMsg'=>'参数缺失'],JSON_UNESCAPED_UNICODE);
        }
        $oApi = new RiskApi;
        $model = new BdRisk();
        $bdrisk = $model->add($postdata);
        if(!$bdrisk){
            return json_encode(['retCode'=>'-1','retMsg'=>$model->errinfo],JSON_UNESCAPED_UNICODE);
        }
        $result = $oApi->sendRequest($postdata);
        $res = json_decode($result);
        $data['error_code'] = (string)ArrayHelper::getValue($res,'retCode','');
        $data['error_msg'] = ArrayHelper::getValue($res,'retMsg','');
        $data['black_level'] = ArrayHelper::getValue($res,'blackLevel','');
        $data['black_reason'] = ArrayHelper::getValue($res,'blackReason','');
        $data['black_detail'] = ArrayHelper::getValue($res,'blackDetail','');
        $rs = $bdrisk->updateRisk($data);
        if(!$rs){
            return json_encode(['retCode'=>'-1','retMsg'=>$model->errinfo],JSON_UNESCAPED_UNICODE);
        }
        //var_dump($result);
        return $result;
        
    }
}
