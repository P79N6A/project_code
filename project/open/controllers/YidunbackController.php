<?php
/**
 * 蚁盾-上数接口回调
 * @author zhangfei
 */

namespace app\controllers;

use app\common\Logger;
use app\modules\api\common\ApiController;
use app\modules\api\common\yidun\ClientYd;
use app\models\JxlStat;
use app\models\YidunRequest;
use app\models\YidunPull;
use Yii;


class YidunbackController extends ApiController {

    private $backData;

    public function init() {
        //parent::init(); 千万不要执行父类的验证方法
        $this->backData = $this->post();
    }

    public function actionIndex() {
        
    }
    public function actionCallback(){
        $data = $this->backData;
        $logData = json_encode($data);
        Logger::dayLog('yidunback/notify', $logData);
        
        $bizNo = isset($this->backData['bizNo'])?$this->backData['bizNo']:'';
        $status = isset($this->backData['status'])?$this->backData['status']:'FAILED';
        Logger::dayLog('yidun', 'bizNo-status:'.$bizNo.'--'.$status);
        if($bizNo){
            if($status == 'FAILED'){
                $request = new YidunRequest();
                $request = $request->getOneUserInfo($bizNo);
                if($request){
                    $request->result_status = 2;//采集失败
                    $saveRes = $request->save();
                    if($saveRes){//保存成功通知业务端
                        $request->clientNotify();//post异步通知失败
                    }
                }
                $pull_status = 2;//失败
            }else{
                $pull_status = 0;//初始
            }
            $ydPull = new YidunPull();
            $data = date("Y-m-d H:i:s");
            $dataList = [
				'bizno' => $bizNo,
				'pull_status' => $pull_status,
				'create_time' => $data
		    ];
            $res = $ydPull->saveData($dataList);
            if(!$res){
                Logger::dayLog('yidunback/notify', 'SaveYidunPull  false');
            }
            echo "{'status': 'true'}";exit;
        }else{
            echo "{'status': 'false'}";exit;
        }

    }

}
