<?php

namespace app\models\cloud;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "dc_multi_idcard".
 * 身份证号多投
 * @property string $id
 * @property string $idcard
 * @property integer $mid_y
 * @property integer $mid_fm
 * @property integer $mid_other
 * @property integer $mid_br
 * @property string $modify_time
 * @property string $create_time
 */
class MultiIdcard extends BaseNewDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dc_multi_idcard';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['idcard', 'modify_time', 'create_time'], 'required'],
            [['mid_y', 'mid_fm', 'mid_other', 'mid_br'], 'integer'],
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
        ];
    }

    public function getIdMultiInfo($where,$select = '*')
    {
        $select = explode(',',$select);
        $res =  $this->find()->select($select)->where(['idcard' => $where])->Asarray()->one();
        foreach ($select as $k => $v) {
            $val = ArrayHelper::getValue($res,$v,'');
            $res[$v] = $val ? intval($val) : 0;
        }
        return $res;
    }
    public function getIdMultiData($where,$select = '*')
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
