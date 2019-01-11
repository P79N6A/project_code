<?php

namespace app\models\xs;

use Yii;
use app\common\Logger;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "dc_black_box".
 *
 * @property string $id
 * @property string $fid
 * @property string $phone
 * @property string $idcard
 * @property integer $device_status_middle
 * @property integer $device_status_high
 * @property integer $device_first_appear
 * @property integer $root_check
 * @property integer $cheat_tool_check
 * @property integer $suspected_simulated_position
 * @property integer $no_blackbox_param
 * @property integer $device_lack_high
 * @property integer $device_lack_middle
 * @property integer $device_get_abnormal
 * @property integer $monitor_debugger
 * @property integer $break_prison
 * @property integer $not_inner_mesh_ip
 * @property integer $false_equipment
 * @property integer $reg_not_inner_mesh_ip
 * @property integer $android_blue_stacks
 * @property integer $one_device_account_num
 * @property integer $seven_device_account_num
 * @property integer $one_device_ip_num
 * @property integer $seven_device_ip_num
 * @property integer $one_account_device_num
 * @property integer $seven_account_device_num
 * @property integer $three_m_card_multi
 * @property integer $three_m_device_multi
 * @property string $create_time
 * @property string $modify_time
 */
class XsBlackBox extends \app\models\repo\CloudBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dc_black_box';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fid', 'device_status_middle', 'device_status_high', 'device_first_appear', 'root_check', 'cheat_tool_check', 'suspected_simulated_position', 'no_blackbox_param', 'device_lack_high', 'device_lack_middle', 'device_get_abnormal', 'monitor_debugger', 'break_prison', 'not_inner_mesh_ip', 'false_equipment', 'reg_not_inner_mesh_ip', 'android_blue_stacks', 'one_device_account_num', 'seven_device_account_num', 'one_device_ip_num', 'seven_device_ip_num', 'one_account_device_num', 'seven_account_device_num', 'three_m_card_multi', 'three_m_device_multi'], 'integer'],
            [['phone', 'idcard', 'create_time', 'modify_time'], 'required'],
            [['create_time', 'modify_time'], 'safe'],
            [['phone', 'idcard'], 'string', 'max' => 20]
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
            'phone' => '手机',
            'idcard' => '身份证',
            'device_status_middle' => '设备状态异常_中风险',
            'device_status_high' => '设备状态异常_高风险',
            'device_first_appear' => '设备状态异常_设备首次出现',
            'root_check' => 'ROOT识别',
            'cheat_tool_check' => '作弊工具识别',
            'suspected_simulated_position' => '设备状态异常_疑似模拟定位',
            'no_blackbox_param' => '设备标识缺失异常_没有传递设备指纹参数',
            'device_lack_high' => '设备标识缺失异常_高风险',
            'device_lack_middle' => '设备标识缺失异常_中风险',
            'device_get_abnormal' => '设备获取异常',
            'monitor_debugger' => '设备状态异常_监测到调试器',
            'break_prison' => '越狱识别',
            'not_inner_mesh_ip' => '设备缺失且使用国外或港澳台IP',
            'false_equipment' => '设备状态异常_虚假设备',
            'reg_not_inner_mesh_ip' => '登录设备缺失且使用国外或港澳台IP',
            'android_blue_stacks' => '安卓模拟器识别',
            'one_device_account_num' => '1天内设备使用过多账户进行借款',
            'seven_device_account_num' => '7天内设备使用过多账户进行借款',
            'one_device_ip_num' => '1天内设备使用过多的IP进行借款',
            'seven_device_ip_num' => '7天内设备使用过多的IP进行借款',
            'one_account_device_num' => '1天内账户在过多的设备上进行借款',
            'seven_account_device_num' => '7天内账户在过多的设备上进行借款',
            'three_m_card_multi' => '3个月内银行卡在多个平台进行借款',
            'three_m_device_multi' => '3个月内设备在多个平台进行借款',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }
    public function getOne($idcard,$phone) {
        $where = ['idcard' => $idcard,'phone' => $phone];
        return static::find()->where($where)->limit(1)->one();
    }

    /**
     * 设置设备指纹的方法
     */
    public function setDeviceMark($data) {
        //1. 字段验证
        $time = date("Y-m-d H:i:s");
        $idcard = isset($data['idcard']) ? $data['idcard'] : '';
        $phone = isset($data['phone']) ? $data['phone'] : '';
        $fid = isset($data['fid']) ? $data['fid'] : 0;
        if (!$idcard || !$phone) {
            return false;
        }

        //2. 仅过滤>0值
        $postData = $this->filterValues($data);
        if (empty($postData)) {
            return false;
        }

        //3. 更新还是添加
        $model = $this->getOne($idcard,$phone);
        if (!$model) {
            $model = new self;
            $postData['fid'] = $fid;
            $postData['idcard'] =  $idcard;
            $postData['phone'] = $phone;
            $postData['create_time'] =  $time;
        }
        $postData['modify_time'] = $time;
        //4. 保存数据
        $error = $model->chkAttributes($postData);
        if ($error) {
            Logger::dayLog("xs/XsBlackBox","saveData","save failed", $postData, $error);
            return false;
        }

        return $model->save();
    }

    /**
     * 设置0,1值数据
     * @param [] $data
     * @return []
     */
    private function filterValues($data) {
        if (!is_array($data) || empty($data)) {
            return [];
        }
        $fields = [
            'device_status_middle',
            'device_status_high',
            'device_first_appear',
            'root_check',
            'cheat_tool_check',
            'suspected_simulated_position',
            'no_blackbox_param',
            'device_lack_high',
            'device_lack_middle',
            'device_get_abnormal',
            'monitor_debugger',
            'break_prison',
            'not_inner_mesh_ip',
            'false_equipment',
            'reg_not_inner_mesh_ip',
            'android_blue_stacks',
            'one_device_account_num',
            'seven_device_account_num',
            'one_device_ip_num',
            'seven_device_ip_num',
            'one_account_device_num',
            'seven_account_device_num',
            'three_m_card_multi',
            'three_m_device_multi',
        ];
        $postData = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $fields) && $value > 0 ) {
                $postData[$key] = $value;
            }
        }
        return $postData;
    }
}
