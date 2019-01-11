<?php
namespace app\commands\sobot;

use app\commands\BaseController;
use app\commonapi\ApiSobot;
use app\commonapi\Logger;
use app\models\news\Accesstoken;

class GettokenController extends BaseController {
    public function actionIndex() {
        $token_json = (new ApiSobot())->getToken();
        $token = json_decode($token_json, true);
        if (empty($token) || $token['code'] != '1000' || empty($token['data']) || empty($token['data']['access_token'])) {
            Logger::dayLog('script/sobot/gettoken', '获取token接口错误', $token_json, $token);
            exit('error');
        }

        $o_access_token = (new Accesstoken())->getByType(3);
        if (empty($o_access_token)) {
            $result = (new Accesstoken())->add_record($token['data']['access_token'], 3);
            if (empty($result)) {
                Logger::dayLog('script/sobot/gettoken', '保存token失败', $token_json, $result);
                exit('yi_access_token error');
            }
        } else {
            $result = $o_access_token->update_record($token['data']['access_token']);
            if (empty($result)) {
                Logger::dayLog('script/sobot/gettoken', '更新token失败', $token_json, $result);
                exit('yi_access_token error');
            }
        }
        exit('success');
    }
}