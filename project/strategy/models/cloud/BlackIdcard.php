<?php

namespace app\models\cloud;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "dc_black_idcard".
 *
 * @property string $id
 * @property string $idcard
 * @property integer $bid_y
 * @property integer $bid_fm_sx
 * @property integer $bid_fm_court_sx
 * @property integer $bid_fm_court_enforce
 * @property integer $bid_fm_lost
 * @property integer $bid_other
 * @property integer $bid_br
 * @property string $modify_time
 * @property string $create_time
 */
class BlackIdcard extends BaseNewDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dc_black_idcard';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['idcard', 'modify_time', 'create_time'], 'required'],
            [['bid_y', 'bid_fm_sx', 'bid_fm_court_sx', 'bid_fm_court_enforce', 'bid_fm_lost', 'bid_other', 'bid_br'], 'integer'],
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

    public function getIdBlackInfo($where,$select = '*')
    {
        $select = explode(',',$select);
        $res =  $this->find()->select($select)->where(['idcard' => $where])->Asarray()->one();
        foreach ($select as $k => $v) {
            $val = ArrayHelper::getValue($res,$v,'');
            $res[$v] = $val ? intval($val) : 0;
        }
        return $res;
    }

    public function getIdBlackData($where,$select = '*')
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
