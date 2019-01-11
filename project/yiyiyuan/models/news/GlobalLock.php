<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;
use app\commonapi\Logger;

/**
 * This is the model class for table "yi_global_lock".
 *
 * @property integer $id
 * @property string $name
 * @property integer $type
 * @property integer $status
 * @property integer $lock_time
 * @property integer $version
 * @property string $create_time
 */
class GlobalLock extends BaseModel
{   
    CONST INIT_STATUS = 0;
    CONST LOCK_STATUS = 1;

    // CONST MAXLOCKTIME = 0;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_global_lock';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'type', 'lock_time', 'create_time'], 'required'],
            [['type', 'status', 'lock_time', 'version'], 'integer'],
            [['create_time'], 'safe'],
            [['name'], 'string', 'max' => 30]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'type' => 'Type',
            'status' => 'Status',
            'lock_time' => 'Lock Time',
            'version' => 'Version',
            'create_time' => 'Create Time',
        ];
    }

    public function optimisticLock()
    {
        return 'version';
    }

    /**
     * 根据type查询
     *
     * @param [type] $type
     * @return void
     */
    public function getByType($type) 
    {
        if ($type < 1) {
            return [];
        }
        $where = [
            'type'   => $type,
            'status' => static::INIT_STATUS,
        ];
        return static::find()->where($where)->limit(1)->one();
    }
    /**
     * 设置锁
     * @return bool
     */
    public function setOptimistic()
    {  
        try{
            $time = time();
            $this->status = static::LOCK_STATUS;
            $this->lock_time = $time;
            $result = $this->save();
        } catch (\Exception $e) {
            Logger::dayLog('globallock', 'setOptimistic','全局锁设置异常'.$e);
            return false;
        }
        return $result;
    }

    /**
     * 取消锁
     * @return bool
     */
    public function unsetOptimistic()
    {   
        try{
            $time = time();
            $this->status = static::INIT_STATUS;
            $this->lock_time = $time;
            $result = $this->save();
        } catch (\Exception $e) {
            Logger::dayLog('globallock', 'unsetOptimistic','全局锁取消异常'.$e);
            return false;
        }
        return $result;
    }
}
