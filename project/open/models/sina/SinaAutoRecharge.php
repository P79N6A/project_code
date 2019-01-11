<?php

namespace app\models\sina;
use app\common\Logger;
use app\modules\api\common\sinapay\Sinapay;

/**
 * 自动充值接口
 */
class SinaAutoRecharge extends \app\models\BaseModel {
    // 支付状态 0:初始; 1:进行中; 2:成功; 11:失败
    const STATUS_INIT = 0; //初始
    const STATUS_DOING = 1; // 进行中
    const STATUS_SUCCESS = 2; // 成功
    const STATUS_FAILURE = 11; // 支付失败

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'sina_auto_recharge';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['req_id', 'create_time', 'modify_time'], 'required'],
            [['amount'], 'number'],
            [['status', 'version'], 'integer'],
            [['create_time', 'modify_time', 'pay_time'], 'safe'],
            [['req_id', 'client_id', 'rsp_status'], 'string', 'max' => 50],
            [['trade_status'], 'string', 'max' => 20],
            [['rsp_status_text'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '结算记录ID',
            'req_id' => '订单号',
            'client_id' => '新浪订单号',
            'amount' => '[必填]结算金额',
            'status' => '0:初始; 1:进行中; 2:成功; 3:失败',
            'trade_status' => '充值状态', //:WAIT_PAY:等待付; PAY_FINISHED:已付款;TRADE_FAILED:交易失败;TRADE_FINISHED:交易结束;TRADE_CLOSED:交易关闭
            'rsp_status' => '响应状态',
            'rsp_status_text' => '响应结果',
            'create_time' => '创建时间',
            'modify_time' => '更新时间',
            'pay_time' => '充值时间',
            'version' => '乐观锁',
        ];
    }
    public function getByReqId($req_id) {
        return static::find()->where(['req_id' => $req_id])->limit(1)->one();
    }
    /**
     * 添加一条纪录到数据库
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function saveData($data) {
        //保存数据
        $time = date("Y-m-d H:i:s");
        $data = [
            'req_id' => $data['req_id'],
            'client_id' => '',
            'amount' => $data['amount'],
            'status' => static::STATUS_INIT,
            'trade_status' => '',
            'rsp_status' => '',
            'rsp_status_text' => '',
            'create_time' => $time,
            'modify_time' => $time,
            'pay_time' => '0000-00-00 00:00:00',
            'version' => 0,
        ];
        $errors = $this->chkAttributes($data);
        if ($errors) {
            Logger::dayLog('sina_auto_recharge', '保存失败', $data, $errors);
            return false;
        }

        return $this->save();
    }
    /**
     * 交易状态映射关系
     * @return [type] [description]
     */
    public function getTradeStatus() {
        return [
            'WAIT_PAY' => static::STATUS_DOING,
            'PAY_FINISHED' => static::STATUS_SUCCESS, //成功
            'TRADE_FAILED' => static::STATUS_FAILURE, // 失败
            'TRADE_FINISHED' => static::STATUS_SUCCESS, //成功
            'TRADE_CLOSED' => static::STATUS_DOING,
        ];
    }
    /**
     * 回写响应结果
     * $this 操作数据
     * @param $rsp_status 接口响应状态
     * @param $rsp_status_text 接口响应结果
     * @param $trade_status 交易状态
     * @return bool
     */
    public function saveRspStatus($rsp_status, $rsp_status_text, $trade_status, $client_id) {
        $this->rsp_status = $rsp_status;
        $this->rsp_status_text = $rsp_status_text;
        $this->trade_status = $trade_status;
        if ($client_id) {
            $this->client_id = $client_id;
        }

        if ($rsp_status == 'APPLY_SUCCESS') {
            // 交易状态
            $map = $this->getTradeStatus();
            if ($trade_status && isset($map[$trade_status])) {
                $this->status = $map[$trade_status];
            }
        } else {
            $this->status = static::STATUS_FAILURE;
        }

        // 终态时更新充值时间
        if (in_array($this->status, [static::STATUS_SUCCESS, static::STATUS_FAILURE])) {
            $this->pay_time = date('Y-m-d H:i:s');
        }

        $this->modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }
    /**
     * 锁定进行中状态
     */
    private function lockStatus() {
        $this->status = static::STATUS_DOING;
        $res = $this->save();
        return $res;
    }
    /**
     * 公司充值到中间帐号.
     * @return bool
     */
    public function innerpay($out_trade_no, $amount, $ip) {
        //1 锁定状态
        if ($this->status != static::STATUS_INIT) {
            // 仅初始化的才可以出款
            return false;
        }
        $res = $this->lockStatus();
        if (!$res) {
            return false;
        }
        $this->refresh();

        //2 调用接口
        $oSinapayApi = new Sinapay();
        $response = $oSinapayApi->inner_pay_money($out_trade_no, $amount, $ip);
        //@todo
        /*$response = '{"sign":"eZp1S27ZDP9KICT1IdsW+WM0hHHupetZbBrU5hreawgdmIUlzeN99IGsvbuvf9LfU4IOZOWMfpENFwUXiDSjqo8s1qdZmou4bCvzw87hlph+cTcEOiqyOvz+4c1m4Jl2tco7DpcN+eFHvIEZGn4KSFGHO3dzVtGLW/n6gcnxG0Y=","sign_version":"1.0","partner_id":"200034807310","_input_charset":"utf-8","response_time":"20160809115456","response_message":"æäº¤æå","sign_type":"RSA","response_code":"APPLY_SUCCESS","out_trade_no":"14707148959584","trade_status":"PAY_FINISHED","pay_status":"PROCESSING"}';
        $response = json_decode($response, true);*/

        //3 错误处理
        if (!$response) {
            $error = $oSinapayApi->errinfo;
            $err_data = json_decode($error, true);
            $result = $this->saveRspStatus($err_data['response_code'], $err_data['response_message'], '', '');
            Logger::dayLog("sinapay", 'db/sina_auto_recharge', '充值失败', $out_trade_no, $error);
            return false;
        }

        //4 保存出款状态
        $trade_status = isset($response['trade_status']) ? $response['trade_status'] : '';
        $client_id = isset($response['inner_trade_no']) ? $response['inner_trade_no'] : '';
        $result = $this->saveRspStatus($response['response_code'], $response['response_message'], $trade_status, $client_id);
        return $result;
    }
    public function optimisticLock() {
        return "version";
    }
    /**
     * 生成一条充值纪录
     * @param  str $req_id
     * @param  int $amount
     * @return bool
     */
    public function generate($req_id, $amount) {
        $one = static::find()->where(['req_id' => $req_id])->limit(1)->one();
        if ($one) {
            return false;
        }

        $data = [
            'req_id' => $req_id,
            'amount' => $amount,
        ];
        $result = $this->saveData($data);
        return $result;
    }
}
