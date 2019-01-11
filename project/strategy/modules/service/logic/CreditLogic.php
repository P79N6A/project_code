<?php
namespace app\modules\service\logic;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\common\Xgboost;
use app\common\FdXgBoostApi;
use app\common\Ganoderma;
use app\common\YArray;
use app\models\StAmount;
use app\models\StCreditResult;
use app\modules\service\common\SaveFunc;
use app\modules\service\common\JavaCrif;
use app\modules\service\common\CloudApi;
use app\modules\service\common\Analysis;
use app\modules\service\common\UserLoanApi;

class CreditLogic extends BaseLogic {
    private $oSaveFunc;
    private $oJavaCrif;
    private $oCloudApi;
    private $oAnalysis;
    private $oUserLoanApi;
    private $org_from = 56;
    private $default_res;
    private $oFdXgBoostApi;
    private $oXgboostApi;
    private static $source_map;

    public function __construct() {
        $this->oSaveFunc = new SaveFunc();
        $this->oJavaCrif = new JavaCrif();
        $this->oCloudApi = new CloudApi();
        $this->oAnalysis = new Analysis();
        $this->oUserLoanApi = new UserLoanApi();
        $this->oXgboostApi = new Xgboost();
        $this->oFdXgBoostApi = new FdXgBoostApi();
        $this->default_res = [
            'AMOUNT' =>  '0',
            'DAYS' =>  '0',
            'RESULT' => StCreditResult::STATUS_REJECT, //驳回
        ];
        self::$source_map = [
                '微信' => 1,
                'weixin' => 1,
                'app' => 2,
                'App' => 2,
                'ios' => 3,
                'IOS' => 3,
                'Ios' => 3,
                'andraoid' => 4,
                'Android' => 4,
                'android' => 4,
                'H5' => 5,
                'h5' => 5,
                'web' => 5,
                '百融' => 6,
                '融360' => 8,
                '借点钱' => 9,
                '借了吗' => 11,
                '借钱用' => 12,
                '360导航' => 13,
                '有鱼' => 14,
            ];
    }
    public function CheckRisk($data) {
        //记录评测请求
        $credit_id = $this->oSaveFunc->saveRequest($data);
        if (!$credit_id) {
            return $this->returnInfo(false, 'S000001');
        }
        $data['credit_id'] = $credit_id;
        # get java credit data
        $java_credit_data = $this->getJavaCreditData($data);
        // query javaCrif LOAN_STRATEGY
        $crif_res = $this->oJavaCrif->queryCrif($credit_id,$java_credit_data,JavaCrif::PRO_LOAN_STRATEGY);
        if (!$crif_res) {
            return $this->returnInfo(false, 'S000002');
        }
        // save loan crif result
        $save_res = $this->oSaveFunc->saveResult($data, $crif_res ,$data['come_from']);
        if (!$save_res) {
            return $this->returnInfo(false, 'S000003');
        }
        if (!SYSTEM_PROD) {
            # test data
            // $crif_res['result_tq'] = '1';
            // $crif_res['result_lz'] = '1';
            // $crif_res['result_model_tq'] = '1';
        }
        // get result_model_tq and chk
        $result_model_tq = ArrayHelper::getValue($crif_res,'result_model_tq',0);
        if ($result_model_tq != 1) {
            $retData = $this->setReturnInfo($data, $crif_res);
            return $this->returnInfo(true, $retData);
        }
        // query Origin
        $org_res = $this->javaOrigin($crif_res, $data, $java_credit_data);
        $retData = $this->setReturnInfo($data, $org_res);
        return $this->returnInfo(true, $retData);
    }

