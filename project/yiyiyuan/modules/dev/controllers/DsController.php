<?php

namespace app\modules\dev\controllers;

use Yii;
use yii\web\Controller;
use app\models\dev\Statistics;

class DsController extends Controller {

    public $layout = 'appdown';
    public $enableCsrfValidation = false;

    public function actionIndex() {
        $this->getView()->title = '先花一亿元app下载';
        $sql = "select * from yi_app_version ORDER BY id desc";
        $model = Yii::$app->db->createCommand($sql)->queryOne();
        return $this->render('index',array(
            'downloan_url' => $model['download_url'],
        ));
    }
    
    public function actionDownload(){
    	$this->getView()->title = '先花一亿元app下载';
    	$sql = "select * from yi_app_version ORDER BY id desc";
    	$model = Yii::$app->db->createCommand($sql)->queryOne();
    	return $this->render('download',array(
    			'downloan_url' => $model['download_url'],
    	));
    }
    
    public function actionDown(){
    	$this->getView()->title = '先花一亿元app下载';
    	$sql = "select * from yi_app_version ORDER BY id desc";
    	$model = Yii::$app->db->createCommand($sql)->queryOne();
    	return $this->render('down',array(
    			'downloan_url' => $model['download_url'],
    	));
    }
    
    //H5页面跳转地址
    public function actionFromh5(){
    	$type = isset($_GET['type']) ? $_GET['type'] : 'android';
    	if($type == 'ios'){
    		$info = $_SERVER;
    		
    		$model = new Statistics();
    		$type = 37;
    		$model->user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    		$model->loan_id = isset($_GET['loan_id']) ? intval($_GET['loan_id']) : 0;
    		$model->from = 'h5';
    		$model->remoteip = isset($info['HTTP_REMOTEIP'])?$info['HTTP_REMOTEIP']:0;
    		$model->user_agent = $info['HTTP_USER_AGENT'];
    		$model->create_time = date('Y-m-d H:i:s');
    		$model->type = $type;
    		 
    		$model->save();
    		
    		$url = "https://itunes.apple.com/cn/app/xian-hua-yi-yi-yuan/id986683563";
    		$appid = 'wx476bb3649401c450';
    		$redirect_url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appid . "&redirect_uri=" .$url. "&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect";
    		header("Location:$redirect_url");
    	}else{
    		$info = $_SERVER;
    		
    		$model = new Statistics();
    		$type = 38;
    		$model->user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    		$model->loan_id = isset($_GET['loan_id']) ? intval($_GET['loan_id']) : 0;
    		$model->from = 'h5';
    		$model->remoteip = isset($info['HTTP_REMOTEIP'])?$info['HTTP_REMOTEIP']:0;
    		$model->user_agent = $info['HTTP_USER_AGENT'];
    		$model->create_time = date('Y-m-d H:i:s');
    		$model->type = $type;
    		 
    		$model->save();
    		
    		$redirect_url = "http://a.app.qq.com/o/simple.jsp?pkgname=com.xianhuahua.yiyiyuan_1";
    		header("Location:$redirect_url");
    	}
    }
    public function actionAndroiddown(){
        $sql = "select * from yi_app_version ORDER BY id desc";
        $model = Yii::$app->db->createCommand($sql)->queryOne();
        return $model['download_url'];
    }

}
