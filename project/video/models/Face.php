<?php

namespace app\models;

use Yii;
class Face extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%face}}';
    }
	/**
     * 添加一条人脸识别接口信息
     */
    public function addFace($condition) {
        if (empty($condition)) {
            return false;
        }
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $this->create_time = date('Y-m-d H:i:s');
        $this->result = $this->save();
        if ($result) {
            return Yii::$app->db->getLastInsertID();
        } else {
            return false;
        }
    }
}
