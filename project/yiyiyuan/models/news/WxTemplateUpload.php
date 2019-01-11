<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_wx_template_upload".
 *
 * @property integer $id
 * @property integer $tid
 * @property string $path
 * @property integer $audit_person
 * @property string $apply_user
 * @property string $create_time
 */
class WxTemplateUpload extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_wx_template_upload';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tid', 'audit_person', 'create_time'], 'required'],
            [['tid', 'audit_person'], 'integer'],
            [['create_time'], 'safe'],
            [['path'], 'string', 'max' => 200],
            [['apply_user'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tid' => 'Tid',
            'path' => 'Path',
            'audit_person' => 'Audit Person',
            'apply_user' => 'Apply User',
            'create_time' => 'Create Time',
        ];
    }

    public function getTemplate()
    {
        return $this->hasOne(WxTemplate::className(), ['id' => 'tid']);
    }

    public function addUpload($condition) {
        if(!$condition || !is_array($condition)){
            return false;
        }
        $time_now = date('Y-m-d H:i:s');
        $condition['create_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($condition);
        if ($error) {
            return $error;
        }
        return $this->save();
    }
}
