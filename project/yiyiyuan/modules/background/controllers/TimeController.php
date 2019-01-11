<?php

namespace app\modules\background\controllers;

use Yii;
use app\commands\SubController;
use app\models\dev\Webunion_broadcast;
use app\models\dev\Webunion_profit_detail;
use app\models\dev\Webunion_user_list;
use app\models\dev\User_bank;
use app\models\dev\User;
use app\models\dev\Standard_order;
use app\models\dev\Standard_reback;
use app\models\dev\Standard_statistics;
use app\models\dev\Standard_information;
use app\models\dev\Webunion_feedback;
use app\models\dev\Webunion_account;
use app\models\dev\Webunion_flow_settlement;
use app\models\dev\Webunion_notice;
use app\commonapi\Common;

class TimeController extends SubController {

    public $layout = 'webunion';

    //给前一天投资的用户父类用户增加冻结收益
    public function actionIndex() {
        $begin_time = date('Y-m-d 00:00:00', (time() - 24 * 3600));
        $end_time = date('Y-m-d 00:00:00');
        $nowtime = date('Y-m-d H:i:s');

        $condition = "create_time >= '$begin_time' and create_time <= '$end_time' and buy_type='GENE' and order_status='SUCCESS'";

        $total = Standard_order::find()->Where($condition)->count();
        //每100条处理一次
        $limit = 100;
        $pages = ceil($total / $limit);

        $this->log("\n" . date('Y-m-d H:i:s') . "......................");
        $this->log("\n共{$total}条数据:每次处理{$limit},需要要处理{$pages}次\n");

        $rate = 0.02;
        $rate_one = 5;
        $rate_two = 3;
        $rate_three = 1;
        $score_type = 8;
        $type = 1;

        for ($i = 0; $i < $pages; $i++) {
            $offset = $i * $limit;
            $standard_orders = Standard_order::find()->where($condition)->limit($limit)->offset($offset)->all();
            if (empty($standard_orders)) {
                break;
            }

            foreach ($standard_orders as $key => $value) {
                $user = User::find()->where(['user_id' => $value->user_id])->asArray()->one();
                // print_r($user);die;
                $standardInfo = Standard_information::findOne($value->standard_id);
                $user_id = $user['user_id'];
                if (!empty($user['from_code'])) {
                    $userinfo_first = User::find()->select(array('status', 'user_id', 'is_webunion', 'from_code'))->where(['invite_code' => $user['from_code']])->andWhere("user_id != $user_id")->one();

                    $account = new Webunion_account();

                    //第一级父级用户获取五成的收益
                    if (!empty($userinfo_first) && ($userinfo_first->status != 5) && ($userinfo_first->is_webunion == 'yes')) {
                        $webunion_count_first = Webunion_account::find()->where(['user_id' => $userinfo_first->user_id])->count();

                        if ($webunion_count_first == 0) {
                            $user_account_first = array(
                                'user_id' => $userinfo_first->user_id
                            );
                            $result = $account->addAccount($user_account_first);
                        }

                        $interest_first = $value->buy_amount * $rate / 365 * $standardInfo->cycle * $rate_one / 9;
                        if ($interest_first < 0.01) {
                            $interest_first = 0.01;
                        }
                        $account_first = Webunion_account::find()->where(['user_id' => $userinfo_first->user_id])->one();
                        $interest_first = ceil($interest_first * 100) / 100;
                        $account_first->frozen_interest += $interest_first;
                        $account_first->version += 1;
                        $account_first->last_modify_time = $nowtime;
                        $account_first->save();
                        //添加一条收益明细
                        $ret_check_first = $this->addUserProfit($userinfo_first->user_id, $score_type, $value->id, $interest_first, 2, $type, $value->standard_id, $user_id);
                    }
                    //父父级用户获取三成的收益
                    if (!empty($userinfo_first->from_code)) {
                        $userinfo_second = User::find()->select(array('status', 'user_id', 'is_webunion', 'from_code'))->where(['invite_code' => $userinfo_first->from_code])->andWhere("user_id != $user_id")->one();

                        if (!empty($userinfo_second) && ($userinfo_second->status != 5) && ($userinfo_second->is_webunion == 'yes')) {
                            $webunion_count_second = Webunion_account::find()->where(['user_id' => $userinfo_second->user_id])->count();

                            if ($webunion_count_second == 0) {
                                $user_account_second = array(
                                    'user_id' => $userinfo_second->user_id
                                );
                                $result = $account->addAccount($user_account_second);
                            }

                            $interest_second = $value->buy_amount * $rate / 365 * $standardInfo->cycle * $rate_two / 9;
                            if ($interest_second < 0.01) {
                                $interest_second = 0.01;
                            }
                            $account_second = Webunion_account::find()->where(['user_id' => $userinfo_second->user_id])->one();
                            $interest_second = ceil($interest_second * 100) / 100;
                            $account_second->frozen_interest += $interest_second;
                            $account_second->version += 1;
                            $account_second->last_modify_time = $nowtime;
                            $account_second->save();
                            //添加一条收益明细
                            $ret_check_second = $this->addUserProfit($userinfo_second->user_id, $score_type, $value->id, $interest_second, 2, $type, $value->standard_id, $user_id);
                        }
                        //父父父级用户获取1成的收益
                        if (!empty($userinfo_second->from_code)) {
                            $userinfo_third = User::find()->select(array('status', 'user_id', 'is_webunion', 'from_code'))->where(['invite_code' => $userinfo_second->from_code])->andWhere("user_id != $user_id")->one();

                            if (!empty($userinfo_third) && ($userinfo_third->status != 5) && ($userinfo_third->is_webunion == 'yes')) {
                                $webunion_count_third = Webunion_account::find()->where(['user_id' => $userinfo_third->user_id])->count();

                                if ($webunion_count_third == 0) {
                                    $user_account_third = array(
                                        'user_id' => $userinfo_third->user_id
                                    );
                                    $result = $account->addAccount($user_account_third);
                                }
                                $interest_third = $value->buy_amount * $rate / 365 * $standardInfo->cycle * $rate_three / 9;
                                if ($interest_third < 0.01) {
                                    $interest_third = 0.01;
                                }
                                $account_third = Webunion_account::find()->where(['user_id' => $userinfo_third->user_id])->one();
                                $interest_third = ceil($interest_third * 100) / 100;
                                $account_third->frozen_interest += $interest_third;
                                $account_third->version += 1;
                                $account_third->last_modify_time = $nowtime;
                                $account_third->save();
                                //添加一条收益明细
                                $ret_check_third = $this->addUserProfit($userinfo_third->user_id, $score_type, $value->id, $interest_third, 2, $type, $value->standard_id, $user_id);
                            }
                        }
                    }
                }
            }
        }
    }

