<?php

namespace app\models\dev;

use Yii;

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
class Loan_repay extends \yii\db\ActiveRecord
{
    public $huankuan_amount;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_loan_repay';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            
        ];
    }
    
    /**
     * 添加还款记录
     */
    public function addRepay($condition){
    	if(empty($condition)){
    		return false;
    	}
    	foreach ($condition as $key=>$val){
    		$this->{$key}=$val;
    	}
    	$nowtime = date('Y-m-d H:i:s');
    	$this->last_modify_time = $nowtime;
    	$this->createtime = $nowtime;
        $this->version = 1;
        try {
            $result = $this->save();
            if($result){
                $orderid = 'Y'.date('mdHis').$this->id;
                $this['repay_id'] = (string)$orderid;
                $result = $this ->save();
                if($result){
                    return $this->id;
                }else{
                    return false;
                }
            }
        } catch (\Exception $ex) {
            return FALSE;
        }
    }
    
        //修改还款信息
    public function updateRepay($condition) {
        if(empty($condition)){
    		return false;
    	}
    	foreach ($condition as $key=>$val){
    		$this->{$key}=$val;
    	}
        $this->last_modify_time = date('Y-m-d H:i:s');
        return $this->save();
    }

	public function getBank() {
        return $this->hasOne(User_bank::className(), ['id' => 'bank_id']);
    }

	public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

	public function getLoan() {
        return $this->hasOne(User_loan::className(), ['loan_id' => 'loan_id']);
    }
    
    public function getLog() {
    	return $this->hasOne(Manager_logs::className(), ['log_id' => 'id']);
    }

}
