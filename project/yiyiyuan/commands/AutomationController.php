<?php

/**
 * 自动化推送消息和获取消息结果
 */
/**
 *   linux : /data/wwwroot/yiyiyuan/yii automation sent
 *   window : d:\xampp\php\php.exe D:\www\yiyiyuan\yii automation sent
 */

namespace app\commands;

use app\commands\BaseController;
use app\commonapi\Logger;
use app\commonapi\Switchs;
use app\models\dev\AntiAutomation;
use app\models\dev\ApiSms;
use app\models\dev\Payaccount;
use app\models\dev\User;
use app\models\dev\User_loan;
use app\models\dev\User_loan_extend;
use app\models\dev\White_list;
use app\models\Flow;
use app\models\news\User_loan_flows;
use Yii;
use yii\helpers\ArrayHelper;

class AutomationController extends BaseController {
    //private $today;

    /**
     * 初始化
     */
    public function init() {
        parent::init();
    }

    /**
     * 5分钟轮询
     * 推送自动化数据
     */
    public function sent() {
        //1 获取自动化的数据
        $now = time();
        $dataStart = date('Y-m-d H:i:00', $now - 86400);
        $dataEnd = date('Y-m-d H:i:00', $now - 600);

        $where = [
            'AND',
            ['status' => 5],
            ['prome_status' => 10],
            ['>=', 'create_time', $dataStart],
            ['<', 'create_time', $dataEnd],
        ];
        $data = User_loan::find()->where($where)->limit(1000)->all();
        $nums = is_array($data) ? count($data) : 0;
        Logger::dayLog('automation', 'send', $dataStart . ' to ' . $dataEnd, '1 获取user_loan条数', $nums);
        if (empty($data)) {
            return 0;
        }

        //2 锁定状态,避免下次重复处理
        $loan_ids = ArrayHelper::getColumn($data, 'loan_id');
        $nums = User_loan::updateAll(['prome_status' => 21, 'last_modify_time' => date('Y-m-d H:i:s')], ['loan_id' => $loan_ids]);
        Logger::dayLog('automation', 'send', $dataStart . ' to ' . $dataEnd, '2 锁定user_loan.prome_status=21条数', $nums);

        //2.2 更新反欺诈通过的bankbill进行获取账单状态WAIT
        $bill_nums = \app\models\news\Bankbill::updateAll(['status' => 'WAIT', 'last_modify_time' => date('Y-m-d H:i:s')], ['loan_id' => $loan_ids]);
        Logger::dayLog('automation', 'send', $dataStart . ' to ' . $dataEnd, '2.2更新反欺诈通过的bankbill进行获取账单状态WAIT', $bill_nums);

        //3 根据白名单区分借款走向
        $autoData = [];
        foreach ($data as $v) {
            //判断是否是白名单用户
            $userinfo = User::findOne($v['user_id']);
            $whiteModel = new White_list();
            $isWhite = $whiteModel->isWhiteList($userinfo['identity']);
            if ($isWhite) {
                //是白名单用户，进入自动化流程
                $type = 2;
            } else {
                //不是白名单，进入释放流程
                $type = 1;
            }

            $autoData[] = [
                'user_id' => $v['user_id'],
                'loan_id' => $v['loan_id'],
                'type' => $type,
            ];
        }
        $nums = AntiAutomation::addBatchByUsers($autoData);
        //4 写日志
        Logger::dayLog('automation', 'send', $dataStart . ' to ' . $dataEnd, '3插入到anti_automation条数', $nums);
        return $nums;
    }

