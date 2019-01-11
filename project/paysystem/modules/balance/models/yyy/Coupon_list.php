<?php

namespace app\modules\balance\models\yyy;

use Yii;

/**
 * This is the model class for table "yi_coupon_list".
 *
 * @property string $id
 * @property string $apply_id
 * @property string $title
 * @property integer $type
 * @property string $sn
 * @property integer $val
 * @property integer $limit
 * @property string $start_date
 * @property string $end_date
 * @property string $mobile
 * @property integer $status
 * @property string $use_time
 * @property string $create_time
 */
class Coupon_list extends YyyBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_coupon_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apply_id', 'type', 'val', 'limit', 'status'], 'integer'],
            [['title', 'val'], 'required'],
            [['start_date', 'end_date', 'use_time', 'create_time'], 'safe'],
            [['title'], 'string', 'max' => 1024],
            [['sn'], 'string', 'max' => 32],
            [['mobile'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'apply_id' => 'Apply ID',
            'title' => 'Title',
            'type' => 'Type',
            'sn' => 'Sn',
            'val' => 'Val',
            'limit' => 'Limit',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'mobile' => 'Mobile',
            'status' => 'Status',
            'use_time' => 'Use Time',
            'create_time' => 'Create Time',
        ];
    }
}