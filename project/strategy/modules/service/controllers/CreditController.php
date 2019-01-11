<?php 

namespace app\modules\service\controllers;

use Yii;
use yii\web\Controller;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\models\service\DcServicePhone;
use app\modules\service\logic\CreditLogic;
class CreditController extends ApiController 
{
    private $times_up = 10000;
    public function init()
    {
        parent::init();
        $content_type = ArrayHelper::getValue($_SERVER,'CONTENT_TYPE','');
        if (strpos($content_type,'urlencoded') || strpos($content_type,'json')) {
            $request_data = file_get_contents('php://input');
            $request_data = json_decode($request_data,true);
        } else {
            $request_data = $this->post();
        }
        Logger::dayLog('service/init', 'postdata', $request_data, $content_type);
        $auth_code = $this->chkAuth($request_data);
    }

    public function actionIndex() {
        echo "access forbiden"; 
    }

    // 授信接口
    public function actionChkrisk()
    {
        $postdata = $this->postdata;
        if (!is_array($postdata) || empty($postdata)) {
            Logger::dayLog('service/chkrisk', 'postdata', '数据异常', json_encode($postdata));
            return $this->returnMsg('X000003');
        }
        $relation = ArrayHelper::getValue($postdata,'relation','');
        if (empty($postdata['mobile']) || empty($postdata['identity']) || is_array($relation)) {
            Logger::dayLog('service/chkrisk', 'postdata', '手机号为空', json_encode($postdata));
            return $this->returnMsg('X000003');
        }
        $oCreditLogic = new CreditLogic();
        $chkphone_res = $oCreditLogic->CheckRisk($postdata);
        if (!$chkphone_res) {
            return $this->returnMsg($oCreditLogic->info);
        }
        return $this->returnMsg('0000',$oCreditLogic->info);
    }
}