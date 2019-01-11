<?php

namespace app\models;

use Yii;
class ServerApp extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%service_app}}';
    }
	/**
	 * 获取是否存在权限
	 */
	public function hasAuth( $aid, $service_id ){
		if( !$aid || !$service_id){
			return null;
		}
		return $dataM = ServerApp::find()->where(["aid"=>$aid,"service_id"=>$service_id])->count() > 0;
	}
}
