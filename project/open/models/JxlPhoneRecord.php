<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "jxl_phone_record".
 *
 * @property integer $id
 * @property string $phone
 * @property string $place
 * @property string $other_cell_phone
 * @property string $subtotal
 * @property integer $type
 * @property integer $start_time
 * @property integer $use_time
 * @property string $init_type
 * @property string $call_type
 * @property string $create_time
 */
class JxlPhoneRecord extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jxl_phone_record';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phone','other_cell_phone',], 'required'],
            [['requestid','subtotal', 'use_time', 'create_time',], 'integer'],
            [['phone', 'place', 'other_cell_phone', 'init_type', 'call_type'], 'string', 'max' => 20],
            [['start_time'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'phone' => '手机号',
            'place' => '呼叫地',
            'other_cell_phone' => '其它手机',
            'subtotal' => '未知',
            'start_time' => '开始通话时间',
            'use_time' => '通话时长',
            'init_type' => '主叫 被叫',
            'call_type' => '本地通话 or 其他',
            'create_time' => 'Create Time',
        ];
    }
	/**
	 * 批量查询数据到db中
	 */
    public function batchSaveData($requestid, $data)
    {
        if( !is_array($data) || empty($data) ){
        	return FALSE;
        }
		$time = time();
		foreach($data as $r){
			$result = $this->getRecord($r['cell_phone'], $r['other_cell_phone'], $r['start_time']);
			if(!$result){
				$saveData = [
					'requestid' => $requestid,
					'phone'   => $r['cell_phone'], 
					'place'   => isset($r['place']) ? $r['place'] : '', 
					'other_cell_phone'=>$r['other_cell_phone'], 
					'subtotal'  => isset($r['subtotal']) ? intval($r['subtotal']) : 0, 
					'start_time'=> isset($r['start_time']) ? $r['start_time'] : 0, 
					'use_time'  => isset($r['use_time']) ? $r['use_time'] : 0, 
					'init_type' => isset($r['init_type']) ? $r['init_type'] : '', 
					'call_type' => isset($r['call_type']) ? $r['call_type'] : '', 
					'create_time' => $time,
				];
				$o = new self();
				$error = $o -> chkAttributes($saveData);
				if( $error ){
					\app\common\Logger::dayLog(
						'juxinli/jxlphonerecord',
						'保存失败',$saveData,
						'错误原因',$error
					);
				}
				$res = $o -> save();
			}else{
				// 更新旧数据
				$result -> modify_time = time();
				$result -> requestid = $requestid;
				$res = $result -> save();
			}
		}
		return $res;
    }
	/**
	 * 是否存在相同的纪录
	 */
	public function getRecord($phone,$other_cell_phone,$start_time){
		$condition = [
			'phone'				=> $phone,
			'other_cell_phone'	=> $other_cell_phone,
			'start_time'		=> $start_time
		];
		return static::find()->where($condition)->one();
	}
	/**
	 * 获取某次请求的通话纪录
	 */
	public function getByRequestId($requestid,$offset=0,$limit=100){
		$condition = [
			'requestid'	=> intval($requestid),
		];
		// 默认最新一百条
		$data = static::find()	-> where($condition)
								-> offset($offset)
								-> limit($limit)
								-> orderBy("start_time DESC")
								-> asArray()
								-> all();
		if(empty($data)){
			return null;
		}else{
			return $data;
		}
	}
	/**
	 * 获取某次请求的通话纪录
	 */
	public function getByPhone($phone,$offset=0,$limit=100){
		$condition = [
			'phone'	=> $phone,
		];
		// 默认最新一百条
		$data = static::find()	-> where($condition)
								-> offset($offset)
								-> limit($limit)
								-> orderBy("start_time DESC")
								-> asArray()
								-> all();
		if(empty($data)){
			return null;
		}else{
			return $data;
		}
	}
}
