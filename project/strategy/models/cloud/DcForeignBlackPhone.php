<?php

namespace app\models\cloud;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "dc_foreign_black_phone".
 *
 * @property string $id
 * @property string $phone
 * @property integer $match_status
 * @property string $modify_time
 * @property string $create_time
 */
class DcForeignBlackPhone extends BaseNewDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dc_foreign_black_phone';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phone', 'modify_time', 'create_time'], 'required'],
            [['match_status'], 'integer'],
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
            'match_status' => '黑名单状态',
            'modify_time' => '修改时间',
            'create_time' => '创建时间',
        ];
    }

    public function getBlack($where)
    {
        return $this->find()->where($where)->count();
    }

    public function getForeignBlackPhoneInfo($where,$select = '*')
    {
        $select = explode(',',$select);
        $res =  $this->find()->select($select)->where(['phone'=>$where])->Asarray()->one();
        foreach ($select as $k => $v) {
            $val = ArrayHelper::getValue($res,$v,'');
            $res[$v] = $val ? intval($val) : 0;
        }
        return $res;
    }

    public function getForeignBlackPhoneData($where,$select = '*')
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
