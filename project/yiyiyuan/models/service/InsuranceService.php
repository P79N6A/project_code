<?php

namespace app\models\service;

use app\commonapi\Logger;
use app\models\news\User_loan;
use app\commonapi\Apihttp;
use app\models\news\Insurance;
use app\models\news\Insure;
use app\models\news\User;
use Yii;


class InsuranceService extends Service
{

    /**
     * 核保
     * @param $loan_id
     * @param $money
     * @param $type 1:借款购买 2：主动购买 3：续期购买
     * @return array
     */
    public function policy($loan_id, $money, $type)
    {
        if(!$loan_id || !$type){
            return ['code' => '10001', 'msg' => '系统错误'];
        }
        $loanInfo = User_loan::findOne($loan_id);
        if(!$loanInfo){
            return ['code' => '10001', 'msg' => '系统错误'];
        }
        $userInfo = User::findOne($loanInfo->user_id);
        if(!$userInfo){
            return ['code' => '10001', 'msg' => '系统错误'];
        }
        $reqId = 'X' . $userInfo->user_id . date('Ymdhis');//请求序号
        //合保
        $policyCode = $this->postPolicy($userInfo, $reqId, $money, $loanInfo->days);
        if ($policyCode == 20) {
            return ['code' => '10002', 'msg' => '核保失败'];
        }
        //添加核保记录
        $insuranceId = $this->addInsurance($reqId, $loanInfo->loan_id, $loanInfo->user_id, $money, $loanInfo->days, $type);
        if (empty($insuranceId)) {
            return ['code' => '10001', 'msg' => '系统错误'];
        }
        $insuranceObj = (new Insurance())->getRecordById($insuranceId);
        if ($policyCode != '0000') {
            $failInfo = $insuranceObj->updateFail();
            if (!$failInfo) {
                Logger::dayLog('insurance/insure', 'insurance.fail状态更新失败', 'user ID：' . $insuranceObj->user_id);
                return ['code' => '10001', 'msg' => '系统错误'];
            }
            return ['code' => '10002', 'msg' => '核保失败'];
        }
        $successInfo = $insuranceObj->updateSuccess();
        if (!$successInfo) {
            Logger::dayLog('insurance/insure', 'insurance.success状态更新失败', 'user ID：' . $insuranceObj->user_id);
            return ['code' => '10001', 'msg' => '系统错误'];
        }
        return ['code' => '0000', 'msg' => '核保成功', 'data' => $insuranceId];
    }

    /**
     * 保险购买
     * @param $insuranceId
     * @param int $source
     * @param $type 1:借款购买 2：主动购买 3：续期购买
     * @return string
     */
    public function buy($insuranceId, $source = 1, $type)
    {
        if(!$insuranceId || !$type){
            return ['code' => '10001', 'msg' => '系统错误'];
        }
        $insuranceInfo = Insurance::findOne($insuranceId);
        if(!$insuranceInfo){
            return ['code' => '10001', 'msg' => '系统错误'];
        }
        $order_id = $insuranceInfo->user_id . date('YmdHis');
        //投保支付
        $callbackurl = $this->getCallbackUrl($type);
        $url = $this->policypay($insuranceInfo, $order_id, $callbackurl);
        if (!$url) {
            return ['code' => '10002', 'msg' => '接口调用失败'];
        }
        //添加支付记录
        $order_id = $this->addInsure($insuranceInfo, $order_id, $source, $type);
        if (!$order_id) {
            return ['code' => '10003', 'msg' => '支付记录添加失败'];
        }
        return ['code' => '0000', 'msg' => '', 'url' => $url];
    }

    /**
     * 调用核保接口
     * @param $userInfo
     * @param $reqId
     * @param $money
     * @param $days
     * @return bool
     */
    private function postPolicy($userInfo, $reqId, $money, $days) {
        if (empty($userInfo) || empty($reqId) || empty($money) || !is_object($userInfo)) {
            return false;
        }
        $contacts = [
            'req_id' => $reqId, //请求序号
            'premium' => $money, //保费
            'identityid' => $userInfo->identity, //身份证
            'user_mobile' => $userInfo->mobile, //手机号
            'user_name' => $userInfo->realname, //姓名
            'benifitName' => $userInfo->realname, //受益人姓名
            'benifitCertiType' => 'I', //受益人证件类型
            'benifitCertiNo' => $userInfo->identity, //证件号码
            'fund' => '10', //资金方
            'callbackurl' => Yii::$app->params['policy_notify_url'], //回调地址
            'policyDate' => $days, //借款天数
        ];
        $api = new Apihttp();
        $result = $api->postPolicy($contacts);
        if ($result['res_code'] != '0000') {
            Logger::dayLog('insurance/insure', '投保失败', 'user ID：' . $userInfo->user_id, $contacts, $result);
        }
        return $result['res_code'];
    }

    /**
     * 调用保险购买接口
     * @param $insureInfo
     * @param $order_id
     * @param $callbackurl
     * @return bool|string
     */
    private function policypay($insureInfo, $order_id, $callbackurl) {
        $contacts = [
            'req_id' => $insureInfo->req_id, //请求序号
            'client_id' => $order_id,
            //'callbackurl' => Yii::$app->params['policypay_notify_url_x'], //回调地址
            'callbackurl' => $callbackurl, //回调地址
        ];
        $api = new Apihttp();
        $result = $api->policypay($contacts);
        if ($result['res_code'] != 0) {
            Logger::dayLog('installment/policypay', '投保支付失败', 'insure ID：' . $insureInfo->id, $contacts, $result);
            return false;
        }
        if (isset($result['res_data']) && isset($result['res_data']['url'])) {
            $redirect_url = (string) $result['res_data']['url'];
            if (empty($redirect_url)) {
                return false;
            }
            return $redirect_url;
        } else {
            return false;
        }
    }

    /**
     * 添加核保记录
     * @param $reqId
     * @param $loan_id
     * @param $userId
     * @param $money
     * @param $days
     * @param $type 1:借款购买 2：主动购买 3：续期购买
     * @return bool|string
     */
    private function addInsurance($reqId, $loan_id, $userId, $money, $days, $type = 1) {
        $insuranceModel = new Insurance();
        $condition = [
            'req_id' => $reqId,
            'loan_id' => $loan_id,
            'user_id' => $userId,
            'money' => $money,
            'days' => $days,
            'type' => $type,
        ];
        $insuranceId = $insuranceModel->saveData($condition);
        if (!$insuranceId) {
            return false;
        }
        return $insuranceId;
    }

    /**
     * 添加保险支付记录
     * @param $insureInfo
     * @param $order_id
     * @param $source
     * @param $type 1:借款购买 2：主动购买 3：续期购买
     * @return bool|mixed
     */
    private function addInsure($insureInfo, $order_id, $source, $type = 1) {
        $data = [
            'req_id' => $insureInfo->req_id,
            'loan_id' => $insureInfo->loan_id,
            'user_id' => $insureInfo->user_id,
            'order_id' => $order_id,
            'money' => $insureInfo->money,
            'source' => $source,
            'version' => 0,
            'type' => $type,
        ];
        $res = (new Insure())->saveData($data);
        if (!$res) {
            return false;
        }
        return $data['order_id'];
    }

    private function getCallbackUrl($type){
        switch ($type)
        {
            case 1://借款购买
                return Yii::$app->params['policypay_notify_url'];
                break;
            case 2://主动购买
                return Yii::$app->params['policypay_notify_url_z'];
                break;
            case 3://续期购买
                return Yii::$app->params['policypay_notify_url_x'];
                break;
            default:
                return Yii::$app->params['policypay_notify_url'];
        }
    }


}