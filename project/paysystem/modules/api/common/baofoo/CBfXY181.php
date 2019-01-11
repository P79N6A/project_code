<?php
/**
 * @desc 宝付协议支付,通道Id为181的专用API
 * @author 孙瑞
 */

namespace app\modules\api\common\baofoo;
use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\common\Func;
use app\modules\api\common\baofoo\functions\BFRSA;
use app\modules\api\common\baofoo\functions\SignatureUtils;
use app\modules\api\common\baofoo\functions\AESUtil;
use app\models\StdError;
use app\models\baofoo\BfXYOrder;
use app\models\baofoo\BfSign;
use app\models\Payorder;

class CBfXY181 {
    private $config;
    private $orderId;
    private $channelId;
    //交易结果暂未知，需查询类
    private $bfQueryCode = ['BF00100','BF00112','BF00113','BF00115','BF00144','BF00202','BF00238'];

    /**
     * 获取配置文件名
     * @return array 失败返回[] 成功返回配置文件数组
     */
    private function getCfg() {
        if(!empty($this->config)){
            return $this->config;
        }
        if(!$this->channelId){
            return [];
        }
        $configPath = __DIR__ . "/config/prod{$this->channelId}.php";
        if (!file_exists($configPath)) {
            return [];
        }
        $config = include $configPath;
        return $config;
    }

    /**
     * 创建协议支付子订单
     * @param object $oPayorder 主订单数据对象
     * @return array res_code=>返回码 res_data=>返回信息
     */
    public function createOrder($oPayorder) {
        //1. 数据检测
        if (empty($oPayorder) || empty($oPayorder->channel_id) && empty($oPayorder->id)) {
            return StdError::returnStdError("1630001","参数错误");
        }
        $bfOrderData = $oPayorder->attributes;
		$bfOrderData['bind_id'] = 0;
		$bfOrderData['cli_identityid'] = $bfOrderData['aid'].'_'.$bfOrderData['channel_id'].'_'.$bfOrderData['identityid'];
        //2. 保存子订单
        $oBfXY = new BfXYOrder();
        if (!$oBfXY->addBfxyOrder($bfOrderData)) {
            Logger::dayLog('bfxy', 'createXYOrder: 创建协议支付子订单失败,主订单id为:'.$bfOrderData['orderId'].',失败原因:'.$oBfXY->errinfo);
            return StdError::returnStdError($bfOrderData['channelId'],"0012");
        }
        //3. 返回跳转Url地址
        $urlData = $oBfXY->getPayUrls('bfxy181');
        Logger::dayLog('bfxy', 'createXYOrder: 创建协议支付子订单成功,返回跳转地址为:'.json_encode($urlData));
        return ['res_code' => 0, 'res_data' => $urlData];
    }

