<?php
namespace app\modules\sfapi\logic;


use app\models\Request;
use app\models\StBaseApi;
use app\models\yyy\YyyApi;
use app\modules\sfapi\common\BaseApi;
use app\modules\sfapi\common\PublicFunc;
use app\modules\sfapi\common\JavaCrif;
use app\modules\sfapi\common\CloudApi;
use app\models\yyy\UserLoan;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;

class CloudLogic extends BaseLogic
{
    private $stBaseApi;
    public function __construct()
    {
        $this->stBaseApi = new StBaseApi();
    }
    public function setBlack($data)
    {
        //请求cloud获取同盾数据
        $api = new BaseApi();
        $res = $api->queryCloud($data,'cloud/setblack');
        if (!$res) {
            return $this->returnInfo(false, $api->info);
        }
       	return $this->returnInfo(true, $api->info);
    }

    public function unsetBlack($data)
    {
        //请求cloud获取同盾数据
        $api = new BaseApi();
        $res = $api->queryCloud($data,'cloud/unsetblack');
        if (!$res) {
            return $this->returnInfo(false, $api->info);
        }
       	return $this->returnInfo(true, $api->info);
    }

    public function getOrigin($data)
    {
        $data['from'] = Request::TIANQI;//天启决策
        //记录请求
        $func = new PublicFunc();
        $request = $func->addRequest($data);
        if (!$request) {
            return $this->returnInfo(false, '请求记录失败');
        }
        $data['request_id'] = $request;
        //获取用户数据
        $yyyApi = new YyyApi();
        $user_info = $yyyApi->getUserInfo($data);
        if (empty($user_info)) {
            return $this->returnInfo(false, '用户不存在');
        }
        $user_info = array_merge($user_info,$data);
        $data['phone'] = $user_info['mobile'];
        $data['name'] = $user_info['realname'];
        $data['idcard'] = $user_info['identity'];
        // 获取Prome分数
        $prome_info = $this->stBaseApi->getPromeScore($data);
        //获取用户初借复借类型 =1 初借 ；> 1 复借
        $loan_extend = $this->getLoanExtend($data);
        //获取天启数据
        $org_info = $this->getOriginData($data,$prome_info);
        //获取借款数据
        $loan_select = 'loan_id,source,business_type';
        $loan_info = $yyyApi->getLoanData($data,$loan_select);
        if (empty($loan_info)) {
            return $this->returnInfo(false, '借款不存在');
        }
        $user_loan_info = array_merge($user_info,$loan_info,$loan_extend,$prome_info);
        //获取天启决策数据
        $org_data = $this->getOrgData($user_loan_info,$org_info);
        //获取用户同盾数据
        $cloudApi = new CloudApi();
        $multi_info = $cloudApi->getMultiInfo($data);
        $org_data = array_merge($org_data,$multi_info);
        //请求接口
        $process_code = JavaCrif::PRO_CODE_TIANQI;
        $request = ArrayHelper::getValue($org_data, 'request_id');
        $javaCrif = new JavaCrif();
        $crif_res = $javaCrif->queryCrif($request,$org_data,$process_code);
        if (empty($crif_res)) {
            return $this->returnInfo(false, '决策异常');
        }
        $result = array_merge($crif_res,$org_info);
        //记录决策结果
        $save_res = $func->saveRes($data, $result);
        if (!$save_res) {
            return $this->returnInfo(false, '结果记录异常');
        }
        // $res_data = ArrayHelper::getValue($result, 'RESULT');
        return $this->returnInfo(true, $crif_res);
    }

    //获取天启决策结果
    private function getOrgData($user_loan_info,$org_info)
    {
        if (empty($org_info)) {
            $org_info = [
                'credit_score' => 0,
                'model_score_v2' => 0,
                'tianqi_score_v2' => -111,
                'is_black' => 0,
            ];
        }
        $org_data = array_merge($user_loan_info,$org_info);
        return $org_data;
    }

    private function getLoanExtend($data)
    {
        $yyyApi = new YyyApi();
        $extend_select = 'loan_total,success_num';
        $loan_extend = $yyyApi->getLoanExtend($data,$extend_select);
        if (empty($loan_extend)) {
           $loan_extend['loan_total'] = '';
           $loan_extend['success_num'] = '';
        }
        return $loan_extend;
    }

