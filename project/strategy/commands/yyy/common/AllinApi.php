<?php

namespace app\commands\yyy\common;

use Yii;
use yii\helpers\ArrayHelper;

use app\common\Logger;
use app\modules\api\common\CloudApi;
use app\models\TmpBlack;
use app\models\open\OpJxlStat;
use app\models\MobileWhiteList;

use app\models\yyy\User;
use app\models\yyy\YiUserCredit;
use app\models\yyy\YyyApi;
use app\models\yyy\UserPassword;
use app\models\yyy\UserLoan;
use app\models\yyy\UserLoanExtend;
use app\models\yyy\YiFavoriteContacts;

use app\models\antifraud\Address;
use app\models\antifraud\AfAddrLoan;
use app\models\antifraud\Contact;
use app\models\antifraud\Detail;
use app\models\antifraud\Report;
use app\models\antifraud\AfSsReport;
use app\models\antifraud\DetailOther;
use app\models\antifraud\AfBase;

use app\models\ygy\YgyUserCredit;

/**
 * 一亿元完整数据基类
 */
class AllinApi
{
    protected $oYyyApi;
    protected $oCloudApi;
    private static $device_map;

    public function __construct()
    {
        $this->oYyyApi = new YyyApi();
        $this->oCloudApi = new CloudApi();
        self::$device_map = ['1'=> 'web','3'=> 'ios','4'=>'android','5'=> 'android'];
    }

