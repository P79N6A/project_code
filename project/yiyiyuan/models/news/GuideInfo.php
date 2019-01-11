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
class GuideInfo extends \app\models\BaseModel {

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
            'id'           => 'ID',
            'code'         => 'Code',
            'mobile'       => 'Mobile',
            'phonelist'    => 'Phonelist',
            'device_info'  => 'Device Info',
            'iden_info'    => 'Iden Info',
            'work_info'    => 'Work Info',
            'contact'      => 'Contact',
            'bank'         => 'Bank',
            'phone_detail' => 'Phone Detail',
            'uid'          => 'Uid',
            'create_time'  => 'Create Time',
        ];
    }

    /**
     * 新增记录
     * @param $data
     * @return bool
     */
    public function addOne($data) {
        $data['create_time'] = date("Y-m-d H:i:s");
        $data['version']     = 0;
        $error               = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

}
