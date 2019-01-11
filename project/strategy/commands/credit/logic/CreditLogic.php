<?php
/**
 *  智融决策逻辑
 */
namespace app\commands\credit\logic;

use app\commands\configdata\UserLimit;
use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\commands\base\logic\BaseLogic;
use app\models\yyy\QjUserCredit;

class CreditLogic extends BaseLogic
{
    public function __construct()
    {
        parent::__construct();
    }

    public function queryOriginCrif(&$user_crif_data, $prome_crif_res,$process_code)
    {
        // 1, 初始数据
        $user_crif_keys = [
            'come_from',
            'credit_score',
            'is_black_tq',
            'loan_total',
            'model_score_v2',
            'source',
            'success_num',
            'tianqi_score_v2',
            'type',
            'quota',
            'user_id',
            'mid_fm_one_m',
            'mid_fm_seven_d',
            'mid_fm_three_m',
            'mph_fm_one_m',
            'mph_fm_seven_d',
            'mph_fm_three_m',
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

        //临时d_test56 （0未在名单中，1在名单中）start
        $oUserLimit = new UserLimit();
        Logger::dayLog("UserLimit", "data:",json_encode($user_crif_data));
        $user_id = ArrayHelper::getValue($user_crif_data, 'user_id', '');
        $prome_tq_arr['fd_test56'] = $oUserLimit->searchUser($user_id);
        Logger::dayLog("UserLimit", "fd_test56:",$prome_tq_arr['fd_test56']);
        //临时需求end

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
        if (empty($crif_res)) {
            Logger::dayLog('CreditLogic/queryOriginCrif', '天启决策异常', $prome_tq_arr);
        }
        return $crif_res;
    }
}
