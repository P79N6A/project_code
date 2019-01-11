<?php

namespace app\models\lian;
use app\common\Crypt3Des;
use app\common\Logger;
use app\models\BindBank;
use app\models\Payorder;
use Yii;

/**
 * This is the model class for table "lian_order".
 *
 */
class LianOrder extends \app\models\BaseModel {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'lian_order';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['bind_id', 'aid', 'orderid', 'aid_orderid', 'identityid', 'amount', 'create_time', 'modify_time', 'repayment_no'], 'required'],
            [['bind_id', 'aid', 'amount', 'status', 'client_status'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['orderid'], 'string', 'max' => 25],
            [['aid_orderid'], 'string', 'max' => 30],
            [['callbackurl', 'error_msg'], 'string', 'max' => 255],
            [['lian_orderid', 'repayment_no'], 'string', 'max' => 50],
            [['error_code', 'identityid'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '主键',
            'bind_id' => '绑卡id',
            'aid' => '应用id',
            'orderid' => '客户订单号',
            'aid_orderid' => '唯一订单号',
            'identityid' => '用户',
            'amount' => '交易金额(单位：分)',
            'callbackurl' => '回调地址',
            'url_return' => '签约授权返回地址',
            'create_time' => '(内部)创建时间',
            'modify_time' => '(内部)最后修改时间',
            'status' => '(内部)0:默认;2成功;5:已撤消; 6:未支付;11失败; 13:风控',
            'client_status' => '(内部)异步通知客户端状态 0:未响应 1:响应支付成功 11：响应支付失败',
            'lian_orderid' => '(内部)畅捷流水号',
            'repayment_no' => '连连订单号',
            'error_code' => '(内部)畅捷返回错误码',
            'error_msg' => '(内部)畅捷返回错误描述',
        ];
    }
    /**
     * 获取编号
     * @param  str $orderid
     * @param  int $aid
     * @return   str
     */
    private function getRepaymentNo($orderid, $aid) {
        return "R{$aid}O{$orderid}";
    }
    private function getAOrderId($orderid, $aid) {
        return "{$aid}_{$orderid}";
    }
    public function getIdentityid($identityid, $aid) {
        return "I{$aid}U{$identityid}";
    }
    public function getByLianId($id) {
        $id = intval($id);
        if (($id > 0) === false) {
            return null;
        }

        //2. 获取订单数据
        return static::findOne($id);
    }
    /**
     * 根据商户唯一订单号查询d
     * @param  str $aid_orderid
     * @return bool
     */
    public function getByAOrderId($aid_orderid) {
        if (!$aid_orderid) {
            return null;
        }
        return static::find()->where(['aid_orderid' => $aid_orderid])->limit(1)->one();
    }
    /**
     * 根据商户唯一订单号查询d
     * @param  str $aid_orderid
     * @return bool
     */
    public function getByRepaymentNo($repayment_no) {
        if (!$repayment_no) {
            return null;
        }
        return static::find()->where(['repayment_no' => $repayment_no])->limit(1)->one();
    }
    /**
     * 验证是否是最终状态
     * @return boolean [description]
     */
    public function is_finished() {
        return in_array($this->status, [Payorder::STATUS_PAYOK, Payorder::STATUS_PAYFAIL]);
    }
    /**
     * 保存数据
     */
    public function saveOrder($postData) {
        //1 数据验证
        if (!is_array($postData) || empty($postData)) {
            return $this->returnError(null, "数据不能为空");
        }
        if (empty($postData['orderid'])) {
            return $this->returnError(null, "订单不能为空");
        }
        if (empty($postData['aid'])) {
            return $this->returnError(null, "应用id不能为空");
        }

        $repayment_no = $this->getRepaymentNo($postData['orderid'], $postData['aid']);

        //这个订单长度可能不够. 若不行使用保存的id号, 形如: 4I123
        $aid_orderid = $this->getAOrderId($postData['orderid'], $postData['aid']);

        //2  保存数据
        $time = date("Y-m-d H:i:s");
        $data = [
            'aid' => $postData['aid'],
            'bind_id' => $postData['bind_id'],
            'orderid' => $postData['orderid'],
            'aid_orderid' => $aid_orderid,
            'identityid' => $postData['identityid'],
            'amount' => $postData['amount'],
            'callbackurl' => $postData['callbackurl'],
            'url_return' => '', // 暂时无用 @todo
            'create_time' => $time,
            'modify_time' => $time,
            'status' => $postData['status'],
            'client_status' => 0,
            'lian_orderid' => '',
            'repayment_no' => $repayment_no,
            'error_code' => '',
            'error_msg' => '',
        ];

        //4  字段检测
        if ($errors = $this->chkAttributes($data)) {
            return $this->returnError(null, implode('|', $errors));
        }

        //5  保存数据
        $result = $this->save();
        if (!$result) {
            return $this->returnError(null, implode('|', $this->errors));
        }
        return true;
    }
    /**
     * 保存订单
     * @param  int $status
     * @param  string $reason
     * @return bool
     */
    /**
     * 保存订单
     * @param  int $status
     * @param  str $lian_orderid
     * @return bool
     */
    public function saveStatus($status, $lian_orderid) {
        if ($lian_orderid) {
            $this->lian_orderid = (string) $lian_orderid;
        }

        $status = intval($status);
        $this->status = $status;
        $this->modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }
    /**
     * 保存失败订单
     * @param  str $error_code
     * @param  str $error_msg
     * @return  bool
     */
    public function saveFail($error_code, $error_msg) {
        $error_code = (string) $error_code;
        $error_code = substr($error_code, 0, 20);
        $this->error_code = $error_code;

        $error_msg = (string) $error_msg;
        $error_msg = substr($error_msg, 0, 255);
        $this->error_msg = $error_msg;

        $this->status = Payorder::STATUS_PAYFAIL;
        $this->modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }
    /**
     * 生成绑卡的链接地址
     * @param $id
     * @return string
     */
    public function getLianPayUrl($id) {
        $cryid = $this->encryptId($id);
        $url = Yii::$app->request->hostInfo . '/lianpay/payurl/?xhhorderid=' . urlencode($cryid);
        return $url;
    }
    /**
     * 加解密id
     * @param  int $id
     * @return str
     */
    public function encryptId($id) {
        return Crypt3Des::encrypt((string) $id, Yii::$app->params['trideskey']);
    }
    public function decryptId($cryid) {
        if (!$cryid) {
            return '';
        }
        try {
            $id = Crypt3Des::decrypt($cryid, Yii::$app->params['trideskey']);
        } catch (\Exception $error) {
            Logger::dayLog('lian', 'lianpay/getLianId', $cryid, '不能解密', $error);
            $id = '';
        }
        return $id;
    }
    public function optimisticLock() {
        return "version";
    }
    /**
     * 由未绑定更新为绑定状态
     * @return bool
     */
    public function savePayBind($agreeno) {
        //1 更新为绑定状态
        if($this->status != Payorder::STATUS_NOBIND){
            return false;
        }
        $result = $this->saveStatus(Payorder::STATUS_BIND, '');
        if (!$result) {
            return false;
        }

        //2. 同步主订单状态
        $result = $this->upPayorderStatus();
        if (!$result) {
            return false;
        }

        //3. 更新绑卡表为成功
        $oBindBank = BindBank::findOne($this->bind_id);
        if (!$oBindBank) {
            return false;
        }
        $result = $oBindBank->setBind($agreeno);
        if (!$result) {
            return false;
        }
        return true;
    }
    /**
     * 更新订单为成功
     * @param str  $oid_paybill  连连支付订单号
     * @return bool
     */
    public function savePaySuccess($oid_paybill) {
        //1 状态是否终态
        $is_finished = $this->is_finished();
        if ($is_finished) {
            return false;
        }

        //2 成功处理流程
        $result = $this->saveStatus(Payorder::STATUS_PAYOK, $oid_paybill);
        if (!$result) {
            Logger::dayLog('lian', "lianorder/savePaySuccess", $this->attributes, $this->errors);
            return false;
        }

        //3. 同步主订单状态
        $result = $this->upPayorderStatus();
        return $result;
    }
    /**
     * 更新订单为成功
     * @param str  $error_code  错误码
     * @param str  $error_msg  错误原因
     * @return bool
     */
    public function savePayFail($error_code, $error_msg) {
        //1 状态是否终态
        $is_finished = $this->is_finished();
        if ($is_finished) {
            return false;
        }

        //2. 更新为失败状态
        $result = $this->saveFail($error_code, $error_msg);
        if (!$result) {
            Logger::dayLog('lian', "lianorder/savePayFail", $this->attributes, $this->errors);
            return false;
        }

        //3. 同步主订单状态
        $result = $this->upPayorderStatus();
        return $result;
    }
    /**
     * 同步主订单状态
     * @param  int $status
     * @param  str $res
     * @return bool
     */
    public function upPayorderStatus() {
        $oPayorder = (new Payorder)->getByOrder($this->orderid, $this->aid);
        if ($oPayorder) {
            $result = $oPayorder->saveStatus($this->status, $this->error_msg, $this->lian_orderid);
            if (!$result) {
                return false;
            }
        }
        return true;
    }
}