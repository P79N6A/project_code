<?php

namespace app\models\yyy;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "yi_anti_fraud".
 *
 * @property string $id
 * @property string $user_id
 * @property string $loan_id
 * @property integer $type
 * @property integer $model_status
 * @property integer $result_status
 * @property string $result_subject
 * @property string $result_time
 * @property string $modify_time
 * @property string $create_time
 * @property string $version
 */
class AntiFraud extends BaseDBModel
{
    # 模型处理状态
    const MODEL_INIT = 1; // 待处理
    const MODEL_LOCK = 2; // 处理中
    const MODEL_ANALYSE = 3; // 模型结束，待释放
    const MODEL_DEAL = 4; // 释放中
    const MODEL_OK = 5; // 完成
    # 结果状态
    const RESULT_INIT = 0; // 初始
    const RESULT_FRAUD = 1; // 欺诈
    const RESULT_SAFE = 2; // 安全

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_anti_fraud';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'loan_id', 'type', 'model_status', 'result_status', 'version'], 'integer'],
            [['result_subject', 'result_time', 'modify_time', 'create_time', 'version'], 'required'],
            [['result_subject'], 'string'],
            [['result_time', 'modify_time', 'create_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键，递增',
            'user_id' => '用户ID',
            'loan_id' => '借款id',
            'type' => '(预留)类型：1首次借贷类型；2复贷类型；3驳回类型',
            'model_status' => '状态:1待模型处理，2模型处理中，3模型结束，待释放，4释放中，5完成',
            'result_status' => '结果状态:0:初始; 1:欺诈; 2:安全',
            'result_subject' => '结果建议: 期诈; 安全',
            'result_time' => '结果时间',
            'modify_time' => '最后修改时间',
            'create_time' => '创建时间',
            'version' => '乐观锁',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    public function getData($model_status, $result_status){
        $afraudDatas = static::find()
            ->where(['model_status' => $model_status])
            ->andWhere(['result_status' => $result_status])
            ->joinWith('user',true,'LEFT JOIN')
            ->asArray()
            ->all();
        return $afraudDatas;
    }

    /**
     * @param $ids
     * @param $model_status
     * @return int
     */
    public function lockAntiFraud($ids, $model_status){
        $now = date('Y-m-d H:i:s', time());
        if (!is_array($ids) || empty($ids)) {
            return 0;
        }
        $res = static::updateAll([
                'model_status' => $model_status,
                'modify_time' => $now
            ], ['id' => $ids]);
        return $res;
    }
}
