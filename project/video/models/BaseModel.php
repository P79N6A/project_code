<?php
/**
 * 数据库模型父类
 */
namespace app\models;

use Yii;
class BaseModel extends \yii\db\ActiveRecord
{
	/**
	 * 定义出错数据
	 */
	public $errinfo;
	
	/**
	 * 事务处理
	 */
	private static $transaction;

	private static $_models=array();			// class name => model

	public static function model($className=__CLASS__)
	{
		if(isset(self::$_models[$className]))
			return self::$_models[$className];
		else
		{
			$model=self::$_models[$className]=new $className(null);
			$model->attachBehaviors($model->behaviors());
			return $model;
		}
	}

	/**
	 * 封装规则检查, 应用此方法，需要new Model
	 * @param  [] $postData 检测的字段
	 * @return [] 字段检测结果信息
	 */
	public function chkAttributes( $postData, $scenario = '' ){
		//1 加入场景
		if ($scenario) {
			$this->setScenario($scenario);
		}
		$this -> attributes = $postData;

		//2 当提交无错误时
		if ($this->validate()) {
			return null;
		}

		//3 有错误时,只取第一个错误就ok了
	    $errors = [];
		foreach($this->errors as $attribute => $es){
			$errors[$attribute] = $es[0];
		}
		return $errors;
	}

	/**
	 * 根据主键查询
	 */
	public function getById($id){
		return static::findOne($id);
	}

	/**
	 * 根据主键查询
	 */
	public function getByIds($ids){
		if(!is_array($ids)){
			return null;
		}
		return static::findAll($ids);
	}

	/**
	 *批量插入操作
	 * @param  [[],[],...] $values 二维数据，要求每个数组字段个数,名称，顺序一致
	 * @return [type]         [description]
	 */
	public static function insertBatch($values){
		if( !is_array($values) || !is_array($values[0]) ){
			return false;
		}
		$columns = array_keys($values[0]);
		$vs = [];
		foreach($values as $v){
			$temp=[];
			foreach($columns as $name){
				$temp[] = $v[$name];
			}
			$vs[] = $temp;
		}
		$db = static::getDb();
		$command = $db->createCommand()->batchInsert(static::tableName(), $columns, $vs);
		return $command->execute();
	}

	/**
	 * 返回错误信息
	 * @param  false | null $result 错误信息
	 * @param  str $errinfo 错误信息
	 * @return false | null 同参数$result
	 */
	public function returnError($result, $errinfo){
		$this->errinfo = $errinfo;
		return $result;
	}
	
	/*
	 * 事务处理封装
	 */
	public static function beginTransaction(){
		self::$transaction = Yii::$app->db->beginTransaction();
	}
	public static function endTransaction($ok){
		if($ok){
			self::$transaction -> commit();
		}else{
			self::$transaction -> rollBack();
		}
	}
}
