<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/12
 * Time: 15:49
 */

namespace app\modules\service\controllers;

use app\modules\api\common\ServiceSign;
use app\common\Crypt3Des;
use app\common\Func;
use app\models\App;
use app\models\Log;
use app\models\service\WhiteIp;
use Yii;
use yii\web\Controller;
use app\common\Logger;
use yii\helpers\ArrayHelper;

/**
 * @desc api入口文件方法
 */
class PhoneApiController extends Controller
{
    /**
     * api接口不需要 token 验证
     */
    public $enableCsrfValidation = false;
    /**
     * 初始化操作
     */
    public function init()
    {

    }

    protected function chkAuth($chk_arr)
    {
        //1  基本参数检测
        //1.1 主要参数的检测
        if (!is_array($chk_arr) || empty($chk_arr) || !isset($chk_arr['stime'])) {
            return 'X000003';
        }
        //1.2 type 的检测空值
        $type = ArrayHelper::getValue($chk_arr,'type','');
        if (empty($type)) {
            return 'X000004';
        }
        //1.3 service_id 的检测空值
        $service_id = ArrayHelper::getValue($chk_arr,'service_id','');
        if (empty($service_id)) {
            return 'X000005';
        }

        $service_config = $this->getConfig();
        //2 service_id 是否授权
        $service_params = ArrayHelper::getValue($service_config,$service_id,[]);
        if (empty($service_params)) {
            return 'X000011';
        }
        //3 是否开通此服务
        $service_type = ArrayHelper::getValue($service_params,'type',[]);
        if (!in_array($type,$service_type)) {
            return 'X000012';
        }
        //4 检测ip;
        $chk_res = $this->chkIp($service_id);
        if (!$chk_res) {
            return 'X000013';
        }
        //5 请求的参数的签名是否正确
        $sign = ArrayHelper::getValue($chk_arr,'sign','');
        $stime = ArrayHelper::getValue($chk_arr,'stime','');
        $chk_res = $this->chkSign($service_params,$stime,$sign);
        if (!$chk_res) {
            return 'X000014';
        }
        return '0000';
    }

    // check sign
    private function chkSign($service_params,$time,$sign) {
        if (empty($service_params)|| !is_array($service_params) || empty($time)) {
            return false;
        }
        $ServiceSign = (new ServiceSign)->setBlackSign($service_params,$time);
        if (!strcmp($ServiceSign,$sign) === 0) {
            return false;
        }
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
        $result = [
            'rsp_code' => $res_code,
            'rsp_msg' => $res_msg,
            'res_data'=> $res_data,
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
        $ip_arr = (new WhiteIp)->validIp($service_id);
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
