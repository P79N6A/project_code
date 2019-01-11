<?php
/**
 * 融360接口
 * @author zhangfei
 */
namespace app\modules\api\controllers;

use app\common\Crypt3Des;
use app\common\Logger;
use app\modules\api\common\juxinli\JxlRequest;
use yii;
use app\models\JxlRequestModel;
use app\models\JxlStat;
use app\modules\api\common\ApiController;
use app\modules\api\common\rong\RongApi;
use app\modules\api\common\rong\Rsa;

class RongController extends ApiController {

    private $rapi;
    private $rras;
    private $uid;
    private $outUniqueId;//报告ID
    /**
     * 服务id号
     */
    protected $server_id = 8;

    public function init() {
        parent::init();
        $env = YII_ENV_DEV ? 'dev' : 'prod';
        $this->rapi = new RongApi($env);
    }

    public function actionIndex() {
        echo 'ceshi';exit;
    }
/*--------------API------------start------*/
    public function actionLoginrule() {

        $data = $this->reqData;
        $idcard = $data['idcard'];
        $callbackurl = isset($data['callbackurl'])?$data['callbackurl']:'';
        $process_code = $data['process_code'];
        if($process_code == '10001'){//请求短信、图片next接口
            $this->actionPublicdata();
        }
        if($process_code == '10002'){//刷新短信验证码
            $this->actionRmsgcode();
        }
        if($process_code == '10003'){//刷新图片验证码
            $this->actionRpiccode();
        }

        if (!$idcard) {
            return $this->resp(8003, '身份证不能为空');
        }
        //检测年龄和区域
        $jxlRequestModel = new JxlRequestModel();
        if (!$jxlRequestModel->validBirth($idcard)) {
            return $this->resp(8004, '您的年龄不符合要求');
        }
        if (!$jxlRequestModel->validArea($idcard)) {
            return $this->resp(8005, $jxlRequestModel->errinfo);
        }
        //判断几个月时间内,使用历史数据
        /*$oJxlStat = new JxlStat;
        $oHistory = $oJxlStat->getRongHistory($data['phone']);
        if ($oHistory) {
            return $this->resp(0, [
                'requestid' => $oHistory['requestid'], // 查询时使用这个就可以了
                'token' => '', // 使用这个token也可以查询
                'phone' => $oHistory['phone'],
                'process_code' => 10008,
                'response_type' => '',
                'status' => true,
            ]);
        }*/

        //最近2分钟时相同的数据返回同样的结果不需要再次入库
        $account = isset($data['account']) ? $data['account'] : $data['phone'];
        $oSameJxl = (new JxlRequestModel)->getRecentSame($account, $data['password'], $data['captcha']);
        if ($oSameJxl) {
            $this->uid = $oSameJxl->id;
        }else{
            //保存数据到db中
            $data['create_time'] = time();
            $data['account'] = $account;
            $data['type'] = isset($data['type']) ? $data['type'] : 'SUBMIT_CAPTCHA';
            $data['aid'] = $this->appData['id'];
            $data['token'] = '';
            $data['website'] = '';
            $data['source'] = 3;//来源融360
            $data['response_type'] = '';
            $data['result'] = '';
            $data['contacts'] = '';
            $data['callbackurl'] = $callbackurl;

            $jxlRequestModel = new JxlRequestModel();
            if ($errors = $jxlRequestModel->chkAttributes($data)) {
                return $this->resp(8003, implode('|', $errors));
            }
            $res = $jxlRequestModel->save();
            $this->uid = $jxlRequestModel->attributes['id'];
            $this->reqData['user_id'] = $this->uid;
            if (!$res) {
                return $this->dayLog(
                    'rong',
                    'actionLoginrul',
                    '提交数据', $data,
                    '失败原因', $jxlRequestModel->errors
                );
            }
        }
        $bizData = array(
            'cellphone' => $data['phone'],
            'password' => $data['password'],
            'user_id' => $this->uid
        );
        //获取基本信息
        $res = $this->rapi->operatorSend($bizData, 'crawler.api.mobile.v4.getLoginRule');
        if (!is_array($res)) {
            $this->dayLog('rong','Loginrule:请求失败 userid:'.$this->uid);
            $this->resp(8004, "服务器异常 请重试1");
        }elseif($res['error'] != 200){
            $this->dayLog('rong','Loginrule:'.$res.' userid:'.$this->uid);
            return $this->resp($res['error'] ,$res['msg']);
        }else{
            //运营商请求登陆
            $res = $this->actionUserlogin();
//            return $this->resp(0,$res);
            $callData = $this->getResParam($res,'login');
            if(!is_array($callData)){
                $this->dayLog('rong','Loginrule:calldata请求失败 userid:'.$this->uid);
                $this->resp(8004, "数据异常 请重试2");
            }else {
                if ($callData['flag'] == 1) {
                    return $this->resp(0, [
                        'requestid' => $this->uid,
                        'phone' => $this->reqData['phone'],
                        'process_code' => 10008,
                        'response_type' => '',
                        'status' => true,
                    ]);
                    /*sleep(20);
                    $this->actionUserdata();
                    $this->outUniqueId = $this->actionReport();
                    sleep(10);
                    $this->reqData['outUniqueId'] = $this->outUniqueId;
                    $this->actionDetail();*/
                } else {
                    $callData['requestid'] = $this->uid;
                    $callData['process_code'] = 10001;//发送短信或者图片或者图片+短信验证码
                    $this->resp(0, $callData);

                }
            }

        }

    }

