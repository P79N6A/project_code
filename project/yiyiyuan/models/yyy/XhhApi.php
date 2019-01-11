<?php

namespace app\models\yyy;

use app\commonapi\Logger;
use app\models\dev\Fraudmetrix_return_info;
use app\models\dev\Loan_event;
use app\models\dev\Register_event;
use app\models\xs\XsApi;
use Yii;

/**
 * 一亿元接口
 */
class XhhApi {

    public $from = [
        '1' => 'weixin',
        '2' => 'ios',
        '3' => 'android',
        '4' => 'H5',
        '5' => 'API',
        '6' => 'bairong',
        '8' => 'rong360',
        '9' => 'jiedianqian',
    ];

    /**
     * 决策引擎接口
     * @param type $user
     * @param type $from 1:weixin 2:ios 3:android 4:H5
     * @param type $type  reg,loan
     * @param type $amount
     * @param type $days
     * @param type $desc
     * @return array
     */
    public function runDecisions($user, $from, $type = 'reg', $amount = 0, $days = 7, $desc = '') {
        $from = isset($this->from[$from]) ? $this->from[$from] : 'weixin';
        $start = date('Y-m-d H:i:s', strtotime('-1 hours'));
        $where = [
            'AND',
            ['>=', 'create_time', $start],
            ['user_id' => $user->user_id]
        ];
        if ($type == 'reg') {
            $count = Register_event::find()->where($where)->orderBy('id desc')->asArray()->one();
        } else {
            $count = Loan_event::find()->where($where)->orderBy('id desc')->asArray()->one();
        }
        if (!empty($count)) {
            return $this->compare($count);
        }



        if ($type == 'reg') {
            $res = $this->runDecisionsReg($user, $from);
        } else {
            $res = $this->runDecisionsLoan($user, $from, $amount, $days, $desc);
        }
        $limit = $this->runLimit($user, $type, $res);
        Logger::dayLog('fraulit', $user->user_id, $limit);
        if (!empty($limit)) {
            return $this->compare($limit);
        }
        $fraModel = new Fraudmetrix_return_info;
        if ($type == 'reg') {
            $res = $fraModel->saveRegGetFraudmetrix($user, $res);
        } else {
            $loan_no_keys = $user->user_id . "_loan_no";
            $loan_no = Yii::$app->redis->get($loan_no_keys);
            $res = $fraModel->saveLoanGetFraudmetrix($user, $res, $amount, $days, $desc, $loan_no);
        }
        if (!empty($res)) {
            $limit = $this->runLimit($user, $type, $res);
            Logger::errorLog(print_r(array('two'), true), 'typettt');
            Logger::errorLog(print_r($limit, true), 'fraureglimits');
            if (!empty($limit)) {
                return $this->compare($limit);
            }
        }
        return [];
    }

    /**
     * 注册事件
     */
    public function runDecisionsReg($user, $from) {
        //1. 注册事件数据录入
        $rengine = new RulesEngine;
        $post_data = $rengine->AssemblyDataByReg($user, $from);
        $api = new XsApi;
        $res = $api->runReg($post_data);
        Logger::errorLog(print_r($res, true), 'fraureg');
        if (!$res) {
            return $this->error("1001", "获取数据失败");
        }
        return $this->success($res);
    }

    /**
     * 借款事件
     */
    public function runDecisionsLoan($user, $from, $amount, $days, $desc) {
        //1. 借款事件数据录入
        $rengine = new RulesEngine;
        $post_data = $rengine->AssemblyDataByLoan($user, $from, $amount, $days, $desc);
        $api = new XsApi;

        Logger::errorLog(print_r($post_data, true), 'frauloan_post');
        $res = $api->runLoan($post_data);
        Logger::errorLog(print_r($res, true), 'frauloan');
        if (!$post_data) {
            return $this->error("1001", "获取数据失败");
        }
        return $this->success($res);
    }

    /**
     * 根据决策请求结果进行验证
     * @param type $user
     * @param type $type
     * @param type $res
     * @return type
     */
    private function runLimit($user, $type, $res) {
        if ($res['res_code'] != 0) {
            return $res;
        }
        $engine = new RulesEngine;
        if ($type == 'reg') {
            $limit = $engine->RegLimit($user, $res);
            Logger::errorLog(print_r($limit, true), 'fraureglimit');
        } else {
            $limit = $engine->LoanLimit($user, $res);
            Logger::errorLog(print_r($limit, true), 'frauloanlimit');
        }
        return $limit;
    }

    /**
     * 返回成功json
     * @param $res
     * @return json
     */
    private function success($res) {
        if (is_array($res)) {
            $res['res_code'] = '0';
        } else {
            $res = [
                'res_code' => '0',
                'res_data' => $res,
            ];
        }
        return $res;
        //return json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 返回错误json
     * @param $rsp_code
     * @param $res_data
     * @return json
     */
    private function error($rsp_code, $res_data) {
        return [
            'res_code' => (string) $rsp_code,
            'res_data' => $res_data,
        ];
    }

    private function compare($limit, $type = 'loan') {
        $keys = array_keys($limit);
        $event = [
            'loan' => ['loan_time_start', 'loan_time_end', 'age_value', 'more_loan_value', 'one_more_loan_value', 'seven_more_loan_value', 'one_number_account_value', 'is_black'],
            'reg' => ['age_value', 'area_value', 'number_value', 'ip_value', 'is_black'],
        ];
        $mark = 0;
        foreach ($keys AS $val) {
            if (in_array($val, $event[$type])) {
                $mark = 1;
                break;
            }
        }
        return $mark ? $limit : [];
    }

}
