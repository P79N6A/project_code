<?php

namespace app\modules\sfapi\controllers;

use app\common\ApiSign;
use app\common\Logger;
use app\models\Loan;
use app\models\Request;
use app\models\Result;
use app\models\yyy\AntiFraud;
use app\models\yyy\Juxinli;
use app\models\yyy\UserHistoryInfo;
use app\models\yyy\UserLoan;
use app\models\TmpBlack;
use app\models\yyy\UserLoanExtend;
use app\models\yyy\UserQuotaRecord;
use app\models\StloanExtend;
use app\models\yyy\WhiteList;
use app\modules\sfapi\logic\LoanLogic;
use app\modules\sfapi\common\CloudApi;
use Yii;
use yii\helpers\ArrayHelper;

// use app\models\Loanone;
// use app\models\Loantwo;
class LoanController extends ApiController
{
    const LOAN_ONE = 2;//借款决策
    const LOAN_TWO = 3;//反欺诈+评分卡
    const PRO_CODE_LOAN = 'xhh_loan_1';
    const PRO_CODE_FRAUD = 'xhh_loan_2';
    const PRO_CODE_SCORE = 'xhh_reloan';

    private $test_data;
    // public function init()
    // {

    // }
    //借款决策
    public function actionQueryloan()
    {
        $postdata = $this->postdata;

        if (!is_array($postdata) || empty($postdata)) {
            Logger::dayLog('loan_error', 'postdata', '数据异常', $postdata);
            return $this->error('20001', '数据异常',$postdata,3);
        }
        Logger::dayLog('loan_one', 'postdata', $postdata);
        $postdata['from'] = self::LOAN_ONE;
        //验证请求唯一性 如存在则返回上一结果
        $request = new Request();
        $req = $request->getReqInfo($postdata);
        if ($req) {
            Logger::dayLog('addRequest', 'addRequest',$postdata);
            return $this->success($postdata, '', $req);
        }
        //记录请求
        $request = new Request();
        $request_id = $request->saveRequest($postdata);
        if (!$request_id) {
            Logger::dayLog('loan_error', 'addRequest', $postdata);
            return $this->error('20002', '请求记录失败',$postdata,3);
        }
        $postdata['request_id'] = $request_id;
        $loan_id = ArrayHelper::getValue($postdata, 'loan_id');
        $loan_no = ArrayHelper::getValue($postdata, 'loan_no');
        $user_id = ArrayHelper::getValue($postdata, 'identity_id');
        //是否重复请求
        // if (empty($loan_id)) {
        //     $req_where = ['and', ['user_id' => $user_id], ['loan_no' => $loan_no]];
        // } else {
        //     $req_where = ['and', ['user_id' => $user_id], ['loan_id' => $loan_id]];
        // }
        $loan = new Loan();
        // $res = $loan->getInfo($req_where);
        // if (!empty($res)) {
        //     Logger::dayLog('loan_error', '重复请求', $postdata);
        //     return $this->error('20009', '重复请求',$postdata,3);
        // }
        //获取所需数据
        $loanlogic = new LoanLogic($postdata);
        $loanOne = $loanlogic->getLoanOneInfo($postdata);
        $loan_info = $loanlogic->info;
        if (!$loanOne) {
            Logger::dayLog('loan_error', 'getLoanOneInfo', $loan_info, $postdata);
            return $this->error('20003', $loanlogic->info,$postdata,3);
        }
        //记录借款信息
        $loan_info['last_step'] = 1;
        // 催收黑名单
        $cloud_api = new CloudApi();
        $loan_info['id_collection_black'] = $cloud_api->getForeignBlackIdcard($loan_info['identity']);
        $loan_info['ph_collection_black'] = $cloud_api->getForeignBlackPhone($loan_info['mobile']);
        $res = $loan->addLoanInfo($loan_info);
        if (!$res) {
            Logger::dayLog('addLoanInfo', '记录失败', $loan->errors);
            return $this->error('20010', '记录失败',$postdata,3);
        }
        $loan_data = [
            'request_id' => $request_id,
            'process_code' => self::PRO_CODE_LOAN,
            'params_data' => $loan_info,
        ];
        $api = new \app\modules\api\common\BaseApi();
        $result = $api->sendRequest($loan_data);
        if (empty($result)) {
            Logger::dayLog('loan_error', 'result', '决策结果为空', $result, $postdata);
            return $this->error('20004', '决策结果为空',$postdata,3);
        }
        if (isset($result['res_code']) && $result['res_code'] != 0) {
            Logger::dayLog('loan_error', 'result', '决策异常', $result, $postdata);
            return $this->error('20005', '决策异常',$postdata,3);
        }
        $res_data = ArrayHelper::getValue($result, 'LOAN_RESULT');
        //记录结果
        $record_res = new Result();
        $res = $record_res->saveRes($postdata, $result);
        if (!$res) {
            Logger::dayLog('error', 'result','结果记录失败:',$result,$postdata);
            return $this->error('20006', '结果记录失败',$postdata,1);
        }
        return $this->success($postdata, $loan_info, $res_data);
    }

