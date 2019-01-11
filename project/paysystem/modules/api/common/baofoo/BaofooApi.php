<?php
namespace app\modules\api\common\baofoo;

use app\modules\api\common\baofoo\functions\BFRSA;
use app\modules\api\common\baofoo\functions\SdkXML;
use app\common\Curl;
use app\common\Logger;


/**
 * @desc 宝付接口类;
 * @author lubaba
 */
class BaofooApi {
    private $config = null;
    private $bfrsa = null;
    private $sdkxml = null;
    public function __construct($cfg) {
        // 获取配置文件
        $this->config = $this->getConfig($cfg);
    }
    
    /**
     * @desc 获取配置文件
     * @param  str $cfg 
     * @return  []
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
     * @desc 请求宝付公共请求参数
     * @return  []
     */
    private function getCommomParam($sign,$txn_sub_type){
        if(!$this->config) return [];
        return [
            'version' => $this->config['version'],
            'terminal_id' => $this->config['terminal_id'],
            'txn_type' => $this->config['txn_type'],
            'txn_sub_type' => $txn_sub_type,
            'member_id' => $this->config['member_id'],
            'data_type' => $this->config['data_type'],
            'data_content'=>$sign
        ];
    }
    /**
     * @desc 请求宝付代付公共请求参数
     * @return  []
     */
    private function getCommomParamDf($sign){
        if(!$this->config) return [];
        return [
            'version' => $this->config['version_df'],
            'member_id' => $this->config['member_id'],
            'terminal_id' => $this->config['terminal_id_df'],
            'data_type' =>$this->config['data_type'],
            'data_content'=>$sign
        ];
    }
    /**
     * @desc 直接支付接口
     * @param  obj $oPayorder
     * @param  string $baofooBankcode 银行卡code
     * @return [res_code, res_data]
     */
    public function pay($oPayorder,$baofooBankcode) {
        $nowDate = date('YmdHis',time());
        $dataContent = [
            'txn_sub_type' => $this->config['txn_sub_type'],
            'biz_type' => $this->config['biz_type'],
            'terminal_id' => $this->config['terminal_id'],
            'member_id' => $this->config['member_id'],
            'pay_code' => $baofooBankcode,
            'acc_no'=>$oPayorder->cardno,
            'id_card_type'=>$this->config['id_card_type'],
            'id_card'=>$oPayorder->idcard,
            'id_holder'=>$oPayorder->name,
            'mobile'=>$oPayorder->phone,
            'trans_id'=>$oPayorder->channel_id."_".$oPayorder->orderid,
            'txn_amt'=>$oPayorder->amount,
            'trade_date'=>$nowDate,
            'trans_serial_no'=>time().uniqid('baofoo')
        ];
        //生成签名
        $sign = $this->createSign($dataContent);
        $data = $this->getCommomParam($sign,$this->config['txn_sub_type']);
        if(empty($data)) return false; 
        $returnInfo = $this->HttpClientPost($this->config['action_url'],$data);
        // 返回结果
        $response = $this->parseResult($returnInfo);
        Logger::dayLog('baofoo', 'baofooApi/pay',$this->config['action_url'], $sign, $response);
        return $response;
    }

    /**
     * @desc 余额查询
     * @param array $queryData
     * @return  array
     */
    public function getBalance(){
        $data = [
            'member_id' =>  $this->config['member_id'],
            'terminal_id' => $this->config['b_terminal_id'],
            'return_type' => 'json',
            'trans_code' => 'BF0001',
            'account_type' => '1',
            'version' => '4.0'
        ];
        $MAK = "&";//分隔符
        $singMd5Str = "member_id=".$data["member_id"].$MAK."terminal_id=".$data["terminal_id"].$MAK."return_type=".$data["return_type"].$MAK."trans_code=".$data["trans_code"].$MAK."version=".$data["version"].$MAK."account_type=".$data["account_type"].$MAK."key=".$this->config['Key'];
        $data["sign"] = strtoupper(md5($singMd5Str));  //验签  需要转大写
        $returnInfo = $this->HttpClientPost($this->config['b_query_url'],$data);
        $endataContent = json_decode($returnInfo,TRUE);
        Logger::dayLog('baofoo', 'baofooApi/getBalance',$this->config['b_query_url'], $endataContent);
        return $endataContent;
    }

