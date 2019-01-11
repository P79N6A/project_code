<?php
namespace app\modules\sfapi\logic;


use app\common\Logger;
use app\models\Request;
use app\models\yyy\YyyApi;
use app\models\cloud\MobileOperator;
use app\models\cloud\DcLoan;
use app\models\open\OpJxlStat;
use app\models\open\OpJxlRequest;
use app\modules\sfapi\common\BaseApi;
use app\modules\sfapi\common\PublicFunc;
use app\modules\sfapi\common\JavaCrif;
use app\modules\sfapi\common\CloudApi;
use Yii;
use yii\helpers\ArrayHelper;

class ReportLogic extends BaseLogic
{
    private $corp_code;
    private $province_code;
    public function __construct() {
        $config = $this->getConfig();
        $this->corp_code = ArrayHelper::getValue($config,'corp_code',[]);
        $this->province_code = ArrayHelper::getValue($config,'province_code',[]);
    }
    // 运营商决策逻辑入口（对外）
    public function mobileCredit($data) {
        $data['from'] = Request::REPORT_CREDIT;//分期决策
        //记录请求
        $func = new PublicFunc();
        $request = $func->addRequest($data);
        if (!$request) {
            return $this->returnInfo(false, '请求记录失败');
        }
        $data['request_id'] = $request;
        $mobile = ArrayHelper::getValue($data,'mobile','');
        //分析用户手机号获取省份及运营商
        $credit_data = $this->analysisMobile($mobile);
        //获取用户历史请求运营商数据
        $credit_data += $this->getOperatorHistory($mobile);
        // 获取历史借款数据
        $credit_data += $this->getLoanHistory($data);
        $operator_data = array_merge($data,$credit_data);
        //请求接口
        $process_code = JavaCrif::PRO_CODE_OPERATOR;
        $javaCrif = new JavaCrif();
        $crif_res = $javaCrif->queryCrif($request,$operator_data,$process_code);
        if (empty($crif_res)) {
            return $this->returnInfo(false, '决策异常');
        }
        //记录决策结果
        $save_res = $func->saveRes($operator_data, $crif_res);
        if (!$save_res) {
            return $this->returnInfo(false, '结果记录异常');
        }
        $retData = array_merge($data,$crif_res);
        return $this->returnInfo(true, $retData);
    }
    /**
     * [analysisMobile 分析手机号]
     * @param  [string] $mobile [description]
     * @return [array]         [description]
     */
    private function analysisMobile($mobile)
    {
        // 获取号码详情
        $default_data = ['corp' => -111,'province' => -111];
        if (empty($mobile)) {
            return $default_data;
        }

        $mob_str = substr($mobile,0,7);
        $where = ['mob' => $mob_str];
        $oMobileOperator = new MobileOperator();
        $mobile_operator = $oMobileOperator->getOne($where);
        if (empty($mobile_operator)) {
            return $default_data;
        }
        $corp = ArrayHelper::getValue($mobile_operator,'Corp','');
        $province = ArrayHelper::getValue($mobile_operator,'Province','');
        $return_data = [
            'corp' => ArrayHelper::getValue($this->corp_code,$corp,-111),
            'province' => ArrayHelper::getValue($this->province_code,$province,-111),
        ];
        return $return_data;
    }

    private function getOperatorHistory($mobile){
        // 一年内
        $mytime= date("Y-m-d H:i:s", strtotime("-1 year"));
        // 上次请求时间
        $where = ['and',['phone' => $mobile],['>=','create_time', $mytime]];
        $oOpJxlRequest = new OpJxlRequest();
        $last_request = $oOpJxlRequest->getJxlRequest($where);
        // 上次成功时间
        $oOpJxlStat = new OpJxlStat();
        $last_success = $oOpJxlStat->getJxl($where);
        $last_report_query_time = ArrayHelper::getValue($last_request,'create_time','0');
        $last_report_source = ArrayHelper::getValue($last_request,'source',0);
        $last_suc_report_create_time = ArrayHelper::getValue($last_success,'create_time','0000-00-00');
        $last_suc_report_source = ArrayHelper::getValue($last_success,'source',0);
        $minute_num = 0;
        if ($last_suc_report_create_time != '0000-00-00' || $last_report_query_time != '0') {
            $minute_num = floor((strtotime($last_suc_report_create_time)-$last_report_query_time)/60);
        }
        
        $return_data = [
            'last_report_query_time' =>$last_report_query_time == '0' ? '0000-00-00' : date('Y-m-d H:i:s',$last_report_query_time),
            'last_report_source' => $last_report_source,
            'last_suc_report_create_time' => $last_suc_report_create_time,
            'last_suc_report_source' => $last_suc_report_source,
            'minute_num' => $minute_num,
        ];
        return $return_data;
    }

    private function getLoanHistory($data) {
        $user_id = ArrayHelper::getValue($data,'user_id','');
        $aid = ArrayHelper::getValue($data,'aid','');
        $default_data = ['success_num' => 0, 'loan_total' => 0];
        if (empty($user_id)) {
            return $default_data;
        }
        //get loan_lotal
        $oDcLoan = new DcLoan();
        $loan_total = $oDcLoan->getMultiLoan($user_id);
        // get success_num
        $yyyApi = new YyyApi();
        $extend_select = 'success_num';
        $where = ['user_id' => $user_id];
        $loan_extend = $yyyApi->getLoanExtendOther($where,$extend_select);
        $return_data = [
            'success_num' => ArrayHelper::getValue($loan_extend,'success_num',0),
            'loan_total' => $loan_total+2,
        ];
        return $return_data;
    }
}