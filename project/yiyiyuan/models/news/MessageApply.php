<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yx_message_apply".
 *
 * @property string $id
 * @property string $title
 * @property string $contact
 * @property integer $type
 * @property integer $apply_depart
 * @property string $apply_user
 * @property integer $audit_person
 * @property integer $apply_status
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $version
 */
class MessageApply extends \app\models\BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_message_apply';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
                [['platform_type', 'msg_type', 'type', 'apply_depart', 'apply_user', 'audit_person', 'apply_status', 'create_time', 'last_modify_time'], 'required'],
                [['contact', 'push_contact'], 'string'],
                [['push_contact'], 'string', 'max' => '30'],
                [['type', 'apply_depart', 'audit_person', 'apply_status', 'version', 'back_action', 'exec_status'], 'integer'],
                [['send_time', 'create_time', 'last_modify_time', 'msg_type', 'platform_type'], 'safe'],
                [['title'], 'string', 'max' => 1024],
                [['push_title'], 'string', 'max' => 15],
                [['apply_user'], 'string', 'max' => 32],
                [['back_url'], 'string', 'max' => 256]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id'               => 'ID',
            'title'            => 'Title',
            'contact'          => 'Contact',
            'type'             => 'Type',
            'apply_depart'     => 'Apply Depart',
            'apply_user'       => 'Apply User',
            'audit_person'     => 'Audit Person',
            'apply_status'     => 'Apply Status',
            'send_time'        => 'Send_time',
            'create_time'      => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
            'version'          => 'Version',
        ];
    }

    /**
     * 乐观所版本号
     * @return string
     */
    public function optimisticLock() {
        return "version";
    }

    /**
     * 锁定
     */
    public function lockAll($ids) {
        if (!is_array($ids) || empty($ids)) {
            return 0;
        }
        $rows = static::updateAll(['status' => 1,'exec_status' => 2], ['id' => $ids]);
        return $rows;
    }

    public function getUrl() {
        return $this->hasMany(MessageUpload::className(), ['mid' => 'id']);
    }

    /**
     * 通过type 获取消息列表
     * @param $type 1：自定义 2：全部用户
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function getByType($type = 2) {
        $type = intval($type);
        if (!$type) {
            return null;
        }
        return self::find()->where(['type' => $type, 'apply_status' => 1])->all();
    }

    public function save_address($condition) {
        if (!$condition || !is_array($condition)) {
            return false;
        }
        $condition['apply_depart']     = 1; //申请部门默认1
//        $condition['apply_user'] = '';
//        $condition['audit_person'] =0;
//        $condition['apply_status'] = 0;
        $condition['last_modify_time'] = date('Y-m-d H:i:s');
        $condition['create_time']      = date('Y-m-d H:i:s');
        $error                         = $this->chkAttributes($condition);
        if ($error) {
            var_dump($error);
            die;
            return $error;
        }
        return $this->save();
    }

    public function getSystemmessagelist() {
        return $this->hasMany(SystemMessageList::className(), ['mid' => 'id']);
    }

}
