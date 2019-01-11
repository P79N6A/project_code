<?php

namespace app\models\dev;

use Yii;
use app\commonapi\apiInterface\Sinaopenacc;

/**
 * This is the model class for table "account".
 *
 * @property string $id
 * @property string $mobile
 * @property string $password
 * @property string $school
 * @property integer $edu_levels
 * @property string $entrance_time
 * @property string $account_name
 * @property string $identity
 * @property string $create_time
 */
class Payaccount extends \app\models\BaseModel {

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
            [['user_id', 'create_time'], 'required'],
            [['user_id', 'type', 'step', 'activate_result'], 'integer'],
            [['activate_time', 'create_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '主键，递增',
            'user_id' => '用户ID',
            'type' => '操作类型1:新浪出款接口',
            'step' => '操作步骤1:第一步(开户);2:第二步(激活)',
            'activate_result' => '操作结果1:成功;0:失败；',
            'activate_time' => '最后修改时间',
            'create_time' => '创建时间',
        ];
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

    /**
     * 
     * @param type $user_id
     * @param type $type
     * @return boolean
     */
    public function getPaystatusByUserId($user_id, $type = 1, $step = 2) {
        if (empty($user_id)) {
            return false;
        }
        $result = Payaccount::find()->where(['user_id' => $user_id, 'type' => $type, 'step' => $step])->orderBy('activate_time desc')->one();
        return $result;
    }

    //保存数据
    public function saveData($data){
        if( !is_array($data) || empty($data) ){
            return false;
        }
        $time = date('Y-m-d H:i:s');
        $newData = [
            'user_id' => $data['user_id'],
            'type' => 1, 
            'step' => $data['step'],
            'activate_result' => $data['result'],
            'activate_time' => $time,
            'create_time' =>  $time,
        ];
        $error = $this->chkAttributes($newData);
        if($error){
            return false;
        }
        return $this->save();
    }

    //新浪开户
    public function sinaOpenAcc($user_id,$loan_id){
        if(!$user_id || !$loan_id)
            return 'FAIL';
        
        //1.执行开户接口调用,只要是未成功的都需要执行一次
        $extend = User_loan_extend::find()->where(['loan_id' => $loan_id])->limit(1)->one();
        $user = User::find()->where(['user_id'=>$user_id])->limit(1)->one();
        $loan = User_loan::find()->where(['loan_id'=>$loan_id])->limit(1)->one();
        $bank = User_bank::find()->where(['id'=>$loan->bank_id])->limit(1)->one();
        $ip = explode(',', $extend->userIp);
        $request_id = "yyy" . (string) time() . (string) rand(1000, 9999);
        $postData = [
            'request_id' => $request_id,
            'user_id' => $user_id, // 唯一, 不可重复, 测试随便填, 生产只能填写自己, 则以后被占用
            'name' => $user->realname,
            'idcard' => $user->identity,
            'phone' => $user->mobile, // 唯一, 不可重复, 测试随便填, 生产只能填写自己,后被占用
            'cardno' => $bank->card,
            'card_type' => $bank->type == 0 ? 1 : 2, //1:借记; 2:贷记（信用卡）
            'bankcode' => $bank->bank_abbr,
            'ip' => $ip[0],
        ];
        $apihttp = new Sinaopenacc();
        $res = $apihttp -> openacc($postData);
        
        //2.根据返回结果，保存数据
        if( !$res || $res['res_code'] != '0000' ){
            //加一条失败记录
            $result = 0 ;
            $flag = 'FAIL';
        }else{
            $result = 1 ;
            //判断用户是否开户成功
            $checkSuccess = $this->checkOpenAcc($user_id);
            if( $checkSuccess ){
                $flag = 'ALL SUCCESS';
            }else{
                $flag = 'FRIST SUCCESS'; 
            }
        }
        $checkExist = $this->getPaystatusByUserId($user_id,1,1);
        if( !$checkExist ){
            $addData = [
                'user_id'=>$user_id,
                'step'=>1,
                'result'=>$result
            ];
            $ret = $this->saveData($addData);
            if( !$ret ){
                $flag = 'FAIL';
            }
        }

        return $flag;
    }

    //判断用户是否新浪开户成功
    public function checkOpenAcc($user_id,$type=1){
        if (empty($user_id)) {
            return false;
        }
        $result = static::find()->where(['user_id' => $user_id, 'type' => $type, 'step' => 2 , 'activate_result' => 1])->one();
        return $result;
    }
}