    /**
     * 发送签约验证码
     * @param obj $oBaofoo 宝付子订单对象
     * @return array res_code=>返回码 res_data=>返回信息
     */
    public function sendSignSms($oBaofoo) {
        // 获取配置文件
        $this->channelId = $oBaofoo->channel_id;
        $this->orderId = $oBaofoo->payorder_id;
        $this->config = $this->getCfg();
        if (!$this->channelId || !$this->orderId || !$this->config){
            return StdError::returnStdError("1630001","参数错误");
        }
        // 获取签约信息
        $oBfSign = new BfSign();
        $bindId = $oBfSign->getSignInfo($oBaofoo);
		if($bindId < 0){
			return StdError::returnStdError($this->channelId,"0003");
		}elseif($bindId > 0){
			// 保存bindId
			if(!$oBaofoo->saveBindId($bindId)){
				return StdError::returnStdError($this->channelId,"0014");
			}
			// 发送短信
			$result = $oBaofoo->payorder->requestSms();
			if (!$result) {
				return StdError::returnStdError($this->channelId,"0010",$oBaofoo->payorder->errinfo);
			}
			return ['res_code' => 0,'res_data' =>'success'];
		}
		// 保存签约信息
		$oSignInfo = new BfSign();
		if (!$oSignInfo->addSignInfo($oBaofoo->payorder->attributes)) {
			return StdError::returnStdError($this->channelId,"0014");
		}
		if(!$oBaofoo->saveBindId($oSignInfo->id)){
			return StdError::returnStdError($this->channelId,"0014");
		}
        // 校验银行卡类型
        $cardType = (string)ArrayHelper::getValue($oSignInfo, 'bankcard_type','100');
        if(!in_array($cardType,$this->config['card_type'])){
            Logger::dayLog('bfxy', 'sendSignSms_Fail: 银行卡类型错误导致发送短信失败,主订单id为:'.$this->orderId);
            return StdError::returnStdError($this->channelId,"0025");
        }
        // 获取银行卡数据信息信息
		// 宝付要求的银行卡信息参数格式为: 卡号|持卡人姓名|身份证号|手机号|信用卡安全码|信用卡有效期
        $cardInfo['cardno'] = ArrayHelper::getValue($oSignInfo, 'cardno','');
        $cardInfo['name'] = ArrayHelper::getValue($oSignInfo, 'name','');
        $cardInfo['idcard'] = ArrayHelper::getValue($oSignInfo, 'idcard','');
        $cardInfo['phone'] = ArrayHelper::getValue($oSignInfo, 'phone','');
		// 借记卡无信用卡信息 使用 || 占位  信用卡则使用信用卡信息拼接: |信用卡安全码|信用卡有效期
		$creditCardStr = '||';
        if($cardType == $this->config['card_type']['credit_card']){
            $cardInfo['card_cvv2'] = ArrayHelper::getValue($oSignInfo, 'card_cvv2','');
            $cardInfo['card_date'] = ArrayHelper::getValue($oSignInfo, 'card_date','');
			$creditCardStr = '|'.$cardInfo['card_cvv2'].'|'.$cardInfo['card_date'];
        }
        // 对银行卡信息参数进行筛选
        array_walk($cardInfo, function ($item, $key){
            if(!$item){
                Logger::dayLog('bfxy', 'sendSignSms_Fail: '.$key.'字段为空,因银行卡信息不全发送短信失败,主订单id为:'.$this->orderId);
                return StdError::returnStdError($this->channelId,"0026");
            }
        });
        $accInfo = $cardInfo['cardno'].'|'.$cardInfo['name'].'|'.$cardInfo['idcard'].'|'.$cardInfo['phone'].$creditCardStr;
        Logger::dayLog('bfxy', 'sendSignSms_Log: 签约卡信息为:'.$accInfo.',主订单id为:'.$this->orderId);
        $sendRes = $this->doSendMsg(ArrayHelper::getValue($oSignInfo, 'cli_identityid'),$cardType,$accInfo);
        if(!$sendRes){
            return StdError::returnStdError($this->channelId,"0010");
        }
        // 保存短信请求数据并返回
        if (!$oBfSign->saveSignInfo($oSignInfo,$sendRes)) {
            return StdError::returnStdError($this->channelId,"0014");
        }
        // 判断请求结果返回第三方信息
        if($sendRes['error_code'] != '0000'){
            return StdError::returnStdError($this->channelId,"0010",$sendRes['error_msg']);
        }
        return ['res_code' => 0,'res_data' =>'success'];
    }

