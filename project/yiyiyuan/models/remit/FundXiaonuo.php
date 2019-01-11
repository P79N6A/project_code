<?php

/**
 * 小诺出款
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/24
 * Time: 16:38
 */

namespace app\models\remit;

use app\commonapi\apiInterface\Xiaonuo;
use app\commonapi\Logger;
use app\models\news\Address;
use app\models\news\Card_bin;
use app\models\news\Favorite_contacts;
use app\models\news\Money_limit;
use app\models\news\User_bank;
use app\models\news\User_extend;
use app\models\news\User_loan;
use app\models\news\User_remit_list;
use Yii;

class FundXiaonuo implements CapitalInterface{

    public function pay($oRemit) {
        $hitresult = $this->ownRule($oRemit);
        if ($hitresult) {
            return ['status' => 'FAIL', 'res_code' => 'hitruleerror', 'res_msg' => '出款总额超限'];
        }
        //1.提交到小诺
        $send_return_data = $this->sendXiaonuo($oRemit);
        $error_code = $this->errorCode();
        //2.结果处理
        if ($send_return_data['res_code'] == '0000') {
            $status = 'DOREMIT';
        } else if (in_array($send_return_data['res_code'], $error_code)) {
            $status = 'FAIL';
        } else {
            $status = NULL;
        }
        return ['status' => $status, 'res_code' => $send_return_data['res_code'], 'res_msg' => $send_return_data['res_msg']];
    }

    /**
     * 每笔规则限制
     * @return bool true:触犯规则 false:未触犯规则
     */
    private function ownRule($oRemit) {
        $oRemitList = new User_remit_list();
        $oMoneyLimit = new Money_limit;
        $start_time = date('Y-m-d 00:00:00');
        $end_time = date('Y-m-d 23:59:59');
        $MaxAmount = $oMoneyLimit->todayTimeMaxMoney(0, User_remit_list::FUND_XIAONUO);
        $amount = $oRemitList->pushMoney($start_time, $end_time, [User_remit_list::FUND_XIAONUO], 0, $type=2);
        if ($amount + $oRemit->real_amount > $MaxAmount) {
            return true;
        }
        return FALSE;
    }

    public function hitRule() {
        $time = date('Y-m-d H:i:s');
        $date = date('Y-m-d');
        $oMoneyLimit = new Money_limit;
        $oRemitList = new User_remit_list;

        //1：周六周日不推单
        if (date("w") == 6 || date("w") == 0) {
            Logger::dayLog("fundrule/xn", "周六日不推单");
            return true;
        }
        //2：推单时间和金额限额
        if ($time > $date . ' 16:00:00') {//当天设置的开始时间之前或者16点之后不推单
            Logger::dayLog("fundrule/xn", "每天16点后不推单");
            return true;
        }
        $todaySuccessAmount = $oRemitList->todaySuccessMoney(0, User_remit_list::FUND_XIAONUO,$type=2);
        $todayMaxAmount = $oMoneyLimit->todayTimeMaxMoney(0, User_remit_list::FUND_XIAONUO);
        if (empty($todayMaxAmount)) {
            Logger::dayLog("fundrule/xn", $todayMaxAmount, "没有符合出款的时间设置");
            return true;
        }
        if ((bccomp(floatval($todaySuccessAmount), floatval($todayMaxAmount), 2) == 1)) {
            Logger::dayLog("fundrule/xn", $todaySuccessAmount, $todayMaxAmount, "当日通道出款金额超限");
            return true;
        }

        return false;
    }

