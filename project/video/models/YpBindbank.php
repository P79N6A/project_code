<?php

namespace app\models;

use \app\common\Logger;

/**
 * 易宝绑卡
 *
 * @property integer $status
 */
class YpBindbank extends \app\models\BaseModel {

    // 支付状态
    const STATUS_INIT = 0;
    const STATUS_REQOK = 1; // 请求成功
    const STATUS_BINDOK = 2; // 绑定成功
    const STATUS_BINDFAIL = 11; // 绑定失败
    const STATUS_REQNO = 12; // 请求失败

    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yp_bindbank';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['aid', 'identityid', 'identitytype', 'requestid', 'cardno', 'idcardno', 'username', 'phone', 'userip'], 'required'],
            [['aid', 'identitytype', 'create_time', 'modify_time', 'error_code', 'status'], 'integer'],
            [['cardno', 'registeridcardno', 'os', 'imei'], 'string', 'max' => 50],
            [['registerdate'], 'string', 'max' => 25],
            [['identityid', 'requestid'], 'string', 'max' => 50],
            [['card_top', 'card_last', 'idcardtype', 'registeridcardtype', 'smscode'], 'string', 'max' => 10],
            [['codesender', 'bankcode', 'idcardno', 'username', 'phone', 'registerphone', 'registercontact', 'userip'], 'string', 'max' => 20],
            [['registerip'], 'string', 'max' => 30],
            [['ua'], 'string', 'max' => 100],
            [['requestid'], 'unique', 'message' => 'requestid已经存在!'],
            //[['identityid', 'identitytype', 'cardno'], 'unique', 'targetAttribute' => ['identityid', 'identitytype', 'cardno'], 'message' => '此identityid已经绑定过相同的银行卡'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '主键',
            'aid' => '应用id',
            'identityid' => '商户生成的用户唯一',
            'identitytype' => '用户标识类',
            'requestid' => '商户生成的唯一绑卡请求号，最长',
            'cardno' => '银行卡号',
            'idcardtype' => '证件类型固定值01',
            'idcardno' => '身份证号',
            'username' => '姓名',
            'phone' => '银行留存电话',
            'registerphone' => '（可选）用户在商户的系统注册的手机号',
            'registerdate' => '（可选）用户注册日期，易宝是ymdhis格式',
            'registerip' => '（可选）用户IP',
            'registeridcardtype' => '（可选）用户注册证 01',
            'registeridcardno' => '（可选）用户注册身份证',
            'registercontact' => '（可选）用户注册电话',
            'os' => '（可选）用户使用的操作系统',
            'imei' => '（可选）设备唯一标识',
            'userip' => '（可选）用户请求ip地址',
            'ua' => '（可选）浏览器信息',
            'create_time' => '(内部)创建时间',
            'modify_time' => '(内部)最后修改时间',
            'error_code' => '(内部)易宝返回错误码',
            'smscode' => '短信验证码',
            'status' => '0:默认; 1:请求成功 2:绑定成功 ;11:请求失败; 12:绑定失败',
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
        $one = static::find()->where(['requestid' => $requestid])->one();
        if (!$one) {
            return FALSE;
        }
        $one->status = $status;
        return $one->save();
    }
    /**
     * 检测绑定关系
     */
    /*public function authbind($identitytype,$identityid){
    if(!$identitytype){
    return null;
    }
    if(!$identityid){
    return null;
    }
    return static::find()->where([
    'identitytype'=>$identitytype,
    'identityid'=>$identityid

    ])->one();
    }*/
    /**
     * 获取某帐号绑定状态
     */
    /*public function isBindByIdentity($identitytype,$identityid){
    $one = $this->authbind($identitytype,$identityid);
    if( !$one ){
    return 0;
    }
    return $one -> status == 2;
    }*/
    /**
     * 判断某帐号是否成功绑定过此卡
     */
    public function chkSameUserCard($aid, $identityid, $cardno) {
        $total = static::find()->where([
            'aid' => $aid,
            'identityid' => $identityid,
            'cardno' => $cardno,
            'status' => self::STATUS_BINDOK,
        ])->count();

        return $total > 0;
    }
    /**
     * 判断某帐号是否成功绑定过此卡
     */
    public function getByRequest($requestid, $aid) {
        if (!$requestid || !$aid) {
            return null;
        }
        return static::find()->where([
            'requestid' => $requestid,
            'aid' => $aid,
        ])->one();
    }
    /**
     * 发送短信
     */
    public function sendSms($phone, $smscode, $codesender, $amount, $aid = 1) {
        $result = false;
        $codesender = strtoupper($codesender);
        switch ($codesender) {
        case 'MERCHANT':
            $amount = $amount / 100;
            $result = $this->sendSmsContent($phone, $smscode, $amount, $aid);
            break;

        case 'YEEPAY':
            $result = true;
            break;

        case 'BANK':
            $result = true;
            break;

        default:
            $result = false;
        }
        return $result;
    }
    /**
     * 短信模板
     * @param str $smscode
     * @param  intege $amount
     * @param  int $aid
     * @return  bool
     */
    private function sendSmsContent($phone, $smscode, $amount, $aid) {
        $channel = 'yunxin'; //yunxin | chuanglan
        $oSms = new Sms;
        switch ($aid) {
        case 4:
            $sign = '【花生米富】';
            $content = "{$smscode}支付验证码.即将支付金额{$amount}元，如有问题请联系花生米富微信客服。";
            $result = $oSms->sendCodeBySign($phone, $content, $sign, $channel);
            break;
        case 1:
            $channel = 'chuanglan'; //yunxin | chuanglan
            $sign = '【先花一亿元】';
            $content = "{$smscode}，验证码 （为了资金安全，请勿将验证码告知他人）。支付金额{$amount}元，如有问题请联系先花一亿元微信客服。";
            $result = $oSms->sendCodeBySign($phone, $content, $sign, $channel);
            break;
        default:
            # code...
            $result = false;
            break;
        }
        Logger::daylog(
            'sms/yptztind',
            $result ? 'success' : 'error',
            'phone', $phone,
            'smscode', $smscode
        );
        return $result ? true : false;
    }
}
