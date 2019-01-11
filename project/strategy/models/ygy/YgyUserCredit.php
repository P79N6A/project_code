<?php

namespace app\models\ygy;

use Yii;

/**
 * This is the model class for table "ygy_user_credit".
 *
 * @property string $id
 * @property string $user_id
 * @property string $loan_id
 * @property string $req_id
 * @property integer $type
 * @property integer $source
 * @property integer $score
 * @property integer $status
 * @property integer $res_status
 * @property string $amount
 * @property string $shop_amount
 * @property integer $days
 * @property integer $shop_days
 * @property string $interest_rate
 * @property string $shop_interest_rate
 * @property double $crad_rate
 * @property double $shop_crad_rate
 * @property string $invalid_time
 * @property integer $pay_status
 * @property string $uuid
 * @property string $device_tokens
 * @property integer $device_type
 * @property string $device_ip
 * @property string $res_info
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class YgyUserCredit extends BaseDBModel
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
            [['user_id', 'loan_id', 'req_id', 'type', 'source', 'score', 'status', 'res_status', 'days', 'shop_days', 'pay_status', 'device_type', 'version'], 'integer'],
            [['amount', 'shop_amount', 'interest_rate', 'shop_interest_rate', 'crad_rate', 'shop_crad_rate'], 'number'],
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
            'type' => '类型 1先借款 2先支付',
            'source' => '1:亿元发起的评测2：智融发起的评测',
            'score' => '信用分数',
            'status' => '评审状态 1 评测中 2 成功 3人工 ',
            'res_status' => '是否可以借款 1可借 2不可借',
            'amount' => '可借最大金额',
            'shop_amount' => '商城可借最大金额',
            'days' => '可借最大天数',
            'shop_days' => '商城可借最大天数',
            'interest_rate' => '日息费',
            'shop_interest_rate' => '日息费',
            'crad_rate' => '购卡费率',
            'shop_crad_rate' => '商城转售费率',
            'invalid_time' => '失效时间',
            'pay_status' => '支付状态 0初始 1成功',
            'uuid' => 'uuid',
            'device_tokens' => '设备编号',
            'device_type' => '设备类型1：微信2：app3：ios4：andraoid5：H5',
            'device_ip' => '设备ip',
            'res_info' => '决策返回结果',
            'last_modify_time' => '最后修改时间',
            'create_time' => '创建时间',
            'version' => 'Version',
        ];
    }

    public function getYgyUserCredit($where,$select = '*')
    {
        return $this->find()->where($where)->select($select)->orderBy('id DESC')->asArray()->one();
    }
}