    //数据源
    public function getAllinInfo($data)
    {
        // 获取用户数据
        $all_data = $this->getUserAllInfo($data);
        if (!$all_data) {
            return [];
        }
        $user_id = ArrayHelper::getValue($all_data, 'user_id', '');
        $phone = ArrayHelper::getValue($all_data, 'phone', '');
        $idcard = ArrayHelper::getValue($all_data, 'idcard', '');
        //获取cloud数据集
        $allin_info = $this->getCloudDatas($all_data);
        // 获取用户额度 
        $allin_info += $this->getQuotadata($user_id);
        //获取反欺诈数据集
        $allin_info += $this->getPromeDatas($all_data);
        // 获取复贷数据集
        $allin_info += $this->getReloanDates($user_id);
        // 黑名单数据集
        $allin_info += $this->getBlack($user_id, $phone, $idcard);
        // 白名单决策
        $allin_info['is_test'] = $this->checkTestWhite($phone);
        //组合
        $allin_info = array_merge($all_data, $allin_info);
        return $allin_info;
    }
    private function checkTestWhite($mobile) {
        //增加白名单
        try {
            // if (!SYSTEM_PROD) {
            //     $mobile = '13264221422';
            // }
            $oMobileWhiteList = new MobileWhiteList();
            $while_info = $oMobileWhiteList->getIsWhilte($mobile);
            if ($while_info){
                return 1;
            }
            return 0;
        } catch(\Exception $e){
            Logger::dayLog("queryError", "is_test error:",$e->getMessage());
            return 0;
        }
    }
    /**
     * 获取用户信息
     * @param $user_id
     * @return array|bool
     * @todo [修改 source uuid IP 查询地址]
     */
    public function getUserAllInfo($data_set)
    {
        $aid = ArrayHelper::getValue($data_set, 'aid',1);
        if (empty($data_set)) {
            return [];
        }
        $user_id = ArrayHelper::getValue($data_set, 'user_id');
        //user表信息
        $oUser = new User();
        $user_info = $oUser->getUser(['user_id' => $user_id]);
        if (empty($user_info)) {
            Logger::dayLog('YyyApi/getUserInfoAll', $data_set, '用户不存在');
            return [];
        }
        $user_extend = $user_info->userExtend;
        //地址
        $oAddress = new \app\models\yyy\Address();
        $address_info = $oAddress->getAddressByUserId($user_id);
        if ($aid == 17) {
            // ygy_user_credit
            $user_credit = $this->getYgyUserCreditByReqid($data_set);
        } else {
            // yi_user_credit(token_id,source)
            $user_credit = $this->getUserCreditByReqid($data_set);
        }
        // user_loan_extend(uuid,userIp)
        $success_num = $this->getSuccessNum($data_set);
        $source = ArrayHelper::getValue($user_credit, 'device_type','000');
        $uesr_data = [
            'user_id' => ArrayHelper::getValue($user_info, 'user_id'),// 一亿元 user_id
            'identity' => ArrayHelper::getValue($user_info, 'identity'),
            'mobile' => ArrayHelper::getValue($user_info, 'mobile'),
            'realname' => ArrayHelper::getValue($user_info, 'realname'),
            'telephone' => ArrayHelper::getValue($user_info, 'telephone'),
            'reg_time' => ArrayHelper::getValue($user_info, 'create_time'),
            'query_time' => date('Y-m-d H:i:s'),
            // 同盾所需数据
            'identity_id' => ArrayHelper::getValue($user_info, 'user_id'),// 一亿元 user_id
            'idcard' => ArrayHelper::getValue($user_info, 'identity'),
            'phone' => ArrayHelper::getValue($user_info, 'mobile'),
            'name' => ArrayHelper::getValue($user_info, 'realname'),
            'ip' => ArrayHelper::getValue($user_credit, 'device_ip'), //ip地址
            'device' => ArrayHelper::getValue($user_credit, 'uuid'), // 设备号
            'source' => (string)$source, //
            'xhh_apps' => (string)ArrayHelper::getValue(self::$device_map, $source,'web'), //来源ios,android,web,....
            'token_id' => ArrayHelper::getValue($user_credit, 'device_tokens'),// app编号
            'black_box' => ArrayHelper::getValue($user_credit, 'black_box'),// 设备指纹
            'aid' => ArrayHelper::getValue($data_set, 'aid'),
            'req_id' => ArrayHelper::getValue($data_set, 'req_id'),
            'come_from' => ArrayHelper::getValue($user_info, 'come_from'),
            // 公司与学校信息
            'company_name' => ArrayHelper::getValue($user_extend, 'company'),
            'company_industry' => (string)ArrayHelper::getValue($user_extend, 'industry'), // 选填 行业
            'company_position' => ArrayHelper::getValue($user_extend, 'position'), // 选填 职位
            'company_phone' => ArrayHelper::getValue($user_extend, 'telephone'), // 选填 公司电话
            'company_address' => ArrayHelper::getValue($user_extend, 'company_address'), // 选填 公司地址
            'school_name' => ArrayHelper::getValue($user_extend, 'school'), // 选填 学校名称
            'school_time' => ArrayHelper::getValue($user_extend, 'school_time'), // 选填 入学时间
            'edu' => ArrayHelper::getValue($user_extend, 'edu'), // 选填 本科,研究生
            'latitude' => ArrayHelper::getValue($address_info, 'latitude'), // 维度
            'longtitude' => ArrayHelper::getValue($address_info, 'longitude'), // 经度
            'accuracy' => "", // 精度
            'speed' => "", //速度
            'location' => ArrayHelper::getValue($address_info, 'address'), //地址
            'success_num' => $success_num, //历史成功借款次数
            // 'loan_total' => ArrayHelper::getValue($loan_extend_info, 'loan_total', 0),
        ];
        $uesr_data['type'] = $success_num > 0 ? 2 : 1;
        return array_merge($data_set, $uesr_data);
    }

    private function getUserCreditByReqid($data)
    {
        $oUserCredit = new YiUserCredit();
        $req_id = ArrayHelper::getValue($data, 'id');
        $where = ['req_id'=>$req_id];
        $user_credit = $oUserCredit->getUserCredit($where);
        return $user_credit;
    }

