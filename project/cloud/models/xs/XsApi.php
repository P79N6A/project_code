<?php

namespace app\models\xs;

use app\common\Common;
use app\common\Apihttp;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\models\yyy\YiLoanAddress;
use app\models\yyy\YiAddress;
use app\models\rc\RcBaiduApi;

/**
 * 统一对外开放接口
 */
class XsApi {

    private $loanAddress;
    private $address;
    private static $type;
    public function __construct()
    {
        $this->address = new  YiAddress();
        $this->loanAddress = new YiLoanAddress();
    }
    /**
     * 注册事件
     */
    public function runReg($post_data) {
        //1. 注册事件数据录入
        $oBasic = (new XsApiSetBasic)->setBasic('reg', $post_data);
        if (!$oBasic) {
            return $this->error("20101", "数据保存出错");
        }
        //2. 判断是否有同盾信息
        $oRun = new XsApiReturn;
        $res = $oRun->getReg($oBasic);
        if (!$res) {
            return $this->error("20102", "计算结果出错");
        }
        if (!isset($res['fm']) || empty($res['fm'])) {
            $res = $this->getRegFrau($post_data,$res);
        }
        if (!isset($res['bd']) || empty($res['bd'])) {
            $res['bd'] = $this->getBaiduRisk($post_data,$res);
        }
        if (isset($res['res_code']) && $res['res_code'] != '0') {
            return $this->error($res['res_code'],$res['res_data']);
        }
        $res = $this->decSelf($res);
        return $this->success($res);
    }
    /**
     * 借款事件
     */
    public function runLoan($post_data) {
        //1. 借款事件数据录入
        $oBasic = (new XsApiSetBasic)->setBasic('loan', $post_data);
        if (!$oBasic) {
            return $this->error("20201", "数据保存出错");
        }

        //2. 借款事件运算结果
        self::$type = ArrayHelper::getValue($post_data,'type',1);
        $oRun = new XsApiReturn;
        $res = $oRun->getLoan($oBasic,self::$type);
        if (!$res) {
            return $this->error("20203", "计算结果出错");
        }

        if (!isset($res['fm']) || empty($res['fm'])) {
            $res = $this->getLoanFrau($post_data, $res);
        }
        if (!isset($res['bd']) || empty($res['bd'])) {
            $res['bd'] = $this->getBaiduRisk($post_data,$res);
        }
        if (isset($res['res_code']) && $res['res_code'] != '0') {
            return $this->error($res['res_code'],$res['res_data']);
        }
        $res = $this->decSelf($res);
        return $this->success($res);

    }
    /**
     * 需减去自身的变量
     * @return data
     */
    private function decSelf(&$data){
        $decs = [
            //关系减自身
            'ip_devices',
            'device_ips',
            'ip_users',
            'device_users',

            // 多投减自身
            // "mph_y",
            // "mph_fm",
            // "mph_other",
            // "mph_br",
            // "mid_y",
            // "mid_fm",
            // "mid_other",
            // "mid_br ",

            // 高频借款减自身
            'loan_num_1',
            'loan_num_7',

            // 当月同一设备借款用户数限制减自身
            'device_loan_month',
        ];
        foreach ($decs as $key_dec) {
            if( isset($data[$key_dec]) ){
                $data[$key_dec] = $data[$key_dec] > 0 ? $data[$key_dec] -1 : 0;
            }
        }
        return $data;
    }
    /**
     * 目前仅限于一亿元黑名单录入
     */
    public function setBlack($phone, $idcard) {
        //1. 身份证
        $result = false;
        if ($idcard) {
            $data = [
                "idcard" => $idcard,
                "bid_y" => 1,
            ];
            $oBlackIdcard = new XsBlackIdcard;
            $result = $oBlackIdcard->setBlack($data);
            if (!$result) {
                return false;
            }
        }

        //2. 手机号录入
        if ($phone) {
            $data = [
                "phone" => $phone,
                "bph_y" => 1,
            ];
            $oBlackPhone = new XsBlackPhone;
            $result = $oBlackPhone->setBlack($data);
            if (!$result) {
                return false;
            }
        }

        return $result;
    }
    /**
     * 目前仅限于一亿元黑名单取消
     */
    public function unSetBlack($phone, $idcard) {
        //1. 身份证
        $result = false;
        if ($idcard) {
            $data = [
                "idcard" => $idcard,
                "bid_y" => 0,
            ];
            $oBlackIdcard = new XsBlackIdcard;
            $result = $oBlackIdcard->unSetBlack($data);
        }

        //2. 手机号录入
        if ($phone) {
            $data = [
                "phone" => $phone,
                "bph_y" => 0,
            ];
            $oBlackPhone = new XsBlackPhone;
            $result = $oBlackPhone->unSetBlack($data);
        }

        return true;
    }
    /**
     * 同盾数据录入
     */
    public function setFM($post_data) {
        //1. 保存同盾数据
        $model = new XsApiSetFM();
        $res = $model->setFM($post_data,self::$type);

        if (!$res) {
            return $this->error("20201", "数据保存出错");
        }

        //2 参数
        $event = $model->oFM->event;
        $basic_id = $model->oFM->basic_id;
        if (!$basic_id) {
            // 仅保存成功, 但不计算规则
            return $this->success([]);
        }

        //3 计算规则
        $oRun = new XsApiReturn;
        $res = $oRun->get($basic_id);
        if (!$res) {
            return $this->error("20102", "计算结果出错");
        }
        // $res = $this->decSelf($res);
        return $this->success($res);
    }
    /**
     * 黑名单导入
     * 用于一亿元, 其它黑名单和百融
     */
    public function importBlack($post_data) {
        //1. 身份证
        $oYArray = new YArray;
        $result = false;
        if ($post_data['idcard']) {
            $data = $oYArray->getByKeys($post_data, [
                'idcard',
                'bid_y',
                'bid_other',
                'bid_br',
            ], 0);

            $oBlackIdcard = new XsBlackIdcard;
            $result = $oBlackIdcard->setBlack($data);
            if (!$result) {
                return false;
            }
        }

        //2. 手机号录入
        if ($post_data['phone']) {
            $data = $oYArray->getByKeys($post_data, [
                'phone',
                'bph_y',
                'bph_other',
                'bph_br',
            ], 0);
            $oBlackPhone = new XsBlackPhone;
            $result = $oBlackPhone->setBlack($data);
            if (!$result) {
                return false;
            }
        }

        return $result;
    }

