<?php

namespace app\modules\api\common\cjremit;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use app\modules\api\common\changjie\CjtSdk;
use app\modules\api\common\cjremit\Rsasc;
/**
 * 畅捷通支付类
 */
class CjtApi {

    private $config;
    private $cjtSdkObj;

    public function __construct($cfg) {
        // 获取配置文件
        $this->config = $this->getConfig($cfg);
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
/*
*获得代付提交的body实体
*
*/
    public function getBodyPayment($orderInfo,$postData,$channel_id){
        if(empty($orderInfo)) return [];

        $oRsa = new Rsasc();
        $needEncryptData = array();
        $needEncryptData['AcctNo'] = $orderInfo->guest_account; //对手人账号
        $needEncryptData['AcctName'] = $orderInfo->guest_account_name;
        $needEncryptData = $oRsa->publicRsaSign($needEncryptData,$this->config['public_key_path']);


        $postData['Service'] = $this->config['Service'];
        $postData['Version'] = $this->config['Version'];
//        $postData['CorpAcctNo'] = $this->config['CorpAcctNo'];//企业账号
        $postData['PartnerId'] = $this->config['PartnerId'];//商户号
        $postData['InputCharset']= $this->config['InputCharset'];   #字符编码
        $postData['TradeDate'] = date('Ymd').'';
        $postData['TradeTime'] = date('His').'';

        //回调地址
        $backUrl =   $this->config['back_url'].'?xhh_code_id='.$channel_id;
        if (!SYSTEM_PROD) { //测试地址
            $backUrl =  'http://182.92.80.211:8091/cjback/notify?xhh_code_id='.$channel_id;
        }
        if($postData['TransCode'] == CjRemit::CJ_COMMIT_CODE){
            $postData['CorpPushUrl'] = $backUrl;//商户推送的URL地址
        }

//        $postData['TransCode'] =  $trx_code; //功能码
//        $postData['OutTradeNo'] = $orderInfo->client_id; //外部流水号
//        $postData['BusinessType'] = '0';//业务类型 0私人 1公司
//        $postData['BankCommonName'] = $orderInfo->guest_account_bank;// 通用银行名称
//        $postData['AccountType'] = $account_type;//账户类型 00借记卡 01贷记卡
//        $postData['Currency'] = 'CNY';  //人民币
//        $postData['TransAmt'] = $orderInfo->settle_amount;//交易金额
//        $postData['CorpPushUrl'] = 'http://172.20.11.16';//商户推送的URL地址
//        $postData['PostScript'] = '用途';//交易金额
        $postData = array_merge($postData, $needEncryptData);

        $postData['Sign']= $oRsa->rsaSign($postData,$this->config['private_key_path']);
        $postData['SignType'] = 'RSA'; //签名类型
        Logger::dayLog('cjremit/submit', $postData);



        $query  =   http_build_query($postData);
        $url     =$this->config['trade_url'].$query;
        Logger::dayLog('cjremit/reQuestUrl', 'reQuest', $url);
        $cfg = array(
            'ssl' => true
        );
        $response= $this->curlOpen($url,$cfg);
        return $response;
    }


    function curlOpen ($url, $cfg)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        if ($cfg['ssl']) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($ch,CURLINFO_HEADER_OUT,true);
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * 验签参数去空--验签--返回结果
     * @param $waitSing
     * @param $sing
     * @return bool
     */
    public function singVerification($waitSing,$sing){
//        foreach($waitSing as $k=>$v){
//            if(empty($v)){
//                unset($waitSing[$k]);
//            }
//        }
        $waitSing = array_filter($waitSing); //过滤掉空值
        $oRsa = new Rsasc();
        return $oRsa->rsaVerify($waitSing,$sing,$this->config['public_key_path']);
    }



}
