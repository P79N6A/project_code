<?php
/**
 * 数据库模型父类
 */
namespace app\models\tidb;

use Yii;
class BaseDBModel extends \app\models\BaseModel
{
    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_tidb');
    }
}