    /**
     * 每5分钟轮询
     * 获取自动化跑完的数据，并且是未通过的，立即释放
     * yii automation receive
     */
    public function release() {
        //1 获取自动化的数据
        $oAntiAuto = new AntiAutomation;
        $result_time = date('Y-m-d H:i:00', time() - 300); //半小时之前, 因为有缓冲
        $automations = $oAntiAuto->getRelease($result_time);
        $nums = is_array($automations) ? count($automations) : 0;
        Logger::dayLog('automation', 'release', $result_time . ' 前10小时缓冲', '1 从anti_automation获取欺诈条数', $nums);
        if (empty($automations)) {
            return 0;
        }

        //2 锁定状态,避免下次重复处理
        $ids = ArrayHelper::getColumn($automations, 'id');
        $nums = AntiAutomation::updateAll(['model_status' => 4, 'modify_time' => date('Y-m-d H:i:s')], ['id' => $ids]);
        Logger::dayLog('automation', 'release', $result_time . ' 前10小时缓冲', '2 锁定anti_automation.model_status=4条数', $nums);

        //3 通过,即进入人工信审中
        $nums = $this->updateNoAutoData($automations);
        Logger::dayLog('automation', 'release', $result_time . ' 前12小时缓冲', '3 终止user_loan.prome_status=5条数', $nums);

        //4 结束自动化整体流程
        $nums = AntiAutomation::updateAll(['model_status' => 5, 'modify_time' => date('Y-m-d H:i:s')], ['id' => $ids]);
        Logger::dayLog('automation', 'release', $result_time . ' 前10小时缓冲', '4 终止anti_automation.model_status=5条数', $nums);

        return $nums;
    }

    /**
     * 每5分钟轮询
     * 获取自动化跑完的数据，并且是未通过的，立即释放
     * yii automation receive
     */
    public function reject() {
        //1 获取自动化的数据
        $oAntiAuto = new AntiAutomation;
        $result_time = date('Y-m-d H:i:00', time() - 300); //5分钟之前, 因为有缓冲
        $automations = $oAntiAuto->getReject($result_time);
        $nums = is_array($automations) ? count($automations) : 0;
        Logger::dayLog('automation', 'reject', $result_time . ' 前10小时缓冲', '1 从anti_automation获取欺诈条数', $nums);
        if (empty($automations)) {
            return 0;
        }

        //2 锁定状态,避免下次重复处理
        $ids = ArrayHelper::getColumn($automations, 'id');
        $nums = AntiAutomation::updateAll(['model_status' => 4, 'modify_time' => date('Y-m-d H:i:s')], ['id' => $ids]);
        Logger::dayLog('automation', 'reject', $result_time . ' 前10小时缓冲', '2 锁定anti_automation.model_status=4条数', $nums);

        //3 驳回
        $rejectData = $this->updateReject($automations);
        if ($rejectData) {
            Logger::dayLog('automation', 'reject', $result_time . ' 前1小时内', '3 自动化审核驳回失败的数据: user_loan.prome_status=21条数', $rejectData);
        } else {
            Logger::dayLog('automation', 'reject', $result_time . ' 前12小时缓冲', '3 终止user_loan.status=7,user_loan.prome_status=5条数', $nums);
        }

        //4 结束自动化整体流程
        $nums = AntiAutomation::updateAll(['model_status' => 5, 'modify_time' => date('Y-m-d H:i:s')], ['id' => $ids]);
        Logger::dayLog('automation', 'reject', $result_time . ' 前10小时缓冲', '4 终止anti_automation.model_status=5条数', $nums);

        return $nums;
    }

    /**
     * 每次出款前3分钟轮询，即(每小时27分，57分时执行)
     * 获取自动化跑完的数据，并且是通过的，根据一个固定的阀值进行判断
     * yii automation
     */
    public function pass() {
        //1 获取自动化的数据
        $oAntiAuto = new AntiAutomation;
        $result_time = date('Y-m-d H:i:00'); //当前分钟之前
        $normals = $oAntiAuto->getNormal($result_time, 2); //取复贷借款
        $nums = is_array($normals) ? count($normals) : 0;
        Logger::dayLog('automation', 'pass', $result_time . ' 前1小时内', '1 从anti_automation获取正常条数', $nums);
        if (empty($normals)) {
            return 0;
        }

        //2 锁定状态,避免下次重复处理
        $ids = ArrayHelper::getColumn($normals, 'id');
        $nums = AntiAutomation::updateAll(['model_status' => 4, 'modify_time' => date('Y-m-d H:i:s')], ['id' => $ids]);
        Logger::dayLog('automation', 'pass', $result_time . ' 前1小时内', '2 锁定anti_automation.model_status=4条数', $nums);

        //3 处理模型通过的数据
        $nums = $this->checkPass($normals, $result_time);

        //4 结束自动化整体流程
        $nums = AntiAutomation::updateAll(['model_status' => 5, 'modify_time' => date('Y-m-d H:i:s')], ['id' => $ids]);
        Logger::dayLog('automation', 'pass', $result_time . ' 前1小时内', '4 终止anti_automation.model_status=5条数', $nums);
    }

