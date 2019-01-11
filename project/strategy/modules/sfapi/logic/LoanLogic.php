<?php
namespace app\modules\sfapi\logic;

use app\common\Logger;
use app\models\antifraud\Address;
use app\models\antifraud\Black;
use app\models\antifraud\Contact;
use app\models\antifraud\Detail;
use app\models\antifraud\Report;
use app\models\antifraud\DetailOther;
use app\models\cloud\BlackIdcard;
use app\models\cloud\BlackPhone;
use app\models\cloud\MultiIdcard;
use app\models\cloud\MultiPhone;
use app\models\cloud\DcLoan;
use app\models\cloud\DcBaidurisk;
use app\models\Loan;
use app\models\Request;
use app\models\yyy\AntiFraud;
use app\models\yyy\LoanEvent;
use app\models\yyy\User;
use app\models\yyy\UserLoan;
use app\models\loan\SfLoan;
use app\modules\sfapi\common\scorecard\MhhScoreCard;
use Yii;
use yii\helpers\ArrayHelper;

class LoanLogic extends BaseLogic
{

    public function getLoanOneInfo($data)
    {
        //请求cloud获取同盾数据
        $api = new \app\modules\sfapi\common\BaseApi();
        $res = $api->queryCloud($data,'cloud/loan');
        if (!$res) {
            return $this->returnInfo(false, $api->info);
        }
        $rsp_data = $api->info; 
        //关联request表
        $res = (new request)->bindRequest($data,$rsp_data);
        $data = array_merge($data,$rsp_data);
        //标准化决策参数
        $loan_info = $this->normalData($data);
        //请求cloud百度LBS接口并获取数据
        $api = new \app\modules\sfapi\common\CloudApi();
        $baidulbs = $api->queryCloud($loan_info,'baidulbs');
        //获取决策数据
        $loan_info += $this->getMultiInfo($loan_info);
        
        $loan_info += $this->getBlackInfo($loan_info);
        
        $loan_info += $this->getBaiduInfo($loan_info);
        //获取高频数据
        $loan_info += $this->getMultiLoan($loan_info);
        $request_id = ArrayHelper::getValue($data, 'request_id');
        $loan_info['request_id_one'] = $request_id;
        //数据类型转换
        $loan_info = $this->transType($loan_info);
        return $this->returnInfo(true, $loan_info);
    }

    public function getLoanTwoInfo($data)
    {
        //请求运营商分析数据
        $api = new \app\modules\sfapi\common\BaseApi();
        $res = $api->queryAnti($data);
        if (!$res) {
            return $this->returnInfo(false, $api->info);
        }
        $anti_info = $api->info;
        //标准化决策参数
        $loan_info = $this->normalData($data);
        //获取运营商数据
        $loan_info += $this->getOperator($anti_info,$data['identity_id']);
        $request_id = ArrayHelper::getValue($data, 'request_id');
        $loan_info['request_id_two'] = $request_id;
        //数据类型转换
        $loan_info = $this->transType($loan_info);
        return $this->returnInfo(true, $loan_info);
    }

    public function transType($data)
    {
        foreach ($data as $k => $val) {
            if ($k != 'identity' && $k != 'mobile' && $k != 'loan_create_time' && $k != 'telephone' && $k != 'realname' && $k != 'source' && $k != 'loan_no' && $k != 'query_time' && $k != 'rsp_code' && $k != 'rsp_msg' && $k != 'report_night_percent' && $k != 'black_level') {
                $data[$k] = (int)$data[$k];
            }
        }
        return $data;
    }