    private function getYgyUserCreditByReqid($data){
        $oUserCredit = new YgyUserCredit();
        $req_id = ArrayHelper::getValue($data, 'id');
        $where = ['req_id' => (int)$req_id];
        $user_credit = $oUserCredit->getYgyUserCredit($where);
        return $user_credit;
    }
    private function getSuccessNum($data)
    {
        $oUserLoanExtend = new UserLoanExtend();
        $user_id = ArrayHelper::getValue($data, 'user_id');
        $where = ['user_id'=>$user_id,'status'=>'SUCCESS'];
        $user_Loan_extend = $oUserLoanExtend->getLoanExtendInfo($where);
        if (empty($user_Loan_extend)) {
            return 0;
        }
        $success_num = ArrayHelper::getValue($user_Loan_extend, 'success_num','0');
        $loan_info = $user_Loan_extend->userLoan;
        if (empty($loan_info)) {
            return $success_num;
        }
        if ($loan_info->status == 8 && in_array($loan_info->business_type,[1,4])) {
            return $success_num+1;
        }
        return $success_num;
    }

    public function getLastSuccessLoanData($user_id){
        $oUserLoan = new UserLoan();
        $last_success_loan = $oUserLoan->getSuLoan($user_id);
        $ret_data = [
            'last_success_loan_days' => (int)ArrayHelper::getValue($last_success_loan,'days',0),
        ];
        return $ret_data;
    }
    public function getCloudDatas(&$data)
    {
        $url = 'loan';
        // if (isset($data['success_num']) && $data['success_num'] > '0') {
        //     $url = 'fraudmetrix';
        // }
        // 同盾多投及黑名单数据
        $cloud_info = $this->oCloudApi->cloudApi($data, $url);
        // 天启数据
        $origin_info = $this->oCloudApi->getOrigin($data);
        $origin_info['is_black_tq'] = $origin_info['is_black'];
        unset($origin_info['is_black']);
        // baidu risk
        $risk_info = $this->oCloudApi->getBaiduRiskInfo($data);
        //baidu prea
        $prea_info = $this->oCloudApi->getBaiduPreaInfo($data);
        // 学信数据
        $edu_info = $this->oCloudApi->getTxskedu($data);
        $cloud_info = array_merge($cloud_info, $origin_info, $risk_info, $prea_info, $edu_info);
        // 一个月内设备数
        $device = ArrayHelper::getValue($data, 'device', '');
        $cloud_info['one_number_account_value'] = $this->oCloudApi->getOneMouthDeviceAccount($device);
        try {
            $baidulbs = $this->oCloudApi->queryCloud($data,'baidulbs');
        } catch (\Exception $e) {
            Logger::dayLog('AllinApi/baidulbs', json_encode($data), $e->getMessage());
        }
        
        return $cloud_info;
    }

    // 获取cloud数据集

    private function getQuotadata($user_id)
    {
        $where = ['user_id' => $user_id];
        $select = 'quota';
        // $quota = $this->oYyyApi->getQuota($where,$select); // yi_user_quota
        $quota = $this->oYyyApi->getTemQuota($where, $select); // yi_tem_quota
        if (empty($quota)) {
            $quota['quota'] = 0;
        }
        return $quota;
    }

    // 获取反欺诈数据集

    public function getPromeDatas(&$data)
    {
        //获取运营商数据
        $report_info = $this->getReportInfo($data);
        //获取常用联系人是否为一亿元用户
        $report_info['com_c_user'] = $this->getComcUser($data['user_id']);
        //获取运营商报告类型
        $report_info['report_type'] = $this->getReportType($data);
        return $report_info;
    }

