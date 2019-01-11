<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_application_list".
 *
 * @property string $id
 * @property string $user_id
 * @property string $mobile
 * @property string $content
 * @property integer $type
 * @property string $app_name
 * @property string $app_package
 * @property integer $status
 * @property string $send_time
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class ApplicationList extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_application_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'type', 'version'], 'integer'],
            [['content'], 'string'],
            [['last_modify_time', 'create_time'], 'safe'],
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
            'content' => 'Content',
            'type' => 'Type',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }
    public function save_address($condition) {
        if(!$condition || !is_array($condition)){
            return false;
        }
        $condition['last_modify_time'] = date('Y-m-d H:i:s');
        $condition['create_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($condition);
        if ($error) {
            return $error;
        }
        return $this->save();
    }

    public function update_address($condition) {
        if(!$condition || !is_array($condition)){
            return false;
        }
        $condition['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($condition);
        if ($error) {
            return $error;
        }
        return $this->save();
    }

    /**
     * 封装规则检查
     */
    public function chkAttributes($postData) {
        $this->attributes = $postData;

        // 当提交无错误时
        if ($this->validate()) {
            return null;
        }

        // 有错误时,只取第一个错误就ok了
        $errors = [];
        foreach ($this->errors as $attribute => $es) {
            $errors[$attribute] = $es[0];
        }
        return $errors;
    }
}