    /****
     * @desc 商户转账
     * 
     */
    public function transferAcc($data) {
        //组装数据
        if(empty($data)) return [];
        $dataContent = [];
        $data['to_acc_name'] = $this->config['to_acc_name'];
        $data['to_acc_no'] = $this->config['to_acc_no'];
        $data['to_member_id'] = $this->config['to_member_id'];
        $dataContent['trans_content']['trans_reqDatas']['trans_reqData'] = $data;
        //生成签名
        $sign = $this->createSign($dataContent);
        $curlData = $this->getCommomParamDf($sign);
        if(empty($curlData)) return false;
        $returnInfo = $this->HttpClientPost($this->config['pay_url'],$curlData);
        // 返回结果
        $response = $this->parseResult($returnInfo);
        Logger::dayLog('bfpay', 'transfer',$this->config['pay_url'], $sign, $response);
        return $response;
    }

    /****
     * @desc 转账查询
     * 
     */
    public function transferQuery($data) {
        //组装数据
        if(empty($data)) return [];
        $dataContent = [];
        $data['trans_member_id'] = $this->config['member_id'];
        $dataContent['trans_content']['trans_reqDatas']['trans_reqData'] = $data;
        //生成签名
        $sign = $this->createSign($dataContent);
        $curlData = $this->getCommomParamDf($sign);
        if(empty($curlData)) return false;
        $returnInfo = $this->HttpClientPost($this->config['tran_query_url'],$curlData);
        // 返回结果
        $response = $this->parseResult($returnInfo);
        Logger::dayLog('bfpay', 'transferQuery',$this->config['tran_query_url'], $sign, $response);
        return $response;
    }

    /**
     * @desc 代扣查询
     * @param array $queryData
     * @return  array
     */
    public function BaofooQuery($queryData){
        $dataContent = [
            'txn_sub_type' => $this->config['query_txn_sub_type'],
            'biz_type' => $this->config['biz_type'],
            'terminal_id' => $this->config['terminal_id'],
            'member_id' => $this->config['member_id'],
            'trans_serial_no'=>$queryData['trans_serial_no'],
            'orig_trans_id'=>$queryData['orig_trans_id'],
            'orig_trade_date'=>$queryData['orig_trade_date']
        ];
        //生成签名
        $sign = $this->createSign($dataContent);
        $data = $this->getCommomParam($sign,$this->config['query_txn_sub_type']);
        if(empty($data)) return false; 
        $returnInfo = $this->HttpClientPost($this->config['query_url'],$data);
         // 返回结果
        $response = $this->parseResult($returnInfo);
        Logger::dayLog('baofoo', 'baofooApi/BaofooQuery',$this->config['query_url'], $sign, $response);
        return $response;
    }

    /**
     * @desc 预绑卡
     * @param  array
     * @return array
    */
    public function prepBinding($params){
        $dataContent = [
            'txn_sub_type' => $this->config['prep_binding_txn_sub_type'],
            'biz_type' => $this->config['biz_type'],
            'terminal_id' => $this->config['terminal_id'],
            'member_id' => $this->config['member_id'],
            //自定义参数
            'trans_serial_no'=>$params['trans_serial_no'],
            'trans_id'=>$params['trans_id'],
            'acc_no'=>$params['acc_no'],
            'id_card'=>$params['id_card'],
            'id_holder'=>$params['id_holder'],
            'mobile'=>$params['mobile'],
            'pay_code'=>$params['pay_code'],
            'trade_date'=>$params['trade_date']
        ];
        //生成签名
        $sign = $this->createSign($dataContent);
        //组装参数
        $data = $this->getCommomParam($sign,$this->config['prep_binding_txn_sub_type']);
        if(empty($data)) return false; 
        //请求宝付
        $returnInfo = $this->HttpClientPost($this->config['action_url'],$data);
        // 返回结果
        $response = $this->parseResult($returnInfo);
        Logger::dayLog('baofoo', 'baofooApi/prepBinding',$this->config['action_url'], $sign, $response);
        return $response;
    }

