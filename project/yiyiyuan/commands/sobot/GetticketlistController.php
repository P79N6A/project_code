<?php
namespace app\commands\sobot;

use app\commands\BaseController;
use app\commonapi\ApiSobot;
use app\commonapi\Logger;
use app\models\news\Accesstoken;
use app\models\news\SobotTicketList;

class GetticketlistController extends BaseController {
    public function actionIndex() {
        $o_access_token = (new Accesstoken())->getByType(4);
        $is_invalid = true;
        if (!empty($o_access_token)) {
            $is_invalid = $o_access_token->isInvalid(24);
        }
        if (empty($o_access_token) || empty($is_invalid)) {
            Logger::dayLog('script/sobot/getticketlist', 'token不存在or失效', $o_access_token, $is_invalid);
            exit('token error');
        }
        //获取客服列表
        $result_json = (new ApiSobot())->getService($o_access_token->access_token);
        $result = json_decode($result_json, true);
        if (empty($result) || $result['code'] != '1000,正常返回!' || empty($result['data']['serviceList'])) {
            Logger::dayLog('script/sobot/getticketlist', '接口错误', $result_json, $result);
            exit('error');
        }
        $is_error = '';
        foreach ($result['data']['serviceList'] as $item) {
            $ticket_json = (new ApiSobot())->getTicketList($o_access_token->access_token, $item['serviceId'], 1);
            $ticket = json_decode($ticket_json, true);
            if (empty($ticket) || $ticket['code'] != '1000,正常返回!' || empty($ticket['data'])) {
                continue;
            }
            foreach ($ticket['data'] as $value) {
                $o_ticket = (new SobotTicketList())->getByTicketId($value['ticketId']);
                $value['stringFields'] = json_encode($value['stringFields']);
                $value['doubleFields'] = json_encode($value['doubleFields']);
                $value['dealFields'] = json_encode($value['dealFields']);
                $value['res_json'] = json_encode($value);
                $value = (new ApiSobot())->updateStr($value);
                if (!empty($o_ticket)) {
                    $ticket_result = $o_ticket->updateRecord($value);
                } else {
                    $ticket_result = (new SobotTicketList())->addRecord($value);
                }
                if (empty($ticket_result)) {
                    Logger::dayLog('script/sobot/getticketlist', '更新失败', $ticket_result, $value);
                    $is_error = 'yi_sobot_ticket_list';
                }
            }
        }
        if (!empty($is_error)) {
            exit('yi_sobot_ticket_list error');
        }
        exit('success');
    }
}