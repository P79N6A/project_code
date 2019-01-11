<?php
namespace app\modules\api\logic;

use app\common\Logger;
use app\modules\api\common\CloudApi;
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
use app\models\cloud\DeviceUser;
use app\models\cloud\DcBaidurisk;
use app\models\Loan;
use app\models\Result;
use app\models\yyy\AntiFraud;
use app\models\yyy\LoanEvent;
use app\models\yyy\User;
use app\models\yyy\UserLoan;
use app\models\yyy\UserExtend;
use Yii;
use yii\helpers\ArrayHelper;

class LoanLogic extends BaseLogic
{
    public $where = [];
    public $loan_where = [];
    public $loan_id;
    public $source_type = [];

    function __construct($data)
    {
        parent::__construct();
        $user_id = ArrayHelper::getValue($data, 'user_id', 0);
        $loan_id = ArrayHelper::getValue($data, 'loan_id', 0);
        $loan_no = ArrayHelper::getValue($data, 'loan_no', 0);
        $this->where = ['user_id' => $user_id];
        $this->loan_where = ['and', ['user_id' => $user_id], ['loan_id' => $loan_id]];
        $this->req_where = ['and', ['user_id' => $user_id], ['loan_no' => $loan_no]];
        $this->loan_id = $loan_id;
        $this->user_id = $user_id;
        $this->source_type = ['1' => '微信', '2' => 'app', '3' => 'ios', '4' => 'android', '5' => 'H5'];
    }

    public function getLoanOneInfo($data)
    {
        //校验用户信息
        $chk_res = $this->chkUserLoanInfo();
        if (!$chk_res) {
            return $this->returnInfo(false, $this->info);
        }
        $loan_info = $this->info;
        $user_id = ArrayHelper::getValue($data, 'user_id');
        $request_id = ArrayHelper::getValue($data, 'request_id');
        $loan_no = ArrayHelper::getValue($data, 'loan_no');
        $from = ArrayHelper::getValue($data, 'from');
        $loan_info['from'] = $from;
        $loan = new Loan();
        if ($data['from'] == 2) {
            $res = $loan->getInfo($this->req_where);
            if (!empty($res)) {
                return $this->returnInfo(false, '重复请求');
            }
        }
        $loan_info = array_merge($loan_info, $data);
        //请求cloud百度LBS接口并获取数据
        $api = new \app\modules\api\common\CloudApi();
        $baidulbs = $api->queryCloud($loan_info,'baidulbs');
        //获取注册决策数据
        $multiPhone = new MultiPhone();
        $ph_multi_select = 'mph_y,mph_fm,mph_other,mph_br,mph_fm_seven_d,mph_fm_one_m,mph_fm_three_m';
        $loan_info += $multiPhone->getPhMultiInfo($loan_info['mobile'], $ph_multi_select);

        $multiIdcard = new MultiIdcard();
        $id_multi_select = 'mid_y,mid_fm,mid_other,mid_br,mid_fm_seven_d,mid_fm_one_m,mid_fm_three_m';
        $loan_info += $multiIdcard->getIdMultiInfo($loan_info['identity'], $id_multi_select);

        $blackIdcard = new BlackIdcard();
        $id_black_select = 'bid_fm_sx,bid_fm_court_sx,bid_fm_court_enforce,bid_fm_lost,bid_y,bid_other,bid_br';
        $loan_info += $blackIdcard->getIdBlackInfo($loan_info['identity'], $id_black_select);

        $blackPhone = new BlackPhone();
        $ph_black_select = 'bph_fm_sx,bph_y,bph_other,bph_fm_small,bph_fm_fack,bph_br';
        $loan_info += $blackPhone->getPhBlackInfo($loan_info['mobile'], $ph_black_select);
        $loan_where = ['and', ['user_id' => $user_id], ['loan_no' => $loan_no]];
        if (empty($this->loan_id)) {
            //获取用户当天申请次数
            $user_loan = new UserLoan();
            $loan_info['one_more_loan_value'] = $user_loan->getOneValue($user_id);
            //获取用户七天内申请次数
            $loan_info['seven_more_loan_value'] = $user_loan->getSevenValue($user_id);
        } else {
            $loan_event = new LoanEvent();
            $loan_event_select = 'one_more_loan_value,seven_more_loan_value';
            $loan_info += $loan_event->getLoanEventInfo($loan_where, $loan_event_select);
        }
        //获取用户类型
        $loan_info['type'] = isset($data['type']) ? $data['type'] : $this->getLoanType($user_id);
        if (!empty($this->loan_id)) {
            $loan_info['loan_create_time'] = $loan_info['create_time'];
        }
        $loan_info['query_time'] = date('Y-m-d H:i:s');
        unset($loan_info['create_time']);
        //百度金融评级
        $baiduRisk = new DcBaidurisk();
        $baidu_select = 'black_level';
        $loan_info += $baiduRisk->getBaiduRisk($loan_info,$baidu_select);
        //数据类型转换
        $loan_info = $this->transType($loan_info);
        return $this->returnInfo(true, $loan_info);
    }

