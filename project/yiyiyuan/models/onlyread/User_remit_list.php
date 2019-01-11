<?php

namespace app\models\onlyread;

/**
 * This is the model class for table "{{%yi_user_remit_list}}".
 *
 * @property string $id
 * @property string $order_id
 * @property string $loan_id
 * @property string $admin_id
 * @property string $settle_request_id
 * @property string $real_amount
 * @property string $settle_fee
 * @property string $settle_amount
 * @property string $rsp_code
 * @property string $remit_status
 * @property string $create_time
 * @property string $bank_id
 * @property string $user_id
 * @property integer $type
 * @property string $last_modify_time
 * @property string $remit_time
 * @property integer $fund
 * @property integer $payment_channel
 */
class User_remit_list extends ReadBaseModel {

    const CN_SINA = 1; // 新浪(废弃)
    const CN_ZX = 2; // 中信(废弃)
    const CN_JF = 3; // 玖富(暂时废弃)
    const CN_BF = 8; // 宝付
    const CN_BF_YYY = 107; // 宝付(同8)
    const CN_BF_PEANUT = 114; // 宝付
    const CN_RB = 6; // 融宝(即将废弃2017.6.26)
    const CN_RB_YYY = 110; //融宝一亿元(同6)
    const CN_RB_PEANUT = 112; // 融宝花生米富
    const CN_CHANGJIE = 117; // 畅捷代付
    const FUND_PEANUT = 1; //米富
    const FUND_JF = 2; //玖富
    const FUND_LIANJIAO = 3; //联交所
    const FUND_JINLIAN = 4; //金联储
    const FUND_XIAONUO = 5; //小诺
    const FUND_WEISM = 6; //微神马
    const FUND_CUNGUAN = 10; //存管

    /**
     * @inheritdoc
     */

    public static function tableName() {
        return 'yi_user_remit_list';
    }   

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['admin_id', 'create_time', 'bank_id'], 'required'],
            [['loan_id', 'admin_id', 'bank_id', 'user_id', 'type', 'fund', 'payment_channel', 'version'], 'integer'],
            [['real_amount', 'settle_fee', 'settle_amount'], 'number'],
            [['create_time', 'last_modify_time', 'remit_time'], 'safe'],
            [['order_id', 'settle_request_id'], 'string', 'max' => 32],
            [['rsp_code'], 'string', 'max' => 30],
            [['rsp_msg'], 'string', 'max' => 50],
            [['remit_status'], 'string', 'max' => 12],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'order_id' => '订单编号',
            'loan_id' => 'Loan ID',
            'admin_id' => 'Admin ID',
            'settle_request_id' => '结算请求号',
            'real_amount' => '实际出款金额',
            'settle_fee' => '出款手续费',
            'settle_amount' => '结算金额',
            'rsp_code' => '操作码',
            'rsp_msg' => '出错原因',
            'remit_status' => '打款状态',
            'create_time' => '担保卡添加时间',
            'bank_id' => 'Bank ID',
            'user_id' => '用户ID',
            'type' => '出款类型',
            'last_modify_time' => '最后修改时间',
            'remit_time' => '出款时间',
            'fund' => '资金方',
            'payment_channel' => '出款通道',
        ];
    }

    /**
     * 乐观所版本号
     * * */
    public function optimisticLock() {
        return "version";
    }

   
}
