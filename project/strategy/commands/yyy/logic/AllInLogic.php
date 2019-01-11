<?php
/**
 * 一亿元 整合借款决策
 */
namespace app\commands\yyy\logic;

use app\commands\configdata\UserLimit;
use app\common\AntifrauApi;
use app\models\MobileWhiteList;
use app\models\ygy\YgyUser;
use app\models\ygy\YgyUserLoan;
use app\models\yyy\UserLoan;
use App\User;
use Yii;
use yii\helpers\ArrayHelper;

use app\common\Logger;
use app\common\Xgboost;
use app\common\FdXgBoostApi;
use app\common\Ganoderma;
use app\models\Request;
use app\models\Result;
use app\models\StAntiLoan;
use app\models\StloanExtend;
use app\models\StNotify;
use app\models\StAmount;
use app\models\antifraud\AfDetailTag;
use app\models\StrategyRequest;
use app\modules\api\common\JavaCrif;
use app\commands\yyy\common\AllinApi;
use app\commands\base\logic\BaseLogic;
use app\models\yyy\QjUserCredit;
use app\modules\promeapi\common\SaveFunc;


class AllInLogic extends BaseLogic
{
    protected $oStrategyReq;
    protected $oAllinApi;
    private $oXgboostApi;
    private $oFdXgBoostApi;
    private $oGanodermaApi;
    protected $org_from;
    private $default_res;
    private $oantifrau;

    public function __construct()
    {
        parent::__construct();
        $this->oStrategyReq = new StrategyRequest();
        $this->oAllinApi = new AllinApi();
        $this->oXgboostApi = new Xgboost();
        $this->oFdXgBoostApi = new FdXgBoostApi();
        $this->oGanodermaApi = new Ganoderma();
        $this->oantifrauApi = new AntifrauApi();
        $this->org_from = Yii::$app->params['from']['STRATEGY_ALLIN_ORIGIN'];
        $this->default_res = [
            'AMOUNT' =>  '0',
            'CARD_MONEY' =>  '0',
            'CRAD_RATE' =>  '0',
            'DAYS' =>  '0',
            'INTEREST_RATE' =>  '0',
            'RESULT' => Result::STATUS_REJECT, //驳回
            'ious_days' => '0',
            'ious_status' => '0',
            'result_tq' => '0',
            'result_model_tq' => '0',
        ];
    }

    public function runAllin($from, $aid)
    {
        // 1.获取st_strategy_request表数据
        $strateReqs = $this->oStrategyReq->getByStatus(StrategyRequest::OPERA_SUCCESS,$from,$aid);
        if (empty($strateReqs)) {
            Logger::dayLog('runallin', 'run', 'there is nothing data to deal with');
            return false;
        }
        // 2.锁定状态
        if (SYSTEM_PROD) {
            $strateReqIds = ArrayHelper::getColumn($strateReqs, 'id');
            $lockRes = $this->oStrategyReq->lockStrateReq($strateReqIds, StrategyRequest::JAVA_DOING);
            if (!$lockRes) {
                Logger::dayLog('runallin', 'lockStrateReq', $strateReqIds, 'strateReq锁定失败');
                return false;
            }
        }
        $success_num = 0;
        // 3.导入到st_request表，并调用java决策
        foreach ($strateReqs as $strateReq) {
            try {
            // 1)存入request表并请求java决策记录结果
                $strateReqId = $strateReq['id'];
                $from = ArrayHelper::getValue($strateReq,'come_from',0);
                $javaCrif = $this->strategy($strateReq, $strateReqId, $from);
                $requestId = ArrayHelper::getValue($javaCrif,'request_id',0);

                // 2)插入通知表
                $oStNotify = new StNotify();
                $resNotify = $oStNotify->saveData($strateReqId, $from);
                if(!$resNotify) {
                    Logger::dayLog('runallin', 'saveNotify', $requestId, '通知表插入失败:'.$oStNotify->errinfo);
                }

                // 3)回写成功的状态
                $resStrateReq = $this->oStrategyReq->updateStatus($strateReqId, StrategyRequest::JAVA_SUCCESS);
                if (!$resStrateReq) {
                    Logger::dayLog('runallin', 'updStatus', $strateReqId, 'strateReq表更新失败');
                }
                $success_num++;
            } catch (\Exception $e) {
                Logger::dayLog('runallin/error','run',$strateReqId,'定时执行失败:'.$e->getMessage());
            }
        }
        return $success_num;
    }