    private function getOriginData($data,$prome_info)
    {
        $result_tq = (int)ArrayHelper::getValue($prome_info,'result_tq',0);
        $loan_id = ArrayHelper::getValue($data,'loan_id',0);
        //Logger::dayLog('origin/prome_info', $loan_id, $prome_info);
        if ($result_tq === 1) {
            //初借请求天启接口，获取天启数据
            $api = new BaseApi();
            $res = $api->queryCloud($data,'cloud/origin');
            if (!$res) {
                $data = [
                    'credit_score' => 0,
                    'model_score_v2' => 0,
                    'tianqi_score_v2' => -111,
                    'is_black' => 0,
                ];
                return $data;
            }
            $org_info = $api->info;
            $data = ArrayHelper::getValue($org_info,'data',[]);
        } else {
            //复借不请求天启,查询天启数据库获取天启数据
            $cloudApi = new CloudApi();
            $data = $cloudApi->getOrigin($data);
            if (empty($data)) {
                $data = [
                    'credit_score' => 0,
                    'model_score_v2' => 0,
                    'tianqi_score_v2' => -111,
                    'is_black' => 0,
                ];
            }
        }
        return $data;
    }
    
    //天行学历
    public function getTxskEdu($data)
    {
        $data['from'] = Request::TXSKEDU;//学历决策
        //记录请求
        $func = new PublicFunc();
        $request = $func->addRequest($data);
        if (!$request) {
            return $this->returnInfo(false, '请求记录失败');
        }
        $data['request_id'] = $request;
        //获取用户数据
        $yyyApi = new YyyApi();
        $user_info = $yyyApi->getUserInfo($data);
        if (empty($user_info)) {
            return $this->returnInfo(false, '用户不存在');
        }
        $user_info = array_merge($user_info,$data);
        $data['phone'] = $user_info['mobile'];
        $data['name'] = $user_info['realname'];
        $data['idcard'] = $user_info['identity'];
        //获取天行学信网数据
        $edu_info = $this->getTxEduData($user_info);
        //获取借款数据
        $loan_select = 'loan_id,source,business_type,days,amount';
        $loan_info = $yyyApi->getLoanData($data,$loan_select);
        if (empty($loan_info)) {
            return $this->returnInfo(false, '借款不存在');
        }
        //获取用户借款附属信息
        $loan_extend = $this->getLoanExtend($data);
        // 获取Prome分数
        $prome_info['PROME_V4_SCORE'] = $this->getProme($data);
        $prome_info['result_tq'] = 0;
        if (isset($data['type']) && $data['type'] == 2) {
            $prome_info['result_tq'] = $this->getReloantq($data);
        }
        //获取天启数据
        $org_info = $this->getOriginData($data,$prome_info);
        ############转义
        $org_info['is_black_tq'] = $org_info['is_black'];
        unset($org_info['is_black']);
        ############
        $edu_info = array_merge($edu_info,$prome_info,$org_info);
        //获取用户历史借款信息
        $history_loan = $this->getAntiInfo($data);
        // 获取百度prea信用分数据
        $cloudApi = new CloudApi();
        $bd_prea = $cloudApi->getBaiduPreaInfo($user_info);
        //获取用户同盾数据
        $multi_info = $cloudApi->getMultiInfo($data);
        $edu_data = array_merge($user_info,$loan_info,$edu_info,$bd_prea,$loan_extend,$history_loan,$multi_info);
        //请求接口
        if (SYSTEM_PROD) {
            $process_code = JavaCrif::PRO_CODE_TXSKEDU;
        } else {
            $process_code = 'anti_chsi_decision';
        }
        $request = ArrayHelper::getValue($edu_data, 'request_id');
        $javaCrif = new JavaCrif();
        $crif_res = $javaCrif->queryCrif($request,$edu_data,$process_code);
        if (empty($crif_res)) {
            return $this->returnInfo(false, '决策异常');
        }
        //记录决策结果
        $save_res = $func->saveRes($data, $crif_res);
        if (!$save_res) {
            return $this->returnInfo(false, '结果记录异常');
        }
        // $res_data = ArrayHelper::getValue($crif_res, 'RESULT');
        return $this->returnInfo(true, $crif_res);
    }

    private function getProme($data){
        $prome_info = $this->stBaseApi->getPromeScore($data);
        $prome_score = ArrayHelper::getValue($prome_info,'PROME_V4_SCORE',0);
        return $prome_score;
    }

