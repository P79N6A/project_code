<?php
/**
 * 天行数科银联四要素验证
 * 内部错误码范围 10000
 * @author lijin
 */
namespace app\modules\api\controllers;
use app\common\Logger;
use app\models\sina\SinaBankcode;
use app\models\sina\SinaBindbank;
use app\models\sina\SinaBindbankLog;
use app\models\sina\SinaRemit;
use app\models\sina\SinaUser;
use app\modules\api\common\ApiController;
use app\modules\api\common\sinapay\Sinapay;
use app\modules\api\common\sinapay\CSinaRemit;
use Yii;

class SinapayController extends ApiController {
    /**
     * 服务id号
     */
    protected $server_id = 15;

    /**
     * 银行四联API
     */
    private $oSinaApi;

    public function init() {
        parent::init();
    }

    // 绑卡接口
    public function actionBindcard() {
        //1 字段检测, 重组参数
        $data = $this->reqData;
        if (!isset($data['name'])) {
            return $this->resp(150001, "姓名不能为空");
        }
        if (!isset($data['idcard'])) {
            return $this->resp(150002, "身份证不能为空");
        }
        if (!isset($data['phone'])) {
            return $this->resp(150003, "手机号不能为空");
        }
        if (!isset($data['cardno'])) {
            return $this->resp(150004, "卡号不能为空");
        }
        if (!isset($data['request_id'])) {
            return $this->resp(150005, "请求request_id不能为空,且必须唯一");
        }
        $data = $this->getBindRequest($data);
        if (empty($data['bankcode'])) {
            return $this->resp(150015, "不支持该银行卡");
        }

        $oUser = new SinaUser;
        $oBind = new SinaBindbank;
        $oLog = new SinaBindbankLog;
        $oSinapayApi = new Sinapay;

        //2 查询request_id是否重复
        $res = $oLog->getByRequestId($data['request_id']);
        if ($res) {
            return $this->resp(150006, "request_id:{$data['request_id']}已经存在了!");
        }

        //3 本地数据库是否已绑定
        $bind_data = $oBind->getSameCard($data['identity_id'], $data['cardno']);
        if ($bind_data) {
            //return $this->resp(150007, "卡信息已经存在,重复绑定!");
            $response = [
                'request_id' => $bind_data['request_id'],
                'aid' => $bind_data['aid'],
                'user_id' => $bind_data['user_id'],
                'identity_id' => $bind_data['identity_id'],
                'cardno' => $bind_data['cardno'],
                'sina_card_id' => $bind_data['sina_card_id'],
                'type' => 'sina_api',
            ];
            return $this->resp(0, $response);
        }

        //4 从日志中获取是否超限
        $is_top = $oLog->chkQueryNum($data['cardno']);
        if ($is_top) {
            return $this->resp(150008, "今日此卡绑定次数过多");
        }

        //5 若日志存在相同的数据, 返回上次同样的错误
        $log = $oLog->existSameCard($data);
        if ($log && $log->status == SinaBindbankLog::STATUS_FAIL) {
            $errormsg = $log->response_message ? $log->response_message : "失败";
            return $this->resp(150009, $errormsg);
        }

        //6 保存到请求日志log中
        $result = $oLog->saveData($data);
        if (!$result) {
            Logger::dayLog('sinapay', 'bindcard', 'saveData', $oLog->errors, $oLog->errinfo, 'data', $data);
        }

        //7 检测否注册,没有自动注册
        $user_model = $oUser->regs($data);
        // 当注册失败时
        if (!$user_model) {
            $error = $oUser->errinfo;
            $err_data = json_decode($error, true);
            Logger::dayLog('sinapay', 'bindcard', 'register', $err_data);

            $oLog->status = SinaBindbankLog::STATUS_FAIL;
            $oLog->response_code = $err_data['response_code'];
            $oLog->response_message = $err_data['response_message'];
            $oLog->response = $error;
            $oLog->modify_time = date('Y-m-d H:i:s');
            $res = $oLog->save();
            if (!$res) {
                Logger::dayLog('sinapay', 'bindcard', 'bindlog', 'save', $oLog->attributes, $oLog->errors);
            }

            return $this->resp(150010, $oLog->response_message);
        }

        //8 调用绑卡接口
        $bindcard_data = [
            'bank_code' => $data['bankcode'],
            'bank_account_no' => $data['cardno'],
            'card_type' => $data['card_type'],
            'ip' => $data['ip'],
        ];
        $sina_card_id = $oSinapayApi->binding_bank_card($data['identity_id'], $data['request_id'], $bindcard_data);
        if ($oSinapayApi->isTimeout()) {
            $sina_card_id = $oSinapayApi->binding_bank_card($data['identity_id'], $data['request_id'], $bindcard_data);
        }

        // 当绑定失败时
        if (!$sina_card_id) {
            $error = $oSinapayApi->errinfo;
            $err_data = json_decode($error, true);
            Logger::dayLog('sinapay', 'bindcard', 'bind', $err_data);

            $oLog->status = SinaBindbankLog::STATUS_FAIL;
            $oLog->response_code = $err_data['response_code'];
            $oLog->response_message = $err_data['response_message'];
            $oLog->response = $error;
            $oLog->modify_time = date('Y-m-d H:i:s');
            $res = $oLog->save();
            if (!$res) {
                Logger::dayLog('sinapay', 'bindcard', 'bindlog', 'save', $oLog->attributes, $oLog->errors);
            }

            return $this->resp(150011, $oLog->response_message);
        }

        //9 保存正确结果
        $oLog->sina_card_id = $sina_card_id;
        $oLog->status = SinaBindbankLog::STATUS_OK;
        $oLog->modify_time = date('Y-m-d H:i:s');
        $res = $oLog->save();
        if (!$res) {
            Logger::dayLog('sinapay', 'bindcard', 'bindlog', 'save', $oLog->attributes, $oLog->errors);
        }

        //10 保存到绑卡表中
        $bind_data = $oLog->attributes;
        $oBindbank = new SinaBindbank;
        $result = $oBindbank->saveData($bind_data);
        if (!$result) {
            Logger::dayLog("sinapay", 'bindcard', "bindbank", $bind_data, "错误原因", $oSinaBindbank->errors);
            return $this->resp(150012, "绑卡保存失败");
        }

        //11 成功时
        $response = [
            'request_id' => $bind_data['request_id'],
            'aid' => $bind_data['aid'],
            'user_id' => $bind_data['user_id'],
            'identity_id' => $bind_data['identity_id'],
            'cardno' => $bind_data['cardno'],
            'sina_card_id' => $bind_data['sina_card_id'],
            'type' => 'sina_api',
        ];
        return $this->resp(0, $response);
    }
    /**
     * 获取请求参数 150001-100
     * @param  [] $data 参数类型
     * @return [] 重组参数
     */
    private function getBindRequest($data) {
        $aid = $this->appData['id'];

        $oLog = new SinaBindbankLog;
        $identity_id = $oLog->generatorIdentityId($aid, $data['user_id']);
        $card_type = $oLog->getCardType($data['card_type']);
        $data['bankcode'] = (new SinaBankcode)->getBankCode($data['cardno']);
        return [
            'request_id' => $data['request_id'],
            'aid' => $aid,
            'user_id' => $data['user_id'],
            'identity_id' => $identity_id,
            'cardno' => $data['cardno'],
            'name' => $data['name'],
            'idcard' => $data['idcard'],
            'phone' => $data['phone'],
            'bankcode' => $data['bankcode'],
            'card_type' => $card_type,
            'ip' => $data['ip'] ? $data['ip'] : $this->getLocalIP(),
        ];
    }

