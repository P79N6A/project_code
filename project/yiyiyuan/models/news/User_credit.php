<?php

namespace app\models\news;

use app\commonapi\Apihttp;
use app\commonapi\Keywords;
use app\commonapi\Logger;
use app\models\BaseModel;
use Yii;

/**
 * This is the model class for table "yi_user_credit".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $loan_id
 * @property integer $req_id
 * @property integer $type
 * @property integer $score
 * @property integer $status
 * @property integer $res_status
 * @property string $amount
 * @property integer $days
 * @property string $interest_rate
 * @property string $crad_rate
 * @property string $invalid_time
 * @property integer $pay_status
 * @property string $uuid
 * @property string $device_tokens
 * @property integer $device_type
 * @property string $device_ip
 * @property string $res_info
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class User_credit extends BaseModel {

    public $can_max_money;
    public $user_credit_status;
    public $audit_status;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_user_credit';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'create_time'], 'required'],
            [['user_id', 'loan_id', 'req_id', 'score', 'status', 'res_status', 'days', 'shop_days', 'device_type', 'version', 'type', 'source', 'pay_status','period','installment_result'], 'integer'],
            [['amount', 'shop_amount', 'interest_rate', 'shop_interest_rate', 'crad_rate', 'shop_crad_rate'], 'number'],
            [['invalid_time', 'last_modify_time', 'create_time', 'black_box'], 'safe'],
            [['uuid', 'device_tokens'], 'string', 'max' => 128],
            [['device_ip'], 'string', 'max' => 16],
            [['res_info'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'loan_id' => 'Loan ID',
            'req_id' => 'Req ID',
            'type' => 'Type',
            'source' => 'Source',
            'score' => 'Score',
            'status' => 'Status',
            'res_status' => 'Res Status',
            'amount' => 'Amount',
            'shop_amount' => 'Shop Amount',
            'days' => 'Days',
            'shop_days' => 'Shop Days',
            'interest_rate' => 'Interest Rate',
            'shop_interest_rate' => 'Shop Interest Rate',
            'crad_rate' => 'Crad Rate',
            'shop_crad_rate' => 'Shop Crad Rate',
            'invalid_time' => 'Invalid Time',
            'pay_status' => 'Pay Status',
            'uuid' => 'Uuid',
            'device_tokens' => 'Device Tokens',
            'black_box' => 'Black Box',
            'device_type' => 'Device Type',
            'device_ip' => 'Device Ip',
            'res_info' => 'Res Info',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'version' => 'Version',
            'period' => 'Period',
            'installment_result' => 'Installment Result',
        ];
    }

    public function optimisticLock() {
        return "version";
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    /**
     * 根据user_id获取测评记录
     * @param type $user_id
     * @return type
     */
    public function getUserCreditByUserId($user_id) {
        if (empty($user_id)) {
            return NULL;
        }
        $oUsercreditResult = static::find()->where(['user_id' => $user_id])->one();
        return $oUsercreditResult;
    }

    /**
     * 查询记录根据req_id
     * @param $req_id
     * @param bool $is_array
     * @return array|null|\yii\db\ActiveRecord
     * @author 王新龙
     * @date 2018/7/9 18:54
     */
    public function getByReqId($req_id, $is_array = false) {
        if (empty($req_id) || !is_numeric($req_id)) {
            return null;
        }
        $sql = self::find()->where(['req_id' => $req_id]);
        if ($is_array) {
            $sql = $sql->asArray();
        }
        return $sql->one();
    }

    /**
     * 更新记录
     * @param $condition
     * @return bool
     * @author 王新龙
     * @date 2018/7/9 18:55
     */
    public function updateUserCredit($condition) {
        if (empty($condition) || !is_array($condition)) {
            return false;
        }
        $time = date('Y-m-d H:i:s');
        $data = $condition;
        $data['last_modify_time'] = $time;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 新增记录
     * @param $condition
     * @return bool
     */
    public function addUserCredit($condition) {
        if (empty($condition) || !is_array($condition)) {
            return false;
        }
        $time = date('Y-m-d H:i:s');
        $data = $condition;
        $data['last_modify_time'] = $time;
        $data['create_time'] = $time;
        $data['version'] = 0;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 更新记录为初始状态
     * @param $creditNo
     * @return bool
     */
    public function updateInit($condition) {
        if (empty($condition) || !is_array($condition)) {
            return false;
        }
        $time = date('Y-m-d H:i:s');
        $data = $condition;
        $data['order_id'] = null;
        $data['score'] = 0;
        $data['status'] = 1;
        $data['res_status'] = null;
        $data['amount'] = null;
        $data['shop_amount'] = null;
        $data['days'] = null;
        $data['shop_days'] = null;
        $data['interest_rate'] = null;
        $data['shop_interest_rate'] = null;
        $data['crad_rate'] = null;
        $data['shop_crad_rate'] = null;
        $data['invalid_time'] = null;
        $data['res_info'] = null;
        $data['last_modify_time'] = $time;
        $data['create_time'] = $time;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 检查是否可以评测（修改任何一亿元资料即可通过）
     * @param $userId   一亿元用户id
     * @param $creditLastTime
     * @return bool
     */
    public function chkCreditByMaterial($user_id, $creditLastTime) {
        $creditLastTime = strtotime($creditLastTime);
        //选填资料
        $selection0bj = (new Selection())->getNewestHistory($user_id);
        if (!empty($selection0bj)) {
            $selection_last_time = strtotime($selection0bj->last_modify_time);
            if ($creditLastTime < $selection_last_time) {
                return true;
            }
        }
        //银行流水
//        $o_selection_bankflow = (new Selection_bankflow())->getByUserId($user_id);
//        if(!empty($o_selection_bankflow)){
//            $selection_bankflow_last_time = strtotime($o_selection_bankflow->last_modify_time);
//            if($o_selection_bankflow->process_code == '10008' && $creditLastTime < $selection_bankflow_last_time){
//                return true;
//            }
//        }
        //亿元信用卡
        $userBankObj = (new User_bank())->getCreditCardInfo($user_id);
        if (!empty($userBankObj)) {
            $bank_last_time = strtotime($userBankObj->last_modify_time);
            if ($creditLastTime < $bank_last_time) {
                return true;
            }
        }
        return false;
    }

    /**
     * 检查是否可以评测
     * @return bool
     */
    public function chkCredit($fillIn = false, $user_id, $loan_id, $user_credit_status) {
        if (empty($user_id)) {
            return FALSE;
        }
        //评测中
        if (in_array($this->status, [1, 3])) {
            return false;
        }
        $repeatNum = (new User_loan())->isRepeatUser($user_id);
        $nowTime = date('Y-m-d H:i:s');
        if ($user_credit_status != 2) {
            //没失效,没借款
            if (!empty($this->invalid_time) && $this->invalid_time > $nowTime && empty($loan_id)) {
                if (!$fillIn && $repeatNum <= 0) {
                    return false;
                }
            }
        } else {
            if (!$fillIn && $repeatNum <= 0) {
                return false;
            }
        }


        //存在进行中的订单
        $loan = (new User_loan())->getHaveinLoan($user_id);
        if ($loan != 0) {
            return false;
        }

        if ($repeatNum == 0) {
            $oUserRejectLoan = (new User_loan())->getLastRejectLoan($user_id);
            $smallTime = date('Y-m-d H:i:s', strtotime('- 24 hours'));
            if (!empty($oUserRejectLoan) && $oUserRejectLoan->last_modify_time > $smallTime && !$fillIn) {
                return false;
            }
        }
        return true;
    }

    /**
     * 检查亿元评测状态
     * @return user_credit_status 1:未测评;2已测评不可借;3:评测中;4:已测评可借未购买;5:已测评可借已购买;6:已过期;
     */
    public function checkYyyUserCredit($user_id) {
        if (empty($user_id)) {
            return NULL;
        }
        $oUserCredit = static::find()->where(['user_id' => $user_id])->one();
        $yyyCredit = $this->getYyyCredit($oUserCredit);
        $invalid_time = 0;
        if (empty($oUserCredit) || $oUserCredit->status == 0) {
            //未评测
            $user_credit_status = 1;
        } elseif (in_array($oUserCredit->status, [1, 3])) {
            //评测中
            $user_credit_status = 3;
        } elseif ($oUserCredit->status == 2 && $oUserCredit->res_status == 2) {
            //已评测，不可借
            $user_credit_status = 2;
            $invalid_time = $oUserCredit->last_modify_time;
        } elseif ($oUserCredit->status == 2 && $oUserCredit->res_status == 1 && $oUserCredit->pay_status == 0 && $yyyCredit) {
            //已评测，可借 ,未购卡
            $user_credit_status = 4;
            $invalid_time = $oUserCredit->invalid_time;
        } elseif ($oUserCredit->status == 2 && $oUserCredit->res_status == 1 && $oUserCredit->pay_status == 0 && !$yyyCredit) {
            //已评测，可借 ,未购卡,一亿元额度数据不完整
            $user_credit_status = 2;
            $invalid_time = $oUserCredit->last_modify_time;
        } elseif ($oUserCredit->status == 2 && $oUserCredit->res_status == 1 && $oUserCredit->pay_status == 1) {
            //已评测，可借 ,已购卡
            $user_credit_status = 5;
            $invalid_time = $oUserCredit->invalid_time;
        }

        if (!empty($oUserCredit->invalid_time)) {
            if ($oUserCredit->invalid_time < date('Y-m-d H:i:s')) {
                //已过期
                $user_credit_status = 6;
            }
        }
        if (!empty($oUserCredit->loan_id)) {
            $user_credit_status = 6; //重新评测
        }
        $user_credit = [
            'order_amount' => empty($oUserCredit->amount) ? Keywords::getMaxCreditAmounts() : $oUserCredit->amount,
            'user_credit_status' => $user_credit_status,
            'user_credit_zrys_status' => 1,
            'invalid_time' => $invalid_time,
            'days' => !empty($oUserCredit) ? $oUserCredit->days : '56',
            'period' => (!empty($oUserCredit) && !empty($oUserCredit->period)) ? $oUserCredit->period : 1,
        ];
        return $user_credit;
    }

    //评测失效
    public function Invalid($user_id) {
        if (empty($user_id)) {
            return NULL;
        }
        $oUserCredit = static::find()->where(['user_id' => $user_id])->one();
        $user_credit_status = 0;
        if (!empty($oUserCredit->invalid_time)) {
            if ($oUserCredit->invalid_time < date('Y-m-d H:i:s')) {
                //已过期
                $user_credit_status = 6;
            }
        }
        return $user_credit_status;
    }

    //修改智融钥匙返回状态  返回首页
    public function GetZrysUserCreditNew($zrys_usercredit_status) {
        //未评测  1:未测评;2已测评不可借;3:评测中;4:已测评未购买;5:已测评已购买;6:已过期;7:存在未支付的白条;8:存在处理中的退卡
        if ($zrys_usercredit_status != 5) {
            return 0; //返回首页
        } else {
            return 1; //不返回首页
        }
    }

    /**
     * 评测驳回导流
     * @param $user_id
     * @return array
     * @author 王新龙
     * @date 2018/8/1 15:38
     */
    public function getCreditReject($user_id, $source = 4) {
        if (empty($user_id) || !is_numeric($user_id)) {
            return null;
        }
        $o_user = (new User())->getById($user_id);
        if (empty($o_user)) {
            return null;
        }
        $o_user_credit = $this->getUserCreditByUserId($user_id);
        if (empty($o_user_credit)) {
            return null;
        }
        //评测未完成 or 评测不是驳回
        if ($o_user_credit->status != 2 || $o_user_credit->res_status != 2) {
            return null;
        }
        //是否已弹过
        $o_scan_times = (new BehaviorRecord())->getByLoanId($o_user_credit->req_id, 7);
        if (!empty($o_scan_times)) {
            return null;
        }
        if ($o_user_credit->status == 2 && $o_user_credit->res_status == 2) {
            $behavior_record_data = array(
                'user_id' => $o_user->user_id,
                'loan_id' => $o_user_credit->req_id,
                'type' => 7,
            );
            (new BehaviorRecord())->addList($behavior_record_data);
            //监管进场不弹窗口
            if (Keywords::inspectOpen() == 2) {
                return array(
                    'is_reject' => 0,
                    'guide_url' => '',
                    'is_selection' => 0,
                    'reject_data' => array()
                );
            }
            if ($source == 5) {
                $guide_url = 'http://www.youxinyouqian.com/dev/traffic/diversion';
            } else {
                $guide_url = Yii::$app->params['youxin_url'] . '?utm_source=reject&channel=reject&phone=' . $o_user->mobile;
            }
            $reject_arr = [
                'is_reject' => 1,
                'guide_url' => $guide_url,
                'reject_data' => ['审核不通过', $o_user_credit->last_modify_time, '评分不足'],
                'is_selection' => 0
            ];
            return $reject_arr;
        }
        return null;
    }

    /**
     * 5天未提现引导
     * @param $user_id
     * @return array
     * @author 金帅
     * @date 2018/8/1 15:38
     */
    public function getCreditFiveOverReject($user_id) {
        if (empty($user_id) || !is_numeric($user_id)) {
            return null;
        }

        $oPayAccount = PayAccountError::find()->where(['user_id' => $user_id, 'type' => 6, 'status' => 0, 'res_code' => 'fivedayover'])->one();
        //是否已弹过
        if (empty($oPayAccount)) {
            return null;
        }

        $reject_arr = [
            'is_reject' => 1,
            'reject_data' => ['借款失效', $oPayAccount->create_time, '超过5天未提现'],
            'guide_url' => '',
            'is_selection' => 0
        ];
        $oPayAccount->updateStatusSuccess();
        return $reject_arr;
    }

    /**
     * 监测是否有新可用评测,并返回评测数据
     * @param $o_user
     * @return array|bool
     */
    public function checkCanCredit($o_user) {//@todo
        if (empty($o_user) || !is_object($o_user)) {
            return false;
        }
        $o_user_credit = $this->getUserCreditByUserId($o_user->user_id);
        if(empty($o_user_credit)){
            return FALSE;
        }
        if($o_user_credit->status != 2 && $o_user_credit->res_status != 1){
            return FALSE;
        }
        if(!empty($o_user_credit->loan_id)){
            return FALSE;
        }
        if($o_user_credit->invalid_time <= date('Y-m-d H:i:s')){
            return FALSE;
        }
        if($o_user_credit->pay_status != 1){
            return FALSE;
        }
        return $o_user_credit;
    }

    /**
     * 一亿元评测结果
     * @param type $o_user_credit
     * @return boolean true:一亿元评测数据完整  false:一亿元评测数据不完整
     */
    public function getYyyCredit($o_user_credit) {
        if (empty($o_user_credit->amount) || empty($o_user_credit->days) || empty($o_user_credit->interest_rate) || empty($o_user_credit->crad_rate)) {
            return false;
        }
        return true;
    }

    /**
     * 判断一亿元评测是否是驳回（包括一亿元无额度时的假驳回）
     * @param type $oCredit
     * @return boolean true：驳回 false:不是驳回
     */
    public function getCreditRejectReturn($oCredit){
        $rejectCredit = false;
        $yyyCredit = $this->getYyyCredit($oCredit);
        if( $oCredit['status'] == 2 && $oCredit['res_status'] == 2  ){
            $rejectCredit = true;
        }elseif($oCredit['status'] == 2 && $oCredit['res_status'] == 1 && $oCredit['pay_status'] == 0 && !$yyyCredit){
            $rejectCredit = true;
        }
        return $rejectCredit;
    }

    /**
     * 商城评测结果
     * @param type $o_user_credit
     * @return boolean
     */
    public function getShopCredit($o_user_credit) {
        if (empty($o_user_credit->shop_amount) || empty($o_user_credit->shop_days) || empty($o_user_credit->shop_interest_rate) || empty($o_user_credit->shop_crad_rate)) {
            return false;
        }
        return true;
    }

    /**
     * 判断先花商城是否生成了订单，但一亿元借款还是初始借款
     * @param type $user
     * @return boolean true:无商城订单可发起评测 false:有订单不可发起
     */
    public function getshopOrder($user) {
        $apiHttp = new Apihttp();
        $payResult = $apiHttp->getCancreditByShoporder(['mobile' => $user->mobile, 'source' => 1]);
        if ($payResult['rsp_code'] == '0000' || empty($payResult)) {
            return true;
        }
        return false;
    }

    /**
     * 推送智荣钥匙评测状态
     * @param $userLoanExtendObj
     * @return string
     */
    public function postCreditStatus($oUserCredit, $oUser, $crad_mondy) {
        if (empty($oUserCredit)) {
            return false;
        }
        if ((empty($oUserCredit['amount']) || $oUserCredit['amount'] == 0) && $oUserCredit['source'] == 2 && $oUserCredit['status'] == 2 && $oUserCredit['res_status'] == 1) {
            $res_status = 2; //如果亿元没有额度的话通知智融驳回
        } else {
            $res_status = $oUserCredit['res_status'];
        }
        $contacts = [
            'realname' => $oUser->realname,
            'identity' => $oUser->identity,
            'res_status' => $res_status, //默认不可借
            'req_id' => $oUserCredit['req_id'],
            'loan_amount' => $oUserCredit['amount'], //借款金额
            'amount' => $crad_mondy, //服务卡金额
            'source' => $this->getSource($oUserCredit['source']), //1:亿元发起的评测2：智融发起的评测'
            'days' => $oUserCredit['days'],
            'invalid_time' => $oUserCredit['invalid_time'],
            'callback_url' => Yii::$app->params['signal_notify_url'], //回调地址
            'user_mobile' => $oUser->mobile, //手机号
            'come_from' => $oUserCredit->device_type, //评测来源
            'type' => 1, //代表亿元
            'platform_id' => 1,
        ];
        Logger::dayLog('notify/tuisong', '有信令推送', 'req_ID：' . $oUserCredit['req_id'], $contacts);
        $api = new Apihttp();
        $result = $api->postCreditStatus($contacts);
        if ($result['rsp_code'] != '0000') {
            Logger::dayLog('notify/pushcreditstatus', '有信令推送credit_status失败', 'req_ID：' . $oUserCredit['req_id'], $contacts, $result);
            return false;
        }
        return true;
    }

    public function getSource($source) {
        if ($source == 3 || $source == 4) {//如果是商城发起的评测默认是亿元发送的order
            $source = 1;
        }
        return $source;
    }

    /**
     * 查询记录，根据loan_id
     * @param $loan_id
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getByLoanId($loan_id){
        if(empty($loan_id)){
            return null;
        }
        return self::find()->where(['loan_id'=>$loan_id])->one();
    }
    
    /*
     * 判断风控返回的结果是否符合分期规则
     * 2018 12 18
     */
    public function getPeriodReject($credit_subject){
        //检测金额
        if( empty($credit_subject['AMOUNT']) ){
            Logger::dayLog('notify/credit', '数据与分期规则冲突：amount为空');
            return false;
        }
        $can_period = ($credit_subject['AMOUNT']/$credit_subject['period'] ) %500 ;
        if( $can_period !=0 ){
             Logger::dayLog('notify/credit', '数据与分期规则冲突：金额与500取余不等于0',$can_period,$credit_subject['AMOUNT'],$credit_subject['period']);
             return false;
        }
        
        return true;
    }
}