    /**
     * 校验签约验证码
     * @param obj $oBaofoo 宝付子订单对象
     * @param string $validatecode 验证码
     * @return array res_code=>返回码 res_data=>返回信息
     */
    public function checkSignSms($oBaofoo,$validatecode) {
        // 获取配置文件
        $this->channelId = $oBaofoo->channel_id;
        $this->orderId = $oBaofoo->payorder_id;
		$bindId = $oBaofoo->bind_id;
        $this->config = $this->getCfg();
        if (!$this->channelId || !$this->orderId || !$validatecode || !$this->config){
            return StdError::returnStdError("1630001","参数错误");
        }
        // 获取签约信息
        $oBfSign = new BfSign();
        $oSignInfo = $oBfSign->getOne($bindId);
        if(!$oSignInfo || !$oSignInfo->pre_sign_code){
            Logger::dayLog('bfxy', 'checkSignSms_Fail: 获取用户银行卡签约信息失败,主订单id为:'.$this->orderId);
            return StdError::returnStdError($this->channelId,"0003");
        }
		if(!empty($oSignInfo->sign_code)){
			if($oBaofoo->payorder->smscode != $validatecode){
				return StdError::returnStdError($this->channelId,"0008");
			}
			// 校验己方验证码并返回成功
			return ['res_code' => 0,'res_data' =>'success'];
		}
        $unique_code = $oSignInfo->pre_sign_code.'|'.$validatecode;
        $checkRes = $this->doCheckMsg($unique_code);
        if(!$checkRes){
            return StdError::returnStdError($this->channelId,"0018");
        }
        // 保存短信校验数据并返回
        if (!$oBfSign->saveSignInfo($oSignInfo,$checkRes)) {
            return StdError::returnStdError($this->channelId,"0018");
        }
        // 判断请求结果返回第三方信息
        if($checkRes['error_code'] != '0000'){
            return StdError::returnStdError($this->channelId,"0018");
        }
        return ['res_code' => 0,'res_data' =>'success'];
    }

    /**
     * 协议支付
     * @param int $payOrderId 主订单id
     * @param int $channelId 通道id
     * @param int $bindId 签约表id
     * @return array res_code=>返回码 res_data=>返回信息
     */
    public function pay($payOrderId,$channelId,$bindId) {
        // 获取配置文件
        $this->channelId = $channelId;
        $this->orderId = $payOrderId;
        $this->config = $this->getCfg();
        if (!$payOrderId || !$channelId || !$bindId || !$this->config){
            return StdError::returnStdError("1630001","参数错误");
        }
        // 获取签约信息
        $oBfSign = new BfSign();
        $oSignInfo = $oBfSign->getOne($bindId);
        if(!$oSignInfo){
            Logger::dayLog('bfxy', 'pay_Fail: 获取用户银行卡签约信息失败,主订单id为:'.$this->orderId);
            return StdError::returnStdError($this->channelId,"0003");
        }
        // 获取订单信息
        $oBfXYOrder = new BfXYOrder();
        $oOrderInfo = $oBfXYOrder->getOne($payOrderId,'payorder_id');
        if(!$oOrderInfo){
            Logger::dayLog('bfxy', 'pay_Fail: 获取用户订单信息失败,主订单id为:'.$this->orderId);
            return StdError::returnStdError($this->channelId,"0003");
        }
        // 对订单加锁
        if(!$oOrderInfo->saveStatus(Payorder::STATUS_DOING)){
            Logger::dayLog('bfxy', 'pay_Log: 订单加锁失败,主订单id为:'.$this->orderId);
        }
        return $this->doPay($oOrderInfo->attributes,$oSignInfo->attributes);
    }

    /**
     * 获取请求宝付的公共参数
     * @param string $code 宝付请求的交易类型
     * @return array 失败返回[] 成功返回公共参数数组
     */
    private function getCommomParam($code){
        if(!$this->config){
            return [];
        }
        if(!array_key_exists($code, $this->config['txn_type'])){
            return [];
        }
        $time = time();
        $commonData = [
            'send_time' => date('Y-m-d H:i:s',$time),
            'version' => $this->config['version'],
            'terminal_id' => $this->config['terminal_id'],
            'txn_type' => $this->config['txn_type'][$code],
            'member_id' => $this->config['member_id'],
            'msg_id' => md5($code.$time.(Func::randStr(20)))
        ];
        // 订单查询接口不需要生成数字信封
        if($code != 'check_pay'){
            $BfRsa = new BFRSA($this->config["pfxfilename"], $this->config["cerfilename"], $this->config["private_key_password"]);
            $commonData['dgtl_envlp'] = $BfRsa->encryptByCERFile("01|".$this->config['aes_key']);
        }
        return $commonData;
    }

