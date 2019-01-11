<?php

namespace app\modules\api\common\xn;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use app\common\Crypt3DesStr;
use app\modules\api\common\xn\Util;
/**
 * 小诺配置api
 */
class XnApi {

    public $config;
    private $object;
    public function __construct($cfg) {
        // 获取配置文件
        $this->config = $this->getConfig($cfg);
        $this->object = new Util();
    }
    
    /**
     * 获取配置文件
     * @param  str $env
     * @param  str $aid
     * @return   []
     */
    private function getConfig($cfg) {
        $configPath = dirname(__DIR__) . "/xn/config/{$cfg}.php";
        if (!file_exists($configPath)) {
            throw new \Exception($configPath . "配置文件不存在", 98);
        }
        $config = include $configPath;
        return $config;
    }
    /**
     * 拼接xml请求数据
     * trx_code:报文交易代码
     * return xml
     */
    public function getJsonParam($bodyInfo,$params){
        if(empty($bodyInfo) || empty($params)){
            return false;
        } 
        $microtime = $this->getMillisecond();
        $transactionId = 'TRAN_'.$microtime;
        $head = array('transactionId'=>$transactionId,'appid'=>$this->config['appid']);
        $data = json_encode(array('head'=>$head,'body'=>$bodyInfo));
        $postdata = $this->encrypt($data,$this->config['key'],$this->config['_v']);
        $postdata= array(
            'params'=>$postdata,
            'sign'=>md5($postdata.$this->config['key'].time()),
            'ts'=>time(),
            'appid'=>$this->config['appid']
        );

        if($params=='agreement'){
            $postUrl = $this->config['agreementUrl'];
        }elseif($params=='bank'){
            $postUrl = $this->config['bankurl'];
        }elseif($params =='bill'){
            $postUrl = $this->config['billurl'];
        }elseif($params=='repayment'){
            $postUrl = $this->config['repaymenturl'];
        }

        $res = $this->object->clientPost($postUrl,$postdata);
        $response = json_decode($res,true);
        Logger::dayLog('xn/xnapi',$postUrl,$data,$response);
        return $response;
    }
    /**
     * Undocumented function
     * 获取毫秒
     * @return void
     */
    private function getMillisecond() { 
        list($s1, $s2) = explode(' ', microtime()); 
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000); 
    }
    public function dodecrypt($post){
    
        return $this->decrypt($post,$this->config['key'],$this->config['_v']);
       
    }
    /*
    *获得代付查询的body实体
    *
    */
    public function getQueryBody($qry_req_sn){
        if(!$qry_req_sn) return [];
        return[
            'qry_req_sn' => $qry_req_sn
        ];
    }

    /**
     * 加密
     */
    public function encrypt($postData, $key, $v)
    {
        $crypt = new Crypt3DesStr($key,$v);
        return $crypt->encrypt($postData);
    }

    /**
     * 解密
     */
    public function decrypt($postData, $key,$v)
    {
        $crypt = new Crypt3DesStr($key,$v);
        return $crypt->decrypt($postData);
    }

    /**
     * 验签
     */
    public function verify($data,$t,$sign)
    {
        $checksign=md5($data.$this->config['key'].$t);
        if($sign != $checksign)
        {
            return false;
        }
        return true;
    }

}
