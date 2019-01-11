<?php

namespace app\models\news;

use Yii;
use app\commonapi\Logger;

/**
 * This is the model class for table "yi_user_bank_bill".
 *
 * @property integer $id
 * @property string $user_id
 * @property string $loan_id
 * @property string $credit_card
 * @property string $credit_url
 * @property string $credit_mofify_time
 * @property string $deposit_card
 * @property string $deposit_url
 * @property string $deposit_mofify_time
 * @property string $create_time
 * @property string $status
 */
class Bankbill extends \app\models\BaseModel
{
    public $last_modify_time;
    public $loan_detail_url;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_user_bank_bill';
    }

    
    public function getloan() {
        return $this->hasOne(User_loan::className(), ['loan_id' => 'loan_id']);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'loan_id', 'status'], 'required'],
            [['user_id', 'loan_id'], 'integer'],
            [['credit_mofify_time', 'deposit_mofify_time', 'create_time','last_modify_time'], 'safe'],
            [['credit_card', 'deposit_card'], 'string', 'max' => 64],
            [['credit_url', 'deposit_url', 'loan_detail_url'], 'string', 'max' => 128],
            [['status'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'loan_id' => 'Loan ID',
            'credit_card' => 'Credit Card',
            'credit_url' => 'Credit Url',
            'credit_mofify_time' => 'Credit Mofify Time',
            'deposit_card' => 'Deposit Card',
            'deposit_url' => 'Deposit Url',
            'deposit_mofify_time' => 'Deposit Mofify Time',
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
            'status' => 'Status',
        ];
    }

    /**
     * 通过用户user_id获取借款银行卡账单
     * @param $user_id
     * @return array
     */
    public function getBankBillForUserid($user_id)
    {
        if (empty($user_id)) return array();
        $bank_bill_info = self::find()->where(['user_id'=>$user_id])->one();
        if (empty($bank_bill_info)) {
            return array();
        }
        return $bank_bill_info;
    }

    /**
     * 通过借款laon_id获取借款银行卡账单
     * @param $loan_id
     * @return array
     */
    public function getBankBillForLoanId($loan_id)
    {
        if (empty($loan_id)) return array();
        $bank_bill_info = self::find()->where(['loan_id'=>$loan_id])->one();
        if (empty($bank_bill_info)) {
            return array();
        }
        return $bank_bill_info;
    }

    /**
     * 判断是否存是，存在更新，不存丰添加
     * @param $condition
     * @return bool
     */
    public function addList($condition)
    {
        if (!empty($condition['user_id'])){
            $bank_bill = (new self())->getBankBillForUserid($condition['user_id']);
            if (!empty($bank_bill)){
                return $bank_bill->updateBankbill($condition);
            }
        }
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $this->create_time = date('Y-m-d H:i:s');
        $this->last_modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }

    /**
     * 新增一条数据
     * @param $condition
     * @return bool
     */
    public function addData($condition)
    {
        if (empty($condition)) return false;
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $this->create_time = date('Y-m-d H:i:s');
        $this->last_modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }

    /**
     * 每次都插入一条
     * @param $user_id
     * @param $loan_id
     * @return bool
     */
    private function addBankData($user_id, $loan_id)
    {
        if (empty($user_id) || empty($loan_id)) return false;
        $bank_info =new User_bank();
        $data_set = array(
            'user_id'=>$user_id,
            'loan_id'=>$loan_id,
            'status'=>'INIT',
        );
        $time = date('Y-m-d H:i:s');
        //储蓄卡信息
        $bank_data = $bank_info->getDepositCardInfo($user_id);
        if (empty($bank_data)) return false;
        $data_set['deposit_card'] = empty($bank_data['card']) ?  '' : $bank_data['card'];
        $data_set['deposit_mofify_time'] = $time;
        //信用卡信息
        $bank_data = $bank_info->getCreditCardInfo($user_id);
        if (empty($bank_data)) return false;
        $data_set['credit_card'] = empty($bank_data['card']) ? '' : $bank_data['card'];
        $data_set['credit_mofify_time'] = $time;
        $result = $this->addData($data_set);
        Logger::errorLog(print_r($data_set, true), 'bankbill');
        return $result;
    }
    /**
     * 更新
     * @param $condition
     * @return bool
     */
    public function updateBankbill($condition)
    {
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $this->last_modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }

    /**
     * 检查修改借款银行卡账单
     * @param $user_id
     * @param $loan_id
     * @param int $business_type 1 储蓄卡, 2信用卡
     * @return bool
     */
    public function checkBankbill($user_id, $loan_id, $business_type = 1)
    {
        return $this->addBankData($user_id, $loan_id);

        if ($business_type != 2) return false; //暂时只有担保可用，以后增加信用借口可以去掉
        //$business_type = 2;
        //获取银行卡信息
        $bank_info =new User_bank();

        $data_set = array(
            'user_id'=>$user_id,
            'loan_id'=>$loan_id,
            'status'=>'INIT',
        );
        $cur_time = strtotime("-3 months"); //前三个月时间戳
        //获取借款银行卡账单信息
        $bank_bill_info = $this->getBankBillForUserid($user_id);
        if (!empty($bank_bill_info)){
            //储蓄卡信息
            if ($business_type == 1){
                $deposit_mofify_time = strtotime($bank_bill_info['deposit_mofify_time']);
                //判断储蓄卡获取时间大于三个月并且储蓄卡账单地址不为空，不修改
                if (($deposit_mofify_time > $cur_time) && !empty($bank_bill_info['deposit_url'])){
                    return false;
                }
            }
            //信用卡
            if ($business_type == 2){
                $credit_mofify_time = strtotime($bank_bill_info['credit_mofify_time']);
                //判断储蓄卡获取时间大于三个月并且储蓄卡账单地址不为空，不修改
                if (($credit_mofify_time > $cur_time) && !empty($bank_bill_info['credit_url'])){
                    return false;
                }
            }
        }
        $result = false;
        //获取储蓄卡信息
        if ($business_type == 1){
            $bank_data = $bank_info->getDepositCardInfo($user_id);
            if (empty($bank_data)) return false;
            $data_set['deposit_card'] = empty($bank_data['card']) ?  '' : $bank_data['card'];
            $data_set['deposit_mofify_time'] = date('Y-m-d H:i:s');
            $result = self::addList($data_set);
        }
        //获取信用卡信息
        if ($business_type == 2) {
            //储蓄卡信息
            $bank_data = $bank_info->getDepositCardInfo($user_id);
            if (empty($bank_data)) return false;
            $data_set['deposit_card'] = empty($bank_data['card']) ?  '' : $bank_data['card'];
            $data_set['deposit_mofify_time'] = date('Y-m-d H:i:s');
            //信用卡信息
            $bank_data = $bank_info->getCreditCardInfo($user_id);
            //var_dump($bank_data);exit;
            if (empty($bank_data)) return false;
            $data_set['credit_card'] = empty($bank_data['card']) ? '' : $bank_data['card'];
            $data_set['credit_mofify_time'] = date('Y-m-d H:i:s');
            $result = self::addList($data_set);
            Logger::errorLog(print_r($data_set, true), 'bankbill');
        }
        return $result;
    }
}