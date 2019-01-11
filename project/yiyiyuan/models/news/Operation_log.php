<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;

/**
 * This is the model class for table "yi_operation_log".
 *
 * @property string $id
 * @property string $user_id
 * @property integer $operation_type
 * @property integer $come_from
 * @property integer $type
 * @property string $create_time
 */
class Operation_log extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_operation_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'operation_type', 'type', 'create_time'], 'required'],
            [['user_id', 'operation_type', 'come_from', 'type'], 'integer'],
            [['create_time'], 'safe']
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
            'operation_type' => 'Operation Type',
            'come_from' => 'Come From',
            'type' => 'Type',
            'create_time' => 'Create Time',
        ];
    }
    
    /**
     * 添加日志信息
     * @param type $condition
     * @return boolean
     */
    public function save_operlog($condition) {
        if (empty($condition)) {
            return false;
        }
        $data = $condition;
        $data['create_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
        
    }
    
    /**
     * 
     * @param type $user
     * @param type $operation_type 1:实名认证2:工作信息
     * @return type array
     */
    public function getOperationCondition($user, $operation_type = 1) {
        if (empty($user)) {
            return [];
        }
        switch ($operation_type) {
            case 1:
                $condition['type'] = $user->identity_valid == 1 ? 1 : 2;
                break;
            case 2:
                $extend = $user->extend;
                $condition['type'] = (empty($extend) || empty($extend->company_area)) ? 1 : 2;
                break;
            default :
        }
        $condition['user_id'] = $user->user_id;
        $condition['operation_type'] = $operation_type;
        $condition['come_from'] = $user->come_from;
        return $condition;
    }
}
