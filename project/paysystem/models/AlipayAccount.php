<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "alipay_account".
 *
 * @property integer $id
 * @property string $merid
 * @property string $key
 * @property integer $is_yq
 * @property integer $status
 * @property string $limit_max_amount
 * @property string $limit_day_amount
 * @property integer $limit_day_total
 * @property integer $limit_type
 * @property string $limit_start_time
 * @property string $limit_end_time
 * @property string $create_time
 * @property string $modify_time
 * @property string $tip
 */
class AlipayAccount extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'alipay_account';
    }

  
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merid', 'business_id','limit_start_time', 'limit_end_time', 'create_time', 'modify_time', 'tip'], 'required'],
            [['is_yq', 'business_id','status', 'limit_day_total', 'limit_type'], 'integer'],
            [['limit_max_amount', 'limit_day_amount'], 'number'],
            [['create_time', 'modify_time'], 'safe'],
            [['merid'], 'string', 'max' => 30],
            [['key', 'limit_start_time', 'limit_end_time'], 'string', 'max' => 50],
            [['tip'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'merid' => 'Merid',
            'key' => 'Key',
            'is_yq' => 'Is Yq',
            'status' => 'Status',
            'limit_max_amount' => 'Limit Max Amount',
            'limit_day_amount' => 'Limit Day Amount',
            'limit_day_total' => 'Limit Day Total',
            'limit_type' => 'Limit Type',
            'limit_start_time' => 'Limit Start Time',
            'limit_end_time' => 'Limit End Time',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
            'tip' => 'Tip',
        ];
    }
    /**
     * Undocumented function
     * 获得可用账户
     * @param [type] $is_yq
     * @return void
     */
    public function getAlipayAccount($is_yq){
        $where = [
            'is_yq'=>$is_yq,
            'status'=>1
        ];
        $data = static::find()->where($where)->limit(1)->one();
        return $data;
    }
}