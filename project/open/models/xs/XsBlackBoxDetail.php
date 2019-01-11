<?php

namespace app\models\xs;

use Yii;
use app\common\Logger;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "dc_black_box_detail".
 *
 * @property string $id
 * @property string $fid
 * @property string $geoip_info
 * @property string $device_info
 * @property string $device_status_middle_detail
 * @property string $device_status_high_detail
 * @property string $device_first_appear_detail
 * @property string $root_check_detail
 * @property string $cheat_tool_check_detail
 * @property string $suspected_simulated_position_detail
 * @property string $no_blackbox_param_detail
 * @property string $device_lack_high_detail
 * @property string $device_lack_middle_detail
 * @property string $device_get_abnormal_detail
 * @property string $monitor_debugger_detail
 * @property string $break_prison_detail
 * @property string $not_inner_mesh_ip_detail
 * @property string $false_equipment_detail
 * @property string $reg_not_inner_mesh_ip_detail
 * @property string $android_blue_stacks_detail
 * @property string $one_device_account_num_detail
 * @property string $seven_device_account_num_detail
 * @property string $one_device_ip_num_detail
 * @property string $seven_device_ip_num_detail
 * @property string $one_account_device_num_detail
 * @property string $seven_account_device_num_detail
 * @property string $three_m_card_multi_detail
 * @property string $three_m_device_multi_detail
 * @property string $create_time
 */
class XsBlackBoxDetail extends \app\models\repo\CloudBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dc_black_box_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fid'], 'integer'],
            [['geoip_info', 'device_info', 'device_status_middle_detail', 'device_status_high_detail', 'device_first_appear_detail', 'root_check_detail', 'cheat_tool_check_detail', 'suspected_simulated_position_detail', 'no_blackbox_param_detail', 'device_lack_high_detail', 'device_lack_middle_detail', 'device_get_abnormal_detail', 'monitor_debugger_detail', 'break_prison_detail', 'not_inner_mesh_ip_detail', 'false_equipment_detail', 'reg_not_inner_mesh_ip_detail', 'android_blue_stacks_detail', 'one_device_account_num_detail', 'seven_device_account_num_detail', 'one_device_ip_num_detail', 'seven_device_ip_num_detail', 'one_account_device_num_detail', 'seven_account_device_num_detail', 'three_m_card_multi_detail', 'three_m_device_multi_detail'], 'string'],
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
            'geoip_info' => 'Geoip Info',
            'device_info' => 'Device Info',
            'device_status_middle_detail' => '设备状态异常_中风险',
            'device_status_high_detail' => '设备状态异常_高风险',
            'device_first_appear_detail' => '设备状态异常_设备首次出现',
            'root_check_detail' => 'ROOT识别',
            'cheat_tool_check_detail' => '作弊工具识别',
            'suspected_simulated_position_detail' => '设备状态异常_疑似模拟定位',
            'no_blackbox_param_detail' => '设备标识缺失异常_没有传递设备指纹参数',
            'device_lack_high_detail' => '设备标识缺失异常_高风险',
            'device_lack_middle_detail' => '设备标识缺失异常_中风险',
            'device_get_abnormal_detail' => '设备获取异常',
            'monitor_debugger_detail' => '设备状态异常_监测到调试器',
            'break_prison_detail' => '越狱识别',
            'not_inner_mesh_ip_detail' => '设备缺失且使用国外或港澳台IP',
            'false_equipment_detail' => '设备状态异常_虚假设备',
            'reg_not_inner_mesh_ip_detail' => '登录设备缺失且使用国外或港澳台IP',
            'android_blue_stacks_detail' => '安卓模拟器识别',
            'one_device_account_num_detail' => '1天内设备使用过多账户进行借款',
            'seven_device_account_num_detail' => '7天内设备使用过多账户进行借款',
            'one_device_ip_num_detail' => '1天内设备使用过多的IP进行借款',
            'seven_device_ip_num_detail' => '7天内设备使过多的IP进行借款',
            'one_account_device_num_detail' => '1天内账户在过多的设备上进行借款',
            'seven_account_device_num_detail' => '7天内账户在过多的设备上进行借款',
            'three_m_card_multi_detail' => '3个月内银行卡在多个平台进行借款',
            'three_m_device_multi_detail' => '3个月内设备在多个平台进行借款',
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
            Logger::dayLog("xs/XsBlackBoxDetail","saveData","save failed", $postData, $error);
            return false; 
        } 
        return $this->save(); 
    }
}
