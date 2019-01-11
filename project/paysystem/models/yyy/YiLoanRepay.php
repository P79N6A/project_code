<?php

namespace app\models\yyy;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;
/**
 * This is the model class for table "yi_loan_repay".
 *
 * @property integer $id
 * @property string $repay_id
 * @property integer $user_id
 * @property integer $loan_id
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
class YiLoanRepay extends \app\models\yyy\YyyBase 
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
    //查询还款成功的记录
    public function getLoanRepay($loan_id){
        $data = static::find()->where(array('loan_id'=>$loan_id,'status'=>1))->orderBy('id asc')->all();
        return $data;
    }
    public function getLoanRepayFix($loan_id){
        $data = static::find()->where(array('loan_id'=>$loan_id,'status'=>1))->one();
        return $data;
    }

    public function getRepayData($start_time ='0000-00-00',$end_time ='0000-00-00'){//一亿元还款成功的单子
        //查询通知的数据
        $where = [
            'AND',
            ['>=', 'createtime', $start_time],
            ['<', 'createtime', $end_time],
            ['status' => 1],
        ];
        // 按查询时间排序
        $data = static::find()->where($where)->all();
        return $data;

    }

    //总的还款记录
    public function getRepayCount($start_time='0000-00-00',$end_time = '0000-00-00'){
        $where = [
            'AND',
            ['>=', 'createtime', $start_time],
            ['<', 'createtime', $end_time],
            ['status' => 1],
        ];
        return self::find()
                ->select("loan_id")
                ->where($where)
                ->count();
    }

}