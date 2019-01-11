<?php

namespace app\models\yyy;

use Yii;

/**
 * This is the model class for table "yi_user_credit".
 *
 * @property string $id
 * @property string $user_id
 * @property string $loan_id
 * @property string $req_id
 * @property integer $score
 * @property integer $status
 * @property integer $res_status
 * @property string $amount
 * @property integer $days
 * @property string $interest_rate
 * @property string $invalid_time
 * @property string $uuid
 * @property string $device_tokens
 * @property integer $device_type
 * @property string $device_ip
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class YiUserCredit extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_user_credit';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'create_time'], 'required'],
            [['user_id', 'loan_id', 'req_id', 'score', 'status', 'res_status', 'days', 'device_type', 'version'], 'integer'],
            [['amount', 'interest_rate'], 'number'],
            [['invalid_time', 'last_modify_time', 'create_time'], 'safe'],
            [['uuid', 'device_tokens'], 'string', 'max' => 128],
            [['device_ip'], 'string', 'max' => 16]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '用户id',
            'loan_id' => '借款id',
            'req_id' => '评审id',
            'score' => '信用分数',
            'status' => '评审状态 1 评测中 2 成功 3人工 ',
            'res_status' => '是否可以借款 1可借 2不可借',
            'amount' => '可借最大金额',
            'days' => '可借最大天数',
            'interest_rate' => '日息费',
            'invalid_time' => '失效时间',
            'uuid' => 'uuid',
            'device_tokens' => '设备编号',
            'black_box' => '设备指纹',
            'device_type' => '设备类型1：微信2：app3：ios4：andraoid5：H5',
            'device_ip' => '设备ip',
            'last_modify_time' => '最后修改时间',
            'create_time' => '创建时间',
            'version' => 'Version',
        ];
    }

    public function getUserCredit($where,$select = '*')
    {
        return $this->find()->where($where)->select($select)->orderBy('id DESC')->asArray()->one();
    }
}
