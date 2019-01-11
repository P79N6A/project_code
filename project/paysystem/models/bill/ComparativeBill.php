<?php

namespace app\models\bill;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "comparative_bill".
 *
 * @property string $id
 * @property string $client_id
 * @property integer $channel_id
 * @property string $client_number
 * @property string $guest_account_name
 * @property string $guest_account_bank
 * @property string $guest_account
 * @property string $identityid
 * @property string $user_mobile
 * @property string $settle_amount
 * @property string $amount
 * @property string $settle_fee
 * @property integer $uid
 * @property integer $error_types
 * @property integer $error_status
 * @property integer $channel_status
 * @property integer $type
 * @property string $reason
 * @property string $bill_create_time
 * @property string $bill_number
 * @property string $create_time
 * @property string $modify_time
 */
class ComparativeBill extends \app\models\BaseModel
{
    const TYPE_SUCCESS = 1; //1正常
    const TYPE_ERROR = 2; //2差错
    const TYPE_HANDLE_ERROR = 3; //3处理错误
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'comparative_bill';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['client_id', 'bill_create_time', 'bill_number', 'create_time'], 'required'],
            [['channel_id','uid', 'error_types', 'error_status', 'channel_status', 'type'], 'integer'],
            [['settle_amount', 'amount', 'settle_fee'], 'number'],
            [['reason'], 'string'],
            [['bill_create_time', 'bill_number', 'create_time', 'modify_time'], 'safe'],
            [['client_id','child_channel_id', 'client_number'], 'string', 'max' => 50],
            [['guest_account_name'], 'string', 'max' => 32],
            [['guest_account_bank', 'identityid', 'user_mobile'], 'string', 'max' => 20],
            [['guest_account'], 'string', 'max' => 64]
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
            'client_number' => 'Client Number',
            'child_channel_id' => 'Child Channel Id',
            'guest_account_name' => 'Guest Account Name',
            'guest_account_bank' => 'Guest Account Bank',
            'guest_account' => 'Guest Account',
            'identityid' => 'Identityid',
            'user_mobile' => 'User Mobile',
            'settle_amount' => 'Settle Amount',
            'amount' => 'Amount',
            'settle_fee' => 'Settle Fee',
            'uid' => 'Uid',
            'error_types' => 'Error Types',
            'error_status' => 'Error Status',
            'channel_status' => 'Channel Status',
            'type' => 'Type',
            'reason' => 'Reason',
            'bill_create_time' => 'Bill Create Time',
            'bill_number' => 'Bill Number',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 计算上传文件订单的总数
     * @param $billtime
     * @return false|int|null
     */
    public function getFileCount($billtime)
    {
        if (empty($billtime)){
            return false;
        }
        $total = self::find()->where(['bill_create_time'=> $billtime])->count();
        return empty($total)? 0 : $total;
    }

    /**
     * 获取上传文件订单数据
     * @param $pages
     * @param $billtime
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    public function getFileData($pages, $billtime)
    {
        if (empty($pages)){
            return false;
        }
        $result = self::find()
            ->where(['bill_create_time' => $billtime])
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy('create_time desc')
            ->all();
        return $result;
    }

    /**
     * 获取上传文件订单总金额
     * @param $billtime
     * @return int
     */
    public function getFileMoney($billtime)
    {
        if (empty($billtime)){
            return 0;
        }
        $total = self::find()->where(['bill_create_time'=> $billtime])->sum('settle_amount');

        return empty($total)? 0 : $total;
    }

    /**
     * 获取上传文件订单总手续费
     * @param $billtime
     * @return int|mixed
     */
    public function getFileSettle($billtime)
    {
        if (empty($billtime)){
            return 0;
        }
        $total = self::find()->where(['bill_create_time'=> $billtime])->sum('settle_fee');

        return empty($total)? 0 : $total;
    }

    /**
     * 按日期分组账单总数
     * @param string $start_time
     * @param string $end_time
     * @return int
     */
    public function getDateGroupCount($start_time='', $end_time='')
    {
        $result = self::find();
        if (!empty($start_time)){
            $result->andWhere(['>=', 'bill_number', date("Y-m-d 00:00:00", strtotime($start_time))]);
        }
        if (!empty($end_time)){
            $result->andWhere(['<=', 'bill_number', date("Y-m-d 23:59:59", strtotime($end_time))]);
        }
        $total = $result->groupBy("bill_number")->count('bill_number');

        return empty($total)? 0 : $total;
    }

    /**
     * 按日期分组账单数据
     * @param $pages
     * @param string $start_time
     * @param string $end_time
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    public function getDateGroupDatas($pages, $start_time='', $end_time='')
    {
        if (empty($pages)){
            return false;
        }
        $result =  self::find();
        if (!empty($start_time)){
            $result->andWhere(['>=', 'bill_number', date("Y-m-d 00:00:00", strtotime($start_time))]);
        }
        if (!empty($end_time)){
            $result->andWhere(['<=', 'bill_number', date("Y-m-d 23:59:59", strtotime($end_time))]);
        }
        return $result->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy('bill_number desc')
            ->groupBy("bill_number")
            ->all();
    }

    /**
     * 时间区间总笔数
     * @param string $start_time
     * @param string $end_time
     * @return int|\yii\db\ActiveQuery
     */
    public function getSectionTotal($start_time='', $end_time='')
    {
        $result = self::find();
        if (!empty($start_time)){
            $result->andWhere(['>=', 'bill_number', date("Y-m-d 00:00:00", strtotime($start_time))]);
        }
        if (!empty($end_time)){
            $result->andWhere(['<=', 'bill_number', date("Y-m-d 23:59:59", strtotime($end_time))]);
        }
        $total = $result->andWhere(['type'=>[self::TYPE_SUCCESS, self::TYPE_HANDLE_ERROR]])->count();

        return empty($total)? 0 : $total;
    }

    /**
     * 时间区间总金额
     * @param string $start_time
     * @param string $end_time
     * @return int|\yii\db\ActiveQuery
     */
    public function getSectionMoney($start_time='', $end_time='')
    {
        $result = self::find();
        if (!empty($start_time)){
            $result->andWhere(['>=', 'bill_number', date("Y-m-d 00:00:00", strtotime($start_time))]);
        }
        if (!empty($end_time)){
            $result->andWhere(['<=', 'bill_number', date("Y-m-d 23:59:59", strtotime($end_time))]);
        }
        $total = $result->andWhere(['type'=>[self::TYPE_SUCCESS, self::TYPE_HANDLE_ERROR]])->sum('settle_amount');

        return empty($total)? 0 : $total;
    }

    /**
     * 时间区间总手续费
     * @param string $start_time
     * @param string $end_time
     * @return int|\yii\db\ActiveQuery
     */
    public function getSectionFee($start_time='', $end_time='')
    {
        $result = self::find();
        if (!empty($start_time)){
            $result->andWhere(['>=', 'bill_number', date("Y-m-d 00:00:00", strtotime($start_time))]);
        }
        if (!empty($end_time)){
            $result->andWhere(['<=', 'bill_number', date("Y-m-d 23:59:59", strtotime($end_time))]);
        }
        $total = $result->andWhere(['type'=>[self::TYPE_SUCCESS, self::TYPE_HANDLE_ERROR]])->sum('settle_fee');

        return empty($total)? 0 : $total;
    }

    /**
     * 时间区间总手续费
     * @param string $start_time
     * @param string $end_time
     * @return int|\yii\db\ActiveQuery
     */
    public function getSectionBillError($start_time='', $end_time='')
    {
        $result = self::find();
        if (!empty($start_time)){
            $result->andWhere(['>=', 'bill_number', date("Y-m-d 00:00:00", strtotime($start_time))]);
        }
        if (!empty($end_time)){
            $result->andWhere(['<=', 'bill_number', date("Y-m-d 23:59:59", strtotime($end_time))]);
        }
        $total = $result->andWhere(['type'=>self::TYPE_ERROR])->count();

        return empty($total)? 0 : $total;
    }

    /**
     * 通过账单日期获取总笔数
     * @param $bill_number
     * @return int
     */
    public function getBillNumberCount($bill_number)
    {
        if (empty($bill_number)){
            return 0;
        }
        $total = self::find()->where(['bill_number' => $bill_number])->count();

        return empty($total)? 0 : $total;
    }

    /**
     * 通过账单日期获取总金额
     * @param $bill_number
     * @return int|mixed
     */
    public function getBillNumberMoney($bill_number)
    {
        if (empty($bill_number)){
            return 0;
        }
        $total =self::find()->where(['bill_number' => $bill_number, 'type'=>[self::TYPE_HANDLE_ERROR,self::TYPE_SUCCESS]])->sum('settle_amount');

        return empty($total)? 0 : $total;
    }

    /**
     * 通过账单日期获取总手续费
     * @param $bill_number
     * @return int|mixed
     */
    public function getBillNumberFee($bill_number)
    {
        if (empty($bill_number)){
            return 0;
        }
        $total =self::find()->where(['bill_number' => $bill_number, 'type'=>[self::TYPE_HANDLE_ERROR,self::TYPE_SUCCESS]])->sum('settle_fee');

        return empty($total)? 0 : $total;
    }

    /**
     * 差错账总笔数
     * @param $bill_number
     * @return int
     */
    public function getBillNumberError($bill_number)
    {
        if (empty($bill_number)){
            return 0;
        }
        $total =self::find()->where(['bill_number' => $bill_number, 'type'=> self::TYPE_ERROR])->count();

        return empty($total)? 0 : $total;;
    }

    public function getBillNumberTotal($bill_number, $channel_id='', $client_number='')
    {
        if (empty($bill_number)){
            return false;
        }
        $result = self::find()
            ->where(['bill_number' => $bill_number]);
        if (!empty($channel_id)){
            $result -> andWhere(['channel_id' => $channel_id]);
        }
        if (!empty($client_number)){
            $result -> andWhere(['client_number' => $client_number]);
        }
        $total = $result->groupBy("channel_id")->count();
        return empty($total) ? 0 : $total;
    }

    /**
     * 通过账单日期获取当日所有数据
     * @param $pages
     * @param $bill_number
     * @param string $channel_id
     * @param string $client_number
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    public function getBillNumberData($pages, $bill_number, $channel_id='', $client_number='')
    {
        if (empty($bill_number) || empty($pages)){
            return false;
        }
        $result = self::find()
            ->where(['bill_number' => $bill_number]);
        if (!empty($channel_id)){
            $result -> andWhere(['channel_id' => $channel_id]);
        }
        if (!empty($client_number)){
            $result -> andWhere(['client_number' => $client_number]);
        }
        return $result->offset($pages->offset)
            ->limit($pages->limit)->groupBy("channel_id")->all();

    }

    /**
     * 通过账单日期和通道获取总笔数
     * @param $bill_number
     * @param $channel_id
     * @return int
     */
    public function getChannelBillCount($bill_number, $channel_id)
    {
        if (empty($bill_number) || empty($channel_id)){
            return 0;
        }
        $whereconfig = [
            'and',
            ['>=', 'bill_number', date("Y-m-d 00:00:00", strtotime($bill_number))],
            ['<=', 'bill_number', date("Y-m-d 23:59:59", strtotime($bill_number))],
            ['channel_id'=>$channel_id],
        ];
        $total = self::find()
            ->where($whereconfig)
            ->count();

        return empty($total)? 0 : $total;
    }

    /**
     * 通过账单日期和通道获取总笔数
     * @param $bill_number
     * @param $channel_id
     * @return int|mixed
     */
    public function getChannelBillMoney($bill_number, $channel_id)
    {
        if (empty($bill_number) || empty($channel_id)){
            return 0;
        }
        $whereconfig = [
            'and',
            ['>=', 'bill_number', date("Y-m-d 00:00:00", strtotime($bill_number))],
            ['<=', 'bill_number', date("Y-m-d 23:59:59", strtotime($bill_number))],
            ['channel_id'=>$channel_id],
        ];
        $total = self::find()
            ->where($whereconfig)
            ->sum('settle_amount');
        return empty($total) ? 0 : $total;
    }

    /**
     * 通过账单日期和通道获取总手续费
     * @param $bill_number
     * @param $channel_id
     * @return int|mixed
     */
    public function getChannelBillFee($bill_number, $channel_id)
    {
        if (empty($bill_number) || empty($channel_id)){
            return 0;
        }
        $whereconfig = [
            'and',
            ['>=', 'bill_number', date("Y-m-d 00:00:00", strtotime($bill_number))],
            ['<=', 'bill_number', date("Y-m-d 23:59:59", strtotime($bill_number))],
            ['channel_id'=>$channel_id],
        ];
        $total = self::find()
            ->where($whereconfig)
            ->sum('settle_fee');
        return empty($total) ? 0 : $total;
    }
    
    /**
     * 通过账单日期和通道获取总手续费
     * @param $bill_number
     * @param $channel_id
     * @return int|mixed
     */
    public function getChannelBillError($bill_number, $channel_id)
    {
        if (empty($bill_number) || empty($channel_id)){
            return 0;
        }
        $whereconfig = [
            'and',
            ['>=', 'bill_number', date("Y-m-d 00:00:00", strtotime($bill_number))],
            ['<=', 'bill_number', date("Y-m-d 23:59:59", strtotime($bill_number))],
            ['channel_id'=>$channel_id],
            ['type'=>self::TYPE_ERROR]
        ];
        $total = self::find()
            ->where($whereconfig)
            ->count();
        return empty($total) ? 0 : $total;
    }

    /**
     * 通过账单日期和通道获查看是否存在子集
     * @param $bill_number
     * @param $channel_id
     * @return int
     */
    public function getChannelName($bill_number, $channel_id)
    {
        if (empty($bill_number) || empty($channel_id)){
            return 0;
        }
        $whereconfig = [
            'and',
            ['>=', 'bill_number', date("Y-m-d 00:00:00", strtotime($bill_number))],
            ['<=', 'bill_number', date("Y-m-d 23:59:59", strtotime($bill_number))],
            ['channel_id'=>$channel_id],
            ['>', 'child_channel_id', 0]
        ];
        $total = self::find()
            ->where($whereconfig)
            ->groupBy('child_channel_id')->count();
        if (empty($total) || $total == 1){
            return 0;
        }
        return $total;
    }

    /**
     * 通过账单日期和通道获查看是否存在子集数据
     * @param $bill_number
     * @param $channel_id
     * @return int
     */
    public function getChannelChildData($bill_number, $channel_id)
    {
        if (empty($bill_number) || empty($channel_id)){
            return 0;
        }
        $whereconfig = [
            'and',
            ['>=', 'bill_number', date("Y-m-d 00:00:00", strtotime($bill_number))],
            ['<=', 'bill_number', date("Y-m-d 23:59:59", strtotime($bill_number))],
            ['channel_id'=>$channel_id],
        ];
        return self::find()
            ->where($whereconfig)
            ->groupBy('child_channel_id')->all();
    }

    /**
     * 通过账单日期和通道获查看是否存在子集总笔数
     * @param $bill_number
     * @param $channel_id
     * @param $child_channel_id
     * @return int
     */
    public function getChannelChildTotal($bill_number, $channel_id ,$child_channel_id)
    {
        if (empty($bill_number) || empty($channel_id)){
            return 0;
        }
        $whereconfig = [
            'and',
            ['>=', 'bill_number', date("Y-m-d 00:00:00", strtotime($bill_number))],
            ['<=', 'bill_number', date("Y-m-d 23:59:59", strtotime($bill_number))],
            ['channel_id'=>$channel_id],
            ['child_channel_id' => $child_channel_id],
        ];
        $total = self::find()
            ->where($whereconfig)
            ->count();
        return empty($total) ? 0 : $total;
    }

    /**
     * 通过账单日期和通道获查看是否存在子集总金额/元
     * @param $bill_number
     * @param $channel_id
     * @param $child_channel_id
     * @return int
     */
    public function getChannelChildMoney($bill_number, $channel_id ,$child_channel_id)
    {
        if (empty($bill_number) || empty($channel_id)){
            return 0;
        }
        $whereconfig = [
            'and',
            ['>=', 'bill_number', date("Y-m-d 00:00:00", strtotime($bill_number))],
            ['<=', 'bill_number', date("Y-m-d 23:59:59", strtotime($bill_number))],
            ['channel_id'=>$channel_id],
            ['child_channel_id' => $child_channel_id],
        ];
        $total = self::find()
            ->where($whereconfig)
            ->sum("settle_amount");
        return empty($total) ? 0 : $total;
    }

    /**
     * 通过账单日期和通道获查看是否存在子集总手续费/元
     * @param $bill_number
     * @param $channel_id
     * @param $child_channel_id
     * @return int
     */
    public function getChannelChildFee($bill_number, $channel_id ,$child_channel_id)
    {
        if (empty($bill_number) || empty($channel_id)){
            return 0;
        }
        $whereconfig = [
            'and',
            ['>=', 'bill_number', date("Y-m-d 00:00:00", strtotime($bill_number))],
            ['<=', 'bill_number', date("Y-m-d 23:59:59", strtotime($bill_number))],
            ['channel_id'=>$channel_id],
            ['child_channel_id' => $child_channel_id],
        ];
        $total = self::find()
            ->where($whereconfig)
            ->sum("settle_fee");
        return empty($total) ? 0 : $total;
    }

    /**
     * 通过账单日期和通道获查看是否存在子集总手续费/元
     * @param $bill_number
     * @param $channel_id
     * @param $child_channel_id
     * @return int
     */
    public function getChannelChildError($bill_number, $channel_id ,$child_channel_id)
    {
        if (empty($bill_number) || empty($channel_id)){
            return 0;
        }
        $whereconfig = [
            'and',
            ['>=', 'bill_number', date("Y-m-d 00:00:00", strtotime($bill_number))],
            ['<=', 'bill_number', date("Y-m-d 23:59:59", strtotime($bill_number))],
            ['channel_id'=>$channel_id],
            ['child_channel_id' => $child_channel_id],
            ['type' => self::TYPE_ERROR],
        ];
        $total = self::find()
            ->where($whereconfig)
            ->count();
        return empty($total) ? 0 : $total;
    }

    /**
     * 通过账单日期获取总笔数
     * @param $bill_number
     * @param string $channel_id
     * @param string $client_number
     * @return int
     */
    public function getChannelSearchCount($bill_number, $channel_id='', $client_number='')
    {
        if (empty($bill_number)){
            return 0;
        }

        $whereconfig = [
            'and',
            ['>=', 'bill_number', date("Y-m-d 00:00:00", strtotime($bill_number))],
            ['<=', 'bill_number', date("Y-m-d 23:59:59", strtotime($bill_number))],
        ];
        $result = self::find()->where($whereconfig);
        if (!empty($channel_id)){
            $result -> andWhere(['channel_id' => $channel_id]);
        }
        if (!empty($client_number)){
            $result -> andWhere(['client_number' => $client_number]);
        }
        $total = $result->count();
        return empty($total) ? 0 : $total;
    }

    /**
     * 通过账单日期获取总金额
     * @param $bill_number
     * @param string $channel_id
     * @param string $client_number
     * @return int
     */
    public function getChannelSearchMoney($bill_number, $channel_id='', $client_number='')
    {
        if (empty($bill_number)){
            return 0;
        }

        $whereconfig = [
            'and',
            ['>=', 'bill_number', date("Y-m-d 00:00:00", strtotime($bill_number))],
            ['<=', 'bill_number', date("Y-m-d 23:59:59", strtotime($bill_number))],
        ];
        $result = self::find()->where($whereconfig);
        if (!empty($channel_id)){
            $result -> andWhere(['channel_id' => $channel_id]);
        }
        if (!empty($client_number)){
            $result -> andWhere(['client_number' => $client_number]);
        }
        $total = $result->sum('settle_amount');
        return empty($total) ? 0 : $total;
    }

    /**
     * 通过账单日期获取总手续费
     * @param $bill_number
     * @param string $channel_id
     * @param string $client_number
     * @return int
     */
    public function getChannelSearchFee($bill_number, $channel_id='', $client_number='')
    {
        if (empty($bill_number)){
            return 0;
        }

        $whereconfig = [
            'and',
            ['>=', 'bill_number', date("Y-m-d 00:00:00", strtotime($bill_number))],
            ['<=', 'bill_number', date("Y-m-d 23:59:59", strtotime($bill_number))],
        ];
        $result = self::find()->where($whereconfig);
        if (!empty($channel_id)){
            $result -> andWhere(['channel_id' => $channel_id]);
        }
        if (!empty($client_number)){
            $result -> andWhere(['client_number' => $client_number]);
        }
        $total = $result->sum('settle_fee');
        return empty($total) ? 0 : $total;
    }

    /**
     * 通过账单日期获取总手续费
     * @param $bill_number
     * @param string $channel_id
     * @param string $client_number
     * @return int
     */
    public function getChannelSearchBillError($bill_number, $channel_id='', $client_number='')
    {
        if (empty($bill_number)){
            return 0;
        }

        $whereconfig = [
            'and',
            ['>=', 'bill_number', date("Y-m-d 00:00:00", strtotime($bill_number))],
            ['<=', 'bill_number', date("Y-m-d 23:59:59", strtotime($bill_number))],
            ['type'=>self::TYPE_ERROR],
        ];
        $result = self::find()->where($whereconfig);
        if (!empty($channel_id)){
            $result -> andWhere(['channel_id' => $channel_id]);
        }
        if (!empty($client_number)){
            $result -> andWhere(['client_number' => $client_number]);
        }
        $total = $result->count();
        return empty($total) ? 0 : $total;
    }

    /**
     * 上游账单共用部分
     * @param $data_set
     * @return \yii\db\ActiveQuery
     */
    private function whereUperBillCount($data_set='')
    {
        $result = self::find();
        //商户订单号
        if (!empty($data_set['client_id'])){
            $result->andWhere(['client_id' => $data_set['client_id']]);
        }
        //收款人
        if (!empty($data_set['guest_account_name'])){
            $result->andWhere(['guest_account_name' => $data_set['guest_account_name']]);
        }
        //账单日期
        if (!empty($data_set['start_bill_time'])){
            $result->andWhere(['>=', 'bill_number', date("Y-m-d 00:00:00", strtotime($data_set['start_bill_time']))]);
        }else{
            $result->andWhere(['>=', 'bill_number', date("Y-m-01 00:00:00", time())]);
        }
        if (!empty($data_set['end_bill_time'])){
            $result->andWhere(['<=', 'bill_number', date("Y-m-d 23:59:59", strtotime($data_set['end_bill_time']))]);
        }else{
            $result->andWhere(['<=', 'bill_number', date("Y-m-d 23:59:59", time())]);
        }
        //出款通道名称
        if (!empty($data_set['channl_id'])){
            $result->andWhere(['channel_id' => $data_set['channl_id']]);
        }
        return $result;
    }

    /**
     * 上游账单总条数
     * @param $data_set
     * @return int
     */
    public function getUperBillCount($data_set='')
    {
        $total = $this->whereUperBillCount($data_set)->count();
        return empty($total) ? 0 : $total;
    }

    /**
     * 上游账单数据
     * @param $pages
     * @param $data_set
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getUperBillData($pages, $data_set='')
    {
        return $this->whereUperBillCount($data_set)->offset($pages->offset)
        ->limit($pages->limit)->orderBy('id desc')->all();
    }

    /**
     * 上游账单总金额
     * @param string $data_set
     * @return int|mixed
     */
    public function getUperBillMoney($data_set='')
    {
        $total = $this->whereUperBillCount($data_set)->sum('settle_amount');
        return empty($total) ? 0 : $total;
    }

    /**
     * 上游账单总手续费
     * @param string $data_set
     * @return int|mixed
     */
    public function getUperBillFee($data_set='')
    {
        $total = $this->whereUperBillCount($data_set)->sum('settle_fee');
        return empty($total) ? 0 : $total;
    }

    /**
     * 上游账单总手续费
     * @param string $data_set
     * @return int|mixed
     */
    public function getUperBillError($data_set='')
    {
        $total = $this->whereUperBillCount($data_set)->andWhere(['type'=>self::TYPE_ERROR])->count();
        return empty($total) ? 0 : $total;
    }

    /**
     * 上游账单下载
     * @param string $data_set
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getUperBillDown($data_set='')
    {
        return $this->whereUperBillCount($data_set)->orderBy('id desc')->all();
    }

    /**
     * 对账成功列表公共部分
     * @param $data_set
     * @return \yii\db\ActiveQuery
     */
    public function whereReconciliation($data_set)
    {
        $result = self::find();
        //商户订单号
        if (!empty($data_set['client_id'])){
            $result->andWhere(['client_id' => $data_set['client_id']]);
        }
        //收款人
        if (!empty($data_set['guest_account_name'])){
            $result->andWhere(['guest_account_name' => $data_set['guest_account_name']]);
        }
        //账单日期
        if (!empty($data_set['start_bill_time'])){
            $result->andWhere(['>=', 'bill_number', date("Y-m-d 00:00:00", strtotime($data_set['start_bill_time']))]);
        }else{
            $result->andWhere(['>=', 'bill_number', date("Y-m-01 00:00:00", time())]);
        }
        if (!empty($data_set['end_bill_time'])){
            $result->andWhere(['<=', 'bill_number', date("Y-m-d 23:59:59", strtotime($data_set['end_bill_time']))]);
        }else{
            $result->andWhere(['<=', 'bill_number', date("Y-m-d 23:59:59", time())]);
        }
        //出款通道名称
        if (!empty($data_set['channl_id'])){
            $result->andWhere(['channel_id' => $data_set['channl_id']]);
        }
        //通道商编号
        if (!empty($data_set['client_number'])){
            $result->andWhere(['client_number' => $data_set['client_number']]);
        }
        return $result;
    }

    public function getReconciliationCount($data_set)
    {
        $result = $this->whereReconciliation($data_set);
        $total = $result -> andWhere(['type'=>[self::TYPE_SUCCESS, self::TYPE_HANDLE_ERROR]])->count();
        return empty($total) ? 0 : $total;
    }

    /**
     * 对账成功列表
     * @param $pages
     * @param string $data_set
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getReconciliationData($pages, $data_set = '')
    {
        $result = $this->whereReconciliation($data_set);
        return $result->offset($pages->offset)
            ->limit($pages->limit) -> andWhere(['type'=>[self::TYPE_SUCCESS, self::TYPE_HANDLE_ERROR]])->all();
    }

    /**
     * 对账成功列表总金额
     * @param string $data_set
     * @return int|mixed
     */
    public function getReconciliationMoney($data_set = '')
    {
        $result = $this->whereReconciliation($data_set);
        $total = $result -> andWhere(['type'=>[self::TYPE_SUCCESS, self::TYPE_HANDLE_ERROR]])->sum('settle_amount');
        return empty($total) ? 0 : $total;
    }

    /**
     * 对账成功列表总手续费
     * @param string $data_set
     * @return int|mixed
     */
    public function getReconciliationFee($data_set = '')
    {
        $result = $this->whereReconciliation($data_set);
        $total = $result -> andWhere(['type'=>[self::TYPE_SUCCESS, self::TYPE_HANDLE_ERROR]])->sum('settle_fee');
        return empty($total) ? 0 : $total;
    }

    /**
     * 对账成功列表总手续费
     * @param string $data_set
     * @return int|mixed
     */
    public function getReconciliationError($data_set = '')
    {
        $result = $this->whereReconciliation($data_set);
        $total = $result -> andWhere(['type'=>self::TYPE_ERROR])->count();
        return empty($total) ? 0 : $total;
    }

    /**
     * 对账成功列个下载数据
     * @param string $data_set
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getReconciliationDown($data_set='')
    {
        return $this->whereReconciliation($data_set)->andWhere(['type'=>[self::TYPE_SUCCESS, self::TYPE_HANDLE_ERROR]])->orderBy('id desc')->all();
    }

    /**
     * 差错账列表公共部分
     * @param $data_set
     * @return \yii\db\ActiveQuery
     *
     */
    public function whereGetErrorList($data_set)
    {
        $result = self::find();
        //商户订单号
        if (!empty($data_set['client_id'])){
            $result->andWhere(['client_id' => $data_set['client_id']]);
        }
        //收款人
        if (!empty($data_set['guest_account_name'])){
            $result->andWhere(['guest_account_name' => $data_set['guest_account_name']]);
        }
        //账单日期
        if (!empty($data_set['start_bill_time'])){
            $result->andWhere(['>=', 'bill_number', date("Y-m-d 00:00:00", strtotime($data_set['start_bill_time']))]);
        }else{
            $result->andWhere(['>=', 'bill_number', date("Y-m-01 00:00:00", time())]);
        }
        if (!empty($data_set['end_bill_time'])){
            $result->andWhere(['<=', 'bill_number', date("Y-m-d 23:59:59", strtotime($data_set['end_bill_time']))]);
        }else{
            $result->andWhere(['<=', 'bill_number', date("Y-m-d 23:59:59", time())]);
        }
        //出款通道名称
        if (!empty($data_set['channl_id'])){
            $result->andWhere(['channel_id' => $data_set['channl_id']]);
        }
        //通道商编号
        if (!empty($data_set['client_number'])){
            $result->andWhere(['client_number' => $data_set['client_number']]);
        }
        //差错类型
        if (!empty($data_set['error_types'])){
            $result->andWhere(['error_types' => $data_set['error_types']]);
        }
        return $result;
    }

    /**
     * 差错账列表总笔数
     * @param $data_set
     * @return int
     */
    public function getErrorLisCount($data_set)
    {
        $result = $this->whereGetErrorList($data_set);
        $total = $result -> andWhere(['type'=>self::TYPE_ERROR])->count();
        return empty($total) ? 0 : $total;
    }

    /**
     * 差错账列表
     * @param $pages
     * @param string $data_set
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getErrorLisData($pages, $data_set = '')
    {
        $result = $this->whereGetErrorList($data_set);
        return $result->offset($pages->offset)
            ->limit($pages->limit) -> andWhere(['type'=>self::TYPE_ERROR]) ->orderBy('create_time desc')->all();
    }

    /**
     * 差错账列表总金额
     * @param string $data_set
     * @return int|mixed
     */
    public function getErrorLisMoney($data_set = '')
    {
        $result = $this->whereGetErrorList($data_set);
        $total = $result -> andWhere(['type'=>self::TYPE_ERROR])->sum('settle_amount');
        return empty($total) ? 0 : $total;
    }

    /**
     * 差错账列表总手续费
     * @param string $data_set
     * @return int|mixed
     */
    public function getErrorLisFee($data_set = '')
    {
        $result = $this->whereGetErrorList($data_set);
        $total = $result -> andWhere(['type'=>self::TYPE_ERROR])->sum('settle_fee');
        return empty($total) ? 0 : $total;
    }

    /**
     * 差错账列表下载数据
     * @param string $data_set
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getErrorLisDown($data_set='')
    {
        return $this->whereGetErrorList($data_set)->andWhere(['type'=>self::TYPE_ERROR])->orderBy('id desc')->all();
    }
    /**
     * @param $id
     * @return array|bool|null|\yii\db\ActiveRecord
     */
    public function getBillData($id)
    {
        if (empty($id)){
            return false;
        }
        return self::find()->where(['id'=>$id])->one();
    }

    /**
     * 通过商户订单号和出款通道号查找数据
     * @param $client_id
     * @param $channel_id
     * @return array|int|null|\yii\db\ActiveRecord
     */
    public function getClientChannelOne($client_id, $channel_id)
    {
        if (empty($client_id) || empty($channel_id)){
            return 0;
        }
        return self::find()->where(['channel_id'=> $channel_id, 'client_id' => $client_id])->one();
    }

    public function saveData($data_set)
    {
        if (empty($data_set)){
            return false;
        }

        $data_set = [
            'client_id' 			=> (string)ArrayHelper::getValue($data_set, 'client_id', ''), //商户订单号',
            'channel_id' 			=> ArrayHelper::getValue($data_set, 'channel_id', 0), //出款通道id：0:未知,1:融宝,2:宝付,3:畅捷,4:玖富,5:微神马,6:新浪,7:小诺理财',
            'child_channel_id' 		=> (string)ArrayHelper::getValue($data_set, 'child_channel_id', ''), //出款通道子集',
            'client_number' 		=> (string)ArrayHelper::getValue($data_set, 'client_number', 0), //通道商编号',
            'guest_account_name' 	=> ArrayHelper::getValue($data_set, 'guest_account_name', ''), //收款人姓名',
            'guest_account_bank' 	=> ArrayHelper::getValue($data_set, 'guest_account_bank', ''), //收款人银行',
            'guest_account' 		=> ArrayHelper::getValue($data_set, 'guest_account', ''), //收款人银行卡号',
            'identityid' 			=> ArrayHelper::getValue($data_set, 'identityid', ''), //收款人证件号',
            'user_mobile' 			=> ArrayHelper::getValue($data_set, 'user_mobile', ''), //收款人手机号',
            'settle_amount' 		=> (float)ArrayHelper::getValue($data_set, 'settle_amount', 0), //借款本金(单位：元)',
            'amount' 				=> (float)ArrayHelper::getValue($data_set, 'amount', 0), //出款借款本金(单位：元)',
            'settle_fee' 			=> (float)ArrayHelper::getValue($data_set, 'settle_fee', 0), //手续费(单位：元)',
            'uid' 					=> ArrayHelper::getValue($data_set, 'uid', 1), //用户uid',
            'error_types' 			=> ArrayHelper::getValue($data_set, 'error_types', 0), //差错类型:1:通道单边账,2:支付系统单边账,3:支付系统有误,4:支付系统状态有误,5:支付对业务单边账,6:业务系统单边账,7:业务系统金额有误,8:业务系统状态有误,9:关闭订单',
            'error_status' 			=> ArrayHelper::getValue($data_set, 'error_status', 0), //差错状态 1:已处理 2:未处理  3:关闭订单',
            'channel_status'		=> ArrayHelper::getValue($data_set, 'channel_status', 0), //通道状态',
            'type' 					=> ArrayHelper::getValue($data_set, 'type', 0), //账单类型：1正常，2差错, 3处理错误',
            'reason' 				=> ArrayHelper::getValue($data_set, 'reason', ''), //原因',
            'bill_create_time' 		=> ArrayHelper::getValue($data_set, 'bill_create_time', '0000-00-00'), //连接文件上传时间',
            'bill_number' 			=> ArrayHelper::getValue($data_set, 'bill_number', '0000-00-00'), //账单日期',
            'create_time' 			=> date("Y-m-d H:i:s", time()), //创建时间',
            'modify_time' 			=> date("Y-m-d H:i:s", time()), //更新时间',
        ];
        if ($errors = $this->chkAttributes($data_set)) {
            return $this->returnError(null, implode('|', $errors));
        }
        $result = $this->save();
        return $result;
    }

    public function getIdInfo($id)
    {
        if (empty($id)){
            return false;
        }
        return self::find()->where(['id'=>$id])->one();
    }

    public function updateBill($data_set)
    {
        if (empty($data_set)){
            return false;
        }
        foreach($data_set as $k=>$v){
            $this->$k = $v;
        }
        $this->modify_time = date("Y-m-d H:i:s", time());
        return $this->save();
    }
}