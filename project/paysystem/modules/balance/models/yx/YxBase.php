<?php
/**
 * 所有yxl的数据库表均需要继承此类
 */
namespace app\modules\balance\models\yx;

class YxBase extends \app\models\BaseModel {
    public static function getDb() {
		return \Yii::$app->xhh_yxl;
	}
}
