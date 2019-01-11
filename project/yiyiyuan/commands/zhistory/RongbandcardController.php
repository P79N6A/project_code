<?php
/**
 * 融360绑卡信息推并生成订单
 *
 * notify_status：
 *      1：初始 2：锁定  3：成功  4：不存在借款  5：四要素验证不过  6：记录失败  7：更新失败
 *      8：借款决策未通过  9：生成借款不成功  10：银行卡已经被其他用户绑定
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/3
 * Time: 13:55
 */
namespace app\commands;


use app\commonapi\Apihttp;
use app\commonapi\Common;
use app\commonapi\Http;
use app\commonapi\Logger;
use app\commonapi\RSA;
use app\models\news\Address;
use app\models\news\Card_bin;
use app\models\news\Fraudmetrix_return_info;
use app\models\news\Loan_repay;
use app\models\news\RongBank;
use app\models\news\RongLoan;
use app\models\news\User_bank;
use app\models\news\User_loan;
use app\models\news\User_loan_extend;
use app\models\news\White_list;
use app\models\news\YiLoanNotify;
use app\models\news\Loan_mapping;
use Yii;
use yii\console\Controller;
use yii\web\User;
set_time_limit(0);
ini_set('memory_limit', '-1');


class RongbandcardController extends Controller
{
    protected $interest = [7=>0.07, 14=>0.1]; //利息
    protected $day_rate = 0.0005; //日息
    protected $appId = 3300063;
    protected $priv = '-----BEGIN PRIVATE KEY-----
MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAKBbZ0OoOkZsTq3s
xpVbDvPfmFrSB5ENISAmSfxbYcdbOB/apNbRXKW+JiVj5Hv5Mz3DzlXUmM8Y7bbP
DpYjxsNDQrbd1DFVrcfFSs9JLB5zlD62fILKuSjZV89OKgdwg7GGqp8vcNZEuEgu
9ALuTWNCNQMDT4W8OQvDD5LaTrQZAgMBAAECgYAWLE1dF5fnQPaoKgNTh6HLqvFA
LaaKMgyQi3rTgDdG/6AFF5CPe6eZ628O4H8pfU3OjpKrX5g5mrLUAlF8BTpocYLY
Kpy9Oy2eGBI9ca9zaTup1aItGMiw9o4KnEzVb+KSy1lHsXY6SW1VigysotZunxYU
ZvC2KCCBnwcdXEUh2QJBANLXpycddBCY415mpgUqUy7txkGeMrjp8/FOLP1KbRkE
C8WjI54EX4AjXc2cSclIShAezMK8Na6F8jlTrGW7T7MCQQDCs6wtOXvm7d8ZiKU6
YHTcYMa6ecd7lTBLctwpc88XmOI1+z/TszVoVBVH6WqftP9GogGtwgHHHN/O+1af
5acDAkEAifbbRdkcDZA9l5QLpu2fKOImDOH7xswv+AJzpfqBkRD4swahU9EAvNRn
mRdfoPpQnGPLENIfPmgfrCt4b8k1yQJAGZjVgfyUtX+AXTMBxfL4aiCu/8US3MR4
XPL0zt5S059d3gryETr2QokLYzDku6poBTk3T0i6QxsgsW2JrevbUQJBAMAk32Z2
RfmVIeMl73fY0JRzkVv0uWqPShfP0qrIKNdkDXmUrImN2G4klkF8oD/4Aza+AGe2
ERnMnyFZOLfhqQU=
-----END PRIVATE KEY-----';
    public function actionIndex()
    {
        $limit = 500;
        $rong_bank = RongBank::find()->where(['source'=>8, 'notify_status'=> 1])->limit($limit)->all();
        if (!empty($rong_bank)){
            foreach($rong_bank as $value){
                //判断是否生成运营商数据
                $operator_stat = $this->getOperatorData($value['order_no']);
                if ($operator_stat && $value->notify_status == 1) {
                    $value->updateRongBank(2); //锁定状态
                    $this->logicalProcessing($value);
                }
            }
        }
    }
    /**
     * 查看用户是否存在运营商数据
     * @param $r_loan_id
     * @return bool
     */
    private function getOperatorData($r_loan_id)
    {
        $rong_loan = RongLoan::find()->where(['r_loan_id'=>$r_loan_id,  'source'=>8]) -> one();
        if (!empty($rong_loan)){
            $uesr_info = \app\models\news\User::find()->where(['mobile'=>$rong_loan['mobile']])->one();
            if (!empty($uesr_info->juxinli)){
                return true;
            }
        }
        return false;
    }