    /**
     * 每次出款前3分钟轮询，即(每小时27分，57分时执行)
     * 获取自动化跑完的数据，并且是通过的，根据一个固定的阀值进行判断
     * yii automation
     */
    public function passFirst() {
        //1 获取自动化的数据
        $oAntiAuto = new AntiAutomation;
        $result_time = date('Y-m-d H:i:00'); //当前分钟之前
        $normals = $oAntiAuto->getNormal($result_time, 1); //取初贷
        $nums = is_array($normals) ? count($normals) : 0;

        Logger::dayLog('automation', 'passFirst', $result_time . ' 前1小时内', '1 从anti_automation获取正常条数', $nums);
        if (empty($normals)) {
            return 0;
        }

        //2 锁定状态,避免下次重复处理
        $ids = ArrayHelper::getColumn($normals, 'id');
        $nums = AntiAutomation::updateAll(['model_status' => 4, 'modify_time' => date('Y-m-d H:i:s')], ['id' => $ids]);
        Logger::dayLog('automation', 'passFirst', $result_time . ' 前1小时内', '2 锁定anti_automation.model_status=4条数', $nums);

        //3 处理评分模型通过的数据
        $noPassLoan = [];
        foreach ($normals as $key => $val) {
            $loan_id = $val->loan_id;
            $user_id = $val->user_id;

            $payInfo = $this->getPayInfo($loan_id);
            $outstatus = 'TB-AUTHED';
            $userinfo = User::find()->where(['user_id' => $user_id])->limit(1)->one();
            $sms = new ApiSms;
            $outmoney = 1;
            if ($userinfo['status'] != '3') {
                $outmoney = 0;
                $outstatus = 'WILLAUTHED';
                $sms->sendCallUserHuotiSms($userinfo['mobile']);
            }
            $is_rb = 1;
            if ($is_rb) {
                $ret = $this->disposeOne($loan_id, -3, 6, 5, $payInfo['fund'], $outstatus, $payInfo['payment_channel'], $outmoney);
                if ($ret && $ret != 'SUCCESS') {
                    $noPassLoan[] = $loan_id;
                    continue;
                }
            } else {
                //1.新浪开户
                $payaccount = new Payaccount;
                $checkOpen = $payaccount->sinaOpenAcc($user_id, $loan_id);

                if ($checkOpen == 'FRIST SUCCESS') {
                    //审核通过，默认为新浪且不可出款
                    $ret = $this->disposeOne($loan_id, -3, 6, 5, $payInfo['fund'], $outstatus, $payInfo['payment_channel']);
                    if ($ret && $ret != 'SUCCESS') {
                        $noPassLoan[] = $loan_id;
                        continue;
                    }
                    //发送提醒短信和微信
                    $userinfo = User::find()->where(['user_id' => $user_id])->limit(1)->one();
                    if ($userinfo) {
                        $smsRet = $sms->sendOpenAccSms($userinfo['mobile']);
                        if (!$smsRet) {
                            Logger::dayLog('OpenSinaAcc', 'FAIL', $result_time . ' 前1小时内', '自动化审核通过成功，开户成功，短信发送失败: ', $loan_id, $user_id);
                        }
                    }
                } elseif ($checkOpen == 'ALL SUCCESS') {
                    //审核通过，用户以前设置过支付密码，直接通过且可出款
                    $ret = $this->disposeOne($loan_id, -3, 6, 5, $payInfo['fund'], $outstatus, $payInfo['payment_channel'], $outmoney);
                    if ($ret && $ret != 'SUCCESS') {
                        $noPassLoan[] = $loan_id;
                        continue;
                    }
                } else {
                    //审核通过，默认为新浪且不可出款
                    $ret = $this->disposeOne($loan_id, -3, 6, 5, $payInfo['fund'], $outstatus, $payInfo['payment_channel']);
                    if ($ret && $ret != 'SUCCESS') {
                        $noPassLoan[] = $loan_id;
                        continue;
                    }
                    //怎么处理？？？？？有可能第一次成功，第二次失败，也可能第一次失败,也可能是保存数据失败
                    Logger::dayLog('OpenSinaAcc', 'FAIL', $result_time . ' 前1小时内', '自动化审核通过成功，开户失败: ', $loan_id, $user_id);
                }
            }
        }
        if ($noPassLoan) {
            Logger::dayLog('automation', 'passFirst', $result_time . ' 前1小时内', '3 自动化审核通过失败的数据: user_loan.prome_status=21条数', $noPassLoan);
        } else {
            Logger::dayLog('automation', 'passFirst', $result_time . ' 前1小时内', '3 自动化审核通过: user_loan.prome_status=5条数', $nums);
        }

        //4 结束自动化整体流程
        $nums = AntiAutomation::updateAll(['model_status' => 5, 'modify_time' => date('Y-m-d H:i:s')], ['id' => $ids]);
        Logger::dayLog('automation', 'passFirst', $result_time . ' 前1小时内', '4 终止anti_automation.model_status=5条数', $nums);
    }

