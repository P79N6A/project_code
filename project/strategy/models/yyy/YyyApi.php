<?php

namespace app\models\yyy;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\antifraud\Address;
use app\models\antifraud\Detail;
use app\models\Overdueloan;
use app\models\credit\YxUserCredit;
use app\common\Logger;
/**
 * 一亿元数据源
 */
class YyyApi 
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
        $sys_info = array_merge($user_info,$loan_info);
        //用户历史成功次数
        $loan_extend = $this->sysLoanExtend($sys_info);

        //yi_sure数据
        $loan_sure = $this->getYisure($sys_info);

        $sys_info = array_merge($sys_info, $loan_extend,$loan_sure);
        //获取用户通讯录数据
        $sys_info += $this->getAntiInfo($sys_info);
        
        //获取复合数据
        $sys_info += $this->getComplex($sys_info);
        
        //本次借款之前客户历史欺诈拒绝次数
        $sys_info += $this->getFrejectNum($data);
        return $this->success($sys_info);
    }
    //贷后附属信息
    public function sysLoanExtend($data)
    {
        $user_id = ArrayHelper::getValue($data, 'user_id');
        $parent_loan_id = ArrayHelper::getValue($data, 'parent_loan_id');
        $extend_select = 'loan_total,success_num';
        $where = ['user_id' => $user_id,'loan_id' => $parent_loan_id];
        $loan_extend = $this->getLoanExtendOther($where,$extend_select);
        return $loan_extend; 
    }

    /**
     * [getUserInfo 获取基本用户数据]
     * @param  [array] $data [description]
     * @return [array]       [description]
     */
    public function getUserInfo($data)  
    {
    	$user_id = ArrayHelper::getValue($data, 'user_id');
    	$user = new User();
    	$where = ['user_id'=>$user_id];
        $user_select = 'user_id,identity,mobile,realname,come_from,telephone,create_time';
        $user_info = $user->getInfo($where, $user_select);
        if (empty($user_info)) {
        	return [];
        }
        return $user_info;
    }

    //yi_sure
    public function getYisure($data)
    {
        $allData = [
                'type_insure' => '',
                'money_insure' => '',
                'actual_money_insure' => '',
            ];
        $user_id = ArrayHelper::getValue($data, 'user_id','');
        $parent_loan_id = ArrayHelper::getValue($data, 'parent_loan_id','');
        if (empty($user_id) || empty($parent_loan_id)) {
            return $allData;
        }
        $where = ['user_id' => $user_id,'loan_id' => $parent_loan_id,'status' => 1];
        $a_where = ['user_id' => $user_id,'loan_id' => $parent_loan_id,'status' => 1,'type' => 1];
        $yiSure = new YiInsure();
        $loan_yisure = $yiSure->getYisureData($where);
        $yiSurance = new YiInsurance();
        $loan_yisurance = $yiSurance->getYisureData($a_where);
        if (empty($loan_yisure) && empty($loan_yisurance)) {
            return $allData;
        }
        $allData = [
            'type_insure' => isset($loan_yisure['type']) ? (float)$loan_yisure['type'] : 0,
            'money_insure' =>isset($loan_yisurance['money']) ? (float)$loan_yisurance['money'] : 0,
            'actual_money_insure' => isset($loan_yisure['actual_money']) ? (float)$loan_yisure['actual_money'] : 0,
        ];
        return $allData;
    }

    /**
     * [getUserInfo 获取本次借款数据]
     * @param  [array] $data [description]
     * @return [array]       [description]
     */
    private function getLoanInfo($data)
    {
        $loan_id = ArrayHelper::getValue($data, 'loan_id','');
        if (empty($loan_id)) {
            return [];
        }
    	//本次借款信息
        $loan_info = $this->userLoanInfo($data);
        if (empty($loan_info)) {
            return [];
        }
        
        //本次借款的逾期天数
        $time = substr(date('Y-m-d H:i:s'), 0,10);
        $end_date = substr($loan_info['end_date'], 0,10);
        $due_day = (int)((strtotime($time)-strtotime($end_date))/(60*60*24));

        //本次借款已有还款次数
        $loan_repay = new LoanRepay();
        $repay_cnt = $loan_repay->getRepaycnt($loan_id);

        $retData = [
        	'loan_id' => $loan_info['loan_id'],
            'parent_loan_id' => $loan_info['parent_loan_id'],
        	'loan_create_time' => $loan_info['create_time'],
        	'end_date' => $loan_info['end_date'],
        	'obs_status' => $due_day,
            'source'=>(int)$loan_info['source'],
        	'repay_cnt' => $repay_cnt,
        	];    
        $retData = array_merge($loan_info,$retData);
        return $retData;
    }
    // 贷后借款数据
    public function userLoanInfo($data)
    {
        $user = new UserLoan();
        $loan_id = ArrayHelper::getValue($data, 'loan_id','');
        $where = ['loan_id'=>$loan_id];
        $loan_select = 'loan_id,parent_loan_id,create_time,source,end_date,repay_time,number,settle_type,amount,withdraw_fee,days';
        $loan_info = $user->getInfo($where, $loan_select);
        if (empty($loan_info)) {
            return [];
        }
        return $loan_info;
    }
    /**
     * 获取用户通讯录数据
     * @param $res_data
     * @return array
     */
	private function getAntiInfo($data)
	{
		//获取聚信立请求ID
		$antifraud = new AntiFraud;
		$where = ['user_id'=>$data['user_id'],'loan_id'=>$data['parent_loan_id']];
        $anti_info = $antifraud->getInfo($where, 'user_id,id');
        $anti_id = $anti_info['id'];

        $anti_where = ['and', ['request_id' => $anti_id],['aid'=>'1'], ['user_id' => $data['user_id']]];
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
		$user_loan = new UserLoan();
		$ComplexData = $user_loan->complexData($data);
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
		$userLoanFlows = new UserLoanFlows();
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
        $over_loan = new Overdueloan();
        $user_id = ArrayHelper::getValue($data, 'user_id', '');
        $aid = ArrayHelper::getValue($data, 'aid', '');
        $loan_id = ArrayHelper::getValue($data, 'loan_id', '');
        $request_id = ArrayHelper::getValue($data, 'request_id', '');
        $where = ['and',
            ['loan_id'=>$loan_id], 
            ['user_id' => $user_id],
            ['aid'=>$aid], 
        ];
        $loan_sys = $over_loan->find()->where($where)->limit(1)->orderBy('ID DESC')->one();
        $time = date('Y-m-d H:i:s');
        if (!empty($request_id)) {
            $loan_sys->request_id = $request_id;
        }
        $loan_sys->obs_status = $this->getNewobs($loan_sys);
        $loan_sys->query_time = $time;
        $loan_sys->modify_time = $time;
        $loan_repay = new LoanRepay();
        $loan_sys->repay_cnt = $loan_repay->getRepaycnt($data['loan_id']);
        $res = $loan_sys->save();
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
     * [getTotal 一亿元申请次数]
     * @return [type] [description]
     */
    public function getTotal($user_id)
    {
        $user = new UserLoan();
        $where = ['user_id'=>$user_id];
        $total_count = $user->getTotalCount($where);
        return $total_count;
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
    //获取用户借款信息
    public function getLoanData($data,$loan_select)
    {
        $loan_id = ArrayHelper::getValue($data, 'loan_id');
        $user_id = ArrayHelper::getValue($data, 'user_id');
        $user = new UserLoan();
        $where = ['loan_id' => $loan_id,'user_id' => $user_id];
        $loan_info = $user->getInfo($where, $loan_select);
        if (empty($loan_info)) {
            return [];
        }
        return $loan_info;
    }
    //判断用户类型 type=1，为初贷；=2为复贷；
    public function getUserType($user_id)
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
    //获取借款附属表数据
    public function getLoanExtend($data,$loan_select)
    {
        $loan_id = ArrayHelper::getValue($data, 'loan_id');
        $user_id = ArrayHelper::getValue($data, 'user_id');
        $userLoanExtend = new UserLoanExtend();
        $where = ['loan_id' => $loan_id,'user_id' => $user_id];
        $loan_extend = $userLoanExtend->getInfo($where, $loan_select);
        if (empty($loan_extend)) {
            return [];
        }
        return $loan_extend;

    }

    //获取借款附属表数据
    public function getLoanExtendOther($where,$loan_select)
    {
        $userLoanExtend = new UserLoanExtend();
        $loan_extend = $userLoanExtend->getLoanExtend($where,$loan_select);
        return $loan_extend;
    }

    /**
     * [getTerm 查询用户是否为分期用户]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */       
    public function getTerm($data)
    {
        $term = 0;
        $user_id = ArrayHelper::getValue($data, 'user_id');
        $where = ['user_id' => $user_id];
        $yiTerm = new YiTerm();
        $res = $yiTerm->getTerm($where);
        if (!empty($res)) {
            $term = 1;
        }
        return $term;
    }

    //获取用户额度 (暂废弃)
    public function getQuota($where,$select)
    {
        $userQuota = new UserQuota();
        $quota = $userQuota->getUserQuota($where,$select);
        return $quota;
    }
    //获取用户额度
    public function getTemQuota($where,$select)
    {
        $temQuota = new YiTemQuota();
        $quota = $temQuota->getTemQuota($where,$select);
        return $quota;
    }

    public function getHistoryData($data)
    {
        $user_id = ArrayHelper::getValue($data, 'user_id');
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
     * 获取用户信息 
     * @param $user_id
     * @return array|bool
     * @todo [修改 source uuid IP 查询地址]
     */
    public function getUserInfoAll($data_set)
    {
        if (empty($data_set)){
            return [];
        }
        $user_id = ArrayHelper::getValue($data_set, 'user_id');
        //user表信息
        $oUser = new User();
        $user_info = $oUser->getUser(['user_id'=>$user_id]);
        if (empty($user_info)) {
            Logger::dayLog('YyyApi/getUserInfoAll',$data_set,'用户不存在');
            return []; 
        }
        $user_extend = $user_info->userExtend;
        //用户常用联系人
        // $oFavoriteContacts = new YiFavoriteContacts();
        // $favorite_ifno = $oFavoriteContacts->getFavorite($user_id);
        //地址
        $oAddress = new \app\models\yyy\Address();
        $address_info = $oAddress->getAddressByUserId($user_id);
        //source device IP
        $user_credit = $this->getUserCreditByReqid($data_set);
        $source = ArrayHelper::getValue($user_credit, 'device_type','0');
        $uesr_data = [
            'user_id'=> ArrayHelper::getValue($user_info, 'user_id'),// 一亿元 user_id
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
            'source' => empty($source) ? '0' : (string)$source, //来源ios,android,web,....
            'token_id' => ArrayHelper::getValue($user_credit, 'device_tokens'),// app编号
            'aid' => ArrayHelper::getValue($data_set, 'aid'),
            'req_id' => ArrayHelper::getValue($data_set, 'req_id'),
            'come_from' => ArrayHelper::getValue($user_info, 'come_from'),
            // 公司与学校信息
            'company_name' =>  ArrayHelper::getValue($user_extend, 'company'),
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
            // 'yy_request_id' => ArrayHelper::getValue($data_set, 'req_id'),//运营商报告请求ID
            // 'relation' => json_encode([
            //     [
            //         'name'=>ArrayHelper::getValue($favorite_ifno, 'contacts_name'),
            //         'mobile'=>ArrayHelper::getValue($favorite_ifno, 'mobile'),
            //         'relation'=>ArrayHelper::getValue($favorite_ifno, 'relation_common'),
            //     ],
            //     [
            //         'name'=>ArrayHelper::getValue($favorite_ifno, 'relatives_name'),
            //         'mobile'=>ArrayHelper::getValue($favorite_ifno, 'phone'),
            //         'relation'=>ArrayHelper::getValue($favorite_ifno, 'relation_family'),
            //     ],
            // ]),
        ];
        return array_merge($data_set,$uesr_data);
    }

    private function getUserCreditByReqid($data)
    {
        $oUserCredit = new YxUserCredit();
        $req_id = ArrayHelper::getValue($data, 'id');
        $where = ['req_id'=>$req_id];
        $user_credit = $oUserCredit->getUserCredit($where);
        return $user_credit;
    }
}
