<?php

namespace app\models\xs;

use Yii;

/**
 * This is the model class for table "{{%black_phone}}".
 *
 * @property string $id
 * @property string $phone
 * @property integer $bph_y
 * @property integer $bph_fm_fack
 * @property integer $bph_fm_small
 * @property integer $bph_fm_sx
 * @property integer $bph_other
 * @property integer $bph_br
 * @property string $modify_time
 * @property string $create_time
 */
class XsBlackPhone extends \app\models\xs\XsBaseNewModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%black_phone}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phone', 'modify_time', 'create_time'], 'required'],
            [['bph_y', 'bph_fm_fack', 'bph_fm_small', 'bph_fm_sx', 'bph_other', 'bph_br'], 'integer'],
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
            'bph_y' => '一亿元黑名单',
            'bph_fm_fack' => '同盾虚假',
            'bph_fm_small' => '同盾小号',
            'bph_fm_sx' => '同盾失信',
            'bph_other' => '网络黑名单',
            'bph_br' => '百融黑名单',
            'modify_time' => '修改时间',
            'create_time' => '创建时间',
        ];
    }
        /**
     * 根据手机号获取
     * @param  str $phone 
     * @return obj
     */
    public function getByPhone($phone){
        $where = ['phone'=>$phone];
        return static::find()->where($where)->limit(1)->one();
    }
   /**
     * 设置黑名单的方法
     */
    public function setBlack($data){
        //1. 字段验证
        $time = date("Y-m-d H:i:s");
        $phone = isset($data['phone']) ? $data['phone'] : '';
        if(!$phone){
            return false;
        }

        //2. 仅过滤1值
        $postData = $this->filterValues($data, 1);
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
     * 设置黑名单的方法
     */
    public function unSetBlack($data) {
        $time = date("Y-m-d H:i:s");
        $phone = isset($data['phone']) ? $data['phone'] : '';
        if(!$phone){
            return false;
        }
        $model = $this->getByPhone($phone);
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
            'bph_y' ,
            'bph_fm_fack' ,
            'bph_fm_small' ,
            'bph_fm_sx' ,
            'bph_other' ,
            'bph_br' ,
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
