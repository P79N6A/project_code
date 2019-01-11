<?php
/**
 *
 */
namespace app\modules\api\controllers;

use app\common\Logger;
use app\modules\api\common\ApiController;
use app\modules\api\common\bair\BairongApi;
use app\models\bairong\Bairong;
use yii\helpers\ArrayHelper;
use app\common\Func;
use YII;

set_time_limit(0);

class BairController extends ApiController{

    protected $server_id = 105;
    #错误码107
    /*
     * * 错误码说明
       error_code [
            105001 => '请求参数不完整'，

        ]
   */
    private $obaiduhcapi;  //引入公共类

    public function init() {
        parent::init();
        $this->obaiduhcapi = new BairongApi();
    }
    /*
     *  查询信息信息
     *  @param   $postdata     接收数据
     *  @param   $user_id    用户id
     *

     * */
    public function actionRequest(){
        $value = $this->reqData;
        $postdata=array(
            'strategy_id' =>'STR0002136',//固定值查询的策略编号
            'name'      =>   ArrayHelper::getValue($value,'name'),
            'id'       =>   ArrayHelper::getValue($value,'idcard'),//身份证
            'cell'     =>   ArrayHelper::getValue($value,'cell'),

        );
        $idcard = ArrayHelper::getValue($value,'idcard');//身份证
        $cell  = ArrayHelper::getValue($value,'cell');
        //判断  值是否符合要求
        if(empty($postdata['name']) || empty($postdata['id']) || empty($postdata['cell'])){

            Logger::dayLog('bairong','Request/error','数据不全：',$postdata);
            return $this->resp(107001, array('reason'=>'参数信息不完整。'));
        }
        Logger::dayLog('bairong','Request/success','请求数据：',$postdata);
        $oRequest = new Bairong();
        $rest =$oRequest->isRepeatQuery($idcard,$cell);//根据身份证查询 此用户是否请求过
        if($rest!='true'){
            $rel = json_decode($rest,true);
            return $this->resp(0,$rel);
        }
        $obaiduhcapi =new BairongApi();
        $tokenid = $obaiduhcapi->getTokenid();
        $data = $obaiduhcapi->sendFormat($value);//格式数据
        $req = $oRequest->saveData($data);//请求数据写入表
        $rst = $obaiduhcapi->getResult($postdata,$tokenid);//获取请求结果
        $result = $oRequest->oneSave($req,$rst);
        if($result){
            $rel = json_decode($rst,true);
            return $this->resp(0,$rel);
        }
        return false;
    }


}
    ?>