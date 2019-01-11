<?php

namespace app\models\yyy;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "yi_anti_automation".
 * 一亿元自动化模型表
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
 */
class AntiAutomation extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_anti_automation';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'loan_id', 'type', 'model_status', 'result_status'], 'integer'],
            [['result_subject', 'result_time', 'modify_time', 'create_time'], 'required'],
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
            'type' => '(预留)类型：1首次借贷类型；2复贷类型；',
            'model_status' => '状态:1待模型处理，2模型处理中，3模型结束，待释放，4释放中，5完成',
            'result_status' => '结果状态:0:初始; 1:通过; 2:不处理；3:驳回',
            'result_subject' => '结果建议:各个参数模型结果',
            'result_time' => '结果时间',
            'modify_time' => '最后修改时间',
            'create_time' => '创建时间',
        ];
    }

    public function getUserType($where,$select = '*')
    {
        $select = explode(',',$select);
        $res =  $this->find()->select($select)->where($where)->Asarray()->one();
        foreach ($select as $k => $v) {
            $val = ArrayHelper::getValue($res,$v,'');
            $res[$v] = $val ? intval($val) : 1;
        }
        return $res;
    }

    public function getResultSubject($where,$select = '*')
    {
        $res =  $this->find()->select($select)->where($where)->Asarray()->one();
        if (empty($res) || $res['result_subject'] == 'prome result') {
            $res = [
                'is_desc_normal' => 0,
                'is_amount_no_up' => 0,
                'is_bank_no_edit' => 0,
                'is_fraud_pass' => 0,
                'is_info_no_edit' => 0,
                'is_phonebook_no_edit' => 0,
                'is_report_no_edit' => 0,
                'is_white_true' => 0,
            ];
        } else {
            $res = json_decode($res['result_subject'],true);
        }
        return $res;
    }
}
