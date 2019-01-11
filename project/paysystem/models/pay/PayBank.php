<?php

namespace app\models\pay;

use Yii;

/**
 * This is the model class for table "pay_bank".
 *
 * @property integer $id
 * @property integer $channel_id
 * @property string $std_bankname
 * @property string $bankname
 * @property string $bankcode
 * @property integer $card_type
 * @property integer $status
 * @property string $limit_max_amount
 * @property string $limit_day_amount
 * @property integer $limit_day_total
 * @property string $limit_date
 * @property integer $limit_start_hour
 * @property integer $limit_end_hour
 * @property string $create_time
 */
class PayBank extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pay_bank';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['channel_id', 'std_bankname', 'bankname', 'bankcode', 'limit_date', 'limit_start_hour', 'limit_end_hour', 'create_time'], 'required'],
            [['channel_id', 'card_type', 'status', 'limit_day_total', 'limit_start_hour', 'limit_end_hour'], 'integer'],
            [['limit_max_amount', 'limit_day_amount'], 'number'],
            [['create_time'], 'safe'],
            [['std_bankname', 'bankname', 'bankcode'], 'string', 'max' => 30],
            [['limit_date'], 'string', 'max' => 10]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'channel_id' => 'pay_channel.id',
            'std_bankname' => '标准银行名称',
            'bankname' => '银行名称',
            'bankcode' => '银行编号',
            'card_type' => '1:储蓄卡; 2:信用卡',
            'status' => '0:未启用; 1:正常;  2:临时关闭;',
            'limit_max_amount' => '单笔限额:默认5w',
            'limit_day_amount' => '日限额:默认5w',
            'limit_day_total' => '日限数',
            'limit_date' => '日期:有则指定日期, 为空则不限日期',
            'limit_start_hour' => '限定起始小时',
            'limit_end_hour' => '限定结束小时',
            'create_time' => '创建时间',
        ];
    }
}
