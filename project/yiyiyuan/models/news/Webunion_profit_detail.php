<?php
namespace app\models\news;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "account".
 *
 * @property string $id
 * @property string $mobile
 * @property string $password
 * @property string $school
 * @property integer $edu_levels
 * @property string $entrance_time
 * @property string $account_name
 * @property string $identity
 * @property string $create_time
 */
class Webunion_profit_detail extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_webunion_profit_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
        ];
    }

    /**
     * 添加一条收益明细
     */
    public function addProfit($condition) {

        $profit = new Webunion_profit_detail();
        $profit->user_id = isset($condition['user_id']) ? $condition['user_id'] : '';
        $profit->type = isset($condition['type']) ? $condition['type'] : '';
        $profit->profit_id = isset($condition['profit_id']) ? $condition['profit_id'] : '';
        $profit->profit_amount = isset($condition['profit_amount']) ? $condition['profit_amount'] : '';
        $profit->profit_type = isset($condition['profit_type']) ? $condition['profit_type'] : '';
        $profit->status = isset($condition['status']) ? $condition['status'] : 0;
        $profit->standard_user_id = isset($condition['standard_user_id']) ? $condition['standard_user_id'] : 0;
        $profit->standard_id = isset($condition['standard_id']) ? $condition['standard_id'] : 0;
        $profit->create_time = date('Y-m-d H:i:s');
        $profit->last_modify_time = date('Y-m-d H:i:s');
        $profit->version = 1;

        if ($profit->save()) {
            $id = Yii::$app->db->getLastInsertID();
            return $id;
        } else {
            return false;
        }
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'profit_id']);
    }

    public function getLoan() {
        return $this->hasOne(User_loan::className(), ['loan_id' => 'profit_id']);
    }

    public function getInvest() {
        return $this->hasOne(User_invest::className(), ['invest_id' => 'profit_id']);
    }

    //关联标的订单信息表
    public function getStanorder() {
        return $this->hasOne(Standard_order::className(), ['id' => 'profit_id']);
    }

    //关联标的赎回表
    public function getStanreback() {
        return $this->hasOne(Standard_reback::className(), ['id' => 'profit_id']);
    }

    //关联标的信息表
    public function getStandard() {
        return $this->hasOne(Standard_information::className(), ['id' => 'standard_id']);
    }

    /**
     * 获取统计数据
     */
    public function getMonthStat($user_id) {
        $user_id = intval($user_id);
        if (!$user_id) {
            return null;
        }
        $connection = \Yii::$app->db;
        $table = Webunion_profit_detail::tableName();
        $sql = "SELECT
				  DATE_FORMAT(create_time,'%Y%m') AS ym,
				  SUM(profit_amount) AS total
				FROM yi_webunion_profit_detail
				WHERE profit_type = 2 AND user_id = {$user_id}
				GROUP BY ym
				ORDER BY ym DESC";

        $command = $connection->createCommand($sql);
        $statData = $command->queryAll();
        return $statData;
    }

    /**
     * 获取昨日收益统计数据
     */
    public function getYesterday($user_id) {
        $user_id = intval($user_id);
        if (!$user_id) {
            return null;
        }
        $o = static::find()->select(['sum(profit_amount) as profit_amount'])
                ->where(['user_id' => $user_id, 'profit_type' => 2])
                ->andFilterWhere(['between', 'create_time', date('Y-m-d', strtotime('-1 day')), date('Y-m-d')])
                ->one();

        if ($o && $o['profit_amount']) {
            $total = number_format($o['profit_amount'], 2, ".", "");
        } else {
            $total = 0.0;
        }
        return $total;
    }

    /**
     * 获取某月统计数据
     * @param int $user_id
     * @param str $month 某月第一天
     * @return int
     */
    public function getMonthCount($user_id, $month) {
        $user_id = intval($user_id);
        if (!$user_id) {
            return null;
        }
        //$startDay = date('Y-m-',strtotime($month).'01');
        $range = $this->getMonthRange($month);
        $condition = [
            'AND',
            ['user_id' => $user_id],
            ['profit_type' => 2],
            ['>=', 'create_time', $range[0]],
            ['<', 'create_time', $range[1]],
        ];
        return static::find()->where($condition)->count();
    }

    /**
     * 获取某月统计数据
     * @param int $user_id
     * @param str $month 某月第一天
     * @return []
     */
    public function getMonthDetail($user_id, $month, $offset = 0, $limit = 20) {
        $user_id = intval($user_id);
        if (!$user_id) {
            return null;
        }
        $range = $this->getMonthRange($month);
        $condition = [
            'AND',
            ['user_id' => $user_id],
            ['profit_type' => 2],
            ['>=', 'create_time', $range[0]],
            ['<', 'create_time', $range[1]],
        ];
        $data = static::find()->where($condition)
                //->with('user')
                ->orderBy('id ASC')
                ->offset($offset)
                ->limit($limit)
                ->all();
        return $data;
    }

    /**
     * 获取某月的范围 左开右闭区间
     * @param string $month 某月的第一天
     * @return [start,end]
     */
    private function getMonthRange($month) {
        $startDay = $month;
        $smonth = strtotime($month);
        $nextMonth = $smonth + 86400 * date('t', $smonth) + 86400;
        $endDay = date('Y-m-d', $nextMonth);
        return [$startDay, $endDay];
    }

    /**
     * 获取收益原因
     */
    public function formatReason(&$data) {
        if (empty($data)) {
            return null;
        }

        // 获取会员id
        $user_ids = [];
        $loan_ids = [];
        $invest_ids = [];
        $task_ids = [];
        $standard_statistics_ids = [];
        foreach ($data as $r) {
            $type = $r['type'];
            if ($type == 1 || $type == 2) {
                //为好友id
                $user_ids[] = $r['profit_id'];
            } elseif ($type == 3 || $type == 4) {
                //为借款id
                $loan_ids[] = $r['profit_id'];
            } elseif ($type == 5) {
                //投资id
                $invest_ids[] = $r['profit_id'];
            } elseif ($type == 7) {
                $task_ids[] = $r['profit_id'];
            } elseif ($type == 8) {
                $standard_statistics_ids[] = $r['profit_id'];
            }
        }

        //从标的统计信息表中再次获得user_id
        $standard_statisticsData = [];
        if (!empty($standard_statistics_ids)) {
            $standard_statisticsData = \app\models\dev\Standard_statistics::findAll($standard_statistics_ids);
            $ids = ArrayHelper::getColumn($standard_statisticsData, 'user_id');
            if (is_array($ids)) {
                $user_ids = array_merge($user_ids, $ids);
            }
            $standard_statisticsData = ArrayHelper::index($standard_statisticsData, 'id');
        }

        // 从任务表中再次获得user_id
        $taskData = [];
        if (!empty($task_ids)) {
            $taskData = \app\models\dev\Task::findAll($task_ids);
            $ids = ArrayHelper::getColumn($taskData, 'user_id');
            if (is_array($ids)) {
                $user_ids = array_merge($user_ids, $ids);
            }
            $taskData = ArrayHelper::index($taskData, 'id');
        }


        // 从借款表中再次获取user_id
        $loanData = [];
        if (!empty($loan_ids)) {
            $loanData = \app\models\dev\User_loan::findAll($loan_ids);
            $ids = ArrayHelper::getColumn($loanData, 'user_id');
            if (is_array($ids)) {
                $user_ids = array_merge($user_ids, $ids);
            }
            $loanData = ArrayHelper::index($loanData, 'loan_id');
        }

        // 从投资表中再次获取user_id
        $investData = [];
        if (!empty($invest_ids)) {
            $investData = \app\models\dev\User_invest::findAll($invest_ids);
            $ids = ArrayHelper::getColumn($investData, 'user_id');
            if (is_array($ids)) {
                $user_ids = array_merge($user_ids, $ids);
            }
            $investData = ArrayHelper::index($investData, 'invest_id');
        }

        // 去重处理并获取用户名
        $user_ids = array_unique($user_ids);
        $userMap = [];
        if (!empty($user_ids)) {
            $userData = \app\models\dev\User::findAll($user_ids);
            if (!empty($userData)) {
                $userMap = ArrayHelper::map($userData, 'user_id', 'realname');
            }
        }

        // 重组数据
        $data = ArrayHelper::toArray($data);
        foreach ($data as &$r) {
            $r['realname'] = '';
            $r['amount'] = 0;
            $r['reason'] = '';
            $type = $r['type'];
            if ($type == 1 || $type == 2) {
                // 邀请好友
                $r['realname'] = isset($userMap[$r['profit_id']]) ? $userMap[$r['profit_id']] : '';
                $r['reason'] = "邀请好友" . $r['realname'];
            } elseif ($type == 3 || $type == 4) {
                //为借款
                $user_id = isset($loanData[$r['profit_id']]) ? $loanData[$r['profit_id']]['user_id'] : '';
                if ($user_id) {
                    $r['realname'] = isset($userMap[$user_id]) ? $userMap[$user_id] : '';
                }
                $r['amount'] = isset($loanData[$r['profit_id']]) ? $loanData[$r['profit_id']]['amount'] : 0;
                $r['amount'] = $r['amount'] > 0 ? number_format($r['amount'], 2, ".", "") : '-';
                $r['reason'] = $r['realname'] . "借款" . $r['amount'] . '元';
            } elseif ($type == 5) {
                //投资id
                $user_id = isset($investData[$r['profit_id']]) ? $investData[$r['profit_id']]['loan_user_id'] : '';
                if ($user_id) {
                    $r['realname'] = isset($userMap[$user_id]) ? $userMap[$user_id] : '';
                }
                $r['amount'] = isset($investData[$r['profit_id']]) ? $investData[$r['profit_id']]['amount'] : 0;
                $r['amount'] = $r['amount'] > 0 ? number_format($r['amount'], 2, ".", "") : '-';
                $r['reason'] = $r['realname'] . "投资" . $r['amount'] . '元';
            } elseif ($type == 7) {
                $user_id = isset($taskData[$r['profit_id']]) ? $taskData[$r['profit_id']]['user_id'] : '';
                if ($user_id) {
                    $r['realname'] = isset($userMap[$user_id]) ? $userMap[$user_id] : '';
                }
                $r['amount'] = $r['profit_amount'];
                $r['amount'] = $r['amount'] > 0 ? number_format($r['profit_amount'], 2, ".", "") : '-';
                $r['reason'] = "完成赚钱任务";
            } elseif ($type == 8) {
                $user_id = isset($standard_statisticsData[$r['profit_id']]) ? $standard_statisticsData[$r['profit_id']]['user_id'] : '';
                if ($user_id) {
                    $r['realname'] = isset($userMap[$user_id]) ? $userMap[$user_id] : '';
                }
                $r['amount'] = isset($standard_statisticsData[$r['profit_id']]) ? $standard_statisticsData[$r['profit_id']]['total_onInvested'] : 0;
                $r['amount'] = $r['amount'] > 0 ? number_format($r['amount'], 2, ".", "") : '-';
                $r['reason'] = $r['realname'] . "投资标的" . $r['amount'] . '元';
            }
        }
        return $data;
    }

    /**
     * 邀请人解冻金额计算
     * @param type $invite_user 邀请人
     * @param type $user 被邀请人
     */
    public function getAmount($invite_user, $user, $loan) {
        $reg_fronz = Webunion_profit_detail::find()->where(['user_id' => $invite_user->user_id, 'profit_id' => $user->user_id, 'status' => 1, 'profit_type' => 2, 'type' => 2])->one(); //注册收益
        $loan_fronz = Webunion_profit_detail::find()->where(['user_id' => $invite_user->user_id, 'profit_id' => $loan->loan_id, 'status' => 1, 'profit_type' => 2, 'type' => 3])->one(); //借款收益
        $profit_amount_reg = !empty($reg_fronz) ? $reg_fronz->profit_amount : 0;
        $profit_amount_loan = !empty($loan_fronz) ? $loan_fronz->profit_amount : 0;
        $amount = $profit_amount_reg + $profit_amount_loan;
        return $amount;
    }

}
