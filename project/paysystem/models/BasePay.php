<?php

namespace app\models;
use app\common\Crypt3Des;
use app\models\Payorder;
use app\common\Logger;
use Yii;

/**
 * 第三方支付基类
 */
abstract class BasePay extends \app\models\BaseModel {
    /**
     * 根据商户唯一订单号查询d
     * @param  str $cli_orderid
     * @return bool
     */
    public function getByCliOrderId($cli_orderid) {
        if (!$cli_orderid) {
            return null;
        }
        return static::find()->where(['cli_orderid' => $cli_orderid])->limit(1)->one();
    }
    /**
     * 验证是否是最终状态
     * @return boolean
     */
    public function is_finished() {
        return in_array($this->status, [Payorder::STATUS_PAYOK, Payorder::STATUS_PAYFAIL]);
    }
    /**
     * 更新订单为成功
     * @param str  $other_orderid  第三方支付定单
     * @return bool
     */
    public function savePaySuccess($other_orderid) {
        //1 状态是否终态
        $is_finished = $this->is_finished();
        if ($is_finished) {
            return false;
        }

        //2 成功处理流程
        if ($other_orderid) {
            $this->other_orderid = (string) $other_orderid;
        }

        $this->status = Payorder::STATUS_PAYOK;
        $this->modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        if (!$result) {
            Logger::dayLog('basepay', "savePaySuccess", $this->attributes, $this->errors);
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
    public function savePayFail($error_code, $error_msg, $other_orderid='') {
        //1 状态是否终态
        $is_finished = $this->is_finished();
        if ($is_finished) {
            return false;
        }

        //2. 更新为失败状
        $error_code = (string) $error_code;
        $error_code = substr($error_code, 0, 20);
        $this->error_code = $error_code;

        $error_msg = (string) $error_msg;
        $error_msg = substr($error_msg, 0, 200);
        $this->error_msg = $error_msg;

        if ($other_orderid) {
            $this->other_orderid = (string) $other_orderid;
        }

        $this->status = Payorder::STATUS_PAYFAIL;
        $this->modify_time = date('Y-m-d H:i:s');
        $result = $this->save();

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
        if (!$this->payorder) {
            return false;
        }
        // 标准化错误码 @todo
        $result = $this->payorder->saveStatus($this->status, $this->other_orderid,  $this->error_code, $this->error_msg );
        return $result;
    }
    /**
     * 返回链接地址
     * @return []
     */
    public function getPayUrls($pay_controller, $pay_type) {
        return [
            'url' => $this->getPayUrl($this->id,$pay_controller),
            'pay_type' => $pay_type,
            'status' => $this->status, //1,8
            'orderid' => $this->orderid,
        ];
    }
    /**
     * 生成绑卡的链接地址
     * @param $id
     * @return string
     */
    public function getPayUrl($id,$pay_controller) {
        $cryid = urlencode($this->encryptId($id));
        $url = Yii::$app->request->hostInfo."/{$pay_controller}/payurl/?xhhorderid={$cryid}";
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
            $id = '';
        }
        return $id;
    }
}
