<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;

/**
 * This is the model class for table "yi_function_control".
 *
 * @property string $id
 * @property string $label
 * @property string $type
 * @property string $status
 * @property string $system
 * @property string $last_modify_time
 * @property string $create_time
 */
class Function_control extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_function_control';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['last_modify_time', 'create_time'], 'safe'],
            [['label', 'type'], 'string', 'max' => 20],
            [['status', 'system'], 'string', 'max' => 5]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'label' => 'Label',
            'type' => 'Type',
            'status' => 'Status',
            'system' => 'System',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
        ];
    }
    
    /**
     * 修改信息
     * @param array $condition
     * @return boolean
     */
    public function updateFunctioncontrol($condition) {
        if (empty($condition) || !is_array($condition)) {
            return FALSE;
        }
        $condition['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($condition);
        if ($error) {
            return FALSE;
        }
        return $this->save();
    }
    
    public function addFunctioncontrol($condition){
        if(empty($condition) || !is_array($condition)){
            return FALSE;
        }
        $time = date('Y-m-d H:i:s');
        $data = $condition;
        $data['last_modify_time'] = $time;
        $data['create_time'] = $time;
        $error = $this->chkAttributes($data);
        if($error){
            return false;
        }
        return $this->save();
    }

    public function getPatmenthod($types,$system=2,$label=1,$status=1){
        if(empty($types) || !is_array($types)){
            return null;
        }
        return self::find()->where(['type' => $types, 'system' => $system, 'label' => $label,'status' => $status])->orderBy("id desc")->one();
    }
}
