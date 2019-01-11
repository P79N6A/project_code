<?php

namespace app\models\yeepay;

use app\common\Func;
use \app\common\Logger;

/**
 * 易宝投资通绑卡从表
 *
 */
class YpBindbank extends \app\models\BaseModel {

    // 支付状态
    const STATUS_INIT = 0;
    const STATUS_REQOK = 1; // 请求成功
    const STATUS_BINDOK = 2; // 绑定成功
    const STATUS_OVER = 3; // 解绑成功
    const STATUS_BINDFAIL = 11; // 绑定失败
    const STATUS_REQNO = 12; // 请求失败

    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'pay_yb_bindbank';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['aid', 'channel_id', 'identityid','cli_identityid', 'requestid', 'cardno', 'bankname', 'idcard', 'name', 'phone', 'userip', 'create_time', 'modify_time',], 'required'],
            [['aid', 'channel_id', 'status'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['cli_identityid', 'requestid', 'cardno', 'bankname'], 'string', 'max' => 50],
            [['bankcode', 'idcard', 'name', 'phone', 'userip', 'identityid',  'codesender','error_code'], 'string', 'max' => 20],
            [['idcardtype', 'smscode'], 'string', 'max' => 10],
            [['error_msg'], 'string', 'max' => 100],
            [['requestid'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '主键',
            'aid' => '应用id',
            'channel_id' => '支付通道id',
            'identityid' => '商户生成的用户唯一',
            'requestid' => '商户生成的唯一绑卡请求号，最长',
            'cardno' => '银行卡号',
            'bankname' => '银行名称',
            'bankcode' => '银行编号',
            'idcardtype' => '证件类型固定值01',
            'idcard' => '身份证号',
            'name' => '姓名',
            'phone' => '银行留存电话',
            'userip' => '（可选）用户请求ip地址',
            'create_time' => '(内部)创建时间',
            'modify_time' => '(内部)最后修改时间',
            'error_code' => '(内部)易宝返回错误码',
            'error_msg' => '(内部)易宝返回原因',
            'codesender' => '短信发送方 YEEPAY：易宝发送 | BANK：银行发送 | MERCHANT：商户发送',
            'smscode' => '短信验证码',
            'status' => '0:默认; 1:请求成功; 2:绑定成功 ;11:请求失败; 12:绑定失败',
        ];
    }
    /**
     * 更新状态
     */
    public function updateStatus($requestid, $status) {
        if (!$requestid) {
            return FALSE;
        }
        if (!is_numeric($status)) {
            return FALSE;
        }
        $one = static::find()->where(['requestid' => $requestid])->limit(1)->one();
        if (!$one) {
            return FALSE;
        }
        $one->status = $status;
        $one->modify_time = date('Y-m-d H:i:s', time());
        return $one->save();
    }
    public function saveFail() {

    }
    public function saveSuccess() {
        $this->status = self::STATUS_BINDOK;
        $result = $this->save();
        if (!$result) {
            return false;
        }

        // @todo
        //导入到 主绑定表  pay_bindbank
    }
    /**
     * 判断某帐号是否成功绑定过此卡
     */
    public function getSameUserCard($channel_id, $identityid, $cardno) {
        $where = [
            'channel_id' => $channel_id,
            'identityid' => $identityid,
            'cardno' => $cardno,
            'status' => self::STATUS_BINDOK,
        ];
        $one = static::find()->where($where)->limit(1) -> one();
        return $one;
    }
    /**
     * 判断某帐号是否成功绑定过此卡
     */
    public function getByRequest($requestid) {
        if (!$requestid) {
            return null;
        }
        $where = [
            'requestid' => $requestid,
        ];
        return static::find()->where($where)->limit(1)->one();
    }

    /**
     * 从主订单进行绑卡操作
     * @param  [type] $oPayorder [description]
     * @return [type]            [description]
     */
    public function saveCard($data, $status=0) {
        //1 生成结果
        $userip = Func::get_client_ip();
        if (!$userip) {
            $userip = '127.0.0.1';
        }

        $time = date('Y-m-d H:i:s');
        $request_id = "p" . $data['channel_id'] . '_' . time() . '_' . rand(10000, 99999);

        $cli_identityid = $this->getPayIdentityid(
                    $data['identityid'], 
                    $data['cardno'], 
                    $data['channel_id']
                );

        $postData = [
            'aid' => intval($data['aid']),
            'channel_id' =>  $data['channel_id'], 
            'identityid' =>  $data['identityid'], //用户标识√string最长50位，商户生成的用户唯一标识
            'cli_identityid' =>  $cli_identityid, //用户标识√string最长50位，商户生成的用户唯一标识
            'requestid' => $request_id, //'xhh_13581524051_8', //绑卡请求号√string商户生成的唯一绑卡请求号，最长50位
            'cardno' => $data['cardno'], //银行卡号√string
            'bankname' => $data['bankname'],
            'bankcode' => '',
            'idcard' => $data['idcard'],//证件号√string
            'name' => $data['name'],  //持卡人姓名√string
            'phone' => $data['phone'], //银行预留手机号√string
            'userip' => $userip,
            'create_time' => $time,
            'modify_time' => $time,
            'error_code' => '',
            'error_msg' => '',
            'smscode' => '',
            'codesender' => '',
            'status' => intval($status),
        ];

        //2 是否已经绑定
        // 检测是否已经成功绑定过该卡@todo 此处易宝是不建议校验的
        $isBind = $this->getSameUserCard(
            $postData['channel_id'],
            $postData['identityid'],
            $postData['cardno']
        );
        if ($isBind) {
            return true;
        }

        //3. 字段检查是否正确
        if ($errors = $this->chkAttributes($postData)) {
			Logger::dayLog(
                'yeepay/bindcard',
                '提交数据', $postData,
                '失败原因', $errors
            );
            return $this->returnError(false, "数据保存失败");
        }
        return $this->save();
    }
    /**
     * 保存易宝请求状态结果
     * @return bool
     */
    public function saveReqStatus($ybResult) {
        //1. 保存易宝请求绑定结果信息
        $this->modify_time = date('Y-m-d H:i:s');
        $isError = is_array($ybResult) && isset($ybResult['error_code']);
        if ($isError) {
            // 失败时处理逻辑
            $this->error_code = (string)$ybResult['error_code'];
            $this->error_msg = (string)$ybResult['error_msg'];
            $this->status = self::STATUS_REQNO; //请求失败
            $result = $this->save();
        } else {
            // 成功时处理逻辑
            $this->status = self::STATUS_REQOK; //请求成功
            $this->codesender = $ybResult['codesender'];
            $this->smscode = $ybResult['smscode'] ? $ybResult['smscode'] : '';
            $result = $this->save();
        }

        //2. 纪录数据库错误日志
        if (!$result) {
            Logger::dayLog( 'yeepay/tzt',  'bindbank/saveReqStatus', $ybResult, $this->errors );
            return false;
        }
        return true;
    }
    /**
     * 处理最终绑卡结果
     * @param  string $ybResult
     * @return bool
     */
    public function saveRspStatus($ybResult) {
        //1 处理最终结果
        $this->modify_time = date('Y-m-d H:i:s');
        $isError = is_array($ybResult) &&  isset($ybResult['error_code']);
        if ($isError) {
            // 失败时处理逻辑
            $this->error_code = (string)$ybResult['error_code'];
            $this->error_msg = (string)$ybResult['error_msg'];
            $this->status = YpBindbank::STATUS_BINDFAIL; //绑定失败
            $result = $this->save();
        } else {
            // 成功时处理逻辑
            $this->status = YpBindbank::STATUS_BINDOK; //确认绑定成功
            $result = $this->save();
        }

        //2. 纪录数据库错误日志
        if (!$result) {
            Logger::dayLog(  'yeepay/tzt',   'bindbank/saveRspStatus', $ybResult,  $this->errors  );
            return false;
        }
        return true;
    }
    /**
     * 转换
     */
    public function getPayIdentityid($identityid, $cardno, $channel_id) {
        if (!$identityid || !$cardno) {
            return '';
        }
        $card_top = substr($cardno, 0, 6);
        $card_last = substr($cardno, -4);
        $identityid = $identityid . '-' . $card_top . $card_last;

        $cli_identityid = Func::toYeepayCode($identityid, $channel_id); 
        return $cli_identityid;
    }
    /**
     * 保存易宝新版投资通请求状态结果
     * @return bool
     */
    public function saveNewReqStatus($ybResult) {
        //1. 保存易宝请求绑定结果信息
        $this->modify_time = date('Y-m-d H:i:s');
        if (!empty($ybResult['errorcode'])) {
            // 失败时处理逻辑
            $this->error_code = (string)$ybResult['errorcode'];
            $this->error_msg = (string)$ybResult['errormsg'];
            $this->status = self::STATUS_REQNO; //请求失败
            $result = $this->save();
        } else {
            // 成功时处理逻辑
            $this->status = self::STATUS_REQOK; //请求成功
            $this->codesender = $ybResult['codesender'];
            $this->smscode = $ybResult['smscode'] ? $ybResult['smscode'] : '';
            $result = $this->save();
        }

        //2. 纪录数据库错误日志
        if (!$result) {
            Logger::dayLog( 'yeepay/newtzt',  'bindbank/saveNewReqStatus', $ybResult, $this->errors );
            return false;
        }
        return true;
    }
    /**
     * 处理新版投资通最终绑卡结果
     * @param  string $ybResult
     * @return bool
     */
    public function saveNewRspStatus($ybResult) {
        //1 处理最终结果
        $this->modify_time = date('Y-m-d H:i:s');
        if (!empty($ybResult['errorcode'])) {
            // 失败时处理逻辑
            $this->error_code = (string)$ybResult['errorcode'];
            $this->error_msg = (string)$ybResult['errormsg'];
            $this->status = YpBindbank::STATUS_BINDFAIL; //绑定失败
            $result = $this->save();
        } else {
            // 成功时处理逻辑
            $this->status = YpBindbank::STATUS_BINDOK; //确认绑定成功
            $result = $this->save();
        }

        //2. 纪录数据库错误日志
        if (!$result) {
            Logger::dayLog(  'yeepay/newtzt',   'bindbank/saveNewRspStatus', $ybResult,  $this->errors  );
            return false;
        }
        return true;
    }
}
