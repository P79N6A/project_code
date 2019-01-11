<?php
/**
 * 所有新cloud的数据库表均需要继承此类
 */
namespace app\models\repo;

use Yii;
use yii\helpers\ArrayHelper;
class CloudBase extends \app\models\BaseModel {
    public static function getDb() {
        return \Yii::$app->db_cloudnew;
    }

    /**
     * 获取数据, 未设置时填充默认值""
     */
    public function getValue($data,$name,$default=""){
        if(empty($data)){
            return $default;
        }
        return ArrayHelper::getValue($data,$name,$default);
    }
}
