<?php

namespace app\models\chanpay;

use Yii;

/**
 * This is the model class for table "chanpay_client_notify".
 *
 * @property integer $id
 * @property integer $remit_id
 * @property string $tip
 * @property integer $remit_status
 * @property integer $notify_num
 * @property integer $notify_status
 * @property string $notify_time
 * @property string $reason
 * @property string $create_time
 */
class ChanpayClientNotify extends \app\models\BaseModel
{
	// 通知状态
	const STATUS_INIT = 0; // 初始
	const STATUS_DOING = 1; // 通知中
	const STATUS_SUCCESS = 2; // 成功
	const STATUS_RETRY = 3; // 重试
	const STATUS_FAILURE = 11; // 支付失败
	const STATUS_NOTIFY_MAX = 13; // 通知达上限
	
	const MAX_NOTIFY = 7; // 最大查询次数
	private $notifyMap;
	public function init(){
		$this->notifyMap = [
				'STATUS_INIT' => static::STATUS_INIT,
				'STATUS_DOING' => static::STATUS_DOING,
				'STATUS_SUCCESS' => static::STATUS_SUCCESS,
				'STATUS_RETRY' => static::STATUS_RETRY,
				'STATUS_FAILURE' => static::STATUS_FAILURE,
				'STATUS_NOTIFY_MAX' => static::STATUS_NOTIFY_MAX,
				];
	}
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'chanpay_client_notify';
    }

    /**
     * 获取字符串形式状态
     * @param  string $status_str
     * @return int | []
     */
    public function gStatus($status_str=null){
    	if($status_str){
    		return $this->notifyMap[$status_str];
    	}else{
    		return $this->notifyMap;
    	}
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['remit_id', 'tip', 'notify_time', 'reason', 'create_time'], 'required'],
            [['remit_id', 'remit_status', 'notify_num', 'notify_status'], 'integer'],
            [['notify_time', 'create_time'], 'safe'],
            [['tip'], 'string', 'max' => 255],
            [['reason'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'remit_id' => 'Remit ID',
            'tip' => 'Tip',
            'remit_status' => 'Remit Status',
            'notify_num' => 'Notify Num',
            'notify_status' => 'Notify Status',
            'notify_time' => 'Notify Time',
            'reason' => 'Reason',
            'create_time' => 'Create Time',
        ];
    }
    
    // 保存请求的数据
    public function saveNotify($postData) {
    	//1 数据验证
    	if (!is_array($postData) || empty($postData)) {
    		return $this->returnError(false, "数据不能为空");
    	}
    	if (empty($postData['remit_id'])) {
    		return $this->returnError(false, "订单不能为空");
    	}
    	$postData['notify_time'] = $postData['create_time'] = date('Y-m-d H:i:s');
    	
    	// 参数检证是否有错
    	if ($errors = $this->chkAttributes($postData)) {
    		print_r($errors);exit;
    		return $this->returnError(false, implode('|', $errors));
    	}
    
    	$result = $this->save($postData);
    	if (!$result) {
    		return $this->returnError(false, implode('|', $this->errors));
    	}
    	return true;
    }
    
    /**
     * 表关联关系
     */
    public function getChanpayquickorder() {
    	return $this->hasOne(ChanpayQuickOrder::className(), ['id' => 'remit_id']);
    }
    
    /**
     * 获取状态为初始和重试的记录
     * @param $start_time 精确到分
     * @param $end_time  精确到分
     * @return []
     */
    public function getClientNotifyList($start_time, $end_time, $limit=50) {
    	$start_time = date('Y-m-d H:i:00', strtotime($start_time));
    	$end_time = date('Y-m-d H:i:00', strtotime($end_time));
    	$where = ['AND',
    			['notify_status' => [static::STATUS_INIT, static::STATUS_RETRY]],
    			//['>=', 'notify_time', $start_time], //@todo 暂不限制开始
    			['<', 'notify_time', $end_time],
    			];
    	$dataList = self::find()->where($where)->limit($limit)->all();
    	if (!$dataList) {
    		return null;
    	}
    	return $dataList;
    }
    
    /**
     * 锁定正在出款接口的状态
     */
    public function lockNotify($ids) {
    	if (!is_array($ids) || empty($ids)) {
    		return 0;
    	}
    	$ups = static::updateAll(['notify_status' => static::STATUS_DOING], ['id' => $ids]);
    	return $ups;
    }
    
    /**
     * 回写响应结果
     * $this 操作数据
     * @param $notify_status 通知状态
     * @return bool
     */
    public function saveNotifyStatus($notify_status, $reason = '') {
    	// 当是出款中时, 更新下次的查询时间
    	$this->refresh();
    	if ($notify_status != static::STATUS_INIT ){
    		// 累加通知次数
    		$this->notify_num++;
    		if($this->notify_num < static::MAX_NOTIFY){
    			// 未超通知限制, 计算下次查询时间间隔
    			$this->notify_time = $this->acNotifyTime($this->notify_num, $this->notify_time);
    		}else{
    			// 超出重试次数限制. 将重试中的变更为超限状态
    			if($notify_status == static::STATUS_RETRY){
    				$notify_status = static::STATUS_NOTIFY_MAX;
    			}
    		}
    	}
    	$this->notify_status = $notify_status;
    	$reason = substr($reason, 0, 20);
    	$this->reason = $reason;
    	$result = $this->save();
    	return $result;
    }
    
    /**
     * 计算下次查询时间
     * @param int $notify_num 当前次数
     * @param str $notify_time 当前时间
     * @return str 下次查询时间
     */
    public function acNotifyTime($notify_num, $notify_time) {
    	// 累加的分钟
    	$addMinutes = [
    			1 => 5,
    			2 => 30,
    			3 => 89,
    			4 => 233,
    			5 => 610,
    			6 => 1560,
    			];
    
    	// 不在上述时,不改变
    	if (!isset($addMinutes[$notify_num])) {
    		return $notify_time;
    	}
    
    	// 累加时间
    	$time = ($notify_time == '0000-00-00 00:00:00') ? time() : strtotime($notify_time);
    	$t = $time + $addMinutes[$notify_num] * 60;
    	return date('Y-m-d H:i:s', $t);
    }
}