    /**
     * 进行释放操作，进入人工信审
     * @param  [type] $loan_id [description]
     * @return [type]          [description]
     */
    private function updateNoAutoData($normals, $prome_status = 5) {
        // 修改prome_status = 10 进入prome
        $loan_ids = ArrayHelper::getColumn($normals, 'loan_id');
        $loan_ids = array_unique($loan_ids);
        $ups = ['prome_status' => $prome_status, 'last_modify_time' => date('Y-m-d H:i:s')];
        $where = ['loan_id' => $loan_ids, 'prome_status' => 21, 'status' => 5];
        $nums = User_loan::updateAll($ups, $where);
        return $nums;
    }

    /**
     * 进行通过操作
     * @param  [type] $loan_id [description]
     * @return [type]          [description]
     */
    private function checkPass($normals, $result_time) {
        //控制阀值，normal(09:30-22:00),warn(22：00-09:30)
        $switchVal = Switchs::getSwitchVal();
        $normalNum = $switchVal['normalNum'];
        $warnNum = $switchVal['warnNum'];
        $normalAmount = $switchVal['normalAmount'];
        $warnAmount = $switchVal['warnAmount'];
        $warnStartTime = $switchVal['warnStartTime'];
        $warnEndTime = $switchVal['warnEndTime'];
        $nowTime = date('Y-m-d H:i');

        $nums = is_array($normals) ? count($normals) : 0;
        if (empty($normals)) {
            return 0;
        }
        $pass = [];
        $noPass = [];
        foreach ($normals as $key => $val) {
            $user_id = $val->user_id;
            $pass[] = $val;
            // $payaccount = new Payaccount;
            // $payacc = $payaccount->checkOpenAcc($user_id);//确认新浪开户是否成功
            // if( $payacc ){
            //     $pass[] = $val;
            // }else{
            //     $noPass[] = $val;
            // }
        }
        //新浪账户未开通的不处理
        // if( $noPass ){
        //     $nums = $this->updateNoAutoData($noPass);
        //     Logger::dayLog('automation', 'pass', $result_time. ' 前1小时内',  '3 未开通新浪账户进入人工审核: user_loan.prome_status=22条数', $nums);
        // }
        //新浪账户开通成功的
        if ($pass) {
            $loan_ids = ArrayHelper::getColumn($pass, 'loan_id');
            $loan_ids = array_unique($loan_ids);
            $nums = is_array($pass) ? count($pass) : 0;
            $totalAmount = User_loan::find()->where(['loan_id' => $loan_ids])->sum('amount');

            if ($nowTime > $warnStartTime && $nowTime < $warnEndTime) {
                //正常时间段
                if ($nums > $normalNum || $totalAmount > $normalAmount) {
                    //不做处理，由信审人员跟进
                    $nums = $this->updateNoAutoData($pass);
                    Logger::dayLog('automation', 'pass', $result_time . ' 前1小时内', '3 超阀值进入特殊人工审核: user_loan.prome_status=22条数', $nums);
                } else {
                    //自动通过
                    $passRet = $this->updatePass($pass);
                    if ($passRet) {
                        Logger::dayLog('automation', 'pass', $result_time . ' 前1小时内', '3 自动化审核通过失败的数据: user_loan.prome_status=21条数', $passRet);
                    } else {
                        Logger::dayLog('automation', 'pass', $result_time . ' 前1小时内', '3 自动化审核通过: user_loan.prome_status=5条数', $nums);
                    }
                }
            } else {
                //非常时间段
                if ($nums > $warnNum || $totalAmount > $warnAmount) {
                    //不做处理，由信审人员跟进
                    $nums = $this->updateNoAutoData($pass);
                    Logger::dayLog('automation', 'pass', $result_time . ' 前1小时内', '3 超阀值进入特殊人工审核: user_loan.prome_status=22条数', $nums);
                } else {
                    //自动通过
                    $passRet = $this->updatePass($pass);
                    if ($passRet) {
                        Logger::dayLog('automation', 'pass', $result_time . ' 前1小时内', '3 自动化审核通过失败的数据: user_loan.prome_status=21条数', $passRet);
                    } else {
                        Logger::dayLog('automation', 'pass', $result_time . ' 前1小时内', '3 自动化审核通过: user_loan.prome_status=5条数', $nums);
                    }
                }
            }
        }

        return $nums;
    }

