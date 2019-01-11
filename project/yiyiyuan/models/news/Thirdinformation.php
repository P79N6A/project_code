<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;
use app\commonapi\Logger;

/**
 * This is the model class for table "yi_third_information".
 *
 * @property string $id
 * @property string $uid
 * @property string $user_id
 * @property integer $sesamescore
 * @property integer $gender
 * @property string $housestate
 * @property string $careertype
 * @property integer $yearlimit
 * @property string $professional
 * @property string $frontfile
 * @property string $backfile
 * @property string $naturefile
 * @property string $address
 * @property string $issuedby
 * @property string $validdate
 * @property string $resolution
 * @property string $osversion
 * @property string $model
 * @property string $totalmemory
 * @property string $wifi
 * @property string $networktype
 * @property string $manufacturer
 * @property string $imeiordeviceid
 * @property string $isios
 * @property integer $gid
 * @property string $ip
 * @property string $country
 * @property string $latitude
 * @property string $longitude
 * @property string $site
 * @property string $last_modify_time
 * @property string $create_time
 */
class Thirdinformation extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_third_information';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'user_id', 'professional', 'sesamescore', 'gender'], 'integer'],
            [['validdate', 'last_modify_time', 'create_time', 'ip','latitude', 'longitude', 'yearlimit','come_from'], 'safe'],
            [['totalmemory'], 'number'],
            [['housestate', 'careertype', 'frontfile', 'backfile', 'naturefile', 'address', 'issuedby', 'wifi', 'manufacturer', 'imeiordeviceid', 'site', 'gid'], 'string', 'max' => 255],
            [['resolution', 'osversion', 'model', 'country'], 'string', 'max' => 20],
            [['networktype', 'isios'], 'string', 'max' => 10]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => 'Uid',
            'user_id' => 'User ID',
            'sesamescore' => 'Sesamescore',
            'gender' => 'Gender',
            'housestate' => 'Housestate',
            'careertype' => 'Careertype',
            'yearlimit' => 'Yearlimit',
            'professional' => 'Professional',
            'frontfile' => 'Frontfile',
            'backfile' => 'Backfile',
            'naturefile' => 'Naturefile',
            'address' => 'Address',
            'issuedby' => 'Issuedby',
            'validdate' => 'Validdate',
            'resolution' => 'Resolution',
            'osversion' => 'Osversion',
            'model' => 'Model',
            'totalmemory' => 'Totalmemory',
            'wifi' => 'Wifi',
            'networktype' => 'Networktype',
            'manufacturer' => 'Manufacturer',
            'imeiordeviceid' => 'Imeiordeviceid',
            'isios' => 'Isios',
            'gid' => 'Gid',
            'ip' => 'Ip',
            'country' => 'Country',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'site' => 'Site',
            'come_from' => 'Comefrom',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
        ];
    }
    
    //添加数据
    public function addThirdinformation($user_id,$condition){
        Logger::dayLog('RonshuApi', "thirdinformation", $condition);
        if (!is_array($condition) || empty($condition) || empty($user_id)) {
            return false;
        }
        $data = $condition;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        
        
        $up_info = $this->checkThirdinfomation($user_id);
//        print_r($up_info);
        if ($up_info) {
            foreach ($data as $k => $v) {
                $up_info->$k = $v;
            }
            $result = $up_info->save();
        } else {
            $data['create_time'] = date('Y-m-d H:i:s');
            $error = $this->chkAttributes($data);
        Logger::dayLog('RonshuApi', "thirdinformation_error", $error);
            if ($error) {
                return false;
            }
            $result = $this->save();
        }
        if (!$result) {
            return false;
        }
        return $result;
    }
    
    /*
     * 根据user_id查询thirdinformation信息
     */

    public function checkThirdinfomation($user_id) {
        if (!$user_id) {
            return FALSE;
        }
        $thirdinfomation_info = static::find()
                ->where(['user_id' => $user_id])
                ->one();

        return $thirdinfomation_info;
    }
    
}
