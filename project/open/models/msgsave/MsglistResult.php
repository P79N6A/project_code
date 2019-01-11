<?php
/**
 * 一亿元采集的短信数据存储表
 * @author 孙瑞
 */
namespace app\models\msgsave;

use app\models\BaseModel;

class MsglistResult extends BaseModel{

	public static function tableName(){
		return 'xhh_msglist_result';
	}

	public function rules(){
		return [
			[['mobile', 'save_path', 'create_time', 'modify_time'], 'required'],
			[['create_time', 'modify_time'], 'safe'],
		];
	}

	public function attributeLabels(){
		return [
			'id' => 'ID',
			'mobile' => '手机号',
			'save_path' => '存储地址',
			'create_time' => '创建时间',
			'modify_time' => '修改时间',
			'version' => '版本号',
		];
	}

	// 乐观锁
    public function optimisticLock() {
        return "version";
    }

	/**
	 * 通过手机号获取历史保存信息
	 * @param array $mobile 手机号列表
	 * @return array 数组内部是查询出的每条数据的对象
	 */
	public function getInfoByMobile($mobile) {
		if(empty($mobile)){
			return [];
		}
		$dataList = self::find()->where(['mobile' => $mobile])->one();
		if (!$dataList) {
			return [];
		}
		return $dataList;
	}

	/**
	 * 添加数据
	 * @param str $mobile 手机号
	 * @param str $path 短信列表存储地址
	 * @param str $grabTime 抓起时间
	 * @return bool 数据保存是否成功
	 */
	public function addData($mobile, $path, $grabTime) {
		if(!$mobile || !$path){
			return false;
		}
		if($this->getInfoByMobile($mobile)){
			return $this->returnError(false, '数据已存在');
		}
		$data = [
			'mobile' => $mobile,
			'save_path' => $path,
			'create_time' => $grabTime,
			'modify_time' => $grabTime,
		];
		$error = $this->chkAttributes($data);
		if ($error) {
			return $this->returnError(false, current($error));
		}
		return $this->save();
	}

	/**
	 * 修改数据
	 * @param obj $oMsgData 数据对象
	 * @return bool
	 */
	public function saveData($oMsgData, $grabTime){
		$error = $oMsgData->chkAttributes(['modify_time' => $grabTime]);
		if ($error) {
			return $oMsgData->returnError(false, $error);
		}
		if(!$oMsgData->update(['modify_time' => $grabTime])){
			return false;
		}
		return true;
	}
}