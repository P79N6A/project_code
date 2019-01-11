<?php
namespace app\models\xs;
use app\commonapi\Fmdown;
use app\commonapi\Logger;

class XsApiSetFM {
    private $oYArray;
    public $oFM;
    public function __construct() {
        $this->oYArray = new YArray;
    }
    /**
     * 保存同盾纪录, 同时更新黑名单库和多投库
     * @param [type] $post_data [description]
     */
    public function setFM($post_data) {
        //1. 解析数据并检测是否存在
        $data = $this->request($post_data);

        //2. 报告内容获取
        $report = $data['report'];
        $detail = $data['detail'];
        unset($data['report'], $data['detail']);
        $oFmdown = new Fmdown();
        $ay_data = $oFmdown->runAnalysis($report, $detail);
        //$ay_data = $this -> testAnalysis();
        if (empty($ay_data)) {
            Logger::dayLog("xs", "api", "XsApiSetFM/setFM", "Fmdown/runAnalysis", "同盾报告解析失败", $post_data, $report, $detail);
            return false;
        }

        //3. 合并两个数组
        $fm_data = array_merge($data, $ay_data);
        //4. 保存黑名单数据
        $result = $this->saveBlackIdcard($fm_data);
        $result = $this->saveBlackPhone($fm_data);
        //5. 保存多投数据
        $result = $this->saveMultiIdcard($fm_data);
        $result = $this->saveMultiPhone($fm_data);
        //6. 保存逾期失信数据
        $result = $this->saveOverIdcard($fm_data);
        $result = $this->saveOverPhone($fm_data);
        //7. 保存同盾数据
        $oFM = new XsFraudmetrix;
        $result = $oFM->saveData($this->getFM($fm_data));
        if (!$result) {
            return false;
        }
        //8. 保存详情数据
        $fm_data['fid'] = $oFM->id;
        $oFmDetail = new XsFraudmetrixDetail;
        $result = $oFmDetail->saveData($fm_data);
        //9. 保存详情附属表数据
        $oFmDetailOther = new XsFraudmetrixDetailOther;
        $result = $oFmDetailOther->saveData($fm_data);
        //10. 保存同盾实时欺诈信息
        $oFmOntime = new XsFraudmetrixOntime;
        $result = $oFmOntime->saveData($fm_data);
        //11. 保存触发规则详情
        $result = $this->saveFmRuleDetail($fm_data);
        $this->oFM = $oFM;
        return true;
    }
    private function saveFmRuleDetail(&$rule_detail) {
        $data = $this->oYArray->getByKeys($rule_detail, [
            'fid',
            'oph_fm_one_m_detail' ,
            'oph_fm_two_m_detail',
            'oph_fm_three_m_detail',
            'oph_fm_six_m_detail',
            'oph_fm_one_y_detail',
            'oph_fm_three_m_plat_detail',
            'oid_fm_one_m_detail',
            'oid_fm_two_m_detail',
            'oid_fm_three_m_detail',
            'oid_fm_six_m_detail',
            'oid_fm_one_y_detail',
            'oid_fm_three_m_plat_detail',
            'bph_fm_fack_detail',
            'bph_fm_small_detail',
            'bph_fm_sx_detail',
            'bid_fm_sx_detail',
            'bid_fm_court_sx_detail',
            'bid_fm_court_enforce_detail',
            'bid_fm_lost_detail',
            'mph_fm_detail',
            'mid_fm_detail',
            'mid_fm_seven_d_detail',
            'mid_fm_one_m_detail',
            'mid_fm_three_m_detail',
            'mph_fm_seven_d_detail',
            'mph_fm_one_m_detail',
            'mph_fm_three_m_detail',
            'three_m_multi_remit_detail',
            'ph_id_user_diff_detail',
            'ip_ph_land_match_detail',
            'ip_id_land_match_detail',
            'ph_id_land_match_detail',
            'attr_land_match_detail',
            'ph_care_list_match_detail',
            'id_care_list_match_detail',
            'vpn_query_match_detail',
            'user_ph_danger_match_detail',
            'user_id_danger_match_detail',
            'user_card_danger_match_detail',
            'user_device_danger_match_detail',
        ], '');
        $oFmOntime = new XsFmRuleDetail;
        $result = $oFmOntime->saveData($data);
        return $result;
    }
    /**
     * 获取事件要求的请求参数
     */
    private function request(&$data) {
        $keys = [
            "basic_id",
            "seq_id",
            "identity_id",
            "phone",
            "idcard",
            'report',
            'detail',
            'create_time',
        ];
        $arr = $this->oYArray->getByKeys($data, $keys, '');
        $arr['basic_id'] = $arr['basic_id'] ? $arr['basic_id'] : 0;
        $arr['identity_id'] = (string)$arr['identity_id'];
        return $arr;
    }

