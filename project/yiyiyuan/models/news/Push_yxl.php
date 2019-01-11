<?php

namespace app\models\news;

use app\commonapi\Apihttp;
use app\commonapi\Logger;
use app\models\BaseModel;
use Exception;
use Yii;

/**
 * This is the model class for table "yi_push_yxl".
 *
 * @property string $id
 * @property string $user_id
 * @property string $loan_id
 * @property integer $type
 * @property integer $notify_num
 * @property integer $notify_status
 * @property string $notify_time
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class Push_yxl extends BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_push_yxl';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'loan_id', 'notify_time', 'last_modify_time', 'create_time', 'version'], 'required'],
            [['user_id', 'loan_id', 'loan_status', 'type', 'notify_num', 'notify_status', 'version'], 'integer'],
            [['notify_time', 'last_modify_time', 'create_time'], 'safe']
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
            'loan_status' => 'Loan Status',
            'type' => 'Type',
            'notify_num' => 'Notify Num',
            'notify_status' => 'Notify Status',
            'notify_time' => 'Notify Time',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }

    /**
     * 乐观所版本号
     * * */
    public function optimisticLock() {
        return "version";
    }

    public function getLoan() {
        return $this->hasOne(User_loan::className(), ['loan_id' => 'loan_id']);
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    public function getExtend() {
        return $this->hasOne(User_loan_extend::className(), ['loan_id' => 'loan_id']);
    }

    public function updateSuccess() {
        $this->notify_status = 1;
        $this->notify_num += 1;
        $this->last_modify_time = date("Y-m-d H:i:s");
        $res = $this->save();
        return $res;
    }

    public function updateError() {
        $status = 3;
        if ($this->notify_num > 6) {
            $status = 2;
        }
        $num = ($this->notify_num) + 1;
        $this->notify_time = $this->acNotifyTime($num, $this->last_modify_time);
        $this->notify_status = $status;
        $this->notify_num = $num;
        $this->last_modify_time = date("Y-m-d H:i:s");
        $res = $this->save();
        return $res;
    }

    /**
     * 计算下次查询时间
     * @param int $notify_num 当前次数
     * @param str $notify_time 当前时间
     * @return str 下次查询时间
     */
    private function acNotifyTime($notify_num, $notify_time) {
        // 累加的分钟
        $addMinutes = [
            1 => 5,
            2 => 10,
            3 => 20,
            4 => 40,
            5 => 80,
            6 => 160,
        ];

        // 不在上述时,不改变
        if (!isset($addMinutes[$notify_num])) {
            return date('Y-m-d H:i:s');
        }

        // 累加时间
        $time = ($notify_time == '0000-00-00 00:00:00') ? time() : strtotime($notify_time);
        $t = $time + $addMinutes[$notify_num] * 60;
        return date('Y-m-d H:i:s', $t);
    }

    //根据借款状态获取
    public function getYxlInfo($loan_id, $type, $loan_status) {
        if (empty($loan_id) || empty($loan_status) || empty($type)) {
            return null;
        }
        $result = self::find()->where(['loan_id' => $loan_id, 'type' => $type, 'loan_status' => $loan_status])->one();
        return $result;
    }

    public function saveYxlInfo($data) {
        try {
            if (empty($data) || !is_array($data)) {
                return false;
            }
            $condition = $data;
            $time = date('Y-m-d H:i:s');
            $condition['notify_time'] = $time;
            $condition['last_modify_time'] = $time;
            $condition['create_time'] = $time;
            $condition['version'] = 0;
            $error = $this->chkAttributes($condition);
            if ($error) {
                return false;
            }
            $result = $this->save();
        } catch (Exception $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * 推送智荣钥匙ORDER
     * @param $userLoanExtendObj
     * @return string
     */
    public function postSignal($oUserloan,$user,$oUserCredit) {
        if (empty($oUserloan) || !is_object($oUserloan) || empty($user) || empty($oUserCredit)) {
            return false;
        }
        $loanFlow = User_loan_flows::find()->where(['loan_id'=>$oUserloan->loan_id,'loan_status'=>6])->one();
        $invalid_time = date("Y-m-d H:i:s",strtotime("+23 hours 30 minutes",strtotime($loanFlow->create_time)));
        $contacts = [
            'loan_id' => $oUserloan->loan_id,
            'req_id' => $oUserCredit->req_id,
            'realname' => $user->realname,
            'identity' => $user->identity,
            'loan_amount' => $oUserloan->real_amount, //借款金额
            'amount' => $oUserloan->real_amount*$oUserCredit->crad_rate,//服务卡金额
            'user_mobile' => $user->mobile, //手机号
            'loan_time' => $oUserloan->create_time, //借款创建时间
            'invalid_time' => $invalid_time,//失效时间
            'callback_url' => Yii::$app->params['signal_notify_url'], //回调地址
            'come_from' => $oUserloan->source,//借款来源
            'source' => (new User_credit())->getSource($oUserCredit->source),//1亿元发起的评测2智融发起的评测
        ];

        $api = new Apihttp();
        $result = $api->postSignal($contacts,1);
        if ($result['rsp_code'] != '0000') {
            $this->updateError();
            Logger::dayLog('app/userloan', '有信令推送order失败', 'loan ID：' . $oUserloan->loan_id, $contacts, $result);
            return false;
        }
        $this->updateSuccess();
    }



    public function createLoanNobuy($loan) {
        $loanFlow = User_loan_flows::find()->where(['loan_id' => $loan->loan_id, 'loan_status' => 6])->one();
        $invalid_time = date("Y-m-d H:i:s", strtotime("+23 hours 30 minutes", strtotime($loanFlow->create_time)));
        $contacts = [
            'loan_id' => $loan->loan_id,
            'realname' => $loan->user->realname,
            'identity' => $loan->user->identity,
            'loan_amount' => $loan->real_amount, //借款金额
            'amount' => $loan->real_amount * 0.18, //服务卡金额
            'user_mobile' => $loan->user->mobile, //手机号
            'loan_time' => $loan->create_time, //借款创建时间
            'invalid_time' => $invalid_time, //失效时间
            'callback_url' => Yii::$app->params['signal_notify_url'], //回调地址
            'come_from' => $loan->source, //借款来源
            'source' => 1
        ];
        $api = new Apihttp();
        $result = $api->postSignal($contacts, $this->type);
        if ($result['rsp_code'] != '0000') {
            $this->updateError();
            Logger::dayLog('signal/signalpush', '有信令推送失败', 'loan ID：' . $this->loan_id, $contacts, $result);
            return false;
        }
        $update_res = $this->updateSuccess();
        if (!$update_res) {
            return FALSE;
        }
        return true;
    }

    /**
     * order记录使用状态并推送智荣钥匙
     * @param $o_user
     * @param $o_user_loan
     * @param $o_user_credit
     * @return array
     */
    public function saveUseAndSend($o_user,$o_user_loan,$o_user_credit){
        //合规进场时，评测默认支付，type记录为3
        if($o_user_credit->type != 3){
            $psuhyxl_condition = [
                'user_id' => $o_user->user_id,
                'loan_id' => $o_user_loan->loan_id,
                'loan_status' => 3,
                'type' => 1,
                'notify_status' => 0,
            ];
            $pushYxlModel = new Push_yxl();
            $push_result = $pushYxlModel->saveYxlInfo($psuhyxl_condition);
            if(empty($push_result)){
                Logger::dayLog('models/push_yxl/addUserLoanRecord', '推送智荣钥匙表记录失败', 'loan_id：' . $o_user_loan->loan_id, $psuhyxl_condition);
                return ['rsp_code' => '10051'];
            }
            $pushYxlModel->postSignal($o_user_loan, $o_user, $o_user_credit);
        }
    }
}
