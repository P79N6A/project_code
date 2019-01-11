<?php

namespace app\modules\newdev\controllers;

use app\models\news\App;
use Yii;
use yii\web\Controller;
use app\models\news\Statistics;

class DsController extends NewdevController {

     public $layout = 'appdown';
    public $enableCsrfValidation = false;
    
    public function behaviors()
    {
        return [];
    }
    public function actionDown(){
    	$this->getView()->title = '先花一亿元app下载';
    	$sql = "select * from yi_app_version ORDER BY id desc";
    	$model = Yii::$app->db->createCommand($sql)->queryOne();
    	return $this->render('down',array(
    			'downloan_url' => $model['download_url'],
    	));
    }

    public function actionDownnew(){
        $this->getView()->title = '先花一亿元app下载';
        $type = $this->get('type',954);
        $down_type = $this->get('down_type',970);
        $agent = isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'';
        $system = "Android";
        if(!empty($agent)){
            $system = $this->getDevice($agent);
        }
        $appUrlModel = new App();
        $appUrl = $appUrlModel->getAppUrl();
        return $this->render('downnew',array(
            'system' => $system,
            'type'  => $type,
            'down_type'  => $down_type,
            'download_url' => $appUrl['download_url'],
        ));
    }

    private function getDevice($agent){
        if(true == preg_match("/.+iPad.+/", $agent)){
            return "iPad";
        }elseif(true == preg_match("/.+iPhone.+/", $agent)){
            return "iPhone";
        }elseif(true == preg_match("/.+Android.+/", $agent)){
            return "Android";
        }
    }
}
