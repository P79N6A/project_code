<?php

namespace app\models\xs;

use Yii;

/**
 * This is the model class for table "{{%multi_idcard}}".
 *
 * @property string $id
 * @property string $idcard
 * @property integer $mid_y
 * @property integer $mid_fm
 * @property integer $mid_other
 * @property integer $mid_br
 * @property string $modify_time
 * @property string $create_time
 */
class XsMultiIdcard extends \app\models\xs\XsBaseNewModel {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%multi_idcard}}';
    }

        /** 
     * @inheritdoc 
     */ 
    public function rules() 
    { 
        return [
            [['idcard', 'modify_time', 'create_time'], 'required'],
            [['mid_y', 'mid_fm', 'mid_other', 'mid_br', 'mid_fm_seven_d', 'mid_fm_one_m', 'mid_fm_three_m'], 'integer'],
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
            'mid_y' => '一亿元多投',
            'mid_fm' => '同盾多投',
            'mid_other' => '第三方多投',
            'mid_br' => '百融多投',
            'modify_time' => '修改时间',
            'create_time' => '创建时间',
            'mid_fm_seven_d' => '7天内申请人身份证号在多个平台进行借款的数量统计',
            'mid_fm_one_m' => '1个月内申请人身份证号在多个平台进行借款的数量统计',
            'mid_fm_three_m' => '3个月内申请人身份证号在多个平台进行借款的数量统计',
        ]; 
    }
    public function getByIdcard($idcard) {
        $where = ['idcard' => $idcard];
        return static::find()->where($where)->limit(1)->one();
    }
    /**
     * 设置多投的方法
     */
    public function setMulti($data) {
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
            'mid_y',
            'mid_fm',
            'mid_other',
            'mid_br',
            'mid_fm_seven_d',
            'mid_fm_one_m',
            'mid_fm_three_m',
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