    /**
     * 执行验证码发送
     * @param string $cliIdentityId 用户相对于宝付的唯一编号
     * @param string $cardType 银行卡类型 101[借记卡] 102[信用卡]
     * @param string $cardInfo 银行卡账户信息
     * @return array 失败返回[] 成功返回签名表数据信息
     */
    private function doSendMsg($cliIdentityId,$cardType,$cardInfo){
        if(!$this->config || !$cliIdentityId || !$cardType || !$cardInfo){
            return [];
        }
        // 获取公共请求参数
        $sendCommon = self::getCommomParam('ready_sign');
        if(!$sendCommon){
            return [];
        }
        // 获取业务请求参数
        $sendData['user_id'] = $cliIdentityId;
        $sendData['card_type'] = $cardType;
        $sendData['id_card_type'] = $this->config['id_card_type']['id_card'];
        $sendData['acc_info'] = AESUtil::AesEncrypt(base64_encode($cardInfo), $this->config['aes_key']);
        // 合并请求数据
        $data = array_merge($sendCommon,$sendData);
        // 对请求数据进行签名
        $SHA1Sign = openssl_digest(urldecode($this->sortAndOutString($data)), "SHA1");
        $sign = SignatureUtils::Sign($SHA1Sign,$this->config["pfxfilename"],$this->config["private_key_password"]);
        $data["signature"]=$sign;
        Logger::dayLog('bfxy', 'sendSignSms_Log: 请求信息为:'. json_encode($data).',主订单id为:'.$this->orderId);
        // 发送请求数据
        $sendRes = $this->HttpClientPost($data,$this->config['apiUrl']);
        Logger::dayLog('bfxy', 'sendSignSms_Log: 宝付返回信息为:'. $sendRes.',主订单id为:'.$this->orderId);
        // 校验返回信息生成签名表数据信息
        $envlpData = $this->checkEnvlpData($sendRes);
        $saveData = $this->getModelArr($envlpData);
        if(!$saveData){
            return [];
        }
        $saveData['pre_sign_msg'] = $data['msg_id'];
        unset($saveData['sign_code']);
        return $saveData;
    }

    /**
     * 执行验证码校验
     * @param string $unique_code 预签约唯一信息[ 预签约入库唯一码|用户验证码 ]
     * @return array 失败返回[] 成功返回签名表数据信息
     */
    private function doCheckMsg($unique_code){
        if(!$this->config || !$unique_code){
            return [];
        }
        // 获取公共请求参数
        $sendCommon = self::getCommomParam('confirm_sign');
        if(!$sendCommon){
            return [];
        }
        // 获取业务请求参数
        Logger::dayLog('bfxy', 'checkSignSms_Log: 预签约唯一信息为:'.$unique_code.',主订单id为:'.$this->orderId);
        $sendData['unique_code'] = AESUtil::AesEncrypt(base64_encode($unique_code), $this->config['aes_key']);
        // 合并请求数据
        $data = array_merge($sendCommon,$sendData);
        // 对请求数据进行签名
        $SHA1Sign = openssl_digest(urldecode($this->sortAndOutString($data)), "SHA1");
        $sign = SignatureUtils::Sign($SHA1Sign,$this->config["pfxfilename"],$this->config["private_key_password"]);
        $data["signature"] = $sign;
        Logger::dayLog('bfxy', 'checkSignSms_Log: 请求信息为:'. json_encode($data).',主订单id为:'.$this->orderId);
        // 发送请求数据
        $sendRes = $this->HttpClientPost($data,$this->config['apiUrl']);
        Logger::dayLog('bfxy', 'checkSignSms_Log: 宝付返回信息为:'. $sendRes.',主订单id为:'.$this->orderId);
        // 校验返回信息生成签名表数据信息
        $envlpData = $this->checkEnvlpData($sendRes);
        $saveData = $this->getModelArr($envlpData);
        if(!$saveData){
            return [];
        }
        $saveData['sign_msg'] = $data['msg_id'];
        unset($saveData['pre_sign_code']);
        return $saveData;
    }

