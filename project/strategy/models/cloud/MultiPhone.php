<?php

namespace app\models\cloud;
use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "dc_multi_phone".
 * 手机号多投
 * @property string $id
 * @property string $phone
 * @property integer $mph_y
 * @property integer $mph_fm
 * @property integer $mph_other
 * @property integer $mph_br
 * @property string $modify_time
 * @property string $create_time
 */
class MultiPhone extends BaseNewDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dc_multi_phone';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phone', 'modify_time', 'create_time'], 'required'],
            [['mph_y', 'mph_fm', 'mph_other', 'mph_br'], 'integer'],
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
            'mph_y' => '一亿元多投',
            'mph_fm' => '同盾多投',
            'mph_other' => '第三方多投',
            'mph_br' => '百融多投',
            'modify_time' => '修改时间',
            'create_time' => '创建时间',
        ];
    }

    public function getPhMultiInfo($where,$select = '*')
    {
        $select = explode(',',$select);
        $res =  $this->find()->select($select)->where(['phone'=>$where])->Asarray()->one();
        foreach ($select as $k => $v) {
            $val = ArrayHelper::getValue($res,$v,'');
            $res[$v] = $val ? intval($val) : 0;
        }
        return $res;
    }

    public function getPhMultiData($where,$select = '*')
    {
        $select = explode(',',$select);
        $res =  $this->find()->select($select)->where($where)->Asarray()->orderby('id DESC')->one();
        foreach ($select as $k => $v) {
            $val = ArrayHelper::getValue($res,$v,'');
            $res[$v] = $val ? intval($val) : 0;
        }
        return $res;
    }
}
