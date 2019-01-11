<?php

/**
 * 好友首贷逾期，清除邀请人相应的冻结金额
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

class RemovefrozenmoneyController extends Controller {

    // 命令行入口文件
    public function actionIndex() {
        $startdate = date('2016-10-24 00:00:00');
        $today = date('Y-m-d 00:00:00');
        $total = User_loan::find()->joinWith('user', TRUE, 'LEFT JOIN')->where([User_loan::tableName() . '.end_date' => $today, User_loan::tableName() . '.status' => [12,13]])->count();
        $pages = ceil($total / 1000);
        for ($i = 0; $i < $pages; $i++) {
            $loan = User_loan::find()->joinWith('user', TRUE, 'LEFT JOIN')->where([User_loan::tableName() . '.end_date' => $today, User_loan::tableName() . '.status' => [12,13]])->offset($i * 1000)->limit(1000)->all();
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
                            $invite_user = User::find()->where(['invite_code' => $val->user->from_code])->one();
                            $amount = (new Webunion_profit_detail)->getAmount($invite_user, $val->user, $val);
                            $result = $this->frozenMoney($invite_user, -1*$amount, $val);
                            if ($result) {
                                $transaction->commit();
                                Logger::errorLog(print_r(array('invite_user_id' => '邀请人ID' . $invite_user->user_id, 'user_id' => '被邀请人ID' . $val->user->user_id, 'amount' => $amount, 'loan_id' => $val->loan_id), true), 'webunion_remove_frozen', 'crontab');
                            } else {
                                $transaction->rollBack();
                                Logger::errorLog(print_r(array('invite_user_id' => $invite_user->user_id, 'user_id' => $val->user->user_id, 'amount' => 0), true), 'webunion_remove_frozen_error', 'crontab');
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * 减少邀请人的冻结收益
     * @param type $user 邀请人
     */
    private function frozenMoney($user, $amount, $loan) {
        $webunion = Webunion_account::find()->where(['user_id' => $user->user_id])->one();
        $ret = $webunion->setAccountinfo($webunion->user_id, array('frozen_interest' => $amount));
        if ($ret) {
            $this->addDetail($user, 4, 2, abs($amount), $loan);
            return true;
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
    private function addDetail($user, $type, $status, $amount, $loan = '') {
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
