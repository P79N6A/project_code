<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_mall_order_address".
 *
 * @property string $id
 * @property string $order_id
 * @property string $user_id
 * @property string $receive_name
 * @property string $receive_mobile
 * @property integer $area_code
 * @property string $address_detail
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $version
 */
class MallOrderAddress extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_mall_order_address';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'user_id', 'receive_name', 'receive_mobile', 'area_code', 'address_detail'], 'required'],
            [['order_id', 'user_id', 'area_code', 'version'], 'integer'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['receive_name'], 'string', 'max' => 32],
            [['receive_mobile'], 'string', 'max' => 20],
            [['address_detail'], 'string', 'max' => 128]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'user_id' => 'User ID',
            'receive_name' => 'Receive Name',
            'receive_mobile' => 'Receive Mobile',
            'area_code' => 'Area Code',
            'address_detail' => 'Address Detail',
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
            'version' => 'Version',
        ];
    }

    public function optimisticLock() {
        return "version";
    }

    /**
     * 添加收货地址
     * @param $data
     * @return bool
     */
    public function setAddress($data){
        if(empty($data) || !is_array($data)){
            return false;
        }
        $condition = $data;
        $now = date('Y-m-d H:i:s');
        $condition['create_time'] = $now;
        $condition['last_modify_time'] = $now;
        $error = $this->chkAttributes($condition);
        if ($error) {
            return FALSE;
        }
        return $this->save();
    }

}
