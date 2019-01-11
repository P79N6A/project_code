<?php

namespace app\models\credit;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\yyy\YyyApi;
use app\models\Overdueloan;
use app\common\Logger;

use app\models\Request;
use app\models\yyy\AntiFraud;
use app\models\yyy\User;
use app\models\yyy\UserLoanExtend;
use app\models\TmpBlack;
use app\models\yyy\YiFavoriteContacts;
use app\modules\api\common\CloudApi;
use app\models\antifraud\Address;
use app\models\antifraud\AfAddrLoan;
use app\models\antifraud\Contact;
use app\models\antifraud\Detail;
use app\models\antifraud\Report;
use app\models\antifraud\AfSsReport;
use app\models\antifraud\DetailOther;
use app\models\antifraud\AfBase;
use app\models\open\OpJxlStat;
/**
 * 智融数据基类
 */
class CreditApi 
{
    protected $oYyyApi;
    protected $oCloudApi;
    public function __construct()
    {
        $this->oYyyApi = new YyyApi();
        $this->oCloudApi = new CloudApi();
    }
	//数据源
    public function getCreditInfo($data)
    {
        // 获取用户数据
        $all_data = $this->oYyyApi->getUserInfoAll($data);
        if (!$all_data) {
            return [];
        }
        // 获取用户历史成功借款次数
        $credit_info = $this->getSuccessNum($all_data['user_id']);
        // 获取用户历史申请次数
        // $credit_info += $this->getLoanTotal($all_data['user_id']);         
        //获取cloud数据集
        $credit_info += $this->getCloudDatas($all_data);
        // 获取天启数据
        $credit_info += $this->getOriginInfo($all_data);
        // 获取用户额度 
        $credit_info += $this->getQuotadata($all_data['user_id']);
        // 获取百度金融数据
        $credit_info += $this->getBaiduRisk($all_data);
        //获取反欺诈数据集
        $credit_info += $this->getPromeDatas($all_data);
        // 获取复贷数据集
        $credit_info += $this->getReloanDates($all_data);
        // 一个月内该设备借款账户数 
        $credit_info += $this->getOneNumbAccount($all_data['device']);
        ###################################################
        $tmp_black = new TmpBlack();
        $where = ['user_id'=> $all_data['user_id']];
        $credit_info['is_black_tem'] = $tmp_black->getTmpbBlack($where) > 0 ? 1 : 0;
        ###################################################
        // 催收黑名单
        $credit_info['id_collection_black'] = $this->oCloudApi->getForeignBlackIdcard($all_data['idcard']);
        $credit_info['ph_collection_black'] = $this->oCloudApi->getForeignBlackPhone($all_data['phone']);
        //$credit_info组合
        $credit_data = array_merge($all_data,$credit_info);
        return $credit_data;
    }

