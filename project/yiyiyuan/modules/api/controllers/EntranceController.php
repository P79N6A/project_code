<?php

namespace app\modules\api\controllers;

use app\commonapi\appLogger;
use app\commonapi\Common;
use app\commonapi\Logger;
use app\modules\api\common\ApiController;
use Exception;
use Yii;

class EntranceController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $array_notify = $this->getParamArr();
        if (empty($array_notify['service_type'])) {
            $array['rsp_code'] = '99996';
            $array['rsp_msg'] = $this->geterrorcode($array['rsp_code']);
            echo json_encode($array);
            exit;
        }
        //记录独立日志
        $this->saveLog(Yii::$app->request->post());
        Logger::errorLog(print_r($array_notify, true), $array_notify['service_type'], 'api');
        //根据提交参数进行验签
        if (!empty($array_notify) && is_array($array_notify)) {
            if ($array_notify['service_type'] == 'crash') {
                $array_notify['sign'] = 'crash';
            }
            $postsign = $array_notify['sign'];
            $service_type = $array_notify['service_type'];
            if ($array_notify['service_type'] != 'uploadpic' && $array_notify['service_type'] != 'crash') {
                $sign = $this->verificationSign($array_notify);
            } else {
                $sign = $array_notify['sign'];
            }
//            echo $sign;die;
            $is_dev = false;
            if (defined('SYSTEM_ENV') && SYSTEM_ENV == 'dev') {
                $is_dev = true;
            }
            if ($sign == $postsign || $is_dev) {
                //根据参数的值调用对应的接口处理文件
                if (!isset($service_type) || empty($service_type)) {
                    Logger::dayLog('app/entrance', '99995', 'service_type缺失');
                    $array['rsp_code'] = '99995';
                    $array['rsp_msg'] = $this->geterrorcode($array['rsp_code']);
                    echo json_encode($array);
                    exit;
                }
                $version = $array_notify['version'];
                if ($version >= '1.2.6' && $service_type != 'interface') {
                    $array_version = explode(".", $version);
                    $now_version = $array_version[0] . $array_version[1] . $array_version[2];
                    $service_name = '/api/controllers' . $now_version . '/' . $service_type;
                } else {
                    $service_name = '/api/' . $service_type;
                }
                try {
                    Yii::$app->runAction($service_name);
                } catch (Exception $e) {
                    Logger::dayLog('app/entrance', $service_name, $e);
                    $array['rsp_code'] = '99995';
                    $array['rsp_msg'] = $this->geterrorcode($array['rsp_code']);
                    echo json_encode($array);
                    exit;
                }
            } else {
                $array['rsp_code'] = '99998';
                $array['rsp_msg'] = $this->geterrorcode($array['rsp_code']);
                echo json_encode($array);
                exit;
            }
        } else {
            $array['rsp_code'] = '99997';
            $array['rsp_msg'] = $this->geterrorcode($array['rsp_code']);
            echo json_encode($array);
            exit;
        }
    }

    private function verificationSign($array_notify) {
        unset($array_notify['sign']);
        $paramkey = array_keys($array_notify);
        sort($paramkey);
        $signstr = '';
        foreach ($paramkey as $key => $val) {
            $signstr .= $array_notify[$val];
        }
        //系统分配的密匙
        $key = Yii::$app->params['app_key'];
        //签名
        $signstr = urldecode($signstr);
        $sign = md5($signstr . $key);
        return $sign;
    }

    private function saveLog($post) {
        $usid = isset($post['_user_id']) ? $post['_user_id'] : '';
        if (empty($usid) || $usid == 'empty') {
            $usid = isset($post['user_id']) ? $post['user_id'] : '';
        }
        $net = isset($post['_net']) ? $post['_net'] : '';
        $ips = explode(',', Common::get_client_ip());
        $ip = !empty($ips) ? $ips[0] : '';
        $logArr = [
            'path' => isset($post['service_type']) ? $post['service_type'] : '',
            'version' => isset($post['version']) ? $post['version'] : '',
            'tokenid' => isset($post['_tokenid']) ? $post['_tokenid'] : '',
            'app_version' => isset($post['_app_version']) ? $post['_app_version'] : '',
            'user_id' => $usid,
            'uuid' => isset($post['_uuid']) ? $post['_uuid'] : '',
            'phone_model' => isset($post['_phone_model']) ? $post['_phone_model'] : '',
            'source' => isset($post['_source']) ? $post['_source'] : '',
            'gps' => isset($post['_gps']) ? $post['_gps'] : '',
            'net' => strtoupper($net),
            'mac' => isset($post['_mac']) ? $post['_mac'] : '',
            'system_version' => isset($post['_system_version']) ? $post['_system_version'] : '',
            'imei' => isset($post['_imei']) ? $post['_imei'] : '',
            'ip' => $ip,
            'time' => date('Y-m-d H:i:s'),
        ];

        appLogger::saveLog('appLog', $logArr);
    }

}
