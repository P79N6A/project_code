<?php
namespace app\commands\sobot;

use app\commands\BaseController;
use app\commonapi\ApiSobot;
use app\commonapi\Logger;
use app\models\news\Accesstoken;
use app\models\news\SobotSessionMessage;

class GetsessionmessageController extends BaseController {
    public function actionIndex() {
        $o_access_token = (new Accesstoken())->getByType(3);
        $is_invalid = true;
        if (!empty($o_access_token)) {
            $is_invalid = $o_access_token->isInvalid(24);
        }
        if (empty($o_access_token) || empty($is_invalid)) {
            Logger::dayLog('script/sobot/getsessionmessage', 'token不存在or失效', $o_access_token, $is_invalid);
            exit('token error');
        }
        $startDate = date("Y-m-d",strtotime("-1 day"));
        $endDate = date("Y-m-d",strtotime("-1 day"));
        $data = [
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
        $token_json = (new ApiSobot())->getSessionMessage($o_access_token->access_token, $data);
        $token = json_decode($token_json, true);
        if (empty($token) || $token['code'] != '1000' || empty($token['data']) || empty($token['data']['item'])) {
            Logger::dayLog('script/sobot/getsessionmessage', '接口错误', $token_json, $token);
            exit('error');
        }
        $data = [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'res_json' => $token_json
        ];
        $data = array_merge($data, (new ApiSobot())->updateStr($token['data']['item']));
        $o_session = (new SobotSessionMessage())->getByStartAndEnd($startDate, $endDate);
        if (!empty($o_session)) {
            $result = $o_session->updateRecord($data);
            if (empty($result)) {
                Logger::dayLog('script/sobot/getsessionmessage', '更新失败', $data, $result);
                exit('yi_access_token error');
            }
        } else {
            $result = (new SobotSessionMessage())->addRecord($data);
            if (empty($result)) {
                Logger::dayLog('script/sobot/getsessionmessage', '记录失败', $data, $result);
                exit('yi_access_token error');
            }
        }
        exit('success');
    }
}