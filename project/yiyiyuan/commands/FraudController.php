<?php

/**
 * 欺诈推送消息和获取消息结果
 */
/**
 *   linux : /data/wwwroot/yiyiyuan/yii fraud sent
 *   window : d:\xampp\php\php.exe D:\www\yiyiyuan\yii fraud sent
 */

namespace app\commands;

use app\commands\BaseController;
use app\common\ApiSign;
use app\common\Curl;
use app\commonapi\ApiFrund;
use app\commonapi\Logger;
use app\models\dev\AntiFraud;
use app\models\dev\User;
use app\models\dev\User_loan;
use app\models\dev\User_loan_extend;
use app\models\dev\User_loan_flows;
use app\models\dev\White_list;
use yii\helpers\ArrayHelper;
use Yii;

class FraudController extends BaseController {
    //private $today;

    /**
     * 初始化
     */
    public function init() {
        parent::init();
    }

    /**
     * 5分钟轮询
     * 推送反欺诈数据
     */
    public function sent() {
        //1 获取反欺诈的数据
        $now = time();
        $dataStart = date('Y-m-d H:i:00', $now - 36000);
        $dataEnd = date('Y-m-d H:i:00', $now - 1800);

        $where = [
            'AND',
            ['status' => 5],
            ['prome_status' => 0],
            ['>=', 'create_time', $dataStart],
            ['<', 'create_time', $dataEnd],
        ];
        $data = User_loan::find()->where($where)->limit(600)->all();
        $nums = is_array($data) ? count($data) : 0;
        Logger::dayLog('fraud', 'send', $dataStart . ' to ' . $dataEnd, '1 获取user_loan条数', $nums);
        if (empty($data)) {
            return 0;
        }

        //2 锁定状态,避免下次重复处理
        $loan_ids = ArrayHelper::getColumn($data, 'loan_id');
        $nums = User_loan::updateAll(['prome_status' => 11, 'last_modify_time' => date('Y-m-d H:i:s')], ['loan_id' => $loan_ids]);
        Logger::dayLog('fraud', 'send', $dataStart . ' to ' . $dataEnd, '2 锁定user_loan.prome_status=11条数', $nums);

        //3 放入到反欺诈表中
        $userData = [];
        foreach ($data as $v) {
            $userData[] = [
                'user_id' => $v['user_id'],
                'loan_id' => $v['loan_id'],
            ];
        }
        $nums = AntiFraud::addBatchByUsers($userData);

        //4 写日志
        Logger::dayLog('fraud', 'send', $dataStart . ' to ' . $dataEnd, '3插入到anti_fraud条数', $nums);
        return $nums;
    }

    /**
     * 5分钟轮询
     * 更新反欺诈结果
     * @return int
     */
    private function sendFraudResult() {
        //1 获取欺诈初始的数据（3，0）
        $oAntiFraud = new AntiFraud;
        $result_time = date('Y-m-d H:i:00', time());
        $frauds = $oAntiFraud->getInit($result_time);
        $nums = is_array($frauds) ? count($frauds) : 0;
        Logger::dayLog('fraud', 'sendFraudResult', $result_time . ' 前5小时内', '1 从anti_fraud获取初始欺诈条数', $nums);
        if (empty($frauds)) {
            return 0;
        }
        //2 锁定状态,避免下次重复处理
        $ids = ArrayHelper::getColumn($frauds, 'id');
        $nums = AntiFraud::updateAll(['model_status' => 6, 'modify_time' => date('Y-m-d H:i:s')], ['id' => $ids]);
        Logger::dayLog('fraud', 'sendFraudResult', $result_time . ' 前5小时内', '2 锁定auti_fraud.model_status=6条数', $nums);

        //3 调用反欺诈结果，更新result_status，结束auti_fraud.model_status=6
        $nums = 0;
        foreach ($frauds as $fraud) {
            $result = $this->doInit($fraud);
            if (!$result) {
                continue;
            }
            $nums++;
        }
        Logger::dayLog('fraud', 'sendFraudResult', $result_time . ' 前5小时内', '3 更新result_status结果条数', $nums);
    }

    //进行反欺诈初始化操作
    private function doInit($fraud) {
        $result = $fraud->lockInit();
        if (!$result) {
            return false;
        }
        $result = $this->sendFraudtoRule($fraud->loan_id);
        $fraudInfo = $fraud->updateFraudResult($result);
        if (!$fraudInfo) {
            return false;
        }
        return $fraudInfo;
    }

