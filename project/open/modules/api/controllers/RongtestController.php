<?php
/**
 * 融360接口
 * @author zhangfei
 */
namespace app\modules\api\controllers;

use app\common\Logger;
use yii;
use app\models\JxlRequestModel;
use app\models\JxlStat;
use app\modules\api\common\ApiController;
use app\modules\api\common\rong\RongApi;
use app\modules\api\common\rong\Rsa;

class RongtestController extends ApiController {

    private $rapi;
    private $rras;
    private $logobj;
    //private $reqData;

    public function init() {
//        parent::init();
        $env = YII_ENV_DEV ? 'dev' : 'prod';
        $this->rapi = new RongApi($env);
        $this->logobj  = new Logger();
        //$this->reqData = array('cellphone' => '13581524051','password' => '82838511','user_id' => '1');//短信验证
        //$this->reqData = array('cellphone' => '13439660605','password' => '860505','user_id' => '2');//短信验证
//        $this->reqData = array('cellphone' => '13811792146','password' => 'cl881107','user_id' => '3');
        //$this->reqData = array('cellphone' => '18600220919','password' => '881107','user_id' => '4');//不需要验证
//        $this->reqData = Yii::$app->request->post();
        $this->reqData = $this->post();
        //$this->reqData = array('cellphone' => '15093560261','password' => '900704','user_id' => '5');//图片验证
    }

    public function actionIndex() {
        echo 'ceshi';exit;
    }
/*--------------API------------start------*/
    public function actionLoginrule() {


        //获取基本信息
        $res = $this->rapi->operatorSend($this->reqData, 'crawler.api.mobile.v4.getLoginRule');
        if (!is_array($res)) {
            $this->resp(8004, "异常");
        }elseif($res['error'] != 200){
            return $this->resp($res['error'] ,$res['msg']);
        }else{
            //运营商请求登陆
            $res = $this->actionUserlogin();

            $callData = $this->getResParam($res,'login');
            if(!is_array($callData)){
                $this->resp(8004, "数据异常");
            }else {
                if ($callData['flag'] == 1) {
                    $this->actionUserdata();
                } else {
                    $this->resp(0, $callData);
                }
            }

        }

    }

    public  function actionUserlogin(){//运营商登陆信息 已发送短信
        $method = 'login';
        /*$bizData = array(
            'cellphone' => $this->reqData['cellphone'],
            'password' => $this->reqData['password'],
            'user_id' => $this->reqData['user_id']
        );*/
        $res = $this->rapi->operatorSend($this->reqData, 'crawler.api.mobile.v4.'.$method);

        if (!is_array($res)) {
            return $this->resp(8004, "异常");
        }elseif($res['error'] != 200){
            return $this->resp($res['error'] ,$res['msg']);
        }else{
//            print_r($res);
            return $res;
            //$this->resp(0, $res);
        }

    }
    public function actionPublicdata(){//第二：获取next接口信息
        $method = $this->reqData['method'];
        if ($method == '') {
            $this->resp(8004, "DATA不能为空");
        }else{
            $res = $this->rapi->operatorSend($this->reqData, 'crawler.api.mobile.v4.'.$method);
            if (!is_array($res)) {
                $this->resp(8004, "异常");
            }elseif($res['error'] != 200){
                return $this->resp($res['error'] ,$res['msg']);
            }else{
                $callData = $this->getResParam($res,$method);
                if(!is_array($callData)){
                    $this->resp(8004, "数据异常");
                }else {
                    if ($callData['flag'] == 1) {
                        $this->actionUserdata();
                    } else {
                        $this->resp(0, $callData);
                    }
                }
            }
        }

    }

    public  function actionUserdata(){//抓取用户数据
//        $bizData = array(
//            'user_id' => $this->reqData['user_id']
//        );
        $res = $this->rapi->operatorSend($this->reqData, 'wd.api.mobilephone.getdata');
        print_r($res);exit();
        if (!is_array($res)) {
            $this->resp(8004, "异常请重试");
        }elseif($res['error'] != 200){
            return $this->resp($res['error'] ,$res['msg']);
        }else{//抓取数据成功
            $this->rapi->writeLog($this->reqData['user_id'],json_encode($res));//记录日志
//            $this->logobj->errorLog(json_encode($res),'log','open360');
//            print_r($res);
            $this->resp(0, $res);
//            return $res;
        }

    }

    public  function actionRpiccode(){//刷新图片验证码
        $method = 'refreshPicCode';
//        $bizData = array(
//            'cellphone' => $this->reqData['cellphone'],
//            'piccode_type' => 2,
//            'user_id' => $this->reqData['user_id']
//        );

        $res = $this->rapi->operatorSend($this->reqData, 'crawler.api.mobile.v4.'.$method);

        if (!is_array($res)) {
            $this->resp(8004, "异常");
        }elseif($res['error'] != 200){
            return $this->resp($res['error'] ,$res['msg']);
        }else{
//            print_r($res);
            $this->resp(0, $res);
        }

    }