    public  function actionUserlogin(){//运营商登陆信息 已发送短信
        $method = 'login';
        $bizData = array(
            'cellphone' => $this->reqData['phone'],
            'password' => $this->reqData['password'],
            'user_id' => $this->uid
        );
        $res = $this->rapi->operatorSend($bizData, 'crawler.api.mobile.v4.'.$method);
        if (!is_array($res)) {
            $this->dayLog('rong','login:请求失败');
            return $this->resp(8004, "服务器异常 请重试3");
        }elseif($res['error'] != 200){
            $this->dayLog('rong','login:'.$res);
            return $this->resp($res['error'] ,$res['msg']);
        }else{
            return $res;
        }

    }
    public function actionPublicdata(){//第二：获取next接口信息
        $method = $this->reqData['method'];
        if ($method == '') {
            $this->resp(8004, "method不能为空");
        }else{
            $this->reqData['cellphone'] = $this->reqData['phone'];
            $this->uid = $this->reqData['user_id'] = $this->reqData['requestid'];
            $this->reqData['message_code'] = $this->reqData['captcha'];

            $res = $this->rapi->operatorSend($this->reqData, 'crawler.api.mobile.v4.'.$method);
            if (!is_array($res)) {
                $this->dayLog('rong','Publicdata:请求失败 userid:'.$this->reqData['requestid']);
                $this->resp(8004, "服务器异常 请重试4");
            }elseif($res['error'] != 200){
                $this->dayLog('rong','Publicdata:'.$res.' userid:'.$this->reqData['requestid']);
                return $this->resp($res['error'] ,$res['msg']);
            }else{
                $callData = $this->getResParam($res,$method);
                if(!is_array($callData)){
                    $this->dayLog('rong','Publicdata:calldata请求失败 userid:'.$this->reqData['requestid']);
                    $this->resp(8004, "数据异常 请重试5");
                }else {
                    if ($callData['flag'] == 1) {
                        return $this->resp(0, [
                            'requestid' => $this->uid,
                            'phone' => $this->reqData['phone'],
                            'process_code' => 10008,
                            'response_type' => '',
                            'status' => true,
                        ]);
                    } else {

                        $callData['requestid'] = $this->reqData['requestid'];
                        $callData['process_code'] = 10001;
                        $rurl = $this->rapi->getRouteurl();
                        $param = Crypt3Des::decrypt(json_encode($callData));
                        $rurl = $rurl.'?param='.$param;
                        return $this->resp(0, $callData);

                    }
                }
            }
        }

    }

    public  function actionUserdata(){//抓取用户数据
        $bizData = array(
            'user_id' => $this->uid
        );
        $sta = 1;
        $res = $this->rapi->operatorSend($bizData, 'wd.api.mobilephone.getdata');

        if (!is_array($res)) {
            $this->resp(8004, "服务器异常 请重试");
        }elseif($res['error'] != 200){
            return $this->resp($res['error'] ,$res['msg']);
        }else{//抓取数据成功
            $datalist = $res['wd_api_mobilephone_getdata_response']['data']['data_list'];
            if(empty($datalist)){
                $sta = $sta + 1;
                if($sta == 2){
                    $this->actionUserdata();
                }
                $this->resp(8004, "拉取失败，请重试");
            }else{
                $opr = $datalist[0]['userdata']['user_source'];
                $opr = strtolower($opr);
                $request = new JxlRequestModel();
                $request = $request->getById($this->uid);
                $request->website = $opr;//用户运营商
                $request->save();

                $this->rapi->writeLog($this->uid.'_detail',json_encode($res));//记录详情json
                return true;
            }
        }

    }