    private function logicalProcessing($data)
    {
        //获取用户信息
        $user_info = $this->getRongLoan($data->order_no);
        if ($user_info['code'] != 200){
            $data->updateRongBank(4); //不存在订单
            return $this->httpRong($data->order_no, 2);
        }
        $bank_stat = $this->saveBankInfo($user_info['data'], $data);
        if ($bank_stat != 200){
            return $this->httpRong($data->order_no, 2);
        }
        //修改rong_bank的notify_status
        $ret = $data->updateRongBank(3);
        //借款决策
        $chkLoanRes = $this->doChkLoan($user_info['data'], $data);
        if ($chkLoanRes != 200) {
            $data->updateRongBank(8);
            $this->httpRong($data->order_no, 1);
            return $this->httpRongResult($data->order_no);
        }else{
            //生成借款
            $loanRes = $this->doLoan($user_info['data'], $data);
            if ($loanRes != 200) {
                $data->updateRongBank(9);
                $this->httpRong($data->order_no, 1);
                return $this->httpRongResult($data->order_no);
            }
        }
        return $this->httpRong($data->order_no, 1);
    }
    /**
     * 生成借款
     * @param $userInfo
     * @param $postData
     * @return array
     * @throws \yii\db\Exception
     */
    private function doLoan($userInfo, $postData)
    {
        //获取借款信息
        $rong_loan_info = RongLoan::find()->where(['r_loan_id'=>$postData['order_no'], 'source'=>8])->one();
        if (empty($rong_loan_info)){
            return 4012; //不存在订单
        }
        //读取银行卡信息
        $where = [
            "bank_mobile" => $postData['user_mobile'],
            "card" => $postData['bank_card'],
            'status' => 1,
        ];
        $bank = (new User_bank())->find()->where($where)->one();
        if (!$bank) {
            return 4013; //银行卡不存在
        }
        $bank_id = $bank->id;
        $transaction = Yii::$app->db->beginTransaction();
        //添加用户借款信息
        $amount = $rong_loan_info['application_amount'];
        $days = $rong_loan_info->application_term;
        $day_rate = $this->day_rate;
        $loan_no_keys = $userInfo->user_id . "_loan_no";
        $loan_no = $this->getRedis($loan_no_keys);
        $condition = array(
            'user_id' => $userInfo->user_id,
            'loan_no' => $loan_no,
            'real_amount' => $amount,
            'amount' => $amount,
            'credit_amount' => 0,
            'recharge_amount' => 0,
            'current_amount' => $amount,
            'days' => $days,
            'type' => 2,
            'status' => 5,
            'interest_fee' => round($amount * $day_rate * $days, 2),
            'withdraw_fee' => round($amount * $this->interest[$days], 2),
            'desc' => '其他',
            'bank_id' => $bank_id,
            'withdraw_time' => date('Y-m-d H:i:s', time()),
            'is_calculation' => 1,
            'source' => 8,
        );
        if (empty($loan_no)) {
            $condition['status'] = 3;
        }
        $ret = (new User_loan())->addUserLoan($condition);
        if (!$ret){
            return 4014; //生成借款失败
        }
        //记录生成借款日志
        $loan_no_keys = $userInfo->user_id . "_loan_no";
        //如果redis存在是就返回200
        $loan_no = $this->getRedis($loan_no_keys);
        if ($loan_no){
            $loan_id_string = $userInfo->user_id.'  '.$loan_no;
            Logger::errorLog($loan_id_string."\n", 'loan_id_getrule', 'r360');
        }

        //新同盾记录表的loan_no为loan_id
        $fraudmetrix = new Fraudmetrix_return_info();
        $fraudmetrix->setLoanId($ret, $loan_no);
        $whiteModel = new White_list();
        if (!$whiteModel->isWhiteList($userInfo->user_id)) {
            //同盾信息里面的final_score更新到loan记录里面
            $loan_info = User_loan::find()->where(['loan_id'=>$ret])->one();
            (new User_loan())->saveFinalScore($loan_info);
        }

        $address_info = Address::find()->where(['user_id'=>$userInfo->user_id])->one();
        $come_from = '';
        if (!empty($address_info)){
            $come_from = $address_info->come_from == 1 ? 'android': 'ios';
        }
        $rong_state = $rong_loan_info->updateRongLoan($ret);
        if (!empty($come_from)){
            $rong_loan_info->updateRongLoanDevice($come_from);
        }
        if (!$rong_state){
            return 4015; //修改中间表失败
        }
        //删除redis loanKey
        $this->delRedis($loan_no_keys);
        $loan_id = $ret;
        $uuid = '';
        if(!empty($data_info)){
            $uuid = $data_info['deviceInfo']['deviceId'];
        }
        //生成借款附属表
        $extendId = $this->addLoanExtend($loan_id, $uuid);
        if (!$extendId) {
            $transaction->rollBack();
            return 4015; //生成loan_extend失败
        }

        $transaction->commit();
        return 200;
    }

