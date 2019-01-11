<?php
namespace app\modules\settlement\controllers;

use app\common\Logger;
use yii\web\Controller;
use app\models\open\Rbremit;
use app\models\open\CjRemit;
use app\models\open\BfRemit;
use app\models\yyy\YiUserRemitList;
use app\models\yyy\YiUserLoan;
use app\models\yyy\YiLoanRepay;
use app\models\Channel;
use app\models\SettleBill;
use app\models\SettleBillOriginal;
use yii\helpers\ArrayHelper;
/**
 * 清结算入口
 */
class SettleController extends Controller
{
    public function actionIndex(){
        //查询对账单出款成功订单
        $origin_data = (new YiLoanRepay)->getRepayData();
        //var_dump($origin_data);die;
        $settle_data = [];
        $settle_datas = [];
        if(!empty($origin_data)){
            foreach($origin_data as $key=>$val){
                $loan_id = $val['loan_id'];
                 //查询一亿元借款表
                $loan_data = (new YiUserLoan)->getLoanByLoanId($loan_id);
                $loan_id = $loan_data['parent_loan_id'];//出款表的loan_id
                //查询一亿元出款表
                $remit_data = (new YiUserRemitList)->getRemitData($loan_id);
                
                //组合数据
                $settle_data['req_id'] = $client_id;//商户订单号
                $settle_data['remit_channel_id'] = $remit_data['payment_channel'];//出款通道  
                $pay_channel_id = $this->getRepaymentChannel($val['platform']);            
                $settle_data['pay_channel_id'] = $pay_channel_id;//还款通道              
                $settle_data['remit_channel'] = $this->getChannel($remit_data['payment_channel']);//出款通道名称
                $settle_data['pay_channel'] = $this->getChannel($pay_channel_id);//还款通道名称
                $settle_data['remit_type'] = $this->getRemitType($remit_data['type']);//出款类型
                $settle_data['loan_id'] = $loan_data['loan_id'];//借款id
                $settle_data['user_id'] = $loan_data['user_id'];//用户id
                $settle_data['loan_time'] = $loan_data['create_time'];//借款时间
                $settle_data['loan_days'] = $loan_data['days'];//借款周期
                $settle_data['loan_money'] = $loan_data['amount'];//借款本金 说是借款金额
                $settle_data['withdraw_fee'] = $loan_data['withdraw_fee'];//前置服务费
                $settle_data['free_amount'] = $loan_data['coupon_amount'];//免息卷金额
                $settle_data['interest_fee'] = $loan_data['interest_fee'];//利息
                $settle_data['fund'] = $this->getFund($remit_data['fund']);//资金方  
                $settle_data['end_date'] = $loan_data['end_date'];//到期时间
                $settle_data['repay_status'] = $this->getRepayStatus($loan_data);//还款状态
                $settle_data['all_money'] = $this->getAllMoney($loan_data);//需还款总额
                $settle_data['chase_amount'] = $this->getChaseAmount($loan_data['chase_amount'],$settle_data['all_money']);//滞纳金收益 逾期费用
                $settle_data['settle_status'] = $this->getSettleStatus($loan_data);//结算状态
                $settle_data['is_yq'] = $this->getIsyq($loan_data['chase_amount']);//是否逾期

                $settle_data['repay_time'] = $val['repay_time'];//还款时间 
                $settle_data['yq_days'] = $this->getYqdays($val['repay_time'],$loan_data['end_date']);//逾期天数 
                $settle_data['repay_money'] = $val['actual_money'];//回款金额 
                $settle_data['repay_actual_money'] = $this->getRepayActualMoney($val['actual_money'],$settle_data['loan_money']);//回款本金
                $settle_data['is_badloan'] = $this->getBadloan($loan_data,$val['repay_time']);//是否坏账
                $settle_data['badloan_money'] = $this->getBadloanMoney($settle_data['is_badloan'],$settle_data['all_money']);//坏账金额
                $settle_data['badloan_actualmoney'] = empty($settle_data['is_badloan'])?0:$loan_data['amount'];//坏账本金
                $settle_data['badloan_fee'] = empty($settle_data['is_badloan'])?0:$loan_data['interest_fee'];//坏账利息
                $settle_data['badloan_back'] = $this->getBadloanBack($settle_data['is_badloan'],$loan_data);//坏账收回
                

                if(!empty($settle_data)){
                    $settleBill = new SettleBill();
                    $result = $settleBill->createData($settle_data);
                    if(!$result){
                        Logger::dayLog('settle/settlebill','createData',$settleBill->errinfo,$settle_data);
                        continue;
                    }
                    //保存完更新settlebilloriginal状态
                    // $result = (new SettleBillOriginal)->updateByClientId($settle_data['req_id']);
                    // if(!$result){
                    //     Logger::dayLog('settle/settlebilloriginal','updateByClientId','更新状态失败',$client_id,$result);
                    //     continue;
                    // }
                }
                

                
            }
        }
        
    }
    


