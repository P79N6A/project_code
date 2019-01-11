<?php
/**
 * 一亿元短信历史数据表
 * @author 孙瑞
 */
namespace app\models\yiyiyuan;

class YiMsgList extends YyyBase{
	const MSG_TYPE = 1;
	const APP_TYPE = 2;

	public static function tableName(){
		return 'yi_application_list';
	}

	public function rules(){
		return [
			[['user_id', 'content', 'type', 'last_modify_time', 'create_time'], 'required'],
			[['user_id', 'type'], 'integer'],
			[['create_time', 'last_modify_time'], 'safe'],
		];
	}

	public function attributeLabels(){
		return [
			'id' => 'ID',
			'user_id' => '用户Id',
			'content' => '数据内容',
			'type' => '数据类型',
			'create_time' => '创建时间',
			'last_modify_time' => '更新时间',
			'version' => '版本号',
		];
	}

	// 乐观锁
    public function optimisticLock() {
        return "version";
    }

	public function getListByIdRange($start_id = 0, $end_id = 0){
		if($start_id >= $end_id){
			return [];
		}
		$where = ['AND',
            ['>=', 'id', $start_id],
            ['<', 'id', $end_id],
			['type' => self::MSG_TYPE]
        ];
		$result = self::find()->where($where)->select(['user_id','content','last_modify_time'])->all();
		if(!$result){
			return [];
		}
		return $result;
	}

	public function getMaxId(){
		$num = YiMsgList::find()->max('id');
		return $num + 1;
	}
}