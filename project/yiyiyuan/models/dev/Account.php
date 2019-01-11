<?php

namespace app\models\dev;

use Yii;
use app\commonapi\Http;

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
class Account extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_account';
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
     * 创建用户的账户
     */
    public function createAccount($user_id, $userinfo = array()) {
        if (empty($user_id)) {
            return null;
        }
        $amount = floatval(200); //默认额度
        $current_amount = floatval($amount);
        $remain_amount = floatval(100000000 - $amount);

        $array_account = array(
            'user_id' => $user_id,
            'remain_amount' => $remain_amount,
            'amount' => $amount,
            'current_amount' => $current_amount,
            'create_time' => date('Y-m-d H:i:s')
        );
        $result = $this->addAccount($array_account);
        //记录提额的日志
        $amount_date = array(
            'type' => 2,    
            'user_id' => $user_id,
            'amount' => $amount
        );
        $user_amount = new User_amount_list();
        $user_amount->CreateAmount($amount_date);
        return $result;
    }

    /**
     * 获取学生用户的分数
     * @param unknown $userinfo
     * @return 分数
     */
    public function getUserScore($userinfo) {
        $userscore = array(
            'city' => 0,
            'school' => 0,
            'grade' => 0,
            'degree' => 0
        );
        $school_id = $userinfo['school_id'];
        $identity = substr($userinfo['identity'], 0, 4);
        $grade = $this->getGrade($userinfo['school_time']);
        $edu = $userinfo['edu'];
        $sql = "select number,level,score,type from " . Score::tableName() . " where ";
        $sql .= "(number=$school_id and type ='school') or ";
        $sql .= "(number='$identity' and type='city') or ";
        $sql .= "(number='$grade' and type='grade') or ";
        $sql .= "(number='$edu' and type='degree')";
        $ret = Yii::$app->db->createCommand($sql)->queryAll();

        if ($ret) {
            foreach ($ret as $val) {
                if ($val['type'] == 'city') {
                    $userscore['city'] = $val['score'];
                }
                if ($val['type'] == 'school') {
                    $userscore['school'] = $val['score'];
                }
                if ($val['type'] == 'grade') {
                    $userscore['grade'] = $val['score'];
                }
                if ($val['type'] == 'degree') {
                    $userscore['degree'] = $val['score'];
                }
            }
        }

        return $userscore;
    }

    /**
     * 获取社会用户的分数
     * @param unknown $userinfo
     * @return 分数
     */
    public function getWorkScore($userinfo) {
        $workscore = array(
            'city' => 0,
            'work' => 0,
            'job' => 0
        );
        $identity = substr($userinfo['identity'], 0, 4);
        $job = $userinfo['industry'];
        $work = $userinfo['position'];
        $sql = "select number,level,score,type from " . Score::tableName() . " where ";
        $sql .= "(number='$identity' and type='city') or ";
        $sql .= "(number='$job' and type='job') or ";
        $sql .= "(number='$work' and type='work')";
        $ret = Yii::$app->db->createCommand($sql)->queryAll();

        if ($ret) {
            foreach ($ret as $val) {
                if ($val['type'] == 'city') {
                    $workscore['city'] = $val['score'];
                }
                if ($val['type'] == 'job') {
                    $workscore['job'] = $val['score'];
                }
                if ($val['type'] == 'work') {
                    $workscore['work'] = $val['score'];
                }
            }
        }

        return $workscore;
    }

    /**
     * 获取入学时间
     * @param unknown $school_time
     * @return number
     */
    public function getGrade($school_time) {
        $school_time = strtotime($school_time . "-09"); //从9月开始算
        $now_time = strtotime(date('Y-m', time()));

        $grade = floor(( $now_time - $school_time) / (60 * 60 * 24 * 365));
        return $grade;
    }

    /**
     * 添加账户
     * 
     * @return number
     */
    public function addAccount($condition) {
        $account = new Account();
        $account->user_id = $condition['user_id'];
        $account->remain_amount = $condition['remain_amount'];
        $account->amount = $condition['amount'];
        $account->current_amount = $condition['current_amount'];
        $account->create_time = date('Y-m-d H:i:s');

        if ($account->save()) {
            $id = Yii::$app->db->getLastInsertID();
            return $id;
        } else {
            return false;
        }
    }

    /**
     * 提额
     * @param type $userinfo    用户信息
     * @param type $type    提额类型
     * @param type $num 提额数目
     */
    public function updateAccount($userinfo, $type, $num) {
        $user_id = $userinfo['user_id'];
        $account = $userinfo->account;
        $amount = floatval($num) + $account->amount;
        $current_amount = floatval($num) + $account->current_amount;
        $create_time = date('Y-m-d H:i:s', time());
        $remain_amount = floatval(100000000 - $amount);
        $condition = array(
            'remain_amount' => $remain_amount,
            'amount' => $amount,
            'current_amount' => $current_amount,
        );
        if ($account->updateAccountinfo($condition)) {
            //记录提额的日志
            $amount_date = array(
                'type' => $type,
                'user_id' => $user_id,
                'amount' => floatval($num),
            );
            $user_amount = new User_amount_list();
            $user_amount->CreateAmount($amount_date);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 更新账户信息
     * 
     * @return bool
     */
    public function setAccountinfo($user_id, $condition = array()) {
        if (empty($user_id) || empty($condition)) {
            return null;
        }

        $account = Account::find()->where(['user_id' => $user_id])->one();
        if (isset($condition['remain_amount'])) {
            $account->remain_amount -= $condition['remain_amount'];
        }
        if (isset($condition['amount'])) {
            $account->amount += $condition['amount'];
        }
        if (isset($condition['recharge_amount'])) {
            $account->recharge_amount += $condition['recharge_amount'];
        }
        if (isset($condition['guarantee_amount'])) {
            $account->guarantee_amount += $condition['guarantee_amount'];
        }
        if (isset($condition['current_amount'])) {
            $account->current_amount += $condition['current_amount'];
        }
        if (isset($condition['real_guarantee_amount'])) {
            $account->real_guarantee_amount += $condition['real_guarantee_amount'];
        }
        if (isset($condition['current_loan'])) {
            $account->current_loan +=$condition['current_loan'];
        }
        if (isset($condition['total_income'])) {
            $account->total_income +=$condition['total_income'];
        }
        $account->version += 1;

        if ($account->save() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function updateAccountInfo($condition) {
        if (empty($condition)) {
            return null;
        }
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $this->version += 1;
        if ($this->save() > 0) {
            return $this;
        } else {
            return false;
        }
    }

    /**
     * 修改投资的投资额度和可用额度
     */
    public function setInvestCurrentAmount($user_id, $amount, $version) {
        $sql_invest_account = "update " . Account::tableName() . " set current_invest=(current_invest+" . $amount . "),current_amount=(current_amount-" . $amount . "),total_invest=(total_invest+" . $amount . "),version=(version+1) where user_id=" . $user_id . " and version=" . $version;

        $ret_invest_account = Yii::$app->db->createCommand($sql_invest_account)->execute();
        if ($ret_invest_account) {
            return true;
        } else {
            return false;
        }
    }

}
