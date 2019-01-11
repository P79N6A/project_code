<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_activity_elastic_layer".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $activity_id
 * @property string $create_time
 * @property integer $version
 */
class ActivityElasticLayer extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_activity_elastic_layer';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'activity_id', 'create_time'], 'required'],
            [['user_id', 'activity_id', 'version'], 'integer'],
            [['create_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'activity_id' => 'Activity ID',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }

    public function save_record($condition) {
        if(!$condition || !is_array($condition)){
            return false;
        }
        $condition['create_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        return $this->save();
    }
}
