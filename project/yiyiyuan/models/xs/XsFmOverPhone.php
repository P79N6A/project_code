<?php

namespace app\models\xs;

use Yii;

/**
 * This is the model class for table "dc_fm_over_phone".
 *
 * @property string $id
 * @property string $phone
 * @property integer $oph_fm_one_m
 * @property integer $oph_fm_two_m
 * @property integer $oph_fm_three_m
 * @property integer $oph_fm_six_m
 * @property integer $oph_fm_one_y
 * @property integer $oph_fm_three_m_plat
 * @property string $modify_time
 * @property string $create_time
 */
class XsFmOverPhone extends \app\models\xs\XsBaseNewModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dc_fm_over_phone';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phone', 'modify_time', 'create_time'], 'required'],
            [['oph_fm_one_m', 'oph_fm_two_m', 'oph_fm_three_m', 'oph_fm_six_m', 'oph_fm_one_y', 'oph_fm_three_m_plat'], 'integer'],
            [['modify_time', 'create_time'], 'safe'],
            [['phone'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'phone' => '手机号',
            'oph_fm_one_m' => '一个月内手机号信贷逾期次数统计',
            'oph_fm_two_m' => '二个月内手机号信贷逾期次数统计',
            'oph_fm_three_m' => '三个月内手机号信贷逾期次数统计',
            'oph_fm_six_m' => '六个月内手机号信贷逾期次数统计',
            'oph_fm_one_y' => '十二个月内手机号信贷逾期次数统计',
            'oph_fm_three_m_plat' => '三个月内手机号信贷逾期平台个数统计',
            'modify_time' => '修改时间',
            'create_time' => '创建时间',
        ];
    }

    public function getByPhone($phone){
        $where = ['phone'=>$phone];
        return static::find()->where($where)->limit(1)->one();
    }
   /**
     * 设置逾期的方法
     */
    public function setOver($data){
        //1. 字段验证
        $time = date("Y-m-d H:i:s");
        $phone = isset($data['phone']) ? $data['phone'] : '';
        if(!$phone){
            return false;
        }

        //2. 仅过滤>0值
        $postData = $this->filterValues($data);
        if (empty($postData)) {
            return false;
        }

        //3. 更新还是添加
        $model = $this->getByPhone($phone);
        if (!$model) {
            $model = new self;
            $postData['phone'] =  $phone;
            $postData['create_time'] =  $time;
        }
        $postData['modify_time'] = $time;

        //4. 保存数据
        $error = $model->chkAttributes($postData);
        if ($error) {
            return false;
        }

        return $model->save();
    }
    /**
     * 设置0,1值数据
     * @param [] $data
     * @return []
     */
    private function filterValues($data){
        if(!is_array($data) || empty($data)){
            return [];
        }
        $fields = [
            'oph_fm_one_m',
            'oph_fm_two_m',
            'oph_fm_three_m',
            'oph_fm_six_m',
            'oph_fm_one_y',
            'oph_fm_three_m_plat',
        ];
        $postData = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $fields) && $value > 0) {
                $postData[$key] = $value;
            }
        }
        return $postData;
    }
}