    /**
     * 保存同盾数据
     */
    private function getFM($fm_data) {
        $data = $this->oYArray->getByKeys($fm_data, [
            'seq_id',
            'basic_id',
            'identity_id',
            'phone',
            'idcard',
            'event',
            'decision',
            'score',
            'create_time',
        ], '');

        // 同盾的黑名单与多投判断
        $data['is_black'] = $this->isFMBlack($fm_data);
        $data['is_multi'] = $this->isFMMulti($fm_data);
        return $data;
    }
    private function isFMBlack(&$data) {
        return $this->chkExists($data, [
            'bid_fm_sx',
            'bid_fm_court_sx',
            'bid_fm_court_enforce',
            'bid_fm_lost',

            'bph_fm_fack',
            'bph_fm_small',
            'bph_fm_sx',
        ]);
    }
    private function isFMMulti(&$data) {
        return $this->chkExists($data, ['mid_fm', 'mph_fm']);
    }
    /**
     * 检测是否存在键值
     * @param  [] $data
     * @param  [] $keys
     * @return bool
     */
    private function chkExists(&$data, $keys) {
        $num = 0;
        foreach ($keys as $key) {
            if (isset($data[$key]) && $data[$key] > 0) {
                $num = 1;
                break;
            }
        }
        return $num;
    }

    /**
     * 同盾身份证黑名单录入
     * @param  [] $black_data
     * @return bool
     */
    private function saveBlackIdcard($black_data) {
        $data = $this->oYArray->getByKeys($black_data, [
            'idcard',
            'bid_fm_sx',
            'bid_fm_court_sx',
            'bid_fm_court_enforce',
            'bid_fm_lost',
        ], '');
        $oBlackIdcard = new XsBlackIdcard;
        $result = $oBlackIdcard->setBlack($data);
        return $result;
    }

    /**
     * 同盾手机号黑名单录入
     * @param  [] $black_data
     * @return bool
     */
    private function saveBlackPhone($black_data) {
        $data = $this->oYArray->getByKeys($black_data, [
            'phone',
            'bph_fm_fack',
            'bph_fm_small',
            'bph_fm_sx',
        ], '');

        $oBlackPhone = new XsBlackPhone;
        $result = $oBlackPhone->setBlack($data);
        return $result;
    }
    /**
     * 同盾身份证黑名单录入
     * @param  [] $black_data
     * @return bool
     */
    private function saveMultiIdcard($multi_data) {
        $data = $this->oYArray->getByKeys($multi_data, [
            'idcard',
            'mid_fm',
            'mid_fm_seven_d',
            'mid_fm_one_m',
            'mid_fm_three_m',
        ], '');
        $oMultiIdcard = new XsMultiIdcard;
        $result = $oMultiIdcard->setMulti($data);
        return $result;
    }

    /**
     * 同盾手机号黑名单录入
     * @param  [] $multi_data
     * @return bool
     */
    private function saveMultiPhone($multi_data) {
        $data = $this->oYArray->getByKeys($multi_data, [
            'phone',
            'mph_fm',
            'mph_fm_seven_d',
            'mph_fm_one_m',
            'mph_fm_three_m',
        ], '');

        $oMultiPhone = new XsMultiPhone;
        $result = $oMultiPhone->setMulti($data);
        return $result;
    }

    /**
     * 同盾手机号逾期录入
     * @param  [] $over_data
     * @return bool
     */
    private function saveOverPhone($over_data) {
        $data = $this->oYArray->getByKeys($over_data, [
            'phone',
            'oph_fm_one_m',
            'oph_fm_two_m',
            'oph_fm_three_m',
            'oph_fm_six_m',
            'oph_fm_one_y',
            'oph_fm_three_m_plat',
        ], '');
        $overPhone = new XsFmOverPhone;
        $result = $overPhone->setOver($data);
        return $result;
    }

    private function saveOverIdcard($over_data) {
        $data = $this->oYArray->getByKeys($over_data, [
            'idcard',
            'oid_fm_one_m',
            'oid_fm_two_m',
            'oid_fm_three_m',
            'oid_fm_six_m',
            'oid_fm_one_y',
            'oid_fm_three_m_plat',
        ], '');
        $overIdcard = new XsFmOverIdcard;
        $result = $overIdcard->setOver($data);
        return $result;
    }
    private function testAnalysis() {
        return array(
            //   'seq_id' => '1459914689810637F30792D742449412',
            'event' => 'loan_web',
            'decision' => 'Reject',
            'score' => 70,
            'bph_fm_fack' => 0,
            'bph_fm_small' => 0,
            'bph_fm_sx' => 0,
            'bid_fm_sx' => 0,
            'bid_fm_court_sx' => 0,
            'bid_fm_court_enforce' => 0,
            'bid_fm_lost' => 0,
            'mph_fm' => 0,
            'mid_fm' => 0,
        );
    }
}
