<?php

namespace app\modules\api\controllers;

use app\common\ApiSign;
use app\common\Logger;
use app\models\Loan;
use app\models\StloanExtend;
use app\models\Request;
use app\models\Result;
use app\models\TmpBlack;
use app\models\StBaseApi;
use app\models\yyy\AntiFraud;
use app\models\yyy\AntiCrif;
use app\models\yyy\Juxinli;
use app\models\yyy\UserHistoryInfo;
use app\models\yyy\UserLoan;
use app\models\yyy\UserLoanExtend;
use app\models\yyy\UserQuotaRecord;
use app\models\yyy\WhiteList;
use app\modules\api\logic\LoanLogic;
use app\modules\api\common\CloudApi;
use Yii;
use yii\helpers\ArrayHelper;

// use app\models\Loanone;
// use app\models\Loantwo;
class LoanController extends ApiController
{
    const LOAN_ONE = 2;
    const LOAN_TWO = 3;
    const RELOAN = 4;

    public function init()
    {

    }

    //借款决策
    public function actionLoanone()
    {
        $datas = $this->post();
        Logger::dayLog('init', 'postdata', $datas);
        if (!is_array($datas) || !isset($datas['data']) || !isset($datas['_sign'])) {
            Logger::dayLog('datas', 'datas', '数据异常！', $datas);
            return $this->resp(2, 'Reject');
        }
        $isVerify = (new ApiSign)->verifyData($datas['data'], $datas['_sign']);
        if (!$isVerify) {
            Logger::dayLog('datas', 'datas', '验签失败！', $datas);
            return $this->resp(2, 'Reject');
        }
        $postdata = json_decode($datas['data'], true);

        // $postdata = $this->postdata;
        if (!is_array($postdata) || empty($postdata)) {
            Logger::dayLog('postdata', 'postdata', '数据异常', $postdata);
            return $this->resp(2, 'Reject');
        }
        $user_id = ArrayHelper::getValue($postdata, 'user_id');
        $loan_no = ArrayHelper::getValue($postdata, 'loan_no');
        $loan_id = ArrayHelper::getValue($postdata, 'loan_id', 0);
        Logger::dayLog('loan_one', 'postdata', $postdata);
        $postdata['from'] = self::LOAN_ONE;
        $postdata['aid'] = 1;
        //记录请求
        $request = new Request();
        $request_id = $request->addRequest($postdata);
        if (!$request_id) {
            Logger::dayLog('loan_error', 'addRequest', $postdata);
            return $this->resp(2, 'Reject');
        }
        //是否重复请求
        $req_where = ['and', ['user_id' => $user_id], ['loan_no' => $loan_no]];
        $loan = new Loan();
        $res = $loan->getInfo($req_where);
        if (!empty($res)) {
            Logger::dayLog('loan_error', '重复请求', $postdata);
            return $this->resp(2, 'Reject');
        }
        //判断用户是否在一小时有决策结果
        // $record_res = new Result();
        // $onehour = $record_res->getOneHour($user_id);
        // if (!empty($onehour)) {
        //     if ($onehour->res_status == 3) {
        //         Logger::dayLog('loan_error', '已决策2', $postdata);
        //         return $this->resp(2, 'Reject');
        //     }
        //     Logger::dayLog('loan_error', '已决策0', $postdata);
        //     return $this->resp(0, 'Pass');
        // }
        //获取所需数据
        $loanlogic = new LoanLogic($postdata);
        $loanOne = $loanlogic->getLoanOneInfo($postdata);
        $loan_info = $loanlogic->info;
        if (!$loanOne) {
            Logger::dayLog('loan_error', 'getLoanOneInfo', $loan_info, $postdata);
            return $this->resp(2, 'Reject');
        }
        $loan_info['request_id_one'] = $request_id;
        // 催收黑名单
        $cloud_api = new CloudApi();
        $loan_info['id_collection_black'] = $cloud_api->getForeignBlackIdcard($loan_info['identity']);
        $loan_info['ph_collection_black'] = $cloud_api->getForeignBlackPhone($loan_info['mobile']);
        //记录借款信息
        $res = $loan->addLoanInfo($loan_info);
        if (!$res) {
            Logger::dayLog('addLoanInfo', '记录失败', $loan->errors);
            return $this->resp(2, 'Reject');
        }
        $loan_data = [
            'request_id' => $request_id,
            'process_code' => 'xhh_loan_1',
            'params_data' => $loan_info,
        ];
        $api = new \app\modules\api\common\BaseApi();
        $result = $api->sendRequest($loan_data);
        if (empty($result)) {
            Logger::dayLog('loan_error', 'result', '返回结果为空', $result, $postdata);
            return $this->resp(2, 'Reject');
        }
        if (isset($result['res_code']) && $result['res_code'] != 0) {
            Logger::dayLog('loan_error', 'result', '决策异常', $result, $postdata);
            return $this->resp(2, 'Reject');
        }
        $ret_info = json_encode($result, JSON_UNESCAPED_UNICODE);
        //记录结果
        $res_data = ArrayHelper::getValue($result, 'LOAN_RESULT');
        $res_info = [
            'request_id' => $request_id,
            'from' => self::LOAN_ONE,
            'res_info' => $ret_info,
            'res_status' => $res_data,
            'user_id' => $user_id,
            'loan_no' => $loan_no,
            'loan_id' => $loan_id
        ];
        $record_res = new Result();
        $res = $record_res->addResInfo($res_info);
        if (!$res) {
            Logger::dayLog('loan_error', 'result', '结果记录失败:', $result, $postdata);
            return $this->resp(2, 'Reject');
        }
        switch ($res_data) {
            case 1:
                return $this->resp(0, 'Pass');
                break;
            case 2:
                return $this->resp(1, 'Manual');
                break;
            case 3:
                return $this->resp(2, 'Reject');
                break;
            default:
                return $this->resp(2, 'Reject');
        }
    }
    //反欺诈决策
    public function actionLoantwo()
    {
        $datas = $this->post();
        Logger::dayLog('init', 'postdata', $datas);
        if (!is_array($datas) || !isset($datas['data']) || !isset($datas['_sign'])) {
            Logger::dayLog('datas', 'datas', '数据异常！', $datas);
            return $this->resp(2, 'Reject');
        }
        $isVerify = (new ApiSign)->verifyData($datas['data'], $datas['_sign']);
        if (!$isVerify) {
            Logger::dayLog('datas', 'datas', '验签失败！', $datas);
            return $this->resp(2, 'Reject');
        }
        $postdata = json_decode($datas['data'], true);
        // $postdata = $this->postdata;
        if (!is_array($postdata) || empty($postdata)) {
            Logger::dayLog('postdata', 'postdata', '数据异常', $postdata);
            return $this->resp(2, 'Reject');
        }
        $loan_id = ArrayHelper::getValue($postdata, 'loan_id');
        $user_id = ArrayHelper::getValue($postdata, 'user_id');
        Logger::dayLog('loan_two', 'postdata', $postdata);
        $postdata['from'] = self::LOAN_TWO;
        $postdata['aid'] = 1;
        //记录请求
        $request = new Request();
        $request_id = $request->addRequest($postdata);
        if (!$request_id) {
            Logger::dayLog('loan_error2', 'addRequest', $postdata);
            return $this->resp(2, 'Reject');
        }
        $postdata['request_id'] = $request_id;
        //获取决策数据
        $loanlogic = new LoanLogic($postdata);
        $loanTwo = $loanlogic->getLoanTwoInfo($postdata);
        if (!$loanTwo) {
            Logger::dayLog('loan_error2', 'getLoanTwoInfo', $loanlogic->info, $postdata);
            return $this->resp(2, 'Reject');
        }
        $loan_info = $loanlogic->info;
        //记录借款信息
        $loan = new Loan();
        $res = $loan->addLoanInfo($loan_info);
        if (!$res) {
            Logger::dayLog('loan_error2', '记录失败', $loan->errors);
            return $this->resp(2, 'Reject');
        }
        $loan_data = [
            'request_id' => $request_id,
            'process_code' => 'xhh_loan_2',
            'params_data' => $loan_info,
        ];
        //发送请求
        $api = new \app\modules\api\common\BaseApi();
        $result = $api->sendRequest($loan_data);
        if (empty($result)) {
            Logger::dayLog('loan_error2', '请求失败1', $result, $postdata);
            return $this->resp(2, 'Reject');
        }
        if (isset($result['res_code']) && $result['res_code'] != 0) {
            Logger::dayLog('loan_error2', '请求失败2', $result, $postdata);
            return $this->resp(2, 'Reject');
        }
        //记录结果
        $save_res = $loanlogic->saveResInfo($loan_info,$result,$request_id);
        if (!$save_res) {
            Logger::dayLog('loan_error2', '结果记录失败', $result, $postdata,$loanlogic->info);
            return $this->resp(2, 'Reject');
        }
        $res_data = ArrayHelper::getValue($result, 'LOAN_RESULT',0);
        switch ($res_data) {
            case 1:
                return $this->resp(0, 'Pass');
                break;
            case 2:
                return $this->resp(1, 'Manual');
                break;
            case 3:
                return $this->resp(2, 'Reject');
                break;
            default:
                return $this->resp(2, 'Reject');
        }
    }
    //复贷决策
    public function actionReloan()
    {
        $datas = $this->post();
        Logger::dayLog('init/reloan', 'postdata', $datas);
        if (!is_array($datas) || !isset($datas['data']) || !isset($datas['_sign'])) {
            Logger::dayLog('datas', 'datas', '数据异常！', $datas);
            return $this->resp('3', '数据异常！');
        }
        $isVerify = (new ApiSign)->verifyData($datas['data'], $datas['_sign']);
        if (!$isVerify) {
            Logger::dayLog('datas', 'datas', '验签失败！', $datas);
            return $this->resp('3', '验签失败！');
        }
        $postdata = json_decode($datas['data'], true);

        // $postdata = $this->postdata;
        if (empty($postdata) || !is_array($postdata)) {
            Logger::dayLog('reloan_error', '结果记录失败', $postdata);
            return $this->resp('3', '结果记录失败');
        }
        $user_id = ArrayHelper::getValue($postdata, 'user_id');
        $loan_id = ArrayHelper::getValue($postdata, 'loan_id');
        $where = ['user_id' => $user_id];
        $postdata['from'] = self::RELOAN;
        //记录请求
        $request = new Request();
        $request_id = $request->addRequest($postdata);
        if (!$request_id) {
            Logger::dayLog('reloan', '请求记录失败', $request->errors);
            return $this->resp('3', '请求记录失败');
        }
        //获取决策数据
        $loanlogic = new LoanLogic($postdata);
        $loan_info = $loanlogic->getAntiInfo($loan_id,$user_id);
        $loan_info['request_id'] = $request_id;
        //记录借款附属信息
        $st_loan_extend = new StloanExtend();
        $ex_res = $st_loan_extend->addInfo($loan_info);
        if (!$ex_res) {
            Logger::dayLog('reloan', '附属记录失败', $st_loan_extend->errors);
            return $this->resp('3', '附属记录失败');
        }
        // 获取上次天启成功调用时间
        $loan_info['last_create_time_tq'] = $loanlogic->getOriginQueryTime($loan_info);
        $loan_info['query_time'] = date('Y-m-d H:i:s');
        ###################################################
        $tmp_black = new TmpBlack();
        $where = ['user_id'=> $user_id];
        $loan_info['is_black_tem'] = $tmp_black->getTmpbBlack($where) > 0 ? 1 : 0;
        // 催收黑名单
        $cloud_api = new CloudApi();
        $loan_info['id_collection_black'] = $cloud_api->getForeignBlackIdcard($loan_info['identity']);
        $loan_info['ph_collection_black'] = $cloud_api->getForeignBlackPhone($loan_info['mobile']);
        // prome决策分数
        $oStBaseApi = new StBaseApi();
        $prome_score = $oStBaseApi->getPromeScore($postdata);
        ###################################################
        $loan_info = array_merge($loan_info, $postdata, $prome_score);
        $anti_data = [
            'request_id' => $request_id,
            'params_data' => $loan_info,
        ];
        if (SYSTEM_PROD) {
            $anti_data['process_code'] = 'xhh_reloan';
        } else {
            $anti_data['process_code'] = 'xhh_reloan_1';
        }
        $api = new \app\modules\api\common\BaseApi();
        $result = $api->sendRequest($anti_data);
        if (empty($result)) {
            Logger::dayLog('reloan', '请求失败1', $result, $postdata,$loan_info);
            return $this->resp('3', '请求失败1');
        }
        if (isset($result['res_code']) && $result['res_code'] != 0) {
            Logger::dayLog('reloan', '请求失败2', $result, $postdata,$loan_info);
            return $this->resp('3', '请求失败2');
        }
        //记录结果
        $save_res = $loanlogic->saveResInfo($loan_info,$result,$request_id);
        if (!$save_res) {
            Logger::dayLog('reloan', '结果记录失败', $result, $postdata, $loanlogic->info);
            return $this->resp('3', '结果记录失败');
        }
        $res_data = ArrayHelper::getValue($result, 'LOAN_RESULT',0);
        switch ($res_data) {
            case 1:
                return $this->resp('1', 'Pass');
                break;
            case 2:
                return $this->resp('2', 'Manual');
                break;
            case 3:
                return $this->resp('3', 'Reject');
                break;
            default:
                return $this->resp('3', 'Reject');
        }
    }

