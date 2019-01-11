<?php
namespace app\modules\sysapi\logic;

use app\common\Logger;
use app\models\credit\CreditApi;
use app\models\loan\SfApi;
use app\models\Overdueloan;
use app\models\Request;
use app\models\Result;
use app\models\StBaseApi;
use app\models\ygy\YgyUserCreditList;
use app\models\yyy\UserLoan;
use app\models\yyy\UserPassword;
use app\models\yyy\YiUserCreditList;
use app\models\yyy\YiUserLoan;
use app\models\yyy\YyyApi;
use app\modules\sysapi\common\BaseApi;
use yii\helpers\ArrayHelper;

class SysloanLogic extends BaseLogic
{
    private $anti_centu = 'anti_centu';
    private $over_before = 'Rollover_decision';
    private $device;
    public function __construct()
    {
        $this->device = [
            'ios' => 1,
            'android' => 2,
            'FLYME' => 3,
            'YYB_CPD' => 4,
            'YYB_QQLLQ' => 5,
            'YYB' => 6,
            'OPPO' => 7,
            'VIVO' => 8,
            'ANZHI' => 9,
            'XM' => 10,
            'LESHI' => 11,
            'C360' => 12,
            'CHUIZI' => 13,
            'SANXING' => 14,
            '' => -111,
        ];
    }
    public function Collect($data)
    {
        //记录请求
        $request = new Request();
        $request_id = $request->addRequest($data);
        if (!$request_id) {
            Logger::dayLog('collect', 'addRequest', $request->errors, $data);
            return $this->returnInfo(false, '请求保存失败');
        }
        $data['request_id'] = $request_id;
        //借款信息是否已存在
        $loan_exist = $this->loanExist($data);
        if (!empty($loan_exist)) {
            $sys_info = $loan_exist;
        } else {
            //判断用户来源并获取数据
            $get_res = $this->getInfo($data);
            if (!$get_res) {
                return $this->returnInfo(false, $this->info);
            }
            $sys_info = $this->info;
        }
        // 新增prome分数
        $oStBaseApi = new StBaseApi();
        $prome_info = $oStBaseApi->getPromeScore($data);
        // 复贷数据集
        $reloan_info = $oStBaseApi->getReloanData($data);
        // parent_loan_info
        $parent_loan_info = $this->getParentLoanInfo($sys_info);
        // user password date
        $user_password = $this->getUserPassword($data);
        $add_array = array_merge($prome_info, $reloan_info, $parent_loan_info, $user_password);
        //用户有信令信息
        $sys_info['money_yxcard'] = $this->getOrderAmoutByPloanId($sys_info['parent_loan_id']);
        //add
        $uesr_id = ArrayHelper::getValue($data, 'user_id');
        $loan_id = ArrayHelper::getValue($data, 'loan_id');
        $success_loan = $this->getSuLoan($uesr_id, $loan_id);
        //贷后决策引擎风险余额计算方式调整增加字段
        $balance_calc_parma = $this->balanceCalcParma($loan_id);

        $sys_info = array_merge($sys_info, $success_loan, $add_array, $balance_calc_parma);

        //发送结果
        $collect_res = $this->sendCollect($sys_info, $this->anti_centu);
        $res_data = $this->info;
        if (!$collect_res) {
            return $this->returnInfo(false, $this->info);
        }
        //保存结果
        $save_res = $this->saveRes($res_data, $data);
        if (!$save_res) {
            return $this->returnInfo(false, $this->info);
        }
        return $this->returnInfo(true, $res_data);

    }
    private function getUserPassword($data)
    {
        $user_id = ArrayHelper::getValue($data, 'user_id', '');
        $return_data = ['device_type' => '-111'];
        $device_type = ['android' => 1, 'ios' => 2, '' => -111];
        if (empty($user_id)) {
            return $return_data;
        }
        $oUserPassword = new UserPassword();
        $password_info = $oUserPassword->getPwdByUserId($user_id);
        $device = ArrayHelper::getValue($password_info, 'device_type', '');
        $return_data = [
            'device_type' => ArrayHelper::getValue($this->device, $device, -111),
        ];
        return $return_data;
    }
    /**
     * 获取父借款数据
     *
     * @param [type] $data
     * @return array
     * @Description
     * @author chengzhneyuan
     * @since
     */
    private function getParentLoanInfo(&$data)
    {
        $return_data = ['parent_end_date' => '', 'parent_repay_time' => ''];
        $parent_loan_id = ArrayHelper::getValue($data, 'parent_loan_id', '');
        if (empty($parent_loan_id)) {
            return $return_data;
        }
        $where = ['loan_id' => $parent_loan_id];
        $oUserLoan = new UserLoan();
        $parent_loan_info = $oUserLoan->getLoanInfo($where);
        $return_data = [
            'parent_end_date' => ArrayHelper::getValue($parent_loan_info, 'end_date', ''),
            'parent_repay_time' => ArrayHelper::getValue($parent_loan_info, 'repay_time', ''),
        ];
        return $return_data;

    }
    /**
     * 获取成功借款数
     * @param $user_id
     * @param $loan_id
     * @return array
     */
    private function getSuLoan($user_id, $loan_id)
    {
        if (empty($user_id) || empty($loan_id)) {
            return ['loan_create_time' => '', 'last_repay_time' => '', 'last_end_date' => ''];
        }
        $user_loan = new UserLoan();
        $loan_su_info = $user_loan->getSuLoan($user_id);
        $return_data = [];
        $return_data['last_repay_time'] = ArrayHelper::getValue($loan_su_info, 'repay_time', '');
        $return_data['last_end_date'] = ArrayHelper::getValue($loan_su_info, 'end_date', '');
        return $return_data;

    }

