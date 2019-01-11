<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "st_amount".
 *
 * @property integer $id
 * @property string $amount_detail
 * @property string $create_time
 * @property string $modify_time
 */
class StAmount extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'st_amount';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['amount_detail'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'amount_detail' => 'Amount Detail',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 设置黑名单的方法
     */
    public function setAmount($data){
        //1. 字段验证
        $time = date("Y-m-d H:i:s");

        if(empty($data) || !is_array($data)){
            return false;
        }
        //2. 更新还是添加
        $model = $this->findOne(['id' => 1]);
        if (!$model) {
            $model = new self;
            $postData['id'] = 1;
            $postData['create_time'] =  $time;
        }
        $postData['amount_detail'] =  json_encode($data);
        $postData['modify_time'] = $time;

        //3. 保存数据
        $error = $model->chkAttributes($postData);
        if ($error) {
            return false;
        }

        return $model->save();
    }

    public function getDayAmount(){
        $def_data = [
            'day7_sum_amount' => 0,
            'day14_sum_amount' => 0,
            'day28_sum_amount' => 0,
            'day56_sum_amount' => 0,
        ];
        $result = $this->findOne(['id' => 1]);
        if (empty($result) || empty($result['amount_detail'])) {
            return $def_data;
        }
        $amount_detail_json = ArrayHelper::getValue($result,'amount_detail','');
        $amount_detail = json_decode($amount_detail_json);
        $ret_arr  = [
            'day7_sum_amount' => ArrayHelper::getValue($amount_detail,'day7_sum_amount',0),
            'day14_sum_amount' => ArrayHelper::getValue($amount_detail,'day14_sum_amount',0),
            'day28_sum_amount' => ArrayHelper::getValue($amount_detail,'day28_sum_amount',0),
            'day56_sum_amount' => ArrayHelper::getValue($amount_detail,'day56_sum_amount',0),
        ];
        return $ret_arr;
    }
}
