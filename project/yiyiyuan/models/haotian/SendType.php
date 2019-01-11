<?php

namespace app\models\haotian;

use Yii;

/**
 * This is the model class for table "send_type".
 *
 * @property string $id
 * @property integer $send_type
 * @property integer $sms_type
 * @property string $send_content
 * @property string $number
 * @property integer $days
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class SendType extends HaotianBaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sms_send_type';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['send_type', 'sms_type', 'days', 'version'], 'integer'],
            [['number'], 'number'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['send_content'], 'string', 'max' => 255],
            [['title'], 'string', 'max' => 60]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'send_type' => 'Send Type',
            'sms_type' => 'Sms Type',
            'send_content' => 'Send Content',
            'number' => 'Number',
            'days' => 'Days',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }
}
