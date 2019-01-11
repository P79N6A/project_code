<?php
/**
 * 所有新cloud的数据库表均需要继承此类
 */
namespace app\models\sys;

class SyBaseModel extends \app\models\BaseModel {
    public static function getDb() {
        return \Yii::$app->db_sysloan;
    }
}
