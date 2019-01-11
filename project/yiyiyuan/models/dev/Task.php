<?php

namespace app\models\dev;

use app\commonapi\Common;
use Yii;



class Task extends \yii\db\ActiveRecord {
	public static function tableName(){
		return 'yi_task';
	}

	/**
     * @inheritdoc
     */
    public function rules() {
        return [
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
        ];
    }



    //添加任务
    public function addTask($user_id,$type,$step=1){
        $task = new Task();
        $task->user_id = $user_id;
        $task->source_type = $type;
        $task->step = $step;
        $task->source_id = 0;
        $task->status = 1;
        $task->create_time = date('Y-m-d H:i:s');
        $result = $task->save();
        if ($result) {
            $id = Yii::$app->db->getLastInsertID();
            return $id;
        }else{
            return false;
        }
    }
}