<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_difference_amount".
 *
 * @property string $id
 * @property string $loan_amount
 * @property string $invest_amount
 * @property string $loan_expire_amount
 * @property string $create_time
 */
class Difference_amount extends \app\models\BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_difference_amount';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['loan_amount', 'invest_amount', 'loan_expire_amount'], 'number'],
            [['create_time'], 'required'],
            [['create_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'loan_amount' => 'Loan Amount',
            'invest_amount' => 'Invest Amount',
            'loan_expire_amount' => 'Loan Expire Amount',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * 添加用户
     */
    public function addRecord($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data = $condition;
        $data['create_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return $error;
        }
        return $this->save();
    }

    /**
     * 当天是否已经推送过第三方债权
     */
    public function verifyDayRecord() {
        $today_time = date('Y-m-d 00:00:00');
        $already = self::find()->where(['>=', 'create_time', $today_time])->count();
        return $already > 0 ? TRUE : FALSE;
    }

    public function getRemainmoney() {
        $today = date('Y-m-d 00:00:00');
        $diff_money = self::find()->where(['>=', 'create_time', $today])->one();
        if (empty($diff_money)) {
            return 0;
        }
        $remainMoney = $diff_money['loan_amount'] - $diff_money['loan_expire_amount'];
        return $remainMoney;
    }

    public function getRecord() {
        $today = date('Y-m-d 00:00:00');
        $diff_money = self::find()->where(['>=', 'create_time', $today])->one();
        return $diff_money;
    }

    public function updateRecord($condition) {
        if (empty($condition)) {
            return FALSE;
        }
        $error = $this->chkAttributes($condition);
        if ($error) {
            return FALSE;
        }
        $result = $this->save();
        return $result;
    }

}
