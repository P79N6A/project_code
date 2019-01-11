<?php
/**
 * 运营商报告基类
 */
namespace app\modules\service\common;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\common\Curl;
use app\common\ApiSign;

use app\models\StCreditRequest;
use app\models\antifraud\Address;
use app\models\antifraud\Contact;
use app\models\antifraud\Detail;
use app\models\antifraud\Report;
use app\models\antifraud\AfAddrLoan;
use app\models\antifraud\AfDetailTag;
use app\models\antifraud\AfSsReport;
use app\models\antifraud\DetailOther;
class Analysis
{
	private $anti_key;	
	private $anti_url;

	function __construct()
    {
        $this->anti_key = Yii::$app->params['operator']['auth_key'];
    	if (SYSTEM_PROD) {
    		$this->anti_url = Yii::$app->params['operator']['url'];
    	} else {
    		$this->anti_url = '182.92.80.211:8888/api/operator';
    	}
    }
    //运营商数据（对外）
    public function getAntiInfo($data)
    {
        //请求运营商分析数据
        $res = $this->queryAnti($data);
        if (!$res) {
            return $this->returnInfo(false, $this->info);
        }
        $anti_info = $this->info;
        //获取运营商数据
        $report_info = $this->getReportInfo($anti_info,$data['identity_id']);
        return $this->returnInfo(true, $report_info);
    }
    //请求运营商分析报告
    public function queryAnti($data)
    {
        # normal params 
        $sendData = $this->setAntiParams($data);

        # get sign params 
		$sendData['sign'] = $this->getSign($sendData);

        # query antiApi
        $anti_data = $this->queryOperatorApi($sendData);
        #bind credit_request
        if ($anti_data) {
            $res = (new StCreditRequest)->bindCreditRequest($data,$anti_data);
        }
		return $anti_data;
    }

    private function queryOperatorApi($sendData) {
        $anti_json = (new Curl)->postForm($this->anti_url,$sendData);
        Logger::dayLog('service/queryAnti', 'postdata', $sendData, $anti_json,$this->anti_url);
        $anti_array = json_decode($anti_json,true);
        if (empty($anti_array)) {
            Logger::dayLog('service/queryAnti','运营商接口异常',$anti_json,$this->anti_url,$sendData);
            return [];
        }
        if (isset($anti_array['code']) && $anti_array['code'] != '0') {
            Logger::dayLog('service/queryAnti','分析运营商数据异常',$anti_json,$this->anti_url,$sendData);
            return [];
        }
        $anti_data = ArrayHelper::getValue($anti_array,'data',[]);
        return $anti_data;
    }

    private function setAntiParams($data){
        $anti_params = [
            'request_id' => ArrayHelper::getValue($data,'report_id',''),
            'credit_id' => ArrayHelper::getValue($data,'credit_id',''),
            'identity' => ArrayHelper::getValue($data,'identity',''),
            'realname' => ArrayHelper::getValue($data,'realname',''),
            'contact' => ArrayHelper::getValue($data,'relation',[]),
            'phone' => ArrayHelper::getValue($data,'mobile',''),
            'aid' => ArrayHelper::getValue($data,'aid',''),
        ];
        return $anti_params;
    }
    //	数据加密
    private function getSign($data)
    {
    	$str = '';
    	ksort($data);
    	foreach ($data as $k => $v) {
    		$str .= $k.'='.$v.'&';
    	}
    	$str = rtrim($str,'&');
    	$sign = md5(substr(md5($str),0,30).$this->anti_key);
    	return $sign;
    }
    public function antiDetailTag($data){
        if (empty($data) || !is_array($data)) {
            return [];
        }
        $request_id = ArrayHelper::getValue($data,'request_id','');
        $aid = ArrayHelper::getValue($data,'aid','');
        if (!$request_id || !$aid) {
            return [];
        }
        $oAfDetailTag = new AfDetailTag();
        $where  = ['request_id' => $request_id, 'aid' => $aid];
        //获取详单标签数据
        $detail_tag = $oAfDetailTag->getDetailTagByWhere($where);
        return $detail_tag;
    }

