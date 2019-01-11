<?php
/**
 * 所有存管的数据库表均需要继承此类
 */
namespace app\modules\balance\models;

class SystemBase extends \app\models\BaseModel {
    public static function getDb() {
		return \Yii::$app->db;
	}
}