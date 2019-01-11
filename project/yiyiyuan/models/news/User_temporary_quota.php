<?php

namespace app\models\news;

use app\models\BaseModel;
use Yii;

/**
 * This is the model class for table "yi_user_temporary_quota".
 *
 * @property string $id
 * @property string $user_id
 * @property string $temporary_quota
 * @property string $begin_time
 * @property string $end_time
 * @property integer $status
 * @property string $desc
 * @property integer $type
 * @property string $last_modify_time
 * @property string $create_time
 */
class User_temporary_quota extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_user_temporary_quota';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'temporary_quota', 'begin_time', 'end_time', 'desc', 'create_time'], 'required'],
            [['user_id', 'status', 'type'], 'integer'],
            [['temporary_quota'], 'number'],
            [['begin_time', 'end_time', 'last_modify_time', 'create_time'], 'safe'],
            [['desc'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'temporary_quota' => 'Temporary Quota',
            'begin_time' => 'Begin Time',
            'end_time' => 'End Time',
            'status' => 'Status',
            'desc' => 'Desc',
            'type' => 'Type',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * 对用户进行临时提额
     * @param type $user_id
     * @param type $amount  临时额度
     * @param type $days    临时额度有效天数
     * @param type $desc    临时额度提升原因描述 1:注册  2:还款
     * @return bool
     */
    public function setTemporary($user_id, $amount, $days, $desc, $type = 1) {
        $beg_time = '2017-03-24 00:00:00';
        $nowdate = date('Y-m-d H:i:s');
        if ($type == 1) {
            //发放截至时间
            $endtime = date('Y-m-d H:i:s', strtotime('+7 days', strtotime($beg_time)));
        } else if ($type == 2) {
            $endtime = date('Y-m-d H:i:s', strtotime('+4 days', strtotime($beg_time)));
            return FALSE;
        }
        if ($nowdate < $beg_time || $nowdate > $endtime) {
            return FALSE;
        }
        if (empty($user_id)) {
            return FALSE;
        }
        if ($type == 1 && !$this->is_upTemporary()) {
            return FALSE;
        }
        $user_quota = User_quota::find()->where(['user_id' => $user_id])->one();
        if (empty($user_quota)) {
            //新添加用户额度，默认额度为1000
            $userQuotaModel = new User_quota();
            $quotaResult = $userQuotaModel->setUserQuota($user_id, '系统提额');
            if ($quotaResult) {
                $user_quota = User_quota::find()->where(['user_id' => $user_id])->one();
            } else {
                return FALSE;
            }
        }
        if (3000 <= $user_quota->quota + $user_quota->temporary_quota) {
            return FALSE;
        }
        if ($user_quota->temporary_quota > 0) {
            return FALSE;
        }
        $begin_time = date('Y-m-d 00:00:00');
        $end_time = date('Y-m-d 00:00:00', strtotime("+$days days"));
        $result = $this->inputTemporary($user_quota, $amount, $begin_time, $end_time, $type, $desc);
        return $result;
    }

    /**
     * 此方法只针对2017-03-08需求，首贷用户临时额度是否进行提升
     * 目前规则：每天10点之后最多发放3600位注册用户临时额度
     * 自定规则：10-12|12-14|14-16|16-18 每个区间发900人
     * 若当前期间发放达到900，停止发放，若不够900，剩余人数加入到下个区间时间段，一次类推，直到发放完3600个
     */
    public function is_upTemporary() {
        $hours = [10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23];
        $now_hour = date('H');
        if (in_array($now_hour, $hours)) {
            $where = [
                'AND',
                ['>', 'create_time', date('Y-m-d 00:00:00')],
                ['type' => 1, 'status' => 1],
            ];
            $count = User_temporary_quota::find()->where($where)->count();
            if ($count >= 3600) {
                return FALSE;
            } else {
                $num = floor(($now_hour - 10) / 2);
                //$reg_num分别对应$num为下标
                $reg_num = [900, 1800, 2700, 3600, 3600, 3600, 3600]; //900, 1800, 2700, 3600, 3600, 3600, 3600
                if ($count < $reg_num[$num]) {
                    return TRUE;
                } else {
                    return FALSE;
                }
            }
        } else {
            return FALSE;
        }
    }

    public function inputTemporary($userQuota, $amount, $begin_time, $end_time, $type, $desc) {
        $userQuota->temporary_quota = $amount;
        $userQuota->last_modify_time = date('Y-m-d H:i:s');
        $result = $userQuota->save();
        if ($result) {
            $o = new self;
            $re_quota = $o->addTemporaryQuota($userQuota->user_id, $amount, $begin_time, $end_time, $type, $desc);
            return $re_quota;
        } else {
            return FALSE;
        }
    }

    /**
     * 添加一条临时额度纪录
     * @user_id int 用户id
     * @param int $amount  临时额度
     * @param date $begin_time    开始时间
     * @param date $end_time    结束时间
     * @param int $type    提额类型 1:首贷 2：复贷
     * @param int $desc    临时额度提升原因描述 1:注册  2:还款
     * @return bool
     */
    public static function addTemporaryQuota($user_id, $amount, $begin_time, $end_time, $type, $desc) {
        $o = new self;
        // 数据
        $create_time = date('Y-m-d H:i:s');
        if ($create_time > $begin_time && $create_time < $end_time) {
            $status = 1;
        } else {
            $status = 2;
        }
        $data = [
            'user_id' => $user_id,
            'temporary_quota' => $amount,
            'begin_time' => $begin_time,
            'end_time' => $end_time,
            'status' => $status,
            'desc' => $desc,
            'type' => $type,
            'last_modify_time' => $create_time,
            'create_time' => $create_time,
        ];
        // 保存数据
        $o->attributes = $data;
        $result = $o->save();
        return $result;
    }

}