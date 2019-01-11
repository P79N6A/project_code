<?php
/**
 * 所有一亿元的数据库表均需要继承此类
 */
namespace app\models\own;

class OwnBaseModel extends \app\models\BaseModel {
	public static function getDb() {
		return \Yii::$app->dbown;
	}
}
