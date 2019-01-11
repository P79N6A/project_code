<?php
namespace app\modules\api\common\lianlian;
use app\common\Curl;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * 连连支付api接口;
 * 这个是这个包对外开放的唯一接口.
 * 流程如下
 * 新用户: 签约授权-支付 4.2-4.5
 * 老用户: 授权-支付 4.3-4.5
 */
class LianApi {
    private $config;
    private $oLianService;

    public function __construct($cfg) {
        // 获取配置文件
        $this->config = $this->getConfig($cfg);
        $this->oLianService = new LLpaySubmit($this->config);
    }

    /**
     * 4.2 签约授权
     * @param  [] $payData
     * @return [res_code, res_data]
     */
    public function signApply($payData) {
        $url = $this->config['url_signapply'];
        $data = $this->getSignApply($payData);
        $res_data = $this->buildSign($data);
        //var_export($res_data);exit;
        $req_data = Json::encode($res_data);
        $html_code = $this->oLianService->buildRequestFormCode($url, $req_data);
        return [
            'res_code' => 0,
            'res_data' => [
                'url' => $url,
                'req_data' => $req_data,
                "html_code" => $html_code,
            ],
        ];
    }
    /**
     * 4.2 组合签约授予权数据格式
     * @param  [] $payData
     * @return []
     */
    private function getSignApply($payData) {
        /*$repaymentPlan = json_encode(['repaymentPlan' => [[
        'date' => $payData['date'],
        'amount' => $payData['amount'],
        ]]], JSON_UNESCAPED_UNICODE);

        $sms_param = json_encode([
        'contract_type' => $payData['contract_type'],
        'contact_way' => $payData['contact_way'],
        ], JSON_UNESCAPED_UNICODE);*/

        $params = [
            'version' => $this->config['version'],
            'oid_partner' => $this->config['oid_partner'],
            'app_request' => $this->config['app_request'],
            'sign_type' => $this->config['sign_type'],
            'id_type' => 0,
            'pay_type' => 'I',

            'user_id' => $payData['user_id'],
            'id_no' => $payData['id_no'],
            'acct_name' => $payData['acct_name'],
            'card_no' => $payData['card_no'],
            'url_return' => $payData['url_return'],
            'risk_item' => $this->getRiskItem($payData),
            /*'repayment_plan' => $repaymentPlan,
        'repayment_no' => $payData['repayment_no'],
        'sms_param' => $sms_param, #短信参数*/
        ];
        return $params;
    }

