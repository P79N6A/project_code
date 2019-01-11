<?php

namespace app\modules\newdev\controllers;

use app\models\news\Juxinli;
use app\models\news\User;
use app\common\ApiClientCrypt;
use app\commonapi\Logger;
use Yii;

class NotifymobileController extends NewdevController{
    public $enableCsrfValidation = false;

    public function behaviors() {
        return [];
    }


    public function actionWebcallback(){
        $http = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $data_type = '';
        if($this->post()){
            $data_type = 'POST';
            $data = $this->post();
            $result = (new ApiClientCrypt)->parseResponse(json_encode($data));
            Logger::errorLog(print_r([$http,$data,$result], true), 'callbackPost', 'Mobile');
        }
        if($data_type != 'POST' && $this->get()){
            $data_type = 'GET';
            $data = $this->get();
            $result = (new ApiClientCrypt)->parseResponse(json_encode($data));
            Logger::errorLog(print_r([$http,$data,$result], true), 'callbackGet', 'Mobile');
        }
        if((!isset($result['res_code']) || $result['res_code'] != '0') && $data_type == 'GET'){
            exit('网络错误');
        }
        $userinfo = (new User())->find()->where(["mobile"=>$result['res_data']['phone']])->one();
        if(empty($userinfo)){
            exit('用户不存在');
        }
        $juxinli = (new Juxinli())->find()->where(["user_id" => $userinfo->user_id,'type'=>1])->one();
        if($result['res_data']['from'] == 1 && $data_type == 'GET'){
            $nextPages = '/borrow/userinfo/requireinfo';
        }else{
            $nextPages = null;
        }

        if($juxinli){
            $now_time = date('Y-m-d H:i:s');
            $end_time = date("Y-m-d H:i:s", strtotime('+ 4 month', strtotime($juxinli->last_modify_time)));
            $juxinli->refresh();
            if($juxinli->process_code == '10008' && $now_time <= $end_time && $data_type == 'GET'){
                $this->nextStap($nextPages, $result, $data_type);
            }
        }
        $res = $this->addJuxinli($result, $userinfo);
        if($res){
            $this->nextStap($nextPages, $result, $data_type);
        }
    }

    /**
     * 添加聚信立数据
     * @param $result
     * @param $userinfo
     * @return bool|string
     */
    private function addJuxinli($result, $userinfo){
        $condition = [
            'requestid' => $result['res_data']['requestid'],
            'user_id' => $userinfo->user_id,
            'source' => $result['res_data']['source'],
            'type' => 1,
        ];
        if($result['res_data']['status'] == 1 || $result['res_data']['status'] == 4){//采集成功 || 拉取中
            $condition['process_code'] = '10008';
            $condition['status'] = '1';
        }else{
            $condition[ 'process_code'] = '30000';
        }

        return (new Juxinli())->save_juxinli($condition);
    }

    /**
     * 执行结束，跳转
     * @param $nextPages
     * @param $result
     * @param $data_type
     * @return bool
     */
    private function nextStap($nextPages, $result, $data_type = 'GET'){
        if($data_type == 'POST'){
            echo 'SUCCESS';
            exit;
        }
//        if($result['res_data']['status'] != 1){
//            $this->redirect(Yii::$app->params['webcallback']);
//        }
        if($result['res_data']['from'] == 1){//web
            $this->redirect($nextPages);
        }
    }

}
