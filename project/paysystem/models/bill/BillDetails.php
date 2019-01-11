<?php

namespace app\models\bill;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "bill_details".
 *
 * @property string $id
 * @property string $client_id
 * @property integer $channel_id
 * @property string $guest_account_bank
 * @property string $guest_account_name
 * @property string $guest_account
 * @property string $identityid
 * @property string $settle_amount
 * @property string $settle_fee
 * @property string $user_mobile
 * @property string $error_types
 * @property integer $error_status
 * @property integer $type
 * @property string $bill_number
 * @property string $reason
 * @property string $create_time
 * @property string $modify_time
 */
class BillDetails extends \app\models\BaseModel
{
    const SUCCESS_TYPE = 1;
    const FAIL_TYPE = 2;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'bill_details';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['client_id', 'create_time'], 'required'],
            [['channel_id', 'error_status', 'type', 'bill_number', 'req_id', 'uid'], 'integer'],
            [['settle_amount', 'settle_fee', 'amount'], 'number'],
            [['reason'], 'string'],
            [['create_time', 'modify_time'], 'safe'],
            [['client_id'], 'string', 'max' => 50],
            [['guest_account_bank', 'identityid', 'user_mobile'], 'string', 'max' => 20],
            [['guest_account_name'], 'string', 'max' => 32],
            [['guest_account'], 'string', 'max' => 64],
            [['error_types'], 'string', 'max' => 255]
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
            'guest_account_bank' => 'Guest Account Bank',
            'guest_account_name' => 'Guest Account Name',
            'guest_account' => 'Guest Account',
            'identityid' => 'Identityid',
            'settle_amount' => 'Settle Amount',
            'settle_fee' => 'Settle Fee',
            'amount' => 'Amount',
            'user_mobile' => 'User Mobile',
            'error_types' => 'Error Types',
            'error_status' => 'Error Status',
            'type' => 'Type',
            'bill_number' => 'Bill Number',
            'reason' => 'Reason',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
            'uid' => 'Uid',
        ];
    }

    /**
     * 交易总笔数
     * @param $channel_id
     * @param $bill_number
     * @return int
     */
    public function totalPenCount($channel_id, $bill_number)
    {
        if (empty($channel_id) || empty($bill_number)){
            return 0;
        }
        $config = [
            'and',
            ['channel_id' => $channel_id],
            ['bill_number' => $bill_number],
        ];
        $total = self::find()->where($config)->count();
        return empty($total)?0:$total;
    }

    /**
     * 交易总金额
     * @param $channel_id
     * @param $bill_number
     * @return int|mixed
     */
    public function totalMoney($channel_id, $bill_number)
    {
        if (empty($channel_id) || empty($bill_number)){
            return 0;
        }
        $config = [
            'and',
            ['channel_id' => $channel_id],
            ['bill_number' => $bill_number],
        ];
        $money = self::find()->where($config)->sum('settle_amount');
        return empty($money)?0:$money;
    }

    /**
     * 结算手续费
     * @param $channel_id
     * @param $bill_number
     * @return int|mixed
     */
    public function totalSettleFee($channel_id, $bill_number)
    {
        if (empty($channel_id) || empty($bill_number)){
            return 0;
        }
        $config = [
            'and',
            ['channel_id' => $channel_id],
            ['bill_number' => $bill_number],
        ];
        $total_settle_fee = self::find()->where($config)->sum('settle_fee');
        return empty($total_settle_fee)?0:$total_settle_fee;
    }

    /**
     * 出款通道数据
     * @param $channel_id
     * @param $bill_number
     * @param $pages
     * @return array|int|\yii\db\ActiveRecord[]
     */
    public function billListData($channel_id, $bill_number, $pages)
    {
        if (empty($channel_id) || empty($bill_number)){
            return 0;
        }
        $config = [
            'and',
            ['channel_id' => $channel_id],
            ['bill_number' => $bill_number],
        ];
        $result = self::find()
            ->where($config)
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy('modify_time desc')
            ->all();
        return $result;
    }

    /**
     * 总数
     * @return int
     */
    public function totalCount()
    {
        $total = self::find()->count();
        return empty($total)?0:$total;
    }

    /**
     * 所有账单
     * @param $pages
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    public function getAllData($pages)
    {
        if (empty($pages)){
            return false;
        }
        $result = self::find()
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy('modify_time desc')
            ->all();
        return $result;
    }

    /**
     * 对账成功总数
     * @return int
     */
    public function successBillTotal()
    {
        $total = self::find()->where(['type' => self::SUCCESS_TYPE])->count();
        return empty($total)?0:$total;
    }

    /**
     * 对账成功列表分页
     * @param $pages
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    public function successBillData($pages)
    {
        if (empty($pages)){
            return false;
        }
        $result = self::find()
            ->where(['type' => self::SUCCESS_TYPE])
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy('modify_time desc, id desc')
            ->all();
        return $result;
    }

    /**
     * 对账成功列表总数据
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    public function getSuccessBillData()
    {
        $result = self::find()
            ->where(['type' => self::SUCCESS_TYPE])
            ->orderBy('modify_time desc')
            ->all();
        return $result;
    }

    /**
     * 差错账总数
     * @return int
     */
    public function failBillTotal()
    {
        $total = self::find()->where(['type' => self::FAIL_TYPE])->count();
        return empty($total)?0:$total;
    }
    /**
     * 对账差错列表总数据
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    public function getFailBillData()
    {
        $result = self::find()
            ->where(['type' => self::FAIL_TYPE])
            ->orderBy('modify_time desc')
            ->all();
        return $result;
    }
    /**
     * 差错账列表
     * @param $pages
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    public function failBillData($pages)
    {
        if (empty($pages)){
            return false;
        }
        $result = self::find()
            ->where(['type' => self::FAIL_TYPE])
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy('modify_time desc')
            ->all();
        return $result;
    }

    public function getSuccessDetailsData($id)
    {
        if (empty($id)){
            return false;
        }
        return self::find()->where(['id'=>$id, 'type'=>self::SUCCESS_TYPE])->one();
    }
    public function getFailDetailsData($id)
    {
        if (empty($id)){
            return false;
        }
        return self::find()->where(['id'=>$id, 'type'=>self::FAIL_TYPE])->one();
    }

    public function updateBillData($data_set)
    {
        if (empty($data_set)){
            return $this->returnError(false, 400);
        }
        foreach($data_set as $key=>$value){
            $this->$key = $value;
        }
        $this->modify_time = date("Y-m-d H:i:s", time());
        $result = $this->save();
        return $result;
    }
    
    public function saveBillDetails($data_set)
    {
        if (empty($data_set)){
            return false;
        }
        $bill_data = [
            'client_id' => ArrayHelper::getValue($data_set, 'client_id', 0), //商户订单号',
            'channel_id' => ArrayHelper::getValue($data_set, 'channel_id', ''), //出款通道id',
            'guest_account_bank' => ArrayHelper::getValue($data_set, 'guest_account_bank', ''), //收款人银行',
            'guest_account_name' => ArrayHelper::getValue($data_set, 'guest_account_name', ''), //收款人姓名',
            'guest_account' => ArrayHelper::getValue($data_set, 'guest_account', ''), //收款人银行卡号',
            'identityid' => ArrayHelper::getValue($data_set, 'identityid', ''), //收款人证件号',
            'settle_amount' =>  ArrayHelper::getValue($data_set, 'settle_amount', 0), //借款本金(单位：元)',
            'amount' => ArrayHelper::getValue($data_set, 'amount', 0), //出款借款本金(单位：元)',
            'settle_fee' => ArrayHelper::getValue($data_set, 'settle_fee', 0), //结算手续费',
            'user_mobile' => ArrayHelper::getValue($data_set, 'user_mobile', ''), //收款人手机号',
            'error_types' => ArrayHelper::getValue($data_set, 'error_types', ''), //差错类型',
            'error_status' => (int)ArrayHelper::getValue($data_set, 'error_status', 0), //差错状态:1差错已处理',
            'type' => ArrayHelper::getValue($data_set, 'type', 0), //账单类型：1正常，2差错',
            'bill_number' => ArrayHelper::getValue($data_set, 'bill_number', ''), //账单编号',
            'reason' => ArrayHelper::getValue($data_set, 'reason', ''), //原因',
            'create_time' => date("Y-m-d H:i:s", time()), //创建时间',
            'modify_time' => date("Y-m-d H:i:s", time()), //更新时间',
        ];
        $errors = $this->chkAttributes($bill_data);
        if ($errors) {
            return false;
        }
        $ret = $this->save();
        return $ret;
    }

    public function getClientId($client_id, $channel_id)
    {
        return self::find()->where(['client_id'=>$client_id, 'channel_id'=>$channel_id])->one();
    }
}