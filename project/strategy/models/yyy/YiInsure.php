<?php

namespace app\models\yyy;

use Yii;

/**
 * This is the model class for table "yi_insure".
 *
 * @property string $id
 * @property string $req_id
 * @property string $order_id
 * @property string $user_id
 * @property string $loan_id
 * @property string $new_loan_id
 * @property integer $type
 * @property integer $source
 * @property integer $status
 * @property string $money
 * @property string $actual_money
 * @property string $paybill
 * @property string $repay_time
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class YiInsure extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_insure';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['req_id', 'order_id', 'user_id', 'loan_id', 'last_modify_time', 'create_time'], 'required'],
            [['user_id', 'loan_id', 'new_loan_id', 'type', 'source', 'status', 'version'], 'integer'],
            [['money', 'actual_money'], 'number'],
            [['repay_time', 'last_modify_time', 'create_time'], 'safe'],
            [['req_id', 'order_id', 'paybill'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'req_id' => '合保订单号',
            'order_id' => '投保订单号',
            'user_id' => '用户ID',
            'loan_id' => '借款ID',
            'new_loan_id' => '续期新生成的loanid',
            'type' => '购买类型 1:借款购买 2：主动购买',
            'source' => '来源 1微信 5android 6IOS',
            'status' => '状态 0初始 -1同步 1成功 4失败 5失效',
            'money' => '投保金额',
            'actual_money' => '实际支付金额',
            'paybill' => '流水号',
            'repay_time' => '支付时间',
            'last_modify_time' => '最后修改时间',
            'create_time' => '创建时间',
            'version' => 'Version',
        ];
    }

    public function getYisureData($where)
    {
        return $this->find()->where($where)->orderby('ID DESC')->one();
    }
}