    private function getOneNumbAccount($device)
    {
        $res['one_number_account_value'] = 0;
        if (empty($device)) {
            return $res;
        }
        //一个月内
        $res['one_number_account_value'] = $this->oCloudApi->getOneMouthDeviceAccount($device);
        return $res;
    }
    private function getBaiduRisk(&$data)
    {
        $query_data = [
                        'user_id' => $data['identity_id'],
                        'mobile' => $data['phone'],
                        'identity' => $data['idcard'],
                    ];
        $res = $this->oCloudApi->getBaiduRiskInfo($query_data);
        return $res;
    }
    private function getLoanTotal($user_id)
    {
        $total = ['loan_total'=> 0];
        if (empty($user_id)) {
            return $total;
        }
        // 一亿元申请次数
        $yyy_total = $this->oYyyApi->getTotal($user_id);

        // 智融钥匙申请次数
        $zhrong_total = $this->getCreditTotal($user_id);

        $total['loan_total'] = $yyy_total+$zhrong_total;
        return $total;
    }
    private function getCreditTotal($user_id)
    {
        if (!$user_id) {
            return 0;
        }
        $credit_list = (new YxUserCreditList)->getTotal($user_id);
        $union = 0;
        $all = count($credit_list);
        foreach ($credit_list as $credit) {
            if ($credit['status'] == 2 && $credit['res_status'] == 1) {
                $union++;
            }
        }
        return $all - $union;
    }
    private function getReloanDates(&$data)
    {
        $relaon_data = $this->oYyyApi->getHistoryData($data);
        return $relaon_data;
    }
    private function getUser($data)
    {
        $user = $this->oYyyApi->getUserInfo($data);
        if (empty($user)) {
            return [];
        }
        $user['reg_time'] = $user['create_time'];
        unset($user['create_time']);
        return $user;
    }
    // 获取cloud数据集
    public function getCloudDatas(&$data)
    {
        $url = 'loan';
        // if ($success_num > '0') {
        //     $url = 'fraudmetrix';
        // }
        $cloud_info = $this->oCloudApi->cloudApi($data,$url);
        return $cloud_info;
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
        $anti_where = ['and', ['request_id' => $request_id], ['aid' => $aid],['user_id' => $user_id]];
        //原始数据#####################################################
        $address = new Address();
        $address_select = 'addr_tel_count,addr_parents_count,addr_contacts_count,addr_relative_count,addr_count,addr_collection_count';
        $operator += $address->getPromeData($anti_where, $address_select);

        $contact = new Contact();
        $contact_select = 'com_c_total,com_r_total,com_r_rank,com_c_total_mavg,com_r_total_mavg';
        $operator += $contact->getPromeData($anti_where, $contact_select);

        $report = new Report();
        $report_select = 'report_aomen,report_court,report_fcblack,report_shutdown,report_night_percent,report_120,report_110,report_loan_connect,report_lawyer';
        $operator += $report->getPromeData($anti_where, $report_select);

        $report = new AfSsReport();
        $report_select = 'score,consume_fund_index,indentity_risk_index,social_stability_index';
        $operator += $report->getPromeData($anti_where, $report_select);

        $addr_loan = new AfAddrLoan();
        $addr_select = 'realadl_tot_reject_num,realadl_tot_freject_num,realadl_tot_sreject_num,realadl_tot_dlq14_num,realadl_dlq14_ratio,history_bad_status';
        $operator += $addr_loan->getPromeData($anti_where, $addr_select);
        //转换变量名###########
        $operator['realadl_wst_dlq_sts'] = $operator['history_bad_status'];
        unset($operator['history_bad_status']);
        #######################
        $detail = new Detail();
        $detail_select = 'com_day_connect_mavg,com_night_connect_p,com_tel_people,com_valid_mobile,com_month_call_duration,com_hours_call_davg,com_count,com_call,com_answer,com_hours_connect,vs_valid_match,com_valid_all,vs_phone_match';
        $operator += $detail->getPromeData($anti_where, $detail_select);

        $detail_other = new DetailOther();
        $other_select = 'tot_phone_num,same_phone_num,last3_not_mobile_count,last3_all,last6_not_mobile_count,shutdown_sum_days,total_duration';
        $operator += $detail_other->getPromeData($anti_where, $other_select);
        
        $other_select = 'phone_register_month';
        $operator += $detail_other->getDetailOther($anti_where, $other_select);
        //计算后数据###############################################
        //retain_ratio
        if ($operator['tot_phone_num'] == 0 || is_null($operator['same_phone_num'])) {
            $operator['retain_ratio'] = null;
        } else {
            $operator['retain_ratio'] = (float)(sprintf('%.2f',($operator['same_phone_num']/$operator['tot_phone_num'])));
        }

        //last_3mth_Oth_ratio ; last_3mth_oth_incr
        if ($operator['last3_all'] == 0 || is_null($operator['last3_not_mobile_count'])) {
            $operator['last_3mth_Oth_ratio'] = null;
        } else {
            $operator['last_3mth_Oth_ratio'] = (float)(sprintf('%.2f',($operator['last3_not_mobile_count']/$operator['last3_all'])));
        }
        if ($operator['last6_not_mobile_count'] == 0 || is_null($operator['last3_not_mobile_count'])) {
            $operator['last_3mth_oth_incr'] = null;
        } else {
            $operator['last_3mth_oth_incr'] = (float)(sprintf('%.2f',($operator['last3_not_mobile_count']/$operator['last6_not_mobile_count'])));
        }
        //becalled_ratio
        if ($operator['com_count'] == 0 || is_null($operator['com_answer'])) {
            $operator['becalled_ratio'] = null;
        } else {
            $operator['becalled_ratio'] = (float)(sprintf('%.2f',($operator['com_answer']/$operator['com_count'])));
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
        $count = count($user->getUser(['mobile'=>$mobile]));
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
        $base_info= $base->getBase($where);
        if (empty($base_info)) {
            return 0;
        }
        $jxl_id = $base_info->jxlstat_id;
        if (empty($jxl_id)) {
            return 0;
        }
        //开放平台jxl_stat
        $jxl_stat = new OpJxlStat();
        $where = ['id'=>$jxl_id];
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
    private function getSuccessNum($user_id)
    {
        $data = ['success_num'=> 0,'type' => 1];
        if (empty($user_id)) {
            return $data;
        }
        $where = ['user_id'=>$user_id];
        $user_Loan_extend = (new UserLoanExtend)->getLoanExtendInfo($where);
        if (empty($user_Loan_extend)) {
            return $data;
        }
        $success_num = ArrayHelper::getValue($user_Loan_extend, 'success_num','0');
        $loan_info = $user_Loan_extend->userLoan;
        if (!empty($loan_info)) {
            if ($loan_info->status == 8 && in_array($loan_info->business_type,[1,4])) {
                $success_num += 1;
            }
        }
        $data['success_num'] = $success_num;
        if ($data['success_num'] > 0) {
            $data['type'] = 2;
        } 
        return $data;
    }
    private function getOriginInfo(&$data)
    {
        $origin_info = $this->oCloudApi->getOrigin($data);
        $origin_info['is_black_tq'] = $origin_info['is_black'];
        unset($origin_info['is_black']);
        return $origin_info;
    }
    // 用户额度  @todo  额度表改为yi_tem_qouta 
    private function getQuotadata($user_id)
    {
        $where = ['user_id' => $user_id];
        $select = 'quota';
        // $quota = $this->oYyyApi->getQuota($where,$select); // yi_user_quota
        $quota = $this->oYyyApi->getTemQuota($where,$select); // yi_tem_quota
        if (empty($quota)) {
            $quota['quota'] = 0; 
        } 
        return $quota;
    }

    // 用户订单信息
    public function getYxOrderInfo($where,$select = '*')
    {
        $quota = (new YxOrder)->getOrder($where,$select);
        if (empty($quota)) {
            return [];
        } 
        return $quota;
    }
}
