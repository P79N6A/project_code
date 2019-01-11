<?php

namespace app\models\credit;

use Yii;

/**
 * This is the model class for table "yx_order".
 *
 * @property string $id
 * @property string $user_id
 * @property string $card_id
 * @property string $loan_id
 * @property string $req_id
 * @property integer $source
 * @property string $order_no
 * @property string $loan_amount
 * @property string $amount
 * @property integer $pay_type
 * @property integer $status
 * @property string $callback_url
 * @property string $buy_time
 * @property string $loan_time
 * @property string $invalid_time
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class YxOrder extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yx_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'card_id', 'loan_amount', 'amount', 'callback_url', 'loan_time', 'invalid_time', 'last_modify_time', 'create_time'], 'required'],
            [['user_id', 'card_id', 'loan_id', 'req_id', 'source', 'pay_type', 'status', 'version'], 'integer'],
            [['loan_amount', 'amount'], 'number'],
            [['buy_time', 'loan_time', 'invalid_time', 'last_modify_time', 'create_time'], 'safe'],
            [['order_no'], 'string', 'max' => 64],
            [['callback_url'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '用户ID',
            'card_id' => '服务卡ID',
            'loan_id' => '借款ID',
            'req_id' => '评测表req_id',
            'source' => '来源 1一亿元;8豆荚贷;9有米花',
            'order_no' => '卡号编码',
            'loan_amount' => '借款金额',
            'amount' => '金额',
            'pay_type' => '1:正常支付2:白条支付',
            'status' => '状态 0初始;1购买成功;2失效;3已使用;4已退;',
            'callback_url' => '回调地址',
            'buy_time' => '购卡成功时间',
            'loan_time' => '借款创建时间',
            'invalid_time' => '失效时间',
            'last_modify_time' => '最后修改时间',
            'create_time' => '创建时间',
            'version' => '乐观锁版本号',
        ];
    }

    public function getOrder($where,$select = '*')
    {
        return $this->find()->where($where)->select($select)->orderBy('id DESC')->asArray()->one();
    }


    public function getAllOrder($where,$select = '*')
    {
        return $this->find()->where($where)->select($select)->all();
    }
}