    //逾前催收逻辑
    public function Overbefore($data)
    {
        //记录请求
        $request = new Request();
        $request_id = $request->addRequest($data);
        if (!$request_id) {
            Logger::dayLog('overbefore', 'addRequest', $request->errors, $data);
            return $this->returnInfo(false, '请求保存失败');
        }
        $data['request_id'] = $request_id;
        //获取借款数据
        $api = new YyyApi();
        $loan_info = $api->userLoanInfo($data);
        if (empty($loan_info)) {
            return $this->returnInfo(false, '借款不存在');
        }
        $loan_info = array_merge($loan_info, $data);
        //借款附属信息
        $loan_extend_info = $api->sysLoanExtend($loan_info);
        //yi_sure数据
        $loan_sure = $api->getYisure($loan_info);
        //整合数据
        $loan_info = array_merge($loan_info, $loan_extend_info, $loan_sure);
        //用户有信令信息
        $loan_info['money_yxcard'] = $this->getOrderAmoutByPloanId($loan_info['parent_loan_id']);
        //add
        $uesr_id = ArrayHelper::getValue($data, 'user_id');
        $loan_id = ArrayHelper::getValue($data, 'loan_id');
        $success_loan = $this->getSuLoan($uesr_id, $loan_id);
        $loan_info = array_merge($loan_info, $success_loan);
        //发送结果
        $collect_res = $this->sendCollect($loan_info, $this->over_before);
        $res_data = $this->info;
        if (!$collect_res) {
            return $this->returnInfo(false, $this->info);
        }
        //保存结果
        $save_res = $this->saveRes($res_data, $data);
        if (!$save_res) {
            return $this->returnInfo(false, $this->info);
        }
        return $this->returnInfo(true, $res_data);
    }
    /**
     * [getInfo 获取数据并保存]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    private function getInfo($data)
    {
        $aid = ArrayHelper::getValue($data, 'aid');
        switch ($aid) {
            case 1:
                $sys_info = $this->getYyyInfo($data);
                break;
            case 8:
                $sys_info = $this->getSfInfo($data);
                break;
            default:
                return '';
                break;
        }
        if (!$sys_info) {
            return $this->returnInfo(false, $this->info);
        }
        //保存数据请求
        $save_date = array_merge($data, $sys_info);
        $sysloan = new Overdueloan();
        $save_res = $sysloan->saveDate($save_date);
        if (!$save_res) {
            Logger::dayLog('sys_save', '记录失败', $sysloan->errors);
            return $this->returnInfo(false, '记录失败');
        }
        return $this->returnInfo(true, $save_date);
    }
    /**
     * 一亿元数据
     */
    private function getYyyInfo($data)
    {
        $yyyApi = new YyyApi();
        $sys_info = $yyyApi->getSysInfo($data);
        if ($sys_info['res_code'] != '0') {
            return $this->returnInfo(false, $sys_info);
        }
        return $sys_info;
    }

