<?php

namespace app\models\xs;

use Yii;

/**
 * This is the model class for table "dc_fm_rule_detail".
 *
 * @property string $id
 * @property string $fid
 * @property string $bph_fm_fack_detail
 * @property string $bph_fm_small_detail
 * @property string $bph_fm_sx_detail
 * @property string $bid_fm_sx_detail
 * @property string $bid_fm_court_sx_detail
 * @property string $bid_fm_court_enforce_detail
 * @property string $bid_fm_lost_detail
 * @property string $oph_fm_one_m_detail
 * @property string $oph_fm_two_m_detail
 * @property string $oph_fm_three_m_detail
 * @property string $oph_fm_six_m_detail
 * @property string $oph_fm_one_y_detail
 * @property string $oid_fm_one_m_detail
 * @property string $oid_fm_two_m_detail
 * @property string $oid_fm_three_m_detail
 * @property string $oid_fm_six_m_detail
 * @property string $oid_fm_one_y_detail
 * @property string $oid_fm_three_m_plat_detail
 * @property string $oph_fm_three_m_plat_detail
 * @property string $mid_fm_detail
 * @property string $mid_fm_seven_d_detail
 * @property string $mid_fm_one_m_detail
 * @property string $mid_fm_three_m_detail
 * @property string $mph_fm_detail
 * @property string $mph_fm_seven_d_detail
 * @property string $mph_fm_one_m_detail
 * @property string $mph_fm_three_m_detail
 * @property string $three_m_multi_remit_detail
 * @property string $ph_id_user_diff_detail
 * @property string $ip_ph_land_match_detail
 * @property string $ip_id_land_match_detail
 * @property string $ph_id_land_match_detail
 * @property string $attr_land_match_detail
 * @property string $ph_care_list_match_detail
 * @property string $id_care_list_match_detail
 * @property string $vpn_query_match_detail
 * @property string $user_ph_danger_match_detail
 * @property string $user_id_danger_match_detail
 * @property string $user_card_danger_match_detail
 * @property string $user_device_danger_match_detail
 * @property string $create_time
 */
