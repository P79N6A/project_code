<?php

namespace app\models\xs;

use Yii;

/**
 * This is the model class for table "dc_fraudmetrix_ontime".
 *
 * @property string $id
 * @property string $fid
 * @property integer $ph_id_user_diff
 * @property integer $ip_ph_land_match
 * @property integer $ip_id_land_match
 * @property integer $ph_id_land_match
 * @property integer $attr_land_match
 * @property integer $ph_care_list_match
 * @property integer $id_care_list_match
 * @property integer $vpn_query_match
 * @property integer $user_ph_danger_match
 * @property integer $user_id_danger_match
 * @property integer $user_card_danger_match
 * @property integer $user_device_danger_match
 * @property string $create_time
 */
class XsFraudmetrixOntime extends \app\models\xs\XsBaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dc_fraudmetrix_ontime';
    }

    public static function getDb() {
        return \Yii::$app->dbxsnew;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fid', 'ph_id_user_diff', 'ip_ph_land_match', 'ip_id_land_match', 'ph_id_land_match', 'attr_land_match', 'ph_care_list_match', 'id_care_list_match', 'vpn_query_match', 'user_ph_danger_match', 'user_id_danger_match', 'user_card_danger_match', 'user_device_danger_match'], 'integer'],
            [['create_time'], 'required'],
            [['create_time'], 'safe'],
            [['fid'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fid' => '同盾表id',
            'ph_id_user_diff' => '身份证姓名借款人手机号组合模糊证据库:0:否; 1:是',
            'ip_ph_land_match' => 'IP位置与手机归属地匹配:0:否; 1:是',
            'ip_id_land_match' => 'IP地理位置与身份证归属地匹配:0:否; 1:是',
            'ph_id_land_match' => '手机地理位置与身份证归属地匹配:0:否; 1:是',
            'attr_land_match' => '属性位置和位置匹配:0:否; 1:是',
            'ph_care_list_match' => '手机号关注名单:0:否; 1:是',
            'id_care_list_match' => '身份证号关注名单:0:否; 1:是',
            'vpn_query_match' => 'VPN代理访问:0:否; 1:是',
            'user_ph_danger_match' => '借款人手机疑似风险群体:0:否; 1:是',
            'user_id_danger_match' => '借款人身份证疑似风险群体:0:否; 1:是',
            'user_card_danger_match' => '借款人卡号疑似风险群体:0:否; 1:是',
            'user_device_danger_match' => '借款人设备疑似风险群体:0:否; 1:是',
            'create_time' => '创建时间',
        ];
    }

    public function saveData($data)
    { 
        $postData = [ 
            'fid' => $data['fid'],
            'create_time' =>$data["create_time"],
        ];
        $isOk = false;
        if(isset($data['ph_id_user_diff']) && $data['ph_id_user_diff'] > 0 ){
            $isOk = true;
            $postData['ph_id_user_diff'] = $data['ph_id_user_diff'];
        }
        if(isset($data['ip_ph_land_match']) && $data['ip_ph_land_match'] > 0 ){
            $isOk = true;
            $postData['ip_ph_land_match'] = $data['ip_ph_land_match'];
        }
        if(isset($data['ip_id_land_match']) && $data['ip_id_land_match'] > 0 ){
            $isOk = true;
            $postData['ip_id_land_match'] = $data['ip_id_land_match'];
        }
        if(isset($data['ph_id_land_match']) && $data['ph_id_land_match'] > 0 ){
            $isOk = true;
            $postData['ph_id_land_match'] = $data['ph_id_land_match'];
        }
        if(isset($data['attr_land_match']) && $data['attr_land_match'] > 0 ){
           $isOk = true;
           $postData['attr_land_match'] =  $data['attr_land_match'];
        }
        if(isset($data['ph_care_list_match']) && $data['ph_care_list_match'] > 0 ){
           $isOk = true;
           $postData['ph_care_list_match'] =  $data['ph_care_list_match'];
        }
        if(isset($data['id_care_list_match']) && $data['id_care_list_match'] > 0 ){
           $isOk = true;
           $postData['id_care_list_match'] =  $data['id_care_list_match'];
        }
        if (isset($data['vpn_query_match']) && $data['vpn_query_match'] >0) {
            $isOk = true;
            $postData['vpn_query_match'] = $data['vpn_query_match'];
        }
        if(isset($data['user_ph_danger_match']) && $data['user_ph_danger_match'] > 0 ){
           $isOk = true;
           $postData['user_ph_danger_match'] = $data['user_ph_danger_match'];
        }
        if(isset($data['user_id_danger_match']) && $data['user_id_danger_match'] > 0 ){
           $isOk = true;
           $postData['user_id_danger_match'] = $data['user_id_danger_match'];
        }
        if(isset($data['user_card_danger_match']) && $data['user_card_danger_match'] > 0 ){
           $isOk = true;
           $postData['user_card_danger_match'] = $data['user_card_danger_match'];
        }
        if(isset($data['user_device_danger_match']) && $data['user_device_danger_match'] > 0 ){
           $isOk = true;
           $postData['user_device_danger_match'] = $data['user_device_danger_match'];
        }
        if(!$isOk){
            return false;
        }
        $error = $this->chkAttributes($postData);
        if ($error) { 
            Logger::dayLog("xs","db","XsFraudmetrixOntime/saveData","save failed", $postData, $error);
            return false; 
        }
        return $this->save();
    }
}
