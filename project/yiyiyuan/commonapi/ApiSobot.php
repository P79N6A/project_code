<?php
namespace app\commonapi;

use app\common\Curl;
use Yii;

class ApiSobot {
    private $appId = '7ab75f255d0943f4ba5ff9986df0c5ed';
    private $appKey = 'AxrV9m1onlu3';

    private $ticket_appId = 'f6d3504002d04065a8b4ed55373b7a41';//工单appId
    private $ticket_appKey = 'Hl82PLjXl5Cq';//工单appKey

    //企业ID：f0af5952377b4331a3499999b77867c2

    public function httpPost($url, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSLVERSION, 3);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_URL, $url);
        $ret = curl_exec($ch);

        curl_close($ch);
        return $ret;
    }

    private function httpGet($url) {//get https的内容
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //不输出内容
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * 获取access_token
     * @return string
     * @author 王新龙
     * @date 2018/9/29 19:35
     */
    public function getToken() {
        $time = time();
        $data = [
            'appId' => $this->appId,
            'appKey' => $this->appKey,
            'createTime' => $time
        ];
        $sign = '';
        foreach ($data as $key => $val) {
            $sign .= $val;
        }
        $md5Sign = md5($sign);
        $url = 'https://open.sobot.com/open/platform/getAccessToken.json?appId=' . $this->appId . '&createTime=' . $time . '&sign=' . $md5Sign . '&expire=24';
        $result_json = $this->httpGet($url);
        Logger::dayLog('api/report/gettoken', $result_json, $url);
        $result = json_decode($result_json, true);
        if (!$result) {
            return '{"code":"404","rsp_msg":"service error"}';
        }
        if ($result['code'] != '1000' || empty($result['data'])) {
            return '{"code":"200","rsp_msg":"api error"}';
        }
        return $result_json;
    }

    /**
     * 在线统计，机器人会话概览接口
     * @return string
     * @author 王新龙
     * @date 2018/10/11 15:22
     */
    public function getSession($access_token, $data) {
        $action = 'wb_session_robot';
        $params = [
            'action' => $action,
            'access_token' => $access_token,
            'data' => $data,
        ];
        $url = 'https://open.sobot.com/open/platform/api.json';
        $result_json = Http::curl_json('post', $url, json_encode($params));
        Logger::dayLog('api/report/getsession', $result_json, $url, $params);
        $result = json_decode($result_json, true);
        if (!$result) {
            return '{"rsp_code":"404","rsp_msg":"service error"}';
        }
        if ($result['code'] != '1000' || empty($result['data'])) {
            return '{"rsp_code":"200","rsp_msg":"api error"}';
        }
        return $result_json;
    }


    /**
     * 在线统计，机器人满意度评价统计
     * @return string
     * @author 王新龙
     * @date 2018/10/11 17:52
     */
    public function getSatisfaction($access_token, $data) {
        $action = 'wb_robot_satisfaction';
        $params = [
            'action' => $action,
            'access_token' => $access_token,
            'data' => $data,
        ];
        $url = 'https://open.sobot.com/open/platform/api.json';
        $result_json = Http::curl_json('post', $url, json_encode($params));
        Logger::dayLog('api/report/getsession', $result_json, $url, $params);
        $result = json_decode($result_json, true);
        if (!$result) {
            return '{"code":"404","rsp_msg":"service error"}';
        }
        if ($result['code'] != '1000' || empty($result['data'])) {
            return '{"code":"200","rsp_msg":"api error"}';
        }
        return $result_json;
    }

    /**
     * 在线统计，人工会话概览接口
     * @param $access_token
     * @param $data
     * @return mixed|string
     * @author 王新龙
     * @date 2018/10/11 18:53
     */
    public function getSessionHuman($access_token, $data) {
        $action = 'wb_robot_satisfaction';
        $params = [
            'action' => $action,
            'access_token' => $access_token,
            'data' => $data,
        ];
        $url = 'https://open.sobot.com/open/platform/api.json';
        $result_json = Http::curl_json('post', $url, json_encode($params));
        Logger::dayLog('api/report/getsession', $result_json, $url, $params);
        $result = json_decode($result_json, true);
        if (!$result) {
            return '{"code":"404","rsp_msg":"service error"}';
        }
        if ($result['code'] != '1000' || empty($result['data'])) {
            return '{"code":"200","rsp_msg":"api error"}';
        }
        return $result_json;
    }

    /**
     * 在线统计，人工满意度评价统计
     * @param $access_token
     * @param $data
     * @return mixed|string
     * @author 王新龙
     * @date 2018/10/11 18:54
     */
    public function getHumanSatisfaction($access_token, $data) {
        $action = 'wb_human_satisfaction';
        $params = [
            'action' => $action,
            'access_token' => $access_token,
            'data' => $data,
        ];
        $url = 'https://open.sobot.com/open/platform/api.json';
        $result_json = Http::curl_json('post', $url, json_encode($params));
        Logger::dayLog('api/report/gethumansatisfaction', $result_json, $url, $params);
        $result = json_decode($result_json, true);
        if (!$result) {
            return '{"code":"404","rsp_msg":"service error"}';
        }
        if ($result['code'] != '1000' || empty($result['data'])) {
            return '{"code":"200","rsp_msg":"api error"}';
        }
        return $result_json;
    }

    /**
     * 在线统计，客户会话概览接口
     * @param $access_token
     * @param $data
     * @return mixed|string
     * @author 王新龙
     * @date 2018/10/11 19:28
     */
    public function getSessionCustomer($access_token, $data) {
        $action = 'wb_session_customer';
        $params = [
            'action' => $action,
            'access_token' => $access_token,
            'data' => $data,
        ];
        $url = 'https://open.sobot.com/open/platform/api.json';
        $result_json = Http::curl_json('post', $url, json_encode($params));
        Logger::dayLog('api/report/getsessioncustomer', $result_json, $url, $params);
        $result = json_decode($result_json, true);
        if (!$result) {
            return '{"code":"404","rsp_msg":"service error"}';
        }
        if ($result['code'] != '1000' || empty($result['data'])) {
            return '{"code":"200","rsp_msg":"api error"}';
        }
        return $result_json;
    }

    /**
     * 在线统计，会话消息统计接口
     * @param $access_token
     * @param $data
     * @return mixed|string
     * @author 王新龙
     * @date 2018/10/11 20:14
     */
    public function getSessionMessage($access_token, $data) {
        $action = 'wb_session_message';
        $params = [
            'action' => $action,
            'access_token' => $access_token,
            'data' => $data,
        ];
        $url = 'https://open.sobot.com/open/platform/api.json';
        $result_json = Http::curl_json('post', $url, json_encode($params));
        Logger::dayLog('api/report/getsessionmessage', $result_json, $url, $params);
        $result = json_decode($result_json, true);
        if (!$result) {
            return '{"code":"404","rsp_msg":"service error"}';
        }
        if ($result['code'] != '1000' || empty($result['data'])) {
            return '{"code":"200","rsp_msg":"api error"}';
        }
        return $result_json;
    }

    /**
     * 工单，获取token
     * @return mixed|string
     * @author 王新龙
     * @date 2018/10/12 17:23
     */
    public function getTicketToken() {
        $time = time();
        $data = [
            'appId' => $this->ticket_appId,
            'appKey' => $this->ticket_appKey,
            'createTime' => $time
        ];
        $sign = '';
        foreach ($data as $key => $val) {
            $sign .= $val;
        }
        $md5Sign = md5($sign);
        $url = 'https://www.sobot.com/ws-open/ticket/get_access_token?appId=' . $this->ticket_appId . '&createTime=' . $time . '&serviceEmail=xingxuefei@xianhuahua.com&expire=24&sign=' . $md5Sign;
        $result_json = $this->httpGet($url);
        Logger::dayLog('api/report/gettickettoken', $result_json, $url);
        $result = json_decode($result_json, true);
        if (!$result) {
            return '{"code":"404","rsp_msg":"service error"}';
        }
        if ($result['code'] != '1000,正常返回!' || empty($result['data'])) {
            return '{"code":"200","rsp_msg":"api error"}';
        }
        return $result_json;
    }

    /**
     * 工单，获取客服列表
     * @param $access_token
     * @return mixed|string
     * @author 王新龙
     * @date 2018/10/16 18:04
     */
    public function getService($access_token) {
        $params = [
            'access_token' => $access_token
        ];
        $url = 'https://www.sobot.com/ws-open/ticket/getDataDict';
        $result_json = Http::curl_json('post', $url, json_encode($params));
        Logger::dayLog('api/report/getservice', $result_json, $url, $params);
        $result = json_decode($result_json, true);
        if (!$result) {
            return '{"code":"404","rsp_msg":"service error"}';
        }
        if ($result['code'] != '1000,正常返回!' || empty($result['data'])) {
            return '{"code":"200","rsp_msg":"api error"}';
        }
        return $result_json;
    }

    /**
     * 工单，获取工单列表
     * @param $access_token
     * @param $id
     * @param $type
     * @return mixed|string
     * @author 王新龙
     * @date 2018/10/16 19:42
     */
    public function getTicketList($access_token, $id, $type) {
        $params = [
            'access_token' => $access_token,
            'id' => $id,
            'queryType' => $type
        ];
        $url = 'https://www.sobot.com/ws-open/ticket/getTicketList';
        $result_json = Http::curl_json('post', $url, json_encode($params));
        Logger::dayLog('api/report/getticketlist', $result_json, $url, $params);
        $result = json_decode($result_json, true);
        if (!$result) {
            return '{"code":"404","rsp_msg":"service error"}';
        }
        if ($result['code'] != '1000,正常返回!') {
            return '{"code":"200","rsp_msg":"api error"}';
        }
        return $result_json;
    }

    public function updateStr($arr) {
        if (empty($arr) || !is_array($arr)) {
            return $arr;
        }
        $list = [];
        foreach ($arr as $key => $value) {
            if (empty($value) && $value === 0) {
                unset($item);
            }
            $list[$key] = (string)$value;
        }
        return $list;
    }
}