    // 给前一天赎回投资的用户父类减少冻结收益
    public function actionReback() {
        $begin_time = date('Y-m-d 00:00:00', (time() - 24 * 3600));
        $end_time = date('Y-m-d 00:00:00');
        $nowtime = date('Y-m-d H:i:s');

        $condition = "create_time >= '$begin_time' and create_time <= '$end_time' and request_status='SUCCESS'";

        $total = Standard_reback::find()->Where($condition)->count();
        //每100条处理一次
        $limit = 100;
        $pages = ceil($total / $limit);

        $this->log("\n" . date('Y-m-d H:i:s') . "......................");
        $this->log("\n共{$total}条数据:每次处理{$limit},需要要处理{$pages}次\n");

        $rate = 0.02;
        $rate_one = 5;
        $rate_two = 3;
        $rate_three = 1;
        $score_type = 8;
        $type = 2;

        for ($i = 0; $i < $pages; $i++) {
            $offset = $i * $limit;
            $Standard_rebacks = Standard_reback::find()->where($condition)->limit($limit)->offset($offset)->all();
            if (empty($Standard_rebacks)) {
                break;
            }
            foreach ($Standard_rebacks as $key => $value) {
                $user = User::find()->where(['user_id' => $value->user_id])->asArray()->one();
                // print_r($user);die;
                $standardInfo = Standard_information::findOne($value->standard_id);
                $user_id = $user['user_id'];
                if (!empty($user['from_code'])) {
                    $userinfo_first = User::find()->select(array('status', 'user_id', 'is_webunion', 'from_code'))->where(['invite_code' => $user['from_code']])->andWhere("user_id != $user_id")->one();

                    $account = new Webunion_account();

                    //第一级父级用户获取五成的收益
                    if (!empty($userinfo_first) && ($userinfo_first->status != 5) && ($userinfo_first->is_webunion == 'yes')) {
                        $webunion_count_first = Webunion_account::find()->where(['user_id' => $userinfo_first->user_id])->count();

                        if ($webunion_count_first == 0) {
                            $user_account_first = array(
                                'user_id' => $userinfo_first->user_id
                            );
                            $result = $account->addAccount($user_account_first);
                        }
                        $interest_first = $value->transfer_amount * $rate / 365 * $standardInfo->cycle * $rate_one / 9;
                        if ($interest_first < 0.01) {
                            $interest_first = 0.01;
                        }
                        $account_first = Webunion_account::find()->where(['user_id' => $userinfo_first->user_id])->one();
                        $interest_first = ceil($interest_first * 100) / 100;
                        $account_first->frozen_interest -= $interest_first;
                        $account_first->version += 1;
                        $account_first->last_modify_time = $nowtime;
                        $account_first->save();
                        //添加一条收益明细
                        $ret_check_first = $this->addUserProfit($userinfo_first->user_id, $score_type, $value->id, $interest_first, 2, $type, $value->standard_id, $user_id);
                    }
                    //父父级用户获取三成的收益
                    if (!empty($userinfo_first->from_code)) {
                        $userinfo_second = User::find()->select(array('status', 'user_id', 'is_webunion', 'from_code'))->where(['invite_code' => $userinfo_first->from_code])->andWhere("user_id != $user_id")->one();

                        if (!empty($userinfo_second) && ($userinfo_second->status != 5) && ($userinfo_second->is_webunion == 'yes')) {
                            $webunion_count_second = Webunion_account::find()->where(['user_id' => $userinfo_second->user_id])->count();

                            if ($webunion_count_second == 0) {
                                $user_account_second = array(
                                    'user_id' => $userinfo_second->user_id
                                );
                                $result = $account->addAccount($user_account_second);
                            }
                            $interest_second = $value->transfer_amount * $rate / 365 * $standardInfo->cycle * $rate_two / 9;
                            if ($interest_second < 0.01) {
                                $interest_second = 0.01;
                            }
                            $account_second = Webunion_account::find()->where(['user_id' => $userinfo_second->user_id])->one();
                            $interest_second = ceil($interest_second * 100) / 100;
                            $account_second->frozen_interest -= $interest_second;
                            $account_second->version += 1;
                            $account_second->last_modify_time = $nowtime;
                            $account_second->save();
                            //添加一条收益明细
                            $ret_check_second = $this->addUserProfit($userinfo_second->user_id, $score_type, $value->id, $interest_second, 2, $type, $value->standard_id, $user_id);
                        }
                        //父父父级用户获取1成的收益
                        if (!empty($userinfo_second->from_code)) {
                            $userinfo_third = User::find()->select(array('status', 'user_id', 'is_webunion', 'from_code'))->where(['invite_code' => $userinfo_second->from_code])->andWhere("user_id != $user_id")->one();

                            if (!empty($userinfo_third) && ($userinfo_third->status != 5) && ($userinfo_third->is_webunion == 'yes')) {
                                $webunion_count_third = Webunion_account::find()->where(['user_id' => $userinfo_third->user_id])->count();

                                if ($webunion_count_third == 0) {
                                    $user_account_third = array(
                                        'user_id' => $userinfo_third->user_id
                                    );
                                    $result = $account->addAccount($user_account_third);
                                }
                                $interest_third = $value->transfer_amount * $rate / 365 * $standardInfo->cycle * $rate_three / 9;
                                if ($interest_third < 0.01) {
                                    $interest_third = 0.01;
                                }
                                $account_third = Webunion_account::find()->where(['user_id' => $userinfo_third->user_id])->one();
                                $interest_third = ceil($interest_third * 100) / 100;
                                $account_third->frozen_interest -= $interest_third;
                                $account_third->version += 1;
                                $account_third->last_modify_time = $nowtime;
                                $account_third->save();
                                //添加一条收益明细
                                $ret_check_third = $this->addUserProfit($userinfo_third->user_id, $score_type, $value->id, $interest_third, 2, $type, $value->standard_id, $user_id);
                            }
                        }
                    }
                }
            }
        }
    }

