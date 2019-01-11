<?php
/**
 * 一亿元采集的短信数据请求表
 * @author 孙瑞
 */
namespace app\models\msgsave;

use app\models\BaseModel;

class MsglistRequest extends BaseModel{

	public static function tableName(){
		return 'xhh_msglist_request';
	}

	public function rules(){
		return [
			[['mobile', 'data_md5', 'create_time'], 'required'],
		];
	}

	public function attributeLabels(){
		return [
			'id' => 'ID',
			'mobile' => '手机号',
			'data_md5' => '数据摘要',
			'create_time' => '创建时间',
			'version' => '版本号',
		];
	}

	// 乐观锁
    public function optimisticLock() {
        return "version";
    }

	// 判断请求数据是否存在
	public function checkExists($dataMD5){
		if(!$dataMD5){
			return FALSE;
		}
		$where = [
			'data_md5' => $dataMD5
		];
		$num = self::find()->where($where)->count();
		if($num!=0){
			return FALSE;
		}
		return TRUE;
	}

	// 添加请求数据
	public function addRequest($mobile,$dataMD5){
		$data = [
			'mobile' => $mobile,
			'data_md5' => $dataMD5,
			'create_time' => date('Y-m-d H:i:s')
		];
		$error = $this->chkAttributes($data);
		if ($error) {
			return $this->returnError(false, current($error));
		}
		return $this->save();
	}
}