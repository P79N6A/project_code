<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/19
 * Time: 14:12
 */
namespace app\models\yyy;

use Yii;

/**
 * This is the model class for table "yi_plan".
 *
 * @property integer $id
 * @property string $name
 * @property integer $fund
 * @property integer $status
 * @property integer $sort_num
 * @property integer $is_accuracy
 * @property string $start_time
 * @property string $end_time
 * @property string $max_estimate
 * @property string $max_real
 * @property string $max_do_estimate
 * @property string $max_do_real
 * @property string $max_success_money
 * @property string $threshold
 * @property integer $admin_id
 * @property string $plan_time
 * @property string $create_time
 */
class YiPlan extends \app\models\yyy\YyyBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_plan';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'fund', 'start_time', 'end_time', 'max_estimate', 'max_real', 'max_do_estimate', 'max_do_real', 'max_success_money', 'threshold', 'admin_id', 'plan_time', 'create_time'], 'required'],
            [['fund', 'status', 'sort_num', 'is_accuracy', 'admin_id'], 'integer'],
            [['start_time', 'end_time', 'plan_time', 'create_time'], 'safe'],
            [['max_estimate', 'max_real', 'max_do_estimate', 'max_do_real', 'max_success_money', 'threshold'], 'number'],
            [['name'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'fund' => 'Fund',
            'status' => 'Status',
            'sort_num' => 'Sort Num',
            'is_accuracy' => 'Is Accuracy',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'max_estimate' => 'Max Estimate',
            'max_real' => 'Max Real',
            'max_do_estimate' => 'Max Do Estimate',
            'max_do_real' => 'Max Do Real',
            'max_success_money' => 'Max Success Money',
            'threshold' => 'Threshold',
            'admin_id' => 'Admin ID',
            'plan_time' => 'Plan Time',
            'create_time' => 'Create Time',
        ];
    }

    public static function capitalSide()
    {
        return static::find()->groupBy("fund")->asArray()->all();
    }
}