<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_guide_key".
 *
 * @property string $id
 * @property integer $comefrom
 * @property string $key
 * @property string $create_time
 */
class Guide_key extends \app\models\BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_guide_key';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['comefrom'], 'integer'],
            [['create_time'], 'required'],
            [['create_time'], 'safe'],
            [['key'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'comefrom' => 'Comefrom',
            'key' => 'Key',
            'create_time' => 'Create Time',
        ];
    }

    public function getKey($code) {
        $result = self::find()->where(['comefrom' => $code])->one();
        return $result ? $result->key : '';
    }

}
