<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_activity_condition".
 *
 * @property string $id
 * @property integer $activity_id
 * @property integer $rule_condition
 * @property integer $rule_num
 * @property integer $is_accumulation
 * @property string $create_time
 * @property integer $version
 */
class ActivityCondition extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_activity_condition';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['activity_id', 'rule_condition', 'rule_num', 'is_accumulation', 'version'], 'integer'],
            [['create_time'], 'required'],
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
            'activity_id' => 'Activity ID',
            'rule_condition' => 'Rule Condition',
            'rule_num' => 'Rule Num',
            'is_accumulation' => 'Is Accumulation',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }

    public function save_address($condition) {
        if(!$condition || !is_array($condition)){
            return false;
        }
        $condition['create_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($condition);
        if ($error) {
            return $error;
        }
        return $this->save();
    }
}