    public function antiDetailTagExpress($data){
        $def_data = [
            "express_aeavy_number_lable" => 111,
            "express_weight_loss_label"  => 111,
            "express_aeavy_number_p"     => 111,
            "express_weight_loss_p"      => 111,
            "express_aeavy_number_sign"  => 111,
            "express_weight_loss_sign"   => 111,
        ];
        if (empty($data) || !is_array($data)) {
            return $def_data;
        }
        $request_id = ArrayHelper::getValue($data,'request_id','');
        $aid = ArrayHelper::getValue($data,'aid','');
        if (!$request_id || !$aid) {
            return $def_data;
        }
        $oAfDetailTag = new AfDetailTag();
        $where  = ['request_id' => $request_id, 'aid' => $aid];
        //获取详单标签数据
        $express = $oAfDetailTag->getExpress($where);
        $ret_data = [
            "express_aeavy_number_lable" => ArrayHelper::getValue($express,'aeavy_number_lable',111),
            "express_weight_loss_label"  => ArrayHelper::getValue($express,'weight_loss_label',111),
            "express_aeavy_number_p"     => ArrayHelper::getValue($express,'aeavy_number_proportion',111),
            "express_weight_loss_p"      => ArrayHelper::getValue($express,'weight_loss_proportion',111),
            "express_aeavy_number_sign"  => ArrayHelper::getValue($express,'aeavy_number_sign',111),
            "express_weight_loss_sign"   => ArrayHelper::getValue($express,'weight_loss_sign',111),
        ];
        return $ret_data;
    }
    
    //获取运营商数据
    public function getReportInfo($anti_data)
    {
        $operator = [];
        $request_id = ArrayHelper::getValue($anti_data, 'request_id', 0);
        $aid = ArrayHelper::getValue($anti_data, 'aid', 0);
        $anti_where = ['and', ['request_id' => $request_id], ['aid' => $aid]];
        //原始数据#####################################################
        $address = new Address();
        $address_select = 'addr_tel_count,addr_parents_count,addr_contacts_count,addr_relative_count,addr_count';
        $operator += $address->getPromeData($anti_where, $address_select);

        $contact = new Contact();
        $contact_select = 'com_c_rank,com_c_total,com_r_total,com_r_rank';
        $operator += $contact->getPromeData($anti_where, $contact_select);

        $report = new Report();
        $report_select = 'report_aomen,report_court,report_fcblack,report_shutdown';
        $operator += $report->getPromeData($anti_where, $report_select);

        $report = new AfSsReport();
        $report_select = 'score,consume_fund_index,indentity_risk_index,social_stability_index';
        $operator += $report->getPromeData($anti_where, $report_select);
        #######################
        $detail = new Detail();
        $detail_select = 'com_month_num,com_call_duration,com_month_people,com_days_call,com_hours_answer_davg,com_offen_connect,com_day_connect_mavg,com_night_connect_p,com_tel_people,com_valid_mobile,com_month_call_duration,com_hours_call_davg,vs_phone_match,com_count,com_answer,vs_duration_match';
        $operator += $detail->getPromeData($anti_where, $detail_select);

        $detail_other = new DetailOther();
        $other_select = 'last3_answer,tot_phone_num,shutdown_duration_count,same_phone_num,last3_not_mobile_count,last3_all,last6_not_mobile_count,shutdown_sum_days,total_duration,shutdown_max_days';
        $operator += $detail_other->getPromeData($anti_where, $other_select);

        $other_select = 'phone_register_month';
        $operator += $detail_other->getDetailOther($anti_where, $other_select);
        $operator['phone_register_month'] = $operator['report_use_time'];
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

    protected function returnInfo($result, $info)
    {
        $this->info = $info;
        return $result;
    }
}