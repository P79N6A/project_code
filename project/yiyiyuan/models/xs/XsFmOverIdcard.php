<?php

namespace app\models\xs;

use Yii;

/**
 * This is the model class for table "dc_fm_over_idcard".
 *
 * @property string $id
 * @property string $idcard
 * @property integer $oid_fm_one_m
 * @property integer $oid_fm_two_m
 * @property integer $oid_fm_three_m
 * @property integer $oid_fm_six_m
 * @property integer $oid_fm_one_y
 * @property integer $oid_fm_three_m_plat
 * @property string $modify_time
 * @property string $create_time
 */
class XsFmOverIdcard extends \app\models\xs\XsBaseNewModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dc_fm_over_idcard';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['idcard', 'modify_time', 'create_time'], 'required'],
            [['oid_fm_one_m', 'oid_fm_two_m', 'oid_fm_three_m', 'oid_fm_six_m', 'oid_fm_one_y', 'oid_fm_three_m_plat'], 'integer'],
            [['modify_time', 'create_time'], 'safe'],
            [['idcard'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idcard' => '身份证',
            'oid_fm_one_m' => '一个月内身份证号信贷逾期次数统计',
            'oid_fm_two_m' => '二个月内身份证号信贷逾期次数统计',
            'oid_fm_three_m' => '三个月内身份证号信贷逾期次数统计',
            'oid_fm_six_m' => '六个月内身份证号信贷逾期次数统计',
            'oid_fm_one_y' => '十二个月内身份证号信贷逾期次数统计',
            'oid_fm_three_m_plat' => '三月个内身份证号信贷逾期平台个数统计',
            'modify_time' => '修改时间',
            'create_time' => '创建时间',
        ];
    }

    public function getByIdcard($idcard) {
        $where = ['idcard' => $idcard];
        return static::find()->where($where)->limit(1)->one();
    }
    /**
     * 设置逾期的方法
     */
    public function setOver($data) {
        //1. 字段验证
        $time = date("Y-m-d H:i:s");
        $idcard = isset($data['idcard']) ? $data['idcard'] : '';
        if (!$idcard) {
            return false;
        }

        //2. 仅过滤>0值
        $postData = $this->filterValues($data);
        if (empty($postData)) {
            return false;
        }
        //3. 更新还是添加
        $model = $this->getByIdcard($idcard);
        if (!$model) {
            $model = new self;
            $postData['idcard'] =  $idcard;
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
    private function filterValues($data) {
        if (!is_array($data) || empty($data)) {
            return [];
        }
        $fields = [
            'oid_fm_one_m',
            'oid_fm_two_m',
            'oid_fm_three_m',
            'oid_fm_six_m',
            'oid_fm_one_y',
            'oid_fm_three_m_plat',
        ];
        $postData = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $fields) && $value > 0 ) {
                $postData[$key] = $value;
            }
        }
        return $postData;
    }
}
