<?php

namespace app\models\dev;

use app\models\BaseModel;

/**
 * This is the model class for table "yi_user_quota".
 *
 * @property string $id
 * @property string $user_id
 * @property string $quota
 * @property string $temporary_quota
 * @property integer $grade
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class User_quota extends BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_user_quota';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'quota', 'temporary_quota', 'grade', 'last_modify_time', 'create_time', 'version'], 'required'],
            [['user_id', 'grade', 'version'], 'integer'],
            [['quota', 'temporary_quota'], 'number'],
            [['last_modify_time', 'create_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'quota' => 'Quota',
            'temporary_quota' => 'Temporary Quota',
            'grade' => 'Grade',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }

    /**
     * 添加一条纪录
     */
    public static function addQuota($user_id, $amount) {
        $o = User_quota::find()->where(['user_id' => $user_id])->one();
        // 数据
        $create_time = date('Y-m-d H:i:s');
        $data = [
            'user_id' => $user_id,
            'quota' => $amount,
            'temporary_quota' => 0,
            'grade' => 1,
            'last_modify_time' => $create_time,
        ];

        if (empty($o)) {
            $data['create_time'] = $create_time;
            $data['version'] = 1;
            $o = new self;
        }
        // 保存数据
        $o->attributes = $data;
        return $o->save();
    }

    /**
     * save方法自动添加乐观锁
     * @return [type] [description]
     */
    public function optimisticLock() {
        return "version";
    }

    /**
     * 计算用户当前的可借额度，只作为数据库额度表更新数据，不作为前端展示
     * @param type $user_id
     */
    public static function getUserQuota($user_id) {
        $quota = User_quota::find()->where(['user_id' => $user_id])->one();
        $amount = !empty($quota) ? $quota->quota : 1500;
        if ($amount >= 10000) {
            return 10000;
        }
        $where = [
            'AND',
            ['user_id' => $user_id],
            ['status' => 8],
            ['business_type' => [1,4]],
        ];
        $last_loan = User_loan::find()->select(['amount'])->where($where)->orderBy('`loan_id` DESC')->one();
        if ($last_loan['amount'] < $amount) {
            return $amount;
        }
        $start_time = '2016-07-12 00:00:00';
//        $user = User::findOne($user_id);
        $where[] = ['>=', 'create_time', $start_time];
        $where[] = ['amount' => intval($amount)];
        $where[] = ['!=', "DATEDIFF(`repay_time`, `start_date`)", 0];
        $loan_times = User_loan::find()->where($where)->count();
        switch ($amount) {
            case 1500:
                if ($loan_times >= 2) {
                    $amount += 500;
                }
                break;            
            default :
                if ($loan_times >= 3) {
                    $amount = $amount+500 > 10000 ? 10000:$amount+500;
                }
                break;
        }
        return $amount;
    }

    public function setUserQuota($user_id, $desc) {
        if (empty($user_id)) {
            return FALSE;
        }
        $user = User::findOne($user_id);
        if (empty($user)) {
            return FALSE;
        }
        //添加一个用户的额度数据
        $user_quota = User_quota::find()->where(['user_id' => $user_id])->one();
        $quota_result = FALSE;
        if (empty($user_quota)) {
            $old_amount = 1500;
            $amount = 1500;
            $oUser_quota = new User_quota;
            $quota_result = $oUser_quota->addQuota($user_id, 1500);
        } else {
            $amount = User_quota::getUserQuota($user_id);
            $old_amount = intval($user_quota->quota);
            //判断当前的额度是否提高
            if ($old_amount < $amount) {
                $user_quota->quota = $amount;
                $user_quota->last_modify_time = date('Y-m-d H:i:s');
                $quota_result = $user_quota->save();
            }
        }
        if ($quota_result) {
            //保存一条提额记录
            $oUser_quota_record = new User_quota_record;
            $result = $oUser_quota_record->addRecord($user_id, $old_amount, $amount, $desc);
            return $result;
        } else {
            return FALSE;
        }
    }

}
