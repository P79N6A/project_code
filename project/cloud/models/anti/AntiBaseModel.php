<?php
/**
 * 所有开放平台的数据库表均需要继承此类
 */
namespace app\models\anti;
use Yii;
use yii\helpers\ArrayHelper;
class AntiBaseModel extends \app\models\BaseModel {
    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_anti');
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