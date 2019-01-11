<?php
/**
 * 所有债匹的数据库表均需要继承此类
 */
namespace app\modules\balance\models;

class MatchingBase extends \app\models\BaseModel {
    public static function getDb() {
		return \Yii::$app->xhh_matching;
	}
}