    private function strategy($strateReq, $strateReqId, $from)
    {
        // 1)存入st_request表
        $stReqInfo = [
            'user_id' => $strateReq['user_id'],
            'from' => $from,
            'loan_id' => $strateReq['loan_id'],
            'prd_type' => $strateReq['aid'],
            'req_id' => $strateReqId,
        ];
        $oStRequest = new Request();
        $requestId = $oStRequest->addRequest($stReqInfo);
        if (!$requestId) {
            Logger::dayLog('runallin/strategy', 'saveReq', $strateReqId, 'st_request表保存失败:' . $oStRequest->errinfo);
            $this->default_res['request_id'] = 0;
            // error_code
            $this->default_res['error_code'] = '10001';
            $save_res = $this->saveResult($strateReq, $this->default_res, $from);
            return $save_res;
        }
        $this->default_res['request_id'] = $requestId;
        $strateReq['request_id'] = $requestId;
        // 2)获取java请求参数
        $javaData = $this->oAllinApi->getAllinInfo($strateReq);
        if (empty($javaData)) {
            Logger::dayLog('runallin', 'javaCreditData', $strateReqId, '请求参数异常');
            // error_code
            $this->default_res['error_code'] = '10002';
            $save_res = $this->saveResult($strateReq, $this->default_res, $from);
            return $save_res;
        }
        // 3)存储java决策请求参数
        try {
            if (isset($javaData['company_name'])) {
                unset($javaData['company_name']);
            }
            if (isset($javaData['location'])) {
                unset($javaData['location']);
            }
            $save_res = $this->saveAllData($javaData);
        } catch (\Exception $e) {
            Logger::dayLog('runallin/save', 'save_error', $e->getMessage());
        }
        //用户测评时是否存在未结清的账单
        $javaData['is_loaning'] = 0;
        $user_id = ArrayHelper::getValue($strateReq, 'user_id', 0);
        try {
            //$user_id = 1212111221111;
            if (!empty($user_id)) {
                $oUserLoan = new UserLoan();
                $uncleared_count = $oUserLoan->getUncleared($user_id);
                if ($uncleared_count){
                    $javaData['is_loaning'] = $uncleared_count;
                }

            }
        } catch(\Exception $e){
            Logger::dayLog("queryError", "is_loaning error:",$e->getMessage());
        }
        // if (empty($resAntiLoan)) {
        Logger::dayLog('runallin/javaCreditData', 'javaCreditData', json_encode($javaData));
        //     return $res;
        // }
        // 4)调用java决策
        $javaCrif = $this->oJavaCrif->queryCrif($requestId, $javaData, JavaCrif::PRO_CODE_ALLIN);
        // var_dump($javaCrif);die;
        if (empty($javaCrif)) {
            Logger::dayLog('runallin', 'javaCrif', $requestId, '决策异常');
            $this->default_res['error_code'] = '10003';
            $save_res = $this->saveResult($strateReq, $this->default_res, $from);
            return $save_res;
        }
        $javaCrif['request_id'] = $requestId;
        // 5) 授信决策存入结果记录表中
        $save_res = $this->saveResult($strateReq, $javaCrif ,$from);
        if (!$save_res) {
            Logger::dayLog('runallin/strategy','javaCrif',$strateReqId,'授信决策储存失败');
        }
        // 6) 请求天启决策
        $org_res = $this->javaOrigin($javaCrif, $strateReq, $javaData);
        return $javaCrif;
    }

