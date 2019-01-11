<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_goods_address_flows".
 *
 * @property string $id
 * @property string $user_id
 * @property string $receive_name
 * @property string $receive_mobile
 * @property integer $area_code
 * @property string $address_detail
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $version
 */
class Goods_address_flows extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_goods_address_flows';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'area_code', 'version'], 'integer'],
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

    public function setAddressflows($post_data){
        if(empty($post_data) || !is_array($post_data)){
            return false;
        }
        $now = date('Y-m-d H:i:s');
        $condition = [
            'user_id' => $post_data['user_id'],
            'receive_name' => $post_data['user_name'],
            'receive_mobile' => $post_data['mobile'],
            'area_code' => $post_data['district'],
            'address_detail' => $post_data['address'],
            'create_time' => $now,
            'last_modify_time' => $now
        ];
        $error = $this->chkAttributes($condition);
        if ($error) {
            return FALSE;
        }
        return $this->save();
    }
}