    /**
     * 返回成功json
     * @param $res_data
     * @return json
     */
    private function success($res) {
        if (is_array($res)) {
            $res['res_code'] = '0';
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
            'res_code' => (string) $rsp_code,
            'res_data' => $res_data,
        ];
    }
    /**
     * 获取注册同盾信息
     * @param $res_code
     * @param $res_data
     * @return json
     */
    private function getRegFrau($data,$res)
    {   
        #  原调用 reg 同盾
        // $birth_year = $this->getBirthyear($data['idcard']);
        // $company_name = ArrayHelper::getValue($data, 'company_name','');
        // $params = array(
        //     'account_name' => isset($data['name']) ? $data['name'] : '',
        //     'mobile' => isset($data['phone']) ? $data['phone'] : '',
        //     'id_number' => isset($data['idcard']) ? $data['idcard'] : '',
        //     'organization' => $company_name,
        //     'ext_position' => '',
        //     'seq_id' => date('YmdHis') . $data['identity_id'],
        //     'ext_birth_year' => $birth_year,
        //     'token_id' => isset($data['token_id']) ? $data['token_id'] : '',
        //     'ip_address' => isset($data['ip']) ? $data['ip'] : '',
        //     'type' => 2,
        // );
        #  现调用 loan 同盾
        $token_id = ArrayHelper::getValue($data, 'token_id','');
        $birth_year = $this->getBirthyear($data['idcard']);
        $req_no = (new Common)->Order_no();
        $edu = ArrayHelper::getValue($data, 'edu','');
        $params = array(
            'account_name' => isset($data['name']) ? $data['name'] : '',
            'mobile' => isset($data['phone']) ? $data['phone'] : '',
            'id_number' => isset($data['idcard']) ? $data['idcard'] : '',
            'seq_id' => $req_no,
            'ip_address' => isset($data['ip']) ? $data['ip'] : '',
            'type' => 1,
            'token_id' => $token_id,
            'ext_school' => '',
            'ext_diploma' => $edu,
            'ext_start_year' => '',
            'card_number' => ArrayHelper::getValue($data, 'cardno', ''),
            'pay_amount' => isset($data['amount']) ? $data['amount'] : '',
            'event_occur_time' => date('Y-m-d H:i:s'),
            'ext_birth_year' => $birth_year,
            'xhh_apps'  =>ArrayHelper::getValue($data, 'xhh_apps', "web"),  //设备类型
            'black_box' =>ArrayHelper::getValue($data, 'black_box', ''),  //设备指纹
        );
        $basic_id = isset($res['basic_id']) ? $res['basic_id'] : 0;
        $fraudmetrix = new XsFmRetInfo();
        $result = $fraudmetrix->addFmInfo($data['identity_id'],$basic_id);
        if (!$result) {
            Logger::dayLog('result', '请求添加失败', $data,$res);
        }
        $api = new Apihttp();
        $result_company = $api->riskLoanValid($params);
        if (!empty($result_company)) {
            $result = $fraudmetrix->updateFmInfo($result_company, $data['identity_id'] ,$basic_id);
            if (!$result) {
                Logger::dayLog('result', '请求更新失败', $data,$result_company);
            }
        }
        if (isset($result_company->res_code) && $result_company->res_code != '0000' ) {
            $result = $this->object2array($result_company);
            return $result;
        }
        //没有同盾信息，上传同盾信息
        $res = self::chkFMinfo($result_company, $data, $res);
        return $res;
    }
    /**
     * 获取借款同盾信息
     * @param $res_code
     * @param $res_data
     * @return json
     */
    public function getLoanFrau($data, $res) {
        $token_id = ArrayHelper::getValue($data, 'token_id','');
        $birth_year = $this->getBirthyear($data['idcard']);
        $req_no = (new Common)->Order_no();
        $edu = ArrayHelper::getValue($data, 'edu','');
        $params = array(
            'account_name' => isset($data['name']) ? $data['name'] : '',
            'mobile' => isset($data['phone']) ? $data['phone'] : '',
            'id_number' => isset($data['idcard']) ? $data['idcard'] : '',
            'seq_id' => $req_no,
            'ip_address' => isset($data['ip']) ? $data['ip'] : '',
            'type' => 1,
            'token_id' => $token_id,
            'ext_school' => '',
            'ext_diploma' => $edu,
            'ext_start_year' => '',
            'card_number' => ArrayHelper::getValue($data, 'cardno', ''),
            'pay_amount' => isset($data['amount']) ? $data['amount'] : '',
            'event_occur_time' => date('Y-m-d H:i:s'),
            'ext_birth_year' => $birth_year,
            'xhh_apps'  =>ArrayHelper::getValue($data, 'xhh_apps', "web"),  //设备类型
            'black_box' =>ArrayHelper::getValue($data, 'black_box', ''),  //设备指纹
        );
        $loan_id = isset($data['loan_id']) ? $data['loan_id'] : 0;
        $basic_id = isset($res['basic_id']) ? $res['basic_id'] : 0;
        $fraudmetrix = new XsFmRetInfo();
        $result = $fraudmetrix->addFmInfo($data['identity_id'], $basic_id, $event='loan', $loan_id);
        if (!$result) {
            Logger::dayLog('result/addFmInfo', '请求添加失败', $data,$res,$fraudmetrix->errors);
        }
        $api = new Apihttp();
        $result_loan = $api->riskLoanValid($params);
        if (!empty($result_loan)) {
            $result = $fraudmetrix->updateFmInfo($result_loan, $data['identity_id'] ,$basic_id);
            if (!$result) {
                Logger::dayLog('result/updateFmInfo', '请求更新失败', $data,$result_loan,$fraudmetrix->errors);
            }                       
        }
        if (isset($result_loan->res_code) && $result_loan->res_code != '0000' ) {
            Logger::dayLog('result/riskLoanValid', '请求数据异常', $data,$result_loan);
            $result = $this->object2array($result_loan);
            return $result;
        }
        //没有同盾信息，上传同盾信息
        $res = self::chkFMinfo($result_loan, $data, $res);
        return $res;
    }

