<?php

namespace app\modules\balance\models;

use Yii;


class MatchLoanorder extends MatchingBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cm_loan_order';
    }

    
    /**
     * Undocumented function
     * 获取存管总数
     * @param [type] $bill_date
     * @return void
     */
    public function getCgRechargeCount($bill_date){
        if(empty($bill_date)) return false;
        $where = [
            'txDate'    =>$bill_date,
            'retCode'   =>'00000000'
        ];
        $data = self::find()->where($where)->count();
        return $data;
    }
    /**
     * Undocumented function
     * 获取存管充值记录
     * @return void
     */
    public function getCgRechargeData($pages,$bill_date){
        if(empty($bill_date)) return false;
        $where = [
            'txDate'    =>$bill_date,
            'retCode'   =>'00000000'
        ];
        $data = self::find()->select('id,txDate,accountId,acqRes as order_no,idNo,name,mobile,txAmount')->where($where)->offset($pages['offset'])->limit($pages['limit'])->orderBy('id asc')->asArray()->all();
        return $data;
    }
}