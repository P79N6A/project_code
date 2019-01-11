<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/10
 * Time: 10:12
 */
namespace app\common;

use app\common\Http;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use \app\common\ApiClientCrypt;
use app\models\xs\XsSplitApi;

class Fmdown {
    private $res_code; // 0表示无错误
    public $res_data;
    private static $oXsSplitApi;
    private static $seven_percent_class = [];
    private static $one_mouth_percent_class = [];
    public function __construct($type = 1) {
        self::$oXsSplitApi = new XsSplitApi($type);
    }
    /**
     * 获取下载链接
     * @param  str $seq_id
     * @return []
     */
    public function get($seq_id) {
        $url = "fraudmetrix/query";
        $openApi = new ApiClientCrypt;
        $res = $openApi->sent($url, [
            'seq_id' => $seq_id,
        ]);
        $res = $openApi->parseResponse($res);
        $this->res_code = $res['res_code'];
        $this->res_data = $res['res_data'];

        if ($this->res_code) {
            return null;
        } else {
            return $this->res_data;
        }
    }
    /**
     * 下载聚信立报告和详情
     * @return [type] [description]
     */
    public function downReport() {
        if ($this->res_code) {
            return null;
        }
        $url = $this->res_data['report_url'];
        return $this->curlGet($url);
    }
    public function downDetail() {
        if ($this->res_code) {
            return null;
        }
        $url = $this->res_data['detail_url'];
        return $this->curlGet($url);
    }
    /**
     * 增加重试功能: 共计25秒
     * @param  str  $url
     * @param  integer $timeout
     * @return []
     */
    private function curlGet($url, $timeout = 20) {
        //$url = "http://www.test.com/timeout.php";
        $timeouts = [5, 10, 20];
        foreach( $timeouts as $timeout){
            $result = $this->_curlGet($url, $timeout);
            if( $result !== false ){
                break;
            }
        }
        $res =  json_decode($result, true);
        return $res;
    }
    private function _curlGet($url, $timeout) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //不输出内容
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        $result = curl_exec($ch);

        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($http_status != 200) {
            return false;
        }
        return $result;
    }
    /**
     * 分析并返回结果报告
     * @return [type] [description]
     */
    public function analysis($seq_id) {
        //1 下载数据
        $res_data = $this->get($seq_id);
        if (!$res_data) {
            return null;
        }
        $report = $this->downReport();
        $detail = $this->downDetail();
        if( !$report || !$detail){
            return null;
        }

        $data = $this->runAnalysis($report, $detail);
        if (!$data) {
            return null;
        }
        $data['event'] = $this->res_data['event_type'];
        return $data;
    }
    /**
     * 用于给外部用
     * @param  json $report
     * @param  json $detail
     * @return []
     */
    public function runAnalysis(&$report, &$detail) {
        //1. report 不可为空
        $report_res = $this->runReport($report);
        if (!is_array($report_res) || empty($report_res) ) {
            return null;
        }
        //2. 详情可为空, 但貌似也没啥用了
        $detail_res = $this->runDetail($detail);
        if (!$detail_res) {
            $detail_res = [];
        }
        $data = array_merge($report_res, $detail_res,self::$seven_percent_class,self::$one_mouth_percent_class);
        return empty($data) ? null : $data;
    }
    /**
     * 分析报告内容
     * @param  [type] $report [description]
     * @return [type]         [description]
     */
    private function runReport(&$report) {
        if (!is_array($report) || !isset($report['policy_set_name']) ) {
            return null;
        }
        $events = [
            '网页注册策略集',
            '网页借款策略集',
        ];
        $event = $this->reportEvent($report['policy_set_name']);
        $data = [
            'seq_id' => $report['seq_id'],
            'event' => $event,
            'decision' => $report['final_decision'],
            'score' => $report['final_score'],
        ];

        return $data;
    }
    /**
     * 获取事件类型
     * @param  str $v 事件中文名
     * @return     str
     */
    private function reportEvent($v) {
        $event = '';
        switch ($v) {
            case '网页注册策略集':
                $event = 'register_web';
                break;
            case '网页借款策略集':
                $event = 'loan_web';
                break;

            default:
                $event = '';
                break;
        }
        return $event;
    }
    /**
     * 分析详情内容
     * @param $detail [description]
     * @return []
     */
    private function runDetail(&$detail) {
        if (!$detail) {
            return null;
        }
        $rules = $this->detailRules($detail);
        if (!is_array($rules) || empty($rules)) {
            return null;
        }
        // 解析同盾逾期规则
        $over_rules = $this->overAnalysis($rules);
        // 解析原数据
        $myrules = $this->myrulesAnalysis($rules);
        // 解析详情
        $detail = $this->analysisDetail($rules);
        $data = array_merge($over_rules,$myrules,$detail);
        return $data;
    }
    /**
     * 获取规则列表名称
     * @param  [] $detail
     * @return []
     */
    private function detailRules(&$detail) {
        $over_rules = [];
        $myrules = [];
        $list_rules = ArrayHelper::getValue($detail, "策略列表");
        if (!is_array($list_rules) || empty($list_rules)) {
            return null;
        }
        foreach ($list_rules as $item) {
            if (isset($item['策略名称']) && $item['策略名称'] == '失信借款_网页') {
                $rules = $item['规则列表'];
                foreach ($rules as $rule) {
                    $name = ArrayHelper::getValue($rule, "规则名称", '');
                    if ($name == '三月内手机号信贷逾期平台统计' || $name == '三月内身份证号信贷逾期平台统计') {
                        $num = ArrayHelper::getValue($rule, "规则详情.0.失信平台个数", 0);
                    } else {
                        $num = ArrayHelper::getValue($rule, "规则详情.0.失信次数", 0);
                    }
                    $over_rules[] = [
                        'name' => $name,
                        'num' => $num,
                        'detail' => json_encode(ArrayHelper::getValue($rule, "规则详情", ''),JSON_UNESCAPED_UNICODE),
                    ];
                }
            } else {
                $rules = $item['规则列表'];
                foreach ($rules as $rule) {
                    $myrules[] = $this->getMyrules($rule);
                }
            }
        }
        $allrules = array_merge($over_rules, $myrules);
        return $allrules;
    }
    private function analysisDetail(&$rules){
        $rules_info = ArrayHelper::map($rules,'name','detail');
        if (!is_array($rules_info) || empty($rules_info)) {
            return [];
        }
        $oph_fm_one_m = $this->getRuleDetails($rules_info,[
            "一月内手机号信贷逾期统计",
        ]);
        $oph_fm_two_m = $this->getRuleDetails($rules_info,[
            "二月内手机号信贷逾期统计",
        ]);
        $oph_fm_three_m = $this->getRuleDetails($rules_info,[
            "三月内手机号信贷逾期统计",
        ]);
        $oph_fm_six_m = $this->getRuleDetails($rules_info,[
            "六月内手机号信贷逾期统计",
        ]);
        $oph_fm_one_y = $this->getRuleDetails($rules_info,[
            "十二月内手机号信贷逾期统计",
        ]);
        $oph_fm_three_m_plat = $this->getRuleDetails($rules_info,[
            "三月内手机号信贷逾期平台统计",
        ]);
        $oid_fm_one_m = $this->getRuleDetails($rules_info,[
            "一月内身份证号信贷逾期统计",
        ]);
        $oid_fm_two_m = $this->getRuleDetails($rules_info,[
            "二月内身份证号信贷逾期统计",
        ]);
        $oid_fm_three_m = $this->getRuleDetails($rules_info,[
            "三月内身份证号信贷逾期统计",
        ]);
        $oid_fm_six_m = $this->getRuleDetails($rules_info,[
            "六月内身份证号信贷逾期统计",
        ]);
        $oid_fm_one_y = $this->getRuleDetails($rules_info,[
            "十二月内身份证号信贷逾期统计",
        ]);
        $oid_fm_three_m_plat = $this->getRuleDetails($rules_info,[
            "三月内身份证号信贷逾期平台统计",
        ]);
        // 黑名单:身份证
        $bid_fm_sx = $this->getRuleDetails($rules_info, [
            "注册身份证号码命中失信证据库",
            "借款身份证命中全局失信证据库",
        ]);
        $bid_fm_court_sx = $this->getRuleDetails($rules_info, [
            "注册身份证命中法院失信证据库",
            "借款人身份证命中法院失信证据库",
        ]);
        $bid_fm_court_enforce = $this->getRuleDetails($rules_info, [
            "注册身份证命中法院执行证据库",
            "借款人身份证命中法院执行证据库",
        ]);
        $bid_fm_lost = $this->getRuleDetails($rules_info, [
            "注册身份证命中失联证据库",
            "借款人身份证命中失联证据库",
        ]);

        // 黑名单:手机号
        $bph_fm_fack = $this->getRuleDetails($rules_info, [
            "注册手机命中虚假号码证据库",
            "借款手机号命中虚假手机号码证据库",
        ]);
        $bph_fm_small = $this->getRuleDetails($rules_info, [
            "注册手机命中通信小号证据库",
            "借款手机号命中通信小号证据库",
        ]);
        $bph_fm_sx = $this->getRuleDetails($rules_info, [
            "注册手机命中失信证据库",
            "借款手机号命中全局失信证据库",
        ]);

        // 多投
        $mid_fm = $this->getRuleDetails($rules_info, [
            "3个月内身份证在多个平台进行借款",
            "3个月内身份证在5个平台进行借款",
            "3个月内身份证在6个平台进行借款",
            "3个月内身份证在7个平台进行借款",
            "3个月内身份证在8个平台进行借款",
            "3个月内身份证在9个平台进行借款",
            "3个月内身份证在10个平台进行借款",
            "3个月内身份证在11个平台进行借款",
            "3个月内身份证在12个平台进行借款",
            "3个月内身份证在13个平台进行借款",
            "3个月内身份证在14个平台进行借款",
            "3个月内身份证在15个平台进行借款",
            "3个月内身份证在16个平台进行借款",
            "3个月内身份证在17个平台进行借款",
            "3个月内身份证在18个平台进行借款",
            "3个月内身份证在19个平台进行借款",
            "3个月内身份证在多于20个平台进行借款",
        ]);
        $mph_fm = $this->getRuleDetails($rules_info, [
            "3个月内手机在多个平台进行借款",
            "3个月内手机在5个平台进行借款",
            "3个月内手机在6个平台进行借款",
            "3个月内手机在7个平台进行借款",
            "3个月内手机在8个平台进行借款",
            "3个月内手机在9个平台进行借款",
            "3个月内手机在10个平台进行借款",
            "3个月内手机在11个平台进行借款",
            "3个月内手机在12个平台进行借款",
            "3个月内手机在13个平台进行借款",
            "3个月内手机在14个平台进行借款",
            "3个月内手机在15个平台进行借款",
            "3个月内手机在16个平台进行借款",
            "3个月内手机在17个平台进行借款",
            "3个月内手机在18个平台进行借款",
            "3个月内手机在19个平台进行借款",
            "3个月内手机在多于20个平台进行借款",
        ]);
        //7天，一个月，三个月身份证手机号逾期数据
        $seven_d = $this->getRuleDetails($rules_info,[
            '7天内申请人在多个平台进行借款的数量统计',
        ]);
        $one_m = $this->getRuleDetails($rules_info,[
            '1个月内申请人在多个平台进行借款的数量统计'
        ]);
        $three_m = $this->getRuleDetails($rules_info,[
            '3个月内申请人在多个平台进行借款的数量统计'
        ]);

        //3个月内申请人在多个平台被放款_不包含本合作方
        $three_m_multi_remit = $this->getRuleDetails($rules_info, [
            "3个月内申请人在多个平台被放款_不包含本合作方",
        ]);
        // 身份证姓名借款人手机号组合模糊证据库
        $ph_id_user_diff = $this->getRuleDetails($rules_info, [
            "身份证姓名借款人手机号组合模糊证据库",
        ]);
        // IP位置与手机归属地匹配
        $ip_ph_land_match = $this->getRuleDetails($rules_info, [
            "IP位置与手机归属地匹配",
        ]);
        // IP地理位置与身份证归属地匹配
        $ip_id_land_match = $this->getRuleDetails($rules_info, [
            "IP地理位置与身份证归属地匹配",
        ]);
        // 手机地理位置与身份证归属地匹配
        $ph_id_land_match = $this->getRuleDetails($rules_info, [
            "手机地理位置与身份证归属地匹配",
        ]);
        // 属性位置和位置匹配
        $attr_land_match = $this->getRuleDetails($rules_info, [
            "属性位置和位置匹配",
        ]);

        // 手机号关注名单
        $ph_care_list_match = $this->getRuleDetails($rules_info, [
            "关注名单",
        ]);
        // 身份证号关注名单
        $id_care_list_match = $this->getRuleDetails($rules_info, [
            "身份证号关注名单",
        ]);
        // VPN代理访问
        $vpn_query_match = $this->getRuleDetails($rules_info, [
            "VPN代理访问",
        ]);
        // 借款人手机疑似风险群体
        $user_ph_danger_match = $this->getRuleDetails($rules_info, [
            "借款人手机疑似风险群体",
        ]);
        // 借款人身份证疑似风险群体
        $user_id_danger_match = $this->getRuleDetails($rules_info, [
            "借款人身份证疑似风险群体",
        ]);
        // 借款人卡号疑似风险群体
        $user_card_danger_match = $this->getRuleDetails($rules_info, [
            "借款人卡号疑似风险群体",
        ]);
        // 借款人设备疑似风险群体
        $user_device_danger_match = $this->getRuleDetails($rules_info, [
            "借款人设备疑似风险群体",
        ]);
        $data =  [
            // 逾期
            'oph_fm_one_m_detail' => $oph_fm_one_m,
            'oph_fm_two_m_detail' => $oph_fm_two_m,
            'oph_fm_three_m_detail' => $oph_fm_three_m,
            'oph_fm_six_m_detail' => $oph_fm_six_m,
            'oph_fm_one_y_detail' => $oph_fm_one_y,
            'oph_fm_three_m_plat_detail' => $oph_fm_three_m_plat,
            'oid_fm_one_m_detail' => $oid_fm_one_m,
            'oid_fm_two_m_detail' => $oid_fm_two_m,
            'oid_fm_three_m_detail' => $oid_fm_three_m,
            'oid_fm_six_m_detail' => $oid_fm_six_m,
            'oid_fm_one_y_detail' => $oid_fm_one_y,
            'oid_fm_three_m_plat_detail' => $oid_fm_three_m_plat,
            // 黑名单
            "bph_fm_fack_detail" => $bph_fm_fack,
            "bph_fm_small_detail" => $bph_fm_small,
            "bph_fm_sx_detail" => $bph_fm_sx,

            "bid_fm_sx_detail" => $bid_fm_sx,
            "bid_fm_court_sx_detail" => $bid_fm_court_sx,
            "bid_fm_court_enforce_detail" => $bid_fm_court_enforce,
            "bid_fm_lost_detail" => $bid_fm_lost,

            // 多投
            'mph_fm_detail' => $mph_fm, // 手机多投
            'mid_fm_detail' => $mid_fm, // 身份证多投
            //7天，一个月，三个月身份证手机号逾期数据
            //身份证多投
            'mid_fm_seven_d_detail' => $seven_d,
            'mid_fm_one_m_detail' => $one_m,
            'mid_fm_three_m_detail' => $three_m,
            //手机号多投
            'mph_fm_seven_d_detail' => $seven_d,
            'mph_fm_one_m_detail' => $one_m,
            'mph_fm_three_m_detail' => $three_m,
            'three_m_multi_remit_detail' => $three_m_multi_remit,

            // 身份证姓名借款人手机号组合模糊证据库
            'ph_id_user_diff_detail' => $ph_id_user_diff,
            // IP位置与手机归属地匹配
            'ip_ph_land_match_detail' => $ip_ph_land_match,
            // IP地理位置与身份证归属地匹配
            'ip_id_land_match_detail' => $ip_id_land_match,
            // 手机地理位置与身份证归属地匹配
            'ph_id_land_match_detail' => $ph_id_land_match,
            // 属性位置和位置匹配
            'attr_land_match_detail' => $attr_land_match,

            // 手机号关注名单
            'ph_care_list_match_detail' => $ph_care_list_match,
            // 身份证号关注名单
            'id_care_list_match_detail' => $id_care_list_match,

            // VPN代理访问
            'vpn_query_match_detail' => $vpn_query_match,
            // 借款人手机疑似风险群体
            'user_ph_danger_match_detail' => $user_ph_danger_match,
            // 借款人身份证疑似风险群体
            'user_id_danger_match_detail' => $user_id_danger_match,
            // 借款人卡号疑似风险群体
            'user_card_danger_match_detail' => $user_card_danger_match,
            // 借款人设备疑似风险群体
            'user_device_danger_match_detail' => $user_device_danger_match,
        ];
        return $data;
    }
    private function getRuleDetails(&$rules_info,$rule_names){
        $detail = '';
        foreach ($rule_names as $rule_name) {
            if (isset($rules_info[$rule_name])) {
                $detail = $rules_info[$rule_name];
                break;
            }
        }
        return $detail;
    }
    /**
     * 是否命中规则
     * @param str $rules
     * @param str $names
     * @return   0  | 1
     */
    private function detailAnalysis(&$rule_names, $hit_rules) {
        $is_search = 0;
        foreach ($hit_rules as $hit_rule) {
            if (in_array($hit_rule, $rule_names)) {
                $is_search = 1;
                break;
            }
        }
        return $is_search;
    }
    private function detailMultiPhone(&$rules) {
        $phone_rules = [
            1=> "3个月内手机在多个平台进行借款",
            5 => "3个月内手机在5个平台进行借款",
            6 => "3个月内手机在6个平台进行借款",
            7 => "3个月内手机在7个平台进行借款",
            8 => "3个月内手机在8个平台进行借款",
            9 => "3个月内手机在9个平台进行借款",
            10 => "3个月内手机在10个平台进行借款",
            11 => "3个月内手机在11个平台进行借款",
            12 => "3个月内手机在12个平台进行借款",
            13 => "3个月内手机在13个平台进行借款",
            14 => "3个月内手机在14个平台进行借款",
            15 => "3个月内手机在15个平台进行借款",
            16 => "3个月内手机在16个平台进行借款",
            17 => "3个月内手机在17个平台进行借款",
            18 => "3个月内手机在18个平台进行借款",
            19 => "3个月内手机在19个平台进行借款",
            20 => "3个月内手机在多于20个平台进行借款",
        ];
        $rulekv = ArrayHelper::map($rules, 'name', 'num');
        $num = 0;
        foreach ($phone_rules as $rule_num => $rule_name) {
            if (isset($rulekv[$rule_name])) {
                $num = $rulekv[$rule_name];
                if(!$num){
                    $num = $rule_num;
                }
                break;
            }
        }
        return $num;
    }
    private function detailMultiIdcard(&$rules) {
        $idcard_rules = [
            1 => "3个月内身份证在多个平台进行借款",
            5 => "3个月内身份证在5个平台进行借款",
            6 => "3个月内身份证在6个平台进行借款",
            7 => "3个月内身份证在7个平台进行借款",
            8 => "3个月内身份证在8个平台进行借款",
            9 => "3个月内身份证在9个平台进行借款",
            10 => "3个月内身份证在10个平台进行借款",
            11 => "3个月内身份证在11个平台进行借款",
            12 => "3个月内身份证在12个平台进行借款",
            13 => "3个月内身份证在13个平台进行借款",
            14 => "3个月内身份证在14个平台进行借款",
            15 => "3个月内身份证在15个平台进行借款",
            16 => "3个月内身份证在16个平台进行借款",
            17 => "3个月内身份证在17个平台进行借款",
            18 => "3个月内身份证在18个平台进行借款",
            19 => "3个月内身份证在19个平台进行借款",
            20 => "3个月内身份证在多于20个平台进行借款",
        ];
        $rulekv = ArrayHelper::map($rules, 'name', 'num');
        $num = 0;
        foreach ($idcard_rules as $rule_num => $rule_name) {
            if (isset($rulekv[$rule_name])) {
                $num = $rulekv[$rule_name];
                if(!$num){
                    $num = $rule_num;
                }
                break;
            }
        }
        return $num;
    }
    // 解析逾期数据
    private function overAnalysis(&$over_rules)
    {
        if (!is_array($over_rules) || empty($over_rules)) {
            return null;
        }
        $oph_fm_one_m = $this->getOverValue($over_rules, [
            "一月内手机号信贷逾期统计",
        ]);
        $oph_fm_two_m = $this->getOverValue($over_rules, [
            "二月内手机号信贷逾期统计",
        ]);
        $oph_fm_three_m = $this->getOverValue($over_rules, [
            "三月内手机号信贷逾期统计",
        ]);
        $oph_fm_six_m = $this->getOverValue($over_rules, [
            "六月内手机号信贷逾期统计",
        ]);
        $oph_fm_one_y = $this->getOverValue($over_rules, [
            "十二月内手机号信贷逾期统计",
        ]);
        $oph_fm_three_m_plat = $this->getOverValue($over_rules, [
            "三月内手机号信贷逾期平台统计",
        ]);
        $oid_fm_one_m = $this->getOverValue($over_rules, [
            "一月内身份证号信贷逾期统计",
        ]);
        $oid_fm_two_m = $this->getOverValue($over_rules, [
            "二月内身份证号信贷逾期统计",
        ]);
        $oid_fm_three_m = $this->getOverValue($over_rules, [
            "三月内身份证号信贷逾期统计",
        ]);
        $oid_fm_six_m = $this->getOverValue($over_rules, [
            "六月内身份证号信贷逾期统计",
        ]);
        $oid_fm_one_y = $this->getOverValue($over_rules, [
            "十二月内身份证号信贷逾期统计",
        ]);
        $oid_fm_three_m_plat = $this->getOverValue($over_rules, [
            "三月内身份证号信贷逾期平台统计",
        ]);

        $data =  [
            'oph_fm_one_m' => $oph_fm_one_m,
            'oph_fm_two_m' => $oph_fm_two_m,
            'oph_fm_three_m' => $oph_fm_three_m,
            'oph_fm_six_m' => $oph_fm_six_m,
            'oph_fm_one_y' => $oph_fm_one_y,
            'oph_fm_three_m_plat' => $oph_fm_three_m_plat,
            'oid_fm_one_m' => $oid_fm_one_m,
            'oid_fm_two_m' => $oid_fm_two_m,
            'oid_fm_three_m' => $oid_fm_three_m,
            'oid_fm_six_m' => $oid_fm_six_m,
            'oid_fm_one_y' => $oid_fm_one_y,
            'oid_fm_three_m_plat' => $oid_fm_three_m_plat,
        ];
        return $data;
    }
    // 获取逾期数据
    private function getOverValue(&$over_rules,$hit_rules)
    {
        $is_search = 0;
        foreach ($over_rules as $value) {
            foreach ($hit_rules as $hit_rule) {
                if ($hit_rule == $value['name']) {
                    $is_search = $value['num'];
                    break;
                }
            }
        }
        return $is_search;
    }

    private function myrulesAnalysis(&$myrules)
    {
        if (!is_array($myrules) || empty($myrules)) {
            return null;
        }
        $rule_names = ArrayHelper::getColumn($myrules,"name");
        // 黑名单:身份证
        $bid_fm_sx = $this->detailAnalysis($rule_names, [
            "注册身份证号码命中失信证据库",
            "借款身份证命中全局失信证据库",
        ]);
        $bid_fm_court_sx = $this->detailAnalysis($rule_names, [
            "注册身份证命中法院失信证据库",
            "借款人身份证命中法院失信证据库",
        ]);
        $bid_fm_court_enforce = $this->detailAnalysis($rule_names, [
            "注册身份证命中法院执行证据库",
            "借款人身份证命中法院执行证据库",
        ]);
        $bid_fm_lost = $this->detailAnalysis($rule_names, [
            "注册身份证命中失联证据库",
            "借款人身份证命中失联证据库",
        ]);

        // 黑名单:手机号
        $bph_fm_fack = $this->detailAnalysis($rule_names, [
            "注册手机命中虚假号码证据库",
            "借款手机号命中虚假手机号码证据库",
        ]);
        $bph_fm_small = $this->detailAnalysis($rule_names, [
            "注册手机命中通信小号证据库",
            "借款手机号命中通信小号证据库",
        ]);
        $bph_fm_sx = $this->detailAnalysis($rule_names, [
            "注册手机命中失信证据库",
            "借款手机号命中全局失信证据库",
        ]);

        // 多投
        $mid_fm = $this->detailMultiIdcard($myrules);
        $mph_fm = $this->detailMultiPhone($myrules);
        //7天，一个月，三个月身份证手机号逾期数据
        $seven_d = $this->getMulti($myrules,'7天内申请人在多个平台进行借款的数量统计');
        $one_m = $this->getMulti($myrules,'1个月内申请人在多个平台进行借款的数量统计');
        $three_m = $this->getMulti($myrules,'3个月内申请人在多个平台进行借款的数量统计');

        //3个月内申请人在多个平台被放款_不包含本合作方
        $three_m_multi_remit = $this->detailAnalysisNum($myrules, [
            "3个月内申请人在多个平台被放款_不包含本合作方",
        ]);
        // 身份证姓名借款人手机号组合模糊证据库
        $ph_id_user_diff = $this->detailAnalysis($rule_names, [
            "身份证姓名借款人手机号组合模糊证据库",
        ]);
        // IP位置与手机归属地匹配
        $ip_ph_land_match = $this->detailAnalysis($rule_names, [
            "IP位置与手机归属地匹配",
        ]);
        // IP地理位置与身份证归属地匹配
        $ip_id_land_match = $this->detailAnalysis($rule_names, [
            "IP地理位置与身份证归属地匹配",
        ]);
        // 手机地理位置与身份证归属地匹配
        $ph_id_land_match = $this->detailAnalysis($rule_names, [
            "手机地理位置与身份证归属地匹配",
        ]);
        // 属性位置和位置匹配
        $attr_land_match = $this->detailAnalysis($rule_names, [
            "属性位置和位置匹配",
        ]);

        // 手机号关注名单
        $ph_care_list_match = $this->detailAnalysis($rule_names, [
            "关注名单",
        ]);
        // 身份证号关注名单
        $id_care_list_match = $this->detailAnalysis($rule_names, [
            "身份证号关注名单",
        ]);
        // VPN代理访问
        $vpn_query_match = $this->detailAnalysis($rule_names, [
            "VPN代理访问",
        ]);
        // 借款人手机疑似风险群体
        $user_ph_danger_match = $this->detailAnalysis($rule_names, [
            "借款人手机疑似风险群体",
        ]);
        // 借款人身份证疑似风险群体
        $user_id_danger_match = $this->detailAnalysis($rule_names, [
            "借款人身份证疑似风险群体",
        ]);
        // 借款人卡号疑似风险群体
        $user_card_danger_match = $this->detailAnalysis($rule_names, [
            "借款人卡号疑似风险群体",
        ]);
        // 借款人设备疑似风险群体
        $user_device_danger_match = $this->detailAnalysis($rule_names, [
            "借款人设备疑似风险群体",
        ]);
        //新多投
        $data = [
            // 黑名单
            "bph_fm_fack" => $bph_fm_fack,
            "bph_fm_small" => $bph_fm_small,
            "bph_fm_sx" => $bph_fm_sx,

            "bid_fm_sx" => $bid_fm_sx,
            "bid_fm_court_sx" => $bid_fm_court_sx,
            "bid_fm_court_enforce" => $bid_fm_court_enforce,
            "bid_fm_lost" => $bid_fm_lost,

            // 多投
            'mph_fm' => $mph_fm, // 手机多投
            'mid_fm' => $mid_fm, // 身份证多投
            //7天，一个月，三个月身份证手机号逾期数据
            //身份证多投
            'mid_fm_seven_d' => $seven_d['id'],
            'mid_fm_one_m' => $one_m['id'],
            'mid_fm_three_m' => $three_m['id'],
            //手机号多投
            'mph_fm_seven_d' => $seven_d['ph'],
            'mph_fm_one_m' => $one_m['ph'],
            'mph_fm_three_m' => $three_m['ph'],
            'three_m_multi_remit' => $three_m_multi_remit,

            // 身份证姓名借款人手机号组合模糊证据库
            'ph_id_user_diff' => $ph_id_user_diff,
            // IP位置与手机归属地匹配
            'ip_ph_land_match' => $ip_ph_land_match,
            // IP地理位置与身份证归属地匹配
            'ip_id_land_match' => $ip_id_land_match,
            // 手机地理位置与身份证归属地匹配
            'ph_id_land_match' => $ph_id_land_match,
            // 属性位置和位置匹配
            'attr_land_match' => $attr_land_match,

            // 手机号关注名单
            'ph_care_list_match' => $ph_care_list_match,
            // 身份证号关注名单
            'id_care_list_match' => $id_care_list_match,

            // VPN代理访问
            'vpn_query_match' => $vpn_query_match,
            // 借款人手机疑似风险群体
            'user_ph_danger_match' => $user_ph_danger_match,
            // 借款人身份证疑似风险群体
            'user_id_danger_match' => $user_id_danger_match,
            // 借款人卡号疑似风险群体
            'user_card_danger_match' => $user_card_danger_match,
            // 借款人设备疑似风险群体
            'user_device_danger_match' => $user_device_danger_match,
        ];
        return $data;
    }

    private function getMyrules(&$rule)
    {
        $myrules = [];
        $multi_rules = [
            '7天内申请人在多个平台进行借款的数量统计',
            '1个月内申请人在多个平台进行借款的数量统计',
            '3个月内申请人在多个平台进行借款的数量统计',
        ];
        $num_rules = [
            '借款人身份证个数',
            '借款人手机个数',
        ];
        $name = ArrayHelper::getValue($rule, "规则名称", '');
        if (in_array($name, $multi_rules)) {
            $multi_info = ArrayHelper::getValue($rule, "规则详情.0", '');
            foreach ($multi_info as $k => $v) {
                if (in_array($k,$num_rules)) {
                    $num[$k] = $v;
                }
            }
            try {
                if ($name == '7天内申请人在多个平台进行借款的数量统计') {
                    self::$seven_percent_class = self::$oXsSplitApi->getPercentClass($multi_info,7);

                }

                if ($name == '1个月内申请人在多个平台进行借款的数量统计') {
                    self::$one_mouth_percent_class = self::$oXsSplitApi->getPercentClass($multi_info,30);
                }
            } catch (\Exception $e) {
                Logger::dayLog("split/fmdown","analysis failed ", $e->getMessage(),json_decode($multi_info));
            }

        } else {
            $num = ArrayHelper::getValue($rule, "规则详情.0.个数", 0);
        }
        $myrules = [
            'name' => $name,
            'num' => $num,
            'detail' => json_encode(ArrayHelper::getValue($rule, "规则详情.0", ''),JSON_UNESCAPED_UNICODE),
        ];
        return $myrules;
    }

    private function getMulti(&$myrules,$type)
    {
        $rulekv = ArrayHelper::map($myrules, 'name', 'num');
        $id = 0;
        $phone = 0;
        foreach ($rulekv as $k => $v) {
            if ($type == $k) {
                $id = ArrayHelper::getValue($v, "借款人身份证个数", 0);
                $phone = ArrayHelper::getValue($v, "借款人手机个数", 0);
            }
        }

        $is_search = [
            'id'=>$id,
            'ph'=>$phone,
        ];
        return $is_search;
    }
    /**
     * 命中规则个数
     * @param str $rules
     * @param str $names
     * @return   0  | 1
     */
    private function detailAnalysisNum(&$rules, $hit_rules) {
        $num = 0;
        foreach ($rules as $rule) {
            foreach ($hit_rules as $hit_rule) {
                if ($hit_rule == $rule['name']) {
                    $num = ArrayHelper::getValue($rule, "num", 0);
                }
            }
        }
        return $num;
    }
}
