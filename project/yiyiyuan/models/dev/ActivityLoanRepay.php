<?php

namespace app\models\dev;

use app\models\BaseModel;
use Yii;

/**
 * This is the model class for table "yi_activity_loan_repay".
 *
 * @property string $id
 * @property string $order_pay_no
 * @property string $user_id
 * @property string $bank_id
 * @property integer $platform
 * @property integer $source
 * @property integer $status
 * @property string $money
 * @property string $actual_money
 * @property string $paybill
 * @property integer $channel_id
 * @property string $return_code
 * @property string $return_msg
 * @property string $repay_time
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class ActivityLoanRepay extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_activity_loan_repay';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'platform', 'money', 'last_modify_time', 'create_time'], 'required'],
            [['user_id', 'bank_id', 'platform', 'source', 'status', 'channel_id', 'version','is_alert'], 'integer'],
            [['money', 'actual_money'], 'number'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['order_pay_no', 'paybill', 'return_code'], 'string', 'max' => 64],
            [['return_msg'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_pay_no' => 'Order Pay No',
            'user_id' => 'User ID',
            'bank_id' => 'Bank ID',
            'platform' => 'Platform',
            'source' => 'Source',
            'status' => 'Status',
            'money' => 'Money',
            'actual_money' => 'Actual Money',
            'paybill' => 'Paybill',
            'channel_id' => 'Channel ID',
            'return_code' => 'Return Code',
            'return_msg' => 'Return Msg',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'version' => 'Version',
            'is_alert' => 'Is Alert',
        ];
    }
    /**
     * @param $user_id
     * @return array|bool|null|\yii\db\ActiveRecord
     * 查询该用户是否参加过购买活动
     */
    public function getUserData($user_id)
    {
        if(!$user_id || !is_numeric($user_id)){
            return false;
        }
        $data = self::find()->where(['user_id'=>$user_id,'status'=>[1,-1]])->one();
        return $data;
    }

    /**
     * @param $user_id
     * @return array|bool|null|\yii\db\ActiveRecord
     * 查询该用户支付状态
     */
    public function getData($user_id)
    {
        if(!$user_id || !is_numeric($user_id)){
            return false;
        }
        $data = self::find()->where(['user_id'=>$user_id])->orderBy('last_modify_time desc')->one();
        return $data;
    }

    /**
     * 四分钟申请限制
     */
    public function fourlimit($user_id)
    {
        if(!$user_id){
            return false;
        }
        $where = [
            'and',
            ['>', 'create_time', date('Y-m-d H:i:s', strtotime('-4 minute'))],
            ['status'=>[0,-1]],
            ['user_id'=>$user_id],
        ];
        return self::find()->where($where)->one();
    }

    /**
     * @param $condition
     * @param int $source 来源默认 1
     * @return bool
     * 添加记录
     */
    public function addLoan($condition,$source=1)
    {
        if (empty($condition) ) {
            return FALSE;
        }
        // 数据
        $create_time = date('Y-m-d H:i:s');
        $condition['source'] = $source;
        $condition['status'] = 0;
        $condition['last_modify_time'] = $create_time;
        $condition['create_time'] = $create_time;
        // 保存数据
        $errors      = $this->chkAttributes($condition); //attributes = $data;
        if ($errors) {
            return FALSE;
        }
        $result = $this->save();
        return $result;
    }

    /**
     * 更新
     * @param $condition
     * @return bool
     */
    public function update_batch($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data                     = $condition;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error                    = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }
}


