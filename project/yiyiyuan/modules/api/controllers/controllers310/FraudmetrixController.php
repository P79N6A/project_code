<?php
namespace app\modules\api\controllers\controllers310;

use app\commonapi\Apibaidu;
use app\commonapi\Logger;
use app\models\news\Address;
use app\models\news\AddressLoan;
use app\models\news\User;
use app\models\news\User_loan;
use app\models\news\White_list;
use app\models\news\No_repeat;
use app\modules\api\common\ApiController;
use app\commonapi\Apihttp;
use Yii;

/**
 * 借款调用同盾接口
 */
class FraudmetrixController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');
        $amount = Yii::$app->request->post('amount');
        $days = Yii::$app->request->post('days');
        $coupon_id = Yii::$app->request->post('coupon_id');
        $coupon_amount = Yii::$app->request->post('coupon_amount');
        $source = Yii::$app->request->post('source');
        $address = Yii::$app->request->post('address');
        $gps = Yii::$app->request->post('_gps');
        //区分担保和信用
        $business_type = empty(Yii::$app->request->post('business_type')) ? 1 : Yii::$app->request->post('business_type');

        $source = !empty($source) ? intval($source) : 2;
        $desc = '个人或家庭消费';
        if (empty($version) || empty($user_id) || empty($amount) || empty($days)) {
            exit($this->returnBack('99994'));
        }
        $userModel = new User();
        $user = $userModel->getUserinfoByUserId($user_id);
        //判断7-14产品中是否有进行中的借款
        if (!empty($user->identity)) {
            $apiHttp = new Apihttp();
            $canLoan = $apiHttp->havingLoan(['identity' => $user->identity]);
            if (!$canLoan) {
                exit($this->returnBack('99990'));
            }
        }
        if (empty($user)) {
            //@TODO 将else提出  ok
            exit($this->returnBack('10001'));
        }
        if ($user->status == 5) {
            exit($this->returnBack('10023'));
        }

        //用户信用
        $creditResult = (new Apihttp())->getUserCredit(['mobile' => $user->mobile]);
        //1:未测评;2已测评不可借;3:评测中;4:已测评未购买;5:已测评已购买;6:已过期;7：存在未支付的白条
        if (!empty($creditResult['rsp_code']) && $creditResult['rsp_code'] === '0000' && !empty($creditResult['user_credit_status'])) {
            if (in_array($creditResult['user_credit_status'], [3, 4, 7, 8])) {
                $this->reback('0000', 2, 3);
            }
            //评测驳回
            if($creditResult['user_credit_status'] == 2 && !empty($creditResult['credit_invalid_time'])){
                $borrowing = (new User_loan())->getBorrowingByTime($user->user_id,$creditResult['credit_invalid_time']);
                if(!$borrowing){
                    $this->reback('0000', 2, 4);
                }
            }
            if ($creditResult['user_credit_status'] == 5) {
                if($creditResult['order_amount'] != $amount){
                    $this->reback('0000', 2, 3);
                }
                $this->reback('0000', 1);
            }
        }

        $norepet = (new No_repeat())->norepeat($user_id, $type = 1);
        if (!$norepet) {
            exit($this->returnBack('99991'));
        }
        $loanModel = new User_loan();
        //@TODO 调用统一方法 ok
        $loan_info = (new User_loan())->getHaveinLoan($user->user_id);

        if ($loan_info > 0) {
            $array['status'] = 2;
            $array['sta_msg'] = '您存在未完成的借款';
            exit($this->returnBack('0000', $array));
        }
        $create_time = date('Y-m-d H:i:s');
        $loan_no_keys = $user->user_id . "_loan_no";
        $loan_no = Yii::$app->redis->get($loan_no_keys);
        if (empty($loan_no)) {
            $suffix = $user->user_id . rand(100000, 999999);
            $loan_no = date("YmdHis") . $suffix;
            Yii::$app->redis->setex($loan_no_keys, 43200, $loan_no);
            $this->addAddress($user_id, $source, $loan_no, $gps, $address);
        } else {
            $this->reback('0000', 1);
        }
        $whiteModel = new White_list();
        //@TODO 使用提出的loanrule  注意返回值
        $xindiao = \app\commonapi\Keywords::xindiao();
        if ($whiteModel->isWhiteList($user->user_id) || (!empty($xindiao) && in_array($user->mobile, $xindiao))) {
            $result = $loanModel->getRule($user, $source, $amount, $days, $desc, $loan_no, $business_type);
            $this->reback('0000', 1);
        }
        $result = $loanModel->getRule($user, $source, $amount, $days, $desc, $loan_no, $business_type);
        $dayratestr = (new User_loan())->fee;
        $with_fee = (new User_loan())->with_fee;

        \app\commonapi\Logger::dayLog('rule', $user->user_id, 'return', $loan_no, $result);
        if ($result == 1) {//驳回
            $userLoanModel = new User_loan();
            $result = $userLoanModel->_addRejectLoan($user, $loan_no, $amount, $days, $desc, 3, 0, $coupon_id, $coupon_amount, $source, 0, $business_type, $dayratestr, $with_fee);
            if ($result) {
                Yii::$app->redis->del($loan_no_keys);
            }
            $this->reback('0000', 2);
        } elseif ($result == 2) {//拉黑
            $user->setBlack();
            if ($result) {
                Yii::$app->redis->del($loan_no_keys);
            }
            $this->reback('0000', 2, 2);
        } else {
            $this->reback('0000', 1);
        }
    }

    private function reback($code, $status = 1, $sta = 1)
    {
        $array['status'] = $status;
        switch ($sta) {
            case 1:
                $array['sta_msg'] = $status == 2 ? '借款审核中' : '';
                break;
            case 2:
                $array['sta_msg'] = $status == 2 ? '暂不符合借款要求' : '';
                break;
            case 3:
                $array['sta_msg'] = $status == 2 ? '请10分钟后再发起借款' : '';
                break;
            case 4:
                $array['sta_msg'] = $status == 2 ? '请24小时后重试' : '';
                break;
        }
        exit($this->returnBack($code, $array));
    }

    /**
     * 判断关键词是否在输入的语句中
     */
    private function strstring($keyword, $reject = array())
    {
        $mark = 0;
        foreach ($reject as $val) {
            if (strstr($keyword, $val)) {
                $mark = 1;
                break;
            }
        }
        return $mark;
    }

    private function addAddress($userId, $source, $loan_no, $gps, $address)
    {
        if (empty($gps) || $gps == '0.00,0.00') {
            Logger::dayLog('api/fraudmetrix', 'gps为空', $gps, $address, $userId, $source, $loan_no);
            return false;
        }
        $array = explode(',', $gps);
        $longitude = !empty($array[0]) ? $array[0] : '';
        $latitude = !empty($array[1]) ? $array[1] : '';
        if (empty($latitude) || empty($longitude)) {
            Logger::dayLog('api/fraudmetrix', 'latitude或longitude为空', $gps, $userId, $source, $loan_no);
            return false;
        }
        if (empty($address) || $address == 'empty') {
            $address = '';
            $addressResult = (new Apibaidu())->sendReverse($latitude . ',' . $longitude);
            if (!empty($addressResult)) {
                $address = $addressResult['formatted_address'];
            }
            if (empty($address)) {
                Logger::dayLog('api/fraudmetrix', 'baidu接口获取地址失败', $gps, $userId, $source, $loan_no, $addressResult);
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
            Logger::dayLog('api/fraudmetrix', 'GPS记录失败', $userId, $latitude, $longitude, $address, $come_from, $addressResult);
            return false;
        }
        $addressLoanModel = new AddressLoan();
        $result = $addressLoanModel->getRecordByLoanNo($loan_no);
        if (!empty($result)) {
            return false;
        }
        $data = [
            'loan_no' => $loan_no,
            'address_id' => $addressModel->id,
            'user_id' => $userId
        ];
        $addressLoanResult = $addressLoanModel->addRecord($data);
        if (empty($addressLoanResult)) {
            Logger::dayLog('api/fraudmetrix', '借款gps记录表操作失败', $data, $addressLoanResult);
            return false;
        }
        return true;
    }
}