    /**
     * 执行支付操作
     * @param array $orderInfo 子订单信息
     * @param array $signInfo 签约表信息
     * @return array res_code=>返回码 res_data=>返回信息
     */
    private function doPay($orderInfo,$signInfo){
        if(!$orderInfo || !$signInfo){
            return StdError::returnStdError($this->channelId,"0001");
        }
        // 获取公共请求参数
        $sendCommon = self::getCommomParam('query_pay');
        if(!$sendCommon){
            return false;
        }
        // 校验银行卡类型
        $cardType = ArrayHelper::getValue($signInfo, 'bankcard_type','100');
        if(!in_array($cardType,$this->config['card_type'])){
            Logger::dayLog('bfxy', 'pay_Fail: 银行卡类型错误导致支付失败,主订单id为:'.$this->orderId);
            return StdError::returnStdError($this->channelId,"0025");
        }
        // 获取信用卡数据信息
        if($cardType == $this->config['card_type']['credit_card']){
            $card_date = ArrayHelper::getValue($signInfo, 'card_date','');
            $card_cvv2 = ArrayHelper::getValue($signInfo, 'card_cvv2','');
            if(!$card_date || !$card_cvv2){
                Logger::dayLog('bfxy', 'pay_Fail: 信用卡信息缺失导致支付失败,主订单id为:'.$this->orderId);
                return StdError::returnStdError($this->channelId,"0026");
            }
            $sendData['card_info'] = AESUtil::AesEncrypt(base64_encode($card_date.'|'.$card_cvv2), $this->config['aes_key']);
        }
        // 获取业务参数
        $sendData['user_id'] = ArrayHelper::getValue($signInfo, 'cli_identityid','');
        $sendData['protocol_no'] = AESUtil::AesEncrypt(base64_encode(ArrayHelper::getValue($signInfo, 'sign_code','')), $this->config['aes_key']);
        $sendData['trans_id'] = ArrayHelper::getValue($orderInfo, 'orderid','');
        $sendData['txn_amt'] = ArrayHelper::getValue($orderInfo, 'amount','');
        $sendData['return_url'] = $this->config['return_url'].'?channelId='.$this->channelId;
        $sendData["risk_item"]= json_encode(["goodsCategory"=>"02"]);//加入风控参数(固定为JSON字串) "行业类别"=>"互金消金"
        // 合并请求数据
        $data = array_merge($sendCommon,$sendData);
        // 对请求数据进行签名
        $SHA1Sign = openssl_digest(urldecode($this->sortAndOutString($data)), "SHA1");
        $sign = SignatureUtils::Sign($SHA1Sign,$this->config["pfxfilename"],$this->config["private_key_password"]);
        $data["signature"]=$sign;
        Logger::dayLog('bfxy', 'pay_Log: 请求信息为:'. json_encode($data).',主订单id为:'.$this->orderId);
        // 发送请求数据
        $sendRes = $this->HttpClientPost($data,$this->config['apiUrl']);
        Logger::dayLog('bfxy', 'pay_Log: 宝付返回信息为:'. $sendRes.',主订单id为:'.$this->orderId);
        // 检查支付返回信息
        $checkPayRes = $this->checkPayRes($sendRes);
        if(!ArrayHelper::getValue($checkPayRes, 'res_code')){
            Logger::dayLog('bfxy', 'pay_Success: 订单支付成功,主订单id为:'.$this->orderId);
        }
        return $checkPayRes;
    }

