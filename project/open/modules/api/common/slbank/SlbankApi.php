<?php
/**
 * 数立数据交互接口
 * @author 孙瑞
 */
namespace app\modules\api\common\slbank;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\common\Func;
use app\models\GatherResult;
use app\models\slbank\SlbankRequest;
use app\models\slbank\SlbankNotify;
use app\modules\api\common\slbank\SlbankNotify as Notify;

class SlbankApi{

    private $config=array();
    private $retryCode = array(40000,40001,40002,40003,42201,10012,10013,54036);
    /**
     * 获取配置文件
     * @param $cfg
     * @return mixed
     * @throws \Exception
     */
    private function getConfig(){
        $is_prod = SYSTEM_PROD ? true : false;
        $cfg = $is_prod ? "prod" : 'dev';
        $configPath = __DIR__ . DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."{$cfg}.php";
        if (!file_exists($configPath)) {
            throw new \Exception($configPath . "配置文件不存在", 100);
        }
        $config = include $configPath;
        $this->config = $config;
    }

    /**
     * 获取数立授权H5页面Url
     * @param string $show_url 授权登录后的回显地址
     * @param int $requestid 请求入库后生产的主键id
     */
    public function getH5Url($show_url,$requestid){
        if(!$show_url || !$requestid){
            return [];
        }
        try {
            // 获取配置文件
            $this->getConfig();
            // 获取公共请求参数
            $sendData = $this->getCommonParams();
            // 获取定制请求参数
            $sendData['api'] = ArrayHelper::getValue($this->config,'getAuthApi');
            $sendData['notifyUrl'] = $show_url;
            $sendData['encKey'] = $this->rsaEncrypt(ArrayHelper::getValue($this->config,'encKey'));
            $sendData['encType'] = ArrayHelper::getValue($this->config,'encType');
            // 获取业务数据参数
            $bizContent['orgBizNo'] = $this->getOrgBizNo((string)$requestid);
            $bizContent['prodCode'] = ArrayHelper::getValue($this->config,'prodCode');
            $sendData['bizContent'] = $this->encrypt(json_encode($bizContent));
            $sendData['sign'] = $this->sign($sendData);
            $params = $this->buildQuery($sendData);
            $url = ArrayHelper::getValue($this->config,'gateway') . '?' . $params;
            return ['requestid' => $requestid, 'jump_url' => $url, 'org_biz_no' => $bizContent['orgBizNo']];
        } catch (\Exception $ex) {
            return [];
        }
    }