    public  function actionRmsgcode(){//刷新短信验证码
        $method = 'refreshMessageCode';
//        $bizData = array(
//            'cellphone' => $this->reqData['cellphone'],
//            'messagecode_type' => 1,
//            'user_id' => $this->reqData['user_id']
//        );

        $res = $this->rapi->operatorSend($this->reqData, 'crawler.api.mobile.v4.'.$method);

        if (!is_array($res)) {
            $this->resp(8004, "异常");
        }elseif($res['error'] != 200){
            return $this->resp($res['error'] ,$res['msg']);
        }else{
            $this->resp(0, $res);
        }

    }

    public  function actionQuerystae(){
//        $bizData = array(
//            'status_id' => $this->reqData['status_id'],
//        );

        $res = $this->rapi->operatorSend($this->reqData, 'wd.api.crawler.queryStatus');

        if (!is_array($res)) {
            $this->resp(8004, "异常");
        }elseif($res['error'] != 200){
            return $this->resp($res['error'] ,$res['msg']);
        }else{
            $this->resp(0, $res);
        }

    }


    public function actionReport(){//获取运营商报告
        $method = 'tianji.api.tianjireport.collectuser';
        $bizData = array(
            'type' => 'mobile',
            'platform' => 'api',
            'phone' => $this->reqData['phone'],
            'name' => $this->reqData['name'],
            'idNumber' => $this->reqData['idNumber'],
            'userId' => $this->reqData['user_id'],
            'notifyUrl' => 'http://182.92.80.211:8091/api/rongback/callback',
            'outUniqueId' => $this->reqData['outUniqueId'],
            'version' => '2.0'

        );
        $res = $this->rapi->operatorSend($bizData, $method);
        if (!is_array($res)) {
            $this->resp(8004, "异常");
        }elseif($res['error'] != 200){
            return $this->resp($res['error'] ,$res['msg']);
        }else{
            $this->resp(0, $res);
        }
    }

