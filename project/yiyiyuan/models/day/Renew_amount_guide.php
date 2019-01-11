<?php

namespace app\models\day;

use Yii;

/**
 * This is the model class for table "yi_loan_repay_guide".
 *
 * @property string $id
 * @property string $repay_id
 * @property string $user_id
 * @property string $loan_id
 * @property integer $bank_id
 * @property integer $platform
 * @property integer $source
 * @property string $pic_repay1
 * @property string $pic_repay2
 * @property string $pic_repay3
 * @property integer $status
 * @property string $money
 * @property string $actual_money
 * @property string $pay_key
 * @property string $code
 * @property string $paybill
 * @property string $last_modify_time
 * @property string $createtime
 * @property string $repay_time
 * @property string $repay_mark
 * @property integer $version
 */
class Renew_amount_guide extends \app\models\BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'qj_renew_amount';
    }

    public function getLoan() {
        return $this->hasOne(User_loan_guide::className(), ['loan_id' => 'loan_id']);
    }

    public function getUser() {
        return $this->hasOne(User_guide::className(), ['user_id' => 'user_id']);
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['id', 'loan_id', 'type', 'user_id', 'parent_loan_id', 'mark'], 'integer'],
            [['renew_fee', 'chase_fee', 'renew'], 'number'],
            [['last_modify_time', 'create_time', 'start_time', 'end_time'], 'safe'],
        ];
    }

    /**
     * 判断借款是否有展期资格
     * @param $loan_id
     * @return array|null|\yii\db\ActiveRecord
     * @author 代威群
     * @date 2018/8/3 16:41
     */
    public function getRenewFeeNew($loan) {
        if (empty($loan)) {
            return FALSE;
        }
        $now_time = date('Y-m-d H:i:s');
        $where = [
            'AND',
            ['loan_id' => $loan->loan_id],
            ['<=', 'start_time', $now_time],
            ['>=', 'end_time', $now_time]
        ];
        $renew = self::find()->where($where)->one();
        if (!empty($renew)) {
            return $renew->renew_fee;
        }
        return $loan->withdraw_fee + $loan->amount * 0.2;
    }

    /**
     * 查询当前借款是否有续期记录
     * @param $loan
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getRenew($loan_id, $now_date = '') {
        if (empty($loan_id) || !is_numeric($loan_id)) {
            return [];
        }
        $renew = self::find()->where(['loan_id' => $loan_id, 'mark' => 2])->orderBy('id desc')->one();
        if (!empty($renew)) {
            return $renew;
        }
        if (empty($now_date)) {
            $now_date = date('Y-m-d H:i:s');
        }
        $where = [
            'AND',
            ['loan_id' => $loan_id],
            ['<=', 'start_time', $now_date],
            ['>=', 'end_time', $now_date],
            ['mark' => 1]
        ];
        $renew = self::find()->where($where)->orderBy('id desc')->one();
        return $renew;
    }

    /**
     * 添加状态为一的续期记录
     * @param $parent_loan
     * @param $renew
     * @param $end_date
     * @param $res
     * @return bool
     */
    public function addFirstRecord($loan, $renew_fee = 0, $mark = 1, $type = 1, $end_date = '', $start_time = '') {
        if (empty($loan)) {
            return false;
        }
        $re = 0.05;
        if ($renew_fee == 0) {
            $renew_fee = $loan->withdraw_fee + $loan->amount * 0.05;
        }
        $nowTime = date('Y-m-d H:i:s');
        if (empty($start_time)) {
            $start_time = $loan->start_date;
        }
        $data = [
            'loan_id' => $loan->loan_id,
            'renew_fee' => $renew_fee,
            'user_id' => $loan->user_id,
            'parent_loan_id' => $loan->parent_loan_id,
            'mark' => $mark,
            'type' => $type,
            'start_time' => $start_time,
            'end_time' => !empty($end_date) ? $end_date : $loan->end_date,
            'create_time' => $nowTime,
            'last_modify_time' => $nowTime,
            'renew' => $re
        ];
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 修改记录
     * @param $condition
     * @return bool
     * @author 王新龙
     * @date 2018/8/3 17:46
     */
    public function updateRecord($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data = $condition;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        try {
            $result = $this->save();
            return $result;
        } catch (\Exception $ex) {
            return FALSE;
        }
    }

    public function getLoanByTime($startTime, $endTime) {
        $sql = "SELECT a.* from qj_loan_repay as a LEFT JOIN qj_overdue_loan as b  on a.loan_id=b.loan_id where a.last_modify_time >= '$startTime' and a.last_modify_time < '$endTime' and a.status = 1 and b.`id` > 0 GROUP BY repay_id";
        return self::findBySql($sql)->all();
    }

    /**
     * 
     * @return type
     */
    public function getRepayByLoanId($loanId) {
        if (empty($loanId)) {
            return null;
        }

        $where = [
            'AND',
            ['status' => '1'],
            ['loan_id' => $loanId],
        ];
        return self::find()->where($where)->all();
    }

    /**
     * 
     * @return type
     */
    public function getOfflineRepayByLoanId($loanId) {
        if (empty($loanId)) {
            return null;
        }

        $where = [
            'AND',
//            ['status' => '1'],
            ['loan_id' => $loanId],
            ['!=', 'pic_repay1', ''],
            ['NOT', ['pic_repay1' => null]],
        ];
        return self::find()->where($where)->all();
    }

    /*
     * 贷后获取逾前还款列表
     */

    public function getBeforeRepay($repay_id = []) {
        if (empty($repay_id)) {
            return false;
        }
        return self::find()->where(['repay_id' => $repay_id])->all();
    }

    /**
     * 根据条件获取记录
     * @return type
     * 贷后系统
     */
    public function getRepayByConditions($conditions = []) {
        $repayName = Loan_repay_guide::tableName();
        $userTableName = User_guide::tableName();
        $loanTableName = User_loan_guide::tableName();
        $where = [
            'AND',
            [$repayName . '.status' => '1'],
            ['in', $loanTableName . '.status', [12, 13]],
        ];
        if (isset($conditions['repay_id']) && !empty($conditions['repay_id'])) {
            $where[] = [$repayName . '.repay_id' => $conditions['repay_id']];
        }
        if (isset($conditions['loan_id']) && !empty($conditions['loan_id'])) {
            $where[] = [$repayName . '.loan_id' => $conditions['loan_id']];
        }
        if (isset($conditions['mobile']) && !empty($conditions['mobile'])) {
            $where[] = [$userTableName . '.mobile' => $conditions['mobile']];
        }
        if (isset($conditions['realname']) && !empty($conditions['realname'])) {
            $where[] = [$userTableName . '.realname' => $conditions['realname']];
        }
        if (isset($conditions['identity']) && !empty($conditions['identity'])) {
            $where[] = [$userTableName . '.identity' => $conditions['identity']];
        }
        $res = Loan_repay::find()
                ->joinWith('user', true, 'LEFT JOIN')
                ->joinWith('loan', true, 'LEFT JOIN')
                ->where($where)
                ->all();
        return $res;
    }

}