    /**
     * 保存银行流水数据[生成json文件,写入结果数据库,修改请求状态]
     * @param array $requestData 请求表数据数组
     * @return int 2失败 11成功 string 重试原因
     */
    public function saveBankTurnover($requestData){
        $aid = ArrayHelper::getValue($requestData, 'aid');
        $bizNo = ArrayHelper::getValue($requestData, 'biz_no');
        $requestid = ArrayHelper::getValue($requestData, 'id');
        $userId = ArrayHelper::getValue($requestData, 'user_id');
        if(!$aid || !$bizNo || !$requestid || !$userId){
            return '采集数据参数不全';
        }
        Logger::dayLog('slbank/Collect', 'logging 开始获取id为'.$requestid.'的请求的数据');
        // 获取配置文件
        $this->getConfig();
        // 获取公共请求参数
        $sendData = $this->getCommonParams();
        // 获取定制请求参数
        $sendData['api'] = ArrayHelper::getValue($this->config,'getResultApi');
        // 获取业务数据参数
        $bizContent['prodCode'] = ArrayHelper::getValue($this->config,'prodCode');
        $bizContent['bizNo'] = $bizNo;
        $sendData['bizContent'] = json_encode($bizContent);
        $sendData['sign'] = $this->sign($sendData);
        // 请求数立获取采集数据
        $jsonData = (new Notify())->curlPost(ArrayHelper::getValue($this->config,'gateway'), $sendData);
        $jsonData = strtolower($jsonData);
        Logger::dayLog('slbank/Collect', 'logging 已完成对id为'.$requestid.'的请求的数据获取');
        // 获取数据返回码及子码
        $arrData = $this->parseJson($jsonData);
        $codeArr = $this->parseJson(ArrayHelper::getValue($arrData,'data'));
        $resultCode = ArrayHelper::getValue($codeArr,'code');
        $resultSubCode = ArrayHelper::getValue($codeArr,'subcode');
        if(is_null($resultCode) || is_null($resultSubCode) || $resultCode>0){
            // 返回code符合重试设置
            if(in_array($resultCode, $this->retryCode) || in_array($resultSubCode, $this->retryCode)){
                (new SlbankRequest())->changeRequestStatus($requestid,SlbankRequest::STATUS_RETRY,'第三方提示重新采集数据');
                return false;
            }
            // 返回code符合失败设置
            if($resultCode>0){
                $reason = json_encode($codeArr);
            }else{
                $reason = '采集数据Json格式错误';
            }
            $this->saveRequestStatus($requestid,SlbankRequest::STATUS_FAILURE,$reason);
            Logger::dayLog('slbank/Collect', 'failure id为'.$requestid.'的请求新建失败通知成功');
            return SlbankRequest::STATUS_FAILURE;
        }
        // 保存json数据文件
        Logger::dayLog('slbank/Collect', 'logging 开始进行对id为'.$requestid.'的json数据的保存');
        $userInfo = $this->getUserInfo(ArrayHelper::getValue($codeArr, 'bizcontent'));
        $saveResult = $this->saveJsonData($jsonData,$requestid,$aid,$userId,ArrayHelper::getValue($userInfo, 'mobile'));
        if(!$saveResult){
            $this->saveRequestStatus($requestid,SlbankRequest::STATUS_FAILURE,'保存json文件失败',$userInfo);
            Logger::dayLog('slbank/Collect', 'failure id为'.$requestid.'的请求新建失败通知成功');
            return SlbankRequest::STATUS_FAILURE;
        }
        Logger::dayLog('slbank/Collect', 'logging id为'.$requestid.'的json数据的保存成功');
        // 采集成功修改状态
        $this->saveRequestStatus($requestid,SlbankRequest::STATUS_SUCCESS,'结果数据采集成功',$userInfo);
        Logger::dayLog('slbank/Collect', 'success id为'.$requestid.'的请求新建成功通知成功');
        return SlbankRequest::STATUS_SUCCESS;
    }

    /**
     * 构建公共请求参数
     * @return array
     */
    private function getCommonParams(){
        $data['orgCode'] = ArrayHelper::getValue($this->config,'orgCode');
        $data['bizType'] = ArrayHelper::getValue($this->config,'bizType');
        $data['charset'] = ArrayHelper::getValue($this->config,'charset');
        $data['signType'] = ArrayHelper::getValue($this->config,'signType');
        $data['version'] = ArrayHelper::getValue($this->config,'version');
        $data['sdkVersion'] = ArrayHelper::getValue($this->config,'sdkVersion');
        $data['timestamp'] = $this->getMillisecond();
        return $data;
    }

