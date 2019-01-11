<?php

namespace app\models\remit;

use Yii;

/**
 * This is the model class for table "rt_daylog".
 *
 * @property integer $id
 * @property integer $success
 * @property integer $total
 * @property integer $create_day
 */
class Daylog extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rt_daylog';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['success', 'total', 'create_day'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键',
            'success' => '起始时间的unix时间戳,对应的count为此时间戳之后1小时内的计数',
            'total' => '打款结算查询次数',
            'create_day' => '结算结果查询次数',
        ];
    }
}