    // 给前一天完成投资的用户父类，将冻结收益转换为可提现收益
    public function actionRepay() {
        $starttime = date('Y-m-d', strtotime('-1 days'));
        $endtime = date('Y-m-d');
        $nowtime = date('Y-m-d H:i:s');
        $condition = "status = 'FINISHED' and end_date >='$starttime' and end_date<'$endtime'";
        //获取昨天完结的标的信息
        $standards = Standard_information::find()->where($condition)->all();
        $rate = 0.02;
        $rate_one = 5;
        $rate_two = 3;
        $rate_three = 1;
        $score_type = 8;
        if (!empty($standards)) {
            foreach ($standards as $key => $val) {
                //获取标的投标信息统计信息
                $total = Standard_statistics::find()->where(['standard_id' => $val->id, 'user_type' => 'NORMAL'])->andWhere(['>', 'total_onInvested', 0])->count();
                //每100条处理一次
                $limit = 100;
                $pages = ceil($total / $limit);

                $this->log("\n" . date('Y-m-d H:i:s') . "......................");
                $this->log("\n共{$total}条数据:每次处理{$limit},需要要处理{$pages}次\n");
                for ($i = 0; $i < $pages; $i++) {
                    $offset = $i * $limit;
                    $standard_statistics = Standard_statistics::find()->where(['standard_id' => $val->id, 'user_type' => 'NORMAL'])->andWhere(['>', 'total_onInvested', 0])->all();
                    if (empty($standard_statistics)) {
                        break;
                    }

                    $account = new Webunion_account();

                    foreach ($standard_statistics as $k => $v) {
                        $user_id = $v->user_id;
                        //根据统计信息获取投标用户信息
                        $user = User::find()->where(['user_id' => $v->user_id])->one();

                        //为父级用户返现
                        if (!empty($user->from_code)) {
                            //获取父级用户信息
                            $userinfo_first = User::find()->select(array('status', 'user_id', 'is_webunion', 'from_code'))->where(['invite_code' => $user->from_code])->one();
                            //判断父级用户状态是否正确
                            if (!empty($userinfo_first) && ($userinfo_first->status != 5) && ($userinfo_first->is_webunion == 'yes')) {

                                $webunion_count_first = Webunion_account::find()->where(['user_id' => $userinfo_first->user_id])->count();

                                if ($webunion_count_first == 0) {
                                    $user_account_first = array(
                                        'user_id' => $userinfo_first->user_id
                                    );
                                    $result = $account->addAccount($user_account_first);
                                }
                                $interest_first = $v->total_onInvested * $rate / 365 * $val->cycle * $rate_one / 9;
                                if ($interest_first >= 0.01) {
                                    $account_first = Webunion_account::find()->where(['user_id' => $userinfo_first->user_id])->one();
                                    $interest_first = ceil($interest_first * 100) / 100;
                                    $account_first->frozen_interest -= $interest_first;
                                    $account_first->total_history_interest += $interest_first;
                                    $account_first->version += 1;
                                    $account_first->last_modify_time = $nowtime;
                                    $account_first->save();
                                    //添加一条收益明细
                                    $ret_check_first = $this->addUserProfit($userinfo_first->user_id, $score_type, $v->id, $interest_first, 2, 4, $v->standard_id, $user_id);
                                }
                            }
                            if (!empty($userinfo_first->from_code)) {
                                //获取父父级用户信息
                                $userinfo_second = User::find()->select(array('status', 'user_id', 'is_webunion', 'from_code'))->where(['invite_code' => $userinfo_first->from_code])->one();
                                //判断父父级用户状态是否正确
                                if (!empty($userinfo_second) && ($userinfo_second->status != 5) && ($userinfo_second->is_webunion == 'yes')) {

                                    $webunion_count_second = Webunion_account::find()->where(['user_id' => $userinfo_second->user_id])->count();

                                    if ($webunion_count_second == 0) {
                                        $user_account_second = array(
                                            'user_id' => $userinfo_second->user_id
                                        );
                                        $result = $account->addAccount($user_account_second);
                                    }
                                    $interest_second = $v->total_onInvested * $rate / 365 * $val->cycle * $rate_two / 9;
                                    if ($interest_second >= 0.01) {
                                        $account_second = Webunion_account::find()->where(['user_id' => $userinfo_second->user_id])->one();
                                        $interest_second = ceil($interest_second * 100) / 100;
                                        $account_second->frozen_interest -= $interest_second;
                                        $account_second->total_history_interest += $interest_second;
                                        $account_second->version += 1;
                                        $account_second->last_modify_time = $nowtime;
                                        $account_second->save();
                                        //添加一条收益明细
                                        $ret_check_second = $this->addUserProfit($userinfo_second->user_id, $score_type, $v->id, $interest_second, 2, 4, $v->standard_id, $user_id);
                                    }
                                }
                                if (!empty($userinfo_second->from_code)) {
                                    //获取父父级用户信息
                                    $userinfo_third = User::find()->select(array('status', 'user_id', 'is_webunion', 'from_code'))->where(['invite_code' => $userinfo_second->from_code])->one();
                                    //判断父父级用户状态是否正确
                                    if (!empty($userinfo_third) && ($userinfo_third->status != 5) && ($userinfo_third->is_webunion == 'yes')) {

                                        $webunion_count_third = Webunion_account::find()->where(['user_id' => $userinfo_third->user_id])->count();

                                        if ($webunion_count_third == 0) {
                                            $user_account_third = array(
                                                'user_id' => $userinfo_third->user_id
                                            );
                                            $result = $account->addAccount($user_account_third);
                                        }
                                        $interest_third = $v->total_onInvested * $rate / 365 * $val->cycle * $rate_three / 9;
                                        if ($interest_third) {
                                            $account_third = Webunion_account::find()->where(['user_id' => $userinfo_third->user_id])->one();
                                            $interest_third = ceil($interest_third * 100) / 100;
                                            $account_third->frozen_interest -= $interest_third;
                                            $account_third->total_history_interest += $interest_third;
                                            $account_third->version += 1;
                                            $account_third->last_modify_time = $nowtime;
                                            $account_third->save();
                                            //添加一条收益明细
                                            $ret_check_second = $this->addUserProfit($userinfo_third->user_id, $score_type, $v->id, $interest_third, 2, 4, $v->standard_id, $user_id);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            echo '今天没有要处理的数据';
        }
    }

    //添加收益明细
    private function addUserProfit($user_id, $type, $profit_id, $profit_amount, $profit_type, $status, $standard_id, $standard_user_id) {
        $profit_score = array(
            'user_id' => $user_id,
            'type' => $type,
            'profit_id' => $profit_id,
            'profit_amount' => $profit_amount,
            'profit_type' => $profit_type,
            'status' => $status,
            'standard_id' => $standard_id,
            'standard_user_id' => $standard_user_id
        );
        $ret_score = (new Webunion_profit_detail)->addProfit($profit_score);
        if ($ret_score) {
            return true;
        } else {
            return false;
        }
    }

    // 保存日志
    private function log($message) {
        echo $message . "\n";
    }

}
