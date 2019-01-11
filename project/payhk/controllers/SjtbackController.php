<?php
/**
 * 数聚魔盒接口回调
 */

namespace app\controllers;

use app\common\Logger;
use app\modules\api\common\ApiController;
use app\modules\api\common\sjt\ClientSjt;
use app\models\JxlStat;
use app\models\SjtRequest;
use Yii;
use yii\helpers\ArrayHelper;
use app\modules\api\common\sjt\SjtNotify;
class SjtbackController extends ApiController {

    public function init() {
        //parent::init(); 千万不要执行父类的验证方法
    }
    public function actionNotify(){  
        $postdata = $this->post();    
        Logger::dayLog('sjt/sjtback','回调数据',$postdata);
        if(empty($postdata)) return false;
        $notify_event = ArrayHelper::getValue($postdata,'notify_event');
        $notify_data = ArrayHelper::getValue($postdata,'notify_data');
        $notify_data = json_decode($notify_data,true);
        $user_mobile = ArrayHelper::getValue($notify_data,'data.user_mobile','');
        $task_id = ArrayHelper::getValue($notify_data,'task_id','');
        $code = ArrayHelper::getValue($notify_data,'code','');
        $message = ArrayHelper::getValue($notify_data,'message','');
        if(empty($user_mobile) || empty($task_id)){
            return false;
        }
        $where = [
            'phone'     =>$user_mobile,
            'task_id'   =>$task_id
        ];
        $oSjt = (new SjtRequest)->getSjtDataByCondition($where);
        if(empty($oSjt)){
            Logger::dayLog('sjt/sjtback','getSjtDataByCondition','查询不到数据',$where,$postdata);
            return false;
        }
        if($notify_event=='SUCCESS'){
            //调用查询接口获取数据
            $res = (new ClientSjt)->taskQuery($oSjt);
            if(!$res){
                Logger::dayLog('sjt/sjtback','taskQuery','任务查询 处理详单数据出错',$where,$postdata);
                return false;
            }
        }else{
            //失败
            $res = $oSjt->saveReportFailure($code,$message);
            //post异步通知
			$sjtNotify = new SjtNotify($oSjt);
			$sjtNotify->clientNotify();
            if(!$res){
                Logger::dayLog('sjt/sjtback','saveReportFailure','任务失败',$where,$postdata);
                return false;
            }
        }
        return json_encode(['code'=>0,'message'=>'success']);
    }

}
