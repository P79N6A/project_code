<?php

namespace app\models\yyy;

use Yii;

/**
 * This is the model class for table "{{%yi_loan_repay}}".
 *
 * @property string $id
 * @property string $repay_id
 * @property string $user_id
 * @property string $loan_id
 * @property integer $bank_id
 * @property integer $platform
 * @property integer $source
 * @property string $pic_repay1
 * @property string $pic_repay2
 * @property string $pic_repay3
 * @property integer $status
 * @property string $money
 * @property string $actual_money
 * @property string $pay_key
 * @property string $code
 * @property string $paybill
 * @property string $last_modify_time
 * @property string $createtime
 * @property string $repay_time
 * @property string $repay_mark
 * @property integer $version
 */
class LoanRepay extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_loan_repay';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['repay_id', 'user_id', 'loan_id', 'money', 'last_modify_time', 'createtime'], 'required'],
            [['user_id', 'loan_id', 'bank_id', 'platform', 'source', 'status', 'version'], 'integer'],
            [['money', 'actual_money'], 'number'],
            [['last_modify_time', 'createtime'], 'safe'],
            [['repay_id', 'pay_key', 'repay_time'], 'string', 'max' => 32],
            [['pic_repay1', 'pic_repay2', 'pic_repay3', 'repay_mark'], 'string', 'max' => 128],
            [['code'], 'string', 'max' => 6],
            [['paybill'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'repay_id' => '还款订单号',
            'user_id' => '用户ID',
            'loan_id' => '借款ID',
            'bank_id' => '银行卡id',
            'platform' => '支付平台 (1:连连,2:易宝)',
            'source' => '还款来源',
            'pic_repay1' => '还款凭证URL',
            'pic_repay2' => 'Pic Repay2',
            'pic_repay3' => 'Pic Repay3',
            'status' => '还款记录状态，默认为0',
            'money' => '还款金额',
            'actual_money' => '实际还款金额',
            'pay_key' => '还款获取的pay_key',
            'code' => '短信验证码',
            'paybill' => '支付流水号',
            'last_modify_time' => '最后修改时间',
            'createtime' => '创建时间',
            'repay_time' => '还款到账时间',
            'repay_mark' => '后台操作人员备注',
            'version' => 'Version',
        ];
    }
    //  获取已还款次数
    public function getRepaycnt($loan_id)
    {
        $where = ['loan_id' => $loan_id,'status' => 1];
        $repay_cnt = $this->find()->where($where)->count();
        return (int)$repay_cnt;
    }
}
