<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_user_submit_list".
 *
 * @property string $id
 * @property string $order_id
 * @property string $loan_id
 * @property string $settle_request_id
 * @property string $settle_amount
 * @property string $rsp_code
 * @property string $remit_status
 * @property string $create_time
 * @property string $last_modify_time
 * @property string $bank_id
 * @property string $user_id
 * @property integer $type
 */
class User_submit_list extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_user_submit_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'create_time', 'last_modify_time', 'bank_id'], 'required'],
            [['loan_id', 'bank_id', 'user_id', 'type'], 'integer'],
            [['settle_amount'], 'number'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['order_id', 'settle_request_id'], 'string', 'max' => 32],
            [['rsp_code'], 'string', 'max' => 6],
            [['remit_status'], 'string', 'max' => 12]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键',
            'order_id' => '订单编号',
            'loan_id' => '借款ID',
            'settle_request_id' => '交易流水号',
            'settle_amount' => '请求金额',
            'rsp_code' => '返回状态码',
            'remit_status' => '返回状态',
            'create_time' => '请求时间',
            'last_modify_time' => '最后修改时间',
            'bank_id' => '银行卡ID',
            'user_id' => '用户ID',
            'type' => '通道类型',
        ];
    }

    /**
     * 保存请求出款订单
     * @param [] $data
     * @return  bool
     */
    public function saveSubmit($data) {
        $time = date('Y-m-d H:i:s');
        $postData = [
            'order_id' => $data['order_id'],
            'loan_id' => $data['loan_id'],
            'settle_request_id' => $data['settle_request_id'],
            'settle_amount' => $data['settle_amount'],
            'rsp_code' => '',
            'remit_status' => 'INIT',
            'create_time' => $time,
            'bank_id' => $data['bank_id'],
            'user_id' => $data['user_id'],
            'type' => $data['type'],
            'last_modify_time' => $time
        ];

        $error = $this->chkAttributes($postData);

        if($error){
            return false;
        }

        $result = $this->save();
        return $result;
    }

    /**
     * 修改请求表的状态
     */
    public function updateSubmit($order_id, $settle_request_id, $rsp_code) {
        $submit_list = User_submit_list::find()->where(['order_id'=>$order_id])->one();
        $submit_list->settle_request_id = $settle_request_id;
        $submit_list->rsp_code = $rsp_code;
        $submit_list->remit_status = 'SUCCESS';
        $submit_list->last_modify_time = date('Y-m-d  H:i:s');

        $result = $submit_list->save();
        return $result;
    }
}
