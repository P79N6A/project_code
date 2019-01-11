<?php

namespace app\models;

use Yii;
class Server extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%server}}';
    }
	/**
	 * 根据app_id获取信息
	 */
	public function getById( $id ){
		if( !$app_id ){
			return null;
		}
		return $dataM = App::find()->where(["id"=>$id])->limit(1)->one();
	}
}