    /**
     * 添加用户合同拓展表数据
     * @param $loan_id
     * @param string $uuid
     * @return array
     */
    private function addLoanExtend($loan_id,$uuid = "") {
        $loan = User_loan::findOne($loan_id);
        $success_num = (new \app\models\news\User())->isRepeatUser($loan->user_id);
        $loanextendModel = new User_loan_extend();
        $extend = array(
            'user_id' => $loan->user_id,
            'loan_id' => $loan->loan_id,
            'outmoney' => 0,
            'payment_channel' => 0,
            'userIp' => Common::get_client_ip(),
            'extend_type' => '1',
            'success_num' => $success_num,
            'status' => 'INIT',
        );
        if(!empty($uuid)){
            $extend['uuid'] = $uuid;
        }
        $extend = $loanextendModel->addList($extend);
        if (!$extend){
            return 4016; //增加loan_extend失败
        }
        return 200;
    }

    /**
     * 借款决策
     * @param $userInfo
     * @param $postData
     * @return int
     */
    private function doChkLoan($userInfo, $postData)
    {
        //判断7-14产品中是否有进行中的借款
        $apiHttp = new Apihttp();
        $canLoan = $apiHttp->havingLoan(['identity'=>$userInfo->identity]);
        if (!$canLoan) {//不可借
            return 4007;
        }
        if ((new User_loan())->getHaveinLoan($userInfo->user_id)) {
            return 4007; //存在借款
        }
        if ($userInfo->status == 5) {
            return 4008;//黑名单用记
        }
        if ($userInfo->status != 3) {
            return 4012;//非正常用户
        }
        //获取临时保存r360借款
        $rong_loan = RongLoan::find()->where(['r_loan_id' => $postData['order_no'], 'source'=>8])->one();
        if (empty($rong_loan)){
            return 4009; //借款不存在
        }
        $loan_no_keys = $userInfo->user_id . "_loan_no";
        //如果redis存在是就返回200
        $loan_no = $this->getRedis($loan_no_keys);
        if ($loan_no){
            return 200;
        }
        $coupon_id = null;
        $coupon_amount = 0;
        $desc = 10;
        $days = $rong_loan['application_term'];
        $amount = $rong_loan['application_amount'];
        $suffix = $userInfo->user_id . rand(100000, 999999);
        $loan_no = date("YmdHis") . $suffix;
        $this->setRedis($loan_no_keys, $loan_no);
        $loanModel = new User_loan();
        $whiteModel = new White_list();
        $loan_getrule_string = $userInfo->user_id.' '.$loan_no;
        if ($whiteModel->isWhiteList($userInfo->user_id)) {
            $result = $loanModel->getRule($userInfo, 8, $amount, $days, $desc, $loan_no);
            Logger::errorLog($loan_getrule_string."\n", 'loan_getrule', 'r360');
            return 200;
        }
        $result = $loanModel->getRule($userInfo, 8, $amount, $days, $desc, $loan_no);
        Logger::errorLog($loan_getrule_string."\n", 'loan_getrule', 'r360');
        if ($result == 1) {//驳回
            $userLoanModel = new User_loan();
            $result = $userLoanModel->addRejectLoan($userInfo, $loan_no, $amount, $days, $desc, 3, 0, $coupon_id, $coupon_amount, 5, 0);
            $this->delRedis($loan_no_keys);
            return 4010; //驳回
        } elseif ($result == 2) {//拉黑
            $this->delRedis($loan_no_keys);
            $userInfo->setBlack();
            return 4011;//拉黑
        }
        return 200;
    }