    private function getReloantq($data){
        $prome_info = $this->stBaseApi->getReloanAuth($data);
        $result_tq = ArrayHelper::getValue($prome_info,'result_tq',0);
        return $result_tq;
    }
    //请求天行接口并获取数据
    private function getTxEduData($data)
    {
        // $api = new BaseApi();
        // $res = $api->queryCloud($data,'cloud/txskedu');
        // if (!$res) {
            $edu_info = [
                'graduateSchool' =>  '',
                'educationBackground' =>  '',
                'matriculationTime' =>  '',
                'profession' =>  '',
                'graduationTime' =>  '',
                'graduationConclusion' =>  '',
                'educationType' =>  '',
                'queryResult' =>  '',
                'educationBackground' => '',
                'educationType' => '',
                'queryResult' => '',
            ];
            return $edu_info;
        // }
        $edu_data= $api->info;
        return isset($edu_data['data']) ? $edu_data['data'] : [];
    }

    public function getAntiInfo($data){
        //校验用户信息
        $loan_id = ArrayHelper::getValue($data,'loan_id','');
        $user_id = ArrayHelper::getValue($data,'user_id','');
        $otherData = [
            'wst_dlq_sts'=>0,//客户历史最坏逾期天数
            'mth3_dlq_num'=>'',//客户过去3个月逾期次数（按照贷款记） 
            'mth3_wst_sys'=>'',// 客户过去3个月最坏逾期天数
            'mth3_dlq7_num'=>'',//客户过去3个月逾期超过7天的贷款数 
            'mth6_dlq_ratio'=>'',//客户过去6个月有过预期的贷款比例（分母为过去6个月批核的总贷款数）
        ];
        if( !$loan_id || !$user_id)
            return $otherData;
        
        
        $loan = new UserLoan;
        $loanInfo = $loan->getLoanInfo(['loan_id' => $loan_id]);
        $loanAll = $loan->getAllLoan(['user_id'=>$user_id,'status'=>[8,9,11,12,13],'business_type'=>[1,4]]);
        if( $loanAll ) {
            $otherData = [
                'wst_dlq_sts'=>0,//客户历史最坏逾期天数
                'mth3_dlq_num'=>0,//客户过去3个月逾期次数（按照贷款记） 
                'mth3_wst_sys'=>0,// 客户过去3个月最坏逾期天数
                'mth3_dlq7_num'=>0,//客户过去3个月逾期超过7天的贷款数 
                'mth6_dlq_ratio'=>0,//客户过去6个月有过预期的贷款比例（分母为过去6个月批核的总贷款数）
            ];
            $create_time_now = substr($loanInfo['create_time'], 0,10);
            $mth6LoanCount = 0;
            $totalCount = 0;
            $mth3Count = 0;
            foreach ($loanAll as $key => $value) {
                if( $value->loan_id < $loan_id ){
                    $repay_time = substr($value['repay_time'], 0,10);
                    $end_date = substr($value['end_date'], 0,10);
                    //最长逾期时间
                    $due_day = (int)((strtotime($repay_time)-strtotime($end_date))/(60*60*24));
                    if( $due_day > $otherData['wst_dlq_sts'] ){
                        $otherData['wst_dlq_sts'] = $due_day;
                    }
                    //客户过去3个月逾期次数（按照贷款记）
                    $create_time_old = substr($value['create_time'], 0,10);
                    $loanTime = (strtotime($create_time_now)-strtotime($create_time_old))/(60*60*24);
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
            }
            //客户过去6个月有过预期的贷款比例（分母为过去6个月批核的总贷款数）
            if( $totalCount > 0 ){
                $otherData['mth6_dlq_ratio'] = floor(($mth6LoanCount / $totalCount)*100)/100;
            }
            //近三个月无借款
            $three_count = $loan->getThreeMcount($user_id,$loan_id);
            if ($three_count == 0) {
                $otherData['mth3_dlq_num'] = '';
                $otherData['mth3_wst_sys'] = '';
                $otherData['mth3_dlq7_num'] = '';
            }
            //近六个月无借款
            $six_count = $loan->getSixMcount($user_id,$loan_id);
            if( $six_count == 0 ) {
                $otherData['mth3_dlq_num'] = '';
                $otherData['mth3_wst_sys'] = '';
                $otherData['mth3_dlq7_num'] = '';
                $otherData['mth6_dlq_ratio'] = '';
            }
        }
        Logger::dayLog('txskedu/antiInfo', $loan_id, $user_id, $otherData);
        return $otherData;
    }
}