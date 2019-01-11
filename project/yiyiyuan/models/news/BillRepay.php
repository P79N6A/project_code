<?php

namespace app\models\news;

use Yii;
use app\models\news\Loan_repay;

/**
 * This is the model class for table "yi_bill_repay".
 *
 * @property integer $id
 * @property integer $repay_id
 * @property integer $bank_id
 * @property integer $user_id
 * @property integer $loan_id
 * @property integer $bill_id
 * @property integer $status
 * @property string $actual_money
 * @property string $paybill
 * @property integer $platform
 * @property integer $source
 * @property string $repay_time
 * @property string $createtime
 * @property string $last_modify_time
 * @property integer $version
 */
class BillRepay extends \app\models\BaseModel
{
    CONST STATUS_INIT = 0;//初始
    CONST STATUS_REPAY = 2;//生成还款记录repay_id后的状态
    CONST STATUS_REPAYING = 3;//点击支付后状态
    CONST STATUS_SUCCESS = 6;
    CONST STATUS_FAIL = 11;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_bill_repay';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['repay_id', 'bank_id', 'user_id', 'loan_id', 'bill_id', 'platform', 'source', 'repay_time', 'createtime', 'last_modify_time'], 'required'],
            [['repay_id', 'bank_id', 'user_id', 'loan_id', 'bill_id', 'status', 'platform', 'source', 'version'], 'integer'],
            [['actual_money'], 'number'],
            [['repay_time', 'createtime', 'last_modify_time'], 'safe'],
            [['paybill'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'repay_id' => 'Repay ID',
            'bank_id' => 'Bank ID',
            'user_id' => 'User ID',
            'loan_id' => 'Loan ID',
            'bill_id' => 'Bill ID',
            'status' => 'Status',
            'actual_money' => 'Actual Money',
            'paybill' => 'Paybill',
            'platform' => 'Platform',
            'source' => 'Source',
            'repay_time' => 'Repay Time',
            'createtime' => 'Createtime',
            'last_modify_time' => 'Last Modify Time',
            'version' => 'Version',
        ];
    }
    public function toRepay($repay_id){
        try{
            $this->status = static::STATUS_REPAY;   
            $this->repay_id = $repay_id;
            $this->last_modify_time = date('Y-m-d H:i:s');
            return $this->save();
        }catch(\Exception $e){
            return false;
        }
    }
    public function lockToRepaying($ids) {
        $attributes = [
            'status' => static::STATUS_REPAYING,
            'last_modify_time' => date("Y-m-d H:i:s"),
        ];
        $condition = [
            'id' => $ids,
            'status' => static::STATUS_REPAY,
        ];
        $update_num = self::updateAll($attributes, $condition);
        return $update_num;
    }
    public function toSuccess($ids,$loan_repay) {
        $times           = date('Y-m-d H:i:s');
        $attributes = [
            'status' => static::STATUS_SUCCESS,
            'platform'     => $loan_repay->platform,
            'actual_money' => $loan_repay->actual_money,//接受金额精度计算
            'paybill'      => $loan_repay->paybill,
            'source'       =>$loan_repay->source,
            'repay_time'   => $times,
            'last_modify_time' => $times,
        ];
        $condition = [
            'id' => $ids,
            'status' => [static::STATUS_REPAY,static::STATUS_REPAYING]
        ];
        $update_num = self::updateAll($attributes, $condition);
        return $update_num;
    }
    public function toFail($ids) {
        $attributes = [
            'status' => static::STATUS_FAIL,
            'last_modify_time' => date("Y-m-d H:i:s"),
        ];
        $condition = [
            'id' => $ids,
            'status' =>[ static::STATUS_REPAY,static::STATUS_REPAYING],
        ];
        $update_num = self::updateAll($attributes, $condition);
        return $update_num;
    }
    public function getStatusRepaybill($repay_id,$status){
        $where = [
            'repay_id'  =>$repay_id,
            'status'    =>$status,
        ];
        $data = self::find()->where($where)->all();
        return $data;
    }
    public function getBillRepayModifyTime($loan_id){
        $where = [
            'AND',
            ['loan_id'  => $loan_id],
            ['in','status',[static::STATUS_INIT,static::STATUS_REPAY,static::STATUS_FAIL]],
            ['>','last_modify_time',date('Y-m-d H:i:s',strtotime('-2 minute'))]
        ];
        $data = self::find()->where($where)->all();
        return $data;
    }
    public function toSyncMoney($actual_money){
        try{
            $this->actual_money = $actual_money;   
            return $this->save();      
        }catch(\Exception $e){
            return false;
        }
    }


}