    private function getJavaCreditData($data){
        // get cloud
        $cloud_data = $this->getCloudDatas($data);
        // get antifraud
        $antifraud_data = $this->getAntiDatas($data);
        // get user and loan
        $user_loan_data = $this->getUserLoan($data);
        $source = ArrayHelper::getValue($data,'source','weixin');
        $data['source'] = ArrayHelper::getValue(self::$source_map,$source,1);
        // merge all data
        $java_credit_data = array_merge($data,$cloud_data,$antifraud_data,$user_loan_data);
        // deal with data
        unset($java_credit_data['relation']);
        Logger::dayLog('service/loanCreditData', 'loanCreditData', json_encode($java_credit_data));
        return $java_credit_data;
    }
    // 用户及其借款基本数据
    private function getUserLoan($data){
        $relation = ArrayHelper::getValue($data, 'relation', '');
        $relation = json_decode($relation,true);
        $user_params = [
            'mobile' => ArrayHelper::getValue($data, 'mobile', ''),
            'contact' => ArrayHelper::getValue($relation, 'mobile', ''),
        ];
        $user_loan_data = $this->oUserLoanApi->apiOpen($user_params);
        return $user_loan_data;
    }
    // 天启决策
    private function javaOrigin($loan_crif_res, $data, &$user_crif_data){
        $credit_id = ArrayHelper::getValue($data,'credit_id',0);
        $process_code = JavaCrif::PRO_LOAN_TIANQI;
        $origin_crif_res = $this->queryOriginCrif($user_crif_data,$loan_crif_res,$process_code);
        if (empty($origin_crif_res)) {
            Logger::dayLog('service/javaOrigin','javaOrigin',$credit_id,'天启决策异常');
            return $this->default_res;
        }
        // 记录天启结果
        $save_res = $this->oSaveFunc->saveResult($data, $origin_crif_res, $this->org_from);
        if (!$save_res) {
            Logger::dayLog('service/javaOrigin','javaOrigin',$credit_id,'授信决策储存失败');
        }
        return $origin_crif_res;
    }

    private function queryOriginCrif(&$user_crif_data, $loan_crif_res,$process_code)
    {  
        $crif_all_params = array_merge($user_crif_data,$loan_crif_res);
        // 1, 初始数据
        $user_crif_keys = [
            'user_come_from',
            'aid',
            'mobile',
            'mid_fm_one_m',
            'mid_fm_seven_d',
            'mid_fm_three_m',
            'mph_fm_one_m',
            'mph_fm_seven_d',
            'mph_fm_three_m',
            'multi_all_p_class_30',
            'multi_all_p_class_7',
            'multi_big_p_class_30',
            'multi_big_p_class_7',
            'multi_common_p_class_30',
            'multi_common_p_class_7',
            'multi_p2p_p_class_30',
            'multi_p2p_p_class_7',
            'multi_small_p_class_30',
            'multi_small_p_class_7',
            'express_weight_loss_p',
            'tianqi_score_v2',
            'ganoderma_score',
            'is_black_tq',
            'fd_test56',
            'last_success_loan_days',
            'loan_total',
            'PROME_V4_SCORE',
            'quota',
            'source',
            'Strategy_RESULT',
            'success_num',
            'type',
            'user_id',
            'wst_dlq_sts',
        ];
        $prome_tq_arr = (new YArray)->getByKeys($crif_all_params, $user_crif_keys, 0);
        $prome_tq_arr['result_status'] = ArrayHelper::getValue($loan_crif_res, 'RESULT', 0);
        // 2, 孚临接口数据
        $result_lz = ArrayHelper::getValue($loan_crif_res,'result_lz',0);
        if ($result_lz == 1) {
            $prome_tq_arr['ganoderma_score'] = $this->queryGanoderma($user_crif_data);
        }
        try {
            # 查询xgchoost接口
            $prome_tq_arr['xg_prob'] = $this->oXgboostApi->xgboostOpen($crif_all_params); 
        } catch (\Exception $e) {
            Logger::dayLog("queryError", "xgchoost error:",$e->getMessage());
            $prome_tq_arr['xg_prob'] = -111;
        }
        //复贷请求fdxgboots
        try {
            # 查询fdxgchoost接口
            $prome_tq_arr['fd_xgprob'] = $this->oFdXgBoostApi->fdboostOpen($crif_all_params);
        } catch (\Exception $e) {
            Logger::dayLog('queryError', 'fd_xgprob error:', $e->getMessage());
            $prome_tq_arr['fd_xgprob'] = -111;
        }
        // 4, 天启接口数据
        $result_tq = ArrayHelper::getValue($loan_crif_res,'result_tq','0');
        if ($result_tq == '1') {
            $tq_params = [
                'name'    => ArrayHelper::getValue($user_crif_data,'realname',''),
                'idcard'  => ArrayHelper::getValue($user_crif_data,'identity',''),
                'phone'   => ArrayHelper::getValue($user_crif_data,'mobile',''),
                'user_id' => ArrayHelper::getValue($user_crif_data,'user_id',0),
                'loan_id' => ArrayHelper::getValue($user_crif_data,'loan_id',0),
                'aid'     => ArrayHelper::getValue($user_crif_data,'aid',16),
            ];
            $data_tq_arr = $this->getOriginData($tq_params);
            // 合并并覆盖初始天启数据
            $prome_tq_arr = array_merge($prome_tq_arr,$data_tq_arr);
        }
        // 5, days对应的当天购卡金额
        $oStAmount = new StAmount();
        $prome_tq_arr += $oStAmount->getDayAmount();
        // 6, 请求天启决策
        $credit_id = ArrayHelper::getValue($user_crif_data,'credit_id','0');
        Logger::dayLog('service/originCreditData', 'originCreditData', json_encode($prome_tq_arr));
        $crif_res = $this->oJavaCrif->queryCrif($credit_id,$prome_tq_arr,$process_code);
        if (empty($crif_res)) {
            Logger::dayLog('runallin/queryOriginCrif', '天启决策异常', $prome_tq_arr);
        }
        return $crif_res;
    }

