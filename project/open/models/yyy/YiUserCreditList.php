<?php

namespace app\models\yyy;

use Yii;

/**
 * This is the model class for table "yi_user_credit_list".
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
 * @property double $crad_rate
 * @property string $invalid_time
 * @property string $uuid
 * @property string $device_tokens
 * @property integer $device_type
 * @property string $device_ip
 * @property string $res_info
 * @property string $last_modify_time
 * @property string $create_time
 */
class YiUserCreditList extends YyyBaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_user_credit_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'create_time'], 'required'],
            [['user_id', 'loan_id', 'req_id', 'score', 'status', 'res_status', 'days', 'device_type'], 'integer'],
            [['amount', 'interest_rate', 'crad_rate'], 'number'],
            [['invalid_time', 'last_modify_time', 'create_time'], 'safe'],
            [['res_info'], 'string'],
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
            'status' => '评审状态',
            'res_status' => '是否可以借款',
            'amount' => '可借最大金额',
            'days' => '可借最大天数',
            'interest_rate' => '日息费',
            'crad_rate' => '购卡费率',
            'invalid_time' => '失效时间',
            'uuid' => 'uuid',
            'device_tokens' => '设备编号',
            'device_type' => '设备类型',
            'device_ip' => '设备ip',
            'res_info' => '决策返回结果',
            'last_modify_time' => '最后修改时间',
            'create_time' => '创建时间',
        ];
    }

    public function getAllbyPage($where = null,$select = '*',$limit = 1000,$offset = 0)
    {
        $condition = $this->find();
        if (!empty($where)) {
            $condition->where($where);
        }
        return $condition
        ->select($select)
        ->offset($offset)
        ->limit($limit)
        ->asArray()
        ->all();
    }

    public function getCount($where = null)
    {
        $condition = $this->find();
        if (!empty($where)) {
            $condition->where($where);
        }
        return $condition->count();
    }
}