    // 密码接口 150100 - 120
    public function actionPaypassword() {
        //1 字段检测, 重组参数
        $data = $this->reqData;
        $aid = $this->appData['id'];
        if (!isset($data['user_id'])) {
            return $this->resp(150101, "user_id不能为空");
        }
        if (!isset($data['passwordurl'])) {
            return $this->resp(150102, "passwordurl不能为空");
        }
        $server = isset($data['op']) ? $data['op'] : 'set_pay_password';

        //2. 获取用户
        $oLog = new SinaBindbankLog;
        $identity_id = $oLog->generatorIdentityId($aid, $data['user_id']);
        $oUser = SinaUser::getByIdentityId($identity_id);
        if (!$oUser) {
            return $this->resp(150103, "没有该用户");
        }
        if ($oUser->password_valid == 1 && $server == 'set_pay_password') {
            return $this->resp(150104, "密码已经设置过!");
        }

        //3. 保存回调地址
        $oUser->modify_time = date('Y-m-d H:i:s');
        $oUser->passwordurl = $data['passwordurl'];
        $res = $oUser->save();
        if (!$res) {
            Logger::dayLog("sinapay", "paypassword", $oUser->attributes, $oUser->errors);
            return $this->resp(150105, "保存回调地址失败");
        }

        //4. 调用接口
        $open_passwordurl = Yii::$app->request->hostInfo . "/api/sinaback/passwordurl?identity_id={$identity_id}";
        $oSinapayApi = new Sinapay();
        $url = $oSinapayApi->all_pay_password($identity_id, $server, $open_passwordurl);
        if (!$url) {
            $error = $oSinapayApi->errinfo;
            $err_data = json_decode($error, true);
            Logger::dayLog('sinapay', 'paypassword', 'all_pay_password', $identity_id, $server, $err_data);
            return $this->resp(150106, $err_data['response_message']);
        }

        //5. 返回正确结果
        $response = [
            'user_id' => $data['user_id'],
            'identity_id' => $identity_id,
            'redirect_url' => $url,
        ];
        return $this->resp(0, $response);
    }

