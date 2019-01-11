<?php

namespace app\modules\api\controllers;

use app\common\Logger;
use app\modules\api\common\ApiController;
use app\modules\api\common\shenyue\ShenyueApi;
use app\models\shenyue\Shenyue;
use yii\helpers\ArrayHelper;
use app\common\Func;
use YII;

set_time_limit(0);

class ShenyueController extends ApiController{

    protected $server_id = 105;

    private $obaiduhcapi;  //引入公共类

    public function init() {
        parent::init();
    }
    /*
     *  查询信息信息
     *  @param   $postdata     接收数据
     *
     * */
    public function actionRequest(){

        $value = $this->reqData;
        $postdata=array(

            'name' => ArrayHelper::getValue($value,'name'),
            'mobile' => ArrayHelper::getValue($value,'mobile'),
            'idcard' => ArrayHelper::getValue($value,'idcard'),//身份证
            'loan_type' =>'3',//代表p2p
            'source' => ArrayHelper::getValue($value,'source'),//1.流量接口2.神月接口
            'user_id' => ArrayHelper::getValue($value,'user_id'),
            'loan_id' =>ArrayHelper::getValue($value,'loan_id'),
             'aid' =>ArrayHelper::getValue($value,'aid'),

        );
        if (empty($postdata['name']) || empty($postdata['idcard']) || empty($postdata['mobile']) || empty($postdata['source'])) {
            Logger::dayLog('shenyue','Request/error','请求数据不全：',$postdata);
            return $this->resp(105301,'参数不全');
            return false;
        }
        $source = ArrayHelper::getValue($value,'source');
        $idCardno = ArrayHelper::getValue($value,'idcard');
        $oShenyue = new Shenyue();
        $rest =$oShenyue->isRepeatQuery($idCardno,$source);//根据身份证查询 此用户是否请求过
        if($rest != 'true'){
            return $this->resp(0,$rest);
        }
        $oFu = new ShenyueApi();
        $data = $oFu->sendFormat($postdata);
        $res = $oFu->createResport($postdata);//请求第三方接口
        if(empty($res)){
            $result = [
                'data' => '请求失败请检查请求参数是否正确',
            ];
          return $this->resp(0,$result);
        }
        $req = $oShenyue->saveData($data);//请求数据写入表
        $jsonDta = json_encode($res);//数组转json写入文件
        $jsonFile =$oFu->saveJsonData($jsonDta,$req);
        if(!$jsonFile){//写入失败
            Logger::dayLog('shenyue','Request/error','写入json文件错误请求ID：',$req);
            return false;
        }
        $result = $oShenyue->oneSave($req,$jsonFile);
        return $this->resp(0,$jsonFile);


    }



}
    ?>