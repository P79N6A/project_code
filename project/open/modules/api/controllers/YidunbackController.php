<?php
/**
 * 蚁盾-上数接口回调
 * @author zhangfei
 */

namespace app\modules\api\controllers;

use app\common\Logger;
use app\modules\api\common\ApiController;
use app\modules\api\common\yidun\ClientYd;
use app\models\JxlStat;
use app\models\YidunRequest;
use Yii;


class YidunbackController extends ApiController {

    private $backData;
    private $ydapi;

    public function init() {
        //parent::init(); 千万不要执行父类的验证方法
        $env = YII_ENV_DEV ? 'dev' : 'prod';
        $this->ydapi = new ClientYd($env);
        $this->backData = $this->post();
    }

    public function actionIndex() {
        
    }
    public function actionCallback(){
        $data = $this->backData;
        $logData = json_encode($data);
        Logger::dayLog('yidunback/notify', $logData);
        if (empty($data) || !isset($this->backData['status']) || $this->backData['status']=='FAILED') {
            Logger::dayLog('yidunback/notify', ' failed !');
            echo "{'status': 'false'}";exit;
        }
        $bizNo = isset($this->backData['bizNo'])?$this->backData['bizNo']:'';
        Logger::dayLog('yidun', 'bizNo:'.$bizNo);
        $callbackInfo = $this->ydapi->callbackGetCollectionInfo($bizNo);
        $request = new YidunRequest();
        $request = $request->getOneUserInfo($bizNo);
        $jsonPath = $this->ydapi->writeLog($request->requestid.'_all', $callbackInfo);//存储报告json
        $changeJsonPath = $this->ydapi->changeJsonDataFormat($jsonPath,$request->requestid);//转换成聚信立格式的json
        $changeJsonPath = str_replace("_detail.json", ".json", $changeJsonPath);
        $request = $request->getOneRequest($request->requestid);//为了得到手机运营商
        $oJxlStat = new JxlStat();
        $statData = $oJxlStat->getByRequestid($request->requestid);//是否已存在
        if(!$statData){
            $postData = [
                'aid' => $request->aid,
                'requestid' => $request->requestid,
                'name' => $request->name,
                'idcard' => $request->idcard,
                'phone' => $request->phone,
                'website' => $request->website,
                'is_valid' => 3,
                'url' => $changeJsonPath,
                'source' => $request->source
            ];
            //5 保存到DB中
            $result = $oJxlStat->saveStat($postData);
            
        }else{
            $statData->url = $changeJsonPath;
            $result = $statData->save();
        }
        if (!$result) {
            Logger::dayLog('yidun', 'saveStat保存失败'.json_encode($postData));
            echo "{'status': 'false'}";exit;
        }
        $request->result_status = 1;
        $request->save();
        echo "{'status': 'true'}";exit;

    }

}
