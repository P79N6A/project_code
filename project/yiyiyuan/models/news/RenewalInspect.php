<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_renewal_inspect".
 *
 * @property integer $id
 * @property integer $loan_id
 * @property integer $user_id
 * @property integer $status
 * @property integer $is_show_status
 * @property string  $last_modify_time
 * @property string  $create_time
 * @property integer $version
 */
class RenewalInspect extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_renewal_inspect';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['loan_id', 'user_id', 'status', 'is_show_status', 'last_modify_time', 'create_time'], 'required'],
            [['loan_id', 'user_id', 'status', 'is_show_status', 'version'], 'integer'],
            [['last_modify_time', 'create_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'loan_id' => 'Loan ID',
            'user_id' => 'User ID',
            'status' => 'Status',
            'is_show_status' => 'Is Show Status',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }

    /**
     * 乐观所版本号
     * * */
    public function optimisticLock()
    {
        return "version";
    }

    public function addRecord($condition)
    {
        if (empty($condition) || !is_array($condition)) {
            return FALSE;
        }
        $data = $condition;
        $time = date('Y-m-d H:i:s');
        $data['last_modify_time'] = $time;
        $data['create_time'] = $time;
        $data['version'] = 0;
        $error = $this->chkAttributes($data);
        if ($error) {
            return FALSE;
        }

        $result = $this->save();
        if (!$result) {
            return FALSE;
        }
        return $result;
    }

    public function updateRecord($condition)
    {
        if (empty($condition) || !is_array($condition)) {
            return FALSE;
        }
        $data = $condition;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return FALSE;
        }
        $result = $this->save();
        if (!$result) {
            return FALSE;
        }
        return $result;
    }

    public function getByLoanId($loan_id)
    {
        if (empty($loan_id)) {
            return NULL;
        }
        return self::find()->where(['loan_id' => $loan_id])->one();
    }

    public function getByUserIdAndStatus($user_id, $status = [0, 3])
    {
        if (empty($user_id)) {
            return NULL;
        }
        return self::find()->where(['user_id' => $user_id, 'status' => $status])->one();
    }

    public function getByStatus($user_id, $status, $is_show_status)
    {
        if (empty($user_id)) {
            return NULL;
        }
        return self::find()->where(['user_id' => $user_id, 'status' => $status, 'is_show_status' => $is_show_status])->one();
    }

    public function updateIsShow()
    {
        try {
            $this->is_show_status = 1;
            $this->last_modify_time = date('Y-m-d H:i:s');
            return $this->save();
        } catch (\Exception $ex) {
            return FALSE;
        }
    }

    public function batchLock($ids)
    {
        if (empty($ids)) {
            return FALSE;
        }
        return self::updateAll(['status' => '3'], ['id' => $ids, 'status' => 0]);
    }

    public function updateLock()
    {
        try {
            $this->status = 3;
            $this->last_modify_time = date('Y-m-d H:i:s');
            return $this->save();
        } catch (\Exception $ex) {
            return FALSE;
        }
    }

    public function updateFail()
    {
        try {
            $this->status = 2;
            $this->last_modify_time = date('Y-m-d H:i:s');
            return $this->save();
        } catch (\Exception $ex) {
            return FALSE;
        }
    }

    public function updateSuccess()
    {
        try {
            $this->status = 1;
            $this->last_modify_time = date('Y-m-d H:i:s');
            return $this->save();
        } catch (\Exception $ex) {
            return FALSE;
        }
    }

    public function createInspectloan($o_user_loan, $o_renew_amount, $o_renewal_inspect)
    {
        $parent_loan_id = $o_user_loan->parent_loan_id;
        $days = $o_user_loan->days + 1;
        if (in_array($o_user_loan->days, [21, 28])) {
            $days = 56 + 1;
        }
        $parent_loan = (new User_loan())->getById($parent_loan_id);
        $number = $o_user_loan->number + 1;
        $end_date = date('Y-m-d 00:00:00', strtotime("+$days days"));
        $new_loan_id = (new User_loan())->saveRenewLoan($parent_loan, $end_date, $number, $parent_loan_id, $days);
        if (!empty($new_loan_id)) {
            $user_loan_new = User_loan::find()->where(['loan_id' => $new_loan_id])->one();
            $user_loan_new->changeStatus(9);
            $user_loan_new->saveEndtime($user_loan_new->days);

            $condition = [
                'settle_type' => 2,
                'repay_time' => date('Y-m-d H:i:s'),
            ];
            $up = $o_user_loan->update_userLoan($condition);
            if ($up) {
                $over_due = OverdueLoan::find()->where(['loan_id' => $this->loan_id])->one();
                if (!empty($over_due)) {
                    $over_due->clearOverdueLoan();
                }
                $res = $o_user_loan->changeStatus(8);
                $result = (new Renew_amount())->addExtension($parent_loan, $o_renew_amount, $end_date, $new_loan_id);
                if (!$result) {
                    return FALSE;
                }
                $inspect_result = $o_renewal_inspect->updateSuccess();
                if (!$inspect_result) {
                    return FALSE;
                }
                return $res;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }
}
