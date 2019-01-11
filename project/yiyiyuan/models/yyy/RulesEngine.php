<?php

namespace app\models\yyy;

use app\commonapi\Common;
use app\commonapi\Keywords;
use app\models\dev\Address;
use app\models\dev\Areas;
use app\models\dev\Fraudmetrix_return_info;

class RulesEngine {

    private $ip;
    private $position;
    private $rule = [
        //2017-03-08上线(注意每次更改，程序预留变更记录)
        //2017-03-08当天更改阀值------loan_num_1(2-1)/loan_num_7(10-6)
        //2017-03-13当天更改阀值------loan_num_1(1-2)
        //2017-03-21当天更改阀值------loan_num_1(2-1)
        'loan' => [
            'start_time' => '1:00',
            'end_time' => '6:00',
            'age' => 50,
            'is_multi' => 35,
            'loan_num_1' => 1,
            'loan_num_7' => 6,
            'device_loan_month' => 2,
            'min_age' => 18,
        ],
        'reg' => [
            'age' => 40,
            'area' => ["内蒙古", "西藏", "新疆"],
            'device_users' => 1,
            'ip_users' => 999,
            'min_age' => 18,
        ]
    ];

    public function __construct() {
        $ips = explode(',', Common::get_client_ip());
        $this->ip = !empty($ips) ? $ips[0] : '';
        $position = Keywords::getPosition();
        $this->position = $position;
    }

