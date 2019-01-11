<?php
/**
 * 蚁盾-上数接口回调
 * @author zhangfei
 */

namespace app\modules\api\controllers;

use app\common\Logger;
use app\modules\api\common\ApiController;
use app\models\JxlRequestModel;
use app\modules\api\common\yidun\ClientYd;
use app\modules\api\common\rong\RongApi;
use app\models\JxlStat;
use app\models\YidunRequest;
use Yii;


class YidunController extends ApiController {
    private $ydapi;
    private $rapi;
    /**
     * 服务id号
     */
    protected $server_id = 8;

    public function init() {
        //parent::init();
        $env = YII_ENV_DEV ? 'dev' : 'prod';
        $this->ydapi = new ClientYd($env);
        $this->rapi = new RongApi($env);
    }

    public function actionJson(){
        $path = '/ofiles/jxl/201706/12/568_all.json';
        // $path = '/ofiles/jxl/201705/16/jxl.json';
        $res = $this->ydapi->changeJsonDataFormat($path,558);//yidun

        // $jsonPath = Yii::$app->request->hostInfo.$path;
        // $jsonString = file_get_contents($jsonPath);
        // $res = $this->rapi->changeJxlJson($jsonString);//rong
        echo $res;exit;

    }


    public function actionIndex() {
        $bizo = '201706091496975293594a8eb5f94eba';
        $request = new YidunRequest();
        $request = $request->getOneRequest(618);
        $request->website = 'OPERATOR_LIANTONGBEIJING';
        $res = $request->save();
        var_dump($request);exit;
        $res = $this->ydapi->getAccessToken();
    }

    public function actionLogin(){
        $data = $this->reqData;
        $this->ydapi->submitUserInfo($data);
        

    }

}