    /**
     * 提交到小诺
     * @param $oRemit
     * @return array
     */
    private function sendXiaonuo($oRemit) {
        //用户信息
        $user = $oRemit->user;

        //银行卡信息
        $bank = User_bank::findOne($oRemit->loan->bank_id);

        //计算出款金额
        $amount_default = $oRemit->real_amount;

        $amount = number_format($amount_default, 2, '.', '');
        $loan_days = $oRemit->loan->days;

        $format_xiaonuo_data = $this->formatXiaonuo($oRemit->order_id, $user, $bank, $amount, $loan_days);
        if ($format_xiaonuo_data['res_code'] != '0000') {
            return $format_xiaonuo_data;
        }
        $apihttp = new Xiaonuo();
        $ret_result = $apihttp->outXiaonuo($format_xiaonuo_data['res_msg']);
        //$ret_result = ['res_code'=>0000,'res_msg'=>['bidNum'=>"Y20170929030621ID279", 'client_id'=>'Y20170929022706ID276']];
        return $ret_result;
    }

    /**
     * 格式请求的数据
     * @param $order_id
     * @param $user
     * @param $bank
     * @param $amount
     * @param $loan_days
     * @return array
     */
    private function formatXiaonuo($order_id, $user, $bank, $amount, $loan_days) {
        $coser_pay_data = [
            "bidNum" => $order_id, //进件编号
            "loanPeriod" => $loan_days, //期数
            "loanAmount" => $amount, //借款金额（元）
            "loanPurpose" => 1, //借款用途（1生活消费，2教育消费，3家庭医疗，4日常消费，5其他消费，6货物采买，7店铺运营）
            "loanPurposeDesc" => "生活消费", //借款用途描述
            //"proId" => "", //产品id
            "isRepeatLoan" => 2, //是否复借
            "applyTime" => time(), //申请时间
        ];
        //1.获取user_extend信息
        $user_extend = User_extend::find()->where(['user_id' => $user->user_id])->one();
        if (empty($user_extend->home_address) ||
                empty($user_extend->company_address) ||
                empty($user_extend->telephone) ||
                empty($user_extend->marriage) ||
                empty($user_extend->reg_ip)
        ) {
            return ['res_code' => 'invaild_error', 'res_msg' => '用户user_extend缺失'];
        } else {
            $coser_pay_data["liveAddrDetail"] = $user_extend->home_address; //居住地址（现住址）
            $coser_pay_data["company"] = $user_extend->company_address; //工作单位名称
            $coser_pay_data["companyPhone"] = $user_extend->telephone; //单位电话（区号-电话号-分机号）
            $coser_pay_data["marryType"] = $user_extend->marriage; //婚姻状况（1未婚，2已婚，3离异，4丧偶）
            $coser_pay_data["loanIp"] = $user_extend->reg_ip; //设备ip地址
        }
        //2.联系人
        $favorite_contacts = Favorite_contacts::find()->where(['user_id' => $user->user_id])->one();
        if (empty($favorite_contacts->contacts_name) ||
                empty($favorite_contacts->relation_common) ||
                empty($favorite_contacts->mobile)
        ) {
            return ['res_code' => 'invaild_error', 'res_msg' => '用户favorite_contacts缺失'];
        } else {
            $coser_pay_data["emergencyContactName1"] = $favorite_contacts->contacts_name; //紧急联系人1姓名
            $coser_pay_data["emergencyContactRelation1"] = $favorite_contacts->relation_common; //紧急联系人1关系
            $coser_pay_data["emergencyContactPhone1"] = $favorite_contacts->mobile; //紧急联系人1手机号
        }
        //3.用户信息
        if (empty($user->password->score) ||
                empty($user->password->device_tokens) ||
                empty($user->password->iden_address) ||
                empty($user->identity) ||
                empty($user->realname) ||
                empty($user->mobile)
        ) {
            return ['res_code' => 'invaild_error', 'res_msg' => '用户信息缺失'];
        } else {
            $coser_pay_data["faceRecognition"] = $user->password->score; //人脸识别比对结果
            $coser_pay_data["equipmentNum"] = $user->password->device_tokens; //设备号
            $coser_pay_data["hukouAddrDetail"] = $user->password->iden_address; //户籍地址
            $coser_pay_data["idNumber"] = $user->identity; //身份证号
            $coser_pay_data["name"] = $user->realname; //借款人姓名
            $coser_pay_data["tel"] = $user->mobile; //借款人手机号
            //性别
            $sex = substr($user->identity, -2, 1) % 2 ? '2' : '1';
            $coser_pay_data["sex"] = $sex;  //性别 1男 2女
        }
        //4.gps
        $address_info = Address::find()->where(['user_id' => $user->user_id])->one();
        if (empty($address_info->latitude) ||
                empty($address_info->longitude) ||
                empty($address_info->address)
        ) {
            $addressId = rand(100000,999999);
            $address_info = Address::findOne($addressId);
            if(empty($address_info)){
                return ['res_code' => 'invaild_error', 'res_msg' => '用户address gps缺失'];
            }
        }
        $gpsInfo = ['latitude' => $address_info->latitude, 'longitude' => $address_info->longitude, 'address' => $address_info->address];
        $coser_pay_data["gpsInfo"] = json_encode($gpsInfo); //gps定位
        //5.银行卡
        if (empty($bank->card) ||
                empty($bank->bank_name) ||
                empty($bank->bank_mobile)
        ) {
            return ['res_code' => 'invaild_error', 'res_msg' => '用户银行卡信息缺失'];
        } else {
            $card_bin_object = new Card_bin();
            $card_bin_info = $card_bin_object->getCardBinByCard($bank->card);
            $coser_pay_data["bankCard"] = $bank->card; //银行卡号
            $coser_pay_data['bank_addr'] = empty($card_bin_info) ? "" : $card_bin_info['bank_abbr'];
            $coser_pay_data["bankName"] = empty($card_bin_info) ? $bank->bank_name : $card_bin_info['bank_name']; //银行开户行名称
            $coser_pay_data["bankMobile"] = $bank->bank_mobile; //银行预留手机号
        }
        $coser_pay_data["callbackurl"] = Yii::$app->params['remit_repay'];
        //6.是否是复贷
        $user_loan = User_loan::find()->where(['user_id' => $user->user_id, 'status' => 8])->count();
        if ($user_loan > 0) {
            $coser_pay_data["isRepeatLoan"] = 1; //是否复借
        }
        return ['res_code' => '0000', 'res_msg' => $coser_pay_data];
    }

