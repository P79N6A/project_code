<?php
/**
 * 短信有三个通道
 * yunxin:云信; 维客:vike; 微信通:wxt100
 */
namespace app\modules\api\controllers;

use app\common\Http;
use app\common\Logger;
use app\models\Flow;
use app\models\Sms;
use app\modules\api\common\ApiController;
use app\modules\api\common\sms\Sms100;

/**
 * 短信服务
 * 内部错误码范围1000-1999
 */
class SmsController extends ApiController {

    /**
     * 服务id号
     */
    protected $server_id = 1;

    /**
     * 初始化
     */
    public function init() {
        parent::init();
    }

    /***************** start 通用接口 *********************/
    /**
     * 通用接口1: 短信发送(云信)
     *
     */
    public function actionSendyunxin() {
        //1 短信数据保存
        $oSms = $this->saveData($this->reqData, 'yunxin');
        if (!$oSms || !$oSms['id']) {
            return $this->resp(1006, '保存失败');
        }

        //2 根据短信类型判断不同的接口
        if ($oSms['sms_type'] == 1) {
            //1:触发类;
            $result = Http::sendByMobile($oSms['receive_mobile'], $oSms['content']);
        } elseif ($oSms['sms_type'] == 2) {
            //2:营销群发类
            $result = Http::sendMarketingToMobile($oSms['receive_mobile'], $oSms['content']);
        } else {
            return $this->resp(1006, '短信类型参数错误');
        }

        //3 返回结果
        return $this->resp(0, [
            'result' => $result,
            'msgid' => '',
            'channel_type' => 'yunxin',
        ]);
    }
    /**
     * 通用接口2: 短信发送 (维克)
     */
    public function actionSendvike() {
        //1 $type 1为一亿元；2为先花花
        if (!isset($this->reqData['type'])) {
            return $this->resp(10003, "发送类型不能为空");
        }
        $type = $this->reqData['type'];

        //2 短信数据保存
        $oSms = $this->saveData($this->reqData, 'vike');
        if (!$oSms || !$oSms['id']) {
            return $this->resp(1006, '未知错误');
        }

        //3 调用接口
        $result = Http::sendVikeToMobile($oSms['receive_mobile'], $oSms['content'], $type);

        return $this->resp(0, [
            'result' => $result,
            'msgid' => '',
            'channel_type' => 'vike',
        ]);
    }

    /**
     * 通用接口3: wxt100 触发短信 发送短信接口
     */
    public function actionSendwxt100() {
        //1 参数验证
        $oSms = $this->saveData($this->reqData, 'wxt100');
        if (!$oSms || !$oSms['id']) {
            return $this->resp(1006, '未知错误');
        }

        //2 调用短信接口
        $env = YII_ENV_DEV ? 'dev' : 'prod';
        //$env = 'prod';
        $oApi = new Sms100($env);
        $res = $oApi->sendSms($oSms['receive_mobile'], $oSms['content'], $oSms['sms_type']);
        if (!$res) {
            Logger::dayLog("sms", "wxt100", 'api', $oApi->errinfo);
            return $this->resp(1006, $oApi->errinfo);
        }

        //3 保存数据库
        $oSms->msgid = $res['MsgID'];
        $result = $oSms->save();
        if (!$result) {
            Logger::dayLog("sms", "wxt100", 'db', $oSms->errors);
            return $this->resp(1006, $oSms->errinfo);
        }

        //4 返回结果
        return $this->resp(0, [
            'result' => true,
            'msgid' => $res['MsgID'],
            'channel_type' => 'wxt100',
        ]);
    }
    /**
     * 通用接口4 EMA短信发送通道
     */
    public function actionSendema() {
        //1 字段检测
        $data = $this->reqData;
        if (!isset($data['mobile'])) {
            return $this->resp(10001, "手机号不能为空");
        }
        if (!isset($data['content'])) {
            return $this->resp(10002, "短信内容不能为空");
        }
        if (!isset($data['type'])) {
            return $this->resp(10003, "类型不能为空");
        }

        $mobile = $data['mobile'];
        $content = $data['content'];
        $type = $data['type'];
        $result = Http::sendEmatoMobile($mobile, $content, $type);
        $sms = new Sms();
        $ret = $sms->addSms($mobile, $content, 'ema', $type, '');

        //4 返回结果
        return $this->resp(0, [
            'result' => $result,
        ]);
    }

    /**
     * 检测数据是否合法
     */
    private function saveData($data, $channel_type) {
        //1 参数验证
        if (!is_array($data)) {
            return $this->resp(1001, '参数不正确');
        }
        if (!isset($data['mobile'])) {
            return $this->resp(1002, '手机号不能为空');
        }
        if (!isset($data['content'])) {
            return $this->resp(1003, "短信内容不能为空");
        }

        $sms_type = 1; //短信类型: 1:触发类; 2:群发类
        if (isset($data['sms_type'])) {
            $sms_type = $data['sms_type'];
        }
        if (!in_array($sms_type, [1, 2])) {
            return $this->resp(1005, "发送类型设置错误");
        }

        //2 组合结果
        $smsData = [
            'aid' => $this->appData['id'],
            'msgid' => '',
            'code' => isset($data['code']) ? $data['code'] : '',
            'receive_mobile' => $data['mobile'],
            'content' => $data['content'],
            'channel_type' => $channel_type,
            'sms_type' => $sms_type,
        ];

        //3 保存到数据库信息
        $oSms = new Sms();
        $result = $oSms->addSms($smsData);
        if (!$result) {
            Logger::dayLog('sms', 'wxt100', 'db', $smsData, $oSms->errors);
            return $this->resp(1006, "数据保存失败");
        }
        return $oSms;
    }