    public function queryOriginCrif(&$user_crif_data, $prome_crif_res,$process_code)
    {
        // 1, 初始数据
        $user_crif_keys = [
            'aid',
            'come_from',
            'credit_score',
            'is_black_tq',
            'loan_total',
            'model_score_v2',
            'business_type',
            'loan_id',
            'source',
            'quota',
            'mobile',
            'success_num',
            'tianqi_score_v2',
            'type',
            'user_id',
            'mid_fm_one_m',
            'mid_fm_seven_d',
            'mid_fm_three_m',
            'mph_fm_one_m',
            'mph_fm_seven_d',
            'mph_fm_three_m',
            'multi_small_p_class_7',
            'multi_small_p_class_30',
            'multi_p2p_p_class_7',
            'multi_p2p_p_class_30',
            'multi_common_p_class_7',
            'multi_common_p_class_30',
            'multi_big_p_class_7',
            'multi_big_p_class_30',
            'multi_all_p_class_7',
            'multi_all_p_class_30',
            'wst_dlq_sts',
        ];
        $prome_tq_arr = $this->oYArray->getByKeys($user_crif_data, $user_crif_keys, 0);
        $prome_tq_arr['PROME_V4_RESULT'] = ArrayHelper::getValue($prome_crif_res, 'PROME_V4_RESULT', 0);
        $prome_tq_arr['PROME_V4_SCORE'] = ArrayHelper::getValue($prome_crif_res, 'PROME_V4_SCORE', 0);
        $prome_tq_arr['result_status'] = ArrayHelper::getValue($prome_crif_res, 'RESULT', 0);
        $prome_tq_arr['Strategy_RESULT'] = ArrayHelper::getValue($prome_crif_res, 'Strategy_RESULT', 0);
        $idcard = ArrayHelper::getValue($user_crif_data, 'idcard', '');
        # 临时7天借款表信息
        $prome_tq_arr['qj_credit'] = 0;
        $qj_credit = (new QjUserCredit)->getUserByIdentity($idcard);
        if ($qj_credit) {
            $prome_tq_arr['qj_credit'] = 1;
        }
        # days对应的当天购卡金额
        $oStAmount = new StAmount();
        $day_amount = $oStAmount->getDayAmount();

        //临时d_test56 （0未在名单中，1在名单中）start
        $oUserLimit = new UserLimit();
        Logger::dayLog("UserLimit", "data1:",json_encode($user_crif_data));
        $user_id = ArrayHelper::getValue($user_crif_data, 'user_id', '');
        $prome_tq_arr['fd_test56'] = $oUserLimit->searchUser($user_id);
        Logger::dayLog("UserLimit", "data1:",$prome_tq_arr['fd_test56']);
        //临时需求end

        # get detail_tag
        $user_id = ArrayHelper::getValue($user_crif_data, 'user_id', '');
        $oAfDetailTag = new AfDetailTag();
        $express = $oAfDetailTag->getExpress(['user_id' => $user_id]);
        //获取详单标签数据
        $detail_tag = $oAfDetailTag->getDetailTag($user_id);
        $xgboostParams = array_merge($user_crif_data,$prome_crif_res,$detail_tag);
        try {
            # 查询xgchoost接口
            $prome_tq_arr['xg_prob'] = $this->oXgboostApi->xgboostOpen($xgboostParams); 
        } catch (\Exception $e) {
            Logger::dayLog("queryError", "xgchoost error:",$e->getMessage());
            $prome_tq_arr['xg_prob'] = -111;
        }
        //复贷请求fdxgboots
        $fd_xgprob_data = array_merge($user_crif_data, $prome_crif_res, $detail_tag);
        try {
            # 查询fdxgchoost接口
            $prome_tq_arr['fd_xgprob'] = $this->oFdXgBoostApi->fdboostOpen($fd_xgprob_data);
            //Logger::dayLog("aaa", "ganoderma error:", $prome_tq_arr['ganoderma_score']);
        } catch (\Exception $e) {
            Logger::dayLog('queryError', 'fd_xgprob error:', $e->getMessage());
            $prome_tq_arr['fd_xgprob'] = -111;
        }
        $result_lz = ArrayHelper::getValue($prome_crif_res,'result_lz','0');
        if ($result_lz == 1) {
            try {
                # 查询灵芝接口
                $prome_tq_arr['ganoderma_score'] = $this->oGanodermaApi->ganodermaOpen($user_crif_data);
            } catch (\Exception $e) {
                Logger::dayLog("queryError", "ganoderma error:",$e->getMessage());
                $prome_tq_arr['ganoderma_score'] = -111;
            }
        }
        //腾讯分
        $result_tx = ArrayHelper::getValue($prome_crif_res, "result_tx", 0);
        if ($result_tx == 1){
            try {
                $prome_tq_arr['tencent_score'] = $this->oantifrauApi->requestOpen($user_crif_data);
            } catch (\Exception $e) {
                Logger::dayLog("queryError", "tencent_score error:",$e->getMessage());
                $prome_tq_arr['tencent_score'] = -111;
            }

        }

        //增加白名单
        $prome_tq_arr['is_test'] = 0;
        try {
            $oMobileWhiteList = new MobileWhiteList();
            $while_info = $oMobileWhiteList->getIsWhilte($user_id);
            if ($while_info){
                $prome_tq_arr['is_test'] = 1;
            }
        } catch(\Exception $e){
            Logger::dayLog("queryError", "is_test_tq error:",$e->getMessage());
        }
        # get last success loan data
        $last_success_loan_data = $this->oAllinApi->getLastSuccessLoanData($user_id);
        $prome_tq_arr = array_merge($prome_tq_arr,$day_amount,$express,$last_success_loan_data);
        // 2, 天启接口数据
        $result_tq = ArrayHelper::getValue($prome_crif_res,'result_tq','0');
        if ($result_tq == '1') {
            $keys = [
                'name',
                'idcard',
                'phone',
                'user_id',
                'loan_id',
                'aid',
            ];
            $params = $this->oYArray->getByKeys($user_crif_data, $keys, '');
            $data_tq_arr = $this->getOriginData($params);
            // 合并并覆盖初始天启数据
            $prome_tq_arr = array_merge($prome_tq_arr,$data_tq_arr);
        }
        // 3, 请求决策
        $request_id = ArrayHelper::getValue($user_crif_data,'request_id','0');
        // $process_code = JavaCrif::PRO_CODE_PROME_TQ;
        $crif_res = $this->oJavaCrif->queryCrif($request_id,$prome_tq_arr,$process_code);
        Logger::dayLog('aa', '天启决策异常', $crif_res);
        var_dump($crif_res);
        exit;
        if (empty($crif_res)) {
            Logger::dayLog('runallin/queryOriginCrif', '天启决策异常', $prome_tq_arr);
        }
        return $crif_res;
    }

