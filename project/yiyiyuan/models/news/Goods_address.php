<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_goods_address".
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
class Goods_address extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_goods_address';
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

    public function getAddressById($id){
        if(!$id) {
            return null;
        }
        return self::find()->where(['id'=>$id])->one();
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
        $now = date('Y-m-d H:i:s');
        $condition = [
            'user_id' => $data['user_id'],
            'receive_name' => $data['user_name'],
            'receive_mobile' => $data['mobile'],
            'area_code' => $data['district'],
            'address_detail' => $data['address'],
            'create_time' => $now,
            'last_modify_time' => $now
        ];
        $error = $this->chkAttributes($condition);
        if ($error) {
            return FALSE;
        }
        return $this->save();
    }

    /**
     * 通过user_id获取收货地址
     * @param $user_id
     * @param null $default
     * @return bool
     */
    public function getAddress($user_id,$default = null){
        if(empty($user_id)){
            return false;
        }
        $area_code = self::find()->where(['user_id'=>$user_id])->orderBy('last_modify_time desc')->one();
        if(!$area_code){
            return false;
        }
        $areas = new Areas();
        $address = $areas->getProCityAreaName($area_code->area_code);
        $address_info['address'] = $address.$area_code->address_detail;
        $address_info['id'] = $area_code->id;
        $address_info['receive_name'] = $area_code->receive_name;
        $address_info['receive_mobile'] = $area_code->receive_mobile;
        return $address_info;
    }

    /**
     * 修改收货地址
     * @param $data
     * @return bool
     */
    public function editAddress($data){
        if(empty($data) || !is_array($data)){
            return false;
        }
        $now = date('Y-m-d H:i:s');
        $condition = [
            'receive_name' => $data['user_name'],
            'receive_mobile' => $data['mobile'],
            'area_code' => $data['district'],
            'address_detail' => $data['address'],
            'last_modify_time' => $now
        ];
        $error = $this->chkAttributes($condition);
        if ($error) {
            return FALSE;
        }
        return $this->save();
    }
}
