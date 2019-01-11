<?php

namespace app\models\antifraud;

/**
 * This is the model class for table "af_result".
 *
 * @property integer $id
 * @property integer $request_id
 * @property integer $aid
 * @property integer $user_id
 * @property integer $setting_id
 * @property integer $score
 * @property integer $result_status
 * @property string $result_subject
 * @property string $create_time
 */
class AfResult extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'af_result';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['request_id', 'aid', 'user_id', 'setting_id', 'score', 'result_status'], 'integer'],
            [['result_subject'], 'string'],
            [['create_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'request_id' => '请求处理id',
            'aid' => '业务ID',
            'user_id' => '用户ID',
            'setting_id' => '使用的规则',
            'score' => '分数',
            'result_status' => '结果状态',
            'result_subject' => '分析结果',
            'create_time' => '记录创建时间',
        ];
    }

    public function getByReqId($request_id) {
        return static::find()->where(['request_id' => $request_id])->limit(1)->one();
    }
}