        /**
     * Undocumented function
     * 滞纳金收益
     * @param [type] $amount
     * @param [type] $all_money
     * @return void
     */
    private function getChaseAmount($amount,$all_money){
        
        if(!empty($amount)){
            return $amount-$all_money;
        }else{
            return 0;
        }
    }
    /**
     * Undocumented function
     * 多条还款记录 是否逾期
     * @param [type] $end_date到期时间
     * @param [type] $repay_time还款时间
     * @return void
     */
    private function getMuliIsyq($end_date,$repay_time){
        $is_yq = 0;
        if($repay_time<$end_date){
            $is_yq = 0;
        }else{
            $is_yq = 1;
        }
        return $is_yq;
    }
    /**
     * Undocumented function
     * 获得多条还款记录 滞纳金 最后一条显示
     * @param [type] $key
     * @param [type] $count
     * @param [type] $amount
     * @param [type] $all_money
     * @return void
     */
    private function getMuliChase($key,$count,$amount,$all_money){
        $chase_amount = 0;
        if($key==$count && !empty($amount)){
            $chase_amount = $amount-$all_money;
        }
        return $chase_amount;
    }
    /**
     * Undocumented function
     * 获得多条还款记录 利息 最后一条显示
     * @param [type] $key
     * @param [type] $count
     * @param [type] $fee
     * @return void
     */
    private function getMuliFee($key,$count,$fee){
        $res_fee = 0;
        if($key==$count){
            $res_fee = $fee;
        }
        return $res_fee;
    }
    /**
     * Undocumented function
     * 多条还款记录 还款状态
     * @param [type] $end_date到期时间
     * @param [type] $repay_time还款时间
     * @return void
     */
    private function getMuliRepayStatus($end_date,$repay_time){
        $repay_status = '';
        if($repay_time<$end_date){
            $repay_status = '部分还款';
        }else{
            $repay_status = '逾期还款';
        }
        return $repay_status;
    }
    /**
     * Undocumented function
     * 坏账收回
     * @param [type] $is_badloan
     * @param [type] $loan_data
     * @return void
     */
    private function getBadloanBack($is_badloan,$loan_data){
        if(empty($is_badloan)) return '';
        if($loan_data['status']==8){
            return '是';
        }else{
            return '否';
        }
    }
    /**
     * Undocumented function
     * 坏账金额
     * @param [type] $is_badloan
     * @param [type] $repay_money
     * @param [type] $all_money
     * @return void
     */
    private function getBadloanMoney($is_badloan,$all_money){
        $badloan_money = 0;
        if(!empty($is_badloan)){
            $badloan_money = $all_money;
        }
        return $badloan_money;
    }
    /**
     * Undocumented function
     * 是否坏账
     * @param [type] $loan_data
     * @param [type] $repay_time 还款日期 未还款为当日
     * @return void
     */
    private function getBadloan($loan_data,$repay_time){
        $chase_amount = $loan_data['chase_amount'];//逾期费用
        $end_date = $loan_data['end_date'];//到期时间
        if(empty($chase_amount)){
            return 0;
        }else{
            $yq_days = ceil((strtotime(date('Y-m-d',strtotime($repay_time)))-strtotime($end_date))/86400)+1;
            if($yq_days>90){
                return 1;
            }else{
                return 0;
            }
        }

    }
    /**
     * Undocumented function
     * 是否逾期
     * @param [type] $chase_amount
     * @return void
     */
    private function getIsyq($chase_amount){
        return empty($chase_amount)?0:1;
    }
    /**
     * Undocumented function
     * 获得结算状态
     * @param [type] $loan_data
     * @return void
     */
    private function getSettleStatus($loan_data){
        $status = $loan_data['status'];//状态
        $settle_status = '';
        switch($status){
            case 8:$settle_status='全部结清';break;
            case 13:$settle_data='部分结清';break;
            case 12:$settle_data='坏账';break;
        }
        return $settle_status;
    }
    /**
     * Undocumented function
     * 获得总金额
     * @param [type] $loan_data
     * @return void
     */
    private function getAllMoney($loan_data){
        //借款金额+利息
        $all_money = $loan_data['amount']+$loan_data['interest_fee'];//+$loan_data['chase_amount'];
        return $all_money;
    }
    /**
     * Undocumented function
     * 获得回款本金
     * @param [type] $repay_money 实际还款金额
     * @param [type] $loan_money 实际出库金额
     * @return void
     */
    private function getRepayActualMoney($repay_money,$loan_money){
        if($repay_money>=$loan_money){
            return $loan_money;
        }else{
            return $repay_money;
        }
    }
    /**
     * Undocumented function
     * 获得逾期天数
     * @param [type] $repay_time
     * @param [type] $end_date
     * @return void
     */
    private function getYqdays($repay_time,$end_date){
        $yq_days = 0;
        if($repay_time>=$end_date){
            $yq_days = ceil((strtotime(date('Y-m-d',strtotime($repay_time)))-strtotime($end_date))/86400)+1;
        }
        return $yq_days;
    }
    
