<?php
/**
 * 所有一亿元的数据库表均需要继承此类
 */
namespace app\models\haotian;

class HaotianBaseModel extends \app\models\BaseModel {
	public static function getDb() {
		return \Yii::$app->dbhaotian;
	}
}
