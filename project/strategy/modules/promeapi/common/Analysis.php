<?php
/**
 * 运营商报告基类
 */
namespace app\modules\promeapi\common;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\common\Curl;
use app\common\ApiSign;

use app\models\Request;
use app\models\yyy\AntiFraud;
use app\models\yyy\User;
use app\models\yyy\YiFavoriteContacts;
use app\models\antifraud\Address;
use app\models\antifraud\AfAddrLoan;
use app\models\antifraud\Contact;
use app\models\antifraud\Detail;
use app\models\antifraud\Report;
use app\models\antifraud\AfSsReport;
use app\models\antifraud\DetailOther;
use app\models\antifraud\AfBase;
use app\models\open\OpJxlStat;
class Analysis
{
	private $anti_key;	
	private $anti_url;

	function __construct()
    {
    	if (SYSTEM_PROD) {
    		$this->anti_url = "http://100.112.35.139:8081/api/analysis";
    	} else {
    		$this->anti_url = "http://182.92.80.211:8001/api/analysis";
    	}
    	$this->anti_key = 'spLu1bSt3jXPY8ximZUf9k7F';
    }
    //运营商数据（对外）
    public function getAntiInfo($data)
    {
        //请求运营商分析数据
        // $res = $this->queryAnti($data);
        // if (!$res) {
        //     [];
        // }
        // $anti_info = $this->info;
        //获取用户运营商报告ID
        
        if ($data['aid'] == 1) {
            $data['yy_request_id'] = $this->getYyyId($data);
        } else if($data['aid'] == 8) {
            $data['yy_request_id'] = $this->getSfId($data);
        }
        //获取运营商数据
        $report_info = $this->getReportInfo($data);
        //获取常用联系人是否为一亿元用户
        $report_info['com_c_user'] = $this->getComcUser($data['user_id']);
        //获取运营商报告类型
        $report_info['report_type'] = $this->getReportType($data);
        return $report_info;
    }
    //一亿元运营商报告ID
    private function getYyyId($data)
    {
        //获取聚信立请求ID
        $antifraud = new AntiFraud;
        $where = ['user_id'=>$data['user_id'],'loan_id'=>$data['loan_id']];
        $anti_info = $antifraud->getInfo($where, 'user_id,id');
        $anti_id = $anti_info['id'];
        return $anti_id;
    }

    //7-14运营商报告ID
    private function getSfId($data)
    {
        //获取聚信立请求ID
        $antifraud = new SfOperation();
        $where = ['user_id'=>$data['user_id'],'status'=>2];
        $anti_info = $antifraud->getInfo($where, 'user_id,request_id');
        $anti_id = ArrayHelper::getValue($anti_info, 'request_id','0');
        return $anti_id;
    }

    //请求运营商分析报告
    private function queryAnti($data)
    {
    	if (empty($data['add_url']) || !isset($data['add_url'])) {
    		Logger::dayLog('api/queryAnti',$data);
			return $this->returnInfo(false,'通讯录地址不能为空');
    	}
		$address = $this->getAddress($data);
		$operator = $this->getOperator($data);
		$sendData = [
			'request_id' => (string)(isset($data['yy_request_id']) ? $data['yy_request_id'] : ''),
			'user_id'=>(string)(isset($data['identity_id']) ? $data['identity_id'] : ''),
			'loan_id'=>(string)(isset($data['loan_id']) ? $data['loan_id'] : ''),
			'phone'=>(string)(isset($data['phone']) ? $data['phone'] : ''),
			'identity'=>(string)(isset($data['idcard']) ? $data['idcard'] : ''),
			'aid'=>(string)(isset($data['aid']) ? $data['aid'] : ''),
			'address'=>(string)$address,
			'operator'=>(string)$operator,
			'relation'=>(string)(isset($data['relation']) ? $data['relation'] : ''),
		];
		$sign = $this->getSign($sendData);
		$sendData['sign']=$sign;
		$sendData  = json_encode($sendData,JSON_UNESCAPED_UNICODE);
		$a_url = $this->anti_url;
		$res = Curl::dataPost($sendData,$a_url);
		if (isset($res['http_status']) && $res['http_status'] !== 200) {
			Logger::dayLog('api/queryAnti',$res,$a_url,$sendData);
			return $this->returnInfo(false,'请求运营商数据失败');
		}
		$res = $res['result'];
		$res = json_decode($res,true);
		if (empty($res)) {
			Logger::dayLog('api/queryAnti',$res,$a_url,$sendData);
			return $this->returnInfo(false,'获取运营商数据失败');
		}
		if (isset($res['code']) && $res['code'] != '0') {
			Logger::dayLog('api/queryAnti',$res,$a_url,$sendData);
			return $this->returnInfo(false, $res['msg']);
		}
		return $this->returnInfo(true, $res['data']);
    }

    private function getAddress($data,$type = '2')
    {
    	if (SYSTEM_PROD) {
    		//正式
    		$add_data = [
	    		'type'=>$type,
	    		'data'=>isset($data['add_url']) ? $data['add_url'] : '',
	    	];
    	} else {
    		//测试
    		$add_data = [
	    		'type'=>$type,
	    		'data'=>'http://182.92.80.211:8104/mobile/api/phone/index',
	    	];
    	}
    	$add_data = json_encode($add_data, JSON_UNESCAPED_UNICODE);
    	return $add_data;
    }

    private function getOperator($data,$type = '1')
    {
    	$ret = [
	    		'type'=>$type,
	    		'data'=>isset($data['yy_request_id']) ? $data['yy_request_id'] : 0,
    		];
    	return json_encode($ret, JSON_UNESCAPED_UNICODE);
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

    //获取运营商数据
    private function getReportInfo($data)
    {
        $operator = [];
        $request_id = ArrayHelper::getValue($data, 'yy_request_id', 0);
        $operator['yy_request_id'] = $request_id;
        $user_id = ArrayHelper::getValue($data, 'user_id', 0);
        $aid = ArrayHelper::getValue($data, 'aid', 0);
        $anti_where = ['and', ['request_id' => $request_id], ['aid' => $aid],['user_id' => $user_id]];
        //原始数据#####################################################
        $address = new Address();
        $address_select = 'addr_tel_count,addr_parents_count';
        $operator += $address->getPromeData($anti_where, $address_select);

        $contact = new Contact();
        $contact_select = 'com_r_rank';
        $operator += $contact->getPromeData($anti_where, $contact_select);

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
        $detail_select = 'com_day_connect_mavg,com_night_connect_p,com_tel_people,com_valid_mobile,com_month_call_duration,com_hours_call_davg,com_count,com_call,com_answer';
        $operator += $detail->getPromeData($anti_where, $detail_select);

        $detail_other = new DetailOther();
        $other_select = 'tot_phone_num,same_phone_num,last3_not_mobile_count,last3_all,last6_not_mobile_count';
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
        $request_id = ArrayHelper::getValue($data, 'yy_request_id', '');
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
    private function returnInfo($result, $info)
    {
        $this->info = $info;
        return $result;
    }
}