    /**
     * 4.3 授权
     * @param  [] $payData
     * @return [res_code, res_data]
     */
    public function apply($payData) {
        $repaymentPlan = json_encode(['repaymentPlan' => [[
            'date' => $payData['date'],
            'amount' => $payData['amount'],
        ]]], JSON_UNESCAPED_UNICODE);

        $sms_param = json_encode([
            'contract_type' => $payData['contract_type'],
            'contact_way' => $payData['contact_way'],
        ], JSON_UNESCAPED_UNICODE);

        $data = [
            'oid_partner' => $this->config['oid_partner'],
            'sign_type' => $this->config['sign_type'],
            'api_version' => '1.0',
            'user_id' => $payData['user_id'],
            'repayment_plan' => $repaymentPlan, #还款计划
            'repayment_no' => $payData['repayment_no'],
            'sms_param' => $sms_param,
            'pay_type' => 'D', #支付方式 默认D ：认证支付渠道

            // 二次支付时,只需要传此参数
            'no_agree' => $payData['no_agree'],
        ];

        $signData = $this->buildSign($data);
        $url = $this->config['url_apply'];

        // 本地
        if (defined('SYSTEM_LOCAL') && SYSTEM_LOCAL) {
            $res = $this->testApiApply();
        } else {
            $res = $this->curlPost($url, $signData);
        }

        $res = json_decode($res, true);

        // 返回结果
        Logger::dayLog('lian', 'lianapi/apply', $url, $signData, $res);
        $response = $this->parseResult($res);
        return $response;
    }
    /**
     * 4.5 支付接口
     * @param  [] $payData
     * @return [res_code, res_data]
     */
    public function pay($payData) {
        $data = [
            'oid_partner' => $this->config['oid_partner'],
            'sign_type' => $this->config['sign_type'],
            'version' => $this->config['version'],
            'valid_order' => $this->config['valid_order'],
            'pay_type' => 'D', #支付方式 默认D ：认证支付渠道
            'busi_partner' => '101001',

            'user_id' => $payData['user_id'],
            'no_order' => $payData['no_order'],
            'dt_order' => $payData['dt_order'],
            'name_goods' => $payData['name_goods'],
            'info_order' => $payData['info_order'],
            'money_order' => $payData['money_order'],
            'notify_url' => $payData['notify_url'],

            'risk_item' => $this->getRiskItem($payData),
            'schedule_repayment_date' => $payData['schedule_repayment_date'],
            'repayment_no' => $payData['repayment_no'],

            // 二次支付时,只需要传此参数
            'no_agree' => $payData['no_agree'],
        ];

        $signData = $this->buildSign($data);
        $url = $this->config['url_pay'];

        if (defined('SYSTEM_LOCAL') && SYSTEM_LOCAL) {
            $res = $this->testApiPay();
        } else {
            $res = $this->curlPost($url, $signData);
        }

        $res = json_decode($res, true);

        // 返回结果
        Logger::dayLog('lian', 'lianapi/pay', $url, $signData, $res);
        $response = $this->parseResult($res);
        if (in_array($response['res_code'], ['2008'])) {
            // 在这里的也算是支付中
            // 2008 交易正在处理中
            $response['res_code'] = 0;
        }
        return $response;
    }
    /**
     * 风险控制参数
     * @param  [] $payData
     * @return  str
     */
    private function getRiskItem($payData) {
        $res = json_encode([
            'frms_ware_category' => '2010',
            'user_info_mercht_userno' => $payData['user_id'],
            'user_info_dt_register' => '20141015165530',
            'user_info_full_name' => $payData['acct_name'],
            'user_info_id_no' => $payData['id_no'],
            'user_info_identify_type' => '1',
            'user_info_identify_state' => '1',
            //'frms_ip_addr' => '121.199.129.16',
            'user_info_bind_phone' => $payData['user_info_bind_phone'],
        ], JSON_UNESCAPED_UNICODE);
        return $res;
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
     * 提交数据
     * @param array $data
     * @param str json
     * @return null
     */
    private function curlPost($url, $data) {
        $timeLog = new \app\common\TimeLog();

        $jsonString = json_encode($data, JSON_UNESCAPED_UNICODE);
        $curl = new Curl();
        $curl->addHeader([
            'Content-Type' => 'application/json',
            'Content-Length' => strlen($jsonString),
        ]);
        $curl->setOption(CURLOPT_CONNECTTIMEOUT, 30);
        $curl->setOption(CURLOPT_TIMEOUT, 30);
        $content = $curl->post($url, $jsonString);
        $status = $curl->getStatus();

        $timeLog->save('lian', ['api', 'POST', $status, $url, $jsonString, $content]);

        if ($status == 200) {
            return $content;
        } else {
            Logger::dayLog(
                "lianlian",
                "请求信息", $url, $data,
                "http状态", $status,
                "响应内容", $content
            );
            return null;
        }
    }
    /**
     * 返回结果
     * @param  int $res_code
     * @param  [] | str $res_data
     * @return []
     */
    private function parseResult($res) {
        $ret_code = ArrayHelper::getValue($res, 'ret_code');
        $ret_msg = ArrayHelper::getValue($res, 'ret_msg', '未知错误');

        if ($ret_code == '0000') {
            $result = $this->verify($res);
            if (!$result) {
                return ['res_code' => 'sign_error', 'res_data' => "签名不正确"];
            }
            return ['res_code' => 0, 'res_data' => $res];
        } else {
            return ['res_code' => $ret_code, 'res_data' => $ret_msg];
        }
    }
    /**
     * 获取签名后的数据
     * @param  [] $data
     * @return json
     */
    public function sign($data) {
        return $this->oLianService->buildRequestMysign($data);
    }
    /*
     * 验证数据来源签名
     */
    public function verify($data) {
        if (!is_array($data) || !isset($data['sign'])) {
            return false;
        }
        $sign1 = $data['sign'];
        $data = $this->oLianService->buildRequestPara($data);
        return $sign1 === $data['sign'];
    }
    /**
     * 生成要请求给连连支付的参数数组
     * @param $data 请求前的参数数组
     * @return 要请求的参数数组
     */
    public function buildSign($data) {
        return $this->oLianService->buildRequestPara($data);
    }
    /**
     * 测试授权接口
     */
    private function testApiApply() {
        $res = '{
              "ret_code": "0000",
              "ret_msg": "交易成功",
              "sign_type": "MD5",
              "sign": "837708e23ea3e0ffb6ff725a4a7a6901",
              "correlationID": "4b474e59-8951-4223-a5b2-0fc8e79737a4",
              "token": "A1AEE0C0F3AD0F22149A683DB18CED3D",
              "oid_partner": "201612161001339313",
              "no_agree": "2017010934533801",
              "repayment_no": "R1O1483945671"
            }';
        return $res;
    }
    /**
     * 测试支付接口
     */
    private function testApiPay() {
        $res = '{
                  "ret_code": "0000",
                  "ret_msg": "交易成功",
                  "sign_type": "MD5",
                  "sign": "bbffd4bcea53cbcabb7de7e8c5e84e6f",
                  "correlationID": "67e5c469-d57a-4740-9dc6-ed4f8f5e92ed",
                  "oid_partner": "201612161001339313",
                  "no_order": "1_1483945671",
                  "dt_order": "20170109150827",
                  "money_order": "0.02",
                  "oid_paybill": "2017010911579434",
                  "settle_date": "20170109",
                  "info_order": "购买电子产品",
                  "repayment_no": "R1O1483945671"
            }';
        return $res;
    }
}