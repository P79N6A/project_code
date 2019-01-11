<?php

namespace app\models;

use Yii;
use app\common\ApiServerCrypt;

class App extends  BaseModel
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

	
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%app}}';
    }
	/**
	 * 根据app_id获取信息
	 */
	public function getByAppId( $app_id ){
		if( !$app_id ){
			return null;
		}
		return $dataM = App::find()->where(["app_id"=>$app_id])->limit(1)->one();
	}
	/**
	 * 根据aid加密数据
	 */
	public function encryptData($aid, $res_data){
		$appData = $this->getById($aid);
		if(empty($appData)){
			return null;
		}
		if(empty($res_data)){
			return null;
		}
		$res_data['app_id'] = $appData -> app_id;
		
		// 加密信息
		try{
			return (new ApiServerCrypt())->buildData($res_data, $appData->auth_key);
		}catch(\Exception $e){
			// log_here
			return '';
		}
	}
}