    // 获取服务器时间戳（精确到毫秒）
    private function getMillisecond() {
        list($s1, $s2) = explode(' ', microtime());
        return (float) sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

    /**
     * 平台公钥加密AES_KEY值
     * @param string $encKey
     * @return string
     */
    private function rsaEncrypt($encKey){
        $slopsRsaPubKeyFilePath = ArrayHelper::getValue($this->config,'slopsRsaPubKeyFilePath');
        $slopsRsaPubKeyContent = file_get_contents($slopsRsaPubKeyFilePath);
        openssl_public_encrypt($encKey, $crypted, $slopsRsaPubKeyContent);
        $crypted = base64_encode($crypted);
        return $crypted;
    }

    /**
     * 生成Orgbizno
     * @param string $requestid
     * @return string
     */
    private function getOrgBizNo($requestid){
        $no = 'SHULI'.date('YmdHis').(strlen($requestid)>5?substr($requestid,0,5): str_pad($requestid,5,0,STR_PAD_LEFT));
        return $no;
    }

    /**
     * AES加密
     * @param json $bizContent
     * @return string
     */
    private function encrypt($bizContent){
        Logger::dayLog('slbank/Request','index/logging 记录业务参数信息 bizContent:'.$bizContent);
        if(!$bizContent){
            return '';
        }
        $bizContent = trim($bizContent);
        $encKey = ArrayHelper::getValue($this->config,'encKey');
        @$size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        // PKCS5Padding
        $padding = $size - strlen($bizContent) % $size;
        // 添加Padding
        $bizContent .= str_repeat(chr($padding), $padding);
        @$module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        @$encKey = $this->substr(base64_decode($encKey), 0, mcrypt_enc_get_key_size($module));
        $iv = str_repeat("\0", $size);
        // 初始化加密解密模块
        @mcrypt_generic_init($module, $encKey, $iv);
        // 加密数据
        @$encrypted = mcrypt_generic($module, $bizContent);
        // 结束并返回加密结果
        @mcrypt_generic_deinit($module);
        @mcrypt_module_close($module);
        return base64_encode($encrypted);
    }
    /**
     * RSA签名
     * @param $params
     * @return string
     */
    private function sign($params){
        ksort($params);
        $tmp = [];
        foreach ($params as $k => $v) {
            $tmp[] = "{$k}={$v}";
        }
        $param = implode('&', $tmp);
        $privateKeyFilePath = ArrayHelper::getValue($this->config,'privateKeyFilePath');
        $privateKeyContent = file_get_contents($privateKeyFilePath);
		$privateKey = openssl_pkey_get_private($privateKeyContent);
		openssl_sign($param, $sign, $privateKey);
        $sign = base64_encode($sign);
        return $sign;
    }

    // 重写substr方法增加对mbstring的判断
    private function substr($string, $start, $length){
        return extension_loaded('mbstring') ? mb_substr($string, $start, $length, '8bit') : substr($string, $start, $length);
    }

    /**
     * 构建请求参数
     * @param $para
     * @return string
     */
    private function buildQuery($params){
        ksort($params);
        $arg = "";
        while (list ($key, $val) = each($params)) {
            $arg .= $key . "=" . urlencode($val) . "&";
        }
        //去掉最后一个&字符
        $arg = substr($arg, 0, count($arg) - 2);
        //如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }
        return $arg;
    }

    /**
	 * json数据解析
	 */
	private function parseJson($content){
        if(is_null($content)){
            return null;
        }
		$arr = json_decode($content,true);
		$err = json_last_error();
		if($err){
			return null;
		}else{
			return $arr;
		}
	}

    // 保存请求数据状态
    private function saveRequestStatus($requestid,$status,$codeJson,$userInfo=[]){
        $oSlbankRequest = new SlbankRequest();
        $oSlbankNotify = new SlbankNotify();
        $changeResult = $oSlbankRequest->changeRequestStatus($requestid, $status, $codeJson,$userInfo);
        if(!$changeResult){
            return false;
        }
        $insertResult = $oSlbankNotify->addNotify($requestid, $status);
        if(!$insertResult){
            return false;
        }
        return TRUE;
    }

    // 保存json数据
    private function saveJsonData($data,$requestid,$aid,$userId,$mobile){
        // 保存json数据文件
        $path = '/../../openapi_ofiles/openapi/slbank/'.date('Ym/d/').$requestid.'.json';
        $filePath = Yii::$app->basePath.'/web'.$path;
        Func::makedir(dirname($filePath));
        file_put_contents($filePath, $data);
        //访问路径
        $path = '/ofiles/openapi/slbank/'.date('Ym/d/').$requestid.'.json';
        // 保存json文件路径到数据库中
        $oGatherResult = new GatherResult();
        $saveData['aid'] = $aid;
        $saveData['source'] = $oGatherResult::STATUS_SHULIBANK;
        $saveData['request_id'] = $requestid;
        $saveData['user_id'] = $userId;
        $saveData['mobile'] = $mobile;
        $saveData['data_url'] = $path;
        return $oGatherResult->saveData($saveData);
    }

    /**
     * 破解JSON数据获取用户个人信息
     * @param string $bizContent 业务数据串
     * @return array $return 用户信息
     */
    private function getUserInfo($bizContent){
        $return = ['name'=>'','mobile'=>'','idcard'=>''];
        if(!$bizContent){
            return $return;
        }
        $content = $this->parseJson($bizContent);
        $dataInfo = $this->parseJson($content['bizcontent']);
        if(is_null($dataInfo) || !!empty($dataInfo['bank_person_basic_info'])){
            return $return;
        }
        $name = ArrayHelper::getValue($dataInfo['bank_person_basic_info'], 'name','');
        $telephone = ArrayHelper::getValue($dataInfo['bank_person_basic_info'], 'telephone','');
        $idcard = ArrayHelper::getValue($dataInfo['bank_person_basic_info'], 'idcard','');
        $return['name'] = is_null($name) || $name=='null'?'':$name;
        $return['mobile'] = is_null($telephone) || $telephone=='null'?'':$telephone;
        $return['idcard'] = is_null($idcard) || $idcard=='null'?'':$idcard;
        return $return;
    }
}