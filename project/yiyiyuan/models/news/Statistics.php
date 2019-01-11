<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;

/**
 * This is the model class for table "yi_statistics".
 *
 * @property string $id
 * @property string $user_id
 * @property string $loan_id
 * @property string $from
 * @property string $remoteip
 * @property string $user_agent
 * @property string $create_time
 * @property integer $type
 * @property string $redirect_url
 */
class Statistics extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_statistics';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'loan_id', 'type'], 'integer'],
            [['remoteip', 'user_agent', 'create_time', 'type'], 'required'],
            [['create_time'], 'safe'],
            [['from'], 'string', 'max' => 8],
            [['remoteip'], 'string', 'max' => 16],
            [['user_agent'], 'string', 'max' => 256],
            [['redirect_url'], 'string', 'max' => 128]
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
            'from' => 'From',
            'remoteip' => 'Remoteip',
            'user_agent' => 'User Agent',
            'create_time' => 'Create Time',
            'type' => 'Type',
            'redirect_url' => 'Redirect Url',
        ];
    }
}