    /**
     * 注册事件决策引擎组装数据
     */
    public function AssemblyDataByReg($user, $from) {
        $user_extend = $user->extend;
        $address = Address::find()->where(['user_id' => $user->user_id])->orderBy('create_time desc')->one();
        $position = $this->position;
        $company_area_code = !empty($user_extend) ? $user_extend->company_area : '110101';
        $company_area = Areas::getProCityAreaName($company_area_code);
        $user_extend->position = empty($user_extend->position) ? 1 : $user_extend->position;
        //公共参数
        $post_data = [
            'identity_id' => $user->user_id,
            'idcard' => $user->identity,
            'phone' => $user->mobile,
            'name' => $user->realname,
            'ip' => $this->ip,
            'device' => !empty($user_extend->uuid) ? $user_extend->uuid : '',
            'source' => $from,
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

        return $post_data;
    }

    /**
     * 注册事件决策引擎组装数据
     */
    public function AssemblyDataByLoan($user, $from, $amount = 0, $days = 0, $desc = '') {
        $user_extend = $user->extend;
        $position = $this->position;
        $address = Address::find()->where(['user_id' => $user->user_id])->orderBy('create_time desc')->one();
        $company_area = Areas::getProCityAreaName($user_extend->company_area);
        $user_extend->position = empty($user_extend->position) ? 1 : $user_extend->position;
        //公共参数
        $post_data = [
            'identity_id' => $user->user_id,
            'idcard' => $user->identity,
            'phone' => $user->mobile,
            'name' => $user->realname,
            'ip' => $this->ip,
            'device' => !empty($user_extend->uuid) ? $user_extend->uuid : '',
            'source' => $from,
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
            //关于借款信息
            'loan_id' => 0,
            'amount' => $amount,
            'loan_days' => $days,
            'cardno' => '',
            'reason' => $desc,
            'loan_time' => date('Y-m-d H:i:s')
        ];

        return $post_data;
    }

    /**
     * 
     * @param type $user
     * @param type $res
     * @return array
     */
    public function RegLimit($user, $res) {
        $limit = $this->rule;
        $rule = [];
        if (isset($res['age']) && ($res['age'] > $limit['reg']['age'] || $res['age'] < $limit['reg']['min_age'])) {//年龄限制   
            $rule['age_value'] = $res['age'];
//            return $this->error('2001', '注册决策年龄超限');
        }
        if (isset($res['province']) && in_array($res['province'], $limit['reg']['area'])) {
            $rule['area_value'] = $res['province'];
//            return $this->error('2002', '注册决策地区限制');
        }
        if (isset($res['device_users']) && $res['device_users'] >= $limit['reg']['device_users']) {//设备限制
            $rule['number_value'] = $res['device_users'];
//            return $this->error('2003', '注册决策设备超限');
        }
        if (isset($res['ip_users']) && $res['ip_users'] >= $limit['reg']['ip_users']) {//IP限制
            $rule['ip_value'] = $res['ip_users'];
//            return $this->error('2004', '注册决策IP超限');
        }
        if (isset($res['is_black']) && $res['is_black'] == 1) {//黑名单限制
            $rule['is_black'] = $res['is_black'];
//            return $this->error('2005', '注册决策黑名单限制');
        }
        return $rule;
    }

    /**
     * 借款限制条件
     * @param type $user
     * @param type $user_extend
     * @param type $res
     * @param type $amount
     * @param type $days
     * @param type $desc
     * @param type $coupon_id
     * @param type $coupon_amount
     * @param type $ip
     * @param type $from
     * @return array
     */
    public function LoanLimit($user, $res) {
        $limit = $this->rule;
        $now_time = date('H:i');
        $rule = [];
//        if ($now_time >= $limit['loan']['start_time'] && $now_time < $limit['loan']['end_time']) {
//            $start = date('i', strtotime($limit['loan']['start_time']));
//            $end = date('i', strtotime($limit['loan']['end_time']));
//            $rule['loan_time_end'] = $start;
//            $rule['loan_time_end'] = $end;
////            return $this->error('2006', '借款决策时间');
//        }
//            'age' => 35,
//            'is_multi' => 20,
//            'loan_num_1' => 2,
//            'loan_num_7' => 10,
//            'device_loan_users' => 2,
        if (isset($res['age']) && ($res['age'] > $limit['loan']['age']||$res['age']<$limit['loan']['min_age'])) {//年龄限制
            $rule['age_value'] = $res['age'];
//            return $this->error('2001', '借款决策年龄超限');
        }
        if (isset($res['is_black']) && $res['is_black'] == 1) {//黑名单限制
            $rule['is_black'] = $res['is_black'];
//            return $this->error('2005', '注册决策黑名单限制');
        }
        if ((isset($res['mph_fm']) && $res['mph_fm'] >= $limit['loan']['is_multi']) || (isset($res['mid_fm']) && $res['mid_fm'] >= $limit['loan']['is_multi'])) {//多头负债
            $rule['more_loan_value'] = max([$res['mph_fm'], $res['mid_fm']]);
//            return $this->error('2007', '注册决策多头负债限制');
        }
        if (isset($res['loan_num_1']) && $res['loan_num_1'] >= $limit['loan']['loan_num_1']) {
            $rule['one_more_loan_value'] = $res['loan_num_1'];
        }
        if (isset($res['loan_num_7']) && $res['loan_num_7'] >= $limit['loan']['loan_num_7']) {
            $rule['seven_more_loan_value'] = $res['loan_num_7'];
        }
//            return $this->error('2008', '注册决策高频借款限制');
        if (isset($res['device_loan_month']) && $res['device_loan_month'] >= $limit['loan']['device_loan_month']) {//设备限制
            $rule['one_number_account_value'] = $res['device_loan_month'];
//            return $this->error('2003', '注册决策设备限制');
        }
        return $rule;
    }

    /**
     * 返回成功json
     * @param $res_data
     * @return json
     */
    private function success($res) {
        if (is_array($res)) {
            $res['rsp_code'] = '0';
        } else {
            $res = [
                'res_code' => '0',
                'res_data' => $res,
            ];
        }
        return $res;
        //return json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 返回错误json
     * @param $res_code
     * @param $res_data
     * @return json
     */
    private function error($rsp_code, $res_data) {
        return [
            'rsp_code' => (string) $rsp_code,
            'res_data' => $res_data,
        ];
    }

}