    //获取运营商数据
    private function getReportInfo($data)
    {
        $operator = [];
        $request_id = ArrayHelper::getValue($data, 'req_id', 0);
        $user_id = ArrayHelper::getValue($data, 'user_id', 0);
        $aid = ArrayHelper::getValue($data, 'aid', 0);
        $anti_where = ['and', ['request_id' => $request_id], ['aid' => $aid], ['user_id' => $user_id]];
        //原始数据#####################################################
        $address = new Address();
        $address_select = 'addr_tel_count,addr_parents_count,addr_contacts_count,addr_relative_count,addr_count,addr_collection_count,addr_phones_nodups';
        $operator += $address->getPromeData($anti_where, $address_select);

        $contact = new Contact();
        $contact_select = 'com_c_rank,com_c_total,com_r_total,com_r_rank,com_c_total_mavg,com_r_total_mavg,com_r_duration_mavg';
        $operator += $contact->getPromeData($anti_where, $contact_select);

        $report = new Report();
        $report_select = 'report_aomen,report_court,report_fcblack,report_shutdown,report_night_percent,report_120,report_110,report_loan_connect,report_lawyer';
        $operator += $report->getPromeData($anti_where, $report_select);

        $report = new AfSsReport();
        $report_select = 'score,consume_fund_index,indentity_risk_index,social_stability_index';
        $operator += $report->getPromeData($anti_where, $report_select);

        $addr_loan = new AfAddrLoan();
        $addr_select = 'realadl_tot_reject_num,user_total,realadl_tot_freject_num,realadl_tot_sreject_num,realadl_tot_dlq14_num,realadl_dlq14_ratio,history_bad_status,loan_all';
        $operator += $addr_loan->getPromeData($anti_where, $addr_select);
        //转换变量名###########
        $operator['realadl_wst_dlq_sts'] = $operator['history_bad_status'];
        // unset($operator['history_bad_status']);
        #######################
        $detail = new Detail();
        $detail_select = 'com_month_num,com_call_duration,com_month_people,com_days_call,com_hours_answer_davg,com_offen_connect,com_day_connect_mavg,com_night_connect_p,com_tel_people,com_valid_mobile,com_month_call_duration,com_hours_call_davg,com_count,com_call,com_answer,com_hours_connect,vs_valid_match,com_valid_all,vs_phone_match,com_use_time,com_month_answer_duration,com_mobile_people,com_night_duration_mavg,com_max_tel_connect,vs_duration_match';
        $operator += $detail->getPromeData($anti_where, $detail_select);

        $detail_other = new DetailOther();
        $other_select = 'last3_answer,phone_register_month,shutdown_duration_count,tot_phone_num,same_phone_num,last3_not_mobile_count,last3_all,last6_not_mobile_count,shutdown_sum_days,total_duration,shutdown_max_days';
        $operator += $detail_other->getPromeData($anti_where, $other_select);

        $other_select = 'phone_register_month';
        $operator += $detail_other->getDetailOther($anti_where, $other_select);
        //计算后数据###############################################
        //retain_ratio
        if ($operator['tot_phone_num'] == 0 || is_null($operator['same_phone_num'])) {
            $operator['retain_ratio'] = null;
        } else {
            $operator['retain_ratio'] = (float)(sprintf('%.2f', ($operator['same_phone_num'] / $operator['tot_phone_num'])));
        }

        //last_3mth_Oth_ratio ; last_3mth_oth_incr
        if ($operator['last3_all'] == 0 || is_null($operator['last3_not_mobile_count'])) {
            $operator['last_3mth_Oth_ratio'] = null;
        } else {
            $operator['last_3mth_Oth_ratio'] = (float)(sprintf('%.2f', ($operator['last3_not_mobile_count'] / $operator['last3_all'])));
        }
        if ($operator['last6_not_mobile_count'] == 0 || is_null($operator['last3_not_mobile_count'])) {
            $operator['last_3mth_oth_incr'] = null;
        } else {
            $operator['last_3mth_oth_incr'] = (float)(sprintf('%.2f', ($operator['last3_not_mobile_count'] / $operator['last6_not_mobile_count'])));
        }
        //becalled_ratio
        if ($operator['com_count'] == 0 || is_null($operator['com_answer'])) {
            $operator['becalled_ratio'] = null;
        } else {
            $operator['becalled_ratio'] = (float)(sprintf('%.2f', ($operator['com_answer'] / $operator['com_count'])));
        }
        ######################过滤NaN#########################
        foreach ($operator as $k => $val) {
            if (is_nan($val)) {
                $operator[$k] = null;
            }
        }
        ###############################################
        return $operator;
    }

    private function getComcUser($user_id)
    {
        //获取常用联系人电话
        $contacts = new YiFavoriteContacts();
        $con_info = $contacts->getFavorite($user_id);
        if (empty($con_info)) {
            return 0;
        }
        $mobile = $con_info->mobile;
        if (empty($mobile)) {
            return 0;
        }
        $user = new User();
        $count = count($user->getUser(['mobile' => $mobile]));
        return $count;
    }

