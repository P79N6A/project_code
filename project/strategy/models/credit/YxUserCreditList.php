<?php

namespace app\models\credit;

use Yii;

/**
 * This is the model class for table "yx_user_credit_list".
 *
 * @property string $id
 * @property string $user_id
 * @property string $order_id
 * @property string $credit_no
 * @property string $req_id
 * @property integer $score
 * @property integer $status
 * @property integer $res_status
 * @property string $amount
 * @property integer $days
 * @property string $interest_rate
 * @property string $crad_mondy
 * @property double $crad_rate
 * @property string $invalid_time
 * @property string $last_modify_time
 * @property string $create_time
 */
class YxUserCreditList extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yx_user_credit_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'credit_no', 'create_time'], 'required'],
            [['user_id', 'order_id', 'req_id', 'score', 'status', 'res_status', 'days'], 'integer'],
            [['amount', 'interest_rate', 'crad_mondy', 'crad_rate'], 'number'],
            [['invalid_time', 'last_modify_time', 'create_time'], 'safe'],
            [['credit_no'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '用户id',
            'order_id' => '订单id',
            'credit_no' => '评审请求id',
            'req_id' => '评审id',
            'score' => '信用分数',
            'status' => '评审状态',
            'res_status' => '是否可以借款',
            'amount' => '可借最大金额',
            'days' => '可借最大天数',
            'interest_rate' => '日息费',
            'crad_mondy' => '购卡金额',
            'crad_rate' => '购卡费率',
            'invalid_time' => '失效时间',
            'last_modify_time' => '最后修改时间',
            'create_time' => '创建时间',
        ];
    }

    public function getTotal($user_id)
    {
        return $this->find()->where(['user_id' => $user_id])->all();
    }
}
