<?php 

namespace app\modules\service\controllers;

use Yii;
use yii\web\Controller;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\models\service\DcServicePhone;
use app\modules\service\logic\ChkPhoneLogic;
class ChkPhoneController extends ApiController 
{
    private $times_up = 10000;
    private $postdata;
    public function init()
    {
        parent::init();
        $content_type = $_SERVER["CONTENT_TYPE"];
        if (strpos($content_type,'urlencoded') || strpos($content_type,'json')) {
            $post_json = file_get_contents('php://input');
        } else {
            $post_json = $this->post();
        }
        Logger::dayLog('service/init', 'postdata', $post_json, $content_type);
        if (empty($post_json)) {
            return $this->returnMsg('X000003');
        }
        $this->postdata = json_decode($post_json,true);
        $auth_code = $this->chkAuth($this->postdata);
        if ($auth_code !== '0000') {
            return $this->returnMsg($auth_code);
        }
        //当天请求次数验证
        $times_check = $this->checkTimes();
        Logger::dayLog('service/init', 'postdata', $times_check);
        if (!$times_check) {
            return $this->returnMsg('T000001');
        }
    }

    public function actionIndex() {
        echo "access forbiden"; 
    }

    // 验证黑名单手机号（单个）
    public function actionChkone()
    {
        $postdata = $this->postdata;
        if (!is_array($postdata) || empty($postdata)) {
            Logger::dayLog('postdata', 'postdata', '数据异常', json_encode($postdata));
            return $this->returnMsg('X000003');
        }
        if (!isset($postdata['phone'])) {
            Logger::dayLog('postdata', 'postdata', '手机号为空', json_encode($postdata));
            return $this->returnMsg('P000003');
        }
        $oChkPhonelogic = new ChkPhoneLogic();
        $chkphone_res = $oChkPhonelogic->ChkPhoneOne($postdata);
        return $this->returnMsg('0000',$chkphone_res);
    }

    // 验证黑名单手机号（批量）
    public function actionChkbatch()
    {
        $postdata = $this->postdata;
        if (!is_array($postdata) || empty($postdata)) {
            Logger::dayLog('postdata', 'postdata', '数据异常', json_encode($postdata));
            return $this->returnMsg('X000003');
        }
        if (!isset($postdata['phone_list']) || !is_array($postdata['phone_list'])) {
            Logger::dayLog('postdata', 'postdata', '手机号集合异常', json_encode($postdata));
            return $this->returnMsg('P000033');
        }
        $oChkPhonelogic = new ChkPhoneLogic();
        $chkphone_res = $oChkPhonelogic->ChkPhoneBatch($postdata);
        return $this->returnMsg('0000',$chkphone_res);
    }

    private function checkTimes() {
        $where = ['>=','last_query_time',date('Y-m-d')];
        $oDcServicePhone = new DcServicePhone();
        $times = $oDcServicePhone->getCount($where);
        Logger::dayLog('service/init', 'postdata', $times);
        $chk_res = true;
        if ($times >= $this->times_up) {
            $chk_res = false;
        }
        return $chk_res;
    }
}