<?php

namespace app\models\xs;

use Yii;

/**
 * This is the model class for table "{{%black_idcard}}".
 *
 * @property string $id
 * @property string $idcard
 * @property integer $bid_y
 * @property integer $bid_fm_fack
 * @property integer $bid_fm_court_sx
 * @property integer $bid_fm_court_enforce
 * @property integer $bid_fm_lost
 * @property integer $bid_other
 * @property integer $bid_br
 * @property string $modify_time
 * @property string $create_time
 */
class XsBlackIdcard extends \app\models\repo\CloudBase {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'dc_black_idcard';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['idcard', 'modify_time', 'create_time'], 'required'],
            [['bid_y', 'bid_fm_sx', 'bid_fm_court_sx', 'bid_fm_court_enforce', 'bid_fm_lost', 'bid_other', 'bid_br'], 'integer'],
            [['modify_time', 'create_time'], 'safe'],
            [['idcard'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'idcard' => '身份证',
            'bid_y' => '一亿元黑名单',
            'bid_fm_sx' => '同盾虚假',
            'bid_fm_court_sx' => '同盾法院失信',
            'bid_fm_court_enforce' => '同盾法院执行',
            'bid_fm_lost' => '同盾失联',
            'bid_other' => '网络黑名单',
            'bid_br' => '百融黑名单',
            'modify_time' => '修改时间',
            'create_time' => '创建时间',
        ];
    }
    /**
     * 根据身份证号获取
     * @param  str $idcard 
     * @return obj
     */
    public function getByIdcard($idcard) {
        $where = ['idcard' => $idcard];
        return static::find()->where($where)->limit(1)->one();
    }
    /**
     * 设置黑名单的方法
     */
    public function setBlack($data) {
        //1. 字段验证
        $time = date("Y-m-d H:i:s");
        $idcard = isset($data['idcard']) ? $data['idcard'] : '';
        if (!$idcard) {
            return false;
        }

        //2. 仅过滤1值
        $postData = $this->filterValues($data, 1);
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
     * 设置黑名单的方法
     */
    public function unSetBlack($data) {
        $time = date("Y-m-d H:i:s");
        $idcard = isset($data['idcard']) ? $data['idcard'] : '';
        if (!$idcard) {
            return false;
        }
        $model = $this->getByIdcard($idcard);
        if (!$model) {
            return false;
        }

        // 仅过滤0值
        $postData = $this->filterValues($data, 0);
        if (empty($postData)) {
            return false;
        }
        $error = $model->chkAttributes($postData);
        if ($error) {
            return false;
        }

        $result =  $model->save();
        if(!$result){
            return false;
        }

        // 检测是否有还有1值
        $has1 = $this->filterValues($model->attributes, 1);
        if(empty($has1)){
            return $model->delete();
        }
        return true;
    }
    /**
     * 设置0,1值数据
     * @param [] $data
     * @param int $filter_value  0 | 1
     * @return []
     */
    private function filterValues($data, $filter_value){
        if(!is_array($data) || empty($data)){
            return [];
        }
        $fields = [
            'bid_y',
            'bid_fm_sx',
            'bid_fm_court_sx',
            'bid_fm_court_enforce',
            'bid_fm_lost',
            'bid_other',
            'bid_br'
        ];
        $filter_value = intval($filter_value);
        $postData = [];
        foreach ($data as $key => $value) {
            $value = intval($value);
            if (in_array($key, $fields) && $value === $filter_value) {
                $postData[$key] = $value;
            }
        }
        return $postData;
    }
}