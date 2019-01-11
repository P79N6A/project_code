<?php

namespace app\models\dev;

use app\commonapi\Apihttp;
use app\commonapi\Common;
use app\commonapi\Keywords;
use app\commonapi\Logger;
use app\models\dev\Address;
use app\models\xs\XsApi;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "account".
 *
 * @property string $id
 * @property string $mobile
 * @property string $password
 * @property string $school
 * @property integer $edu_levels
 * @property string $entrance_time
 * @property string $account_name
 * @property string $identity
 * @property string $create_time
 */
class Fraudmetrix_return_info extends ActiveRecord {

    public static $rule = [
        'loan' => [
            'start_time' => '1:00',
            'end_time' => '6:00',
            'age' => 31,
            'is_multi' => 15,
            'loan_num_1' => 2,
            'loan_num_7' => 14,
            'loan_num_7' => 14,
            'device_loan_users' => 4,
        ],
        'reg' => [
            'age' => 40,
//            'area'=>[15 => "内蒙古", 54 => "西藏", 65 => "新疆"],//暂时放到User model的getIdentityValid方法里面限制
            'device_users' => 1,
            'ip_users' => 1,
        ]
    ];

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_fraudmetrix_return_info';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
        ];
    }

    /**
     * 根据同盾返回信息存数据
     * @param type $Fraudetrixinfo
     * @param type $user_id
     * @param type $loan_id
     * @return Fraudmetrix_return_info
     */
    public static function CreateFraudmetrix($Fraudetrixinfo, $user_id, $loan_id = '') {
        $fraudmetrix = new Fraudmetrix_return_info();
        $fraudmetrix->user_id = $user_id;
        $fraudmetrix->loan_id = $loan_id;
        $fraudmetrix->seq_id = isset($Fraudetrixinfo->seq_id) ? $Fraudetrixinfo->seq_id : '';
        $fraudmetrix->success = isset($Fraudetrixinfo->success) ? $Fraudetrixinfo->success : '';
        $fraudmetrix->reason_code = isset($Fraudetrixinfo->reason_code) ? $Fraudetrixinfo->reason_code : '';
        $fraudmetrix->final_decision = isset($Fraudetrixinfo->final_decision) ? $Fraudetrixinfo->final_decision : '';
        $fraudmetrix->final_score = isset($Fraudetrixinfo->finalScore) ? $Fraudetrixinfo->finalScore : '';
        $fraudmetrix->hit_rules = ''; //isset($Fraudetrixinfo->hit_rules) ? serialize($Fraudetrixinfo->hit_rules) : '';
        $fraudmetrix->risk_type = isset($Fraudetrixinfo->risk_type) ? $Fraudetrixinfo->risk_type : '';
        $fraudmetrix->device_info = ''; //isset($Fraudetrixinfo->device_info) ? serialize($Fraudetrixinfo->device_info) : '';
        $fraudmetrix->geoip_info = ''; //isset($Fraudetrixinfo->geoip_info) ? serialize($Fraudetrixinfo->geoip_info) : '';
        $fraudmetrix->policy_set_name = isset($Fraudetrixinfo->policy_set_name) ? $Fraudetrixinfo->policy_set_name : '';
        $fraudmetrix->policy_set = ''; //isset($Fraudetrixinfo->policy_set) ? serialize($Fraudetrixinfo->policy_set) : '';
        $fraudmetrix->attribution = ''; //isset($Fraudetrixinfo->attribution) ? serialize($fraudmetrix->attribution) : '';
        $fraudmetrix->rules = ''; //isset($Fraudetrixinfo->rules) ? serialize($Fraudetrixinfo->rules) : '';
        $fraudmetrix->create_time = date('Y-m-d H:i:s');
        $fraudmetrix->save();
        return $fraudmetrix;
    }

    /**
     * 根据开放平台返回数据存数据
     * @param type $user
     * @param type $condition
     */
    public function CreateFraudmetrixByOpen($user_id, $condition) {
        if (empty($condition) || empty($user_id)) {
            return false;
        }
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $this->create_time = date('Y-m-d H:i:s');
        $this->save();
    }

    /**
     * 决策 注册、借款事件
     */
    public static function RulesEngine($user, $from, $type = 'reg', $amount = 0, $days = 0, $desc = '', $coupon_id = 0, $coupon_amount = 0, $uuid = '') {
        $api = new XsApi;
        $ips = explode(',', Common::get_client_ip());
        $user_extend = $user->extend;
        $address = Address::find()->where(['user_id' => $user->user_id])->orderBy('create_time desc')->one();
        $position = Keywords::getPosition();
        $company_area = Areas::getProCityAreaName($user_extend->company_area);
        //公共参数
        $post_data = [
            'identity_id' => $user->user_id,
            'idcard' => $user->identity,
            'phone' => $user->mobile,
            'name' => $user->realname,
            'ip' => !empty($ips) ? $ips[0] : NULL,
            'device' => !empty($user_extend->uuid) ? $user_extend->uuid : '',
            'source' => $from == 1 ? 'weixin' : ($from == 2 ? 'ios' : 'android'),
            'company_name' => $user_extend->company,
            'company_position' => $position[$user_extend->position][0],
            'company_phone' => $user_extend->telephone,
            'company_address' => $company_area . $user_extend->company_address,
            'edu' => $user_extend->getEdu(),
            'latitude' => !empty($address) ? $address->latitude : '',
            'longtitude' => !empty($address) ? $address->longitude : '',
            'accuracy' => '',
            'speed' => '',
            'location' => !empty($address) ? $address->address : '',
        ];
        //借款事件
        if ($type == 'loan') {
            $post_data['loan_id'] = 0;
            $post_data['amount'] = $amount;
            $post_data['loan_days'] = $days;
            $post_data['cardno'] = '';
            $post_data['reason'] = $desc;
            $post_data['loan_time'] = date('Y-m-d H:i:s');
        }
        if ($type == 'reg') {
            $res = $api->runReg($post_data);
            Logger::errorLog(print_r($res, true), 'fraureg');
        } else {
            $res = $api->runLoan($post_data);
            Logger::errorLog(print_r($res, true), 'frauloan', 'weixin');
        }
        return $res;

//        
//        if ($type == 'loan') {
//            $result = static::LoanLimit($user, $user_extend, $res, $amount, $days, $desc, $coupon_id, $coupon_amount, $ips[0], $from);
//            return $result;
//        } else {
//            $result = static::RegLimit($user, $user_extend, $res, $ips[0]);
//        }
//        return $result;
    }

    public static function getLoanFrau($user, $res, $amount, $days, $desc, $ip, $loan_no) {
        $user_extend = $user->extend;
        $token_id = $user->getTokenId();
        $params = array(
            'account_name' => $user->realname,
            'mobile' => $user->mobile,
            'id_number' => $user->identity,
            'seq_id' => $loan_no,
            'ip_address' => $ip,
            'type' => 1,
            'token_id' => $token_id,
            'ext_school' => '',
            'ext_diploma' => $user_extend->getEdu(),
            'ext_start_year' => '',
            'card_number' => '',
            'pay_amount' => $amount,
            'event_occur_time' => date('Y-m-d H:i:s'),
            'ext_birth_year' => $user->birth_year,
        );
        $api = new Apihttp();
        $result_loan = $api->riskLoanValid($params);
        $fraudmetrix = new Fraudmetrix_return_info();
        $fraudmetrix->CreateFraudmetrix($result_loan, $user->user_id, $loan_no);
        //没有同盾信息，上传同盾信息
        $res = self::setFM($result_loan, $user, $res);
        return $res;
    }

    public function getRegFrau($user, $res) {
        $user_extend = $user->extend;
        $ips = explode(',', Common::get_client_ip());
        $ip = !empty($ips) ? $ips[0] : '';
        $token_id = $user->getTokenId();
        $params = array(
            'account_name' => $user->realname,
            'mobile' => $user->mobile,
            'id_number' => $user->identity,
            'organization' => $user_extend->company,
            'ext_position' => '',
            'seq_id' => date('YmdHis') . $user->user_id,
            'ext_birth_year' => $user->birth_year,
            'token_id' => $token_id,
            'ip_address' => $ip,
            'type' => 2,
        );
        $api = new Apihttp();
        $result_company = $api->riskLoanValid($params);
        $fraudmetrix = new Fraudmetrix_return_info();
        $fraudmetrix->CreateFraudmetrix($result_company, $user->user_id);
        //没有同盾信息，上传同盾信息
        $res = self::setFM($result_company, $user, $res);
        return $res;
    }

    /**
     * 根据同盾信息更改用户状态
     * @param type $user
     * @param type $final_score
     * @param type $final_result
     */
    public function setUserInfoByFrau($user, $final_score, $final_result) {
        if ($final_result == 'Reject' || $final_score >= 80) {
            $user->setBlack();
            $user->setUserinfo($user->user_id, ['final_score' => $final_score]);
            return 5; //拉黑
        } else {
            if (($final_score >= 60) && ($final_score < 80)) {
                $user->setUserinfo($user->user_id, ['final_score' => $final_score, 'status' => 4]);
                return 4; //驳回
            } else {
                $user->setUserinfo($user->user_id, ['final_score' => $final_score]);
                return 1;
            }
        }
    }

    /**
     * 同盾录入接口
     */
    private static function setFM($dbData, $user, $res) {
        $array['final_decision'] = isset($dbData->final_decision) ? $dbData->final_decision : '';
        $array['final_score'] = isset($dbData->finalScore) ? $dbData->finalScore : '';
        if (!empty($dbData->hit_rules)) {
            $array['hit_rules'] = ArrayHelper::toArray($dbData->hit_rules);
        } else {
            $array['hit_rules'] = '';
        }
        $array['policy_name'] = isset($dbData->policy_set_name) ? $dbData->policy_set_name : '';
        if (!empty($dbData->policy_set)) {
            $array['policy_set'] = ArrayHelper::toArray($dbData->policy_set);
        } else {
            $array['policy_set'] = '';
        }
        $array['policy_set_name'] = isset($dbData->policy_set_name) ? $dbData->policy_set_name : '';
        $array['risk_type'] = isset($dbData->risk_type) ? $dbData->risk_type : '';
        $array['seq_id'] = isset($dbData->seq_id) ? $dbData->seq_id : '';
        $array['spend_time'] = '';
        $array['success'] = isset($dbData->success) ? $dbData->success : '';
        $report = $array;

        $detail = isset($dbData->rules) ? $dbData->rules : '';
        $detail = ArrayHelper::toArray($detail);
        $data = [
            "basic_id" => isset($res['basic_id']) ? $res['basic_id'] : 0,
            "identity_id" => $user['user_id'],
            "seq_id" => isset($dbData->seq_id) ? $dbData->seq_id : '',
            "phone" => $user['mobile'],
            "idcard" => $user['identity'],
            "create_time" => date('Y-m-d H:i:s'),
            'report' => $report,
            'detail' => $detail,
        ];
        $oApi = new XsApi;
        $res = $oApi->setFM($data);
        Logger::errorLog(print_r($res, true), 'setfm');
        return $res;
    }

    public function saveRegGetFraudmetrix($user, $res) {
        $fraudmetrix = new Fraudmetrix_return_info(); //加判断
        if (isset($res['fm']) && !empty($res['fm'])) {
            $fraudmetrix->CreateFraudmetrixByOpen($user->user_id, [
                'seq_id' => $res['fm']['seq_id'],
                'final_decision' => $res['fm']['decision'],
                'final_score' => $res['fm']['score'],
            ]);
            return [];
        } else {
            $result_company = $fraudmetrix->getRegFrau($user, $res);
            return $result_company;
        }
    }

    public function saveLoanGetFraudmetrix($user, $res, $amount, $days, $desc, $loan_no) {
        $fraudmetrix = new Fraudmetrix_return_info();
        if (isset($res['fm']) && !empty($res['fm'])) {
            $fraudmetrix->CreateFraudmetrixByOpen($user->user_id, [
                'seq_id' => $res['fm']['seq_id'],
                'loan_id' => $loan_no,
                'final_decision' => $res['fm']['decision'],
                'final_score' => $res['fm']['score'],
            ]);
            return [];
        } else {
            $frau = Fraudmetrix_return_info::find()->where(['loan_id' => $loan_no])->one();
            if (!empty($frau)) {
                return [];
            } else {
                $ips = explode(',', Common::get_client_ip());
                $ip = !empty($ips) ? $ips[0] : '';
                $result_company = $fraudmetrix->getLoanFrau($user, $res, $amount, $days, $desc, $ip, $loan_no);
                return $result_company;
            }
        }
    }

    /**
     * 根据loan_no，更新为loan_id
     * @param type $loan_id
     * @param type $loan_no
     * @return boolean
     */
    public function setLoanId($loan_id, $loan_no) {
        if (empty($loan_id) || empty($loan_no)) {
            return FALSE;
        }
        $fraud = static::find()->where(['loan_id' => $loan_no])->one();
        if (!empty($fraud)) {
            return $fraud->updateRecord(['loan_id' => $loan_id]);
        }
        return FALSE;
    }

}
