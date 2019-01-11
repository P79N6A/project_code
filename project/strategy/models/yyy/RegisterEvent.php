<?php

namespace app\models\yyy;


use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "yi_register_event".
 * 一亿元注册决策表
 * @property string $id
 * @property string $user_id
 * @property integer $old_status
 * @property integer $new_status
 * @property integer $age_value
 * @property string $area_value
 * @property integer $number_value
 * @property integer $ip_value
 * @property integer $is_black
 * @property string $last_modify_time
 * @property string $create_time
 */
class RegisterEvent extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_register_event';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'old_status', 'new_status', 'last_modify_time', 'create_time'], 'required'],
            [['user_id', 'old_status', 'new_status', 'age_value', 'number_value', 'ip_value', 'is_black'], 'integer'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['area_value'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键，自增长',
            'user_id' => '用户ID',
            'old_status' => '驳回之前状态',
            'new_status' => '现在状态',
            'age_value' => '年龄',
            'area_value' => '地域',
            'number_value' => '设备号',
            'ip_value' => 'ip',
            'is_black' => '黑名单限制',
            'last_modify_time' => '最后更新时间',
            'create_time' => '创建时间',
        ];
    }
    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    public function getRegEventInfo($where,$select = '*')
    {
        $select = explode(',',$select);
        $res =  $this->find()->select($select)->where($where)->Asarray()->one();
        foreach ($select as $k => $v) {
            $val = ArrayHelper::getValue($res,$v,'');
            $res[$v] = $val ? intval($val) : 0;
        }
        return $res;
    }
}
