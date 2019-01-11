<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_guide_info".
 *
 * @property string $id
 * @property integer $code
 * @property string $mobile
 * @property string $phonelist
 * @property string $device_info
 * @property string $iden_info
 * @property string $work_info
 * @property string $contact
 * @property string $bank
 * @property integer $phone_detail
 * @property string $uid
 * @property string $create_time
 */
class Guide_info extends \app\models\BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_guide_info';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['code', 'mobile'], 'required'],
            [['code', 'phone_detail'], 'integer'],
            [['phonelist', 'device_info', 'iden_info', 'work_info', 'contact', 'bank'], 'string'],
            [['create_time'], 'safe'],
            [['mobile'], 'string', 'max' => 12],
            [['uid'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'mobile' => 'Mobile',
            'phonelist' => 'Phonelist',
            'device_info' => 'Device Info',
            'iden_info' => 'Iden Info',
            'work_info' => 'Work Info',
            'contact' => 'Contact',
            'bank' => 'Bank',
            'phone_detail' => 'Phone Detail',
            'uid' => 'Uid',
            'create_time' => 'Create Time',
        ];
    }

    public function addRecord($condition) {
        $data['code'] = intval($condition['code']);
        $data['mobile'] = $condition['mobile'];
        $data['phonelist'] = $condition['phonelist']; //{{name:"",number:""},{name:"",number:""}}
        $data['device_info'] = $condition['device_info']; //{gps:"{'latitude':'','longitude':''}",device_type:"",'device_sys':'','device_ip':'','uuid':''}
        $data['iden_info'] = $condition['iden_info']; //name,cid,nation,iden_address,iden_url,pic_url,score,edu,marriage,home_areas,home_address
        $data['work_info'] = $condition['work_info']; //company,position_id,telephone,industry,profession,income,email,company_areas,company_address
        $data['contact'] = $condition['contact']; //contacts_name,relation_common,mobile,relatives_name,relation_family,phone
        $data['bank'] = $condition['bank']; //{{name:"",number:""},{name:"",number:""}}
        $data['phone_detail'] = $condition['phone_detail'];
        $data['uid'] = $condition['uid'];
        $data['create_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return FALSE;
        }
        return $this->save();
    }

}
