<?php
namespace app\modules\sfapi\logic;
use app\common\Crypt3Des;
use app\common\Errorcode;
use app\common\Logger;
use app\modules\api\common\BaseApi;
use Yii;
use yii\helpers\ArrayHelper;

class BaseLogic {

    public $info;
    protected $api;

    public function __construct() {
        $this->api = new BaseApi();
    }
    
    /**
     * Undocumented function
     *
     * @param  $id
     * @param  $pay_controller
     * @return void
     */
    public function getPayUrl($arr)
    {
        $jsonStr = json_encode($arr);
        $cryData = urlencode($this->encryptData($jsonStr));
        $url = Yii::$app->request->hostInfo . "/sms/smsurl/?data={$cryData}";
        return $url;
    }

    /**
     * Undocumented function
     *
     * @param  $id
     * @return void
     */
    public function encryptData($data)
    {
        return Crypt3Des::encrypt((string)$data, Yii::$app->params['trideskey']);
    }

    /**
     * Undocumented function
     *
     * @param  $cryid
     * @return void
     */
    public function decryptData($data)
    {
        if (!$data) {
            return '';
        }
        try {
            $data = Crypt3Des::decrypt($data, Yii::$app->params['trideskey']);
        } catch (\Exception $error) {
            $data = '';
        }
        return $data;
    }

    protected function getConfig() {
        $configPath = __DIR__ . "/../config/params.php";
        if (!file_exists($configPath)) {
            return [];
        }
        $config = include $configPath;
        return $config;
    }

    /**
     * POST 异步通知客户端:并仅通知最终结果, 即(成功|失败)
     * @return bool
     */
    public function clientNotify()
    {
        $isNotify = $this->doClientNotify();
        if ($isNotify) {
            $status = ClientNotify::STATUS_SUCCESS;
        } else {
            $status = ClientNotify::STATUS_INIT;
        }
        $result = (new ClientNotify)->saveData($this->id, $status);
        return $isNotify;
    }

    /**
     * 仅通知
     * @return [type] [description]
     */
    public function doClientNotify()
    {
        // 已经通知过了
        /*if ($this->client_status == 1) {
        return true;
        }*/
        if (!in_array($this->status, [static::STATUS_PAYOK, static::STATUS_PAYFAIL])) {
            return false;
        }
        // 更新通知状态
        $data = $this->clientData();
        $result = $this->clientPost($this->callbackurl, $data, $this->aid);
        if ($result) {
            $this->client_status = 1;
            $this->modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        }
        return $this->client_status == 1;
    }

    /**
     * 返回客户端响应结果
     * @return  []
     */
    public function clientData() {
        return [
            'status' => $this->returnClientStatus($this->status),
            'orderid' => $this->orderid,
            'res_code' => $this->res_code,
            'res_msg' => $this->res_msg,
        ];
    }

    /**
     * POST 异步通知客户端
     * @return bool
     */
    private function clientPost($callbackurl, $data, $aid) {
        Logger::dayLog('payorder/clientPost', $callbackurl,$data,$aid);
        //1 加密
        $res_data = App::model()->encryptData($aid, $data);
        $postData = ['res_data' => $res_data, 'res_code' => 0];

        //2 post提交
        $oCurl = new \app\common\Curl;
        $res = $oCurl->post($callbackurl, $postData);
        Logger::dayLog('payorder/clientPost', 'post', "客户响应|{$res}|", $callbackurl, $data);
        // Logger::dayLog('payorder/clientPost', 'res', $res);
        //3 解析结果
        $res = strtoupper($res);
        return $res == 'SUCCESS';
    }

    /**
     * GET 回调通知客户端 url
     * @return url
     */
    public function clientBackurl()
    {
        $data = $this->clientData();
        $url = $this->clientGet($this->callbackurl, $data, $this->aid);
        return $url;
    }

    /**
     * GET 页面回调链接
     */
    private function clientGet($callbackurl, $data, $aid)
    {
        //1 加密
        $res_data = App::model()->encryptData($aid, $data);

        //2 组成url
        $link = strpos($callbackurl, "?") === false ? '?' : '&';
        $url = $callbackurl . $link . 'res_code=0&res_data=' . rawurlencode($res_data);
        return $url;
    }

    /**
     * Undocumented function
     * 处理结果返回
     * @param  $res
     * @return void
     */
    public function parseResult($res)
    {
        $res = json_decode($res, true);
        if (!$res) return ['res_code' => "-1", 'res_data' => "请求出错，请检查网络！"];
        $result = $this->api->verifySign($res);
        if (!$result) {
            return ['res_code' => 'sign_error', 'res_data' => "验签失败！"];
        }
        unset($res['sign']);
        $retCode = ArrayHelper::getValue($res, 'retCode');
        $errorCode = new Errorcode();
        $_errorCode = $errorCode->errorCode;
        $retMsg = $_errorCode[$retCode];
        // var_dump($res);
        if ($retCode == '00000000') {
            return ['res_code' => 0, 'res_data' => $res];
        } else {
            return ['res_code' => $retCode, 'res_data' => $retMsg];
        }
    }

    /**
     * Undocumented function
     * 异步回调验签
     * @param [type] $res
     * @return void
     */
    public function notifyVerifySign($res)
    {
        $result = $this->api->verifySign($res);
        return $result;
    }

    /**
     * @desc 数据输出
     * @param null $result
     * @param string $info
     * @return null
     */
    protected function returnInfo($result, $info)
    {
        $this->info = $info;
        return $result;
    }
}