<?php

namespace app\modules\api\controllers;

use app\commonapi\Common;
use app\commonapi\Http;
use app\commonapi\Logger;
use app\models\dev\User;
use app\modules\api\common\ApiController;
use Yii;

class InterfaceController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $version = Yii::$app->request->post('version');

        $array_gets = $_GET;
        $array_post = Http::getParamArr();
        $array_notify = array_merge($array_gets, $array_post);
//        print_r($array_notify);exit;
        if (empty($array_notify['service_type'])) {
            $array['rsp_code'] = 9993;
            $array['rsp_msg'] = '请求参数不完整';
            echo json_encode($array);
            exit;
        }
        $service_type = $array_notify['service_type'];
        Logger::errorLog(print_r($array_notify, true), $service_type);
        $service_sign = $array_notify['sign'];
        $sign = Common::verificationSign($array_notify);

        $array_service_type = explode('.', $service_type);
        //$this->$array_service_type[1]( $array_notify );
        call_user_func([$this, 'version'], $array_notify);
    }

    public function appimg($array_notify) {
        $img_type = $array_notify['img_type'];
        $sql = "select img_url from yi_appimg where is_active = 1 and img_type = '" . $img_type . "'";
        $model = Yii::$app->db->createCommand($sql)->queryOne();
        if (!empty($model)) {
            $result = array();
            $result['img_url'] = $model['img_url'];
            $result['rsp_code'] = '0000';
            $result['rsp_msg'] = '操作成功';
            echo json_encode($result);
            exit;
        } else {
            $array['rsp_code'] = 1000;
            $array['rsp_msg'] = '闪图不存在';
            echo json_encode($array);
            exit;
        }
    }

    public function version() {
        $sql = "select * from yi_app_version ORDER BY id desc";
        $model = Yii::$app->db->createCommand($sql)->queryOne();
        if (!empty($model)) {
            $result = array();
            $result['display_ver'] = $model['display_ver'];
            $result['internal_ver'] = $model['internal_ver'];
            $result['forced_upgrade'] = $model['forced_upgrade'];
            $result['download_url'] = $model['download_url'];
            $result['description'] = nl2br($model['description']);
            $result['rsp_code'] = '0000';
            $result['rsp_msg'] = '操作成功';
            echo json_encode($result);
            exit;
        } else {
            $array['rsp_code'] = 2000;
            $array['rsp_msg'] = '没版本信息';
            echo json_encode($array);
            exit;
        }
    }

    private function reback($code, $addresslist) {
        $array['rsp_code'] = $code;
        $array['rsp_msg'] = $this->geterrorcode($code);
        if (empty($addresslist)) {
            $array['list'] = array();
        } else {
            foreach ($addresslist as $key => $val) {
                $array['list'][$key]['name'] = $val->name;
                $array['list'][$key]['mobile'] = $val->phone;
                $user = (new User())->getUserinfoByMobile($val->phone);
                $array['list'][$key]['user_id'] = !empty($user) ? $user->user_id : 0;
                $array['list'][$key]['head'] = !empty($user) && !empty($user->openid) ? (!empty($user->userwx) ? $user->userwx->head : '') : '';
            }
        }
        return $array;
    }

    private function errorreback($code) {
        $array['rsp_code'] = $code;
        $array['rsp_msg'] = $this->geterrorcode($code);
        return $array;
    }

}