    public function getBirthyear($idcard)
    {
        return $birth_year = substr($idcard,6,4);
    }

    /**
     * 请求百度金融接口
     * @param  obj $aid         
     * @return []
     */
    private function getBaiduRisk($oBasic,$res)
    {
        //请求前本地记录 
        $baidurisk = new XsBaidurisk();
        $oBd = $baidurisk->saveData($oBasic,$res);
        if (!$oBd) {
            Logger::dayLog('result/getBaiduRisk', '百度请求记录失败', $oBasic,$baidurisk->errors);
        }
        //请求百度金融接口
        $params = [
            'name' => $oBasic['name'],
            'idcard' => $oBasic['idcard'],
            'phone' => $oBasic['phone'],
        ];
        $api = new Apihttp();
        $baidu_result = $api->BaiduRiskApi($params);
        //更新数据
        $res = $baidurisk->updateBdInfo($baidu_result);
        if (!$res) {
            Logger::dayLog('result/getBaiduRisk', '百度请求更新失败', $oBasic,$baidurisk->errors);
        }
        if (isset($baidu_result['retCode']) && $baidu_result['retCode'] !== 0  ) {
            Logger::dayLog('result/riskLoanValid', '请求数据异常', $params,$baidu_result);
        }
        return $baidu_result;
    }

