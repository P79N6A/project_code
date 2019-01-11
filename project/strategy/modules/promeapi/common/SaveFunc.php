<?php
/**
 * 保存数据基类
 */
namespace app\modules\promeapi\common;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;

use app\models\Request;
use app\models\Loan;
use app\models\Stuser;
use app\models\StProme;
use app\models\Result;
use app\models\StloanExtend;

class SaveFunc 
{
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
     * [saveReg 记录普罗米模型信息]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */    
    public function saveProme($data)
    {
        $prome = new StProme;
        $res = $prome->addPromeInfo($data);
        if (!$res) {
            Logger::dayLog('prome','用户记录失败', $prome->errors,$data);
            return false;
        }
        return $res;
    }
        
}