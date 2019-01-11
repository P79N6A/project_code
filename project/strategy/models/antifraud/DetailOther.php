<?php

namespace app\models\antifraud;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "af_detail_other".
 *
 * @property string $id
 * @property string $request_id
 * @property integer $aid
 * @property integer $user_id
 * @property integer $last3_answer
 * @property integer $last3_all
 * @property integer $last6_answer
 * @property integer $last6_all
 * @property integer $same_phone_num
 * @property integer $phone_register_month
 * @property string $create_time
 */
class DetailOther extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'af_detail_other';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['request_id', 'aid', 'user_id', 'phone_register_month', 'create_time'], 'required'],
            [['request_id', 'aid', 'user_id', 'last3_answer', 'last3_all', 'last6_answer', 'last6_all', 'same_phone_num', 'phone_register_month'], 'integer'],
            [['create_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'request_id' => '请求',
            'aid' => '业务ID',
            'user_id' => '用户id',
            'last3_answer' => '过去3个月被叫次数',
            'last3_all' => '过去3个月总通话次数',
            'last6_answer' => '过去4-6个月被叫次数',
            'last6_all' => '过去4-6个月总通话次数',
            'same_phone_num' => '3个月TOP30与4-6个月TOP20相同联系人数量',
            'phone_register_month' => '手机号码注册时长/月',
            'create_time' => '创建时间',
        ];
    }

    public function getDetailOther($where,$select = '*')
    {
        $select = explode(',',$select);
        $res =  $this->find()->select($select)->where($where)->Asarray()->orderby('id DESC')->one();
        if (empty($res)) {
            foreach ($select as $k => $v) {
                $res[$v] = -111;
            }
            $res['report_use_time'] = $res['phone_register_month'];
            unset($res['phone_register_month']);
            return $res;
        }     
        foreach ($select as $k => $v) {
            $val = ArrayHelper::getValue($res,$v,'');
            if (is_null($val)) {
                $res[$v] = -999;
            } else {
                $res[$v] = $val ? intval($val) : 0;
            }
        }
        $res['report_use_time'] = $res['phone_register_month'];
        unset($res['phone_register_month']);
        return $res;
    }

    public function getData($where,$select = '*')
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