    //反欺诈决策+评分卡
    public function actionAntifraud()
    {
        $postdata = $this->postdata;
        if (empty($postdata) || !is_array($postdata)) {
            Logger::dayLog('reloan', 'postdata', '数据异常', $postdata);
            return $this->error('20001', '数据异常',$postdata,3);
        }
        Logger::dayLog('reloan/postdata', 'postdata', $postdata);
        $postdata['from'] = self::LOAN_TWO;
        //验证请求唯一性 如存在则返回上一结果
        $request = new Request();
        $req = $request->getReqInfo($postdata);
        if ($req) {
            Logger::dayLog('addRequest', 'addRequest',$postdata);
            return $this->success($postdata, '', $req);
        }
        //记录请求
        $request = new Request();
        $request_id = $request->saveRequest($postdata);
        if (!$request_id) {
            Logger::dayLog('reloan/saveRequest', 'saveRequest', $postdata,$request->errors);
            return $this->error('20002', '请求记录失败',$postdata,3);
        }
        if ($postdata['aid'] == 9) {
            return $this->error('0000', 'success',$postdata,3);
        }
        $postdata['request_id'] = $request_id;
        //获取决策数据
        $loanlogic = new LoanLogic();
        $loanOne = $loanlogic->getLoanOneInfo($postdata);
        $loan_info = $loanlogic->info;
        if (!$loanOne) {
            Logger::dayLog('reloan/getLoanOneInfo', 'getLoanOneInfo', $loan_info, $postdata);
            return $this->error('20003', $loanlogic->info,$postdata,3);
        }

        $loanTwo = $loanlogic->getLoanTwoInfo($postdata);
        if (!$loanTwo) {
            Logger::dayLog('reloan/getLoanTwoInfo', 'getLoanTwoInfo', $loanlogic->info, $postdata);
            return $this->error('20003', $loanlogic->info,$postdata,3);
        }
        $loan_info += $loanlogic->info;
        $loan_info += $loanlogic->getAntiInfo($postdata);
        //记录借款信息
        $loan = new Loan();
        $loan_info['last_step'] = 2;
        $res = $loan->addLoanInfo($loan_info);
        if (!$res) {
            Logger::dayLog('reloan/addLoanInfo', '记录失败',$loan_info, $loan->errors);
            return $this->error('20010', '记录失败',$postdata,3);
        }
        //记录借款附属信息
        $st_loan_extend = new StloanExtend();
        $ex_res = $st_loan_extend->addInfo($loan_info);
        if (!$ex_res) {
            Logger::dayLog('reloan/addExtendInfo', '附属记录失败', $st_loan_extend->errors);
            return $this->error('20010', '附属记录失败',$postdata,3);
        }
        //反欺诈决策
        $process_code = self::PRO_CODE_FRAUD;
        ###################################################
        $tmp_black = new TmpBlack();
        $where = ['user_id'=> $loan_info['user_id']];
        $loan_info['is_black_tem'] = $tmp_black->getTmpbBlack($where) > 0 ? 1 : 0;
        // 催收黑名单
        $cloud_api = new CloudApi();
        $loan_info['id_collection_black'] = $cloud_api->getForeignBlackIdcard($loan_info['identity']);
        $loan_info['ph_collection_black'] = $cloud_api->getForeignBlackPhone($loan_info['mobile']);
        ###################################################
        $res = $loanlogic->queryCrif($request_id,$loan_info,$process_code);
        if (!$res) {
            Logger::dayLog('reloan/queryCrif', $process_code, $loanlogic->info, $loan_info);
            return $this->error('20005', $loanlogic->info, $postdata,3);
        }
        $res_fraud = $loanlogic->info;
        //评分卡决策
        if (SYSTEM_PROD) {
            $process_code = self::PRO_CODE_SCORE;
        } else {
            $process_code = 'xhh_reloan_1';
        }
        $res_reloan = $loanlogic->queryCrif($request_id,$loan_info,$process_code);
        if (!$res) {
            Logger::dayLog('reloan/queryCrif', $process_code, $loanlogic->info, $loan_info);
            return $this->error('20005', $loanlogic->info,$postdata,3);
        }
        $res_score = $loanlogic->info;
        //标准化结果参数
        $result = $this->normalRes($res_fraud,$res_score);
        //记录结果
        $res_data = ArrayHelper::getValue($result, 'LOAN_RESULT');
        $record_res = new Result();
        $res = $record_res->saveRes($postdata, $result);
        if (!$res) {
            Logger::dayLog('error', 'result','结果记录失败:',$result,$postdata);
            return $this->error('20006', '结果记录失败',$postdata,1);
        }
        return $this->success($postdata, $loan_info, $res_data);
    }

    private function normalRes($res_one, $res_two)
    {
        $one_status = ArrayHelper::getValue($res_one, 'LOAN_RESULT', 0);
        $two_status = ArrayHelper::getValue($res_two, 'LOAN_RESULT', 0);
        $result = [
                'res_fraud'=>$res_one,
                'res_score'=>$res_two
                ];
        if ($one_status == '1' && $two_status == '1') {
            $result['LOAN_RESULT'] = '1';
        } else {
            $result['LOAN_RESULT'] = '3';
        }
        return $result;
    }
}