    public  function actionRpiccode(){//刷新图片验证码
        $method = 'refreshPicCode';
        $bizData = array(
            'cellphone' => $this->reqData['phone'],
            'piccode_type' => 2,
            'user_id' => $this->reqData['requestid']
        );

        $res = $this->rapi->operatorSend($bizData, 'crawler.api.mobile.v4.'.$method);

        if (!is_array($res)) {
            $this->resp(8004, "服务器异常 请重试");
        }elseif($res['error'] != 200){
            return $this->resp($res['error'] ,$res['msg']);
        }else{
//            print_r($res);
            $img = $res->crawler_api_mobile_login_response->pic_code;
            $this->resp(0, ['img'=>$img,'status' => true,'process_code' => 10001]);
        }

    }

    public  function actionRmsgcode(){//刷新短信验证码
        $method = 'refreshMessageCode';
        $bizData = array(
            'cellphone' => $this->reqData['phone'],
            'messagecode_type' => 1,
            'user_id' => $this->reqData['requestid']
        );

        $res = $this->rapi->operatorSend($bizData, 'crawler.api.mobile.v4.'.$method);

        if (!is_array($res)) {
            $this->resp(8004, "服务器异常 请重试");
        }elseif($res['error'] != 200){
            return $this->resp($res['error'] ,$res['msg']);
        }else{
            $this->resp(0, ['status' => true,'process_code' => 10001]);
        }

    }

    public function actionReport(){//获取运营商报告
        $method = 'tianji.api.tianjireport.collectuser';
        $bizData = array(
            'type' => 'mobile',
            'platform' => 'api',
            'phone' => $this->reqData['phone'],
            'name' => $this->reqData['name'],
            'idNumber' => $this->reqData['idcard'],
            'userId' => $this->uid,
            'notifyUrl' => $this->rapi->config['notifyUrl'],
            'outUniqueId' => time(),
            'version' => '2.0'

        );
        $res = $this->rapi->operatorSend($bizData, $method);
        if (!is_array($res)) {
            $this->resp(8004, "服务器异常 请重试");
        }elseif($res['error'] != 200){
            return $this->resp($res['error'] ,$res['msg']);
        }else{
            $outUniqueId = $res['tianji_api_tianjireport_collectuser_response']['outUniqueId'];
            return $outUniqueId;
//            print_r($res);exit;
//            $this->resp(0, $res);
        }
    }

    public function actionDetail(){//获取运营商报告详情
        $method = 'tianji.api.tianjireport.detail';
        $bizData = array(
            'userId' => $this->uid,
            'outUniqueId' => $this->outUniqueId,
            'reportType' => 'html'

        );
        $res = $this->rapi->operatorSend($bizData, $method);
        if (!is_array($res)) {
            $this->resp(8004, "服务器异常 请重试");
        }elseif($res['error'] != 200){
            return $this->resp($res['error'] ,$res['msg']);
        }else{
            $url = $this->rapi->writeLog($this->uid,json_encode($res));//存储报告json
            $oJxlStat = new JxlStat;
            $request = new JxlRequestModel();
            $postData = [
                'aid' => $this->appData['id'],
                'requestid' => $this->uid,
                'name' => $this->reqData['name'],
                'idcard' => $this->reqData['idcard'],
                'phone' =>  $this->reqData['phone'],
                'website' => 'rong360mobile',
                'is_valid' => 3,
                'url' => $url,
            ];

            //5 保存到DB中
            $result = $oJxlStat->saveStat($postData);
            if (!$result) {
                return $this->dayLog('rong','saveStat','保存失败', $postData);
            }
            $request = $request->getById($this->uid);
            $request->result_status = 1;
            $request->process_code = 10008;
            $request->save();

            return $this->resp(0, [
                'requestid' => $this->uid,
                'token' => '',
                'phone' => $this->reqData['phone'],
                'process_code' => 10008,
                'response_type' => '',
                'status' => true,
            ]);
        }
    }

    /**
     * 开始查询数据
     * 使用手机号
     *
     */
    public function actionQuery() {
        //1 验证
        $phone = $this->reqData['phone'];
        if (!$phone) {
            return $this->resp(8133, "手机号不能为空");
        }

        //2 查找文件
        $oJxlStat = new JxlStat();
        $data = $oJxlStat->getDataByPhone($phone);
        if (empty($data)) {
            return $this->resp(8134, "数据为空");
        }

        //4 返回结果
        return $this->resp(0, $data);
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

    public function postCurl($postData,$url){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        ob_start();
        curl_exec($ch);
        $result = ob_get_contents() ;
        ob_end_clean();
        return $result;
    }



/*----------------------end--------------*/


}
