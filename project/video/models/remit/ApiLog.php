<?php
/**
 * 接口调用日志表, 每次调用均会纪录响应结果
 * @todo 应该将xml也保留下来
 */
namespace app\models\remit;

use Yii;

/**
 * This is the model class for table "rt_api_log".
 *
 * @property integer $id
 * @property integer $remit_id
 * @property integer $type
 * @property integer $pre_status
 * @property integer $status
 * @property string $rsp_status
 * @property string $rsp_status_text
 * @property string $start_time
 * @property string $end_time
 */
class ApiLog extends \app\models\BaseModel
{
	/**
	 * 出款条数和查询条数
	 */
	const REMIT_NUM=200;
	const QUERY_NUM=400;
	
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rt_api_log';
    }

  /**
   * 表关联关系
   */
  public function getRemit() {
    return $this->hasOne(Remit::className(), ['id' => 'remit_id']);
  }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['remit_id', 'pre_status', 'start_time',], 'required'],
            [['remit_id', 'type','pre_status', 'status',], 'integer'],
            [['start_time', 'end_time'], 'safe'],
            [['rsp_status'], 'string', 'max' => 50],
            [['rsp_status_text'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'remit_id' => '出款id',
            'type' => '1:出款请求; 2:查询请求',
            'pre_status' => '前一状态',
            'status' => '处理后状态',
            'rsp_status' => '中信响应状态:空为新加, RSP_TIMEOUT表示无响应',
            'rsp_status_text' => '中信响应信息',
            'start_time' => '请求开始时间',
            'end_time' => '请求结束时间',
        ];
    }
	/**
	 * 获取出款接口剩余条数
	 */
	public function getRestRemit(){
		$num = static::REMIT_NUM - $this->getNumHour(1);
		return $num > 0 ? $num : 0;
	}
	/**
	 * 获取查询接口剩余条数
	 */
	public function getRestQuery(){
		$num = static::QUERY_NUM - $this->getNumHour(2);
		return $num > 0 ? $num : 0;
	}
	/**
	 * 获取近一小时请求纪录
	 * @param $type 1:出款请求(上限200条); 2:查询请求(上限400条)
	 * @return $total
	 */
    private function getNumHour($type){
    	$type = intval($type);
		if(!in_array($type, [1,2])){
			return 0;
		}
		$hourStart = date('Y-m-d H:i:s', time() - 3600);
		$hourEnd = date('Y-m-d H:i:s');
		$where = ['AND',
						 ['type'=>$type,],
						 ['>=','start_time',$hourStart],
						 ['<','start_time',$hourEnd],
						];
		$total = static::find() -> where($where) -> count();
		return $total;
    }
	/**
	 * 保存数据到db库中
	 * @param $remit_id
	 * @param $pre_status 出款表当前的状态
	 * @param $type 1,2  1:出款请求; 2:查询请求
	 * @return bool
	 */
    public function saveData($remit_id, $pre_status, $type ){
    	if(!$remit_id){
    		return false;
    	}
		if(!in_array($type, [1,2])){
			return $this->returnError(false,"type只能是1,2");
		}
		$dayTime = date('Y-m-d H:i:s');
		$row = [
            'remit_id' => $remit_id,
            'type' => $type,
            'pre_status' =>$pre_status,
            'status' => 0,
            'rsp_status' => '',
            'rsp_status_text' => '',
            'start_time' =>$dayTime,
            'end_time' => '0000-00-00 00:00:00',
        ];
		$error = $this->chkAttributes($row);
		if($error){
			return $this->returnError(false, current($error));
		}
		$res = $this->save();
		return $res;
    }
	/**
	 * 回写响应结果
	 * $this操作数据
	 * @param $status 当前处理结果后的状态
	 * @param $rsp_status 中信接口响应状态
	 * @param $rsp_status_text 中信接口响应结果
	 */
	public function saveRspStatus($status,$rsp_status, $rsp_status_text){
		$this->status =$status;
        $this->rsp_status=$rsp_status;
        $this->rsp_status_text=$rsp_status_text;
        $this->end_time=date('Y-m-d H:i:s');
		$result = $this->save();
		return $result;
	}
}
