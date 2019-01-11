<?php
namespace app\modules\sfapi\logic;


use app\common\Logger;
use app\models\Request;
use app\models\yyy\YyyApi;
use app\modules\sfapi\common\BaseApi;
use app\modules\sfapi\common\PublicFunc;
use app\modules\sfapi\common\JavaCrif;
use app\modules\sfapi\common\CloudApi;
use Yii;
use yii\helpers\ArrayHelper;

class PeriodLogic extends BaseLogic
{
    public function loanPeriods($data)
    {
        $data['from'] = Request::PERIODS;//分期决策
        //记录请求
        $func = new PublicFunc();
        $request = $func->addRequest($data);
        if (!$request) {
            return $this->returnInfo(false, '请求记录失败');
        }
        $data['request_id'] = $request;
        //获取用户数据
        // $in_time = date('Y-m-d H:i:s');
        // Logger::dayLog('period_info','in_time', $in_time);
        $yyyApi = new YyyApi();
        $user_info = $yyyApi->getUserInfo($data);
        if (empty($user_info)) {
            return $this->returnInfo(false, '用户不存在');
        }
        // $time1 = explode(' ',microtime());
        //获取用户历史借款成功次数
        $loan_extend = $this->getLoanExtend($data);
        // $time2 = explode(' ',microtime());
        // $thistime1 = $time2[0]+$time2[1]-($time1[0]+$time1[1]);
        // Logger::dayLog('period_info','时间1', $thistime1);
        //获取借款额度
        $quota = $this->getQuota($data);
        // $time3 = explode(' ',microtime());
        // $thistime2 = $time3[0]+$time3[1]-($time2[0]+$time2[1]);
        // Logger::dayLog('period_info','时间2', $thistime2);
        //获取用户类型 1 初贷；2复贷；
        $data['type'] = 1;
        if (isset($loan_extend['success_num']) && $loan_extend['success_num'] > 0) {
            $data['type'] = 2;
        }
        // $time4 = explode(' ',microtime());
        // $thistime3 = $time4[0]+$time4[1]-($time3[0]+$time3[1]);
        // Logger::dayLog('period_info','时间3', $thistime3);
        //获取历史借款概况
        $history_loan = $yyyApi->getHistoryData($data);
        // $time5 = explode(' ',microtime());
        // $thistime4 = $time5[0]+$time5[1]-($time4[0]+$time4[1]);
        // Logger::dayLog('period_info','时间4', $thistime4);
        //查询用户是否为分期用户 0 非分期用户；1 分期用户
        $data['is_fq'] = $yyyApi->getTerm($data);
        // $time6 = explode(' ',microtime());
        // $thistime5 = $time6[0]+$time6[1]-($time5[0]+$time5[1]);
        // Logger::dayLog('period_info','时间5', $thistime5);
        $period_info = array_merge($user_info,$loan_extend,$quota,$history_loan,$data);
        // $out_time = date('Y-m-d H:i:s');
        // Logger::dayLog('period_info','out_time', $out_time);
        // Logger::dayLog('period_info','分期数据', $period_info);
        //记录分期数据
        $period_res = $func->savePeriods($period_info);
        if (!$period_res) {
            return $this->returnInfo(false, '记录数据异常');
        }
        //请求接口
        $process_code = JavaCrif::PRO_CODE_PERIODS;
        $javaCrif = new JavaCrif();
        $crif_res = $javaCrif->queryCrif($request,$period_info,$process_code);
        if (empty($crif_res)) {
            return $this->returnInfo(false, '决策异常');
        }
        //记录决策结果
        $save_res = $func->saveRes($period_info, $crif_res);
        if (!$save_res) {
            return $this->returnInfo(false, '结果记录异常');
        }
        $retData = array_merge($data,$crif_res);
        return $this->returnInfo(true, $retData);
    }

    private function getQuota($data)
    {
        $user_id = ArrayHelper::getValue($data, 'user_id');
        $where = ['user_id' => $user_id];
        $select = 'quota';
        $yyyApi = new YyyApi();
        $quota = $yyyApi->getQuota($where,$select);
        if (empty($quota)) {
            $quota['quota'] = 1500;
        }
        return $quota;
    }
        
    private function getLoanExtend($data)
    {
        $user_id = ArrayHelper::getValue($data, 'user_id');
        $yyyApi = new YyyApi();
        $extend_select = 'loan_total,success_num';
        $where = ['user_id' => $user_id];
        $loan_extend = $yyyApi->getLoanExtendOther($where,$extend_select);
        return $loan_extend;
    }
}