    // 天启决策
    private function javaOrigin($javaCrif, $strateReq, &$javaData){
        if (!SYSTEM_PROD) {
            # test data
            // $javaCrif['result_tq'] = '1';
            // $javaCrif['result_lz'] = '1';
            // $javaCrif['result_model_tq'] = '1';
        }
        $result_model_tq = ArrayHelper::getValue($javaCrif,'result_model_tq',0);
        $strateReqId = ArrayHelper::getValue($strateReq,'id',0);
        if ($result_model_tq == '1') {
            $process_code = JavaCrif::PRO_CODE_XHH_TQ;
            $origin_crif_res = $this->queryOriginCrif($javaData,$javaCrif,$process_code);
            if (empty($origin_crif_res)) {
                Logger::dayLog('runcredit/javaOrigin','javaOrigin',$strateReqId,'天启决策异常');
                $this->default_res['error_code'] = '10004';
                $save_res = $this->saveResult($strateReq, $this->default_res, $this->org_from);
                return $save_res;
            }
            // 记录天启结果
            $save_res = $this->saveResult($strateReq, $origin_crif_res, $this->org_from);
            if (!$save_res) {
                Logger::dayLog('runcredit/javaOrigin','javaOrigin',$strateReqId,'授信决策储存失败');
            }
            return $save_res;
        }
        return true;
    }
    // save result
    private function saveResult($strateReq, $javaCrif, $from){
        $oResult = new Result();
        $strateReq['from'] = $from;
        $save_res = $oResult->saveRes($strateReq, $javaCrif);
        if (!$save_res) {
            Logger::dayLog('runcredit/saveResult', 'saveResult', $oResult->errinfo, $strateReq);
        }
        return $save_res;
    }

    private function saveAllData(&$user_crif_data) {
        # save prome
        $prome_keys = [
            'aid' ,
            'request_id',
            'loan_id',
            'req_id',
            'user_id',
            'query_time',
            'retain_ratio',
            'last_3mth_Oth_ratio',
            'last_3mth_oth_incr',
            'becalled_ratio',
            'com_c_user',
            'report_type',
        ];
        $prome_params = $this->oYArray->getByKeys($user_crif_data, $prome_keys, '');
        $prome_params['yy_request_id'] = $prome_params['req_id'];
        $save_prome_res = (new saveFunc)->saveProme($prome_params);
        # save anti
        $anti_keys = [
            'request_id',
            'loan_id',
            'user_id',
            'addr_contacts_count',
            'addr_relative_count' ,
            'com_r_total_mavg',
            'com_c_total_mavg' ,
            'com_r_rank',
            'com_c_total',
            'com_r_total',
            'addr_count',
            'report_use_time',
            'report_loan_connect',
            'report_110',
            'report_120',
            'report_lawyer',
            'report_aomen',
            'report_court'          ,
            'report_fcblack',
            'report_shutdown',
            'com_hours_connect',
            'com_valid_all',
            'com_valid_mobile',
            'vs_phone_match',
            'vs_valid_match',
            'addr_has_black',
            'report_night_percent',
            'addr_collection_count',
            'loan_create_time',
            'aid',
        ];
        $anti_params = $this->oYArray->getByKeys($user_crif_data, $anti_keys, '');
        $save_anti_res = (new StAntiLoan)->saveAnti($anti_params);
        # save loan_extend
        $loan_extend_keys = [
            'loan_id',
            'mth6_dlq_ratio',
            'mth3_dlq7_num',
            'mth3_wst_sys',
            'mth3_dlq_num',
            'wst_dlq_sts',
            'create_time',
            'request_id',
        ];
        $loan_extend_params = $this->oYArray->getByKeys($user_crif_data, $loan_extend_keys, '');
        $save_extend_res = (new StloanExtend)->addInfo($loan_extend_params);
        return true;
    }
}