    /**
     * 7-14数据
     */
    private function getSfInfo($data)
    {
        $sfApi = new SfApi();
        $sys_info = $sfApi->getSysInfo($data);
        if ($sys_info['res_code'] != '0') {
            return $this->returnInfo(false, $sys_info);
        }
        return $sys_info;
    }

    /**
     * [sendCollect 发送请求]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    private function sendCollect($data, $process_code)
    {
        $sys_data = [
            'request_id' => $data['request_id'],
            'process_code' => $process_code,
            'params_data' => $data,
        ];
        $api = new BaseApi();
        $result = $api->sendRequest($sys_data);
        if (empty($result)) {
            Logger::dayLog('error', 'result', '结果为空：', $result, $data);
            return $this->returnInfo(false, '决策结果为空');
        }
        if (isset($result['res_code']) && $result['res_code'] != 0) {
            Logger::dayLog('error', 'result', '决策异常:', $result, $data);
            return $this->returnInfo(false, '决策异常');
        }
        return $this->returnInfo(true, $result);
    }

    /**
     * [saveRes 记录结果]
     * @param  [type] $result [description]
     * @param  [type] $data   [description]
     * @return [type]         [description]
     */
    private function saveRes($result, $data)
    {
        //标准化参数
        $result['RESULT'] = isset($result['SCORE_STATUS']) ? $result['SCORE_STATUS'] : $result['RESULT'];
        $data['identity_id'] = $data['user_id'];
        $record_res = new Result();
        $res = $record_res->saveRes($data, $result);
        if (!$res) {
            Logger::dayLog('error', 'result', '结果记录失败:', $record_res->errors, $result, $data);
            return $this->returnInfo(false, '结果记录失败');
        }
        return $res;
    }
    /**
     * [loanExist 借款是否存在，存在则更新]
     * @param  [type] $data [Sdescription]
     * @return [type]       [description]
     */
    private function loanExist($data)
    {
        $sys_loan = new Overdueloan();
        $loan_exist = $sys_loan->getLoansys($data);
        if (empty($loan_exist)) {
            return [];
        }
        switch ($data['aid']) {
            case 1:
                $api = new YyyApi();
                $res = $api->UpdateOverloan($data);
                break;
            case 8:
                $api = new SfApi();
                $res = $api->UpdateOverloan($data);
                break;
            default:
                $res = false;
                break;
        }
        if (!$res) {
            Logger::dayLog('error', 'result', '更新失败:', $res, $data);
            return [];
        }
        //获取借款来源
        $loan_exist = $sys_loan->getLoansys($data);
        if (empty($loan_exist)) {
            return [];
        }
        //借款数据
        $loan_info = $api->userLoanInfo($loan_exist);
        $loan_exist = array_merge($loan_exist, $loan_info);
        //借款附属数据
        $loan_extend_info = $api->sysLoanExtend($loan_exist);
        //yi_sure数据
        $loan_sure = $api->getYisure($loan_exist);

        $loan_exist = array_merge($loan_exist, $loan_extend_info, $loan_sure);

        // $loan_exist['source'] = $this->getLoanSource($data);
        $loan_exist['source'] = isset($loan_exist['source']) ? (int)$loan_exist['source'] : 0;
        return $loan_exist;
    }

    private function getLoanSource($data)
    {
        switch ($data['aid']) {
            case 1:
                $loan_select = 'source';
                $yyyApi = new YyyApi();
                $res = $yyyApi->getLoanData($data, $loan_select);
                break;
            case 8:
                $loan_select = 'from_code';
                $sfApi = new SfApi();
                $res = $sfApi->getLoanData($data, $loan_select);
                break;
            default:
                $res = 0;
                break;
        }
        return empty($res) ? 0 : (int) $res;
    }

