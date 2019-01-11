<?php

namespace app\models\policy;
use app\models\App;
use Yii;
use app\common\Logger;
/**
 * This is the model class for table "policy_pay".
 *
 * @property integer $id
 * @property integer $aid
 * @property string $req_id
 * @property string $client_id
 * @property string $amt
 * @property string $za_order_no
 * @property string $pay_trade_no
 * @property string $pay_result
 * @property string $pay_channel
 * @property string $pay_channel_user_no
 * @property string $create_time
 * @property string $modify_time
 * @property string $order_time
 * @property string $pay_time
 * @property string $notify_time
 * @property integer $version
 */
class PolicyPay extends \app\models\BaseModel
{
    const STATUS_INIT = 0; // 初始
    const STATUS_REQING = 1; // 请求中
    const STATUS_DOING = 3; // 处理中
    const STATUS_OUTER = 4; // 失效
    const STATUS_SUCCESS = 6; // 成功
    const STATUS_FAILURE = 11; // 失败
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'policy_pay';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid','req_id', 'client_id', 'create_time', 'modify_time'], 'required'],
            [['aid', 'version','pay_status','expiry_time'], 'integer'],
            [['amt','premium'], 'number'],
            [['create_time', 'modify_time', 'order_time', 'pay_time', 'notify_time'], 'safe'],
            [['req_id', 'client_id','order_id'], 'string', 'max' => 30],
            [['pay_result', 'pay_channel'], 'string', 'max' => 20],
            [['pay_trade_no','za_order_no'], 'string', 'max' => 50],
            [['pay_channel_user_no'], 'string', 'max' => 100],
            [['return_url','callbackurl'], 'string', 'max' => 200]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'aid' => 'Aid',
            'req_id' => 'Req ID',
            'client_id' => 'Client ID',
            'amt' => 'Amt',
            'za_order_no' => 'Za Order No',
            'pay_trade_no' => 'Pay Trade No',
            'pay_result' => 'Pay Result',
            'pay_channel' => 'Pay Channel',
            'pay_channel_user_no' => 'Pay Channel User No',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
            'order_time' => 'Order Time',
            'pay_time' => 'Pay Time',
            'notify_time' => 'Notify Time',
            'version' => 'Version',
        ];
    }
    public function optimisticLock() {
        return "version";
    }
    //保存数据
    public function saveData($postData)
    { 
        if (!is_array($postData) || empty($postData)) {
            return false;
        }
        $combinData = $this->getData($postData);   
        $error = $this->chkAttributes($combinData);
        if ($error) {
            return $this->returnError(null,implode('|', $error));
        }
        $res = $this->save();
        if (!$res) {
            return $this->returnError(null,implode('|', $this->errors));
        }      
        return $this;
    }
    private function getData($postdata)
    {
        $nowtime = date('Y-m-d H:i:s');
        $postdata['pay_status']     = self::STATUS_INIT;         
        $postdata['create_time']    = $nowtime;
        $postdata['modify_time']    = $nowtime;
        $postdata['order_time']     = '0000-00-00 00:00:00';
        $postdata['pay_time']       = '0000-00-00 00:00:00';
        $postdata['notify_time']    = '0000-00-00 00:00:00';
        return $postdata;
    }
    /**
	 * 相同订单号是否重复提交
	 * */
	public function getDataByReqid($aid,$reqId) {
		$where = ['aid' => $aid, 'req_id' => $reqId];
		$ret = static::find()->where($where)->limit(1)->one();
		return $ret;
    }
    /**
     * Undocumented function
     * 根据商户订单号查询
     * @param [type] $order_id
     * @param [type] $client_id
     * @return void
     */
	public function getDataByClientId($order_id,$client_id) {
        if(empty($client_id)||empty($order_id)) return false;
		$where = ['order_id' => $order_id,'client_id'=>$client_id];
		$ret = static::find()->where($where)->limit(1)->one();
		return $ret;
    }
    //修改数据
    public function updateData($postData)
    { 
        if (!is_array($postData) || empty($postData)) {
            return false;
        }
        $postData['modify_time']    = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($postData);
        if ($error) {
            return $this->returnError(null,implode('|', $error));
        }
        $res = $this->save();
        if (!$res) {
            return $this->returnError(null,implode('|', $this->errors));
        }      
        return $res;
    }
    /**
     * Undocumented function
     * 查询请求中订单
     * @param integer $limit
     * @return void
     */
    public function getReqingData($limit=100)
    {
        $where = [
            'AND', 
            ['pay_status' => static::STATUS_REQING],
            ['>', 'create_time', date('Y-m-d H:i:00', strtotime('-7 days'))],
            ['<', 'create_time', date('Y-m-d H:i:00',strtotime('-5 minute'))] 
        ];
        $data = static::find()->where($where)->limit($limit)->all();
        return $data;
    }
    public function saveToDoing(){
        $this->refresh();
        $this->pay_status = static::STATUS_DOING;
        $this->modify_time = date('Y-m-d H:i:s');
        return $this->save();
    }
    public function saveToReqing(){
        $this->refresh();
        $this->pay_status = static::STATUS_REQING;
        $this->modify_time = date('Y-m-d H:i:s');
        return $this->save();
    }
    public function saveToOuter(){
        $this->refresh();
        $this->pay_status = static::STATUS_OUTER;
        $this->modify_time = date('Y-m-d H:i:s');
        return $this->save();
    }
    /**
     * GET 回调通知客户端 url
     * @return url
     */
    public function clientBackurl() {
        $data = $this->clientData();
        $url = $this->clientGet($this->callbackurl, $data, $this->aid);
        return $url;
    }
    /**
     * GET 页面回调链接
     */
    private function clientGet($callbackurl, $data, $aid) {
        //1 加密
        $res_data = App::model()->encryptData($aid, $data);

        //2 组成url
        $link = strpos($callbackurl, "?") === false ? '?' : '&';
        $url = $callbackurl . $link . 'res_code=0&res_data=' . rawurlencode($res_data);
        Logger::dayLog('policy/clientGet',  $url, 'data:', $data);
        return $url;
    }
    private function clientData(){
        $data = [
			'req_id' 		=> $this->req_id,
			'client_id' 	=> $this->client_id,
			'remit_status'  => $this->pay_status,
        ];
        return $data;
    }
}