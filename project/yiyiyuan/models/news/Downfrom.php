<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_downfrom".
 *
 * @property integer $id
 * @property string $downfrom
 * @property string $name
 * @property string $create_time
 */
class Downfrom extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_downfrom';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time'], 'required'],
            [['create_time'], 'safe'],
            [['downfrom', 'name'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'downfrom' => 'Downfrom',
            'name' => 'Name',
            'create_time' => 'Create Time',
        ];
    }
    
    /**
     * 查询downfrom
     */
    public function selectDownfrom($pages) {
        $data = self::find()->offset($pages->offset)->limit($pages->limit)->orderBy('id desc')->all();
        return $data;
    }
}
