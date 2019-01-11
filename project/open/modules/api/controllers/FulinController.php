<?php
/**
 *
 */
namespace app\modules\api\controllers;

use app\common\Logger;
use app\modules\api\common\ApiController;
use app\modules\api\common\fulin\FulinApi;
use app\models\fulin\Fulin;
use yii\helpers\ArrayHelper;
use app\common\Func;
use YII;

set_time_limit(0);

class FulinController extends ApiController{

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
            'idCardno' => ArrayHelper::getValue($value,'idCardno'),
            'timestamp' => time(),

        );
        if (empty($postdata['name']) || empty($postdata['idCardno']) || empty($postdata['mobile'])) {
            Logger::dayLog('fulin','Request/error','请求数据不全：',$postdata);
            return false;
        }
        $idCardno = ArrayHelper::getValue($value,'idCardno');
        $oFulin = new Fulin();
        $rest =$oFulin->isRepeatQuery($idCardno);//根据身份证查询 此用户是否请求过

        if($rest != 'true'){
            $rel = json_decode($rest,true);
            return $this->resp(0,$rel);
        }
        $oFu = new FulinApi();
        $data = $oFu->sendFormat($postdata);
        $req = $oFulin->saveData($data);//请求数据写入表
        $res = $oFu->createResport($postdata);//请求第三方接口
        $result = $oFulin->oneSave($req,$res);
        return $this->resp(0,$res);


    }



}
    ?>