    // 出款接口 150200 - 299
    public function actionRemit() {
        //1  参数验证
        $data = $this->reqData;
        $aid = $this->appData['id'];
        if (!isset($data['req_id'])) {
            return $this->resp(150203, "req_id订单请求号不能为空");
        }
        if (!isset($data['user_id'])) {
            return $this->resp(150201, "user_id不能为空");
        }
        if (!isset($data['cardno'])) {
            return $this->resp(150202, "卡号不能为空");
        }
        if (!isset($data['settle_amount'])) {
            return $this->resp(150208, "出款金额不能为空");
        }
        if (!isset($data['callbackurl'])) {
            return $this->resp(150209, "callbackurl必须设置");
        }
        if (!isset($data['ip'])) {
            $data['ip'] = $this->getLocalIP();
        }

        //2 查询用户
        $oLog = new SinaBindbankLog;
        $identity_id = $oLog->generatorIdentityId($aid, $data['user_id']);

        //3 查询request_id是否重复
        $oRemit = new SinaRemit;
        $res = $oRemit->getByReqId($data['req_id']);
        if ($res) {
            return $this->resp(150204, "此订单已经存在");
        }

        //4 查询绑卡信息
        $oBind = new SinaBindbank;
        $card = $oBind->getSameCard($identity_id, $data['cardno']);
        if (!$card) {
            return $this->resp(150205, "出款失败, 没有绑定该卡");
        }

        //5 帐号是否激活
        $user_model = SinaUser::getByIdentityId($identity_id);
        if (empty($user_model)) {
            return $this->resp(150206, "帐号不存在");
        }
        $is_valid = $user_model->validUser();
        if (!$is_valid) {
            return $this->resp(150210, '帐号存在异常');
        }

        //6 保存出款信息
        $data['identity_id'] = $identity_id;
        $data['aid'] = $aid;
        $data['sina_card_id'] = $card->sina_card_id;
        $oSinaRemit = new SinaRemit;
        $res = $oSinaRemit->saveData($data);
        if (!$res) {
            Logger::dayLog("sinapay", 'remit', '保存失败', $data, $oSinaRemit->errors);
            return $this->resp(150207, '系统异常');
        }

        //7 调用接口
        $oCSinaRemit = new  CSinaRemit;
        $result = $oCSinaRemit -> doRemit($oSinaRemit);

        //8. 返回正确结果
        $response = [
            'req_id' => $oSinaRemit['req_id'],
            'client_id' => $oSinaRemit['client_id'],
            'settle_amount' => $oSinaRemit['settle_amount'],
            'remit_status' => $oSinaRemit['remit_status'],
            'rsp_status' => $oSinaRemit['rsp_status'],
            'rsp_status_text' => $oSinaRemit['rsp_status_text'],
        ];
        return $this->resp(0, $response);
    }
    /**
     * 获取请求参数 150001-100
     * @param  [] $data 参数类型
     * @return [] 重组参数
     */
    private function getRemitApiData($data) {
        $remit_data = [];
        $remit_data['notify_url'] = Yii::$app->request->hostInfo . '/api/sinaback/remitnotifyurl';
        $remit_data['out_trade_no'] = $data['req_id'];
        $remit_data['out_trade_code'] = '2001'; //2001 代付借款金2002 代付（本金/收益）金
        $remit_data['amount'] = $data['settle_amount'];

        $remit_data['identity_id'] = $data['identity_id'];
        $remit_data['sina_card_id'] = $data['sina_card_id'];
        //$remit_data['goods_id'] =  1;//'1469764926'; // 标的信息
        $remit_data['summary'] = '无';
        $remit_data['ip'] = $data['ip'];
        return $remit_data;
    }
    private function getLocalIP() {
        return '121.199.129.16';
    }
}