    /**
     * 同盾数据接口
     */
    private function chkFMinfo($dbData, $user, $res) {
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
        unset($dbData->device_info->geoIp);
        $device_info = isset($dbData->device_info) ? $dbData->device_info : [];
        $array['device_info'] = ArrayHelper::toArray($device_info);
        $geoip_info = isset($dbData->geoip_info) ? $dbData->geoip_info : [];
        $array['geoip_info'] = ArrayHelper::toArray($geoip_info);
        $report = $array;

        $detail = isset($dbData->rules) ? $dbData->rules : '';
        $detail = ArrayHelper::toArray($detail);
        $data = [
            "basic_id" => isset($res['basic_id']) ? $res['basic_id'] : 0,
            "identity_id" => $user['identity_id'],
            "seq_id" => isset($dbData->seq_id) ? $dbData->seq_id : '',
            "phone" => $user['phone'],
            "idcard" => $user['idcard'],
            "create_time" => date('Y-m-d H:i:s'),
            'report' => $report,
            'detail' => $detail,
        ];
        $res = $this->setFM($data);
        Logger::errorLog(print_r($res, true), 'setfm');
        return $res;
    }

    public function object2array($object) 
    {   
        if (is_object($object)) {    
            foreach ($object as $key => $value) {       
                $array[$key] = $value;     
            }   
        } else {     
            $array = $object;   
        }   return $array; 
    } 
    //获取同盾数据
    public function getFraudmetrix($data)
    {
        $phone = ArrayHelper::getValue($data, 'mobile','');
        $idcard = ArrayHelper::getValue($data, 'identity','');
        $event = ArrayHelper::getValue($data, 'event','loan');
        $datetime = ArrayHelper::getValue($data, 'datetime', date('Y-m-d H:i:s', strtotime('-7 day')));
        //1, 查询某段时间内本地同盾数据
        $xsApiReturn = new XsApiReturn();
        $fm = $xsApiReturn->getFmInfo($phone,$idcard,$event,$datetime);
        if (empty($fm)) {
            //标准化参数
            $params = $this->normalParams($data);
            $fm = $this->getLoanFrau($params,[]);
        }
        return $fm;
    }

    private function normalParams($data)
    {
        $params = [
            'name' => isset($data['realname']) ? $data['realname'] : '',
            'phone' => isset($data['mobile']) ? $data['mobile'] : '',
            'idcard' => isset($data['identity']) ? $data['identity'] : '',
            'loan_id'=> isset($data['loan_id']) ? $data['loan_id'] : 0,
            'identity_id' => isset($data['user_id']) ? $data['user_id'] : 0,
            'amount' => isset($data['amount']) ? $data['amount'] : 0,
        ];
        return $params;
    }
    //请百度LBS接口
    public function getBaiduLbs($data)
    {
        $baidu_lbs = [];
        $baiduApi = new XsBaiduApi();
        //获取本次借款地址
        // $this_address = $this->getThisAddress($data);
        $this_address = $this->getThisAddressData($data);//@todo暂时
        if (empty($this_address)) {
            return $this->error("20301", "无本次借款地址");
        }
        $data = array_merge($data,$this_address);
        //获取上次借款地址
        // $last_address = $this->getLastAddress($data);
        $last_address = $this->getLastAddressData($data);//@todo暂时
        if (empty($last_address)) {
            $baidu_lbs = $baiduApi->queryBaiduLbs($data);
            return $this->success($baidu_lbs);
        }
        //获取两次借款距离
        $this_gps = [$this_address['longitude'],$this_address['latitude']];
        $last_gps = [$last_address['longitude'],$last_address['latitude']];
        $distance = $baiduApi->getDistance($this_gps,$last_gps);
        //根据两次借款请求距离判断是否调用LBS数据接口
        if ($distance <= 5) {//小于5KM则不请求LBS接口
            //查询用户本地LBS数据
            $baidu_lbs = $baiduApi->getBaiduLbsInfo($data);
        }
        //本地无LBS数据或两次借款距离大于5KM，请求LBS接口
        if (empty($baidu_lbs)) {
            $baidu_lbs = $baiduApi->queryBaiduLbs($data);
        }
        return $this->success($baidu_lbs);
    }