    private function getOrderAmoutByPloanId($parent_loan_id)
    {
        if (!$parent_loan_id) {
            return 0;
        }
        $where = ['loan_id' => $parent_loan_id, 'status' => [1, 3, 5]];
        $yxApi = new CreditApi();
        $orderInfo = $yxApi->getYxOrderInfo($where);
        if (empty($orderInfo)) {
            return 0;
        }
        return isset($orderInfo['amount']) ? (float) $orderInfo['amount'] : 0;
    }

    private function balanceCalcParma($loan_id)
    {
        $return_data = [
            "user_type"                     => -111,
            "multi_all_p_class_30"          => -111,
            "multi_all_p_class_7"           => -111,
            "multi_big_p_class_30"          => -111,
            "multi_big_p_class_7"           => -111,
            "multi_common_p_class_30"       => -111,
            "multi_common_p_class_7"        => -111,
            "multi_p2p_p_class_30"          => -111,
            "multi_p2p_p_class_7"           => -111,
            "multi_small_p_class_30"        => -111,
            "multi_small_p_class_7"         => -111
        ];
        if (empty($loan_id)){
            return $return_data;
        }
        $oYiUserLoan = new UserLoan();
        $parent_loan_id = $oYiUserLoan->getLoanOne($loan_id);
        if (empty($parent_loan_id)){
            return $return_data;
        }
        $parent_loan_id = ArrayHelper::getValue($parent_loan_id, "parent_loan_id", 0);
        $oYiUserCredit = new YiUserCreditList();
        $credit_data = $oYiUserCredit->getUserCredit(['loan_id'=>$parent_loan_id],"req_id");
        if (empty($credit_data)){
            try {
                //如果一亿元为空就到一个亿里查找
                $oYgyUserCredit = new YgyUserCreditList();
                $credit_data = $oYgyUserCredit->getUserCredit(['loan_id' => $parent_loan_id], "req_id");
                if (empty($credit_data)) {
                    return $return_data;
                }
            }catch (\Exception $e){
                Logger::dayLog('error', '调用一个亿失败', $e->getMessage());
                return $return_data;
            }
        }
        $req_id = ArrayHelper::getValue($credit_data, 'req_id');
        if (empty($req_id)){
            return $return_data;
        }

        $oRequest = new Request();
        $request_data = $oRequest->getRequestByReqidOne($req_id);
        $request_id = ArrayHelper::getValue($request_data, "request_id");
        if (empty($request_id)){
            return $return_data;
        }
        $oResult = new Result();
        //$request_id = 8900;
        $result_data = $oResult->getOne(['request_id'=>$request_id, 'from'=>66]);
        if (!$result_data){
            return $return_data;
        }
        $res_info = ArrayHelper::getValue($result_data, "res_info");
        if (empty($res_info)){
            return $return_data;
        }
        $res_info = json_decode($res_info, true);
        return [
            "user_type"                     => ArrayHelper::getValue($res_info, "user_type", -111),
            "multi_all_p_class_30"          => ArrayHelper::getValue($res_info, "multi_all_p_class_30", -111),
            "multi_all_p_class_7"           => ArrayHelper::getValue($res_info, "multi_all_p_class_7", -111),
            "multi_big_p_class_30"          => ArrayHelper::getValue($res_info, "multi_big_p_class_30", -111),
            "multi_big_p_class_7"           => ArrayHelper::getValue($res_info, "multi_big_p_class_7", -111),
            "multi_common_p_class_30"       => ArrayHelper::getValue($res_info, "multi_common_p_class_30", -111),
            "multi_common_p_class_7"        => ArrayHelper::getValue($res_info, "multi_common_p_class_7", -111),
            "multi_p2p_p_class_30"          => ArrayHelper::getValue($res_info, "multi_p2p_p_class_30", -111),
            "multi_p2p_p_class_7"           => ArrayHelper::getValue($res_info, "multi_p2p_p_class_7", -111),
            "multi_small_p_class_30"        => ArrayHelper::getValue($res_info, "multi_small_p_class_30", -111),
            "multi_small_p_class_7"         => ArrayHelper::getValue($res_info, "multi_small_p_class_7", -111)
        ];
    }
}