    public function updatePass($normals) {
        $loan_ids = ArrayHelper::getColumn($normals, 'loan_id');
        $loan_ids = array_unique($loan_ids);
        $payment_channel = 1; //自动化通过的借款默认为新浪出款
        $relative = '自动化审核';

        $noPassLoan = [];
        //逐条处理借款为通过
        foreach ($normals as $normal) {
            $loan_id = $normal->loan_id;
            $user_id = $normal->user_id;

            $payInfo = $this->getPayInfo($loan_id);
            $outstatus = 'TB-AUTHED';

            $ret = $this->disposeOne($loan_id, -2, 6, 5, $payInfo['fund'], $outstatus, $payInfo['payment_channel'], 1);
            if ($ret && $ret != 'SUCCESS') {
                $noPassLoan[] = $loan_id;
            }
        }

        return $noPassLoan;
    }

    private function getPayInfo($loanId = 0) {

        $result = [
            'fund' => 1,
            'payment_channel' => 112,
        ];
        if (!$loanId) {
            return $result;
        }
        $loaninfo = User_loan::find()->where(['loan_id' => $loanId])->one();
        //计算时间点，给玖富推单
        $nowTime = date('Y-m-d H:i');
        $startTime = date('Y-m-d') . ' 00:30';
        $endTime = date('Y-m-d') . ' 19:30';
        //周一到周五
        $weekday = date('w');
        if (in_array($weekday, ['1', '2', '3', '4', '5'])) {
            //满足条件给玖富推单时，资方为2，出款方为融宝
            if ($loaninfo->is_calculation == 1 && $loaninfo->days == '28') {
                $result['fund'] = 2;
                $result['payment_channel'] = 0;
            }
        }

        // 金联储
        // if('2017-09-28 22:00'<$nowTime && $nowTime <'2017-09-29 09:00'){
        //         $result['fund'] = 4;
        //         $result['payment_channel'] = 0;
        // }
        // 连交所
        /* if ('2017-11-01 10:00' < $nowTime && $nowTime < '2017-11-01 11:30' && $loaninfo->days == '28') {
          $result['fund'] = 3;
          $result['payment_channel'] = 110;
          }
          //小诺
          if ('2017-11-07 00:10' <= $nowTime && $nowTime < '2017-11-07 08:30' && $loaninfo->days == '28') {
          $result['fund'] = 5;
          $result['payment_channel'] = 0;
          } */
        //微神马
        $startTime = date('Y-m-d') . ' 05:00';
        $endTime = date('Y-m-d') . ' 09:30';
        if (in_array($weekday, ['1', '2', '3', '4', '5']) &&
                $startTime <= $nowTime &&
                $nowTime < $endTime &&
                $loaninfo->is_calculation == 1 &&
                $loaninfo->days == '28') {

            $result['fund'] = 6;
            $result['payment_channel'] = 0;
        }
        if (('2017-11-13 20:00' < $nowTime && $nowTime < '2017-11-13 23:30') || ('2017-11-16 09:30' < $nowTime && $nowTime < '2017-11-16 22:00')
        ) {
            $oPayaccount = new \app\models\news\Payaccount;
            $isOpen = $oPayaccount->isOutByCunguan($loaninfo);
            if ($isOpen) {
                $result['fund'] = 10;
                $result['payment_channel'] = 0;
            }
        }

        /* if (in_array($weekday, ['0', '1', '5', '6'])) {
          $result['payment_channel'] = 114;
          } */
        /*
          $payStartTime = date('Y-m-d') . ' 00:30';
          $payEndTime   = date('Y-m-d') . ' 12:00';
          if ($nowTime > $payStartTime && $nowTime < $payEndTime) {
          $result['payment_channel'] = 112;
          }

          // 改成宝付
          $result['payment_channel'] = 114;
         */
        /* if ($nowTime > "2017-08-14 00:00" && $nowTime < "2017-08-14 10:00") {
          $result['fund']            = 4;
          $result['payment_channel'] = 0;
          } */
        $result = [
            'fund' => 0,
            'payment_channel' => 0,
        ];
        return $result;
    }

