<?php

namespace app\modules\service\common;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\antifraud\Address;
use app\models\yyy\User;
use app\models\yyy\UserLoan;
use app\models\credit\YxUserCredit;
use app\models\tidb\TiAddressList;
use app\common\Logger;
use app\commands\configdata\UserLimit;
/**
 * 一亿元数据源
 */
class UserLoanApi 
{
    # API default data
    private function defData(){
        $def_data = [
            'user_come_from' => null,
            'loan_total' => null,
            'mth3_dlq_num' => null,
            'mth3_dlq7_num' => null,
            'mth3_wst_sys' => null,
            'mth6_dlq_ratio' => null,
            'success_num' => null,
            'type' => null,
            'user_id' => null,
            'wst_dlq_sts' => null,
            'realadl_dlq14_ratio' => null,
            'realadl_tot_dlq14_num' => null,
            'realadl_tot_freject_num' => null,
            'realadl_tot_reject_num' => null,
            'realadl_tot_sreject_num' => null,
            'realadl_wst_dlq_sts' => null,
            'reg_time' => null,
            'com_c_user' => null,
            'fd_test56' => null,
            'last_success_loan_days' => null,
        ];
        return $def_data;
    }

	//数据源
    public function apiOpen($data)
    {
        //获取用户的基本数据
        $user_info = $this->getUserInfo($data);
        if (empty($user_info)) {
            return $this->defData();
        }
        // 用户借款详情
        $user_loan_info = $this->getHistoryLoanData($user_info);
        // 成功次数
        $user_loan_info += $this->getUserSucnumAndType($user_info);
        // 借款申请次数
        $user_loan_info += $this->getLoanTotal($user_info);
        // 最近一次借款成功次数
        $user_loan_info += $this->getLastSuccessLoanData($user_info);
        // user limit
        $user_loan_info += $this->getUserLimit($user_info);
        // get com_c_user
        $user_loan_info += $this->getContactUser($data);
        // realadl history  data
        $user_loan_info += $this->getRealadlData($user_info);
        // user loan all info
        $all_info = array_merge($user_info,$user_loan_info);
        return $all_info;
    }
    private function getRealadlData($user_data){
        # def data
        $def_data = [
                'realadl_dlq14_ratio' => null,
                'realadl_tot_dlq14_num' => null,
                'realadl_tot_freject_num' => null,
                'realadl_tot_reject_num' => null,
                'realadl_tot_sreject_num' => null,
                'realadl_wst_dlq_sts' => null,
                'history_bad_status' => null,
                'loan_all' => null,
                'user_total' => null,
            ];
        $mobile = ArrayHelper::getValue($user_data,'mobile','');
        if (empty($mobile)) {
            return $def_data;
        }
        // get address
        $address_list = (new TiAddressList)->getAddressByUserPhone($mobile);
        if (empty($address_list)) {
            return $def_data;
        }

        // 手机号正则验证
        $valid_mobiles = $this->chkMobile($address_list);
        if (empty($valid_mobiles)) {
            return $def_data;
        }
        // get userids
        $user_ids = (new User)->getUserIdByMobiles($valid_mobiles);
        if (empty($user_ids)) {
            return $def_data;
        }
        // get realadl data
        $oUserLoan = new UserLoan();
        $realadl_data = $oUserLoan->getHistroyBadStatus($user_ids);
        if (empty($realadl_data)) {
            return $def_data;
        }
        // get loan all
        $realadl_data['loan_all'] = $oUserLoan->getAllLoanByUids($user_ids);
        // get user_total
        $realadl_data['user_total'] = count($user_ids);
        return $realadl_data;
    }

    private function chkMobile($mobile_list){
        $valid_mobiles = [];
        foreach ($mobile_list as $mobile) {
            $phone = $this->checkPhone($mobile);
            if ($phone) {
                $valid_mobiles[] = $phone;
            }
        }
        return $valid_mobiles;
    }

