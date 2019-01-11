<?php
/**
 * 数据库模型父类
 */
namespace app\models\loan;
use Yii;
class BaseDBModel extends \app\models\BaseModel
{
	public static function getDb()
    {
        return Yii::$app->get('db_loan');
    }
}
