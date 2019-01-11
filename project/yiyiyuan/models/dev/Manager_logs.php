<?php

namespace app\models\dev;

use Yii;


class Manager_logs extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_manager_logs';
    }


    public function updateManagerlogs($condition) {
        if (empty($condition)) {
            return null;
        }
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $this->last_modify_time = date("Y-m-d H:i:s");
        $this->create_time = date("Y-m-d H:i:s");
        if ($this->save() > 0) {
            return $this;
        } else {
            return false;
        }
    }

    public function getRemit() {
        return $this->hasOne(User_remit_list::className(), ['id' => 'log_id']);
    }

}