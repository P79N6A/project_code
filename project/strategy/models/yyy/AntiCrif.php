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
class AntiCrif extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_anti_crif_v1';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'loan_id', 'type', 'result_status', 'model_status'], 'integer'],
            [['result_subject', 'result_time', 'modify_time', 'create_time'], 'required'],
            [['result_score'], 'number'],
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
            'result_score' => '模型分数',
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


    

}
