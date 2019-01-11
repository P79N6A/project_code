<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_open_temp".
 *
 * @property string $user_id
 * @property integer $type
 * @property string $create_time
 */
class Open_temp extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_open_temp';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'create_time'], 'required'],
            [['user_id', 'type'], 'integer'],
            [['create_time', 'user_create_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'type' => 'Type',
            'create_time' => 'Create Time',
            'user_create_time' => 'User Create Time',
        ];
    }

    public function addList($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data = $condition;
        $data['create_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

}
