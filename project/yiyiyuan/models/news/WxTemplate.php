<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_wx_template".
 *
 * @property integer $id
 * @property string $template_id
 * @property string $url
 * @property string $data
 * @property string $send_time
 * @property integer $send_num
 * @property integer $audit_person
 * @property string $apply_user
 * @property string $create_time
 * @property string $update_time
 */
class WxTemplate extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_wx_template';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['data', 'audit_person', 'apply_user'], 'required'],
            [['data'], 'string'],
            [['send_time', 'create_time', 'update_time'], 'safe'],
            [['send_num', 'audit_person'], 'integer'],
            [['template_id'], 'string', 'max' => 100],
            [['url'], 'string', 'max' => 255],
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
            'template_id' => 'Template ID',
            'url' => 'Url',
            'data' => 'Data',
            'send_time' => 'Send Time',
            'send_num' => 'Send Num',
            'audit_person' => 'Audit Person',
            'apply_user' => 'Apply User',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }

    public function addTemplate($condition) {
        if(!$condition || !is_array($condition)){
            return false;
        }
        $time_now = date('Y-m-d H:i:s');
        $condition['create_time'] = date('Y-m-d H:i:s');
        $condition['update_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($condition);
        if ($error) {
            return $error;
        }
        return $this->save();
    }

    public function updateTemplate($condition){
        if(!$condition || !is_array($condition)){
            return false;
        }
        $condition['update_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($condition);
        if ($error) {
            return $error;
        }
        return $this->save();
    }
}
