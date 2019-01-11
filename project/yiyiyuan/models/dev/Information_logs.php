<?php

namespace app\models\dev;

use Yii;

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
class Information_logs extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_information_logs';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
        ];
    }

    static public function CreateInformationLogs($condition) {
        if (!isset($condition['user_id']) || empty($condition['user_id'])) {
            return false;
        }
        if (!isset($condition['operation_type']) || empty($condition['operation_type'])) {
            return false;
        }
        $information = new Information_logs();
        foreach ($condition as $key => $val) {
            if ($key == 'result') {
                $information->{$key} = serialize($val);
            } else {
                $information->{$key} = $val;
            }
        }
        $information->create_time = date('Y-m-d H:i:s');
        $information->save();
    }

    /**
     * 返回验证限制是否还能认证
     * @param type $user
     * @param type $operation 1 身份验证，2 活体验证
     */
    public function getMark($user, $operation) {
        $redis_key = array(
            '1' => array(
                '1' => 'user_iden_dif_' . $user->user_id,
                '2' => 'user_iden_exist_' . $user->user_id,
                '3' => 'user_iden_verify_' . $user->user_id,
            ),
            '2' => array(
                '1' => 'user_pic_times_' . $user->user_id,
            )
        );
        $num = array(
            '1' => array(0, 1, 1, 2),
            '2' => array(0, 5),
        );
        $mark = 1;
        switch ($operation) {
            case 1:
                $arr = $redis_key[1];
                for ($i = 1; $i <= count($arr); $i++) {
                    $user_iden_keys = $arr[$i];
                    $user_iden = Yii::$app->redis->get($user_iden_keys);
                    if ($user_iden >= $num[1][$i]) {
                        $mark = 0;
                        break;
                    }
                }
                break;
            case 2:
                $arr = $redis_key[2];
                for ($i = 1; $i <= count($arr); $i++) {
                    $user_iden_keys = $arr[$i];
                    $user_iden = Yii::$app->redis->get($user_iden_keys);
                    if ($user_iden >= $num[2][$i]) {
                        $mark = 0;
                        break;
                    }
                }
                break;
        }
        return $mark;
    }

    public function iden_logs($user, $operation, $result, $source = 1, $type = 1) {
        $redis_key = array(
            '1' => array(
                '1' => 'user_iden_dif_' . $user->user_id,
                '2' => 'user_iden_exist_' . $user->user_id,
                '3' => 'user_iden_verify_' . $user->user_id,
            ),
            '2' => array(
                '4' => 'user_pic_times_' . $user->user_id,
            )
        );
        $conditon = array(
            'user_id' => $user->user_id,
            'operation_type' => $operation,
            'type' => $type,
            'result' => $result,
            'source' => $source
        );
        $this->CreateInformationLogs($conditon);
        if ($type != 0) {
            $user_iden_keys = $redis_key[$operation][$type];
            $user_iden = Yii::$app->redis->get($user_iden_keys);
            if (empty($user_iden)) {
                Yii::$app->redis->setex($user_iden_keys, 2592000, 1);
            } else {
                Yii::$app->redis->set($user_iden_keys, $user_iden + 1);
            }
            return Yii::$app->redis->get($user_iden_keys);
        } else {
            foreach ($redis_key[1] as $val) {
                Yii::$app->redis->del($val);
            }
            return false;
        }
    }

}