    //标准化借款决策参数
    private function normalData($data)
    {
        $ret_info = [
            'user_id'=>isset($data['identity_id']) ? $data['identity_id'] : 0,
            'identity'=>isset($data['idcard']) ? $data['idcard'] : '',
            'mobile'=>isset($data['phone']) ? $data['phone'] : '',
            'realname'=>isset($data['name']) ? $data['name'] : '',
            'query_time'=>date('Y-m-d H:i:s'),
            'basic_id'=>isset($data['basic_id']) ? $data['basic_id'] : 0,
            'rsp_code'=>isset($data['rsp_code']) ? $data['rsp_code'] : '',
            'rsp_msg'=>isset($data['rsp_msg']) ? $data['rsp_msg'] : '',
            'prd_type'=>isset($data['aid']) ? $data['aid'] : 1,
            'aid'=>isset($data['aid']) ? $data['aid'] : 1,
            'telephone'=>isset($data['company_phone']) ? $data['company_phone'] : '',
            'come_from'=>isset($data['come_from']) ? $data['come_from'] : 0,
            'amount'=> isset($data['amount']) ? $data['amount'] : 0,
            'loan_create_time'=> isset($data['loan_time']) ? $data['loan_time'] : '',
            'loan_no'=> isset($data['loan_no']) ? $data['loan_no'] : '0',
            'loan_id'=> isset($data['loan_id']) ? $data['loan_id'] : 0,
            'business_type'=> isset($data['business_type']) ? $data['business_type'] : 0,
            'days'=> isset($data['loan_days']) ? $data['loan_days'] : 0,
            'source' => isset($data['source']) ? $data['source'] : '',
            'request_id'=>isset($data['request_id']) ? $data['request_id'] : '',
            'type'=>isset($data['type']) ? $data['type'] : 0,
            'from'=>isset($data['from']) ? $data['from'] : 0,
        ];
        return $ret_info;
    }

    /**
     * 获取高频借款
     * @param  str $identity_id 
     * @param  int $aid         
     * @return []
     */
    private function getMultiLoan($data)
    {
        $identity_id = ArrayHelper::getValue($data, 'user_id', 0);
        $aid = ArrayHelper::getValue($data, 'prd_type', 0);
        $start_time = date('Y-m-d');
        $oLoan = new DcLoan;
        $loan_num_1 = $oLoan -> getMultiLoan($identity_id, $aid, $start_time);

        $start_time = date('Y-m-d', strtotime('-6 days'));
        $loan_num_7 = $oLoan -> getMultiLoan($identity_id, $aid, $start_time);

        return [
            'one_more_loan_value' => $loan_num_1,
            'seven_more_loan_value' => $loan_num_7,
        ];
    }

    private function getMultiInfo($data)
    {
        $multiPhone = new MultiPhone();
        $ph_multi_select = 'mph_y,mph_fm,mph_other,mph_br,mph_fm_seven_d,mph_fm_one_m,mph_fm_three_m';
        $MultiInfo = $multiPhone->getPhMultiInfo($data['mobile'], $ph_multi_select);

        $multiIdcard = new MultiIdcard();
        $id_multi_select = 'mid_y,mid_fm,mid_other,mid_br,mid_fm_seven_d,mid_fm_one_m,mid_fm_three_m';
        $MultiInfo += $multiIdcard->getIdMultiInfo($data['identity'], $id_multi_select);
        return $MultiInfo;
    }

    private function getBlackInfo($data)
    {
        $blackIdcard = new BlackIdcard();
        $id_black_select = 'bid_fm_sx,bid_fm_court_sx,bid_fm_court_enforce,bid_fm_lost,bid_y,bid_other,bid_br';
        $BlackInfo = $blackIdcard->getIdBlackInfo($data['identity'], $id_black_select);

        $blackPhone = new BlackPhone();
        $ph_black_select = 'bph_fm_sx,bph_y,bph_other,bph_fm_small,bph_fm_fack,bph_br';
        $BlackInfo += $blackPhone->getPhBlackInfo($data['mobile'], $ph_black_select);
        foreach ($BlackInfo as $val) {
            if ($val != 0) {
                $BlackInfo['is_black'] = 1;
                break;
            }
        }
        return $BlackInfo;
    }

