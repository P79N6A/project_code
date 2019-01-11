<?php

namespace app\models\dev;

use Yii;

/**
 * This is the model class for table "yi_anti_prome_v3".
 *
 * @property string $id
 * @property string $user_id
 * @property string $loan_id
 * @property integer $type
 * @property integer $model_status
 * @property integer $result_status
 * @property string $result_score
 * @property string $result_subject
 * @property string $result_time
 * @property string $modify_time
 * @property string $create_time
 */
class Anti_prome_v3 extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_anti_prome_v3';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'loan_id', 'type', 'model_status', 'result_status'], 'integer'],
            [['result_score'], 'number'],
            [['result_subject', 'result_time', 'modify_time', 'create_time'], 'required'],
            [['result_subject'], 'string'],
            [['result_time', 'modify_time', 'create_time'], 'safe']
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
            'loan_id' => 'Loan ID',
            'type' => 'Type',
            'model_status' => 'Model Status',
            'result_status' => 'Result Status',
            'result_score' => 'Result Score',
            'result_subject' => 'Result Subject',
            'result_time' => 'Result Time',
            'modify_time' => 'Modify Time',
            'create_time' => 'Create Time',
        ];
    }
}
