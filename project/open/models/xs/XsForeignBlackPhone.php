<?php

namespace app\models\xs;

use Yii;

/**
 * This is the model class for table "dc_foreign_black_phone".
 *
 * @property string $id
 * @property string $phone
 * @property integer $match_status
 * @property string $modify_time
 * @property string $create_time
 */
class XsForeignBlackPhone extends \app\models\repo\CloudBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dc_foreign_black_phone';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phone', 'modify_time', 'create_time'], 'required'],
            [['match_status'], 'integer'],
            [['modify_time', 'create_time'], 'safe'],
            [['phone'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'phone' => '手机号',
            'match_status' => '黑名单状态',
            'modify_time' => '修改时间',
            'create_time' => '创建时间',
        ];
    }
}