    // 验证手机号
    private function checkPhone($number)
    {
        $isMatched = preg_match('/^(\+?86-?)?1[2-9][0-9]\d{8}$/', $number, $matche_phone);
        if ($isMatched > 0) {
            if (substr($number,0,3) == '+86') {
                $number = trim(substr($number,3));
            }
            if (substr($number,0,2) == '86') {
                $number = trim(substr($number,2));
            }
            return (string)trim($number,'-');
        }
        return '';
    }

    /**
     * [getContactUser 判断常用联系人是否为一亿元用户]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    private function getContactUser($data){
        $mobile = ArrayHelper::getValue($data,'contact','');
        $user = new User();
        $count = count($user->getUser(['mobile' => $mobile]));
        return ['com_c_user' => $count];
    }
    /**
     * [getLastSuccessLoanData 最近一次成功数据]
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function getLastSuccessLoanData($user_id){
        $oUserLoan = new UserLoan();
        $last_success_loan = $oUserLoan->getSuLoan($user_id);
        $ret_data = [
            'last_success_loan_days' => (int)ArrayHelper::getValue($last_success_loan,'days',0),
        ];
        return $ret_data;
    }
    
    /**
     * [getUserInfo 获取基本用户数据]
     * @param  [array] $data [description]
     * @return [array]       [description]
     */
    public function getUserInfo($data)  
    {
    	$mobile = ArrayHelper::getValue($data, 'mobile');
    	$user = new User();
    	$where = ['mobile'=>$mobile];
        $user_select = 'user_id,come_from,mobile,create_time';
        $user_info = $user->getInfo($where, $user_select);
        if (empty($user_info)) {
        	return [];
        }
        $ret_data = [
            'user_id' => ArrayHelper::getValue($user_info,'user_id'),
            'user_come_from' => ArrayHelper::getValue($user_info,'come_from'),
            'reg_time' => ArrayHelper::getValue($user_info,'create_time'),
            'mobile' => ArrayHelper::getValue($user_info,'mobile'),
        ];
        return $ret_data;
    }

    //判断用户类型 type=1，为初贷；=2为复贷；
    public function getUserSucnumAndType($data)
    {
        $user_id = ArrayHelper::getValue($data,'user_id','');
        $user_loan = new UserLoan();
        $type = 1;
        $where = ['user_id'=>$user_id,'status'=>8,'number' => 0,'business_type'=>[1,4,10]];
        $success_num = $user_loan->find()->where($where)->count();
        if($success_num > 0){
            $type = 2;
        }
        $ret_data = [
            'success_num' => (int)$success_num,
            'type' => $type
        ];
        return $ret_data;
    }
    //临时fd_test56 （0未在名单中，1在名单中）start
    private function getUserLimit($data){
        $oUserLimit = new UserLimit();
        $user_id = ArrayHelper::getValue($data, 'user_id', '');
        $ret_data['fd_test56'] = $oUserLimit->searchUser($user_id);
        return $ret_data;
    }
        
    //获取用户申请借款总次数；
    public function getLoanTotal($data)
    {
        $user_id = ArrayHelper::getValue($data,'user_id','');
        $user_loan = new UserLoan();
        $type = 1;
        $where = ['user_id'=>$user_id,'number'=>0 ,'business_type'=>[1,4,10]];
        $ret_data['loan_total'] = $user_loan->find()->where($where)->count();
        return $ret_data;
    }