    public function actionDetail(){//获取运营商报告详情
        $method = 'tianji.api.tianjireport.detail';
        $bizData = array(
            'userId' => $this->reqData['user_id'],
            'outUniqueId' => $this->reqData['outUniqueId'],
            'reportType' => 'html'

        );
        $res = $this->rapi->operatorSend($bizData, $method);
        if (!is_array($res)) {
            $this->resp(8004, "异常");
        }elseif($res['error'] != 200){
            return $this->resp($res['error'] ,$res['msg']);
        }else{
            $this->rapi->writeLog($this->reqData['user_id'].'_detail',json_encode($res));
            print_r($res);exit;
//            $this->resp(0, $res);
        }
    }



// flog=1  登录验证完成直接拉取数据  0 未完成还需要next请求
    public function getResParam($res=array(),$method=''){//获取next参数
        $callDate = array();
        if(!is_array($res) && empty($res) && $method == ''){
            $this->resp(1001, "返回数据为空");
        }else{
            $mName = $this->rapi->getApiName($method);
            if(array_key_exists('next',$res[$mName])){//有next-method
                $callDate['method'] = $res[$mName]['next']['method'];

                if(array_key_exists('param',$res[$mName]['next'])){//获取param的参数
                    foreach($res[$mName]['next']['param'] as $key=>$val){
                        $callDate[$val['key']] = $val['value'];
                    }
                }

                if(array_key_exists('hidden',$res[$mName]['next'])){//获取hidden的参数
                    foreach($res[$mName]['next']['hidden'] as $key=>$val){
                        $callDate[$val['key']] = $val['value'];
                    }
                }
                $callDate['flag'] = 0;

            }else{
                $callDate['flag'] = 1;
            }
            return $callDate;
        }
    }







/*----------------------end--------------*/
    /* public function actionIndex() {
         //1.获取基本信息

         $bizData = array(
             'cellphone' => $this->reqData['cellphone'],
             'user_id' => $this->reqData['user_id']
         );
         $res = $this->rapi->operatorSend($this->reqData, 'crawler.api.mobile.v4.getLoginRule');

         //$callData = $this->getResParam($res,'getLoginRule');
 //        print_r($res);
 //        print_r($callData);exit;
         if (!is_array($res)) {
             $this->resp(8004, "异常");
         }elseif($res['error'] != 200){
             return $this->resp($res['error'] ,$res['msg']);
         }else{
             //2.运营商登陆信息

             $res = $this->actionUserlogin();
             $callData = $this->getResParam($res,'login');
             if(empty($callData)){
                 $this->resp(8004, "数据异常");
             }else{
                 if($callData['flag'] == 1){//无next直接拉取数据
                     $this->actionUserdata();
                 }else{//执行next
 //                    print_r($res);
 //                    print_r($callData);
                     $is_pic = array_key_exists('pic_code',$callData);
                     $is_msg = array_key_exists('message_code',$callData);
                     if($is_msg && !$is_pic){//发送短信验证
                         $callData['img_login'] = false;
                         $callData['msg_login'] = true;
                         print_r($callData);exit;
                     }
                     if(!$is_msg && $is_pic){//发送图片验证
                         $callData['img_login'] = true;
                         $callData['msg_login'] = false;
                         print_r($callData);exit;
                     }
                     if($is_msg && $is_pic){//发送图片+短信
                         $callData['img_login'] = true;
                         $callData['msg_login'] = true;
                         print_r($callData);exit;
                     }


                 }
             }

         }
         if (!is_array($res)) {
             $this->resp(8004, "异常");
         }elseif($res['error'] != 200){
             return $this->resp($res['error'] ,$res['msg']);
         }else{
             //3.运营商授权登陆
             $mName = $this->rapi->getApiName('login');
             if(!array_key_exists('status_id',$res[$mName])){//需要登陆验证的
                 echo 'bbbbbbbb';
             }else{//不需要登陆验证  直接成功
                 //4.拉取运营商数据
                 $bizData = array(
                     'user_id' => $this->reqData['user_id']
                 );
                 $res = $this->rapi->operatorSend($bizData, 'wd.api.mobilephone.getdata');
                 if (!is_array($res)) {
                     $this->resp(8004, "异常");
                 }elseif($res['error'] != 200){
                     return $this->resp($res['error'] ,$res['msg']);
                 }else{//抓取数据成功
                     $content = date("Y-m-d H:i:s").'|'.json_encode($res);
                     $this->logobj->errorLog($content,'log','open360');
                     echo json_encode($res);
                     //print_r($res);

                 }
             }
         }






 exit;
         return $this->resp(0, [
             'cardno' => 1,
             'idcard' => 2,
             'username' => 3,
             'phone' => 4,
             'status' => true,
             'type' => 'tx',
         ]);
     }

     public function actionChecklogin(){//校验login
         $is_img = $this->reqData['img_login'];
         $is_msg = $this->reqData['msg_login'];
         if($is_msg && !$is_img){

             $this->getPublicData($this->reqData['method']);
         }
     }




     public  function actionSendmsg(){//接收短信验证码
         $method = 'login';
         $bizData = array(
             'cellphone' => $this->reqData['cellphone'],
             'password' => $this->reqData['password'],
             'message_code'=> '194773',//短信验证码
             'user_id' => $this->reqData['user_id']
         );

         $res = $this->rapi->operatorSend($bizData, 'crawler.api.mobile.v4.'.$method);
         if (!is_array($res)) {
             $this->resp(8004, "异常");
         }elseif($res['error'] != 200){
             return $this->resp($res['error'] ,$res['msg']);
         }else{
             print_r($res);
         }

     }

     public  function actionPicmsg(){//接收图片验证码
         $method = 'submitMessageCode';
         $bizData = array(
             'cellphone' => $this->reqData['cellphone'],
             'message_code'=> '148238',//短信验证码
             //'pic_code'=> 'amd8rc',//短信验证码
             'user_id' => $this->reqData['user_id']
         );

         $res = $this->rapi->operatorSend($bizData, 'crawler.api.mobile.v4.'.$method);

         if (!is_array($res)) {
             $this->resp(8004, "异常");
         }elseif($res['error'] != 200){
             return $this->resp($res['error'] ,$res['msg']);
         }else{
             print_r($res);
         }

     }

     public  function actionRpiccode(){//刷新图片验证码
         $method = 'refreshPicCode';
         $bizData = array(
             'cellphone' => $this->reqData['cellphone'],
             'piccode_type' => 2,
             'user_id' => $this->reqData['user_id']
         );

         $res = $this->rapi->operatorSend($bizData, 'crawler.api.mobile.v4.'.$method);

         if (!is_array($res)) {
             $this->resp(8004, "异常");
         }elseif($res['error'] != 200){
             return $this->resp($res['error'] ,$res['msg']);
         }else{
 //            print_r($res);
             $this->resp(0, $res);
         }

     }

     public  function actionRmsgcode(){//刷新短信验证码
         $method = 'refreshMessageCode';
         $bizData = array(
             'cellphone' => $this->reqData['cellphone'],
             'messagecode_type' => 1,
             'user_id' => $this->reqData['user_id']
         );

         $res = $this->rapi->operatorSend($bizData, 'crawler.api.mobile.v4.'.$method);

         if (!is_array($res)) {
             $this->resp(8004, "异常");
         }elseif($res['error'] != 200){
             return $this->resp($res['error'] ,$res['msg']);
         }else{
             print_r($res);
         }

     }


     public function getPublicData($method = '',$bizData=array()){//公共获取融360数据
         if (empty($bizData) && $method == '') {
             $this->resp(8004, "DATA不能为空");
         }else{
             if($bizData){

             }
             $res = $this->rapi->operatorSend($bizData, 'crawler.api.mobile.v4.'.$method);
             $callData = $this->getResParam($res,$method);
             return $callData;
         }
     }
 */

}
