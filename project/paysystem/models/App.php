<?php

namespace app\models;

use Yii;
use app\common\ApiServerCrypt;

class App extends  \app\models\BaseModel
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
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'app_id', 'create_time'], 'required'],
            [['auth_type', 'create_time'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['app_id', 'auth_key'], 'string', 'max' => 100]
        ];
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
    
    public function getApp(){
        return $dataM = App::find()->all();
	}
	public function createData($data){
		$data['create_time'] = time();
		$data['auth_type'] = 1;
		$error = $this->chkAttributes($data);
        if ($error) {
            return $this->returnError(null, current($error));
        }
        //3 保存数据
        $result = $this->save();
        if (!$result) {
            return $this->returnError(null, '保存失败');
        }else{
            return $result;
        }
    }
    
    public function updateData($data){
		$error = $this->chkAttributes($data);
        if ($error) {
            return $this->returnError(null, current($error));
        }
        //3 保存数据
		$result = $this->save();
        if (!$result) {
            return $this->returnError(null, '保存失败');
        }else{
            return $result;
        }
    }
}
