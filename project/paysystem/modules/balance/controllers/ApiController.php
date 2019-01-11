<?php

namespace app\modules\balance\controllers;

use Yii;
use yii\web\Controller;
use app\common\Func;

use app\models\App;
use app\models\ServerApp;
use app\models\Server;
use app\models\WhiteIp;

use app\common\ApiServerCrypt;

use app\models\Log;

/**
 * api入口文件方法
 *
 */
class ApiController extends Controller
{
    /**
     * api接口不需要 token 验证
     */
    public $enableCsrfValidation = false;
    
    protected $reqEncrypt;//提交加密状态
    protected $reqData; // 提交的解密数据
    
    protected $server_id; // 服务id 由子类设置
    
    protected $app_id; // 应用id
    protected $appData; // 商户完整信息
    
    protected $isLog = true;// 是否纪录日志

    protected $errinfo;// 出错信息

    /**
     * 服务端加密处理
     */
    protected $apiServerCrypt;
    
    /**
     * 初始化操作
     */
    public function init()
    {
        $this->apiServerCrypt = new ApiServerCrypt();
        $this->chkData($this->post('app_id'), $this->post('data'));
    }
    /**
     * 返回错误信息
     * @param  false | null $result 错误信息
     * @param  str $errinfo 错误信息
     * @return false | null 同参数$result
     */
    public function returnError($result, $errinfo)
    {
        $this->errinfo = $errinfo;
        return $result;
    }
    /**
     * 检测参数是否正确
     */
    public function chkData($app_id, $postData)
    {
        //1  基本参数检测
        //1.1 app_id的检测空值
        $this->reqEncrypt = $postData;
        $this->app_id     = $app_id;
        
        // @todo;
        //$this->app_id = 1;
        //$this->postData = 'YRWRi7bl8OevN6j4D/FbbAYDSJhJbD+RX2J9cXG0f4hYbGbiGweVSWLRdPk6OygNcgWPnro/7lTdNGrBc7whUWf6PtOdACFKVWQZ8JObOLo=';

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

        //4 检测serverip;
        //$this->chkServerId($this->appData['id'], $this->server_id);
        
        //5 请求的参数的签名是否正确
        $this->chkSign($this->reqEncrypt, $this->appData['auth_key']);
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
     * 第三步：检测是否授权
     */
    private function chkServerId($aid, $server_id)
    {
        //该 aid 是否得到了server_id对应的授权
        $isAuth = (new ServerApp)->hasAuth($aid, $server_id);
        if (!$isAuth) {
            return $this->resp(5, '您没有该服务权限', false);
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
        $data = $this->apiServerCrypt -> parseData($reqEncrypt, $auth_key);

        // 3 大于0表示有错误
        if ($data['res_code'] > 0) {
            return $this->resp($data['res_code'], $data['res_data'], false);
        }
        //file_put_contents('a.log', var_export($data['data'],true));
        $this->reqData = Func::new_trim($data['res_data']);
    }
    /**
     * 响应结果
     * @param $res_code 0: 无错误（即成功), 1...错误码
     * @param $res_data 输出结果：错误信息或者数据格式
     */
    public function resp($res_code, $res_data, $return = false)
    {
        // 纪录日志
        $result = $this->logInfo($res_code, $res_data);
        $returnData = array(
            'res_code' => $res_code,
            'res_data' => $res_data,
        );
        if ($return) {
            return $returnData;
        }
        
        // 若成功返回则需要加密，失败的话就不用了
        if ($res_code === 0 && $this->appData) {
            $returnData['res_data'] = $this->apiServerCrypt->buildData($returnData['res_data'], $this->appData['auth_key']);
        }
        echo json_encode($returnData, JSON_UNESCAPED_UNICODE);
        exit;
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
        if ($res_code==0) {
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
            'req_url' =>  Yii::$app->request->url,
            'req_ip' => Func::get_client_ip(),//'请求IP',
            'req_encrypt' => $this->reqEncrypt,
            'req_info' => serialize($this->reqData),
            'rsp_status' => $res_code,
            'rsp_info' => serialize($res_data),
            'create_time' => $time,
            'modify_time' => $time,
        ];
        $logModel = new Log();
        $logModel -> attributes = $postData;
        
        // 参数检证是否有错
        if (!$logModel->validate()) {
            $errors = $logModel->errors;
            return false;
        }
        
        $result =  $logModel -> save();
        return $result ? $logModel->id : false;
    }
    /**
     * getpost 返回get,post的数据，简单封装下
     */
    protected function get($name = null, $defaultValue = null)
    {
        return Yii::$app->request->get($name, $defaultValue);
    }
    protected function post($name = null, $defaultValue = null)
    {
        return Yii::$app->request->post($name, $defaultValue);
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
    // end getpost

    /**
     * 日志记法
     * 0: file
     * 1... 内容自动以\t分隔, 数组自动var_export($c,true)转换成串
     */
    protected function dayLog()
    {
        call_user_func_array(['\app\common\Logger','dayLog'], func_get_args());
        return true;
    }
}
