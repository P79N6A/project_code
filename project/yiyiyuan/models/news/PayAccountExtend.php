<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_pay_account_extend".
 *
 * @property string $id
 * @property string $pay_account_id
 * @property string $user_id
 * @property integer $step
 * @property string $type
 * @property string $paymax
 * @property string $paydeadline
 * @property string $repaymax
 * @property string $repaydeadline
 * @property string $res_json
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class PayAccountExtend extends \app\models\BaseModel {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_pay_account_extend';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['pay_account_id', 'user_id', 'step', 'last_modify_time', 'create_time'], 'required'],
            [['pay_account_id', 'user_id', 'step', 'version'], 'integer'],
            [['paymax', 'repaymax'], 'number'],
            [['paydeadline', 'repaydeadline', 'last_modify_time', 'create_time'], 'safe'],
            [['res_json'], 'string'],
            [['type'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'pay_account_id' => 'Pay Account ID',
            'user_id' => 'User ID',
            'step' => 'Step',
            'type' => 'Type',
            'paymax' => 'Paymax',
            'paydeadline' => 'Paydeadline',
            'repaymax' => 'Repaymax',
            'repaydeadline' => 'Repaydeadline',
            'res_json' => 'Res Json',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }

    public function optimisticLock() {
        return "version";
    }

    /**
     * 更新记录
     * @param $condition
     * @return bool
     * @author 王新龙
     * @date 2018/9/26 15:04
     */
    public function addRecord($condition) {
        if (empty($condition) || !is_array($condition)) {
            return false;
        }
        $time = date('Y-m-d H:i:s');
        $data = $condition;
        $data['last_modify_time'] = $time;
        $data['create_time'] = $time;
        $data['version'] = 1;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 更新记录
     * @param $condition
     * @return bool
     * @author 王新龙
     * @date 2018/9/26 15:04
     */
    public function updateRecord($condition) {
        if (empty($condition) || !is_array($condition)) {
            return false;
        }
        $time = date('Y-m-d H:i:s');
        $data = $condition;
        $data['last_modify_time'] = $time;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 查询记录，根据user_id & step
     * @param $user_id
     * @param $step 存管步骤
     * @return array|null|\yii\db\ActiveRecord
     * @author 王新龙
     * @date 2018/9/26 15:06
     */
    public function getByUserIdAndStep($user_id, $step) {
        if (empty($user_id) || empty($step)) {
            return null;
        }
        return self::find()->where(['user_id' => $user_id, 'step' => $step])->one();
    }

    /**
     * 获取限制
     * @param int $type 1借款时 2还款时
     * @return bool
     * @author 王新龙
     * @date 2018/9/26 16:51
     */
    public function getLegal($type = 1) {
        switch ($this->step) {
            case 6:
                return $this->getAuthLegal($type);
                break;
        }
        return false;
    }

    /**
     * 四合一限制条件
     * @param $type
     * @return bool
     * @author 王新龙
     * @date 2018/9/26 16:52
     */
    private function getAuthLegal($type) {
        if ($this->paymax < 5000 || $this->repaymax < 5000) {
            return false;
        }
        if ($type == 1) {
            if ($this->paydeadline < date('Y-m-d H:i:s', strtotime('+6 month'))) {
                return false;
            }
            if ($this->repaydeadline < date('Y-m-d H:i:s', strtotime('+6 month'))) {
                return false;
            }
        }
        return true;
    }
}