    /**
     * 检查返回数据格式及验签结果
     * @param string $sendRes 宝付返回信息
     * @return array 失败返回[] 成功返回宝付信息的数组格式
     */
    private function checkSignString($sendRes){
        // 校验返回信息
        if(!$sendRes){
            Logger::dayLog('bfxy', 'parseReturnData_Fail: 返回信息为空,主订单id为:'.$this->orderId);
            return [];
        }
        parse_str($sendRes,$returnData);//参数解析
        if(!array_key_exists("signature",$returnData)){
            Logger::dayLog('bfxy', 'parseReturnData_Fail: 返回信息未签名,主订单id为:'.$this->orderId);
            return [];
        }
        // 数据验签
        $sign=$returnData["signature"];
        unset($returnData["signature"]);
        $returnSignSHA1 = openssl_digest(urldecode($this->sortAndOutString($returnData)), "SHA1");
        if(!SignatureUtils::VerifySign($returnSignSHA1, $this->config['cerfilename'],$sign)){
            Logger::dayLog('bfxy', 'parseReturnData_Fail: 返回信息签名错误,主订单id为:'.$this->orderId);
            return [];
        }
        return $returnData;
    }

    /**
     * 检查预签约和签约时的返回数据
     * @param string $sendRes 宝付返回信息
     * @return array 失败返回[] 成功返回宝付信息的数组格式
     */
    private function checkEnvlpData($sendRes){
        // 校验签名字符串
        $returnData = $this->checkSignString($sendRes);
        if(!$returnData){
            return [];
        }
        // 数据成功校验
        if(!array_key_exists("resp_code",$returnData)){
            Logger::dayLog('bfxy', 'parseReturnData_Fail: 返回信息格式错误,缺少返回码信息,主订单id为:'.$this->orderId);
            return [];
        }
        if($returnData["resp_code"] != "S"){
            Logger::dayLog('bfxy', 'parseReturnData_Fail: 返回信息标识发送失败,主订单id为:'.$this->orderId);
            return $returnData;
        }
        if(!array_key_exists("dgtl_envlp",$returnData)){
            Logger::dayLog('bfxy', 'parseReturnData_Fail: 返回信息格式错误,缺少数据标识,主订单id为:'.$this->orderId);
            return $returnData;
        }
        // 解析返回的数据信息
        $BfRsa = new BFRSA($this->config["pfxfilename"], $this->config["cerfilename"], $this->config["private_key_password"]);
        $returnDgtlEnvlp = $BfRsa->decryptByPFXFile($returnData["dgtl_envlp"]);
        $returnAesKey = $this->getAesKey($returnDgtlEnvlp);
        if(!$returnAesKey){
            Logger::dayLog('bfxy', 'parseReturnData_Fail: 返回信息格式错误,缺少AES加密秘钥,主订单id为:'.$this->orderId);
            return $returnData;
        }
        if(array_key_exists("unique_code",$returnData)){
            $returnData['unique_code'] = base64_decode(AESUtil::AesDecrypt($returnData["unique_code"], $returnAesKey));
        }
        if(array_key_exists("protocol_no",$returnData)){
            $returnData['protocol_no'] = base64_decode(AESUtil::AesDecrypt($returnData["protocol_no"], $returnAesKey));
        }
        Logger::dayLog('bfxy', 'parseReturnData_Log: 宝付返回的明文信息为'. json_encode($returnData).',主订单id为:'.$this->orderId);
        return $returnData;
    }

