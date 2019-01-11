<?php

namespace app\commands\Signalling;

/**
 *  有信令推送
 *  linux : sudo -u www /data/wwwroot/yiyiyuan/yii signalling/signalfinish
 *  windows D:phpStudy\php56n\php.exe D:WWW\yiyiyuanactive\yii signalling/signalfinish SUCCESS(REJECT)
 */
use app\commands\BaseController;
use app\commonapi\Apihttp;
use app\commonapi\Logger;
use app\models\news\Cg_remit;
use app\models\news\Insurance;
use app\models\news\Push_yxl;
use app\models\news\SmsSend;
use app\models\news\UmengSend;
use app\models\news\User;
use app\models\news\User_loan_extend;
use app\models\news\User_loan_flows;
use app\models\service\UserloanService;
use Yii;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class SignalfinishController extends BaseController {

    private $limit = 500;

    public function actionIndex() {
        $countNum = 0;
        $successNum = 0;
        $start_date = date('Y-m-d H:i:00', strtotime('-6 hours'));
        $new_date = date('Y-m-d H:i:00');
        $where = [
            'AND',
            ['>', Push_yxl::tableName() . '.last_modify_time', $start_date],
            ['<', Push_yxl::tableName() . '.last_modify_time', $new_date],
            ['<', Push_yxl::tableName() . '.notify_time', $new_date],
            [Push_yxl::tableName() . '.notify_status' => [0,3]],
            ['<', Push_yxl::tableName() . '.notify_num', 7],
            ['<>', Push_yxl::tableName() . '.type', 3]
        ];
        $sql = Push_yxl::find()->joinWith('loan', true, 'LEFT JOIN')->joinWith('user', true, 'LEFT JOIN')->joinWith('extend', true, 'LEFT JOIN')->where($where);
        $total = $sql->count();
        $pages = ceil($total / $this->limit);
        for ($i = 0; $i < $pages; $i++) {
            $pushYxlList = $sql->limit($this->limit)->all();
            if (empty($pushYxlList)) {
                break;
            }
            $countNum += count($pushYxlList);
            foreach ($pushYxlList as $key => $value) {
                $result = $this->doSignal($value);
                if (!$result) {
                    continue;
                }
                $successNum++;
            }
        }
        Logger::dayLog('signal/signalfinish', date('Y-m-d H:i:s'), '成功需处理总数：' . $countNum, '成功：' . $successNum);
        exit('success:' . $successNum . ';count:' . $countNum);
    }

    private function doSignal($pushYxlModel) {
        if (empty($pushYxlModel) || !is_object($pushYxlModel) || empty($pushYxlModel->loan)|| empty($pushYxlModel->extend) || empty($pushYxlModel->user)) {
            return false;
        }
        //推送数据
        $signal = $this->postSignal($pushYxlModel);
        if (!$signal) {
            return false;
        }
        if($pushYxlModel->type == 1){
//            $tbSuccessResult = $pushYxlModel->extend->doTbSuccess();
//            if (!$tbSuccessResult) {
//                Logger::dayLog('signal/signalpush', 'TB-SUCCESS状态更新失败', 'loan ID：' . $pushYxlModel->loan_id);
//                return false;
//            }
            //添加短信文案到sms_send
            $sendRseult = $this->saveSmsSend($pushYxlModel->loan);
            if (!$sendRseult) {
                Logger::dayLog('signal/signalpush', '添加到短信通知表失败', 'loan ID：' . $pushYxlModel->loan->loan_id);
                return false;
            }
            //添加提现提醒umeng_send
            $umengSend = (new UmengSend())->saveUmengSend($pushYxlModel->loan,1);
            if (!$umengSend) {
                Logger::dayLog('signal/signalpush', '添加到体现通知表失败', 'loan ID：' . $pushYxlModel->loan->loan_id);
                return false;
            }
        }
        return true;
    }

    private function postSignal($pushYxlModel) {
        if (empty($pushYxlModel) || !is_object($pushYxlModel)) {
            return false;
        }
        if($pushYxlModel->type == 1){
            $loanFlow = User_loan_flows::find()->where(['loan_id'=>$pushYxlModel->loan->loan_id,'loan_status'=>6])->one();
            $invalid_time = date("Y-m-d H:i:s",strtotime("+23 hours 30 minutes",strtotime($loanFlow->create_time)));
            $contacts = [
                'loan_id' => $pushYxlModel->loan->loan_id,
                'realname' => $pushYxlModel->user->realname,
                'identity' => $pushYxlModel->user->identity,
                'loan_amount' => $pushYxlModel->loan->real_amount, //借款金额
                'amount' => $pushYxlModel->loan->real_amount*0.18,//服务卡金额
                'user_mobile' => $pushYxlModel->user->mobile, //手机号
                'loan_time' => $pushYxlModel->loan->create_time, //借款创建时间
                'invalid_time' => $invalid_time,//失效时间
                'callback_url' => Yii::$app->params['signal_notify_url'], //回调地址
                'come_from' => $pushYxlModel->loan->source,//借款来源
                'source' => 1
            ];
        }else{
            $status = $pushYxlModel->loan_status;
            $cgRemitModel = Cg_remit::find()->where(['loan_id'=>$pushYxlModel->loan->loan_id])->one();
            if(!empty($cgRemitModel) && $cgRemitModel->remit_status == 'NOREMIT'){
                $status = 4;
            }
            $contacts = [
                'loan_id' => $pushYxlModel->loan->loan_id,
                'source' => 1,
                'status' => $status,
            ];
            if($status == 6){
                $contacts['start_time'] = $pushYxlModel->loan->start_date;
            }
        }
        $api = new Apihttp();
        $result = $api->postSignal($contacts,$pushYxlModel->type);
        if ($result['rsp_code'] != '0000') {
            $pushYxlModel->updateError();
            Logger::dayLog('signal/signalpush', '有信令推送失败', 'loan ID：' . $pushYxlModel->loan_id, $contacts, $result);
            return false;
        }
        $pushYxlModel->updateSuccess();
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
            $content = '您的借款已通过审核，请尽快前往一亿元处理，以免借款失效。';
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


}
