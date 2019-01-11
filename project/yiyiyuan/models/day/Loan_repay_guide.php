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
class Loan_repay_guide extends \app\models\BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'qj_loan_repay';
    }

    public function getLoan() {
        return $this->hasOne(User_loan_guide::className(), ['loan_id' => 'loan_id']);
    }

    public function getUser() {
        return $this->hasOne(User_guide::className(), ['user_id' => 'user_id']);
    }

    public function getBank() {
        return $this->hasOne(User_bank::className(), ['id' => 'bank_id']);
    }

    public function getUserloan() {
        return $this->hasOne(User_loan_guide::className(), ['loan_id' => 'loan_id']);
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'loan_id', 'money', 'last_modify_time', 'createtime'], 'required'],
            [['user_id', 'loan_id', 'bank_id', 'platform', 'source', 'status', 'version'], 'integer'],
            [['money', 'actual_money'], 'number'],
            [['last_modify_time', 'createtime'], 'safe'],
            [['repay_id', 'pay_key', 'repay_time'], 'string', 'max' => 32],
            [['pic_repay1', 'pic_repay2', 'pic_repay3', 'repay_mark'], 'string', 'max' => 128],
            [['code'], 'string', 'max' => 6],
            [['paybill'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'repay_id' => 'Repay ID',
            'user_id' => 'User ID',
            'loan_id' => 'Loan ID',
            'bank_id' => 'Bank ID',
            'platform' => 'Platform',
            'source' => 'Source',
            'pic_repay1' => 'Pic Repay1',
            'pic_repay2' => 'Pic Repay2',
            'pic_repay3' => 'Pic Repay3',
            'status' => 'Status',
            'money' => 'Money',
            'actual_money' => 'Actual Money',
            'pay_key' => 'Pay Key',
            'code' => 'Code',
            'paybill' => 'Paybill',
            'last_modify_time' => 'Last Modify Time',
            'createtime' => 'Createtime',
            'repay_time' => 'Repay Time',
            'repay_mark' => 'Repay Mark',
            'version' => 'Version',
        ];
    }

    /**
     * 获取进行中的还款
     * @param $loan_id
     * @return array|null|\yii\db\ActiveRecord
     * @author 王新龙
     * @date 2018/8/3 16:41
     */
    public function getCarriedRepay($loan_id) {
        if (empty($loan_id)) {
            return null;
        }
        return self::find()->where(['loan_id' => $loan_id, 'status' => '-1'])->orderBy('id desc')->one();
    }

    /**
     * 根据主键查询
     */
    public function getById($id) {
        return static::findOne($id);
    }

    /**
     * 新增记录
     * @param $condition
     * @return bool|string
     * @author 王新龙
     * @date 2018/8/3 16:48
     */
    public function addRecord($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data = $condition;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $data['createtime'] = date('Y-m-d H:i:s');
        $data['version'] = 1;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        try {
            $result = $this->save();
            if ($result) {
                $orderid = 'Y' . date('mdHis') . $this->id;
                $this['repay_id'] = (string) $orderid;
                $result = $this->save();
                if ($result) {
                    return $this->id;
                } else {
                    return false;
                }
            }
        } catch (\Exception $ex) {
            return FALSE;
        }
    }

    /**
     * 获取记录，根据repay_id
     * @param $orderId
     * @return array|bool|null|\yii\db\ActiveRecord
     * @author 王新龙
     * @date 2018/8/3 17:44
     */
    public function getRepayByRepayId($repay_id) {
        if (empty($repay_id)) {
            return false;
        }
        return self::find()->where(['repay_id' => $repay_id])->one();
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

    /**
     * 添加还款记录
     * @author zhangyafeng@xianhuahua.com
     * @date 2018/10/08
     * @param $condition
     * @return bool
     */
    public function save_repay($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data = $condition;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $data['createtime'] = date('Y-m-d H:i:s');
        $data['version'] = 1;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        try {
            $result = $this->save();
            if ($result) {
                $orderid = 'S' . date('mdHis') . $this->id;
                $this['repay_id'] = (string) $orderid;
                $result = $this->save();
                if ($result) {
                    return $this->id;
                } else {
                    return false;
                }
            }
        } catch (\Exception $ex) {
            return FALSE;
        }
    }

    /**
     * 还款订单失败
     * @return boolean
     */
    public function saveSucc($money = '', $repay_time = '') {
        try {
            $this->status = 1;
            if (!empty($money)) {
                $this->money = $money;
            }
            $this->actual_money = $money;
            $this->last_modify_time = date('Y-m-d H:i:s');
            $this->repay_time = empty($repay_time) ? date('Y-m-d H:i:s') : $repay_time;
            $result = $this->save();
        } catch (\Exception $ex) {
            return FALSE;
        }
        return $result;
    }

    /**
     * 还款订单失败
     * @return boolean
     */
    public function saveFail() {
        try {
            $this->status = 4;
            $this->last_modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        } catch (\Exception $ex) {
            return FALSE;
        }
        return $result;
    }

}