    /**
     * 校验支付结果 [同步通知,异步回调,定时补单均使用该方法检查]
     * @param string $payRes 宝付返回信息
     * @param int $channelId 异步回调时需要根据该参数获取配置信息
     * @return array res_code=>返回码 res_data=>返回信息
     * @see $channelId 仅有异步回调时传入该参数,以此区分异步与同步[补单]的区别
     */
    public function checkPayRes($payRes,$channelId = 0){
        $this->channelId = $channelId?$channelId:$this->channelId;
        $this->config = $this->getCfg();
        if(!$payRes || !$this->channelId || !$this->config){
            return StdError::returnStdError("1630001","参数错误");
        }
        // 校验返回信息
        $bizData = $this->checkSignString($payRes);
        if(!$bizData){
            return StdError::returnStdError($this->channelId,"0020");
        }
        // 异步需要先校验订单是否存在并对成员属性订单Id赋值
        if($channelId){
            $oBfOrder = $this->getBfOrder($bizData);
            $this->orderId = $oBfOrder->payorder_id;
        }else{
            $oBfOrder = (new BfXYOrder())->getOne($this->orderId,'payorder_id');
        }
        // 校验返回码
        $bfOrderId = ArrayHelper::getValue($bizData, 'order_id','');
        $bizCode = ArrayHelper::getValue($bizData, 'biz_resp_code','');
        $bizMsg = ArrayHelper::getValue($bizData, 'biz_resp_msg','');
        $oCBank = new CBack();
        if($bizCode != '0000'){
            if(in_array($bizCode, $this->bfQueryCode)){
                Logger::dayLog('bfxy', 'checkPayRes_Log: 订单因第三方返回需等待回调补单操作导致锁死,主订单Id为:'.$this->orderId);
                return StdError::returnStdError($this->channelId,"0017",$oBfOrder->payorder->clientBackurl());
            }
            Logger::dayLog('bfxy', 'checkPayRes_Fail: 订单因第三方返回失败导致支付失败,主订单Id为:'.$this->orderId);
            if($oBfOrder->savePayFail($bizCode,$bizMsg,$bfOrderId)){
                $oCBank->clientNotify($oBfOrder);
            }
            return StdError::returnStdError($this->channelId,"0009",$bizMsg);
        }
        // 校验订单金额
        if( ArrayHelper::getValue($bizData, 'succ_amt',0) != ArrayHelper::getValue($oBfOrder, 'amount',0)){
            Logger::dayLog('bfxy', 'checkPayRes_Fail: 订单因金额不一致导致支付失败,主订单Id为:'.$this->orderId);
            if($oBfOrder->savePayFail($bizCode,'订单金额不一致',$bfOrderId)){
                $oCBank->clientNotify($oBfOrder);
            }
            return StdError::returnStdError($this->channelId,"0023");
        }
        // 保存订单信息
        if(!$oBfOrder->savePaySuccess($bfOrderId)){
            Logger::dayLog('bfxy', 'checkPayRes_Fail: 订单状态修改失败,主订单Id为:'.$this->orderId);
            return StdError::returnStdError($this->channelId,"0012");
        }
        $oCBank->clientNotify($oBfOrder);
        return ['res_code' => 0,'res_data' => $oBfOrder->payorder->clientBackurl()];
    }

    /**
     * 对数组进行排序输出为字符串
     * @param array $data 转换的数组
     * @return string 去除空值后排序输出的字符串
     */
    private function sortAndOutString($data) {
        $TempData = array();
        foreach ($data As $Key => $Value){
            if(!empty($Value)){
                $TempData[$Key] = $Value;
            }
        }
        ksort($TempData);//排序
        return http_build_query($TempData);
    }

    /**
     * 发送Post请求
     * @param array $data post请求参数数组
     * @param string $url post请求地址
     * @return string 失败返回'' 成功返回宝付的响应数据
     */
    private function HttpClientPost($data,$url){
        if(!$this->channelId){
            return '';
        }
        return (new BaofooApi("prod".$this->channelId))->HttpClientPost($url,$data);
    }

    /**
     * 获取Aes加密的key
     * @param string $Strings 解析数字信封后生成的字符串
     * @return string 失败返回'' 成功返回宝付返回的aesKey
     */
    private function getAesKey($Strings){
        $KeyArray = explode("|",$Strings);
        if(count($KeyArray) == 2){
            if(!empty(trim($KeyArray[1]))){
                return $KeyArray[1];
            }else{
                return '';
            }
        }else{
            return '';
        }
    }

    /**
     * 将返回信息数组的key转化为数据库字段名
     * @param array 宝付返回的数据数组
     * @return array  失败返回[] 成功返回由数组字段组成key的数组
     */
    private function getModelArr($data){
        if(!$data){
            return [];
        }
        $return['error_code']= ArrayHelper::getValue($data, 'biz_resp_code','');
        $return['error_msg']= ArrayHelper::getValue($data, 'biz_resp_msg','');
        $return['pre_sign_code']= ArrayHelper::getValue($data, 'unique_code','');
        $return['sign_code']= ArrayHelper::getValue($data, 'protocol_no','');
        return $return;
    }

