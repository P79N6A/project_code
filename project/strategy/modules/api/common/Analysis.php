<?php
/**
 * 运营商报告基类
 */
namespace app\modules\api\common;

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
        
        // if ($data['aid'] == 1) {
        //     $data['yy_request_id'] = $this->getYyyId($data);
        // } else if($data['aid'] == 8) {
        //     $data['yy_request_id'] = $this->getSfId($data);
        // }
        //获取运营商数据
        $report_info = $this->getReportInfo($data);
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

    //请求运营商分析报告(暂时不用)
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
    //(暂时不用)
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
    //(暂时不用)
    private function getOperator($data,$type = '1')
    {
    	$ret = [
	    		'type'=>$type,
	    		'data'=>isset($data['yy_request_id']) ? $data['yy_request_id'] : 0,
    		];
    	return json_encode($ret, JSON_UNESCAPED_UNICODE);
    }

    //	数据加密    //(暂时不用)
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
        $request_id = ArrayHelper::getValue($data, 'req_id', 0);
        $user_id = ArrayHelper::getValue($data, 'user_id', 0);
        $aid = ArrayHelper::getValue($data, 'aid', 0);
        $anti_where = ['and', ['request_id' => $request_id], ['aid' => $aid],['user_id' => $user_id]];
        $address = new Address();
        $address_select = 'addr_contacts_count,addr_relative_count,addr_count,addr_collection_count';
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

    protected function returnInfo($result, $info)
    {
        $this->info = $info;
        return $result;
    }
}