<?php
/**
 * 所有米富的数据库表均需要继承此类
 */
namespace app\modules\balance\models\peanut;

class PeanutBase extends \app\models\BaseModel {
    public static function getDb() {
		return \Yii::$app->xhh_peanut;
	}
}