<?php

/**
 * 所有一亿元的数据库表均需要继承此类
 */

namespace app\models\onlyread;

use Yii;
use yii\helpers\ArrayHelper;

class ReadBaseModel extends \app\models\BaseModel {

    public static function getDb() {
        return \Yii::$app->db;
    }

    /**
     * 获取数据, 未设置时填充默认值""
     */
    public function getValue($data, $name, $default = "") {
        if (empty($data)) {
            return $default;
        }
        return ArrayHelper::getValue($data, $name, $default);
    }

}
