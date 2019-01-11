<?php

namespace app\models\own;

use Yii;


/**
 * 所有一亿元的数据库表均需要继承此类
 */

class OwnNewBaseModel extends OwnBaseModel {

    public static function getDb() {
        return Yii::$app->dbanalysis;
    }

}