    // end 通用接口

    /**
     * 维克手机流量充值
     */
    public function actionVikemobilerecharge() {
        //1 字段检测
        $data = $this->reqData;
        if (!isset($data['mobile'])) {
            return $this->resp(10001, "手机号不能为空");
        }
        if (!isset($data['package'])) {
            return $this->resp(10002, "手机流量值不能为空");
        }
        if (!isset($data['type'])) {
            return $this->resp(10003, "充值来源不能为空");
        }

        $mobile = $data['mobile'];
        $package = $data['package'];
        $type = $data['type'];
        $result = Http::vikeFlowRecharge($mobile, $package);
        $ret_result = json_decode($result);
        $msg_id = isset($ret_result->msg_id) ? $ret_result->msg_id : '';
        $result_code = isset($ret_result->result_code) ? $ret_result->result_code : '';
        $flow = new Flow();
        $ret = $flow->addFlow($mobile, $package, 'INIT', $msg_id, $type);

        return $this->resp(0, [
            'msg_id' => $msg_id,
            'result_code' => $result_code,
        ]);
    }

    /********************* start 专用特定服务 ***********************/
    /**
     * 先花花短信验证码发送(云信)
     * 云信触发
     */
    public function actionSendsmstoxhh() {
        //1 字段检测
        $data = $this->reqData;
        if (!isset($data['mobile'])) {
            return $this->resp(10001, "手机号不能为空");
        }
        if (!isset($data['content'])) {
            return $this->resp(10002, "短信内容不能为空");
        }

        $mobile = $data['mobile'];
        $content = $data['content'];
        $result = Http::sendVerificationcodeToXhhMobile($mobile, $content);
        $sms = new Sms();
        $ret = $sms->addSms($mobile, $content, 'yunxin', 1, '');

        return $this->resp(0, [
            'result' => strval($result),
        ]);
    }

    /**
     * 一亿元短信验证码发送(云信)
     * 云信触发
     */
    public function actionSendsmstoyiyiyuan() {
        //1 字段检测
        $data = $this->reqData;
        if (!isset($data['mobile'])) {
            return $this->resp(10001, "手机号不能为空");
        }
        if (!isset($data['content'])) {
            return $this->resp(10002, "短信内容不能为空");
        }

        $mobile = $data['mobile'];
        $content = $data['content'];
        $result = Http::sendByMobile($mobile, $content);
        $sms = new Sms();
        $ret = $sms->addSms($mobile, $content, 'yunxin', 1, '');

        return $this->resp(0, [
            'result' => strval($result),
        ]);
    }

    /**
     * 群发营销类短信发送(云信)
     * 云信营销群发
     */
    public function actionSendmarketingsmstouser() {
        //1 字段检测
        $data = $this->reqData;
        if (!isset($data['mobile'])) {
            return $this->resp(10001, "手机号不能为空");
        }
        if (!isset($data['content'])) {
            return $this->resp(10002, "短信内容不能为空");
        }

        $mobile = $data['mobile'];
        $content = $data['content'];
        $result = Http::sendMarketingToMobile($mobile, $content);
        $sms = new Sms();
        $ret = $sms->addSms($mobile, $content, 'yunxin', 2, '');

        return $this->resp(0, [
            'result' => strval($result),
        ]);
    }
    //end 特定服务

    /**
     * 创蓝短信发送
     */
    public function actionSendchuanglansmstouser() {
        //1 字段检测
        $data = $this->reqData;
        if (!isset($data['mobile'])) {
            return $this->resp(10001, "手机号不能为空");
        }
        if (!isset($data['content'])) {
            return $this->resp(10002, "短信内容不能为空");
        }
        if (!isset($data['sms_type'])) {
            return $this->resp(10002, "短信类型不能为空");
        }

        $smsData = [
            'aid' => $this->appData['id'],
            'msgid' => '',
            'code' => isset($data['code']) ? $data['code'] : '',
            'receive_mobile' => $data['mobile'],
            'content' => $data['content'],
            'channel_type' => 'chuanglan',
            'sms_type' => $data['sms_type'],
        ];

        $mobile = $data['mobile'];
        $content = $data['content'];
        $sms_type = $data['sms_type'];
        $channel_type = isset($data['channel_type']) ? $data['channel_type'] : 1;
        $result = Http::sendSmsByChuanglan($mobile, $content, $channel_type);
        $sms = new Sms();
        $ret = $sms->addSms($smsData);

        return $this->resp(0, [
            'result' => strval($result),
        ]);
    }

    /**
     * 安捷信标准版短信发送
     */
    public function actionSendanjiexintouser() {
        //1 字段检测
        $data = $this->reqData;
        if (!isset($data['mobile'])) {
            return $this->resp(10001, "手机号不能为空");
        }
        if (!isset($data['content'])) {
            return $this->resp(10002, "短信内容不能为空");
        }
        if (!isset($data['sms_type'])) {
            return $this->resp(10002, "短信类型不能为空");
        }

        $smsData = [
            'aid' => $data['aid'],
            'msgid' => '',
            'code' => isset($data['code']) ? $data['code'] : '',
            'receive_mobile' => $data['mobile'],
            'content' => $data['content'],
            'channel_type' => 'anjiexin',
            'sms_type' => $data['sms_type'],
        ];
        $result = Http::sendAnjiexinToMobile($data['mobile'], $data['content']);
        $sms = new Sms();
        $ret = $sms->addSms($smsData);

        return $this->resp(0, [
            'result' => 'true',
        ]);
    }

}
