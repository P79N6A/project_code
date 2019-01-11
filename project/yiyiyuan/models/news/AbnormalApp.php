<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;
/**
 * This is the model class for table "yi_abnormal_app".
 *
 * @property integer $id
 * @property string $app_name
 * @property integer $manager_id
 * @property string $app_type
 * @property string $create_time
 */
class AbnormalApp extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_abnormal_app';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['app_name', 'manager_id', 'app_type', 'create_time'], 'required'],
            [['manager_id'], 'integer'],
            [['create_time'], 'safe'],
            [['app_name', 'app_type'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'app_name' => 'App Name',
            'manager_id' => 'Manager ID',
            'app_type' => 'App Type',
            'create_time' => 'Create Time',
        ];
    }
    
    public function addApp($condition){
        if(empty($condition) || !is_array($condition)){
            return FALSE;
        }
        $time = date('Y-m-d H:i:s');
        $data = $condition;
        $data['create_time'] = $time;
        $error = $this->chkAttributes($data);
        if($error){
            return false;
        }
        return $this->save();
    }
}
