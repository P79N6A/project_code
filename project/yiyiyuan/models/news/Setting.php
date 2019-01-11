<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;
/**
 * This is the model class for table "yi_setting".
 *
 * @property integer $id
 * @property integer $status
 * @property integer $type
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class Setting extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_setting';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'type', 'version','manager_id'], 'integer'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['create_time'], 'required']
        ];
    }
    
    /**
     * 乐观所版本号
     * * */
    public function optimisticLock() {
        return "version";
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status' => 'Status',
            'type' => 'Type',
            'manager_id' => 'Manager Id',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }
    
    /**
     * 更新配置表数据
     * @param type $condition
     * @return boolean
     */
    public function updateSetting($condition){
        if(empty($condition) || !is_array($condition)){
            return false;
        }
        $data = $condition;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if($error){
            return false;
        }
        return $this->save();
    }
    
    /**
     * 取出商城开关配置数据
     * @return boolean
     */
    public function getShop(){
        $result = self::find()->where(['type' => 1])->one();
        if(empty($result)){
            return false;
        }
        return $result;
    }
   
}