    public function getLoanTwoInfo($data)
    {
        //校验用户及借款信息
        $chk_res = $this->chkUserLoanInfo();
        if (!$chk_res) {
            return $this->returnInfo(false, $this->info);
        }
        $loan_info = $this->info;
        $request_id = ArrayHelper::getValue($data, 'request_id');
        $from = ArrayHelper::getValue($data, 'from');
        $loan_info['from'] = $from;
        //获取用户类型
        $loan_info['type'] = $this->getLoanType($this->user_id);
        //获取反欺诈请求ID
        $antifraud = new AntiFraud;
        $anti_info = $antifraud->getInfo($this->loan_where, 'user_id,id');
        $anti_id = $anti_info['id'];
        $anti_where = ['and', ['request_id' => $anti_id],['aid'=>'1'], ['user_id' => $this->user_id]];
        //获取决策数据
        $address = new Address();
        $address_select = 'addr_contacts_count,addr_relative_count,addr_count';
        $loan_info += $address->getAddress($anti_where, $address_select);
        $contact = new Contact();
        $contact_select = 'com_c_total,com_r_total,com_r_rank,com_c_total_mavg,com_r_total_mavg';
        $loan_info += $contact->getContact($anti_where, $contact_select);

        $report = new Report();
        $report_select = 'report_aomen,report_court,report_fcblack,report_shutdown,report_night_percent,report_120,report_110,report_loan_connect,report_lawyer';
        $loan_info += $report->getReport($anti_where, $report_select);

        $detail = new Detail();
        $detail_select = 'com_valid_mobile,com_hours_connect,vs_valid_match,com_valid_all,vs_phone_match';
        $loan_info += $detail->getDetail($anti_where, $detail_select);

        $black = new Black();
        $black_select = 'addr_has_black';
        $loan_info += $black->getBlack($anti_where, $black_select);
        $detail_other = new DetailOther();
        $other_select = 'phone_register_month,shutdown_sum_days,total_duration';
        $loan_info += $detail_other->getDetailOther($anti_where, $other_select);

        $loan_info['loan_create_time'] = $loan_info['create_time'];
        unset($loan_info['create_time']);
        $loan_info['request_id_two'] = $request_id;
        $loan_info['last_step'] = 2;
        $loan_info['query_time'] = date('Y-m-d H:i:s');
        //获取同一设备30天内申请借款账户数
        // $user_extend = new UserExtend();
        // $device = $user_extend->getInfo($this->where,'uuid');
        $number_value = 0;
        // if (!empty($device['uuid'])) {
        //     $deviceUser = new DeviceUser(); 
        //     $device_time = date('Y-m-d 00:00:00',strtotime('-30 days'));
        //     $number_value = $deviceUser->find()->where(['and',['>=','create_time',$device_time],['event'=> 'loan'],['device'=>$device['uuid']]])->groupBy('identity_id')->count();
        // }
        $loan_info['one_number_account_value'] = $number_value;
        //数据类型转换
        $loan_info = $this->transType($loan_info);
        return $this->returnInfo(true, $loan_info);
    }

//获取用户同盾信息
    private function getCloudInfo($data)
    {
        $data['aid'] = '1';
        $data['event'] = 'loan';
        $cloudApi = new CloudApi();
        $url = 'fraudmetrix';
        $res = $cloudApi->queryCloud($data,$url);
        $cloud_params = [
            'phone' => $data['mobile'],
            'idcard' => $data['identity'],
        ];
        $multiInfo = $cloudApi->getMultiInfo($cloud_params);
        Logger::dayLog('multiInfo', $cloud_params, $multiInfo);
        return $multiInfo;
    }
    public function getAntiInfo($loanId,$userId){
        //校验用户信息
        $chk_res = $this->chkUserLoanInfo();
        if (!$chk_res) {
            return $this->returnInfo(false, $this->info);
        }
        $loanInfo = $this->info;
        $multiInfo = $this->getCloudInfo($loanInfo);
        $loan_id = $loanId;
        $user_id = $userId;
        $otherData = [
            'wst_dlq_sts'=>0,//客户历史最坏逾期天数
            'mth3_dlq_num'=>'',//客户过去3个月逾期次数（按照贷款记） 
            'mth3_wst_sys'=>'',// 客户过去3个月最坏逾期天数
            'mth3_dlq7_num'=>'',//客户过去3个月逾期超过7天的贷款数 
            'mth6_dlq_ratio'=>'',//客户过去6个月有过预期的贷款比例（分母为过去6个月批核的总贷款数）
        ];
        if( !$loan_id || !$user_id)
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
                    // if( $due_day > $otherData['wst_dlq_sts'] ){
                    $wst_dlq_sts[] = $due_day;
                    // }
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
        }
        if (!empty($wst_dlq_sts)){
            rsort($wst_dlq_sts);
            $otherData['wst_dlq_sts'] = $wst_dlq_sts[0];
        }
        Logger::dayLog('antiInfo', $loan_id, $user_id, $otherData);
        $otherData = array_merge($loanInfo,$otherData,$multiInfo);
        return $otherData;
    }
    public function chkUserLoanInfo($loan_step = 0)
    {
        //查询用户信息 借款信息
        $user = new User();
        $user_select = 'user_id,identity,mobile,telephone,come_from,realname';
        $loan_info = $user->getInfo($this->where, $user_select);
        if (empty($loan_info)) {
            Logger::dayLog('user', 'errors', '用户不存在', $this->where);
            return $this->returnInfo(false, '用户不存在');
        }
        if (!empty($this->loan_id)) {
            $user_loan = new UserLoan();
            $user_loan_select = 'loan_id,loan_no,amount,source,days,business_type,create_time';
            $userLoaninfo = $user_loan->getInfo($this->loan_where, $user_loan_select);
            if (empty($userLoaninfo)) {
                Logger::dayLog('loan', 'errors', '借款不存在', $this->loan_where);
                return $this->returnInfo(false, '借款不存在1');
            }
            // $userLoaninfo['source'] = $this->source_type[$userLoaninfo['source']];
            $loan_info = array_merge($userLoaninfo, $loan_info);
        }
        return $this->returnInfo(true, $loan_info);
    }

    public function transType($data)
    {
        foreach ($data as $k => $val) {
            if ($k != 'identity' && $k != 'mobile' && $k != 'loan_create_time' && $k != 'telephone' && $k != 'realname' && $k != 'source' && $k != 'loan_no' && $k != 'query_time' && $k != 'report_night_percent' && $k != 'black_level') {
                $data[$k] = (int)$data[$k];
            }
        }
        return $data;
    }
    //记录结果
    public function saveResInfo($datas,$result,$request_id)
    {
        //记录结果
        $ret_info = json_encode($result, JSON_UNESCAPED_UNICODE);
        $loan_no = ArrayHelper::getValue($datas, 'loan_no');
        $loan_id = ArrayHelper::getValue($datas, 'loan_id');
        $user_id = ArrayHelper::getValue($datas, 'user_id');
        $from = ArrayHelper::getValue($datas, 'from');
        $res_data = ArrayHelper::getValue($result, 'LOAN_RESULT');
        $res_info = [
            'request_id' => $request_id,
            'from' => $from,
            'res_info' => $ret_info,
            'res_status' => $res_data,
            'loan_no' => $loan_no,
            'loan_id' => $loan_id,
            'user_id' => $user_id,
        ];
        $record_res = new Result();
        $res = $record_res->addResInfo($res_info);
        if (!$res) {
            return $this->returnInfo(false, $record_res->errors);
        }
        return $this->returnInfo(true, '');
    }

    //判断用户类型 type=1，为初贷；=2为复贷；
    public function getLoanType($user_id)
    {
        $user_loan = new UserLoan();
        $type = 1;
        $where = ['user_id'=>$user_id,'status'=>8,'business_type'=>[1,4]];
        $loan_count = $user_loan->find()->where($where)->count();
        if($loan_count > 0){
            $type = 2;
        }
        return $type;
    }

    public function getOriginQueryTime($data){
        $phone = ArrayHelper::getValue($data,'mobile','');
        $idcard = ArrayHelper::getValue($data,'identity','');
        if (empty($idcard) || empty($phone)) {
            return '';
        }
        $where_data = ['phone'=>$phone,'idcard'=> $idcard];
        $cloudApi = new CloudApi();
        $origin_data = $cloudApi->getOrigin($where_data);
        $last_create_time_tq = ArrayHelper::getValue($origin_data,'last_create_time_tq','');
        return $last_create_time_tq;
    }
}