    /**
     * 保存银行卡信息
     * @param $userInfo
     * @param $postData
     * @return array|bool
     */
    private function saveBankInfo($userInfo, $postData)
    {
        $bankInfo = (new User_bank())->find()->where(['card' => $postData['bank_card']])->one();
        if ($bankInfo) {
            if ($bankInfo->user_id != $userInfo->user_id) {//银行卡已经被其他用户绑定
                $postData->updateRongBank(10);
                return 4003; //银行卡已经被其他用户绑定
            }
        }
        $cardbin = (new Card_bin())->getCardBinByCard($postData['bank_card'], "prefix_length desc");
        $condition['user_id'] = $userInfo->user_id;
        $condition['type'] = $cardbin['card_type'];
        $condition['bank_abbr'] = $cardbin['bank_abbr'];
        $condition['bank_name'] = $cardbin['bank_name'];
        $condition['card'] = $postData['bank_card'];
        $condition['bank_mobile'] = $postData['user_mobile'];
        //银行卡存在不过行四要素验证
        $verify = $this->bankFourElements($postData, $userInfo->user_id);
        if ($verify == 200) {
            $condition['verify'] = 1;
        } else {
            $postData->updateRongBank(5);
            return $verify;
        }
        $UserBankModel = new User_bank();
        if ($bankInfo) {
            $user_bank_model = $bankInfo;
        }else{
            $user_bank_model = $UserBankModel;
        }
        $ret_userbank = $user_bank_model->addUserbank($condition);
        if (!$ret_userbank) {
            $postData->updateRongBank(6);
            return 4005;//记录表失败
        }
        if ($bankInfo){
            $bank_id = $bankInfo->id;
        }else{
            $bank_id = $UserBankModel->id;
        }
        //默认卡
//        $upDefBank = $UserBankModel->updateDefaultBank($userInfo->user_id, $bank_id);
//        if (empty($upDefBank)){
//            $postData->updateRongBank(7);
//            return 4006;//更新表失败
//        }
        return 200;
    }

    /**
     * 银行卡四要素认证
     * @param $data
     * @param $user_id
     * @return int
     */
    private function bankFourElements($data, $user_id)
    {
        //绑卡之前先做银行卡四要素验证
        //调用银行卡验证接口
        $postinfo = array(
            'identityid' => $user_id,
            'username' => $data['user_name'],
            'idcard' => $data['id_number'],
            'cardno' => $data['bank_card'],
            'phone' => $data['user_mobile']
        );
        $openApi = new Apihttp;
        Logger::errorLog(print_r(array($postinfo), true), 'bank_postinfo', 'r360');
        $result = $openApi->bankInfoValidRong($postinfo);
        Logger::errorLog(print_r(array($result), true), 'bank_postinfo_return', 'r360');
        if ($result['res_code'] != '0000') {
            return 4004;// 绑卡失败
        }
        return 200;
    }

