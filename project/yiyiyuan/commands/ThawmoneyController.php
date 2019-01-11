<?php

/**
 * 邀请好友首贷还款成功，解除冻结金额，并且发放5元现金
 */
/**
 * 1 注意这里引入文件必须是绝对路径。相对路径容易出错
 * 2 使用
 *   linux : /data/wwwroot/yiyiyuan/yii income > /data/wwwroot/yiyiyuan/log/income.log (修改根目录下yii文件的php的解析路径)
 *   window : d:\xampp\php\php.exe D:\www\yiyiyuan\yii income
 */

namespace app\commands;

use app\commonapi\Logger;
use app\models\dev\User;
use app\models\dev\User_loan;
use app\models\dev\Webunion_account;
use app\models\dev\Webunion_profit_detail;
use Yii;
use yii\console\Controller;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class ThawmoneyController extends Controller {

    // 命令行入口文件
    public function actionIndex() {
        $startdate = date('2016-10-24 00:00:00');
        $start_time = date('Y-m-d 00:00:00', strtotime('-1 days'));
        $end_time = date('Y-m-d 00:00:00');
        $total = User_loan::find()->joinWith('user', TRUE, 'LEFT JOIN')->where(['>=', User_loan::tableName() . '.repay_time', $start_time])->andFilterWhere(['<', User_loan::tableName() . '.repay_time', $end_time])->andWhere([User_loan::tableName() . '.status' => 8])->andWhere(User_loan::tableName() . '.chase_amount IS NULL')->count();
        $pages = ceil($total / 1000);
        for ($i = 0; $i < $pages; $i++) {
            $loan = User_loan::find()->joinWith('user', TRUE, 'LEFT JOIN')->where(['>=', User_loan::tableName() . '.repay_time', $start_time])->andFilterWhere(['<', User_loan::tableName() . '.repay_time', $end_time])->andWhere([User_loan::tableName() . '.status' => 8])->andWhere(User_loan::tableName() . '.chase_amount IS NULL')->offset($i * 1000)->limit(1000)->all();
            if (!empty($loan)) {
                foreach ($loan as $key => $val) {
                    if (empty($val->user->from_code)) {
                        continue;
                    } else {
                        $overloan = User_loan::find()->where(['user_id' => $val->user->user_id, 'status' => 8])->count();
                        if ($overloan > 1) {
                            continue;
                        } else {
                            if ($val->user->verify_time < $startdate) {
                                continue;
                            }
                            $transaction = Yii::$app->db->beginTransaction();
                            $result = $this->thaw($val->user, $val);
                            $invite_user = User::find()->where(['invite_code' => $val->user->from_code])->one();

                            if ($result) {
                                $transaction->commit();
                                Logger::errorLog(print_r(array('invite_user_id' => $invite_user->user_id, 'user_id' => $val->user->user_id, 'loan_id' => $val->loan_id), true), 'webunion_account', 'crontab');
                            } else {
                                $transaction->rollBack();
                                Logger::errorLog(print_r(array('invite_user_id' => $invite_user->user_id, 'user_id' => $val->user->user_id, 'amount' => 0, 'loan_id' => $val->loan_id), true), 'webunion_account_error', 'crontab');
                            }
                        }
                    }
                }
            }
        }

//        $this->log("\n处理结果:success{$sucess}个用户发送成功");
    }

    /**
     * 解冻邀请人的邀请认证收益，借款收益，并且添加5元
     * @param type $user 还款成功的user
     */
    private function thaw($user, $loan) {
        $invite_user = User::find()->where(['invite_code' => $user->from_code])->one();
        if (empty($invite_user)) {
            return false;
        }

        $webunion = Webunion_account::find()->where(['user_id' => $invite_user->user_id])->one();
        $l_money = (new Webunion_profit_detail)->getAmount($invite_user, $user, $loan);
        $amount = $l_money + 10;
        if (empty($webunion)) {
            $webaccountModel = new Webunion_account();
            $webaccountModel->addAccount(['user_id' => $invite_user->user_id]);
            $webunion = Webunion_account::find()->where(['user_id' => $invite_user->user_id])->one();
        }
        if (empty($webunion)) {
            return false;
        }
        $ret = $webunion->setAccountinfo($webunion->user_id, array('total_history_interest' => $amount, 'frozen_interest' => -1 * $l_money));
        if ($ret) {
            $res_9 = $this->addDetail($invite_user, 9, $l_money, $loan);
            if ($res_9) {
                $rest = $this->addDetail($invite_user, 9, $l_money, $loan, 2);
                $res_2 = $this->addDetail($invite_user, 2, 10, $user);
                return $res_2 && $rest ? true : false;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 
     * @param type $user
     * @param type $loan
     * @param type $type  9：好友首贷按时还款成功,2：推荐好友审核通过
     * @param type $amount
     */
    private function addDetail($user, $type, $amount, $loan = '', $status = 0) {
        $webDetailModel = new Webunion_profit_detail();
        $condition = array(
            'user_id' => $user->user_id,
            'type' => $type,
            'profit_amount' => $amount,
            'profit_type' => 2,
            'status' => $status,
        );
        if (!empty($loan)) {
            $condition['profit_id'] = $type == 2 ? $loan->user_id : $loan->loan_id;
        }
        $ret = $webDetailModel->addProfit($condition);
        if ($ret) {
            return true;
        } else {
            return false;
        }
    }

    // 纪录日志
    private function log($message) {
        echo $message . "\n";
    }

}