    private function queryGanoderma(&$user_crif_data){
        try {
                $lz_keys = [
                    'realname',
                    'identity',
                    'mobile',
                ];
                $lz_params = (new YArray)->getByKeys($user_crif_data, $lz_keys, '');
                // query Ganoderma
                $ganoderma_score = (new Ganoderma)->ganodermaOpen($lz_params);
            } catch (\Exception $e) {
                Logger::dayLog("queryError", "ganoderma error:",$e->getMessage());
                $ganoderma_score = -111;
            }
            return $ganoderma_score;
    }
    public function getCloudDatas($data)
    {
        $cloud_params = $this->oCloudApi->normalCloudParams($data);
        $url = 'loan';
        // 同盾多投及黑名单数据
        $cloud_info = $this->oCloudApi->cloudApi($cloud_params, $url);
        // 天启数据
        $origin_info = $this->oCloudApi->getOrigin($cloud_params);
        $origin_info['is_black_tq'] = $origin_info['is_black'];
        unset($origin_info['is_black']);
        // baidu risk
        $risk_info = $this->oCloudApi->getBaiduRiskInfo($cloud_params);
        //baidu prea
        $prea_info = $this->oCloudApi->getBaiduPreaInfo($cloud_params);
        // black
        $cloud_info['id_collection_black'] = $this->oCloudApi->getForeignBlackIdcard($data['identity']);
        $cloud_info['ph_collection_black'] = $this->oCloudApi->getForeignBlackPhone($data['mobile']);

        $cloud_info = array_merge($cloud_info, $origin_info, $risk_info, $prea_info);
        return $cloud_info;
    }

    private function getAntiDatas($data){
        // query anti
        $anti_data = $this->oAnalysis->queryAnti($data);
        // select operator data
        $operator_data = $this->oAnalysis->getReportInfo($anti_data);
        // select detail_tag
        $datail_tag = $this->oAnalysis->antiDetailTag($anti_data);
        $anti_all = array_merge($anti_data, $operator_data ,$datail_tag);
        return $anti_all;
    }

    private function getOriginData($params)
    {   
        # default       
        $default_arr = [
                'credit_score' => 0,
                'model_score_v2' => 0,
                'tianqi_score_v2' => -111,
                'is_black_tq' => 0,
            ];
        $orgin_res = $this->oCloudApi->queryCloud($params,'origin');
        if (!$orgin_res) {
            Logger::dayLog('CreditLogic/queryOriginCrif', '天启接口异常', $params);
            return $default_arr;
        }
        $data = ArrayHelper::getValue($orgin_res,'data',[]);
        if (empty($data)) {
            return $default_arr;
        }
        $data['is_black_tq'] = ArrayHelper::getValue($data,'is_black','0');
        unset($data['is_black']);
        return $data;
    }
}