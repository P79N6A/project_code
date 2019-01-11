<?php

namespace app\modules\balance\models\yyy;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "yi_insure".
 *
 * @property string $id
 * @property string $req_id
 * @property string $order_id
 * @property string $user_id
 * @property string $loan_id
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
class Insure extends YyyBase
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
            [['user_id', 'loan_id', 'type', 'source', 'status', 'version'], 'integer'],
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
            'req_id' => 'Req ID',
            'order_id' => 'Order ID',
            'user_id' => 'User ID',
            'loan_id' => 'Loan ID',
            'type' => 'Type',
            'source' => 'Source',
            'status' => 'Status',
            'money' => 'Money',
            'actual_money' => 'Actual Money',
            'paybill' => 'Paybill',
            'repay_time' => 'Repay Time',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }

    /**
     * 实际支付金额
     * @param $condition
     * @return int
     */
    public function insureServer($condition)
    {
        if (empty($condition)){
            return 0;
        }
        $where_config = [
            'AND',
            ['>=', 'create_time', ArrayHelper::getValue($condition, 'start_time')],
            ['<=', 'create_time', ArrayHelper::getValue($condition, 'end_time')],
            ['=', 'status', 1],
        ];
        $total = self::find()->where($where_config)->sum('actual_money');
        return empty($total) ? 0 : $total;
    }
}