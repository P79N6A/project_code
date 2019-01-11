<?php

namespace app\commonapi;

use Yii;
class Apidepository {

    private $domain;
    private $repayDomain;

    public function __construct() {
        $this->domain = Yii::$app->params['yaoyuefuDomain'];
        $this->repayDomain = Yii::$app->params['exchange_url'];
    }

    /**
     * post方式请求
     * @param $url
     * @param $data
     * @return mixed
     */
    public function httpPost($url, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSLVERSION, 3);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_URL, $url);
        $ret = curl_exec($ch);

        curl_close($ch);
        return $ret;
    }

    /**
     * 开户增强
     * @param $params
     * @return mixed
     */
    public function openplus($params) {
        $url = 'account/openplus';
        $apiSign = new ApiSign();
        $postData = $apiSign->signData($params);
        $res = $this->httpPost($this->domain . $url, $postData);
        Logger::dayLog('api/depository/openplus', $res, $params);
        $result = json_decode($res, true);
        if (!is_array($result) || !$result['data']) {
            return false;
        }
        $data = json_decode($result['data'], true);
        if ($data['res_code'] != 0) {
            return false;
        }
        return $data['res_data'];
    }

    /**
     * 发送验证码
     * @param $params
     * @return bool
     */
    public function sendmsg($params) {
        if ($params['srvTxCode'] == 'cardBindPlus') {
            $params['reqType'] = strval(2);
        }
        $url = 'sms/sendmsg';
        $apiSign = new ApiSign();
        $postData = $apiSign->signData($params);
        $res = $this->httpPost($this->domain . $url, $postData);
        Logger::dayLog('api/depository/sendmsg', $res, $params);
        $result = json_decode($res, true);
        if (!is_array($result) || !$result['data']) {
            return false;
        }
        $data = json_decode($result['data'] . "\r\n", true);
        if ($data['res_code'] != 0 || !$data['res_data']['srvAuthCode']) {
            return false;
        }
        return $data['res_data']['srvAuthCode'];
    }

    /**
     * 设置密码
     * @param $params
     * @return bool|mixed
     */
    public function pwdset($params) {
        $url = 'account/pwdset';
        $apiSign = new ApiSign();
        $postData = $apiSign->signData($params);
        $res = $this->httpPost($this->domain . $url, $postData);
        Logger::dayLog('api/depository/pwdset', $res, $params);
        $result = json_decode($res, true);
        if (!is_array($result) || !$result['data']) {
            return false;
        }
        $data = json_decode($result['data'], true);
        if ($data['res_code'] != 0) {
            return false;
        }
        return stripslashes($data['res_data']);
    }

    /**
     * 重置密码增强
     * @param $params
     * @return bool|mixed
     */
    public function pwdresetplus($params) {
        $url = 'account/pwdresetplus';
        $apiSign = new ApiSign();
        $postData = $apiSign->signData($params);
        $res = $this->httpPost($this->domain . $url, $postData);
        Logger::dayLog('api/depository/pwdresetplus', $res, $params);
        $result = json_decode($res, true);
        if (!is_array($result) || !$result['data']) {
            return false;
        }
        $data = json_decode($result['data'], true);
        if ($data['res_code'] != 0) {
            return false;
        }
        return $data['res_data'];
    }

    /**
     * 解除绑定卡
     * @param $params
     * @return mixed
     */
    public function overbind($params) {
        $url = 'bindbank/overbind';
        $apiSign = new ApiSign();
        $postData = $apiSign->signData($params);
        $res = $this->httpPost($this->domain . $url, $postData);
        Logger::dayLog('api/depository/overbind', $res, $params);
        $result = json_decode($res, true);
        if (!is_array($result) || !$result['data']) {
            return false;
        }
        $data = json_decode($result['data'], true);
        if ($data['res_code'] != 0) {
            return false;
        }
        return true;
    }

    /**
     * 绑卡增强
     * @param $params
     * @return mixed
     */
    public function binding($params) {
        $url = 'bindbank/binding';
        $apiSign = new ApiSign();
        $postData = $apiSign->signData($params);
        $res = $this->httpPost($this->domain . $url, $postData);
        Logger::dayLog('api/depository/binding', $res, $params);
        $result = json_decode($res, true);
        if (!is_array($result) || !$result['data']) {
            return false;
        }
        $data = json_decode($result['data'], true);
        if ($data['res_code'] != 0 || !$data['res_data']) {
            return false;
        }
        return true;
    }

    /**
     * 提现
     * @param $params
     * @return mixed
     */
    public function moneyout($params) {
        $url = 'withdraw/moneyout';
        $apiSign = new ApiSign();
        $postData = $apiSign->signData($params);
        $res = $this->httpPost($this->domain . $url, $postData);
        Logger::dayLog('api/depository/moneyout', $res, $params);
        $result = json_decode($res, true);
        if (!is_array($result) || !$result['data']) {
            return false;
        }
        $data = json_decode($result['data'], true);
        return $data;
    }

    /**
     * 开放平台提现
     * @param $params
     * @return mixed
     */
    public function moneyoutopen($params) {
        $url = 'withdraw';
        $apiSign = new ApiSign();
        $postData = $apiSign->signData($params);
        $postData['_sign'] = base64_encode($postData['_sign']);
        $res = $this->httpPost($this->repayDomain . $url, $postData);
        Logger::dayLog('api/depository/moneyoutopen', $res, $params);
        $result = json_decode($res, true);
        if (!is_array($result)) {
            return false;
        }
        return $result;
    }

    /**
     * 绑卡关系查询
     * @param $params
     * @return mixed
     */
    public function cardbind($params) {
        $url = 'queryall/cardbinddetailsquery';
        $params = [
            'channel' => '000002', //交易渠道   A   6   M   000001手机AP000002网页000003微信000004柜面
            'accountId' => '121312321', //电子账号
            'state' => 1, //查询状态 0-所有（默认）1-当前有效的绑定卡
        ];
        $apiSign = new ApiSign();
        $postData = $apiSign->signData($params);
        $res = $this->httpPost($this->domain . $url, $postData);
        Logger::dayLog('api/depository/cardbind', $res, $params);
        $result = json_decode($res, true);
        if (!is_array($result) || !$result['data']) {
            return false;
        }
        $data = json_decode($result['data'], true);
        if ($data['res_code'] != 0 || !$data['res_data']) {
            return false;
        }
        return $data['res_data'];
    }

    /**
     * 用户和平台签约自动投标、自动债权转让、预约提现、无密消费
     * @param $params
     * @return bool
     */
    public function auth($params) {
        $url = 'termsauth/auth';
        $apiSign = new ApiSign();
        $postData = $apiSign->signData($params);
        $res = $this->httpPost($this->domain . $url, $postData);
        Logger::dayLog('api/depository/auth', $res, $params);
        $result = json_decode($res, true);
        if (!is_array($result) || !$result['data']) {
            return false;
        }
        $data = json_decode($result['data'], true);
        if ($data['res_code'] != 0 || !$data['res_data']) {
            return false;
        }
        return stripslashes($data['res_data']);
    }

    /**
     * 3.1.3 免密提现
     * @param $params
     * @return bool
     */
    public function agreemoneyout($params) {
        $url = 'withdraw/agreemoneyout';
        $apiSign = new ApiSign();
        $postData = $apiSign->signData($params);
        $res = $this->httpPost($this->domain . $url, $postData);
        Logger::dayLog('api/depository/agreemoneyout', $res, $params);
        $result = json_decode($res, true);
        if (!is_array($result) || empty($result) || !$result['data']) {
            return ['retCode' => 'responsefail', 'retMsg' => '响应失败'];
        }
        $data = json_decode($result['data'], true);
        if ($data['res_code'] != 0 || !$data['res_data']) {
            return ['retCode' => 'responsefail', 'retMsg' => '响应失败'];
        }
        return $data['res_data'];
    }

    /**
     * 免短信开户
     * @param $params
     * @return bool
     */
    public function freeopen($params) {
        $url = 'account/freeopen';
        $apiSign = new ApiSign();
        $postData = $apiSign->signData($params);
        $res = $this->httpPost($this->domain . $url, $postData);
        Logger::dayLog('api/depository/freeopen', $res, $params);
        $result = json_decode($res, true);
        if (!is_array($result) || !$result['data']) {
            return false;
        }
        $data = json_decode($result['data'], true);
        if ($data['res_code'] != 0 || !$data['res_data']) {
            return false;
        }
        return $data['res_data'];
    }

    /**
     * 账户充值
     * @param $params
     * @return bool
     */
    public function directpayonline($params) {
        $url = 'pay/directpayonline';
        $apiSign = new ApiSign();
        $postData = $apiSign->signData($params);
        $res = $this->httpPost($this->domain . $url, $postData);
        Logger::dayLog('api/depository/directpayonline', $res, $params);
        $result = json_decode($res, true);
        if (!is_array($result) || !$result['data']) {
            return false;
        }
        $data = json_decode($result['data'], true);
        if ($data['res_code'] != 0 || !$data['res_data']) {
            return false;
        }
        return $data['res_data'];
    }

    /**
     * 资金冻结
     * @param $params
     * @return bool
     */
    public function freeze($params) {
        $url = 'frozen/freeze';
        $apiSign = new ApiSign();
        $postData = $apiSign->signData($params);
        $res = $this->httpPost($this->domain . $url, $postData);
        Logger::dayLog('api/depository/freeze', $res, $params);
        $result = json_decode($res, true);
        if (!is_array($result) || !$result['data']) {
            return false;
        }
        $data = json_decode($result['data'], true);
        if ($data['res_code'] != 0 || !$data['res_data']) {
            return false;
        }
        return $data['res_data'];
    }

    /**
     * 推送还款时间
     * @param $params
     * @return bool
     */
    public function postRepayTime($params) {
        $url = 'repay/index';
        $apiSign = new ApiSign();
        $postData = $apiSign->signData($params);
        $res = $this->httpPost($this->repayDomain . $url, $postData);
        Logger::dayLog('api/depository/postrepaytime', $res, $params);
        $result = json_decode($res, true);
        if (!is_array($result) || $result['rsp_code'] != '0000' || empty($result['rsp_data'])) {
            return false;
        }
        return $result['rsp_data'];
    }

    /**
     * 给债匹推送在贷&结清借款
     * @param $params
     * @return string
     * @author 王新龙
     * @date 2018/9/18 15:13
     */
    public function sendUserLoan($params){
        $url = 'loan/loanuser';
        $apiSign = new ApiSign();
        $postData = $apiSign->signData($params);
        $postData['_sign'] = base64_encode($postData['_sign']);
        $res = $this->httpPost($this->repayDomain . $url, $postData);
        Logger::dayLog('api/depository/senduserloan', $this->repayDomain.$url, $postData, $res);
        $result = json_decode($res, true);
        if (!$result) {
            return '{"rsp_code":"404","rsp_msg":"service error"}';
        }
        $isVerify = $apiSign->verifyData($result['data'], $result['_sign']);
        if (!$isVerify) {
            return '{"rsp_code":"200","rsp_msg":"sign error"}';
        }
        return $result['data'];
    }

    /**
     * 债匹是否能够体内还款
     * @param $params
     * @return bool
     */
    public function isrepaydebt($params) {
        $url = 'paycheck/index';
        $apiSign = new ApiSign();
        $postData = $apiSign->signData($params);
        $res = $this->httpPost($this->repayDomain . $url, $postData);
        Logger::dayLog('api/depository/isrepaydebt', $res, $params);
        $result = json_decode($res, true);
        if (is_array($result) && $result['rsp_code'] === '0000') {
            return true;
        }
        return false;
    }

    /**
     * 5天未体现推送
     * @param $params
     * @return bool
     */
    public function pushOverDate($params) {
        $url = 'prepay/index';
        $apiSign = new ApiSign();
        $postData = $apiSign->signData($params);
        $res = $this->httpPost($this->repayDomain . $url, $postData);
        Logger::dayLog('api/depository/pushoverdate', $res, $params);
        $result = json_decode($res, true);
        if (is_array($result) && $result['rsp_code'] === '0000') {
            return true;
        }
        return false;
    }

    /**
     * 页面跳转开户
     * @param $params
     * @return bool
     */
    public function khym($params) {
        $url = 'account/open';
        $apiSign = new ApiSign();
        $postData = $apiSign->signData($params);
        $res = $this->httpPost($this->domain . $url, $postData);
        Logger::dayLog('api/depository/khym', $res, $params);
        $result = json_decode($res, true);
        if (!is_array($result) || !$result['data']) {
            return false;
        }
        $data = json_decode($result['data'], true);
        if ($data['res_code'] != 0 || !$data['res_data']) {
            return false;
        }
        return $data['res_data'];
    }

    /**
     * 缴费授权 还款授权
     * @param $params
     * @return bool
     */
    public function authorize($params) {
        $url = 'termsauth/authorize';
        $apiSign = new ApiSign();
        $postData = $apiSign->signData($params);
        $res = $this->httpPost($this->domain . $url, $postData);
        Logger::dayLog('api/depository/authorize', $res, $params);
        $result = json_decode($res, true);
        if (!is_array($result) || !$result['data']) {
            return false;
        }
        $data = json_decode($result['data'], true);
        if ($data['res_code'] != 0 || !$data['res_data']) {
            return false;
        }
        return $data['res_data'];
    }

    /**
     * 绑卡（跳转页面）
     * @param $params
     * @return bool
     */
    public function bindpage($params) {
        $url = 'bindbank/bindcardpage';
        $apiSign = new ApiSign();
        $postData = $apiSign->signData($params);
        $res = $this->httpPost($this->domain . $url, $postData);
        Logger::dayLog('api/depository/bindpage', $res, $params);
        $result = json_decode($res, true);
        if (!is_array($result) || !$result['data']) {
            return false;
        }
        $data = json_decode($result['data'], true);
        if ($data['res_code'] != 0 || !$data['res_data']) {
            return false;
        }
        return $data['res_data'];
    }

    /**
     * 受托债权推送
     * @param $params
     * @return mixed
     */
    public function entrustloan($params) {
        $url = 'entrustloan';
        $apiSign = new ApiSign();
        $postData = $apiSign->signData($params);
        $postData['_sign'] = base64_encode($postData['_sign']);
        $res = $this->httpPost($this->repayDomain . $url, $postData);
        Logger::dayLog('api/depository/entrustloan', $res, $params, $this->repayDomain . $url);
        $result = json_decode($res, true);
        if (!is_array($result) || $result['rsp_code'] != '0000') {
            return false;
        }
        return $result;
    }

    /**
     * 受托支付申请
     * @param $params
     * @return mixed
     */
    public function entrustpay($params) {
        $url = 'entrustpay';
        $apiSign = new ApiSign();
        $postData = $apiSign->signData($params);
        $postData['_sign'] = base64_encode($postData['_sign']);
        $res = $this->httpPost($this->repayDomain . $url, $postData);
        Logger::dayLog('api/depository/entrustpay', $res, $params);
        $result = json_decode($res, true);
        if (!is_array($result) || $result['rsp_code'] != '0000') {
            return false;
        }
        return $result;
    }

    /**
     * 合规，借款历史记录
     * @param $params
     * @return bool|mixed
     * @author 王新龙
     * @date 2018/9/13 11:17
     */
    public function loanorder($params){
        $url = 'loanorder/index';
        $apiSign = new ApiSign();
        $postData = $apiSign->signData($params);
        $postData['_sign'] = base64_encode($postData['_sign']);
        $res = $this->httpPost($this->repayDomain . $url, $postData);
        Logger::dayLog('api/depository/loanorder', $res, $params);
        $result = json_decode($res, true);
        if (!$result) {
            return '{"rsp_code":"404","rsp_msg":"service error"}';
        }
        $isVerify = $apiSign->verifyData($result['data'], $result['_sign']);
        if (!$isVerify) {
            return '{"rsp_code":"200","rsp_msg":"sign error"}';
        }
        return $result['data'];
    }
    
     /**
     * 开户设密页面 新流程（330版本）  2018-09-17
     * @param $params  //http://testpay.yaoyuefu.com/api/
     * @return bool
     */
    public function cgkh($params) {
        $url = 'account/openencrypt';
        $apiSign = new ApiSign();
        $postData = $apiSign->signData($params);
        $res = $this->httpPost($this->domain . $url, $postData);
        Logger::dayLog('api/depository/cgkh', $res, $params);
        $result = json_decode($res, true);
        if (!$result) {
            return '{"res_code":"404","res_msg":"service error"}';
        }
        $isVerify = $apiSign->verifyData($result['data'], $result['_sign']);
        if (!$isVerify) {
            return '{"res_code":"200","res_msg":"sign error"}';
        }
        return $result['data'];
    }
   
   /**
    * 密码重置页面 新
    * @param type $params
    */ 
   public function cgrestpwd($params){
        $url = 'account/pwdresetpage';
        $apiSign = new ApiSign();
        $postData = $apiSign->signData($params);
        $res = $this->httpPost($this->domain . $url, $postData);
        Logger::dayLog('api/depository/pwdresetpage', $res, $params);
        $result = json_decode($res, true);
        if (!$result) {
            return '{"res_code":"404","res_msg":"service error"}';
        }
        $isVerify = $apiSign->verifyData($result['data'], $result['_sign']);
        if (!$isVerify) {
            return '{"res_code":"200","res_msg":"sign error"}';
        }
        return $result['data'];
   }
   /**
    * 密码修改页面 新
    * @param type $params
    */ 
   public function cgupdatepwd($params){
        $url = 'account/pwdupdate';
        $apiSign = new ApiSign();
        $postData = $apiSign->signData($params);
        $res = $this->httpPost($this->domain . $url, $postData);
       Logger::dayLog('api/depository/pwdupdateres', $res, $params);
        $result = json_decode($res, true);
        if (!$result) {
            return '{"res_code":"404","res_msg":"service error"}';
        }
        $isVerify = $apiSign->verifyData($result['data'], $result['_sign']);
        if (!$isVerify) {
            return '{"res_code":"200","res_msg":"sign error"}';
        }
        return $result['data'];
   } 
   
    /**
    * 页面解绑卡功能 新
    * @param type $params
    */ 
   public function cgOvercard($params){
        $url = 'bindbank/unbindcardpage';
        $apiSign = new ApiSign();
        $postData = $apiSign->signData($params);
        $res = $this->httpPost($this->domain . $url, $postData);
        Logger::dayLog('api/depository/unbindcardpage', $res, $params);
        $result = json_decode($res, true);
        if (!$result) {
            return '{"res_code":"404","res_msg":"service error"}';
        }
        $isVerify = $apiSign->verifyData($result['data'], $result['_sign']);
        if (!$isVerify) {
            return '{"res_code":"200","res_msg":"sign error"}';
        }
        return $result['data'];
   }
   
   /**
    * 四合一授权 新
    * @param type $params
    */ 
   public function cgauth($params){
        $url = 'termsauth/multipleauth';
        $apiSign = new ApiSign();
        $postData = $apiSign->signData($params);
        $res = $this->httpPost($this->domain . $url, $postData);
        Logger::dayLog('api/depository/multipleauth', $res, $params);
        $result = json_decode($res, true);
        if (!$result) {
            return '{"res_code":"404","res_msg":"service error"}';
        }
        $isVerify = $apiSign->verifyData($result['data'], $result['_sign']);
        if (!$isVerify) {
            return '{"res_code":"200","res_msg":"sign error"}';
        }
        return $result['data'];
   }
   
    /**
    * 存管-绑卡关系查询
    * @param type $params
    */ 
   public function cgCardQuery($params){
        $url = 'queryall/cardbinddetailsquery';
        $apiSign = new ApiSign();
        $postData = $apiSign->signData($params);
        $res = $this->httpPost($this->domain . $url, $postData);
        Logger::dayLog('api/depository/cardbinddetailsquery', $res, $params);
        $result = json_decode($res, true);
        if (!$result) {
            return '{"res_code":"404","res_msg":"service error"}';
        }
        $isVerify = $apiSign->verifyData($result['data'], $result['_sign']);
        if (!$isVerify) {
            return '{"res_code":"200","res_msg":"sign error"}';
        }
        return $result['data'];
   }
   
      
   /**
    * app应用列表传输给开放平台
    * @param type $params
    * @return string
    */
   public function application($params){
        $apiSigns = new ApiAppSign();
        $postData = $apiSigns->signData($params);
        $postData = json_encode($postData);
        $res = $apiSigns->postForm($postData);
        Logger::dayLog('api/depository/application', $res, $postData);
        $result = json_decode($res, true);
        Logger::dayLog('api/depository/application', $result);
        return $result;
   }

    /**
     * 获取合同地址
     * @param $params
     * @return string
     */
   public function getContract($params){
       $url = 'bestsign/getdebt';
       $apiSign = new ApiSign();
       $postData = $apiSign->signData($params);
       $postData['_sign'] = base64_encode($postData['_sign']);
       $res = $this->httpPost($this->repayDomain . $url, $postData);
       Logger::dayLog('api/depository/getcontract', $this->repayDomain . $url, $postData, $res);
       $result = json_decode($res, true);
       if (!$result) {
           return '{"rsp_code":"404","res_msg":"service error"}';
       }
       if($result['rsp_code'] != '0000'){
           return '{"rsp_code":"200","res_msg":"data error"}';
       }
       return $res;
   }
}