    /**
     * 获取用户信息
     * @param $order_no
     * @return array
     */
    public function getRongLoan($order_no)
    {
        $rong_loan_info = RongLoan::find()->where(['r_loan_id'=>$order_no, 'source'=>8])->one();
        if (empty($rong_loan_info)){
            return ['code'=> 4001];  //借款不存在
        }
        $user_info = \app\models\news\User::find()->where(['mobile'=>$rong_loan_info->mobile])->one();
        if (empty($user_info)){
            return ['code'=> 4002]; //用户不存在
        }
        return ['code'=> 200, 'data'=>$user_info];
    }

    /**
     * 通知
     * @param $order_no  订单号
     * @param int $bind_status
     */
    private function httpRong($order_no, $bind_status=1)
    {
        if (SYSTEM_ENV == 'prod'){
            $htt_url = "https://openapi.rong360.com/gateway";
        }else{
            $htt_url = "https://openapi-test.rong360.com/gateway";
        }
        $biz_data = json_encode(
            [
                "order_no"=>$order_no,
                "bind_status"=> ($bind_status == 1) ? 1 : 2,
                "reason"=>($bind_status == 1)?'绑卡成功' : "绑卡失败",
            ]);
        $data = [
            "app_id"=> $this->appId,
            "method"=>"is.api.v3.order.bindcardfeedback",
            "sign_type"=> "RSA",
            "timestamp"=> (string)time(),
            "version"=>"1.0",
            "format" => "json",
            "biz_data"=>$biz_data
        ];
        $data['sign'] = $this->saveRsa($this->shortData($data));
        $data = json_encode($data);
        Logger::errorLog(print_r(array($data), true), 'Tieoncard__r360_notify', 'r360');
        $ret = Http::interface_post_json_rong($htt_url, $data);
        Logger::errorLog(print_r(array($ret), true), 'Tieoncard__r360_notify_return', 'r360');

    }

    /**
     * 借款失败通知
     * @param $order_no
     */
    private function httpRongResult($order_no)
    {
        if (SYSTEM_ENV == 'prod'){
            $htt_url = "https://openapi.rong360.com/gateway";
        }else{
            $htt_url = "https://openapi-test.rong360.com/gateway";
        }
        $biz_data = [
            "order_no" => $order_no,
            "conclusion" => 40,
            "remark" => "信用评分过低",
            "refuse_time" => time(),
        ];
        $data = [
            "app_id" => $this->appId,
            "method" => "is.api.v3.order.approvefeedback",
            "sign_type" => "RSA",
            "timestamp" =>(string)time(),
            "version" => "1.0",
            "format" => "json",
            "biz_data" => json_encode($biz_data),
        ];
        $data['sign'] = $this->saveRsa($this->shortData($data));
        $data = json_encode($data);
        Logger::errorLog(print_r(array($data), true), 'Approvefeedback__r360_notify', 'r360');
        $ret = Http::interface_post_json_rong($htt_url, $data);
        Logger::errorLog(print_r(array($ret), true), 'Approvefeedback__r360_notify_return', 'r360');
    }

    private function shortData($sortedParams, $type = "") {
        if ($type != "") {
            $sortedParams['type'] = $type;
        }
        unset($sortedParams['sign']);
        ksort($sortedParams);
        $string = '';
        $index = 0;
        foreach ($sortedParams as $key => $val) {
            $font = $index == 0 ? '' : '&';
            if (!empty($key) && !empty($val)) {
                $string .= $font . $key . '=' . $val;
                $index ++;
            }
        }
        return $string;
    }
    private function saveRsa($str) {
        $rsa = new RSA();
        // 签名的使用
//        $sign = $rsa->sign($str, $this->priv, 'base64', OPENSSL_ALGO_SHA256);
        $sign = $rsa->sign($str, $this->priv, 'base64');
        return $sign;
    }



    //获取redis
    public function getRedis($key) {
        return Yii::$app->redis->get($key);
    }

    //设置redis
    public function setRedis($key, $val) {
        return Yii::$app->redis->setex($key, 1800, $val);
    }

    //删除redis
    public function delRedis($key) {
        Yii::$app->redis->del($key);
    }
}