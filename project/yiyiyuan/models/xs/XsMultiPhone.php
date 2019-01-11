<?php

namespace app\models\xs;

use Yii;

/**
 * This is the model class for table "{{%multi_phone}}".
 *
 * @property string $id
 * @property string $phone
 * @property integer $mph_y
 * @property integer $mph_fm
 * @property integer $mph_other
 * @property integer $mph_br
 * @property string $modify_time
 * @property string $create_time
 */
class XsMultiPhone extends \app\models\xs\XsBaseNewModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%multi_phone}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['phone', 'modify_time', 'create_time'], 'required'],
            [['mph_y', 'mph_fm', 'mph_other', 'mph_br', 'mph_fm_seven_d', 'mph_fm_one_m', 'mph_fm_three_m'], 'integer'],
            [['modify_time', 'create_time'], 'safe'],
            [['phone'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc 
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'phone' => '手机号',
            'mph_y' => '一亿元多投',
            'mph_fm' => '同盾多投',
            'mph_other' => '第三方多投',
            'mph_br' => '百融多投',
            'modify_time' => '修改时间',
            'create_time' => '创建时间',
            'mph_fm_seven_d' => '7天内申请人手机号在多个平台进行借款的数量统计',
            'mph_fm_one_m' => '7天内申请人手机号在多个平台进行借款的数量统计',
            'mph_fm_three_m' => '7天内申请人手机号在多个平台进行借款的数量统计',
        ];
    }

    public function getByPhone($phone) {
        $where = ['phone' => $phone];
        return static::find()->where($where)->limit(1)->one();
    }

    /**
     * 设置多投的方法
     */
    public function setMulti($data) {
        //1. 字段验证
        $time = date("Y-m-d H:i:s");
        $phone = isset($data['phone']) ? $data['phone'] : '';
        if (!$phone) {
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
            $postData['phone'] = $phone;
            $postData['create_time'] = $time;
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
    private function filterValues($data) {
        if (!is_array($data) || empty($data)) {
            return [];
        }
        $fields = [
            'mph_y',
            'mph_fm',
            'mph_other',
            'mph_br',
            'mph_fm_seven_d',
            'mph_fm_one_m',
            'mph_fm_three_m',
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