    /**
     * Undocumented function
     * 获得还款状态
     * @param [type] $loan_data
     * @return void
     */
    private function getRepayStatus($loan_data){
        $chase_amount = $loan_data['chase_amount'];//逾期费用
        $status = $loan_data['status'];//状态
        $repay_status = '';
        if($status==8){
            if(empty($chase_amount)){
                $repay_status = '正常还款';
            }else{
                $repay_status = '逾期还款';
            }
        }else if($status==12){
            $repay_status = '未还款';
        }else if($status==13){
            $repay_status = '部分还款';
        }
        return $repay_status;
    }
    /**
     * Undocumented function
     * 获取通道名称
     * @param [type] $channel_id
     * @return void
     */
    private function getChannel($channel_id){
        if(in_array($channel_id,$this->getThirdChannelId()) ){
            $channel_name = $this->getThirdChannel($channel_id);
        }else{
            $channel_data = (new  Channel)->getChannel(array('id'=>$channel_id));
            $channel_name = $channel_data[$channel_id]['company_name'];
        }     
        return $channel_name;
    }
    /**
     * Undocumented function
     * 获取借款类型
     * @param [type] $remit_type
     * @return void
     */
    private function getRemitType($remit_type){
        $res = '';
        switch($remit_type){
            case 1:$res='一亿元借款';break;
            case 2:$res='担保卡借款';break;
            case 3:$res='收益提钱';break;
        }
        return $res;
    }
    /**
     * Undocumented function
     * 获取借款本金 $is_calculation 1为前置0为后置
     * @param [type] $remit_data
     * @return void
     */
    private function getLoanMoney($remit_data){
        if(empty($remit_data)) return 0;
        $is_calculation = $remit_data['is_calculation'];
        $amount = $remit_data['amount'];
        $withdraw_fee = $remit_data['withdraw_fee'];
        $loan_money = 0;
        if($is_calculation==1){
            $loan_money = $amount-$withdraw_fee;
        }else if($is_calculation==0){
            $loan_money = $amount;
        }
        return $loan_money;
    }
    /**
     * Undocumented function
     * 获得资金方
     * @param [type] $fund
     * @return void
     */
    private function getFund($fund){
        $fund_list =  [
            '1'=>'花生米富',
            '2'=>'玖富',
            '3'=>'连交所',
            '4'=>'金联储',
            '5'=>'小诺',
            '6'=>'微神马',
            '10'=>'存管'
        ];
        $fund_name = isset($fund_list[$fund])?$fund_list[$fund]:'未知';
        return $fund_name;
    }
    /**
     * Undocumented function
     * 获得出款数据
     * @param [type] $type
     * @param [type] $client_id
     * @return void
     */
    private function getRemitData($type,$client_id){
        if(empty($type)) return null;
        $model = null;
        switch($type){
            case 1:$model = new Rbremit;break;
            case 2:$model = new BfRemit;break;
            case 3:$model = new CjRemit;break;
        }
        if(empty($model)) return null;
        $remit_data = $model->getRemitByClientId($client_id);
        return $remit_data;
    }
    /**
     * Undocumented function
     * 还款通道
     * @return void
     */
    private  function getRepaymentChannel($platform) {
        $channel_list =  [
            3 => 101, //易宝投资通
            2 => 102, //易宝一键支付
            6 => 104, //连连支付（一亿元）
            9 => 107, //宝付认证支付（一亿元）
            10 => 108, //连连认证支付（花生米富）
            11 => 109, //易宝代扣
            12 => 110, //融宝快捷（一亿元）
            13 => 112, //融宝快捷（米富）
            14 => 113, //宝付（一亿元）
            15 => 114, //宝付（米富）
            16 => 128, //融宝(逾期)
            17 => 123, //宝付（逾期）
            18 => 117,//畅捷
            19 => 131, //畅捷快捷
            20 => 105, //融宝快捷（花生米富）
            21 => 106, //宝付代扣
        ];
       $channel_id = isset($channel_list[$platform])?$channel_list[$platform]:$platform;
       return $channel_id;
    }
    /**
     * Undocumented function
     * 第三方支付通道id
     * @return void
     */
    private function getThirdChannelId(){
        return [1,4,5,7,8];
    }
    /**
     * Undocumented function
     * 获取第三方支付名称
     * @param [type] $platform
     * @return void
     */
    private function getThirdChannel($platform){
        $third_list = [
            '1' => '线下',
            '4'=>'微信',
            '5'=>'支付宝',
            '7'=>'微信逾期',
            '8'=>'支付宝逾期'
        ];
        $channel_name = isset($third_list[$platform])?$third_list[$platform]:$platform;
        return $channel_name;
    }


}
