<?php

namespace app\models\anti;

use Yii;
use app\common\Logger;

/**
 * This is the model class for table "af_commands".
 *
 * @property string $id
 * @property integer $status
 * @property string $type
 * @property string $start_id
 * @property string $end_id
 * @property string $start_time
 * @property string $end_time
 */
class AfCommands extends AntiBaseModel
{
    const INIT = 0;
    const DOING = 1;
    const FINISHED = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'af_commands';
    }

    /**
     * @inheritdoc
     */
    public function rules() 
    { 
        return [
            [['status', 'type', 'start_id', 'end_id', 'start_time', 'end_time'], 'required'],
            [['status', 'start_id', 'end_id'], 'integer'],
            [['start_time', 'end_time'], 'safe'],
            [['type'], 'string', 'max' => 32]
        ]; 
    } 

    /** 
     * @inheritdoc 
     */ 
    public function attributeLabels() 
    { 
        return [ 
            'id' => 'ID',
            'status' => 'Status',
            'type' => '脚本类型',
            'start_id' => 'Start ID',
            'end_id' => 'End ID',
            'start_time' => '开始时间',
            'end_time' => '结束时间',
        ]; 
    } 

    public function getStartId($where)
    {   
        $res = static::find()->where($where)->select('id,end_id')->orderBy('ID DESC')->one();
        return $res;
    }

    public function saveCommand($start_id,$end_id,$type)
    {
        $time = date("Y-m-d H:i:s");
        $data = [
            'status' => self::INIT,
            'type' => $type,
            'start_id' => $start_id,
            'end_id' => $end_id,
            'start_time' => $time,
            'end_time' => $time,
        ];
        $error = $this->chkAttributes($data); 
        if ($error) { 
            Logger::dayLog("anti/af_commands","save failed", $data, $error);
            return 0;
        }
        $res = $this->save();
        if (!$res) {
            return 0;
        }
        return $this->id;
    }

    public function lockStatus($ids,$status)
    {
        $nums = self::updateAll(['status' => $status,'end_time'=> date('Y-m-d H:i:s')], ['id' => $ids]);
        return $nums;
    }
}