    //获取用户运营商报告来源
    private function getReportType($data)
    {
        $request_id = ArrayHelper::getValue($data, 'req_id', '');
        if (empty($request_id)) {
            return 0;
        }
        //获取聚信立表ID
        $base = new AfBase();
        $where = ['request_id' => $request_id];
        $base_info = $base->getBase($where);
        if (empty($base_info)) {
            return 0;
        }
        $jxl_id = $base_info->jxlstat_id;
        if (empty($jxl_id)) {
            return 0;
        }
        //开放平台jxl_stat
        $jxl_stat = new OpJxlStat();
        $where = ['id' => $jxl_id];
        $jxl_data = $jxl_stat->getJxl($where);
        if (is_null($jxl_data)) {
            return 0;
        }
        $source = $jxl_data->source;
        if (is_null($source)) {
            return 0;
        }
        return $source;
    }

    // 用户额度  @todo  额度表改为yi_tem_qouta

    public function getReloanDates($userId)
    {
        $user_id = $userId;
        $otherData = [
            'wst_dlq_sts'=>0,//客户历史最坏逾期天数
            'mth3_dlq_num'=>'',//客户过去3个月逾期次数（按照贷款记） 
            'mth3_wst_sys'=>'',// 客户过去3个月最坏逾期天数
            'mth3_dlq7_num'=>'',//客户过去3个月逾期超过7天的贷款数 
            'mth6_dlq_ratio'=>'',//客户过去6个月有过预期的贷款比例（分母为过去6个月批核的总贷款数）
        ];
        if(!$user_id)
            return $otherData;
        
        
        $loan = new UserLoan;
        $loanAll = $loan->getAllLoan(['user_id'=>$user_id,'status'=>[8,9,11,12,13],'business_type'=>[1,4]]);
        $wst_dlq_sts = [];
        if( $loanAll ) {
            $otherData = [
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
                // if( $due_day > $otherData['wst_dlq_sts'] ){
                $wst_dlq_sts[] = $due_day;
                // }
                //客户过去3个月逾期次数（按照贷款记）
                $create_time_old = substr($value['create_time'], 0,10);
                $loanTime = (strtotime($nowtime)-strtotime($create_time_old))/(60*60*24);
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
            //客户过去6个月有过预期的贷款比例（分母为过去6个月批核的总贷款数）
            if( $totalCount > 0 ){
                $otherData['mth6_dlq_ratio'] = floor(($mth6LoanCount / $totalCount)*100)/100;
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
                $otherData['mth3_dlq_num'] = '';
                $otherData['mth3_wst_sys'] = '';
                $otherData['mth3_dlq7_num'] = '';
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
                $otherData['mth3_dlq_num'] = '';
                $otherData['mth3_wst_sys'] = '';
                $otherData['mth3_dlq7_num'] = '';
                $otherData['mth6_dlq_ratio'] = '';
            }
        }
        if (!empty($wst_dlq_sts)){
            rsort($wst_dlq_sts);
            $otherData['wst_dlq_sts'] = $wst_dlq_sts[0];
        }
        Logger::dayLog('antiInfo', $user_id, $otherData);
        return $otherData;
    }

    /**
     * 合规调用
     * @param $userId
     * @return array
     */
    public function getReloanDatesHg($userId, $create_time)
    {
        if (empty($create_time)){
            $create_time = date("Y-m-d H:i:s", $create_time);
        }

        $user_id = $userId;
        $otherData = [
            'wst_dlq_sts'=>0,//客户历史最坏逾期天数
            'mth3_dlq_num'=>'',//客户过去3个月逾期次数（按照贷款记）
            'mth3_wst_sys'=>'',// 客户过去3个月最坏逾期天数
            'mth3_dlq7_num'=>'',//客户过去3个月逾期超过7天的贷款数
            'mth6_dlq_ratio'=>'',//客户过去6个月有过预期的贷款比例（分母为过去6个月批核的总贷款数）
        ];
        if(!$user_id)
            return $otherData;


        $loan = new UserLoan;
        $all_loan_where = [
            'AND',
            ['=', 'user_id', $user_id],
            ['in', 'status', [8,9,11,12,13]],
            ['in', 'business_type', [1,4]],
            ['<=', 'create_time', $create_time],
        ];
        $loanAll = $loan->getAllLoan($all_loan_where);
        $wst_dlq_sts = [];
        if( $loanAll ) {
            $otherData = [
                'wst_dlq_sts'=>0,//客户历史最坏逾期天数
                'mth3_dlq_num'=>0,//客户过去3个月逾期次数（按照贷款记）
                'mth3_wst_sys'=>0,// 客户过去3个月最坏逾期天数
                'mth3_dlq7_num'=>0,//客户过去3个月逾期超过7天的贷款数
                'mth6_dlq_ratio'=>0,//客户过去6个月有过预期的贷款比例（分母为过去6个月批核的总贷款数）
            ];
            //$nowtime = date('Y-m-d H:i:s');
            $nowtime = $create_time;
            $mth6LoanCount = 0;
            $totalCount = 0;
            $mth3Count = 0;
            foreach ($loanAll as $key => $value) {
                $repay_time = substr($value['repay_time'], 0,10);
                $end_date = substr($value['end_date'], 0,10);
                //最长逾期时间
                $due_day = (int)((strtotime($repay_time)-strtotime($end_date))/(60*60*24));
                // if( $due_day > $otherData['wst_dlq_sts'] ){
                $wst_dlq_sts[] = $due_day;
                // }
                //客户过去3个月逾期次数（按照贷款记）
                $create_time_old = substr($value['create_time'], 0,10);
                $loanTime = (strtotime($nowtime)-strtotime($create_time_old))/(60*60*24);
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
            //客户过去6个月有过预期的贷款比例（分母为过去6个月批核的总贷款数）
            if( $totalCount > 0 ){
                $otherData['mth6_dlq_ratio'] = floor(($mth6LoanCount / $totalCount)*100)/100;
            }
            //近三个月无借款
            $three_time = date("Y-m-d 00:00:00",strtotime('-89 days', strtotime($create_time)));
            $where = ['and',
                ['>=','create_time',$three_time],
                ['user_id'=>$user_id,'status'=>[8,9,11,12,13],'business_type'=>[1,4]]
            ];
            $three_loan = $loan->getAllLoan($where);
            $three_count = count($three_loan);
            if ($three_count == 0) {
                $otherData['mth3_dlq_num'] = '';
                $otherData['mth3_wst_sys'] = '';
                $otherData['mth3_dlq7_num'] = '';
            }
            //近六个月无借款
            $six_time = date("Y-m-d 00:00:00",strtotime('-179 days', strtotime($create_time)));
            $six_where = ['and',
                ['>=','create_time',$six_time],
                ['user_id'=>$user_id,'status'=>[8,9,11,12,13],'business_type'=>[1,4]]
            ];
            $six_loan = $loan->getAllLoan($six_where);
            $six_count = count($six_loan);
            if( $six_count == 0 ) {
                $otherData['mth3_dlq_num'] = '';
                $otherData['mth3_wst_sys'] = '';
                $otherData['mth3_dlq7_num'] = '';
                $otherData['mth6_dlq_ratio'] = '';
            }
        }
        if (!empty($wst_dlq_sts)){
            rsort($wst_dlq_sts);
            $otherData['wst_dlq_sts'] = $wst_dlq_sts[0];
        }
        Logger::dayLog('antiInfo', $user_id, $otherData);
        return $otherData;
    }

    public function getBlack($user_id, $phone, $idcard)
    {
        // 催收黑名单
        $black['id_collection_black'] = $this->oCloudApi->getForeignBlackIdcard($idcard);
        $black['ph_collection_black'] = $this->oCloudApi->getForeignBlackPhone($phone);
        //临时黑名单
        $tmp_black = new TmpBlack();
        $where = ['user_id' => $user_id];
        $black['is_black_tem'] = $tmp_black->getTmpbBlack($where) > 0 ? 1 : 0;
        return $black;
    }
}