    public function actionLoantest()
    {
        if (SYSTEM_PROD) {
            $st = date('Y-m-d 15:00:00');
        } else {
            $st = date('Y-m-17 13:30:00');
        }
        $end = date('Y-m-d 15:30:00');
        $where = ['and', ['>=', 'create_time', $st], ['<=','create_time', $end]];
        var_dump($where);
        $anti_crif = new AntiCrif();
        // $AntiFraud = new AntiFraud();
        // $loan = $AntiFraud->find()->select(AntiFraud::tableName() . '.user_id,' . AntiFraud::tableName() . '.loan_id,' . UserLoanExtend::tableName() . '.success_num,' . UserLoan::tableName() . '.loan_no')
        //     ->leftjoin(UserLoanExtend::tableName(), AntiFraud::tableName() . '.loan_id = ' . UserLoanExtend::tableName() . '.loan_id')
        //     ->leftjoin(UserLoan::tableName(), AntiFraud::tableName() . '.loan_id = ' . UserLoan::tableName() . '.loan_id')->
        //     where($where)->Asarray()->all();
        // $loan = $userLoan->find()->select(UserLoan::tableName().'.user_id,'.UserLoan::tableName().'.loan_id,'.UserLoan::tableName().'.loan_no')->leftjoin(UserLoan::tableName(),AntiFraud::tableName().'.loan_id = '.UserLoan::tableName().'.loan_id')->where($where)->Asarray()->all();
        $loan = $anti_crif->find()->select('user_id,loan_id')->where($where)->Asarray()->all();
        $count = count($loan);
        foreach ($loan as $val) {
            $res = $this->actionReloan($val);
        }
        echo $count . '----finish';
        die;
    }

    // //记录结果
    // private function record_res($result)
    // {

    // }
}