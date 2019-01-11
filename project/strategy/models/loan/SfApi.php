<?php

namespace app\models\loan;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\antifraud\Address;
use app\models\antifraud\Detail;
/**
 * 7-14数据源
 */
class SfApi 
{
	//数据源
    public function getSysInfo($data)
    {
        //获取用户的基本数据
        $user_info = $this->getUserInfo($data);
        if (empty($user_info)) {
        	return $this->error("20101", "用户不存在");
        }

        //获取本次借款数据
        $loan_info = $this->getLoanInfo($data);
        if (empty($loan_info)) {
        	return $this->error("20102", "借款不存在");
        }
        $sys_info = array_merge($user_info, $loan_info);
        //获取用户通讯录数据
        $sys_info += $this->getAntiInfo($data);
        
        //获取复合数据
        $sys_info += $this->getComplex($sys_info);
        
        //本次借款之前客户历史欺诈拒绝次数
        $sys_info += $this->getFrejectNum($data);
        return $this->success($sys_info);
    }

    /**
     * [getUserInfo 获取基本用户数据]
     * @param  [array] $data [description]
     * @return [array]       [description]
     */
    private function getUserInfo($data)
    {
    	$user_id = ArrayHelper::getValue($data, 'user_id');
    	$user = new SfUser();
    	$where = ['user_id'=>$user_id];
        $user_select = 'user_id,identity,mobile,realname';
        $user_info = $user->getInfo($where, $user_select);
        if (empty($user_info)) {
        	return [];
        }
        return $user_info;
    }

    /**
     * [getUserInfo 获取本次借款数据]
     * @param  [array] $data [description]
     * @return [array]       [description]
     */
    private function getLoanInfo($data)
    {
    	$loan_id = ArrayHelper::getValue($data, 'loan_id');
    	$user = new SfLoan();
    	$where = ['loan_id'=>$loan_id];
        $loan_select = 'loan_id,create_time,from_code,end_date';
        $loan_info = $user->getInfo($where, $loan_select);
        if (empty($loan_info)) {
        	return [];
        }

        //本次借款的逾期天数
        $time = substr(date('Y-m-d H:i:s'), 0,10);
        $end_date = substr($loan_info['end_date'], 0,10);
        $due_day = (int)((strtotime($time)-strtotime($end_date))/(60*60*24));

        //本次借款已有还款次数
        $loan_repay = new SfLoanRepaySuccess();
        $repay_cnt = $loan_repay->getRepaycnt($loan_id);

        $retData = [
        	'loan_id' => $loan_info['loan_id'],
        	'loan_create_time' => $loan_info['create_time'],
        	'end_date' => $loan_info['end_date'],
        	'obs_status' => $due_day,
        	'repay_cnt' => $repay_cnt,
            'source' => (int)$loan_info['from_code'],
        	];
        return $retData;
    }

    /**
     * 获取用户通讯录数据
     * @param $res_data
     * @return json
     */
	private function getAntiInfo($data)
	{
		//获取聚信立请求ID
		$antifraud = new SfOperation();
		$where = ['user_id'=>$data['user_id'],'status'=>2];
        $anti_info = $antifraud->getInfo($where, 'user_id,request_id');
        $anti_id = ArrayHelper::getValue($anti_info, 'request_id','0');
        $anti_where = ['and', ['request_id' => $anti_id],['aid'=>$data['aid']], ['user_id' => $data['user_id']]];

        $address = new Address();
        $address_select = 'addr_parents_count,addr_phones_nodups';
        $address_info = $address->getAddress($anti_where, $address_select);

        $detail = new Detail();
        $detail_select = 'com_days_answer,com_day_connect_mavg,com_tel_people,com_month_num';
        $detail_info = $detail->getDetail($anti_where, $detail_select);

        $retData = [
        	'addr_parents_count' => $address_info['addr_parents_count'],
        	'addr_phones_nodups'=> $address_info['addr_phones_nodups'],
        	'com_days_answer'=> $detail_info['com_days_answer'],
        	'com_day_connect_mavg'=> $detail_info['com_day_connect_mavg'],
        	'com_tel_people'=> $detail_info['com_tel_people'],
        	'com_month_num' => $detail_info['com_month_num'],
        	];
        return $retData;
	}
	/**
	 * [getComplex 获取复杂数据]
	 * @param  [array] $data [description]
	 * @return [array] $retData [description]
	 */
	private function getComplex($data)
	{
		$sf_Loan = new SfLoan();
		$ComplexData = $sf_Loan->complexData($data);
		if (empty($ComplexData) || !is_array($ComplexData)) {
			return [];
		}
		return $ComplexData;

	}
	/**
	 * [getFrejectNum 本次借款之前客户历史欺诈拒绝次数]
	 * @param  [array] $data [description]
	 * @return [array] $retData [description]
	 */
	private function getFrejectNum($data)
	{
		$userLoanFlows = new SfLoanEvent();
		$frejectNum = $userLoanFlows->frejectNum($data);
		$retData = ['tot_freject_num' => $frejectNum];
		return $retData;

	}
    /**
     * [UpdateOverloan 更新数据]
     * @param [type] $data [description]
     */
    public function UpdateOverloan($data)
    {   
        $time = date('Y-m-d H:i:s');
        $data->obs_status = $this->getNewobs($data);
        $data->query_time = $time;
        $data->modify_time = $time;
        //本次借款已有还款次数
        $loan_repay = new SfLoanRepaySuccess();
        $data->repay_cnt = $loan_repay->getRepaycnt($data->loan_id);
        $res = $data->save();
        return $res;
    }

    /**
     * [getNewobs 获取最新逾期天数]
     * @return [type] [description]
     */
    private function getNewobs($data)
    {
        $time = substr(date('Y-m-d H:i:s'), 0,10);
        $end_date = substr($data['end_date'], 0,10);
        $due_day = (int)((strtotime($time)-strtotime($end_date))/(60*60*24));
        return $due_day;
    }

    /**
     * 返回成功json
     * @param $res_data
     * @return json
     */
    private function success($res) {
        if (is_array($res)) {
            $res['res_code'] = '0';
        } else {
            $res = [
                'res_code' => '0',
                'res_data' => $res,
            ];
        }
        return $res;
        //return json_encode($res, JSON_UNESCAPED_UNICODE);
    }
    /**
     * 返回错误json
     * @param $res_code
     * @param $res_data
     * @return json
     */
    private function error($rsp_code, $res_data) {
        return [
            'res_code' => (string) $rsp_code,
            'res_data' => $res_data,
        ];
    }
}
