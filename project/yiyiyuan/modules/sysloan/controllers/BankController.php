<?php

namespace app\modules\sysloan\controllers;

use app\commonapi\Apihttp;
use app\commonapi\Common;
use app\models\news\Bankbill;
use app\models\news\User_bank;
use app\modules\sysloan\common\ApiController;

class BankController extends ApiController {

    public $enableCsrfValidation = false;

    /*
     * 获取银行卡信息
     */

    public function actionBanklist() {
        $required   = ['bank_id'];  //必传参数
        $httpParams = $this->post();  //获取参数
        $verify     = $this->BeforeVerify($required, $httpParams);

        /*         * *************记录访问日志beigin************* */
        $ip         = Common::get_client_ip();
        $result_log = Common::saveLog('BankController', 'actionBanklist', $ip, 'sysloan', $httpParams['bank_id']);
        /*         * *************记录访问日志end**************** */

        $bankInfo = (new User_bank)->getBankById($httpParams['bank_id']);
        $array    = $this->result('0000', $bankInfo);
        exit(json_encode($array));
    }

    private function result($code, $object) {
        $array = $this->errorreback($code);
        if (empty($object)) {
            return $array;
        }
        $array['bank_id']     = $object['id'];
        $array['bank_name']   = $object['bank_name'];
        $array['card_number'] = $object['card'];
        $array['bank_mobile'] = $object['bank_mobile'];
        $array['card_type']   = $object['type'];
        $array['card_status'] = $object['status'];
        $array['verify']      = $object['verify'];
        return $array;
    }

    //银行卡报告
    public function actionBankcode() {
        $required   = ['user_id'];  //必传参数
        $httpParams = $this->post();  //获取参数
        $verify     = $this->BeforeVerify($required, $httpParams);
        $user_id    = $httpParams['user_id'];
        /*         * *************记录访问日志beigin************* */
        $ip         = Common::get_client_ip();
        $result_log = Common::saveLog('BankController', 'actionBankcode', $ip, 'sysloan', $httpParams);
        /*         * *************记录访问日志end**************** */

        $bankbillModel = new Bankbill();
        $bankbill      = $bankbillModel->find()->where(["user_id" => $user_id, 'status' => 'FINISHED'])->orderBy('create_time desc')->all();
        if (empty($bankbill[0])) {
            $array = ['rsp_code' => '1001', 'rsp_msg' => '获取账单失败'];
            exit(json_encode($array));
        }
        $bankbill        = $bankbill[0];
        //信用卡账单地址
        $credit_url      = $bankbill->credit_url;
        //储蓄卡账单地址
        $deposit_url     = $bankbill->deposit_url;
        //小额信贷地址
        $loan_detail_url = $bankbill->loan_detail_url;

        if (!empty($credit_url)) {
            $apihttp    = new Apihttp;
            $credit_res = $apihttp->httpGet($credit_url);
            $credit     = json_decode($credit_res, true);
        } else {
            $credit = false;
        }
        if (!empty($deposit_url)) {
            $apihttp     = new Apihttp;
            $deposit_res = $apihttp->httpGet($deposit_url);
            $deposit     = json_decode($deposit_res, true);
        } else {
            $deposit = false;
        }
        if (!empty($loan_detail_url)) {
            $apihttp         = new Apihttp;
            $loan_detail_res = $apihttp->httpGet($loan_detail_url);
            $loan_detail     = json_decode($loan_detail_res, true);
        } else {
            $loan_detail = false;
        }
        //编码对照表
        $codeArr = [
            '00' => '0,500',
            '01' => '500,1000',
            '02' => '1000,1500',
            '03' => '1500,2000',
            '04' => '2000,2500',
            '05' => '2500,3000',
            '06' => '3000,3500',
            '07' => '3500,4000',
            '08' => '4000,4500',
            '09' => '4500,5000',
            '10' => '5000,5500',
            '11' => '5500,6000',
            '12' => '6000,6500',
            '13' => '6500,7000',
            '14' => '7000,7500',
            '15' => '7500,8000',
            '16' => '8000,8500',
            '17' => '8500,9000',
            '18' => '9000,9500',
            '19' => '9500,10000',
            '20' => '10000,15000',
            '21' => '15000,20000',
            '22' => '20000,25000',
            '23' => '25000,30000',
            '24' => '30000,35000',
            '25' => '35000,40000',
            '26' => '40000,45000',
            '27' => '45000,50000',
            '28' => '50000,55000',
            '29' => '55000,60000',
            '30' => '60000,65000',
            '31' => '65000,70000',
            '32' => '70000,75000',
            '33' => '75000,80000',
            '34' => '80000,85000',
            '35' => '85000,90000',
            '36' => '90000,95000',
            '37' => '95000,100000',
            '38' => '100000,200000',
            '39' => '200000,300000',
            '40' => '300000,400000',
            '41' => '400000,500000',
            '42' => '500000,600000',
            '43' => '600000,700000',
            '44' => '700000,800000',
            '45' => '800000,900000',
            '46' => '900000,1000000',
            '47' => '1000000,2000000',
            '48' => '2000000,3000000',
            '49' => '3000000,4000000',
            '50' => '4000000,5000000',
            '51' => '5000000,6000000',
            '52' => '6000000,7000000',
            '53' => '7000000,8000000',
            '54' => '8000000,9000000',
            '55' => '9000000,10000000',
            '56' => '10000000,10000000以上',
            '99' => '无',
        ];

        $array         = ['rsp_code' => '0000', 'rsp_msg' => '成功'];
        $array['list'] = [
            'credit'      => $credit,
            'deposit'     => $deposit,
            'loan_detail' => $loan_detail,
            'codeArr'     => $codeArr,
        ];
        exit(json_encode($array));
    }

}
