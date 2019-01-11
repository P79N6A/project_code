<?php

namespace app\models\antifraud;

use Yii;

/**
 * This is the model class for table "af_ss_report".
 *
 * @property string $id
 * @property string $request_id
 * @property integer $aid
 * @property string $user_id
 * @property integer $score
 * @property string $rain_risk_reason
 * @property string $rain_score
 * @property string $consume_fund_index
 * @property string $indentity_risk_index
 * @property string $social_stability_index
 * @property integer $phone_register_month
 * @property string $create_time
 */
class AfSsReport extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'af_ss_report';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['request_id', 'aid', 'user_id', 'score', 'phone_register_month'], 'integer'],
            [['aid', 'create_time'], 'required'],
            [['rain_risk_reason'], 'string'],
            [['create_time'], 'safe'],
            [['rain_score', 'consume_fund_index', 'indentity_risk_index', 'social_stability_index'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'request_id' => '请求处理id',
            'aid' => '业务id',
            'user_id' => '用户ID',
            'score' => '芝麻信用分',
            'rain_risk_reason' => '手机号风险原因',
            'rain_score' => '手机号风险评分',
            'consume_fund_index' => '人脉圈消费资产指数',
            'indentity_risk_index' => '人脉圈风险指数',
            'social_stability_index' => '人脉圈社交稳定性指数',
            'phone_register_month' => '手机号码注册时长/月',
            'create_time' => '创建时间',
        ];
    }
}
