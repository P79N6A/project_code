<?php

namespace app\models\xs;

use Yii;

/**
 * This is the model class for table "dc_foreign_black_idcard".
 *
 * @property string $id
 * @property string $idcard
 * @property integer $match_status
 * @property string $modify_time
 * @property string $create_time
 */
class XsForeignBlackIdcard extends \app\models\repo\CloudBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dc_foreign_black_idcard';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['idcard', 'modify_time', 'create_time'], 'required'],
            [['match_status'], 'integer'],
            [['modify_time', 'create_time'], 'safe'],
            [['idcard'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idcard' => '身份证',
            'match_status' => '命中状态',
            'modify_time' => '修改时间',
            'create_time' => '创建时间',
        ];
    }
}