    /**
     * 5分钟轮询
     * 获取欺诈用户并处理
     * yii fraud receive-fraud
     */
    public function receiveFraud() {
        //1 获取欺诈的数据
        $oAntiFraud = new AntiFraud;
        $result_time = date('Y-m-d H:i:00', time() - 1800); //半小时之前, 因为有缓冲
        $frauds = $oAntiFraud->getFraud($result_time);
        $nums = is_array($frauds) ? count($frauds) : 0;
        Logger::dayLog('fraud', 'receiveFraud', $result_time . ' 前12小时缓冲', '1 从anti_fraud获取欺诈条数', $nums);
        if (empty($frauds)) {
            return 0;
        }

        //2 锁定状态,避免下次重复处理
        $ids = ArrayHelper::getColumn($frauds, 'id');
        $nums = AntiFraud::updateAll(['model_status' => 4, 'modify_time' => date('Y-m-d H:i:s')], ['id' => $ids]);
        Logger::dayLog('fraud', 'receiveFraud', $result_time . ' 前12小时缓冲', '2 锁定auti_fraud.model_status=4条数', $nums);

        //3 通过,即进入prome中
        $nums = $this->reject($frauds);
        Logger::dayLog('fraud', 'receiveFraud', $result_time . ' 前12小时缓冲', '3 终止user_loan.prome_status=5条数', $nums);

        //4 结束欺诈整体流程
        $nums = AntiFraud::updateAll(['model_status' => 5, 'modify_time' => date('Y-m-d H:i:s')], ['id' => $ids]);
        Logger::dayLog('fraud', 'receiveFraud', $result_time . ' 前12小时缓冲', '4 终止auti_fraud.model_status=5条数', $nums);
    }

    /**
     * 5分钟轮询
     * 获取正常用户并处理
     * yii fraud receive-normal
     */
    public function receiveNormal() {
        //1 获取欺诈的数据
        $oAntiFraud = new AntiFraud;
        $result_time = date('Y-m-d H:i:00'); //当前分钟之前 
        $normals = $oAntiFraud->getNormal($result_time);
        $nums = is_array($normals) ? count($normals) : 0;
        Logger::dayLog('fraud', 'receiveNormal', $result_time . ' 前1小时内', '1 从anti_fraud获取正常条数', $nums);
        if (empty($normals)) {
            return 0;
        }

        //2 锁定状态,避免下次重复处理
        $ids = ArrayHelper::getColumn($normals, 'id');
        $nums = AntiFraud::updateAll(['model_status' => 4, 'modify_time' => date('Y-m-d H:i:s')], ['id' => $ids]);
        Logger::dayLog('fraud', 'receiveNormal', $result_time . ' 前1小时内', '2 锁定auti_fraud.model_status=4条数', $nums);

        //3 通过,即进入prome中
        $nums = $this->approval($normals);
        Logger::dayLog('fraud', 'receiveNormal', $result_time . ' 前1小时内', '3 进入prome: user_loan.prome_status=10条数', $nums);

        //4 结束欺诈整体流程
        $nums = AntiFraud::updateAll(['model_status' => 5, 'modify_time' => date('Y-m-d H:i:s')], ['id' => $ids]);
        Logger::dayLog('fraud', 'receiveNormal', $result_time . ' 前1小时内', '4 终止auti_fraud.model_status=5条数', $nums);
    }

    /**
     * 进行通过操作
     * @param  [type] $loan_id [description]
     * @return [type]          [description]
     */
    private function approval($normals) {
        // 修改prome_status = 10 进入prome
        $loan_ids = ArrayHelper::getColumn($normals, 'loan_id');
        $loan_ids = array_unique($loan_ids);

        $ups = ['prome_status' => 10, 'last_modify_time' => date('Y-m-d H:i:s')];
        $where = ['loan_id' => $loan_ids, 'prome_status' => 11, 'status' => 5];
        $nums = User_loan::updateAll($ups, $where);
        return $nums;
    }

    /**
     * 进行驳回操作
     * @param  [type] $loan_id [description]
     * @return [type]          [description]
     */
    private function reject($frauds) {
        //修改prome_status = 5 结束流程 @todo 目前都走prome
        $loan_ids = ArrayHelper::getColumn($frauds, 'loan_id');
        $loan_ids = array_unique($loan_ids);
        $nums = count($loan_ids);

        //@todo 进行驳回处理
        foreach ($frauds as $fraud) {
            //判断驳回的原因
            $result_subject = json_decode($fraud->result_subject);
            $loan_id = $fraud->loan_id;
            $user_id = $fraud->user_id;
            //判断是否是白名单用户，如果不是触发了哪类规则,如果是4,7则驳回原因为A类，否则为D类
            $userinfo = User::findOne($user_id);
            $white_user = White_list::find()->where(['mobile' => $userinfo->mobile])->one();
            if (empty($white_user)) {
                $loaninfo = User_loan::find()->where(['loan_id' => $loan_id])->one();
                if ($loaninfo->status == 5) {
                    $ret = $loaninfo->changeStatus(7);
                    if ($ret) {
                        $loaninfo->prome_status = 5;
                        $loaninfo->save();
                        $extend = (new User_loan_extend())->getUserLoanSubsidiaryByLoanId($loan_id);
                        $extend->updateUserLoanSubsidiary(array('extend_type' => 3));
                        if (isset($result_subject->hit) && isset($result_subject->hit[0]) &&
                                ($result_subject->hit[0] == 'report_aomen' || $result_subject->hit[0] == 'report_fcblack')) {
                            $reason = '请30天后再次尝试借款';
                        } else {
                            $reason = '借款申请暂时无法通过，期待下次为您服务';
                        }
                        $relative = NULL;
                        $loan_flow = User_loan_flows::find()->where(['loan_id' => $loan_id, 'loan_status' => 7])->one();
                        if (!empty($loan_flow)) {
                            $loan_flow->reason = $reason;
                            $loan_flow->relative = $relative;
                            $loan_flow->save();
                        }
//                        if ($loaninfo->business_type == 4) {
//                            $bank_bill = Bankbill::find()->where(['loan_id' => $loan_id])->one();
//                            if (!empty($bank_bill)) {
//                                $bank_bill->updateBankbill(['status' => 'REJECT']);
//                            }
//                        }
                    }
                }
            }
        }

        // 白名单用户
        $ups = ['prome_status' => 10, 'last_modify_time' => date('Y-m-d H:i:s')];
        $where = ['loan_id' => $loan_ids, 'prome_status' => 11, 'status' => 5];
        $nums2 = User_loan::updateAll($ups, $where);

        return $nums;
    }

