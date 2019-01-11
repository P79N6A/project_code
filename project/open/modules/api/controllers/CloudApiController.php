<?php
namespace app\modules\api\controllers;

use app\common\ApiSign;
use app\common\Crypt3Des;
use app\common\Func;
use app\models\App;
use app\models\Log;
use app\models\WhiteIp;
use Yii;
use yii\web\Controller;
use app\common\Logger;

/**
 * @desc api入口文件方法
 */
class CloudApiController extends Controller
{
    /**
     * api接口不需要 token 验证
     */
    public $enableCsrfValidation = false;

    public $postdata = [];
    /**
     * 服务端加密处理
     */
    protected $apiServerCrypt;

    /**
     * 初始化操作
     */
    public function init()
    {
        $datas = $this->post();
        Logger::dayLog('init', 'postdata', $datas);
        if (!is_array($datas) || !isset($datas['data']) || !isset($datas['_sign'])) {
            return $this->resp(3, '数据异常！');
        }
        $isVerify = (new ApiSign)->verifyData($datas['data'], $datas['_sign']);
        if (!$isVerify) {
            return $this->resp(4, '验签失败！');
        }
        $data = json_decode($datas['data'], true);

        $this->postdata = $data;
    }

    protected function post($name = null, $defaultValue = null)
    {
        return Yii::$app->request->post($name, $defaultValue);
    }

    /**
     * 检测参数是否正确
     */
    public function chkData($app_id, $postData)
    {
        //1  基本参数检测
        //1.1 app_id的检测空值
        $this->reqEncrypt = $postData;
        $this->app_id = $app_id;

        if (!$this->app_id) {
            return $this->resp(1, '无授权，请检测是否设置app_id');
        }
        //1.2 server_id 的检测空值
        if (!$this->server_id) {
            return $this->resp(2, '没有指定服务，无法完成操作!');
        }

        //2 获取数据库是否存在app_id
        $this->appData = $this->getAppInfo($this->app_id);
        if (!$this->appData) {
            return $this->resp(3, '此app_id无授权!');
        }

        //3 检测ip;
        $this->chkIp($this->appData['id']);

        //5 请求的参数的签名是否正确
        $this->chkSign($this->reqEncrypt, $this->appData['auth_key']);
    }

