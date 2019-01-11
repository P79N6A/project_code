<?php
namespace app\modules\service\controllers;

use app\common\ApiCrypt;
use app\common\Func;
use app\models\StService;
use app\models\StWhitelist;
use app\models\Log;
use Yii;
use yii\web\Controller;
use app\common\Logger;
use yii\helpers\ArrayHelper;

/**
 * @desc api入口文件方法
 */
class ApiController extends Controller
{
    /**
     * api接口不需要 token 验证
     */
    public $enableCsrfValidation = false;
    protected $postdata = null;
    protected static $auth_key = null;
    /**
     * 初始化操作
     */
    public function init()
    {

    }

    protected function chkAuth($chkArr)
    {
        // 1  基本参数检测
        if (!is_array($chkArr) || !isset($chkArr['data']) || !isset($chkArr['app_id'])) {
            return $this->returnMsg('X000003');
        }

        //2.1 service_id 的检测空值
        $service_id = ArrayHelper::getValue($chkArr,'app_id','');
        if (empty($service_id)) {
            return $this->returnMsg('X000005');
        }

        //2.2 service_id 是否授权
        $service_data = $this->chkServiceId($service_id);
        if (empty($service_data)) {
            return $this->returnMsg('X000011');
        }
        //3 检测ip;
        $chk_res = $this->chkIp($service_id);
        if (!$chk_res) {
            return $this->returnMsg('X000013');
        }
        //4 check Sign
        $isVerify = $this->chkSign($chkArr['data'],$service_data);
        if (!$isVerify) {
            return $this->returnMsg('X000014');
        }
        return true;
    }
    // chk service_id
    private function chkServiceId($service_id){
        return (new StService)->getByServiceId($service_id);
    }
    // check sign
    private function chkSign($sign_data,$service_data) {
        $auth_key = ArrayHelper::getValue($service_data,'auth_key','');
        self::$auth_key = $auth_key;
        if (empty($auth_key)) {
            return false;
        }
        $perse_res = (new ApiCrypt)->parseData($sign_data, $auth_key);
        if ($perse_res['res_code'] != 0) {
            Logger::dayLog('service/chkSign', json_encode($service_data), json_encode($perse_res), $sign_data);
            return false;
        }
        $this->postdata = $perse_res['res_data'];
        return true;

    }
    protected function post($name = null, $defaultValue = null)
    {
        return Yii::$app->request->post($name, $defaultValue);
    }

    /**
     * @desc 获取配置文件
     * @param  str $cfg 
     * @return  []
     */
    private function getConfig() {
        $configPath = __DIR__ . "/../config/params.php";
        if (!file_exists($configPath)) {
            throw new \Exception($configPath . "配置文件不存在", 98);
        }
        $config = include $configPath;
        return $config;
    }

    /**
     * 返回错误信息
     * @param $res_code 0: 无错误（即成功), 1...错误码
     * @param $res_data 输出结果：错误信息或者数据格式
     */
    protected function returnMsg($res_code, $res_data = '',$res_msg = '')
    {
        if (empty($res_msg)) {
            $configPath = __DIR__ . "/../config/errorCode.php";
            if (!file_exists($configPath)) {
                throw new \Exception($configPath . "配置文件不存在");
            }
            $config = include $configPath;
            $res_msg = !empty($config[$res_code]) ? $config[$res_code] : '';
        }
        if ($res_code == '0000' && self::$auth_key) {
            // 若成功返回则需要加密，失败的话就不用了
            $res_data = (new ApiCrypt)->buildData($res_data, self::$auth_key);
        }
        $result = [
            'rsp_code' => $res_code,
            'rsp_msg' => $res_msg,
            'rsp_data'=> $res_data,
        ];
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * 第三步：检测是否授权
     */
    private function chkIp($service_id)
    {
        $ip = Func::get_client_ip();
        Logger::dayLog('service/ip', $service_id, $ip);
        $ip_arr = (new StWhitelist)->getWhiteByServiceId($service_id);
        if (!in_array($ip, $ip_arr)) {
            return false;
        }
        return true;
    }

    private function getParam($name, $defaultValue = null)
    {
        $value = $this->get($name, $defaultValue);
        if (is_null($value)) {
            return $this->post($name, $defaultValue);
        } else {
            return $value;
        }
    }
}