class XsFmRuleDetail extends \app\models\xs\XsBaseNewModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dc_fm_rule_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fid'], 'integer'],
            [['bph_fm_fack_detail', 'bph_fm_small_detail', 'bph_fm_sx_detail', 'bid_fm_sx_detail', 'bid_fm_court_sx_detail', 'bid_fm_court_enforce_detail', 'bid_fm_lost_detail', 'oph_fm_one_m_detail', 'oph_fm_two_m_detail', 'oph_fm_three_m_detail', 'oph_fm_six_m_detail', 'oph_fm_one_y_detail', 'oid_fm_one_m_detail', 'oid_fm_two_m_detail', 'oid_fm_three_m_detail', 'oid_fm_six_m_detail', 'oid_fm_one_y_detail', 'oid_fm_three_m_plat_detail', 'oph_fm_three_m_plat_detail', 'mid_fm_detail', 'mid_fm_seven_d_detail', 'mid_fm_one_m_detail', 'mid_fm_three_m_detail', 'mph_fm_detail', 'mph_fm_seven_d_detail', 'mph_fm_one_m_detail', 'mph_fm_three_m_detail', 'three_m_multi_remit_detail', 'ph_id_user_diff_detail', 'ip_ph_land_match_detail', 'ip_id_land_match_detail', 'ph_id_land_match_detail', 'attr_land_match_detail', 'ph_care_list_match_detail', 'id_care_list_match_detail', 'vpn_query_match_detail', 'user_ph_danger_match_detail', 'user_id_danger_match_detail', 'user_card_danger_match_detail', 'user_device_danger_match_detail'], 'string'],
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
            'bph_fm_fack_detail' => '同盾虚假',
            'bph_fm_small_detail' => '同盾小号',
            'bph_fm_sx_detail' => '同盾失信',
            'bid_fm_sx_detail' => '同盾虚假',
            'bid_fm_court_sx_detail' => '同盾法院失信',
            'bid_fm_court_enforce_detail' => '同盾法院执行',
            'bid_fm_lost_detail' => '同盾失联',
            'oph_fm_one_m_detail' => '一个月内手机号信贷逾期次数统计',
            'oph_fm_two_m_detail' => '二个月内手机号信贷逾期次数统计',
            'oph_fm_three_m_detail' => '三个月内手机号信贷逾期次数统计',
            'oph_fm_six_m_detail' => '六个月内手机号信贷逾期次数统计',
            'oph_fm_one_y_detail' => '十二个月内手机号信贷逾期次数统计',
            'oid_fm_one_m_detail' => '一个月内身份证号信贷逾期次数统计',
            'oid_fm_two_m_detail' => '二个月内身份证号信贷逾期次数统计',
            'oid_fm_three_m_detail' => '三个月内身份证号信贷逾期次数统计',
            'oid_fm_six_m_detail' => '六个月内身份证号信贷逾期次数统计',
            'oid_fm_one_y_detail' => '十二个月内身份证号信贷逾期次数统计',
            'oid_fm_three_m_plat_detail' => '三月个内身份证号信贷逾期平台个数统计',
            'oph_fm_three_m_plat_detail' => '三个月内手机号信贷逾期平台个数统计',
            'mid_fm_detail' => '同盾多投',
            'mid_fm_seven_d_detail' => '7天内申请人身份证号在多个平台进行借款的数量统计',
            'mid_fm_one_m_detail' => '1个月内申请人身份证号在多个平台进行借款的数量统计',
            'mid_fm_three_m_detail' => '3个月内申请人身份证号在多个平台进行借款的数量统计',
            'mph_fm_detail' => '同盾多投',
            'mph_fm_seven_d_detail' => '7天内申请人手机号在多个平台进行借款的数量统计',
            'mph_fm_one_m_detail' => '7天内申请人手机号在多个平台进行借款的数量统计',
            'mph_fm_three_m_detail' => '7天内申请人手机号在多个平台进行借款的数量统计',
            'three_m_multi_remit_detail' => '3个月内申请人在多个平台被放款_不包含本合作方',
            'ph_id_user_diff_detail' => '身份证姓名借款人手机号组合模糊证据库:0:否; 1:是',
            'ip_ph_land_match_detail' => 'IP位置与手机归属地匹配:0:否; 1:是',
            'ip_id_land_match_detail' => 'IP地理位置与身份证归属地匹配:0:否; 1:是',
            'ph_id_land_match_detail' => '手机地理位置与身份证归属地匹配:0:否; 1:是',
            'attr_land_match_detail' => '属性位置和位置匹配:0:否; 1:是',
            'ph_care_list_match_detail' => '手机号关注名单:0:否; 1:是',
            'id_care_list_match_detail' => '身份证号关注名单:0:否; 1:是',
            'vpn_query_match_detail' => 'VPN代理访问:0:否; 1:是',
            'user_ph_danger_match_detail' => '借款人手机疑似风险群体:0:否; 1:是',
            'user_id_danger_match_detail' => '借款人身份证疑似风险群体:0:否; 1:是',
            'user_card_danger_match_detail' => '借款人卡号疑似风险群体:0:否; 1:是',
            'user_device_danger_match_detail' => '借款人设备疑似风险群体:0:否; 1:是',
            'create_time' => '创建时间',
        ];
    }

    public function saveData($data){
        $postData = [ 
            'fid' => $data['fid'],
            'create_time' =>date('Y-m-d H:i:s'),
        ];
        unset($data['fid']);
        $isOk = false;
        foreach ($data as $key => $value) {
            if(!empty($data[$key])){
                $isOk = true;
                $postData[$key] = $data[$key];
            }
        }
        if(!$isOk){
            return false;
        }
        $error = $this->chkAttributes($postData);
        if ($error) { 
            Logger::dayLog("xs","db","XsFmRuleDetail/saveData","save failed", $postData, $error);
            return false; 
        } 
        return $this->save(); 
    }
}
