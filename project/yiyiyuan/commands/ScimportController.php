<?php

/**
 * 资方排期导入
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/11
 * Time: 16:00
 */

namespace app\commands;

use app\models\news\Plan;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class ScimportController extends Controller {

    public function actionIndex() {
        $funds = [1, 10, 11];
        for ($i = 0; $i < 3; $i++) {
            $days = date('Y-m-d');
            $choice_time = date('Y-m-d', strtotime("$i days"));
            foreach ($funds as $f_unds) {
                $getFundData = (new Plan())->getFundTime($f_unds, $choice_time);
                if (!empty($getFundData)) {
                    continue;
                }
                $fund_plan = (new Plan())->getSectionLastone($f_unds);
                if (empty($fund_plan) || $fund_plan['plan_time'] < $days) {
                    $data = $this->showData($f_unds, $choice_time);
                } else {
                    $fund_plan['plan_time'] = date("Y-m-d", strtotime($fund_plan['plan_time']) + 86400);
                    $fund_plan['start_time'] = date("Y-m-d H:i:s", strtotime($fund_plan['start_time']) + 86400);
                    $fund_plan['end_time'] = date("Y-m-d H:i:s", strtotime($fund_plan['end_time']) + 86400);
                    $data = [
                        'name' => ArrayHelper::getValue($fund_plan, 'name', ''), //名称
                        'fund' => ArrayHelper::getValue($fund_plan, 'fund', ''), //资方
                        'status' => ArrayHelper::getValue($fund_plan, 'status', 0), //初始; 1:锁定中; 2:成功完成; 3:待处理(同0); 4:关闭; 5:精确匹配中
                        'sort_num' => ArrayHelper::getValue($fund_plan, 'sort_num', 50), //排序,默认50, 1-100正序
                        'is_accuracy' => ArrayHelper::getValue($fund_plan, 'is_accuracy', 0), //精确匹配: 0:非; 1:是
                        'start_time' => ArrayHelper::getValue($fund_plan, 'start_time', ''), //开始时间
                        'end_time' => ArrayHelper::getValue($fund_plan, 'end_time', ''), //结束时间
                        'max_estimate' => ArrayHelper::getValue($fund_plan, 'max_estimate', 0), //预留最大值
                        'max_real' => ArrayHelper::getValue($fund_plan, 'max_real', 0), //实际留存金额(含失败)
                        'max_do_estimate' => ArrayHelper::getValue($fund_plan, 'max_do_estimate', 0), //预留处理金额
                        'max_do_real' => ArrayHelper::getValue($fund_plan, 'max_do_real', 0), //实际处理金额(非失败)
                        'max_success_money' => ArrayHelper::getValue($fund_plan, 'max_success_money', 0), //实际处理成功金额
                        'threshold' => ArrayHelper::getValue($fund_plan, 'threshold', 0), //阀值
                        'admin_id' => ArrayHelper::getValue($fund_plan, 'admin_id', 1), //管理员ID
                        'plan_time' => ArrayHelper::getValue($fund_plan, 'plan_time', ''), //排期时间
                    ];
                }
                if (!empty($data)) {
                    $this->saveData($data);
                }
            }
        }
    }

    private function showData($fund, $choice_time) {
        $funds = [
            '1' => [
                'name' => '花生米富', //名称
                'fund' => '1', //资方, 1:花生米富, 2:玖富, 3:联交所, 4:金联储, 5:小诺, 6:微神马, 10:银行存管
                'status' => 4, //初始; 1:锁定中; 2:成功完成; 3:待处理(同0); 4:关闭; 5:精确匹配中
                'sort_num' => 4, //排序,默认50, 1-100正序
                'is_accuracy' => 0, //精确匹配: 0:非; 1:是
                'start_time' => $choice_time . " 00:00:00", //开始时间
                'end_time' => $choice_time . " 23:59:59", //结束时间
                'max_estimate' => '10000000', //预留最大值
                'max_real' => 0, //实际留存金额(含失败)
                'max_do_estimate' => '10000000', //预留处理金额
                'max_do_real' => 0, //实际处理金额(非失败)
                'max_success_money' => 0, //实际处理成功金额
                'threshold' => 10000, //阀值
                'admin_id' => 1, //管理员ID
                'plan_time' => $choice_time, //排期时间
            ],
            '10' => [
                'name' => '银行存管', //名称
                'fund' => '10', //资方, 1:花生米富, 2:玖富, 3:联交所, 4:金联储, 5:小诺, 6:微神马, 10:银行存管
                'status' => 0, //初始; 1:锁定中; 2:成功完成; 3:待处理(同0); 4:关闭; 5:精确匹配中
                'sort_num' => 2, //排序,默认50, 1-100正序
                'is_accuracy' => 0, //精确匹配: 0:非; 1:是
                'start_time' => $choice_time . " 00:00:00", //开始时间
                'end_time' => $choice_time . " 23:59:59", //结束时间
                'max_estimate' => '10000000', //预留最大值
                'max_real' => 0, //实际留存金额(含失败)
                'max_do_estimate' => '10000000', //预留处理金额
                'max_do_real' => 0, //实际处理金额(非失败)
                'max_success_money' => 0, //实际处理成功金额
                'threshold' => 10000, //阀值
                'admin_id' => 1, //管理员ID
                'plan_time' => $choice_time, //排期时间
            ],
            '11' => [
                'name' => '其他', //名称
                'fund' => '11', //资方, 1:花生米富, 2:玖富, 3:联交所, 4:金联储, 5:小诺, 6:微神马, 10:银行存管
                'status' => 0, //初始; 1:锁定中; 2:成功完成; 3:待处理(同0); 4:关闭; 5:精确匹配中
                'sort_num' => 6, //排序,默认50, 1-100正序
                'is_accuracy' => 0, //精确匹配: 0:非; 1:是
                'start_time' => $choice_time . " 00:00:00", //开始时间
                'end_time' => $choice_time . " 23:59:59", //结束时间
                'max_estimate' => '800000', //预留最大值
                'max_real' => 0, //实际留存金额(含失败)
                'max_do_estimate' => '800000', //预留处理金额
                'max_do_real' => 0, //实际处理金额(非失败)
                'max_success_money' => 0, //实际处理成功金额
                'threshold' => 10000, //阀值
                'admin_id' => 1, //管理员ID
                'plan_time' => $choice_time, //排期时间
            ],
        ];
        return isset($funds[$fund]) ? $funds[$fund] : [];
    }

    private function saveData($datra_set) {
        $oplan = new Plan();
        $fund = ArrayHelper::getValue($datra_set, 'fund', '');
        $plan_time = ArrayHelper::getValue($datra_set, 'plan_time', '');
        $getFundData = $oplan->getFundTime($fund, $plan_time);
        if (!empty($getFundData)) {
            return false;
        }
        $save_data = [
            'name' => ArrayHelper::getValue($datra_set, 'name', ''), //名称
            'fund' => $fund, //资方
            'status' => ArrayHelper::getValue($datra_set, 'status', 0), //初始; 1:锁定中; 2:成功完成; 3:待处理(同0); 4:关闭; 5:精确匹配中
            'sort_num' => ArrayHelper::getValue($datra_set, 'sort_num', 50), //排序,默认50, 1-100正序
            'is_accuracy' => ArrayHelper::getValue($datra_set, 'is_accuracy'), //精确匹配: 0:非; 1:是
            'start_time' => ArrayHelper::getValue($datra_set, 'start_time'), //开始时间
            'end_time' => ArrayHelper::getValue($datra_set, 'end_time'), //结束时间
            'max_estimate' => ArrayHelper::getValue($datra_set, 'max_estimate', 0), //预留最大值
            'max_real' => ArrayHelper::getValue($datra_set, 'max_real', 0), //实际留存金额(含失败)
            'max_do_estimate' => ArrayHelper::getValue($datra_set, 'max_do_estimate', 0), //预留处理金额
            'max_do_real' => ArrayHelper::getValue($datra_set, 'max_do_real', 0), //实际处理金额(非失败)
            'max_success_money' => ArrayHelper::getValue($datra_set, 'max_success_money', 0), //实际处理成功金额
            'threshold' => ArrayHelper::getValue($datra_set, 'threshold', 0), //阀值
            'admin_id' => ArrayHelper::getValue($datra_set, 'admin_id', 1), //管理员ID
            'plan_time' => ArrayHelper::getValue($datra_set, 'plan_time', ''), //排期时间
        ];
        return $oplan->saveData($save_data);
    }

}