    /* private function getPayChannel(){
      $payment_channel = 112;
      //计算时间点
      $nowTime = date('Y-m-d H:i');
      $startTime = date('Y-m-d').' 00:30';
      $endTime = date('Y-m-d').' 11:30';
      //周一到周五
      $weekday = date('w');
      if( in_array($weekday, ['1','2','3','4','5']) ){
      //上午10点--下午2点
      if( $nowTime > $startTime && $nowTime < $endTime){
      $payment_channel = 3;
      }
      }
      return $payment_channel;
      } */

    //自动驳回
    public function updateReject($normals) {
        $loan_ids = ArrayHelper::getColumn($normals, 'loan_id');
        $loan_ids = array_unique($loan_ids);
        $userLoanFlowsModel = new User_loan_flows();
        $noPassLoan = [];
        //逐条处理借款为通过
        foreach ($normals as $normal) {
            $loan_id = $normal->loan_id;
            $user_id = $normal->user_id;

            $ret = $this->disposeOne($loan_id, -2, 7, 5);
            if ($ret && $ret != 'SUCCESS') {
                $noPassLoan[] = $loan_id;
            } else {
                if($normal->result_status == 4){
                    $userLoanFlow = $userLoanFlowsModel->getByStatusLoanId(7, $loan_id);
                    if(!empty($userLoanFlow)){
                        $userLoanFlow->updateReason('评分不足，请尝试申请更低额度或更短期限');
                    }
                }
            }
        }

        return $noPassLoan;
    }

    //处理一个借款
    private function disposeOne($loan_id, $admin_id, $status, $prome_status, $fund = 1, $outstatus = 'TB-AUTHED', $payment_channel = 112, $outmoney = 0) {

        $transaction = Yii::$app->db->beginTransaction();
        //获取用户借款信息和用户信息
        $loaninfo = User_loan::find()->where(['loan_id' => $loan_id])->limit(1)->one();
        $loanextend = User_loan_extend::find()->where(['loan_id' => $loan_id])->limit(1)->one();
        //更新借款的状态的为审核通过，status=7，prome_status=1（用户6小时内不可借款）
        $loaninfo->status = $status;
        $loaninfo->prome_status = $prome_status;
        $loaninfo->last_modify_time = date('Y-m-d H:i:s', time());
        if (!$loaninfo->save()) {
            $transaction->rollBack();
            return $loan_id;
        }
        $relative = '自动化审核';
        //记录借款状态变更流程表
        $flow = new Flow();
        $flow->loan_id = $loaninfo->loan_id;
        $flow->admin_id = $admin_id;
        $flow->admin_name = '自动化审核';
        $flow->loan_status = $status;
        $flow->relative = $relative;
        $flow->create_time = date('Y-m-d H:i:s', time());
        if (!$flow->save()) {
            $transaction->rollBack();
            return $loan_id;
        }
        //修改借款的状态为可出款
        $ups = [];
        if ($status == '6') {
            $ups['outmoney'] = $outmoney;
            $ups['extend_type'] = 2;
            $ups['payment_channel'] = $payment_channel;
            $ups['fund'] = $fund;
            $ups['status'] = $outstatus;
        } elseif ($status == '7') {
            $ups['extend_type'] = 3;
        }
        $ret = $loanextend->updateUserLoanSubsidiary($ups);
        if (!$ret) {
            $transaction->rollBack();
            return $loan_id;
        }
        $transaction->commit();

        return 'SUCCESS';
    }

}
