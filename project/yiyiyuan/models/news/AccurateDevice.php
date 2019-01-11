<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_accurate_device".
 *
 * @property string $id
 * @property string $user_id
 * @property string $device_tokens
 * @property string $device_type
 * @property integer $sms_type
 * @property integer $is_coupon
 * @property string $msg_id
 * @property string $create_time
 */
class AccurateDevice extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_accurate_device';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'sms_type', 'is_coupon'], 'integer'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['device_tokens', 'msg_id'], 'string', 'max' => 64],
            [['device_type'], 'string', 'max' => 10]
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
            'device_tokens' => 'Device Tokens',
            'device_type' => 'Device Type',
            'sms_type' => 'Sms Type',
            'is_coupon' => 'Is Coupon',
            'msg_id' => 'Msg ID',
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
            $accurate->last_modify_time = date("Y-m-d H:i:s");
            $accurate->is_coupon = $coupon_code;
            $ret = $accurate->save();
        }
        return $ret;
    }
}
