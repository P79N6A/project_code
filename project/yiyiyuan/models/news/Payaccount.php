<?php

namespace app\models\news;

use app\commonapi\Keywords;
use app\models\BaseModel;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "yi_pay_account".
 *
 * @property string $id
 * @property string $user_id
 * @property integer $type
 * @property integer $step
 * @property integer $activate_result
 * @property string $activate_time
 * @property string $create_time
 * @property string $accountId
 * @property string $card
 */
class Payaccount extends BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_pay_account';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'activate_time', 'create_time'], 'required'],
            [['user_id', 'type', 'step', 'activate_result', 'isopen', 'sign'], 'integer'],
            [['activate_time', 'create_time'], 'safe'],
            [['accountId'], 'string', 'max' => 25],
            [['card', 'orderId'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '主键',
            'user_id' => '用户ID',
            'type' => '操作类型',
            'step' => '操作步骤',
            'activate_result' => '操作结果',
            'activate_time' => '最后操作时间',
            'create_time' => '创建时间',
            'accountId' => 'Account ID',
            'card' => 'Card',
            'orderId' => 'Order ID',
        ];
    }

    public function getBank() {
        return $this->hasOne(User_bank::className(), ['id' => 'card']);
    }

    public function getPayaccountextend() {
        return $this->hasOne(PayAccountExtend::className(), ['pay_account_id' => 'id']);
    }

    /**
     * 检查是否存在开户记录
     * @param $user_id
     * @param int $type 1：新浪开户 2：江西银行存管开户
     * @param int $step 1：开户 2：设置密码(激活)
     * @return array|bool|null|ActiveRecord
     */
    public function getPaystatusByUserId($user_id, $type = 1, $step = 2) {
        if (empty($user_id)) {
            return false;
        }
        $result = Payaccount::find()->where(['user_id' => $user_id, 'type' => $type, 'step' => $step])->orderBy('activate_time desc')->one();
        return $result;
    }

    /**
     * 检查是否开户/设置密码/授权 成功
     * @param $user_id
     * @param int $type 1：新浪开户 2：江西银行存管开户
     * @param int $step 1：开户 2：设置密码(激活) 3：授权 4：还款授权 5：缴费授权
     * @return array|bool|null|ActiveRecord
     */
    public function getPaysuccessByUserId($user_id, $type = 2, $step = 1) {
        if (empty($user_id)) {
            return false;
        }
        $result = Payaccount::find()->where(['user_id' => $user_id, 'type' => $type, 'step' => $step, 'activate_result' => 1])->orderBy('activate_time desc')->one();
        return $result;
    }

    /**
     * 检查账户是否开户且授权
     * @param $userId   用户id
     * @return bool
     */
    public function chkAccountAndAuth($userId) {
        if (empty($userId) || !is_numeric($userId)) {
            return false;
        }
        $isAccount = $this->getPaysuccessByUserId($userId, 2, 1);
        $isAuth = $this->getPaysuccessByUserId($userId, 2, 2);
        if (!$isAccount || !$isAuth) {
            return false;
        }
        return true;
    }

    public function addList($condition) {
        if (!empty($condition['user_id'])) {
            $type = isset($condition['type']) ? $condition['type'] : 1;
            $step = isset($condition['step']) ? $condition['step'] : 2;
            $paystatus = (new Payaccount())->getPaystatusByUserId($condition['user_id'], $type, $step);
            if (!empty($paystatus)) {
                $result = $paystatus->updatePaystatus($condition);
                return $paystatus->id;
            }
        }
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $time = date('Y-m-d H:i:s');
        $this->activate_time = $time;
        $this->create_time = $time;
        $result = $this->save();
        if ($result) {
            return Yii::$app->db->getLastInsertID();
        } else {
            return false;
        }
    }

    public function updatePaystatus($condition) {
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $time = date('Y-m-d H:i:s');
        $this->activate_time = $time;
        $result = $this->save();
        return $result;
    }

    static public function saveRecord($user_id, $step, $activate_result) {
        $condition = [
            'user_id' => $user_id,
            'type' => 1,
            'step' => $step,
            'activate_result' => $activate_result,
        ];
        $o = new self;
        return $o->addList($condition);
    }

    /**
     * 添加一条数据
     * @param $condition
     * @return bool
     */
    public function add_list($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        if (!empty($condition['user_id'])) {
            $type = isset($condition['type']) ? $condition['type'] : 1;
            $step = isset($condition['step']) ? $condition['step'] : 2;
            $paystatus = (new Payaccount())->getPaystatusByUserId($condition['user_id'], $type, $step);
            if (!empty($paystatus)) {
                $result = $paystatus->update_list($condition);
                return $result;
            }
        }
        $data = $condition;
        $data['activate_time'] = date('Y-m-d H:i:s');
        $data['create_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 添加一条step数据,不更新
     * @param $condition
     * @return bool
     */
    public function add_step($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        if (!empty($condition['user_id'])) {
            $type = isset($condition['type']) ? $condition['type'] : 1;
            $step = isset($condition['step']) ? $condition['step'] : 2;
            $paystatus = (new Payaccount())->getPaystatusByUserId($condition['user_id'], $type, $step);
            if (!empty($paystatus)) {
                return false;
            }
        }
        $data = $condition;
        $data['activate_time'] = date('Y-m-d H:i:s');
        $data['create_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        $res=$this->save();
        if($res){
            return $this->id;
        }
        return false;
    }

    /**
     * 更新一条数据
     * @param $condition
     * @return bool
     */
    public function update_list($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data = $condition;
        if (isset($condition['activate_result'])) {
            $data['activate_time'] = date('Y-m-d H:i:s');
        }
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 更新存管内的绑定银行卡\解绑银行卡
     * @param type $card 
     */
    public function setCard($card) {
        $data['card'] = $card;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 是否能存管内出款
     * @param type $loan
     * @return boolean
     */
    public function isOutByCunguan($loan) {
        $isOpen = $this->getPaysuccessByUserId($loan->user_id, 2, 1);
        $isAuth = $this->getPaysuccessByUserId($loan->user_id, 2, 2);

        if ($isAuth && $isOpen && $isOpen->isopen == 1 && $isOpen->card == $loan->bank_id) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 查询存管信息
     * @param $user_id
     * @param int $type 1借款时调用 2还款时调用
     * @return array
     * @author 王新龙
     * @date 2018/9/18 16:26
     */
    public function isCunguan($user_id,$type =1) {
        $resultArr = [
            'isOpen' => 0,
            'isCard' => 0,
            'isPass' => 0,
            'isAuth' => 0
        ];
        $user_id = intval($user_id);
        if (!$user_id) {
            return $resultArr;
        }
        $isOpen = $this->getPaysuccessByUserId($user_id, 2, 1);
        if (!empty($isOpen)) {
            $resultArr['isOpen'] = 1; //已开户
        }
        if (!empty($isOpen) && !empty($isOpen->card)) {
            $resultArr['isCard'] = 1; //已绑卡
        }
        $isPass = $this->getPaysuccessByUserId($user_id, 2, 2);
        if (!empty($isPass)) {
            $resultArr['isPass'] = 1; //已设置密码
        }
        $repay_result = 0;
        $fund_result = 0;
        $isRepayAuth = $this->getPaysuccessByUserId($user_id, 2, 4);
        if (!empty($isRepayAuth)) {
            $repayTimeOut = $isRepayAuth->isTimeOut();
            if (!$repayTimeOut) {
                $repay_result = 1; //已还款授权
            }
        }
        $isFundAuth = $this->getPaysuccessByUserId($user_id, 2, 5);
        if (!empty($isFundAuth)) {
            $fundTimeOut = $isFundAuth->isTimeOut();
            if (!$fundTimeOut) {
                $fund_result = 1; //已还款授权
            }
        }
        //还款时，如已做还款、缴费授权，则直接还款
        if($repay_result == 1 && $fund_result == 1){
            $resultArr['isAuth'] = 1;
            return $resultArr;
        }
        //四合一授权
        $isAuth = $this->getPaysuccessByUserId($user_id, 2, 6);
        if(!empty($isAuth)){
            $o_pay_account_extend = (new PayAccountExtend())->getByUserIdAndStep($user_id, 6);
            $o_pay_account_extend_result = !empty($o_pay_account_extend) ? $o_pay_account_extend->getLegal($type) : 0;
            if($o_pay_account_extend_result){
                $resultArr['isAuth'] = 1;
            }
        }
        return $resultArr;
    }

    /**
     * 获取授权状态
     * @param $user_id
     * @return int 0:未授权 1：已授权 2：授权过期
     */
    public function getAuthStatus($user_id){
        $status = 0;//未授权
        $repayAuthStatus = 0;
        $fundAuthStatus = 0;
        $fourInOneStatus = 0;
        $user_id = intval($user_id);
        if (!$user_id) {
            return $status;
        }
        $isOpen = $this->getPaysuccessByUserId($user_id, 2, 1);
        if(empty($isOpen)){
            return $status;
        }
        $isRepayAuth = $this->getPaysuccessByUserId($user_id, 2, 4);
        if (!empty($isRepayAuth)) {
            $repayTimeOut = $isRepayAuth->isTimeOut();
            if (!$repayTimeOut) {
                $repayAuthStatus = 1; //已还款授权
            }else{
                $repayAuthStatus = 2; //还款授权过期
            }
        }
        $isFundAuth = $this->getPaysuccessByUserId($user_id, 2, 5);
        if (!empty($isFundAuth)) {
            $fundTimeOut = $isFundAuth->isTimeOut();
            if (!$fundTimeOut) {
                $fundAuthStatus = 1; //已缴费授权
            }else{
                $fundAuthStatus = 2; //缴费授权过期
            }
        }
        //四合一授权
        $isAuth = $this->getPaysuccessByUserId($user_id, 2, 6);
        if(!empty($isAuth)){
            $o_pay_account_extend = (new PayAccountExtend())->getByUserIdAndStep($user_id, 6);
            $o_pay_account_extend_result = !empty($o_pay_account_extend) ? $o_pay_account_extend->getLegal(1) : 0;
            if($o_pay_account_extend_result){
                $fourInOneStatus = 1;
            }else{
                $fourInOneStatus = 2;
            }
        }
        if($fourInOneStatus == 1){
            $status = 1;
        }elseif ($repayAuthStatus == 1 && $fundAuthStatus == 1){
            $status = 1;
        }elseif ($fourInOneStatus == 2 || $repayAuthStatus == 2 || $fundAuthStatus == 2){
            $status = 2;
        }
        return $status;
    }
    
     /**
     * 查询存管信息
     * @param $user_id
     * @param int $type 1借款时调用 2还款时调用
     * @return array
     * @author 王新龙
     * @date 2018/9/18 16:26
     */
    public function isCunguanXuqi($user_id,$type=2) {
        $resultArr = [
            'isPass' => 0,
            'isAuth' => 0
        ];
        $user_id = intval($user_id);
        if (!$user_id) {
            return $resultArr;
        }
     
        $isPass = $this->getPaysuccessByUserId($user_id, 2, 2);
        if (!empty($isPass)) {
            $resultArr['isPass'] = 1; //已设置密码
        }
        if($type == 2){
            $repay_result = 0;
            $fund_result = 0;
            $isRepayAuth = $this->getPaysuccessByUserId($user_id, 2, 4);
            if (!empty($isRepayAuth)) {
                $repayTimeOut = $isRepayAuth->isTimeOut();
                if (!$repayTimeOut) {
                    $repay_result = 1; //已还款授权
                }
            }
            $isFundAuth = $this->getPaysuccessByUserId($user_id, 2, 5);
            if (!empty($isFundAuth)) {
                $fundTimeOut = $isFundAuth->isTimeOut();
                if (!$fundTimeOut) {
                    $fund_result = 1; //已还款授权
                }
            }
            //还款时，如已做还款、缴费授权，则直接还款
            if($repay_result == 1 && $fund_result == 1){
                $resultArr['isAuth'] = 1;
                return $resultArr;
            }
        }
        //四合一授权
        $isAuth = $this->getPaysuccessByUserId($user_id, 2, 6);
        if(!empty($isAuth)){
            $o_pay_account_extend = (new PayAccountExtend())->getByUserIdAndStep($user_id, 6);
            $o_pay_account_extend_result = !empty($o_pay_account_extend) ? $o_pay_account_extend->getLegal(1) : 0;
            if($o_pay_account_extend_result){
                $resultArr['isAuth'] = 1;
            }
        }
        return $resultArr;
    }
    
    

    /**
     * 判断认证是否过期
     * @return bool true 过期 false 未过期 5年
     */
    public function isTimeOut() {
        $result = time() > 60 * 60 * 24 * 365 * 5 + strtotime($this->activate_time) ? true : false;
        return $result;
    }

}