    /**
     * @desc 确认绑卡
     * @param   array
     * @return  array
    */
    public function confirmBinding($params){
        $dataContent = [
            'txn_sub_type' => $this->config['confirm_binding_txn_sub_type'],
            'biz_type' => $this->config['biz_type'],
            'terminal_id' => $this->config['terminal_id'],
            'member_id' => $this->config['member_id'],
            //自定义参数
            'trans_serial_no'=>$params['trans_serial_no'],
            'trans_id'=>$params['trans_id'],
            'sms_code'=>$params['sms_code'],
            'trade_date'=>$params['trade_date']
        ];
        //生成签名
        $sign = $this->createSign($dataContent);
        //组装参数
        $data = $this->getCommomParam($sign,$this->config['confirm_binding_txn_sub_type']);
        if(empty($data)) return false; 
        //请求宝付
        $returnInfo = $this->HttpClientPost($this->config['action_url'],$data);
        // 返回结果
        $response = $this->parseResult($returnInfo);
        Logger::dayLog('baofoo', 'baofooApi/confirmBinding',$this->config['action_url'], $sign, $response);
        return $response;
    }

    /**
     * @desc 确认绑卡
     * @param  array
     * @return array
    */
    public function directBinding($params){
        $dataContent = [
            'txn_sub_type' => $this->config['direct_binding_txn_sub_type'],
            'biz_type' => $this->config['biz_type'],
            'terminal_id' => $this->config['terminal_id'],
            'member_id' => $this->config['member_id'],
            //自定义参数
            'trans_serial_no'=>$params['trans_serial_no'],
            'trans_id'=>$params['trans_id'],
            'acc_no'=>$params['acc_no'],
            'id_card'=>$params['id_card'],
            'id_holder'=>$params['id_holder'],
            'mobile'=>$params['mobile'],
            'pay_code'=>$params['pay_code'],
            'trade_date'=>$params['trade_date']
        ];
        //生成签名
        $sign = $this->createSign($dataContent);
        //组装参数
        $data = $this->getCommomParam($sign,$this->config['direct_binding_txn_sub_type']);
        if(empty($data)) return false; 
        //请求宝付
        $returnInfo = $this->HttpClientPost($this->config['action_url'],$data);
        // 返回结果
        $response = $this->parseResult($returnInfo);
        Logger::dayLog('baofoo', 'baofooApi/directBinding',$this->config['action_url'], $sign, $response);
        return $response;
    }

    /**
     * @desc 解除绑定关系
     * @param  array
     * @return  array
    */
    public function removeBinding($params){
        $dataContent = [
            'txn_sub_type' => $this->config['remove_binding_txn_sub_type'],
            'biz_type' => $this->config['biz_type'],
            'terminal_id' => $this->config['terminal_id'],
            'member_id' => $this->config['member_id'],
            //自定义参数
            'trans_serial_no'=>$params['trans_serial_no'],
            'bind_id'=>$params['bind_id'],
            'trade_date'=>$params['trade_date']
        ];
        //生成签名
        $sign = $this->createSign($dataContent);
        //组装参数
        $data = $this->getCommomParam($sign,$this->config['remove_binding_txn_sub_type']);
        if(empty($data)) return false; 
        //请求宝付
        $returnInfo = $this->HttpClientPost($this->config['action_url'],$data);
        // 返回结果
        $response = $this->parseResult($returnInfo);
        Logger::dayLog('baofoo', 'baofooApi/removeBinding',$this->config['action_url'], $sign, $response);
        return $response;
    }