    /**
     * 响应结果
     * @param $res_code 0: 无错误（即成功), 1...错误码
     * @param $res_data 输出结果：错误信息或者数据格式
     */
    public function resp($res_code, $res_msg = '', $res_data = '')
    {
        $returnData = [
            'identity_id'=>isset($res_data['identity_id']) ? $res_data['identity_id'] :'',
            'basic_id'=>isset($res_data['basic_id']) ? $res_data['basic_id'] :'',
            'event'=>isset($res_data['event']) ? $res_data['event'] :'',
            'idcard'=>isset($res_data['idcard']) ? $res_data['idcard'] :'',
            'phone'=>isset($res_data['phone']) ? $res_data['phone'] :'',
            'aid'=>isset($res_data['aid']) ? $res_data['aid'] :'',
            'req_id'=>isset($res_data['req_id']) ? $res_data['req_id'] :'',
            'rsp_code'=>$res_code,
            'rsp_msg'=>$res_msg,
        ];
        $result = (new ApiSign)->signData($returnData);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * 返回错误信息
     * @param $res_code 0: 无错误（即成功), 1...错误码
     * @param $res_data 输出结果：错误信息或者数据格式
     */
    public function returnMsg($res_code, $res_msg = '', $res_data = '')
    {
        if (empty($res_msg)) {
            $configPath = __DIR__ . "/../config/IperrorCode.php";
            if (!file_exists($configPath)) {
                throw new \Exception($configPath . "配置文件不存在");
            }
            $config = include $configPath;
            $res_msg = !empty($config[$res_code]) ? $config[$res_code] : '';
        }
        $result = [
            'rsp_code'=>$res_code,
            'rsp_msg'=>$res_msg,
        ];
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * 第一步：检测并设置app_id, auth_key, service_id等三个参数
     */
    private function getAppInfo($app_id)
    {
        // 3 数据库中是存在这个应用app_id,并获取auth_key密钥
        return (new App)->getByAppId($app_id);
    }

    /**
     * 第三步：检测是否授权
     */
    private function chkIp($aid)
    {
        //该 app_id 是否得到了server_id对应的授权
        $ip = Func::get_client_ip();
        $result = (new WhiteIp)->validIp($aid, $ip);
        if (!$result) {
            return $this->resp(4, '先花花开发平台:IP受限', false);
        }
    }

    /**
     * 签名检测
     */
    private function chkSign($reqEncrypt, $auth_key)
    {
        if (empty($reqEncrypt)) {
            return $this->resp(6, '提交数据不能为空', false);
        }

        // 2 检测签名是否合法
        $data = $this->apiServerCrypt->parseData($reqEncrypt, $auth_key);

        // 3 大于0表示有错误
        if ($data['res_code'] > 0) {
            return $this->resp($data['res_code'], $data['res_data'], false);
        }
        //file_put_contents('a.log', var_export($data['data'],true));
        $this->reqData = Func::new_trim($data['res_data']);
    }

    /**
     * 返回链接地址
     * $id 表主键id
     * $pay_controller 路由地址
     */
    public function getSmsUrl($id, $pay_controller)
    {
        return [
            'url' => $this->getUrl($id, $pay_controller),
        ];
    }

    /**
     * 生成链接地址
     * @param $id
     * @return string
     */
    public function getUrl($id, $pay_controller)
    {
        $cryid = urlencode($this->encryptId($id));

        $url = Yii::$app->request->hostInfo . "/{$pay_controller}/smsurl/?id={$cryid}";
        return $url;
    }

    /**
     * 加解密id
     * @param  int $id
     * @return str
     */
    public function encryptId($id)
    {
        return Crypt3Des::encrypt((string)$id, Yii::$app->params['trideskey']);
    }

    public function decryptId($cryid)
    {
        if (!$cryid) {
            return '';
        }
        try {
            $id = Crypt3Des::decrypt($cryid, Yii::$app->params['trideskey']);
        } catch (\Exception $error) {
            $id = '';
        }
        return $id;
    }

    /**
     * 纪录日志: 若自行纪录请重写此方法
     * @param $res_code 响应状态
     * @param $res_data 响应内容
     */
    protected function logInfo($res_code, $res_data)
    {
        // 设置为不纪录时
        if (!$this->isLog) {
            return true;
        }

        // 执行正确时不纪录日志
        if ($res_code == 0) {
            return true;
        }

        $logModel = new Log();

        // 这里要纪录各种日志。 以后可扩展成单独的类库
        $time = time();
        //$app_id = is_array($this->appData) && isset($this->appData['id']) ?
        //			$this->appData['id'] : 0;
        $postData = [
            'app_id' => $this->app_id ? $this->app_id : "",// 应用app_id
            'service_id' => $this->server_id,// '服务id',
            'req_url' => Yii::$app->request->url,
            'req_ip' => Func::get_client_ip(),//'请求IP',
            'req_encrypt' => $this->reqEncrypt,
            'req_info' => serialize($this->reqData),
            'rsp_status' => $res_code,
            'rsp_info' => serialize($res_data),
            'create_time' => $time,
            'modify_time' => $time,
        ];
        $logModel = new Log();
        $logModel->attributes = $postData;

        // 参数检证是否有错
        if (!$logModel->validate()) {
            $errors = $logModel->errors;
            return false;
        }

        $result = $logModel->save();
        return $result ? $logModel->id : false;
    }

    protected function getParam($name, $defaultValue = null)
    {
        $value = $this->get($name, $defaultValue);
        if (is_null($value)) {
            return $this->post($name, $defaultValue);
        } else {
            return $value;
        }
    }

    /**
     * getpost 返回get,post的数据，简单封装下
     */
    protected function get($name = null, $defaultValue = null)
    {
        return Yii::$app->request->get($name, $defaultValue);
    }

    public function error($res_code, $res_msg, $res_data = '')
    {
        $returnData = [
            'user_id'=>isset($res_data['user_id']) ? $res_data['user_id'] :'',
            'loan_id'=>isset($res_data['loan_id']) ? $res_data['loan_id'] :'',
            'loan_no'=>isset($res_data['loan_no']) ? $res_data['loan_no'] :'',
            'aid'=>isset($res_data['aid']) ? $res_data['aid'] :'',
            'rsp_code'=>$res_code,
            'rsp_msg'=>$res_msg,
        ];
        $result = (new ApiSign)->signData($returnData);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function success($res, $res_data = '')
    {
        $returnData = [
            'user_id'=>isset($res_data['user_id']) ? $res_data['user_id'] :'',
            'loan_id'=>isset($res_data['loan_id']) ? $res_data['loan_id'] :'',
            'loan_no'=>isset($res_data['loan_no']) ? $res_data['loan_no'] :'',
            'aid'=>isset($res_data['aid']) ? $res_data['aid'] :'',
            'data' => $res,
            'rsp_code'=>'0000',
            'rsp_msg'=> '成功',
        ];
        $result = (new ApiSign)->signData($returnData);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
