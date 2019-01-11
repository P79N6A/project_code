<?php

namespace app\modules\api\common\changjie;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use app\modules\api\common\changjie\CjtSdk;
/**
 * 畅捷通支付类
 */
class CjtApi {

    private $config;
    private $cjtSdkObj;

    public function __construct($cfg) {
        // 获取配置文件
        $this->config = $this->getConfig($cfg);
        $this->cjtSdkObj = new CjtSdk($this->config['private_key_path'],$this->config['public_key_path'],$this->config['private_key_pwd'],$this->config['trade_url']);
    }
    
    /**
     * 获取配置文件
     * @param  str $env
     * @param  str $aid
     * @return   []
     */
    private function getConfig($cfg) {
        $configPath = __DIR__ . "/config/{$cfg}.php";
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
    public function getXmlParam($bodyInfo,$trx_code,$client_id,$retCode=''){
        if($trx_code == '' || $client_id == ''){
            return '';
        } 
        $nowDate = date('YmdHis',time());
        $data = [
            'message'=>[
                'info'=>[
                    'trx_code'  => $trx_code,
                    'version'   => $this->config['version'],
                    'merchant_id'=> $this->config['merchant_id'],
                    'req_sn'    => $client_id,
                    'timestamp' => $nowDate,
                    'signed_msg'=> '',
                ]
            ]
               
        ];
        if(!empty($bodyInfo)){
            $data['message']['body'] = $bodyInfo;
        }
        if($retCode != ''){
            $data['message']['info']['ret_code'] = $retCode;
        }
        if($trx_code == CjRemit::CJ_NOTIFY_CODE){//异步通知返回xml
            $resultXml = $this->cjtSdkObj->array2xml($data);
            $resultXml = $this->cjtSdkObj->sign($resultXml);
            return $resultXml;
        }
        $resultXml = $this->cjtSdkObj->createXml($data);
        $xml = simplexml_load_string($resultXml);
        $result = json_decode(json_encode($xml),TRUE);
        return $result;
    }
/*
*获得代付提交的body实体
*
*/
    public function getBodyPayment($orderInfo){
        if(empty($orderInfo)) return [];
        if(!isset($orderInfo['card_type']) || !$orderInfo['card_type']){
            $orderInfo['card_type'] = 1; //默认借记卡
        }
        if($orderInfo['card_type'] == 1){
            $account_type = '00';
        }elseif($orderInfo['card_type'] == 2){
            $account_type = '02';
        }else{
            $account_type = '00';
        }

        $remitInfo = [
            'business_code' => $this->config['business_code'],
            'corp_acct_no'  => $this->config['corp_acct_no'],
            'product_code'  => $this->config['product_code'],
            'account_prop'  => '0',
            'bank_general_name' => $orderInfo['bankname'],
            'account_type' => $account_type,
            'account_no'    => $orderInfo['cardno'],
            'account_name'  => $orderInfo['name'],
            'bank_name' => $orderInfo['name'],  //开户行详细名称，也叫网点
            'bank_code' => '', //对方账号对应的支行行号
            'drct_bank_code' => '',//对方开户行对应的清算行总行行号
            'currency'      => 'CNY',
            'amount'        => bcmul(100,number_format($orderInfo['amount'],2,'.',''))
        ];

        Logger::dayLog('cjremit/submit', $remitInfo);
        return $remitInfo;
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


}
