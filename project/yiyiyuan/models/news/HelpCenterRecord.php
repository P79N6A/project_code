<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;
/**
 * This is the model class for table "yi_help_center_record".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $help_id
 * @property integer $read
 * @property integer $status
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class HelpCenterRecord extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_help_center_record';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'help_id', 'create_time'], 'required'],
            [['user_id', 'help_id', 'read', 'status', 'version'], 'integer'],
            [['last_modify_time', 'create_time'], 'safe']
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
            'help_id' => 'Help ID',
            'read' => 'Read',
            'status' => 'Status',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }
    
    public function optimisticLock() {
        return "version";
    }
    
    public function getHelpCenterRecord($condition){
        if(empty($condition)){
            return NULL;
        }
        $oData = self::find()->where($condition)->one();
        return $oData;
    }
    /**
     * 更新记录
     * @param type $condition
     * @return boolean
     */
    public function update_record($condition) {
        if (empty($condition) || !is_array($condition)) {
            return false;
        }
        $time = date('Y-m-d H:i:s');
        $data = $condition;
        $data['last_modify_time'] = $time;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }
    
    /**
     * 新增记录
     * @param type $condition
     * @return boolean
     */
    public function addHelpCenterList($condition) {
        if (empty($condition) || !is_array($condition)) {
            return false;
        }
        $time = date('Y-m-d H:i:s');
        $data = $condition;
        $data['last_modify_time'] = $time;
        $data['create_time'] = $time;
        $data['version'] = 0;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }
}
