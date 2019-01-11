<?php
/**
 * 获取基类
 */
namespace app\modules\sfapi\common\scorecard;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\common\Curl;
use app\common\ApiSign;

use app\models\Request;
use app\models\yyy\UserLoan;
class YyyScoreCard
{
	private $wsdl_url;

	function __construct()
    {
    	if (SYSTEM_PROD) {
    		$this->wsdl_url = "http://localhost:8091/ws/S1Public?wsdl";
    	} else {
    		$this->wsdl_url = "http://47.93.121.86:8092/ws/S1Public?wsdl";
    	}
    }

    public function getScoreData($data)
    {
    	if (!is_array($data) || empty($data)) {
    		return [];
    	}
        $loan_id = ArrayHelper::getValue($data, 'loan_id', '');
        $user_id = ArrayHelper::getValue($data, 'user_id', '');
        if (empty($user_id) || empty($loan_id)) {
            return []; 
        }
        $score_data = $this->ScoreData($loan_id,$user_id);
        return $score_data;
    }

    private function ScoreData($loanId,$userId){
        
        $loan_id = $loanId;
        $user_id = $userId;
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
        $loanInfo = $loan->getLoanInfo(['loan_id'=>$loan_id]);
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
        Logger::dayLog('antiInfo', $loan_id, $user_id, $otherData);
        return $otherData;
    }
}