    public function getAntiInfo($data)
    {
        $loan_id = isset($data['loan_id']) ? $data['loan_id'] : 0;
        $user_id = isset($data['identity_id']) ? $data['identity_id'] : 0;
        // if (!SYSTEM_PROD) {
        //     $user_id = 6;
        //     $loan_id = 8;
        // }
        $aid = isset($data['aid']) ? $data['aid'] : 1;
        $otherData = [
            'wst_dlq_sts'=>0,//客户历史最坏逾期天数
            'mth3_dlq_num'=>'',//客户过去3个月逾期次数（按照贷款记） 
            'mth3_wst_sys'=>'',// 客户过去3个月最坏逾期天数
            'mth3_dlq7_num'=>'',//客户过去3个月逾期超过7天的贷款数 
            'mth6_dlq_ratio'=>'',//客户过去6个月有过预期的贷款比例（分母为过去6个月批核的总贷款数）
        ];
        if( !$loan_id || !$user_id)
            return $otherData;
        
        switch ($aid) {
            case '1':
                //一亿元
                $loan = new UserLoan;
                $loanInfo = $loan->getLoanInfo(['loan_id'=>$loan_id]);
                $loanAll = $loan->getAllLoan(['user_id'=>$user_id,'status'=>[8,9,11,12,13],'business_type'=>[1,4]]);
                break;
            case '8':
                //7-14项目
                $loan = new SfLoan;
                $loanInfo = $loan->getLoanInfo(['loan_id'=>$loan_id]);
                $loanAll = $loan->getAllLoan(['user_id'=>$user_id,'status'=>[8,9,11,12,13],'business_type'=>[1,4]]);
                break;
            case '9':
                //米花花项目
                $loan = new MhhScoreCard;
                $mhhLoanInfo = $loan->getScoreData($data);
                return $mhhLoanInfo;
                break;
            default:
                //一亿元
                $loan = new UserLoan;
                $loanInfo = $loan->getLoanInfo(['loan_id'=>$loan_id]);
                $loanAll = $loan->getAllLoan(['user_id'=>$user_id,'status'=>[8,9,11,12,13],'business_type'=>[1,4]]);
                break;
        }        
        if( $loanAll ) {
           $otherData = $this->antiInfo($loan,$loanAll,$loanInfo,$loan_id,$user_id,$aid);
        }
        Logger::dayLog('antiInfo', $loan_id, $user_id, $otherData);
        return $otherData;
    }

    private function antiInfo($loan,$loanAll,$loanInfo,$loan_id,$user_id,$aid)
    {
        $otherData = [
            'wst_dlq_sts'=>0,//客户历史最坏逾期天数
            'mth3_dlq_num'=>0,//客户过去3个月逾期次数（按照贷款记） 
            'mth3_wst_sys'=>0,// 客户过去3个月最坏逾期天数
            'mth3_dlq7_num'=>0,//客户过去3个月逾期超过7天的贷款数 
            'mth6_dlq_ratio'=>0,//客户过去6个月有过预期的贷款比例（分母为过去6个月批核的总贷款数）
        ];
        $create_time_now = substr($loanInfo['create_time'], 0,10);
        $mth6LoanCount = 0;
        $totalCount = 0;
        $mth3Count = 0;
        foreach ($loanAll as $key => $value) {
            if( $value->loan_id < $loan_id ){
                $repay_time = substr($value['repay_time'], 0,10);
                $end_date = substr($value['end_date'], 0,10);
                //最长逾期时间
                $due_day = (int)((strtotime($repay_time)-strtotime($end_date))/(60*60*24));
                if( $due_day > $otherData['wst_dlq_sts'] ){
                    $otherData['wst_dlq_sts'] = $due_day;
                }
                //客户过去3个月逾期次数（按照贷款记）
                $create_time_old = substr($value['create_time'], 0,10);
                $loanTime = (strtotime($create_time_now)-strtotime($create_time_old))/(60*60*24);
                if( $loanTime < 90 && $due_day > 0 ){
                    $otherData['mth3_dlq_num'] += 1;
                }
                //客户过去3个月最坏逾期天数
                if( $loanTime < 90 ){
                    if( $due_day > $otherData['mth3_wst_sys'] ){
                        $otherData['mth3_wst_sys'] = $due_day;
                    }
                }else{
                    $mth3Count++;
                }
                //客户过去3个月逾期超过7天的贷款数
                if( $loanTime < 90 && $due_day >= 7 ){
                    $otherData['mth3_dlq7_num'] += 1;
                }
                //客户过去6个月有过预期的贷款数
                if( $loanTime < 180 && $due_day > 0 ){
                    $mth6LoanCount += 1;
                }
                $totalCount++;
            }
        }
        //客户过去6个月有过预期的贷款比例（分母为过去6个月批核的总贷款数）
        if( $totalCount > 0 ){
            $otherData['mth6_dlq_ratio'] = floor(($mth6LoanCount / $totalCount)*100)/100;
        }
        //近三个月无借款
        $three_count = $loan->getThreeMcount($user_id,$loan_id);
        if ($three_count == 0) {
            $otherData['mth3_dlq_num'] = '';
            $otherData['mth3_wst_sys'] = '';
            $otherData['mth3_dlq7_num'] = '';
        }
        //近六个月无借款
        $six_count = $loan->getSixMcount($user_id,$loan_id);
        if( $six_count == 0 ) {
            $otherData['mth3_dlq_num'] = '';
            $otherData['mth3_wst_sys'] = '';
            $otherData['mth3_dlq7_num'] = '';
            $otherData['mth6_dlq_ratio'] = '';
        }
        return $otherData;
    }

