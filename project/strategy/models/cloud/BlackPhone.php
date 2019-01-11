<?php

namespace app\models\cloud;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "dc_black_phone".
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
class BlackPhone extends BaseNewDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dc_black_phone';
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

    public function getPhBlackInfo($where,$select = '*')
    {
        $select = explode(',',$select);
        $res =  $this->find()->select($select)->where(['phone'=>$where])->Asarray()->one();
        foreach ($select as $k => $v) {
            $val = ArrayHelper::getValue($res,$v,'');
            $res[$v] = $val ? intval($val) : 0;
        }
        return $res;
    }

    public function getPhBlackData($where,$select = '*')
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