    /**
     * 异步回调获取订单对象并校验支付状态
     * @param array $bizData 宝付返回数组
     * @return object 宝付子订单对象
     * @see 因为是回调操作,校验失败后可以直接die()返回
     */
    private function getBfOrder($bizData){
        if(!$bizData){
            die('Again');
        }
        $returnOrderId = ArrayHelper::getValue($bizData, 'trans_id','');
        if(!$returnOrderId){
            die('Again');
        }
        $oBfOrder = (new BfXYOrder())->getOne($returnOrderId,'orderid');
        if(!$oBfOrder){
			Logger::dayLog('bfxy', 'backpay_Log: 宝付的回调订单不存在');
            die('Again');
        }
        if(in_array($oBfOrder->status, [Payorder::STATUS_PAYOK, Payorder::STATUS_PAYFAIL])){
            Logger::dayLog('bfxy', 'backpay_Log: 宝付的回调订单已变更为终态,主订单id为:'.$oBfOrder->payorder_id);
            die('OK');
        }
        return $oBfOrder;
    }

    /**
     * 协议支付补单任务
     * @param string $start_time 开始时间
     * @param string $end_time 结束时间
     * @param int $limit 查询条数
     * @return int 成功数量
     */
    public function xyPayQuery($start_time,$end_time,$limit=100){
        $model = new BfXYOrder();
        $dataList = $model->getProcessList($start_time,$end_time,$limit);
        if(!$dataList){
            Logger::dayLog('bfxy', 'xyPayQuery_Log: 协议支付补单操作未获取到查询数据');
            return 0;
        }
        // 逐条处理
        $success = 0;
        foreach ($dataList as $oBfXY) {
            $result = $this->askPayRes($oBfXY);
            if ($result){
                Logger::dayLog('bfxy', 'xyPayQuery_Success: 协议支付补单成功,主订单id为:'.$oBfXY->payorder_id);
                $success++;
            }
        }
        // 返回结果
        return $success;
    }

    /**
     * 协议支付逐条处理补单数据
     * @param object $oBfXY 宝付支付的子订单对象
     * @return boolean 补单查询支付成功返回ture 否则返回false
     */
    public function askPayRes($oBfXY){
        // 条件判断
        if(!$oBfXY || ArrayHelper::getValue($oBfXY, 'status') != Payorder::STATUS_DOING){
            return false;
        }
        // 获取配置文件
        $this->orderId = ArrayHelper::getValue($oBfXY, 'payorder_id','');
        $this->channelId = ArrayHelper::getValue($oBfXY, 'channel_id','');
        $this->config = $this->getCfg();
        if(!$this->orderId || !$this->channelId || !$this->config){
            return false;
        }
        // 获取请求参数
        $sendCommon = $this->getCommomParam('check_pay');
        if(!$sendCommon){
            return false;
        }
        $sendData['orig_trans_id'] = ArrayHelper::getValue($oBfXY, 'orderid','');
        $sendData['orig_trade_date'] = ArrayHelper::getValue($oBfXY, 'create_time','');
        $data = array_merge($sendCommon,$sendData);
        // 对请求数据进行签名
        $SHA1Sign = openssl_digest(urldecode($this->sortAndOutString($data)), "SHA1");
        $sign = SignatureUtils::Sign($SHA1Sign,$this->config["pfxfilename"],$this->config["private_key_password"]);
        $data["signature"] = $sign;
        Logger::dayLog('bfxy', 'askPay_Log: 请求信息为:'. json_encode($data).',主订单id为:'.$this->orderId);
        // 发送请求数据
        $askRes = $this->HttpClientPost($data,$this->config['apiUrl']);
        Logger::dayLog('bfxy', 'askPay_Log: 宝付返回信息为:'. $askRes.',主订单id为:'.$this->orderId);
        // 校验支付结果
        $checkRes = $this->checkPayRes($askRes);
        if(ArrayHelper::getValue($checkRes, 'res_code')){
            return false;
        }
        return true;
    }
}
