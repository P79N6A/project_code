<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_wx_template_list".
 *
 * @property integer $id
 * @property integer $tid
 * @property string $openid
 * @property string $mobile
 * @property string $create_time
 * @property string $update_time
 * @property string $send_time
 * @property integer $status
 * @property integer $version
 */
class WxTemplateList extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_wx_template_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tid'], 'required'],
            [['tid', 'status', 'version'], 'integer'],
            [['create_time', 'update_time', 'send_time'], 'safe'],
            [['openid'], 'string', 'max' => 64],
            [['mobile'], 'string', 'max' => 20]
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
            'openid' => 'Openid',
            'mobile' => 'Mobile',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'send_time' => 'Send Time',
            'status' => 'Status',
            'version' => 'Version',
        ];
    }

    /**
     * 乐观所版本号
     * @return string
     */
    public function optimisticLock()
    {
        return "version";
    }


    public function getTemplate()
    {
        return $this->hasOne(WxTemplate::className(), ['id' => 'tid']);
    }

    /**
     * 更新记录
     * @param  [type] $condition [description]
     * @return [type]            [description]
     */
    public function updateList($condition) {
        if (empty($condition) || !is_array($condition)) {
            return false;
        }

        $condition['update_time'] = date('Y-m-d H:i:s');

        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        $result = $this->save();
        return $result;
    }

    /**
     * 锁定
     */
    public function lockAll($ids) {
        if (!is_array($ids) || empty($ids)) {
            return 0;
        }
        $rows = static::updateAll(['status' => '1'], ['id' => $ids]);
        return $rows;
    }

    /**
     * 保存为锁定: 锁定当前纪录
     * @return  bool
     */
    public function lock() {
        $result = $this->save();
        try {
            $this->update_time = date('Y-m-d H:i:s');
            $this->status           = '1';
            $result                 = $this->save();
        } catch (Exception $e) {
            $result = false;
        }
        return $result;
    }

}
