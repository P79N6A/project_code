<?php
/**
 * 创蓝校验手机空号检测
 * 结果表
 * @author 孙瑞
 */
namespace app\models\mbcheck;

use Yii;
use app\common\Logger;
use app\models\BaseModel;

class MbcheckResult extends BaseModel{

	public static function tableName(){
		return 'xhh_mbcheck_result';
	}

	public function rules(){
		return [
			[['mobile', 'mobile_status', 'create_time', 'modify_time'], 'required'],
			[['create_time', 'modify_time'], 'safe'],
			[['mobile_status', 'requestid'], 'integer'],
			[['mobile'], 'string', 'max' => 20],
			[['check_res'], 'string', 'max' => 200],
		];
	}

	public function attributeLabels(){
		return [
			'id' => 'ID',
			'requestid' => '请求表Id',
			'mobile' => '手机号',
			'check_res' => '创蓝检测返回结果',
			'mobile_status' => '手机号状态 0:未检测 1:空号  2:实号  3:停机  4:库无  5:沉默号  11:失败',
			'create_time' => '创建时间',
			'modify_time' => '更新时间',
		];
	}

	// 乐观锁
    public function optimisticLock() {
        return "version";
    }

	/**
	 * 添加请求记录
	 * @param1 array $saveData 请求数据
	 * @return bool true 添加成功并已初始化 false 添加失败
	 */
	public function insertInfo($save_data){
		if(!$save_data){
			return 0;
		}
		$now_time = date('Y-m-d H:i:s');
		$save_data['create_time'] = $now_time;
		$save_data['modify_time'] = $now_time;
		$error = $this->chkAttributes($save_data);
		if ($error) {
			Logger::dayLog('mbcheck','result/error 添加检测结果数据错误:'.json_encode($error));
			return $this->returnError(0, $error);
		}
		if(!$this->save()){
			return 0;
		}
		return $this->id;
	}

	/**
	 * 根据条件查询单条语句
	 * @param mixed $data 查询条件字段值
	 * @param string $column 字段名
	 * @param string $order 排序规则
	 * @return false获取失败 obj返回查询到的数据对象
	 */
	public function getOne($data, $column='id', $order='id asc'){
		if(!$data){
			return false;
		}
		$result = self::find()->where([$column => $data])->orderBy($order)->one();
		return $result;
	}
}