<?php
/**
 * 数据库模型父类
 */
namespace app\models\antifraud;
use Yii;
use yii\helpers\ArrayHelper;
class BaseDBModel extends \app\models\BaseModel
{
	public static function getDb()
    {
        return Yii::$app->get('db_antifraud');
    }

    public function getPromeData($where,$select = '*')
    {
        $select = explode(',',$select);
        $res =  $this->find()->select($select)->where($where)->Asarray()->limit(1)->orderby('id DESC')->one();
        if (empty($res)) {
            foreach ($select as $k => $v) {
                $res[$v] = null;
            }
            return $res;
        }
        foreach ($select as $k => $v) {
            $val = ArrayHelper::getValue($res,$v,'');
            $res[$v] = !is_null($val) ? (float)(sprintf('%.4f',$val)) : null;
        }
        return $res;
    }

    public function getOne($where,$select = '*') {
        return $this->find()->select($select)->where($where)->orderby('id DESC')->one();
    }
}
