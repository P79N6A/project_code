<?php

namespace app\modules\api\controllers\controllers314;

use app\commonapi\Apibaidu;
use app\commonapi\Apidepository;
use app\commonapi\Apihttp;
use app\commonapi\Common;
use app\commonapi\ErrorCode;
use app\commonapi\Keywords;
use app\commonapi\Logger;
use app\models\news\Address;
use app\models\news\AddressLoan;
use app\models\news\Coupon_list;
use app\models\news\Coupon_use;
use app\models\news\Fraudmetrix_return_info;
use app\models\news\GoodsOrder;
use app\models\news\Insurance;
use app\models\news\No_repeat;
use app\models\news\Payaccount;
use app\models\news\Push_yxl;
use app\models\news\Term;
use app\models\news\User;
use app\models\news\User_bank;
use app\models\news\User_credit;
use app\models\news\User_label;
use app\models\news\User_loan;
use app\models\news\User_loan_extend;
use app\models\news\User_loan_flows;
use app\models\news\UserCreditList;
use app\models\news\White_list;
use app\models\service\GoodsService;
use app\models\service\StageService;
use app\modules\api\common\ApiController;
use Yii;

class UserloanController extends ApiController {

    public $enableCsrfValidation = FALSE;
    private $is_installment = FALSE;//true 是分期；false 不是分期

    public function actionIndex() {
        $version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');
        $amount = Yii::$app->request->post('amount');
        $days = Yii::$app->request->post('days');
        $gps = Yii::$app->request->post('_gps');
        $bank_id = Yii::$app->request->post('bank_id');
        $coupon_id = Yii::$app->request->post('coupon_id');
        $coupon_val = Yii::$app->request->post('coupon_val');
        $source = Yii::$app->request->post('source');
        $uuid = Yii::$app->request->post('uuid');
        $address = Yii::$app->request->post('address');
        $period = Yii::$app->request->post('term',1);
        $goods_id = '20171116151601';
        $desc = Yii::$app->request->post('desc', '个人或家庭消费');
        if (empty($version) || empty($user_id) || empty($amount) || empty($days) || empty($bank_id) || empty($period) || empty($goods_id) || empty($desc)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }

        $o_user = (new User())->getById($user_id);
        if (empty($o_user)) {
            exit($this->returnBack('10001'));
        }

        //监测评测
        $o_user_credit = (new User_credit())->checkCanCredit($o_user);
        if (empty($o_user_credit)) {
            exit($this->returnBack('10233'));
        }
        $this->is_installment = $o_user_credit->installment_result == 1 ? TRUE : FALSE;

        //检测存管
        $isCungan = (new Payaccount())->isCunguan($o_user->user_id);
        if (in_array(0, $isCungan)) {
            exit($this->returnBack('10210'));
        }

        //检测是否允许借款
        $loanCode = (new User_loan())->checkCanLoan($o_user);
        if ($loanCode != '0000') {
            exit($this->returnBack($loanCode));
        }

        //监测数据是否合法
        $o_user_bank = (new User_bank())->getById($bank_id);
        $o_coupon = (new Coupon_list())->getById($coupon_id);
        $field_code = (new User_loan())->checkLoanField($o_user, $o_user_credit, $o_user_bank, $o_coupon, $amount, $days, $period);
        if ($field_code != '0000') {
            exit($this->returnBack($field_code));
        }

        //@todo 放里面
        $business_type = 1;
        if ($o_user_credit->installment_result == 1) {
            $business_type = 5;
        }

        $loan_info = [
            'amount' => $amount,
            'days' => $days,
            'period' => $period,
            'desc' => $desc,
            'source' => $source,
            'business_type' => $business_type,
            'uuid' => $uuid,
        ];
        $transaction = Yii::$app->db->beginTransaction();
        $o_user_loan = (new User_loan());
        $loan_result = $o_user_loan->addUserLoanRecord($o_user, $o_user_credit, $o_user_bank, $o_coupon, $loan_info);
        if ($loan_result['rsp_code'] == '0000') {
            $transaction->commit();
            //添加GPS信息
            $this->addAddress($user_id, $source, $o_user_loan->loan_no, $gps, $address);
            //推送智荣钥匙使用状态
            (new Push_yxl())->saveUseAndSend($o_user, $o_user_loan, $o_user_credit);
            Yii::$app->redis->del($o_user->user_id . "_loan_no");
            $array['loan_id'] = $o_user_loan->loan_id;
            $array = $this->returnBack('0000', $array);
            echo $array;
            exit;
        } else {
            $transaction->rollBack();
            $array = $this->returnBack($loan_result['rsp_code']);
            echo $array;
            exit;
        }
    }

    /**
     * 添加GPS信息
     * @param $userObj  用户对象
     * @return string
     */
    private function addAddress($userId, $source, $loan_no, $gps, $address) {
        if (empty($gps) || $gps == '0.00,0.00') {
            Logger::dayLog('api/userloan', 'gps为空', $gps, $address, $userId, $source, $loan_no);
            return FALSE;
        }
        $array = explode(',', $gps);
        $longitude = !empty($array[0]) ? $array[0] : '';
        $latitude = !empty($array[1]) ? $array[1] : '';
        if (empty($latitude) || empty($longitude)) {
            Logger::dayLog('api/userloan', 'latitude或longitude为空', $gps, $userId, $source, $loan_no);
            return FALSE;
        }
        if (empty($address) || $address == 'empty') {
            $address = '';
            $addressResult = (new Apibaidu())->sendReverse($latitude . ',' . $longitude);
            if (!empty($addressResult)) {
                $address = $addressResult['formatted_address'];
            }
            if (empty($address)) {
                Logger::dayLog('api/userloan', 'baidu接口获取地址失败', $gps, $userId, $source, $loan_no, $addressResult);
            }
        }
        $come_from = $source == 4 ? 2 : 1;//1：ios  2：安卓
        $addressModel = new Address();
        $addressData = [
            'user_id' => $userId,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'address' => $address,
            'come_from' => $come_from,
        ];
        $addressResult = $addressModel->save_address($addressData);
        if (empty($addressResult)) {
            Logger::dayLog('api/userloan', 'GPS记录失败', $userId, $latitude, $longitude, $address, $come_from, $addressResult);
            return FALSE;
        }
        $addressLoanModel = new AddressLoan();
        $result = $addressLoanModel->getRecordByLoanNo($loan_no);
        if (!empty($result)) {
            return FALSE;
        }
        $data = [
            'loan_no' => $loan_no,
            'address_id' => $addressModel->id,
            'user_id' => $userId
        ];
        $addressLoanResult = $addressLoanModel->addRecord($data);
        if (empty($addressLoanResult)) {
            Logger::dayLog('api/userloan', '借款gps记录表操作失败', $data, $addressLoanResult);
            return FALSE;
        }
        return TRUE;
    }
}