    public function getHistoryLoanData($data)
    {
        $user_id = ArrayHelper::getValue($data, 'user_id');
        $loanData = [
            'wst_dlq_sts'=>0,//客户历史最坏逾期天数
            'mth3_dlq_num'=>'',//客户过去3个月逾期次数（按照贷款记） 
            'mth3_wst_sys'=>'',// 客户过去3个月最坏逾期天数
            'mth3_dlq7_num'=>'',//客户过去3个月逾期超过7天的贷款数 
            'mth6_dlq_ratio'=>'',//客户过去6个月有过预期的贷款比例（分母为过去6个月批核的总贷款数）
        ];
        if(!$user_id) {
            return $loanData;
        }
        
        
        $loan = new UserLoan;
        $loanAll = $loan->getAllLoan(['user_id'=>$user_id,'status'=>[8,9,11,12,13],'business_type'=>[1,4]]);
        $wst_dlq_sts = [];
        if( $loanAll ) {
            $loanData = [
                'wst_dlq_sts'=>0,//客户历史最坏逾期天数
                'mth3_dlq_num'=>0,//客户过去3个月逾期次数（按照贷款记） 
                'mth3_wst_sys'=>0,// 客户过去3个月最坏逾期天数
                'mth3_dlq7_num'=>0,//客户过去3个月逾期超过7天的贷款数 
                'mth6_dlq_ratio'=>0,//客户过去6个月有过预期的贷款比例（分母为过去6个月批核的总贷款数）
            ];
            $nowtime = date('Y-m-d H:i:s');
            $mth6LoanCount = 0;
            $totalCount = 0;
            $mth3Count = 0;
            foreach ($loanAll as $key => $value) {
                $repay_time = substr($value['repay_time'], 0,10);
                $end_date = substr($value['end_date'], 0,10);
                //最长逾期时间
                $due_day = (int)((strtotime($repay_time)-strtotime($end_date))/(60*60*24));
                // if( $due_day > $loanData['wst_dlq_sts'] ){
                $wst_dlq_sts[] = $due_day;
                // }
                //客户过去3个月逾期次数（按照贷款记）
                $create_time_old = substr($value['create_time'], 0,10);
                $loanTime = (strtotime($nowtime)-strtotime($create_time_old))/(60*60*24);
                if( $loanTime < 90 && $due_day > 0 ){
                    $loanData['mth3_dlq_num'] += 1;
                }
                //客户过去3个月最坏逾期天数
                if( $loanTime < 90 ){
                    if( $due_day > $loanData['mth3_wst_sys'] ){
                        $loanData['mth3_wst_sys'] = $due_day;
                    }
                }else{
                    $mth3Count++;
                }
                //客户过去3个月逾期超过7天的贷款数
                if( $loanTime < 90 && $due_day >= 7 ){
                    $loanData['mth3_dlq7_num'] += 1;
                }
                //客户过去6个月有过预期的贷款数
                if( $loanTime < 180 && $due_day > 0 ){
                    $mth6LoanCount += 1;
                }
                $totalCount++;
            }
            //客户过去6个月有过预期的贷款比例（分母为过去6个月批核的总贷款数）
            if( $totalCount > 0 ){
                $loanData['mth6_dlq_ratio'] = floor(($mth6LoanCount / $totalCount)*100)/100;
            }
            //近三个月无借款
            $three_time = date("Y-m-d 00:00:00",strtotime('-89 days'));
            $where = ['and',
                ['>=','create_time',$three_time],
                ['user_id'=>$user_id,'status'=>[8,9,11,12,13],'business_type'=>[1,4]]
            ];
            $three_loan = $loan->getAllLoan($where);
            $three_count = count($three_loan);
            if ($three_count == 0) {
                $loanData['mth3_dlq_num'] = '';
                $loanData['mth3_wst_sys'] = '';
                $loanData['mth3_dlq7_num'] = '';
            }
            //近六个月无借款
            $six_time = date("Y-m-d 00:00:00",strtotime('-179 days'));
            $six_where = ['and',
                ['>=','create_time',$six_time],
                ['user_id'=>$user_id,'status'=>[8,9,11,12,13],'business_type'=>[1,4]]
            ];
            $six_loan = $loan->getAllLoan($six_where);
            $six_count = count($six_loan);
            if( $six_count == 0 ) {
                $loanData['mth3_dlq_num'] = '';
                $loanData['mth3_wst_sys'] = '';
                $loanData['mth3_dlq7_num'] = '';
                $loanData['mth6_dlq_ratio'] = '';
            }
        }
        if (!empty($wst_dlq_sts)){
            rsort($wst_dlq_sts);
            $loanData['wst_dlq_sts'] = $wst_dlq_sts[0];
        }
        Logger::dayLog('antiInfo', $user_id, $loanData);
        return $loanData;
    }
}
