<?php
/**
 *
 */
namespace app\modules\balance\models\zrys;

class ZrysBase extends \app\models\BaseModel {
    public static function getDb() {
		return \Yii::$app->yx_test;
	}
}