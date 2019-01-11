<?php

namespace app\models\yeepay;

//use app\models;
/**
 * This is the model class for table "{{txsk_bind_bank}}".
 *
 */
use app\common\Func;
use \app\common\Logger;
class YeepayBindBank extends \app\models\BaseModel {
    // 状态
    const STATUS_INIT = 0; // 初始
    const STATUS_OK = 1; // 绑卡成功
    const STATUS_FAIL = 2; // 绑卡失败
    const STATUS_OVER = 3; // 解绑
    const STATUS_REQOK = 4; // 请求成功
    const STATUS_REQNO = 5; // 请求失败

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{yeepay_bind_bank}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['channel_id', 'requestid', 'clientid', 'cardno', 'bankname', 'idcardno', 'username', 'phone', 'create_time', 'modify_time'], 'required'],
            [['channel_id', 'status', 'error_code'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['cardno', 'requestid', 'clientid'], 'string', 'max' => 50],
            [['bankname', 'idcardno', 'username', 'phone'], 'string', 'max' => 20],
            [['smscode'], 'string', 'max' => 10],
            [['error_msg'], 'string', 'max' => 100],
            [['clientid'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '主键',
            'channel_id' => '支付通道',
            'requestid' => '商户生成的唯一绑卡请求号',
            'clientid' => '请求易宝的唯一绑卡请求号',
            'cardno' => '银行卡号',
            'bankname' => '银行名称',
            'idcardno' => '身份证号',
            'username' => '姓名',
            'phone' => '银行留存电话',
            'create_time' => '创建时间',
            'modify_time' => '最后修改时间',
            'error_code' => '易宝返回错误码',
            'error_msg' => '易宝返回原因',
            'smscode' => '短信验证码',
            'status' => '状态:0:初始; 1:成功; 2:失败; 3解绑',
        ];
    }
    /**
     * 每日同一身份证五次查询
     * @param string $idcard
     * @return bool
     */
    public function chkQueryNum($idcard) {
        if (!$idcard) {
            return false;
        }
        $today = date('Y-m-d');
        $total = self::find()->where(['idcardno' => $idcard])
            ->andWhere(['>=', 'create_time', $today])
            ->count();

        // 每日 限定为5次
        $limit = 5;
        return $total < $limit;
    }
    /**
     * 是否存在在日志当中
     * @param [] $data
     * @return bool
     */
//    public function existOne($data) {
//        if (!is_array($data)) {
//            return false;
//        }
//        $where = [
//            'cardno' => $data['cardno'],
//            'idcard' => $data['idcard'],
//            'username' => $data['username'],
//            'phone' => $data['phone'],
//        ];
//        return self::find()->where($where)->one();
//    }
    /**
     * 四要素验证失败后缓存3天
     * @param [] $data
     * @return bool
     */
    public function existSameFail($data) {
        if (!is_array($data)) {
            return null;
        }
        $daybefore = date('Y-m-d', strtotime('-3 day'));
        $where = [
            'AND',
            [
                'cardno' => $data['cardno'],
                'idcardno' => $data['idcardno'],
                'username' => $data['username'],
                'phone' => $data['phone'],
                'status' => static::STATUS_FAIL,
            ],
            ['>', 'create_time', $daybefore],
        ];
        return self::find()->where($where)->limit(1)->one();
    }
//    /**
//     * 保存到数据库中
//     */
//    public function savaData($postData) {
//        if (!is_array($postData)) {
//            return false;
//        }
//        $data = [
//            'aid' => $postData['aid'],
//            'cardno' => $postData['cardno'],
//            'idcard' => $postData['idcard'],
//            'username' => $postData['username'],
//            'phone' => $postData['phone'],
//            'error_code' => 0,
//            'error_msg' => '',
//            'status' => static::STATUS_INIT,
//            'create_time' => date('Y-m-d H:i:s'),
//        ];
//
//        $error = $this->chkAttributes($data);
//        if ($error) {
//            return $this->returnError(false, implode("|", $error));
//        }
//        return $this->save();
//    }

    /**
     * 从主订单进行绑卡操作
     * @param  [type] $oPayorder [description]
     * @return [type]            [description]
     */
    public function savaData($data, $status) {
        //1 生成结果
        if (!is_array($data)) {
            return false;
        }
        $time = date('Y-m-d H:i:s');
        $clientid = "c" . $data['channel_id'] . '_' . time() . '_' . rand(10000, 99999);

        $postData = [
            'channel_id' => $data['channel_id'],
            'requestid' => $data['requestid'], //商户生成的用户唯一标识
            'clientid' => $clientid, //请求易宝的唯一绑卡请求号
            'cardno' => $data['cardno'], //银行卡号√string
            'bankname' => $data['bankname'],
            'idcardno' => $data['idcard'],//证件号√string
            'username' => $data['username'],  //持卡人姓名√string
            'phone' => $data['phone'], //银行预留手机号√string
            'create_time' => $time,
            'modify_time' => $time,
            'error_code' => '0',
            'error_msg' => '',
            'smscode' => '',
            'status' => $status,
        ];

        //2. 字段检查是否正确
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
            $this->status = self::STATUS_REQNO; //请求绑卡失败
            $result = $this->save();
        } else {
            // 成功时处理逻辑
            $this->status = self::STATUS_REQOK; //请求绑卡成功
            $this->codesender = $ybResult['codesender'];
            $this->smscode = $ybResult['smscode'] ? $ybResult['smscode'] : '';
            $result = $this->save();
        }

        //2. 纪录数据库错误日志
        if (!$result) {
            Logger::dayLog( 'yeepay/bindcard',  'bindbank/saveReqStatus', $ybResult, $this->errors );
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
            $this->status = static::STATUS_FAIL; //绑卡失败
            $result = $this->save();
        } else {
            // 成功时处理逻辑
            $this->status = static::STATUS_OK; //绑卡成功
            $result = $this->save();
        }

        //2. 纪录数据库错误日志
        if (!$result) {
            Logger::dayLog(  'yeepay/bindcard',   'bindbank/saveRspStatus', $ybResult,  $this->errors  );
            return false;
        }
        return true;
    }
}

