<?php

namespace app\modules\balance\models\yyy;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "yi_user_remit_list".
 *
 * @property string $id
 * @property string $order_id
 * @property string $loan_id
 * @property string $admin_id
 * @property string $settle_request_id
 * @property string $real_amount
 * @property string $settle_fee
 * @property string $settle_amount
 * @property string $rsp_code
 * @property string $rsp_msg
 * @property string $remit_status
 * @property string $create_time
 * @property string $bank_id
 * @property string $user_id
 * @property integer $type
 * @property string $last_modify_time
 * @property string $remit_time
 * @property integer $fund
 * @property integer $payment_channel
 * @property integer $version
 */
class QjUserRemitList extends YyyBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'qj_user_remit_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'admin_id', 'create_time', 'bank_id'], 'required'],
            [['loan_id', 'admin_id', 'bank_id', 'user_id', 'type', 'fund', 'payment_channel', 'version'], 'integer'],
            [['real_amount', 'settle_fee', 'settle_amount'], 'number'],
            [['create_time', 'last_modify_time', 'remit_time'], 'safe'],
            [['order_id', 'settle_request_id'], 'string', 'max' => 32],
            [['rsp_code'], 'string', 'max' => 30],
            [['rsp_msg'], 'string', 'max' => 50],
            [['remit_status'], 'string', 'max' => 12]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'loan_id' => 'Loan ID',
            'admin_id' => 'Admin ID',
            'settle_request_id' => 'Settle Request ID',
            'real_amount' => 'Real Amount',
            'settle_fee' => 'Settle Fee',
            'settle_amount' => 'Settle Amount',
            'rsp_code' => 'Rsp Code',
            'rsp_msg' => 'Rsp Msg',
            'remit_status' => 'Remit Status',
            'create_time' => 'Create Time',
            'bank_id' => 'Bank ID',
            'user_id' => 'User ID',
            'type' => 'Type',
            'last_modify_time' => 'Last Modify Time',
            'remit_time' => 'Remit Time',
            'fund' => 'Fund',
            'payment_channel' => 'Payment Channel',
            'version' => 'Version',
        ];
    }

    /*
     * 出款金额
     */
    public function totalMoney($condition)
    {
        if (empty($condition)){
            return 0;
        }
        $where_config = [
            'AND',
            ['>=', 'create_time', ArrayHelper::getValue($condition, 'start_time')],
            ["<=", 'create_time', ArrayHelper::getValue($condition, 'end_time')],
            ['=', 'remit_status', 'SUCCESS'],
        ];
        $total = self::find()->where($where_config)->sum('real_amount');
        return empty($total) ? 0 : $total;
    }

    /*
     * 利息
     */
    public function settleFee($condition)
    {
        if (empty($condition)){
            return 0;
        }
        $where_config = [
            'AND',
            ['>=', 'create_time', ArrayHelper::getValue($condition, 'start_time')],
            ["<=", 'create_time', ArrayHelper::getValue($condition, 'end_time')],
            ['=', 'remit_status', 'SUCCESS'],
        ];
        $total = self::find()->where($where_config)->sum('settle_fee');
        return empty($total) ? 0 : $total;
    }


    /**---------------------------对账使用split-------------------------------------------------------
     * 通过loan_id获取实际出款记录
     * @param $loan_id
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    public function getOneByData($loan_id)
    {
        if (empty($loan_id)){
            return false;
        }
        return self::find()->where(['AND',['loan_id' => $loan_id],['=', 'remit_status', 'SUCCESS']])->one();
    }

/*-------------------------------对账使用SplitNew  新版-----------------------------------------------------*/
    /**
     * 获取 特定的 所有放款记录
     * @return []
     */
    public function getRequestList($start_time,$end_time,$pages=999999) {

        $result = self::find();
        $result->select (['*','date_format(`create_time`,\'%Y-%m-%d\') tData']);
        $result->andWhere(['remit_status' => 'SUCCESS']);
        $result->andWhere(['fund' => 11]);
        $dataList = $result->limit($pages)->all();
        if (!$dataList) {
            return null;
        }
        return $dataList;
    }
}

