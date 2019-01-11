<?php
/**
 * 数据库模型父类
 */
namespace app\models\cloud;

use Yii;
class BaseNewDBModel extends \app\models\BaseModel
{
	public static function getDb()
    {
        return Yii::$app->get('db_cloudnew');
    }
}