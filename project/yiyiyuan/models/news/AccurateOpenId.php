<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_accurate_open_id".
 *
 * @property string $id
 * @property string $user_id
 * @property string $openid
 * @property integer $is_coupon
 * @property integer $sms_type
 * @property string $create_time
 */
class AccurateOpenId extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_accurate_open_id';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'is_coupon', 'sms_type'], 'integer'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['openid'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'openid' => 'Openid',
            'is_coupon' => 'Is Coupon',
            'sms_type' => 'Sms Type',
            'create_time' => 'Create Time',
        ];
    }

    public function addList($condition)
    {
        if (empty($condition)) return false;
        foreach($condition as $key=>$value){
            $this->$key = $value;
        }
        $this->is_coupon = 0;
        $this->last_modify_time = date("Y-m-d H:i:s");
        $this->create_time = date("Y-m-d H:i:s");
        $ret = $this->save();
        return $ret;
    }
    public function updateIsCoupon($id, $coupon_code=0)
    {
        $ret = false;
        if (empty($id)) return $ret;
        $accurate = self::find()->where(['id'=>$id])->one();

        if (!empty($accurate)){
            $accurate->is_coupon = $coupon_code;
            $accurate->last_modify_time = date("Y-m-d H:i:s");
            $ret = $accurate->save();
        }
        return $ret;
    }
}
