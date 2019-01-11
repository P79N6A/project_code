<?php

namespace app\models;

use Yii;
use app\common\ApiServerCrypt;

class App extends  \app\models\BaseModel
{


	private $auth_key;
	private $app_id;

	function __construct()
	{
		$this->auth_key = '24BEFILOPQRUVWXcdhntvwxy';
		$this->app_id = '2810335722015';
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
	public function  encryptData($aid, $res_data){
		if(empty($res_data)){
			return null;
		}
		$res_data['app_id'] = $this->app_id;
		
		// 加密信息
		try{
			return (new ApiServerCrypt())->buildData($res_data, $this->auth_key);
		}catch(\Exception $e){
			// log_here
			return '';
		}
	}
}
