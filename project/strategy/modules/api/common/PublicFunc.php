<?php
/**
 * 接口基类
 */
namespace app\modules\api\common;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;

use app\models\Request;
use app\models\Loan;
use app\models\Stuser;
use app\models\Result;
use app\models\StloanExtend;
use app\models\StAntiLoan;

class PublicFunc {
	private $wsdl_url;
	private $anti_key;	
	private $cloud_url;
	private $anti_url;

	function __construct()
    {
    	if (SYSTEM_PROD) {
    		$this->wsdl_url = "http://localhost:8091/ws/S1Public?wsdl";
    		$this->cloud_url = "http://100.112.35.139:8082/api/";
    		$this->anti_url = "http://100.112.35.139:8081/api/analysis";
    	} else {
    		$this->wsdl_url = "http://47.93.121.86:8092/ws/S1Public?wsdl";
    		$this->cloud_url = "http://182.92.80.211:8082/api/";
    		$this->anti_url = "http://182.92.80.211:8001/api/analysis";
    	}
    	$this->anti_key = 'spLu1bSt3jXPY8ximZUf9k7F';
    }

    //记录请求
    public function addRequest($data)
    {
    	$request = new Request();
        $request_id = $request->saveRequest($data);
        if (!$request_id) {
            Logger::dayLog('addRequest', 'addRequest',$request->errors,$data);
            return 0;
        }
        return $request_id;
    }
	
	//记录用户数据借款数据
	public function saveLoan($data)
	{
		
		$data['request_id_one'] = $data['request_id'];
        $data['request_id_two'] = $data['request_id'];
		$loan = new Loan();
        $res = $loan->addLoanInfo($data);
        if (!$res) {
            Logger::dayLog('saveLoan', '记录失败',$data, $loan->errors);
            return false;
        }
        return true;
	}

	//记录决策结果
	public function saveRes($data,$result)
	{
		//记录结果
        $res_data = ArrayHelper::getValue($result, 'LOAN_RESULT');
        $record_res = new Result();
        $res = $record_res->saveRes($data, $result);
        if (!$res) {
            Logger::dayLog('saveRes', 'result','结果记录失败:',$record_res->errors,$result,$data);
            return false;
        }
        return true;
	}
	//标准化借款决策参数
    public function normalLoanData($data)
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

    //标准化注册决策参数
    public function normalRegData($data)
    {
        $ret_info = [
            'user_id'=>isset($data['identity_id']) ? $data['identity_id'] : 0,
            'identity'=>isset($data['idcard']) ? $data['idcard'] : '',
            'mobile'=>isset($data['phone']) ? $data['phone'] : '',
            'realname'=>isset($data['name']) ? $data['name'] : '',
            'reg_time'=>isset($data['reg_time']) ? $data['reg_time'] : '',
            'request_id'=>isset($data['request_id']) ? $data['request_id'] : 0,
            'query_time'=>date('Y-m-d H:i:s'),
            'basic_id'=>isset($data['basic_id']) ? $data['basic_id'] : 0,
            'rsp_code'=>isset($data['rsp_code']) ? $data['rsp_code'] : 0,
            'rsp_msg'=>isset($data['rsp_msg']) ? $data['rsp_msg'] : 0,
            'prd_type'=>isset($data['aid']) ? $data['aid'] : 1,
            'from'=>isset($data['from']) ? $data['from'] : 0,
        ];
        return $ret_info;
    }
    /**
     * [saveScoreData 记录评分卡数据]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function saveScoreData($data)
    {
        //记录借款附属信息
        $st_loan_extend = new StloanExtend();
        $ex_res = $st_loan_extend->addInfo($data);
        if (!$ex_res) {
            Logger::dayLog('reloan/addExtendInfo', '附属记录失败', $data,$st_loan_extend->errors);
            return false;
        }
        return true;
    }
    /**
     * [saveReg 记录用户注册信息]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function saveReg($data)
    {
        $record_user = new Stuser;
        $res = $record_user->addUserInfo($data);
        if (!$res) {
            Logger::dayLog('reg','用户记录失败', $record_user->errors,$data);
            return false;
        }
        return $res;
    }
        /**
     * [saveReg 记录用户反欺诈信息]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function saveAntiData($data)
    {
        $record_anti = new StAntiLoan();
        $res = $record_anti->saveAnti($data);
        if (!$res) {
            Logger::dayLog('antifraud','记录失败', $record_anti->errors,$data);
            return false;
        }
        return $res;
    }
        
}