    /**
     * 反欺诈---结果
     * @param type $loan_id
     * @return boolean|int  2：通过   1：驳回
     */
    public function sendFraudtoRule($loan_id) {
        $loan = \app\models\news\User_loan::findOne($loan_id);
        $data = [
            'user_id' => $loan->user_id,
            'loan_id' => $loan->loan_id,
        ];
        $apiSignModel = new ApiSign();
        $sign = $apiSignModel->signData($data);
        $curl = new Curl();
        Logger::dayLog('sendFraudtoRule', print_r(array($sign), true));
        if (SYSTEM_ENV == 'prod') {
            $url = "http://strategy.xianhuahua.com/api/loan/loantwo";
        } else if (SYSTEM_ENV == 'dev') {
            $url = "http://182.92.80.211:8122/api/loan/loantwo";
        } else {
            $url = "http://182.92.80.211:8122/api/loan/loantwo";
        }
        $ret = $curl->post($url, $sign);
        Logger::dayLog('sendFraudtoRule', print_r(array($loan->loan_id => $ret), true));
        $result = json_decode($ret, true);
        $isVerify = $apiSignModel->verifyData($result['data'], $result['_sign']);
        if (!$isVerify) {
            return 1; //调用验签失败，驳回
        }
        if (!empty($result)) {
            $result_data = json_decode($result['data'], true);
            if (isset($result_data['res_code']) && $result_data['res_code'] == 0) {
                return 2;
            } else {
                return 1;
            }
        }
        return 1; //返回数据json解析失败，驳回
    }

    public function sendNewFraud() {
        //1 获取欺诈初始的数据（1，0）
        $oAntiFraud = new AntiFraud;
        $result_time = date('Y-m-d H:i:00', time());
        $frauds = $oAntiFraud->getNewInit($result_time);
        $nums = is_array($frauds) ? count($frauds) : 0;
        Logger::dayLog('fraud', 'sendNewFraudResult', $result_time . ' 前5小时内', '1 从anti_fraud获取初始欺诈条数', $nums);
        if (empty($frauds)) {
            return 0;
        }
        //2 锁定状态,避免下次重复处理
        $ids = ArrayHelper::getColumn($frauds, 'id');
        $lock_nums = AntiFraud::updateAll(['model_status' => 6, 'modify_time' => date('Y-m-d H:i:s')], ['id' => $ids]);
        Logger::dayLog('fraud', 'sendNewFraudResult', $result_time . ' 前5小时内', '2 锁定auti_fraud.model_status=6条数', $lock_nums);

        //3 调用反欺诈结果，更新result_status，结束auti_fraud.model_status=6
        $update_nums = 0;
        foreach ($frauds as $fraud) {
            $result = $this->sendFraud($fraud);
            if (!$result) {
                continue;
            }
            $update_nums++;
        }
        Logger::dayLog('fraud', 'sendNewFraudResult', $result_time . ' 前5小时内', '3 更新result_status结果条数', $update_nums);
    }

    /**
     * 反欺诈数据发送
     * @param $fraud
     * @return int
     */
    private function sendFraud($fraud) {
        $result = $fraud->newLockInit();
        if (!$result) {
            return false;
        }
        $loan = \app\models\news\User_loan::findOne($fraud->loan_id);
        $data = [
            'user_id' => $loan->user_id,
            'loan_id' => $loan->loan_id,
            'aid' => 1,
            'req_id' => $fraud->id, //请求唯一标识
            'callbackurl' => Yii::$app->params['app_url'] . '/new/notifyfraud', //回调地址
        ];
        if (SYSTEM_ENV == 'prod') {
            $url = "http://strategy.xianhuahua.com/api/strategy-req/request";
        } elseif (SYSTEM_ENV == 'dev') {
            $url = "http://182.92.80.211:8122/api/strategy-req/request";
        } else {
            $url = "http://182.92.80.211:8122/api/strategy-req/request";
        }
        $apiSignModel = new ApiFrund();
        $ret = $apiSignModel->send($url, $data);
        Logger::dayLog('sendFraud', print_r(array($loan->loan_id => $ret), true));
        //$result = json_decode($ret, true);
        if ($ret['res_code'] === 0) {
            return $result = $fraud->NewSendSucc();
        }

        return false;
    }

}
