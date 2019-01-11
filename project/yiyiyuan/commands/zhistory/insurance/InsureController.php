<?php

namespace app\commands\insurance;

/**
 *  众安合保
 *  linux : sudo -u www /data/wwwroot/yiyiyuan/yii insurance/insure
 *  windows D:phpStudy\php56n\php.exe D:WWW\yiyiyuanactive\yii insurance/insure
 */
use app\commands\BaseController;
use app\commonapi\Apihttp;
use app\commonapi\Logger;
use app\models\news\Insurance;
use app\models\news\SmsSend;
use app\models\news\UmengSend;
use app\models\news\User_loan_extend;
use app\models\news\WarnMessageList;
use app\models\service\UserloanService;
use Yii;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class InsureController extends BaseController {

    private $limit = 200;

    public function actionIndex() {
        $countNum = 0;
        $successNum = 0;
        $start_date = date('Y-m-d H:i:00', strtotime('-60 minutes'));
        $end_date = date('Y-m-d H:i:00');
        $where = [
            'AND',
            ['>=', User_loan_extend::tableName() . '.last_modify_time', $start_date],
            ['<', User_loan_extend::tableName() . '.last_modify_time', $end_date],
            [User_loan_extend::tableName() . '.status' => 'TB-AUTHED'],
            [Insurance::tableName() . '.id' => NULL]
        ];
        $sql = User_loan_extend::find()->joinWith('insurance', 'TRUE', 'LEFT JOIN')->where($where);
        $total = $sql->count();
        $pages = ceil($total / $this->limit);
        for ($i = 0; $i < $pages; $i++) {
            $tbAuthedList = $sql->limit($this->limit)->all();
            if (empty($tbAuthedList)) {
                break;
            }
            $countNum += count($tbAuthedList);
            foreach ($tbAuthedList as $key => $value) {
                $result = $this->doPolicy($value);
                if (!$result) {
                    continue;
                }
                $successNum++;
            }
        }
        Logger::dayLog('insurance/insure', date('Y-m-d H:i:s'), '成功需处理总数：' . $countNum, '成功：' . $successNum);
        exit('success:' . $successNum . ';count:' . $countNum);
    }

    private function doPolicy($userLoanExtendObj) {
        if (empty($userLoanExtendObj) || !is_object($userLoanExtendObj) || empty($userLoanExtendObj->loan) || empty($userLoanExtendObj->user)) {
            return false;
        }
        $reqId = 'R' . date('Ymdhis') . $userLoanExtendObj->loan_id; //请求序号
        //费率修改
        $date = date('Y-m-d H:i:s', time());
        $rate = 0.18;
        if($date >='2018-04-02' && $date <'2018-04-16'){
            $rate = 0.13;
        }
        $money = $userLoanExtendObj->loan->real_amount * $rate; //保费 
        //合保
        $policyCode = $this->postPolicy($userLoanExtendObj, $reqId, $money);
        //请求无响应
        if ($policyCode == 20) {
            return false;
        }
        //新增insurance投保记录
        $insuranceId = $this->addInsurance($userLoanExtendObj, $reqId, $money);
        if (empty($insuranceId)) {
            return false;
        }
        $insuranceObj = (new Insurance())->getRecordById($insuranceId);
        //失败
//        if ($policyCode != '0000') {
//            $failInfo = $insuranceObj->updateFail();
//            if (!$failInfo) {
//                Logger::dayLog('insurance/insure', 'insurance.fail状态更新失败', 'loan ID：' . $userLoanExtendObj->loan_id);
//                return false;
//            }
//            $userloanServiceModel = new UserloanService();
//            $failResult = $userloanServiceModel->tbReject($userLoanExtendObj->loan_id);
//            if (!$failResult) {
//                Logger::dayLog('insurance/insure', '借款fail状态更新失败', 'loan ID：' . $userLoanExtendObj->loan_id);
//            }
//            return false;
//        }
        //成功
        $successInfo = $insuranceObj->updateSuccess();
        if (!$successInfo) {
            Logger::dayLog('insurance/insure', 'insurance.success状态更新失败', 'loan ID：' . $userLoanExtendObj->loan_id);
            return false;
        }
        $tbSuccessResult = $userLoanExtendObj->doTbSuccess();
        if (!$tbSuccessResult) {
            Logger::dayLog('insurance/insure', 'TB-SUCCESS状态更新失败', 'loan ID：' . $userLoanExtendObj->loan_id);
            return false;
        }
        //添加短信文案到sms_send
        $sendRseult = $this->saveSmsSend($userLoanExtendObj->loan);
        if (!$sendRseult) {
            Logger::dayLog('insurance/insure', '添加到短信通知表失败', 'loan ID：' . $userLoanExtendObj->loan_id);
            return false;
        }
        //添加消息提醒
        $warnMessageModel = new WarnMessageList;
        $warnMessageModel->saveWarnMessage($userLoanExtendObj,1,3);
        //添加提现提醒umeng_send
        $umengSend = (new UmengSend())->saveUmengSend($userLoanExtendObj->loan, 1);
        if (!$umengSend) {
            Logger::dayLog('insurance/insure', '添加到体现通知表失败', 'loan ID：' . $userLoanExtendObj->loan_id);
            return false;
        }
        return true;
    }

    private function saveSmsSend($userLoan) {
        if (!$userLoan) {
            return false;
        }
        $amount = number_format($userLoan->amount, 2);
        $sms_type = 8;
//        if ($userLoan->source == 2) {
//            $channel = 3;
//            $content = $userLoan->user->realname . '先生/女士，您的借款已经通过审核，请关注微信公众号“先花一亿元”进行提现，长时间不提现视为自动放弃！回T退订';
//        } else {
        $channel = 1;
        $content = '尊敬的用户，您在先花一亿元有一笔' . $amount . '元借款已通过审核，请前往APP发起提现，2小时内不发起提现则将借款驳回，同时影响您的信用评级';
//        }
        $mobile = $userLoan->user->mobile;

        $addData['mobile'] = $mobile;
        $addData['content'] = $content;
        $addData['sms_type'] = $sms_type;
        $addData['status'] = 0;
        $addData['channel'] = $channel;
        $addData['send_time'] = date('Y-m-d H:i:s');
        $sms_model = new SmsSend();
        $res = $sms_model->addSmsSend($addData);
        return $res;
    }

    private function addInsurance($userLoanExtendObj, $reqId, $money) {
        if (empty($userLoanExtendObj) || empty($reqId) || !is_object($userLoanExtendObj)) {
            return false;
        }
        $insuranceModel = new Insurance();
        $condition = [
            'req_id' => $reqId,
            'loan_id' => $userLoanExtendObj->loan_id,
            'user_id' => $userLoanExtendObj->user_id,
            'money' => $money
        ];
        $insuranceId = $insuranceModel->saveData($condition);
        if (empty($insuranceId)) {
            Logger::dayLog('insurance/insure', 'insurance记录失败', 'loan ID：' . $userLoanExtendObj->loan_id);
            return false;
        }
        return $insuranceId;
    }

    private function postPolicy($userLoanExtendObj, $reqId, $money) {
        if (empty($userLoanExtendObj) || empty($reqId) || !is_object($userLoanExtendObj)) {
            return false;
        }
        $contacts = [
            'req_id' => $reqId, //请求序号
            'premium' => $money, //保费
            'identityid' => $userLoanExtendObj->user->identity, //身份证
            'user_mobile' => $userLoanExtendObj->user->mobile, //手机号
            'user_name' => $userLoanExtendObj->user->realname, //姓名
            'benifitName' => $userLoanExtendObj->user->realname, //受益人姓名
            'benifitCertiType' => 'I', //受益人证件类型
            'benifitCertiNo' => $userLoanExtendObj->user->identity, //证件号码
            'policyDate' => $userLoanExtendObj->loan->days, //借款天数
            'fund' => '10', //资金方
            'callbackurl' => Yii::$app->params['policy_notify_url'], //回调地址
        ];
        $api = new Apihttp();
        $result = $api->postPolicy($contacts);
        if ($result['res_code'] != '0000') {
            Logger::dayLog('insurance/insure', '投保失败', 'loan ID：' . $userLoanExtendObj->loan_id, $contacts, $result);
        }
        return $result['res_code'];
    }

}