    /**
     * 名确错误
     * @return array
     */
    private function errorCode() {
        return [
            'invaild_error', //字段为空
            'hitruleerror', //出款规则限制
            '104001', // 判断必填字段的
            '104002', // '请求超频',
            '104003', // '单日出款总额超限',
            '104004', //'小诺出款总额超限
            '104005', // '节假日不接受推送用户',
            '104006', //推送用户不在有效时间范围内
            '104007', // '出款金额需大于0',
            '104008', //'周末不接受推送用户',
            '104009', //'银行不再所授范围内'
        ];
    }

    public function isSupport($oLoan)
    {
        $time = date('Y-m-d H:i:s');
        $date = date('Y-m-d');
        
        $loan = $oLoan->loan;
        if (!$loan){
            return false;
        }

        if(!empty($oLoan->remit)){
            $fundIds = array_map(function($record) { 
                return $record->attributes['fund']; 
            },$oLoan->remit);
            if (in_array(CapitalInterface::XIAONUO,$fundIds)){
                return false;
            }
        }
        //周六周日不推单
        if (date("w") == 6 || date("w") == 0) {
            return false;
        }
        if ($loan->amount != $loan->withdraw_fee * 10) {
            return false;
        }
        if ($loan->days != 28 ) {
            return false;
        }
        if ($loan->is_calculation == 0) {
            return false;
        }
        // if ($loan->amount > 3000 || $loan->amount <1000 ) {
        //     return false;
        // }
        //推单时间
        if ($time > $date . ' 16:00:00') {//当天设置的开始时间之前或者16点之后不推单
            return false;
        }
        return true;

    }
    public function getFails()
    {
        // TODO: Implement getFails() method.
    }
}