    /**
     * @desc 查询绑定关系
     * @param  array
     * @return array
    */
    public function queryBinding($params){
        $dataContent = [
            'txn_sub_type' => $this->config['query_binding_txn_sub_type'],
            'biz_type' => $this->config['biz_type'],
            'terminal_id' => $this->config['terminal_id'],
            'member_id' => $this->config['member_id'],
            //自定义参数
            'trans_serial_no'=>$params['trans_serial_no'],
            'acc_no'=>$params['acc_no'],
            'trade_date'=>$params['trade_date']
        ];
        //生成签名
        $sign = $this->createSign($dataContent);
        //组装参数
        $data = $this->getCommomParam($sign,$this->config['query_binding_txn_sub_type']);
        if(empty($data)) return false; 
        //请求宝付
        $returnInfo = $this->HttpClientPost($this->config['action_url'],$data);
        // 返回结果
        $response = $this->parseResult($returnInfo);
        Logger::dayLog('baofoo', 'baofooApi/queryBinding',$this->config['action_url'], $sign, $response);
        return $response;
    }

    /**
     * @desc 认证支付类预支付交易
     * @param   array
     * @return  array
    */
    public function prepPay($params){
        $dataContent = [
            'txn_sub_type' => $this->config['prep_pay_txn_sub_type'],
            'biz_type' => $this->config['biz_type'],
            'terminal_id' => $this->config['terminal_id'],
            'member_id' => $this->config['member_id'],
            //自定义参数
            'trans_serial_no'=>$params['trans_serial_no'],
            'trans_id'=>$params['trans_id'],
            'bind_id'=>$params['bind_id'],
            'txn_amt'=>$params['txn_amt'],
            'trade_date'=>$params['trade_date'],
            'risk_content'=>$params['risk_content']
        ];
        //生成签名
        $sign = $this->createSign($dataContent);
        //组装参数
        $data = $this->getCommomParam($sign,$this->config['prep_pay_txn_sub_type']);
        if(empty($data)) return false; 
        //请求宝付
        $returnInfo = $this->HttpClientPost($this->config['action_url'],$data);
        // 返回结果
        $response = $this->parseResult($returnInfo);
        Logger::dayLog('baofoo', 'baofooApi/prepPay',$this->config['action_url'], $sign, $response);
        return $response;
    }

    /**
     * @desc 认证支付类确定支付交易
     * @param  array
     * @return array
    */
    public function confirmPay($params){
        $dataContent = [
            'txn_sub_type' => $this->config['confirm_pay_txn_sub_type'],
            'biz_type' => $this->config['biz_type'],
            'terminal_id' => $this->config['terminal_id'],
            'member_id' => $this->config['member_id'],
            //自定义参数
            'trans_serial_no'=>$params['trans_serial_no'],
            'business_no'=>$params['business_no'],
            'sms_code'=>$params['sms_code'],
            'trade_date'=>$params['trade_date']
        ];
        //生成签名
        $sign = $this->createSign($dataContent);
        //组装参数
        $data = $this->getCommomParam($sign,$this->config['confirm_pay_txn_sub_type']);
        if(empty($data)) return false; 
        //请求宝付
        $returnInfo = $this->HttpClientPost($this->config['action_url'],$data);
        if(!$returnInfo) return $returnInfo;
        // 返回结果
        $response = $this->parseResult($returnInfo);
        Logger::dayLog('baofoo', 'baofooApi/confirmPay',$this->config['action_url'], $sign, $response);
        return $response;
    }

    /**
     * @desc 认证支付类预支付交易
     * @param  
     * @param  
     * @return 
    */
    public function queryPay($params){
        $dataContent = [
            'txn_sub_type' => $this->config['query_trade_txn_sub_type'],
            'biz_type' => $this->config['biz_type'],
            'terminal_id' => $this->config['terminal_id'],
            'member_id' => $this->config['member_id'],
            //自定义参数
            'trans_serial_no'=>$params['trans_serial_no'],
            'orig_trans_id'=>$params['orig_trans_id'],
            'orig_trade_date'=>$params['orig_trade_date']
        ];
        //生成签名
        $sign = $this->createSign($dataContent);
        //组装参数
        $data = $this->getCommomParam($sign,$this->config['query_trade_txn_sub_type']);
        if(empty($data)) return false; 
        //请求宝付
        $returnInfo = $this->HttpClientPost($this->config['query_url'],$data);
        // 返回结果
        $response = $this->parseResult($returnInfo);
        Logger::dayLog('baofoo', 'baofooApi/queryPay',$this->config['query_url'], $sign, $response,'data',$dataContent);
        return $response;
    }
    

