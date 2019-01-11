<?php

namespace app\modules\newdev\controllers;

use app\commonapi\Apihttp;
use app\commonapi\Keywords;
use app\commonapi\Logger;
use app\models\news\Insurance;
use app\models\news\Insure;
use app\models\news\User;
use app\models\news\User_loan;
use app\modules\newdev\controllers\NewdevController;
use Yii;

class BuyController extends NewdevController {

    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [];
    }

    public function actionIndex()
    {
        $userId = $this->get('user_id');
        $source = $this->get('source');
        $userId = intval($userId);
        if(!$userId || !$source){
            exit('参数错误');
        }
        $userInfo = User::findOne($userId);
        if(!$userInfo || !$userInfo->realname || !$userInfo->user_id){
            exit('用户信息错误');
        }
        $this->getView()->title = '保险购买';
        return $this->render('index', [
            'userInfo' => $userInfo,
            'source' => $source,
        ]);
    }

    /**
     * 核保
     */
    public function actionPolicy()
    {
        $userId = $this->post('user_id');
        $money = $this->post('amount');
        $days = $this->post('days');
        if(!$userId || !$money || !$days){
            echo json_encode(['code' => '10001', 'msg' => '系统错误']);
            exit;
        }
        $userInfo = User::findOne($userId);
        if(!$userInfo){
            echo json_encode(['code' => '10001', 'msg' => '系统错误']);
            exit;
        }
        $reqId = 'Z' . date('Ymdhis') . $userId;//请求序号
        //合保
        $policyCode = $this->postPolicy($userInfo, $reqId, $money, $days);
        if ($policyCode == 20) {
            echo json_encode(['code' => '10002', 'msg' => '核保失败']);
            exit;
        }
        //添加核保记录
        $insuranceId = $this->addInsurance($reqId, -1, $userId, $money, $days);
        if (empty($insuranceId)) {
            echo json_encode(['code' => '10003', 'msg' => '购买记录添加失败']);
            exit;
        }
        $insuranceObj = (new Insurance())->getRecordById($insuranceId);
        if ($policyCode != '0000') {
            $failInfo = $insuranceObj->updateFail();
            if (!$failInfo) {
                Logger::dayLog('insurance/insure', 'insurance.fail状态更新失败', 'user ID：' . $insuranceObj->user_id);
                echo json_encode(['code' => '10004', 'msg' => '状态更新失败']);
                exit;
            }
            echo json_encode(['code' => '10005', 'msg' => '核保失败']);
            exit;
        }
        $successInfo = $insuranceObj->updateSuccess();
        if (!$successInfo) {
            Logger::dayLog('insurance/insure', 'insurance.success状态更新失败', 'user ID：' . $insuranceObj->user_id);
            return false;
        }
        echo json_encode(['code' => '0000', 'msg' => '核保成功', 'data' => $insuranceId]);
        exit;
    }

    /**
     * 保险购买
     */
    public function actionBuy()
    {
        $insuranceId = $this->post('insuranceId');
        $source = $this->post('source');
        if(!$insuranceId){
            echo json_encode(['code' => '10001', 'msg' => '系统错误']);
            exit;
        }
        $insuranceInfo = Insurance::findOne($insuranceId);
        if(!$insuranceInfo){
            echo json_encode(['code' => '10002', 'msg' => '系统错误']);
            exit;
        }
        $order_id = date('YmdHis') . $insuranceInfo->user_id;
        //投保支付
        $url = $this->policypay($insuranceInfo, $order_id);
        if (!$url) {
            echo json_encode(['code' => '10003', 'msg' => '系统错误']);
            exit;
        }
        //添加支付记录
        $order_id = $this->addInsure($insuranceInfo, $order_id, $source);
        if (!$order_id) {
            echo json_encode(['code' => '10004', 'msg' => '系统错误']);
            exit;
        }
        echo json_encode(['code' => '0000', 'msg' => '', 'url' => $url]);
        exit;
    }

    private function policypay($insureInfo, $order_id) {
        $contacts = [
            'req_id' => $insureInfo->req_id, //请求序号
            'client_id' => $order_id,
            'callbackurl' => Yii::$app->params['policypay_notify_url_z'], //回调地址
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
     * 添加保险支付记录
     * @param $insureInfo
     * @param $order_id
     * @param $source
     * @return bool|mixed
     */
    private function addInsure($insureInfo, $order_id, $source) {
        $data = [
            'req_id' => $insureInfo->req_id,
            'loan_id' => $insureInfo->loan_id,
            'user_id' => $insureInfo->user_id,
            'order_id' => $order_id,
            'money' => $insureInfo->money,
            'source' => $source,
            'version' => 0,
            'type' => 2,
        ];
        $res = (new Insure())->saveData($data);
        if (!$res) {
            return false;
        }
        return $data['order_id'];
    }

    /**
     * 添加核保记录
     * @param $reqId
     * @param $loan_id
     * @param $userId
     * @param $money
     * @param $days
     * @return bool|string
     */
    private function addInsurance($reqId, $loan_id, $userId, $money, $days) {
        $insuranceModel = new Insurance();
        $condition = [
            'req_id' => $reqId,
            'loan_id' => $loan_id,
            'user_id' => $userId,
            'money' => $money,
            'days' => $days,
            'type' => 2,
        ];
        $insuranceId = $insuranceModel->saveData($condition);
        if (!$insuranceId) {
            return false;
        }
        return $insuranceId;
    }

}
