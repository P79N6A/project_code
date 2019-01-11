<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_user_credit_list".
 *
 * @property string $id
 * @property string $user_id
 * @property string $loan_id
 * @property string $req_id
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
 */
class UserCreditList extends \app\models\BaseModel {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_user_credit_list';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'create_time'], 'required'],
            [['user_id', 'loan_id', 'req_id', 'score', 'status', 'res_status', 'days','shop_days', 'device_type', 'type','source', 'pay_status','period','installment_result'], 'integer'],
            [['amount','shop_amount', 'interest_rate','shop_interest_rate', 'crad_rate','shop_crad_rate'], 'number'],
            [['invalid_time', 'last_modify_time', 'create_time','black_box'], 'safe'],
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
            'period' => 'Period',
            'installment_result' => 'Installment Result',
        ];
    }

    /**
     * 新增记录
     * @author 王新龙
     * @date 2018/7/30 12:11
     */
    public function addRecord($condition) {
        if (empty($condition) || !is_array($condition)) {
            return false;
        }
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 新增记录
     * @param $condition
     * @return bool
     * @author 王新龙
     * @date 2018/7/30 12:10
     */
    public function updateRecord($condition) {
        if (empty($condition) || !is_array($condition)) {
            return false;
        }
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 同步credit记录至list表
     * @param $reqId
     * @return bool
     * @author 王新龙
     * @date 2018/7/30 14:53
     */
    public function synchro($reqId) {
        $o_user_credit = (new User_credit())->getByReqId($reqId, true);
        if (empty($o_user_credit)) {
            return false;
        }
        unset($o_user_credit['id']);
        $o_user_credit_list = (new UserCreditList())->getByReqId($o_user_credit['req_id']);
        if (!empty($o_user_credit_list)) {
            $result = $o_user_credit_list->updateRecord($o_user_credit);
        } else {
            $result = (new UserCreditList())->addRecord($o_user_credit);
        }
        return $result;
    }

    /**
     * 查询记录根据req_id
     * @param $req_id
     * @return array|null|\yii\db\ActiveRecord
     * @author 王新龙
     * @date 2018/7/30 12:14
     */
    public function getByReqId($req_id) {
        if (empty($req_id)) {
            return null;
        }
        return self::find()->where(['req_id' => $req_id])->one();
    }

    public function getList($user_id, $req_id, $orderby = 'id desc') {
        if (empty($user_id) || !is_string($user_id)) {
            return null;
        }
        return self::find()->where(['user_id' => $user_id])->andWhere(['<>', 'req_id', $req_id])->orderBy($orderby)->all();
    }

    public function getByLoanId($loan_id){
        if(empty($loan_id)){
            return null;
        }
        return self::find()->where(['loan_id'=>$loan_id])->one();
    }
}