    /**
     * @desc 生成签名
     * @param array $data_content_parms 签名参数
     * @return string
     */
    private function createSign($dataContentParms){
        if($this->config['data_type'] == 'json'){
            $encryptedString = str_replace("\\/", "/",json_encode($dataContentParms));//转JSON
        }else{
            $this->sdkxml = new SdkXML();	//实例化XML转换类
	        $encryptedString = $this->sdkxml->toXml($dataContentParms);//转XML
        }
        $this->bfrsa = new BFRSA($this->config["pfxfilename"], $this->config["cerfilename"], $this->config["private_key_password"]); //实例化加密类。
        $Encrypted = $this->bfrsa->encryptedByPrivateKey($encryptedString);	//先BASE64进行编码再RSA加密
        return $Encrypted;
    }

    /**
     * @desc 提交数据
     * @param string $url
     * @param array $data
     * @return string
     */
    public function HttpClientPost($url,$data) {
        $timeLog = new \app\common\TimeLog();
        //$jsonString = json_encode($data);
        $postDataString = http_build_query($data);
        $curl = new Curl();
        // $curl->setOption(CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_1);
        $curl->setOption(CURLOPT_SSLVERSION, 5);
        $curl->setOption(CURLOPT_CONNECTTIMEOUT, 30);
        $curl->setOption(CURLOPT_TIMEOUT, 60);
        $content = '';
        $content = $curl->post($url, $postDataString);
        $status = $curl->getStatus();
        $timeLog->save('baofoo', ['api', 'POST', $status, $url, $postDataString, $content]);
        if ($status != 200) {
            Logger::dayLog(
                "baofoo",
                "请求信息", $url, $data,
                "http状态", $status,
                "响应内容", $content
            );
        }
        return $content;
    }

    /**
     * @desc 非对称解密数据并标准化返回
     * @param string $res
     * @return array
     */
    private function parseResult($res) {
        if(!$res)  return ['resp_code' => "100401", 'resp_msg' => "请求出错，请检查网络"];
        $returnDecode = $this->bfrsa->decryptByPublicKey($res); 
        $endataContent = [];
        if($this->config['data_type'] == 'xml'){
            $endataContent = SdkXML::XTA($returnDecode);
        }else{
            $endataContent = json_decode($returnDecode,TRUE);
        }
       return $endataContent;
    }
    
    public function decryptData($encryptStr){
        $this->bfrsa = new BFRSA($this->config["pfxfilename"], $this->config["cerfilename"], $this->config["private_key_password"]); //实例化加密类。
        $returnDecode = $this->bfrsa->decryptByPublicKey($encryptStr);
        $endataContent = [];
        if($this->config['data_type'] == 'xml'){
            $endataContent = SdkXML::XTA($returnDecode);
        }else{
            $endataContent = json_decode($returnDecode,TRUE);
        }
       return $endataContent;
    }
    /**
     * Undocumented function
     * 转账账单下载
     * @param [type] $settle_date
     * @return void
     */
    public function tranPayBill($settle_date){
        $dataContent = [
            'version'   => $this->config['tran_bill_version'],
            'file_type' => $this->config['file_type'],
            'member_id' => $this->config['member_id'],
            'client_ip' => $this->config['client_ip'],
            'settle_date'=> $settle_date
        ];
        
        //请求宝付
        $returnInfo = $this->HttpClientPost($this->config['tran_bill_url'],$dataContent);
        Logger::dayLog('bftranbill',$returnInfo,$dataContent);
        //$returnInfo = 'resp_code=0000&resp_msg=交易成功&resp_body=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';
        $returnResult = explode('=',$returnInfo);
        return $returnResult;
    }
}