    //请求新百度LBS接口
    public function getNewBaiduLbs($data)
    {
        $baidu_lbs = [];
        $address_data = [
            'latitude' => $data['latitude'],
            'longitude' => $data['longtitude'],
            'fix_address' => $data['location'],
        ];
        $data = array_merge($data,$address_data);
        $oRcBaiduApi = new RcBaiduApi();
        $baidu_lbs = $oRcBaiduApi->queryBaiduLbs($data);
        return $this->success($baidu_lbs);
    }

    /**
     * [getLastAddress 获取本次借款地址]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    private  function getThisAddress($data)
    {
        $loanAddress = $this->loanAddress;
        $where = [
            'loan_no' => $data['loan_no'],
            'user_id' => $data['user_id'],
        ];
        $loan_address = $loanAddress->getLoanAddress($where);
        if (empty($loan_address)) {
            Logger::dayLog('address/loan_address', '无本次借款地址', $data);
            return [];
        }
        $this_address = $loan_address->address;
        if (empty($this_address)) {
            Logger::dayLog('address/this_address', '无本次借款地址', $data);
            return [];
        }
        $address_data = [
            'gps_id' => $this_address['id'],
            'latitude' => $this_address['latitude'],
            'longitude' => $this_address['longitude'],
            // 'home_city' => $this_address['address'],
            // 'home_address' => $this_address['address'],
            // 'company_city' => $loan_address['address'],
            // 'company_address' => $loan_address['address'],
            // 'bus_shop_city' => $loan_address['address'],
            // 'bus_shop_address' => $loan_address['address'],
            'fix_address' => $this_address['address'],
        ];
        return $address_data;
    }
    /**
     * [getLastAddress 获取上次借款地址]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    private  function getLastAddress($data)
    {
        $loanAddress = $this->loanAddress;
        $where = ['and',
            ['!=','loan_no',$data['loan_no']],
            ['user_id' => $data['user_id']],
        ];
        $loan_address = $loanAddress->getLoanAddress($where);
        if (empty($loan_address)) {
            return [];
        }
        $last_address = $loan_address->address;
        if (empty($last_address)) {
            return [];
        }
        $address_data = [
            'gps_id' => $last_address['id'],
            'latitude' => $last_address['latitude'],
            'longitude' => $last_address['longitude'],
            'fix_address' => $last_address['address'],
        ];
        return $address_data;
    }

    /**
     * 暂时方案
     * [getThisAddressData 获取本次借款地址]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    private  function getThisAddressData($data)
    {
        $loanAddress = $this->address;
        $where = [
            'user_id' => $data['user_id'],
        ];
        $loan_address = $loanAddress->getAddressInfo($where);
        if (empty($loan_address)) {
            Logger::dayLog('address/loan_address', '无本次借款地址', $data);
            return [];
        }
        $address_data = [
            'gps_id' => $loan_address['id'],
            'latitude' => $loan_address['latitude'],
            'longitude' => $loan_address['longitude'],
            'home_city' => $loan_address['address'],
            'home_address' => $loan_address['address'],
            'company_city' => $loan_address['address'],
            'company_address' => $loan_address['address'],
            'bus_shop_city' => $loan_address['address'],
            'bus_shop_address' => $loan_address['address'],
            'fix_address' => $loan_address['address'],
        ];
        return $address_data;
    }
    /**
     * 暂时方案
     * [getLastAddress 获取上次借款地址]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    private  function getLastAddressData($data)
    {
        //查询上次成功查询的地址
        $where = [
                'user_id' => $data['user_id'],
                'retCode' => 0,
            ];
        $loan_address = (new XsBaidulbs)->getBaiduLbsData($where);
        if (empty($loan_address)) {
            return [];
        }
        $last_address = $loan_address->address;
        if (empty($last_address)) {
            return [];
        }
        $address_data = [
            'gps_id' => $last_address['id'],
            'latitude' => $last_address['latitude'],
            'longitude' => $last_address['longitude'],
            'fix_address' => $last_address['address'],
        ];
        return $address_data;
    }

}
