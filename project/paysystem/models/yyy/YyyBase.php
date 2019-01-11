<?php
/**
 * 所有一亿元的数据库表均需要继承此类
 */
namespace app\models\yyy;

class YyyBase extends \app\models\BaseModel {
    public static function getDb() {
		return \Yii::$app->xhh_yyy;
	}
}
