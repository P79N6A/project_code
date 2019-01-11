<?php

namespace app\modules\balance\models\yyy;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "yi_loan_repay".
 *
 * @property string $id
 * @property string $repay_id
 * @property string $user_id
 * @property string $loan_id
 * @property integer $bank_id
 * @property integer $platform
 * @property integer $source
 * @property string $pic_repay1
 * @property string $pic_repay2
 * @property string $pic_repay3
 * @property integer $status
 * @property string $money
 * @property string $actual_money
 * @property string $pay_key
 * @property string $code
 * @property string $paybill
 * @property string $last_modify_time
 * @property string $createtime
 * @property string $repay_time
 * @property string $repay_mark
 * @property integer $version
 */
class LoanRepay extends YyyBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_loan_repay';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['repay_id', 'user_id', 'loan_id', 'money', 'last_modify_time', 'createtime'], 'required'],
            [['user_id', 'loan_id', 'bank_id', 'platform', 'source', 'status', 'version'], 'integer'],
            [['money', 'actual_money'], 'number'],
            [['last_modify_time', 'createtime'], 'safe'],
            [['repay_id', 'pay_key', 'repay_time'], 'string', 'max' => 32],
            [['pic_repay1', 'pic_repay2', 'pic_repay3', 'repay_mark'], 'string', 'max' => 128],
            [['code'], 'string', 'max' => 6],
            [['paybill'], 'string', 'max' => 64]
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
            'user_id' => 'User ID',
            'loan_id' => 'Loan ID',
            'bank_id' => 'Bank ID',
            'platform' => 'Platform',
            'source' => 'Source',
            'pic_repay1' => 'Pic Repay1',
            'pic_repay2' => 'Pic Repay2',
            'pic_repay3' => 'Pic Repay3',
            'status' => 'Status',
            'money' => 'Money',
            'actual_money' => 'Actual Money',
            'pay_key' => 'Pay Key',
            'code' => 'Code',
            'paybill' => 'Paybill',
            'last_modify_time' => 'Last Modify Time',
            'createtime' => 'Createtime',
            'repay_time' => 'Repay Time',
            'repay_mark' => 'Repay Mark',
            'version' => 'Version',
        ];
    }

    /**
     * 通过loan_id获取还款记录
     * @param $loan_id
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    public function getDataByLoanid($loan_id)
    {
        if (empty($loan_id)){
            return false;
        }
        return self::find()->where(['loan_id' => $loan_id, 'status' => '1'])->all();
    }

    /*
     * 已偿还本金
     */
    public function repayPrincipal($condition)
    {
        if (empty($condition)){
            return 0;
        }
        $where_config = [
            'AND',
            ['>=', 'createtime', ArrayHelper::getValue($condition, 'start_time')],
            ['<=', 'createtime', ArrayHelper::getValue($condition, 'end_time')],
            ['=', 'status', 1]
        ];
        $total = self::find()->where($where_config)->sum('actual_money');
        return empty($total) ? 0 : $total;
    }



    /**---------------------------对账使用split-------------------------------------------------------
     * 通过loan_id获取还款记录
     * @param $repay_id
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    public function getOneByData($repay_id)
    {
        if (empty($repay_id)){
            return false;
        }
        return self::find()->where(['repay_id' => $repay_id, 'status' => '1'])->one();
    }

    /**
     *
     */
    public  function getAllRecord($loan_ids,$time){
        if (empty($loan_ids)){
            return false;
        }
        $result = self::find();
        $result->select([self::tableName().'.*',Repay_coupon_use::tableName().'.*'])
            ->leftJoin(Repay_coupon_use::tableName(),Repay_coupon_use::tableName().'.loan_id='.self::tableName().'.loan_id');
        $result->andWhere(['in', self::tableName().'.loan_id', $loan_ids]);
        $result->andWhere(['<',self::tableName().'.last_modify_time',$time]);
        $result->andWhere([self::tableName().'.status'=>1]);
        return $result->all();
    }

    /**
     * 根据借款loan_id 获取回款信息
     * @param $repay_id
     * @return array|bool|null|\yii\db\ActiveRecord
     */
    public function getLoanIdByData($loan_id= null)
    {
        if (empty($loan_id)){
            $query = LoanRepay::find();
            $query->select([
                LoanRepay::tableName().'.*',
//                Repay_coupon_use::tableName().'.*',
                'DATE_FORMAT('.LoanRepay::tableName().'.last_modify_time,\'%Y-%m-%d\') as lastData',
                Repay_coupon_use::tableName().'.coupon_amount',
            ]);
            $query->leftJoin(Repay_coupon_use::tableName(),LoanRepay::tableName().'.id='.Repay_coupon_use::tableName().'.repay_id');
            $query->where([ LoanRepay::tableName().'.status' => '1']);
//            $query->where([ LoanRepay::tableName().'.status' => '1',self::tableName().'.loan_id'=>'28882334']);
            $data = $query->asArray()->all();

            return $data;
            var_dump($data);die;
            return LoanRepay::find()
                ->select([
                    'cou.coupon_amount as mem',self::tableName().'.id',
                    'DATE_FORMAT('.self::tableName().'.last_modify_time,\'%Y-%m-%d\') as lastData',
                    Repay_coupon_use::tableName().'.coupon_amount',
                ])
                ->leftJoin(Repay_coupon_use::tableName(),self::tableName().'.id='.Repay_coupon_use::tableName().'.repay_id')
                ->where([ self::tableName().'.status' => '1',self::tableName().'.loan_id'=>'28882334'])
                ->asArray()->all();
        }
        return self::find()
            ->select(['*','DATE_FORMAT('.self::tableName().'.last_modify_time,\'%Y-%m-%d\') as lastData'])
            ->where(['loan_id' => $loan_id, 'status' => '1'])->asArray()->one();
    }




    /*-------------------线下还款拆分使用-------------------------*/
    /*
     * 根据时间区间来获取对应的线下还款
     * */
    public function getUnderRepay($start_time,$end_time){
        $query = LoanRepay::find();
        $query->select([
            LoanRepay::tableName().'.*',
        ]);
        $query->andWhere(['!=', LoanRepay::tableName().'.pic_repay1', 'NUll']);
        $query->andWhere(['>=',LoanRepay::tableName().'.last_modify_time',$start_time]);
        $query->andWhere(['<',LoanRepay::tableName().'.last_modify_time',$end_time]);
        $query->andWhere([LoanRepay::tableName().'.status'=>1]);
        $data = $query->asArray()->all();
        return $data;
//        var_dump($data);die;
    }
}