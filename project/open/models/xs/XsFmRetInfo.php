<?php

namespace app\models\xs;

use Yii;

/** 
 * This is the model class for table "dc_fm_ret_info". 
 * 
 * @property string $req_id
 * @property string $user_id
 * @property string $loan_id
 * @property string $event
 * @property string $basic_id
 * @property string $seq_id
 * @property integer $status
 * @property string $reason_code
 * @property string $create_time
 */ 
class XsFmRetInfo extends \app\models\repo\CloudBase
{ 
    /** 
     * @inheritdoc 
     */ 
    public static function tableName() 
    { 
        return 'dc_fm_ret_info'; 
    } 

    /** 
     * @inheritdoc 
     */ 
    public function rules() 
    { 
        return [
            [['user_id', 'basic_id', 'create_time'], 'required'],
            [['user_id', 'basic_id', 'status'], 'integer'],
            [['create_time'], 'safe'],
            [['loan_id', 'seq_id'], 'string', 'max' => 64],
            [['event'], 'string', 'max' => 10],
            [['reason_code'], 'string', 'max' => 32]
        ]; 
    } 

    /** 
     * @inheritdoc 
     */ 
    public function attributeLabels() 
    { 
        return [ 
            'req_id' => 'Req ID',
            'user_id' => '用户ID',
            'loan_id' => '借款ID',
            'event' => '调用事件',
            'basic_id' => '请求表id',
            'seq_id' => '调用的请求id',
            'status' => '请求状态 0 请求中；1 请求成功；2 请求失败 ',
            'reason_code' => '如果status为2，此处显示错误码',
            'create_time' => '添加时间',
        ]; 
    }

    public function addFmInfo($user_id, $basic_id, $event='reg', $loan_id = '') {
        $time = date("Y-m-d H:i:s");
        $postData = [
            'user_id'      => $user_id,
            'loan_id'      => (string)$loan_id,
            'event'        => $event,
            'basic_id'     => $basic_id,
            'seq_id'       => '',
            'status'       => 0,
            'reason_code'  => '',
            'create_time'  => $time,
        ];

        $error = $this->chkAttributes($postData);
        if ($error) {
            return false;
        }

        return $this->save();
        return $res;
    }

    public function updateFmInfo($fmInfo, $user_id, $basic_id)
    {   
        $this->status = 1;
        if (isset($fmInfo->res_code) && $fmInfo->res_code != '0000' ) {
            $this->status = 2;
            $this->reason_code = $fmInfo->res_data;
        } else {
            $this->seq_id = $fmInfo->seq_id;
        }
        return $this->save();
    }
}
