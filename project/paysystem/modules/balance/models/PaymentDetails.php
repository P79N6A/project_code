<?php

namespace app\modules\balance\models;

use app\common\Logger;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "payment_details".
 *
 * @property string $id
 * @property string $client_id
 * @property integer $channel_id
 * @property string $series
 * @property string $name
 * @property string $opening_bank
 * @property string $guest_account
 * @property string $identityid
 * @property string $user_mobile
 * @property string $amount
 * @property string $payment_amount
 * @property string $settle_fee
 * @property string $payment_date
 * @property string $create_time
 * @property integer $passageway_type
 * @property string $error_types
 * @property integer $loss
 * @property integer $state
 * @property string $reason
 * @property string $modify_time
 * @property integer $uid
 */
class PaymentDetails extends \app\models\BaseModel
{
    const TYPE_SUCCESS = 1; //成功
    const TYPE_FAIL = 2;  //失败

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cg_payment_details';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['client_id', 'create_time','collection_time'], 'required'],
            [['channel_id', 'passageway_type', 'loss', 'state', 'uid', 'type', 'auditing_status', 'file_id', 'collection_state','aid','sort','source'], 'integer'],
            [['amount', 'payment_amount', 'settle_fee'], 'number'],
            [['payment_date', 'create_time', 'modify_time','collection_time'], 'safe'],
            [['reason','collection_reason','return_channel'], 'string'],
            [['client_id', 'series'], 'string', 'max' => 50],
            [['name', 'opening_bank'], 'string', 'max' => 32],
            [['guest_account','error_types'], 'string', 'max' => 64],
            [['identityid', 'user_mobile'], 'string', 'max' => 20],

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'client_id' => 'Client ID',
            'channel_id' => 'Channel ID',
            'series' => 'Series',
            'name' => 'Name',
            'opening_bank' => 'Opening Bank',
            'guest_account' => 'Guest Account',
            'identityid' => 'Identityid',
            'user_mobile' => 'User Mobile',
            'amount' => 'Amount',
            'payment_amount' => 'Payment Amount',
            'settle_fee' => 'Settle Fee',
            'payment_date' => 'Payment Date',
            'create_time' => 'Create Time',
            'passageway_type' => 'Passageway Type',
            'error_types' => 'Error Types',
            'loss' => 'Loss',
            'state' => 'State',
            'reason' => 'Reason',
            'modify_time' => 'Modify Time',
            'uid' => 'Uid',
            'type'=> 'Type',
            'file_id'=>'File Id',
            'auditing_status'=> 'Auditing Status',
            'aid' => 'Aid',
            'source' => 'Source',
            'collection_state' =>'Collection  State',
            'collection_reason' =>'Collection Reason',
            'sort'  =>'Sort',

        ];
    }

    /**
     * 初始条件
     * @param $filter_where
     * @return int|\yii\db\ActiveQuery
     */
    private function paymentWhere($filter_where)
    {
        if (empty($filter_where)){
            return 0;
        }
        $result = self::find();
        if (!empty($filter_where['state_id'])){
            $result->andWhere(['type' => $filter_where['state_id']]);
        }
        if (!empty($filter_where['order_id'])){
            $result->andWhere(['client_id' => $filter_where['order_id']]);
        }
        if (!empty($filter_where['aid'])){
            $result->andWhere(['aid' => $filter_where['aid']]);
        }
        if (!empty($filter_where['return_channel'])){
            $result->andWhere(['return_channel' => $filter_where['return_channel']]);
        }
        if (!empty($filter_where['source'])){
            $result->andWhere(['source' => $filter_where['source']]);
        }
        if (!empty($filter_where['series'])){
            $result->andWhere(['series' => $filter_where['series']]);
        }
        if (!empty($filter_where['start_time'])){
            $result->andWhere(['>=', 'payment_date', $filter_where['start_time']. ' 00:00:00']);
        }
        if (!empty($filter_where['end_time'])){
            $result->andWhere(['<=', 'payment_date', $filter_where['end_time']. ' 23:59:59']);
        }
        if (!empty($filter_where['create_time'])){
            $result->andWhere(['>=', 'create_time', $filter_where['create_time']. ' 00:00:00']);
        }
        if (!empty($filter_where['create_times'])){
            $result->andWhere(['<=', 'create_time', $filter_where['create_times']. ' 23:59:59']);
        }
        return $result;
    }

    /**
     * 计算时间区间条数
     * @param $filter_where
     * @return int
     */
    public function countPaymentData($filter_where)
    {
        if (empty($filter_where)){
            return 0;
        }
        $result = $this->paymentWhere($filter_where);
        return $result->groupBy('channel_id, payment_date')->count();
    }

    /**
     * 计算时间区间条数
     * @param $filter_where
     * @return int
     */
    public function countData($filter_where)
    {
        if (empty($filter_where)){
            return 0;
        }
        $result = $this->paymentWhere($filter_where);
        return $result->count();
        //return $result->groupBy('channel_id, payment_date')->count();
    }

    /**
     * 获取时间区间的数据
     * @param $pages
     * @param $filter_where
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    public function getAllData($pages, $filter_where)
    {
        if (empty($pages)){
            return false;
        }
        $result = $this->paymentWhere($filter_where);

        return $result->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy('create_time desc')
            ->groupBy('channel_id, payment_date,create_time')
            ->all();
    }

    /**
     * 获取时间区间的数据
     * @param $pages
     * @param $filter_where
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    public function getData($pages, $filter_where)
    {
        if (empty($pages)){
            return false;
        }
        $result = $this->paymentWhere($filter_where);

        return $result->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy('create_time desc')
            ->all();
    }
    /**
     * 获取导出的数据
     * @param $pages
     * @param $filter_where
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    public function getDatas($filter_where)
    {

        $result = $this->paymentWhere($filter_where);

        return $result->orderBy('create_time desc')->all();
    }
    /**
     * 获取时间区间的成功总笔数
     * @param $filter_where
     * @return int
     */
    public function getSectionTotal($filter_where)
    {
        if (empty($filter_where)){
            return 0;
        }
        $result = $this->paymentWhere($filter_where);
        $result->andWhere(['type'=>self::TYPE_SUCCESS]);
        $total = $result->count();
        return empty($total) ? 0 : $total;
    }

    /**
     * 获取时间区间的成功总手续费
     * @param $filter_where
     * @return int
     */
    public function getSectionFee($filter_where)
    {
        if (empty($filter_where)){
            return 0;
        }
        $result = $this->paymentWhere($filter_where);
        $result->andWhere(['type'=>self::TYPE_SUCCESS]);
        $total = $result->sum('settle_fee');
        return empty($total) ? 0 : $total;
    }

    /**
     * 获取时间区间的 成功总金额
     * @param $filter_where
     * @return int
     */
    public function getSectionAmount($filter_where)
    {
        if (empty($filter_where)){
            return 0;
        }
        $result = $this->paymentWhere($filter_where);
        $result->andWhere(['type'=>self::TYPE_SUCCESS]);
        $total = $result->sum('amount');
        return empty($total) ? 0 : $total;
    }



    /**
     * 获取时间区间的 差错总笔数
     * @param $filter_where
     * @return int
     */
    public function getSectionFailTotal($filter_where)
    {
        if (empty($filter_where)){
            return 0;
        }
        $result = $this->paymentWhere($filter_where);
        $result->andWhere(['type'=>self::TYPE_FAIL]);
        $total = $result->count();
        return empty($total) ? 0 : $total;
    }

    /**
     * 获取时间区间的 差错总金额
     * @param $filter_where
     * @return int
     */
    public function getSectionFailAmount($filter_where)
    {
        if (empty($filter_where)){
            return 0;
        }
        $result = $this->paymentWhere($filter_where);
        $result->andWhere(['type'=>self::TYPE_FAIL]);
        $total = $result->sum('amount');
        return empty($total) ? 0 : $total;
    }

    /**
     * 获取时间区间的 差错账手续费
     * @param $filter_where
     * @return int
     */
    public function getSectionFailFee($filter_where)
    {
        if (empty($filter_where)){
            return 0;
        }
        $result = $this->paymentWhere($filter_where);
        $result->andWhere(['type'=>self::TYPE_FAIL]);
        $total = $result->sum('settle_fee');
        return empty($total) ? 0 : $total;
    }

    /**
     * 成功条件
     * @param $channel_id
     * @param $payment_date
     * @return array
     */
    private function successWhere($channel_id, $payment_date)
    {
        return [
            'AND',
            ['=', 'channel_id', $channel_id],
            ['=', 'payment_date', $payment_date],
            ['=', 'type', self::TYPE_SUCCESS],
        ];
    }

    /**
     * 成功总笔数
     * @param $channel_id
     * @param $payment_date
     * @return int
     */
    public function getSuccessTotal($channel_id, $payment_date)
    {
        if (empty($payment_date)){
            return 0;
        }
        $where_config = $this->successWhere($channel_id, $payment_date);

        $total = self::find()->where($where_config)->count();
        return empty($total) ? 0 : $total;
    }

    /**
     * 成功总金额
     * @param $channel_id
     * @param $payment_date
     * @return int|mixed
     */
    public function getSuccessMoney($channel_id, $payment_date)
    {
        if (empty($payment_date)){
            return 0;
        }
        $where_config = $this->successWhere($channel_id, $payment_date);

        $total = self::find()->where($where_config)->sum('amount');
        return empty($total) ? 0 : $total;
    }

    /**
     * 成功总手续
     * @param $channel_id
     * @param $payment_date
     * @return int|mixed
     */
    public function getSuccessFee($channel_id, $payment_date)
    {
        if (empty($payment_date)){
            return 0;
        }
        $where_config = $this->successWhere($channel_id, $payment_date);

        $total = self::find()->where($where_config)->sum('settle_fee');
        return empty($total) ? 0 : $total;
    }

    /**
     * 失败条件
     * @param $channel_id
     * @param $payment_date
     * @return array
     */
    private function errorWhere($channel_id, $payment_date)
    {
        return [
            'AND',
            ['=', 'channel_id', $channel_id],
            ['=', 'payment_date', $payment_date],
            ['=', 'type', self::TYPE_FAIL],
        ];
    }

    /**
     * 失败总笔数
     * @param $channel_id
     * @param $payment_date
     * @return int
     */
    public function getErrorTotal($channel_id, $payment_date)
    {
        if (empty($payment_date)){
            return 0;
        }
        $where_config = $this->errorWhere($channel_id, $payment_date);

        $total = self::find()->where($where_config)->count();
        return empty($total) ? 0 : $total;
    }

    /**
     * 失败总金额
     * @param $channel_id
     * @param $payment_date
     * @return int|mixed
     */
    public function getErrorMoney($channel_id, $payment_date)
    {
        if (empty($payment_date)){
            return 0;
        }
        $where_config = $this->errorWhere($channel_id, $payment_date);

        $total = self::find()->where($where_config)->sum('amount');
        return empty($total) ? 0 : $total;
    }

    /**
     * 失败总手续
     * @param $channel_id
     * @param $payment_date
     * @return int|mixed
     */
    public function getErrorFee($channel_id, $payment_date)
    {
        if (empty($payment_date)){
            return 0;
        }
        $where_config = $this->errorWhere($channel_id, $payment_date);

        $total = self::find()->where($where_config)->sum('settle_fee');
        return empty($total) ? 0 : $total;
    }

    /**
     * 放款下载
     * @param $channel_id
     * @param $payment_date
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    public function downStatisticsData($channel_id, $payment_date)
    {
        if (empty($payment_date)){
            return false;
        }
        $where_config = [
            'AND',
            ['=', 'channel_id', $channel_id],
            ['=', 'payment_date', $payment_date],
            ['=', 'source', 1],
        ];
        return self::find()->where($where_config)->all();
    }

    /**
     * 回款下载
     * @param $channel_id
     * @param $payment_date
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    public function downStatisticsDatas($channel_id, $payment_date)
    {
        if (empty($payment_date)){
            return false;
        }
        $where_config = [
            'AND',
            ['=', 'channel_id', $channel_id],
            ['=', 'payment_date', $payment_date],
            ['=', 'source', 2],
        ];
        return self::find()->where($where_config)->all();
    }

    /**
     * 通过文件id获取数据条件
     * @param $filter_where
     * @return int|\yii\db\ActiveQuery
     */
    private function fileWhere($filter_where)
    {
        if (empty($filter_where)){
            return 0;
        }
        $result = self::find();
        if (!empty($filter_where['file_id'])){
            $result->andWhere(['file_id' => $filter_where['file_id']]);
        }
        if (!empty($filter_where['client_id'])){
            $result->andWhere(['client_id' => $filter_where['client_id']]);
        }
        if (!empty($filter_where['name'])){
            $result->andWhere(['name' => $filter_where['name']]);
        }
        return $result;
    }

    /**
     * 通过文件id和条件查找条数
     * @param $filter_where
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    public function getFileIdTotal($filter_where)
    {
        if (empty($filter_where)){
            return false;
        }
        $result = $this->fileWhere($filter_where);
        $total = $result->count();
        return empty($total) ? 0 : $total;
    }

    /**
     * 通过文件id和条件查找数据
     * @param $pages
     * @param $filter_where
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    public function getFileIdData($pages, $filter_where)
    {
        if (empty($pages) || empty($filter_where)){
            return false;
        }
        $result = $this->fileWhere($filter_where);
        return $result->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy('create_time desc')
            ->all();
    }

    /**
     * 通过文件id和条件查找总金额
     * @param $filter_where
     * @return bool|int|mixed
     */
    public function getFileIdMoney($filter_where)
    {
        if (empty($filter_where)){
            return false;
        }
        $result = $this->fileWhere($filter_where);
        $total = $result->sum('amount');
        return empty($total) ? 0 : $total;
    }

    /**
     * 通过文件id和条件查找手续费
     * @param $filter_where
     * @return bool|int|mixed
     */
    public function getFileIdFee($filter_where)
    {
        if (empty($filter_where)){
            return false;
        }
        $result = $this->fileWhere($filter_where);
        $total = $result->sum('settle_fee');
        return empty($total) ? 0 : $total;
    }

    /**
     * 差错账条件
     * @param $filter_where
     * @return int|\yii\db\ActiveQuery
     */
    private function mistakeWhere($filter_where)
    {
        if (empty($filter_where)){
            return 0;
        }
        $result = self::find();
        if (!empty($filter_where['return_channel'])){
            $result->andWhere(['return_channel' => $filter_where['return_channel']]);
        }
        if (!empty($filter_where['source'])){
            $result->andWhere(['source' => $filter_where['source']]);
        }
        if (!empty($filter_where['client_id'])){
            $result->andWhere(['client_id' => $filter_where['client_id']]);
        }
        if (!empty($filter_where['name'])){
            $result->andWhere(['name' => $filter_where['name']]);
        }
        if (!empty($filter_where['error_types'])){
            $result->andWhere(['error_types' => $filter_where['error_types']]);
        }
        if (!empty($filter_where['auditing_status'])){
            $result->andWhere(['auditing_status' => $filter_where['auditing_status']]);
        }
        if (!empty($filter_where['start_time'])){
            $result->andWhere(['>=', 'payment_date', $filter_where['start_time']. ' 00:00:00']);
        }
        if (!empty($filter_where['end_time'])){
            $result->andWhere(['<=', 'payment_date', $filter_where['end_time']. ' 23:59:59']);
        }
        //
        $result->andWhere(['=', 'type', self::TYPE_FAIL]);
        return $result;
    }

    /**
     * 差错账总条数
     * @param $filter_where
     * @return int
     */
    public function getMistakeTotal($filter_where)
    {

        if (empty($filter_where)){

            return 0;

        }
        $result = $this->mistakeWhere($filter_where);
        $total = $result->count();
        return empty($total) ? 0 : $total;
    }

    /**
     * 差错账总金额
     * @param $filter_where
     * @return int
     */
    public function getMistakeAmount($filter_where)
    {
        if (empty($filter_where)){
            return 0;
        }
        $result = $this->mistakeWhere($filter_where);
        $total = $result->sum('amount');
        return empty($total) ? 0 : $total;
    }

    /**
     * 差错账总金额
     * @param $filter_where
     * @return int
     */
    public function getMistakeFee($filter_where)
    {
        if (empty($filter_where)){
            return 0;
        }
        $result = $this->mistakeWhere($filter_where);
        $total = $result->sum('settle_fee');
        return empty($total) ? 0 : $total;
    }
    /**
     * 差错账总数据
     * @param $pages
     * @param $filter_where
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    public function getMistakeData($pages, $filter_where)
    {
        if (empty($pages) || empty($filter_where)){
            return false;
        }
        $result = $this->mistakeWhere($filter_where);
        return $result->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy('create_time desc')
            ->all();
    }
    
    

    public function getDetails($id)
    {
        if (empty($id)){
            return false;
        }
        return self::find()->where(['id'=>$id])->one();
    }

    /**
     * 修改数据
     * @param $data_set
     * @return bool
     */
    public function updateData($data_set)
    {
        if (empty($data_set)){
            return false;
        }
        $this->modify_time = date("Y-m-d H:i:s", time());
        foreach($data_set as $k => $v){
            $this->$k = $v;
        }
        return $this->save();
    }

    /**
     * 保存数据
     * @param $data_set
     * @return bool
     */
    public function saveData($data_set)
    {

        if (empty($data_set)){
            return false;
        }
        $save_data = [
            'client_id'             => (string)ArrayHelper::getValue($data_set, 'client_id'),//商户订单号
            'channel_id'            => ArrayHelper::getValue($data_set, 'channel_id', 0), //回款通道id
            'series'                => (string)ArrayHelper::getValue($data_set, 'series', 0), //通道商编号
            'return_channel'        => (string)ArrayHelper::getValue($data_set, 'return_channel', 0), //回款通道
            'name'                  => ArrayHelper::getValue($data_set, 'name', ''), //姓名
            'opening_bank'          => ArrayHelper::getValue($data_set, 'opening_bank', ''), //开户行
            'guest_account'         => (string)ArrayHelper::getValue($data_set, 'guest_account', ''), //银行卡号
            'identityid'            => (string)ArrayHelper::getValue($data_set, 'identityid', ''), //证件号
            'user_mobile'           => (string)ArrayHelper::getValue($data_set, 'user_mobile', ''), //收款人手机号
            'amount'                => floatval(ArrayHelper::getValue($data_set, 'amount',0)), //第三方交易金额
            'payment_amount'        => ArrayHelper::getValue($data_set, 'payment_amount', ''), //平台金额
            'settle_fee'            => floatval(ArrayHelper::getValue($data_set, 'settle_fee', 0)), //手续费
            'payment_date'          => ArrayHelper::getValue($data_set, 'payment_date', ''), //账单日期
            'create_time'           => ArrayHelper::getValue($data_set, 'create_time', ''), //创建时间
            'file_id'               => ArrayHelper::getValue($data_set, 'file_id', ''), //文件ID
            'auditing_status'       => ArrayHelper::getValue($data_set, 'auditing_status', ''), //审核状态:1待审核2审核通过3审核失败
            'modify_time'           => ArrayHelper::getValue($data_set, 'modify_time', ''), //更新时间
            'reason'                => (string)ArrayHelper::getValue($data_set, 'reason', ''), //原因
            'error_types'           => (string)ArrayHelper::getValue($data_set, 'error_types', ''), //差错类型
            'loss'                  => ArrayHelper::getValue($data_set, 'loss', ''), //确认亏损:1是，2否
            'uid'                   => ArrayHelper::getValue($data_set, 'uid', ''), //用户uid
            'type'                  => ArrayHelper::getValue($data_set, 'type', ''),
            'source'                => ArrayHelper::getValue($data_set, 'source', ''),//1:体外放款，2：体外回款
            'state'                 => ArrayHelper::getValue($data_set, 'state', ''),
            'passageway_type'       => ArrayHelper::getValue($data_set, 'passageway_type', ''),//第三方通道状态和花生米富系统状态
            'aid'                   => ArrayHelper::getValue($data_set, 'aid', ''), //通道id
            'collection_state'      => ArrayHelper::getValue($data_set, 'collection_state', ''), //抓取状态 默认，1抓取中，2成功，3重试
            'collection_reason'     => (string)ArrayHelper::getValue($data_set, 'collection_reason', ''), //抓取返回的信息
            'collection_time'       => ArrayHelper::getValue($data_set, 'collection_time', ''), //更新时间
            'sort'                  => ArrayHelper::getValue($data_set, 'sort', ''),
        ];
        $error = $this->chkAttributes($save_data);
        if ($error) {
            Logger::dayLog('paymentbill/run', '记录数据失败', json_encode($error));
            return false;
        }
        return $this->save();
    }

    /**
     * 获取指定数据
     * @param $client_id
     * @return array|bool|null|\yii\db\ActiveRecord
     */
    public function getOrderId($client_id)
    {
        if (empty($client_id)){
            return false;
        }
        return self::find()->where(['client_id'=>$client_id])->one();
    }



    //---------------------------------------------------------------


    // 通知状态
    #const STATUS_INIT = 1; // 初始
    const STATUS_INIT = 0; // 初始
    #const STATUS_DOING = 5; // 锁定
    const STATUS_DOING = 1; // 锁定
    const STATUS_SUCCESS = 2; // 成功
    const STATUS_RETRY = 3; // 重试
    const STATUS_FAILURE = 11; // 失败
    #99   为展期的 不做拆账


    private $notifyMap;
    public function init(){
        $this->notifyMap = [
            'STATUS_INIT' => static::STATUS_INIT,
            'STATUS_DOING' => static::STATUS_DOING,
            'STATUS_SUCCESS' => static::STATUS_SUCCESS,
            'STATUS_RETRY' => static::STATUS_RETRY,
            'STATUS_FAILURE' => static::STATUS_FAILURE,
        ];
    }
    /**
     * 获取状态为可以抓取的记录
     * @return []
     */
    public function getRequestList($pages=500) {

        $result = self::find();
        #$result->andWhere(['passageway_type' => 3]);
        $result->andWhere(['error_types' => 0]);
//        $result->andWhere(['state' => 1]);
        $result->andWhere(['type' => 1]);
        $result->andWhere(['source' => 1]);
        $result->andWhere(['in','collection_state',[static::STATUS_INIT,static::STATUS_RETRY]]);
        $dataList = $result->limit($pages)->all();
        if (!$dataList) {
            return null;
        }
        return $dataList;
    }

    /*
   * 根据id 查询单条语句
   * */
    public function getOne($id,$column='id'){
        if(empty($id)){
            return false;
        }
        $result = self::find()->where(['=' , $column , $id] )->one();
        return $result;
    }


    public function gStatus($status_str=null){
        if($status_str){
            return $this->notifyMap[$status_str];
        }else{
            return $this->notifyMap;
        }
    }

    /**
     * 锁定正在抓取的状态
     */
    public function lockStatus($ids) {
        if (!is_array($ids) || empty($ids)) {
            return 0;
        }
        $ups = static::updateAll(['collection_state' => static::STATUS_DOING,'collection_time'=>date('Y-m-d H:i:s',time())], ['id' => $ids]);
        return $ups;
    }


    /**
     *
     * $this 修改抓取状态和抓取信息
     * @return bool
     */
    public function saveOneCollectionStatus($data,$collection_state,$reason){
        $re = $this->getOne($data['id']);
        $re->collection_time = date('Y-m-d H:i:s');
        $re->collection_state = $collection_state;
        if(!empty($reason)){
            $reason = substr($reason, 0, 30);
            $re->collection_reason = $reason;
        }
        $result = $re->update();
        return $result;
    }

    /**
     * Undocumented function
     * 更新数据数据
     * @param [type] $postdata
     * @return void
     */
    public function updateDatas($postData){
        if (!is_array($postData) || empty($postData)) {
            return $this->returnError(false, '不能为空');
        }
        $re = $this->getOrderId(trim(ArrayHelper::getValue($postData,'client_id')));
        if($re){

            $new_time = date('Y-m-d H:i:s');
            $re->client_id                 = (string)ArrayHelper::getValue($postData, 'client_id', 0);
            $re->channel_id                = ArrayHelper::getValue($postData, 'channel_id', 0);
            $re->series                    = (string)ArrayHelper::getValue($postData, 'series', 0);
            $re->return_channel            = (string)ArrayHelper::getValue($postData, 'return_channel', '0');
            $re->name                      = ArrayHelper::getValue($postData, 'name', 0);
            $re->opening_bank              = ArrayHelper::getValue($postData, 'opening_bank', 0);
            $re->guest_account             = (string)ArrayHelper::getValue($postData, 'guest_account', 0);
            $re->identityid                = (string)ArrayHelper::getValue($postData, 'identityid', 0);
            $re->user_mobile               = (string)ArrayHelper::getValue($postData, 'user_mobile', '0');
            $re->amount                    = ArrayHelper::getValue($postData, 'amount', 0);
            $re->payment_amount            = ArrayHelper::getValue($postData, 'payment_amount', 0);
            $re->settle_fee                = ArrayHelper::getValue($postData, 'settle_fee', '0');
            $re->payment_date              = ArrayHelper::getValue($postData, 'payment_date', '0');
            $re->create_time               = ArrayHelper::getValue($postData, 'create_time', '0');
            $re->file_id                   = ArrayHelper::getValue($postData, 'file_id', 0);
            $re->auditing_status           = ArrayHelper::getValue($postData, 'auditing_status', 0);
            $re->modify_time               = $new_time;
            $re->reason                    = (string)ArrayHelper::getValue($postData, 'reason', 0);
            $re->error_types               = (string)ArrayHelper::getValue($postData, 'error_types', '0');
            $re->loss                      = ArrayHelper::getValue($postData, 'loss', 0);
            $re->uid                       = ArrayHelper::getValue($postData, 'uid', 0);
            $re->type                      = ArrayHelper::getValue($postData, 'type', 0);
            $re->source                    = ArrayHelper::getValue($postData, 'source', 0);
            $re->state                     = ArrayHelper::getValue($postData, 'state', '');
            $re->passageway_type           = ArrayHelper::getValue($postData, 'passageway_type', 0);
            $re->aid                       = ArrayHelper::getValue($postData, 'aid', 0);
            $re->collection_state          = ArrayHelper::getValue($postData, 'collection_state', '0');
            $re->collection_reason         = (string)ArrayHelper::getValue($postData, 'collection_reason', '0');
            $re->collection_time           = ArrayHelper::getValue($postData, 'collection_time', '0');
            $re->sort                      = ArrayHelper::getValue($postData, 'sort', '0');
            $error = $this->chkAttributes($postData);
            $result =$re->update($postData);
            //var_dump($error);die;
            return $result;
        }

        /*$error = $this->chkAttributes($postData);
        if ($error) {
            return $this->returnError(false, $error);
        }*/

    }
    
}