    //获取运营商数据
    private function getOperator($data,$user_id)
    {
        $operator = [];
        $request_id = ArrayHelper::getValue($data, 'request_id', 0);
        $aid = ArrayHelper::getValue($data, 'aid', 0);
        $anti_where = ['and', ['request_id' => $request_id], ['aid' => $aid],['user_id' => $user_id]];
        $address = new Address();
        $address_select = 'addr_contacts_count,addr_relative_count,addr_count';
        $operator += $address->getAddress($anti_where, $address_select);
        
        $contact = new Contact();
        $contact_select = 'com_c_total,com_r_total,com_r_rank,com_c_total_mavg,com_r_total_mavg';
        $operator += $contact->getContact($anti_where, $contact_select);

        $report = new Report();
        $report_select = 'report_aomen,report_court,report_fcblack,report_shutdown,report_night_percent,report_120,report_110,report_loan_connect,report_lawyer';
        $operator += $report->getReport($anti_where, $report_select);

        $detail = new Detail();
        $detail_select = 'com_valid_mobile,com_hours_connect,vs_valid_match,com_valid_all,vs_phone_match';
        $operator += $detail->getDetail($anti_where, $detail_select);

        $detail_other = new DetailOther();
        $other_select = 'phone_register_month,shutdown_sum_days,total_duration';
        $operator += $detail_other->getDetailOther($anti_where, $other_select);
        return $operator;
    }

    //请求决策系统
    public function queryCrif($request_id,$data,$process_code)
    {
        $loan_data = [
            'request_id' => $request_id,
            'process_code' => $process_code,
            'params_data' => $data,
        ];
        $api = new \app\modules\api\common\BaseApi();
        $result = $api->sendRequest($loan_data);
        if (empty($result)) {
            Logger::dayLog('loan_error2', '决策结果为空', $result, $loan_data);
            return $this->returnInfo(false, '决策结果为空');
        }
        if (isset($result['res_code']) && $result['res_code'] != 0) {
            Logger::dayLog('loan_error2', '决策异常', $result, $loan_data);
            return $this->returnInfo(false, '决策异常');
        }
        return $this->returnInfo(true, $result);
    }

    //获取百度金融数据
    private function getBaiduInfo($data)
    {
        //百度金融评级
        $baiduRisk = new DcBaidurisk();
        $baidu_select = 'black_level';
        $dcBaidurisk = $baiduRisk->getBaiduRisk($data,$baidu_select);
        return $dcBaidurisk;
    }
}