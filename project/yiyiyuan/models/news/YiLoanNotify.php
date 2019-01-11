<?php

namespace app\models\news; 
use app\models\BaseModel;

use Yii; 

/** 
 * This is the model class for table "yi_loan_notify". 
 * 
 * @property string $id
 * @property string $loan_id
 * @property string $channel_loan_id
 * @property string $mark
 * @property integer $status
 * @property integer $notify_num
 * @property string $notify_time
 * @property integer $result
 * @property string $create_time
 * @property string $channel
 * @property integer $notify_status
 * @property string $remit_status
 * @property string $last_modify_time
 */ 
class YiLoanNotify extends BaseModel
{ 
    /** 
     * @inheritdoc 
     */ 
    public static function tableName() 
    { 
        return 'yi_loan_notify'; 
    } 

    /** 
     * @inheritdoc 
     */ 
    public function rules() 
    { 
        return [
            [['loan_id', 'status', 'create_time', 'channel', 'last_modify_time'], 'required'],
            [['loan_id', 'status', 'notify_num', 'result', 'notify_status'], 'integer'],
            [['notify_time', 'create_time', 'last_modify_time'], 'safe'],
            [['channel_loan_id', 'channel', 'remit_status'], 'string', 'max' => 20],
            [['mark'], 'string', 'max' => 32]
        ]; 
    } 

    /** 
     * @inheritdoc 
     */ 
    public function attributeLabels() 
    { 
        return [ 
            'id' => 'ID',
            'loan_id' => 'Loan ID',
            'channel_loan_id' => 'Channel Loan ID',
            'mark' => 'Mark',
            'status' => 'Status',
            'notify_num' => 'Notify Num',
            'notify_time' => 'Notify Time',
            'result' => 'Result',
            'create_time' => 'Create Time',
            'channel' => 'Channel',
            'notify_status' => 'Notify Status',
            'remit_status' => 'Remit Status',
            'last_modify_time' => 'Last Modify Time',
        ]; 
    } 

    /**
     * 保存一条成功失败的记录
     * @return [type] [description]
     */
    public function saveNotifyRecord($data){
        $user_loan = User_loan::find()->where(['loan_id'=>$data['loan_id']])->one();
        if($user_loan->source < 6){
            return true;
        }
        
        //百融放款成功信息添加
        if($user_loan->source == 6 && $data['remit_status'] != 'SUCCESS'){
            return "";
        }
        
        $array_source = [
            '6' => '1419',
            '8' => '3300063',
            '9' => 'xhyyy',
        ];
        $order_mapping_info = (new Loan_mapping())->newestLoanmapping($data['loan_id']);
        if (empty($order_mapping_info) || empty($order_mapping_info->order_id)){
            return false;
        }

        $channel_loan_id = strval($order_mapping_info->order_id);

        $where = [
            'loan_id' => $data['loan_id'],
            'channel' => $array_source[$user_loan->source],
            'status' => 9
        ];

        $notify_num = YiLoanNotify::find()->where($where)->count();
        if($notify_num > 0){
            return true;
        }

        $postData = [
            'loan_id' => $data['loan_id'],
            'channel_loan_id' => $channel_loan_id,
            'status' => 9,
            'channel' => $array_source[$user_loan->source],
            'remit_status' => $data['remit_status']
        ];

        $result = $this->saveNotify($postData);
        return $result;
    }

    /**
     * 保存推送记录
     * @param [] $data
     * @return  bool
     */
    public function saveNotify($data) {
        $time = date('Y-m-d H:i:s');
        $postData = [
            'loan_id' => $data['loan_id'],
            'channel_loan_id' => $data['channel_loan_id'],
            'status' => $data['status'],
            'notify_time' => $time,
            'create_time' => $time,
            'channel' => $data['channel'],
            'remit_status' => $data['remit_status'],
            'last_modify_time' => $time
        ];

        $error = $this->chkAttributes($postData);
        if ($error) {
            return false;
        }
        $result = $this->save();
        return $result;
    }

    public function updateNotify($data)
    {
        $time = date('Y-m-d H:i:s');

        $this->mark = $data['mark'];
        $this->notify_time = $time;
        $this->notify_num = $data['notify_num'];
        $this->result = $data['result'];
        $this->last_modify_time = $time;
        $this->notify_status = $data['notify_status'];

        $result = $this->save();
        return $result;
    }
} 