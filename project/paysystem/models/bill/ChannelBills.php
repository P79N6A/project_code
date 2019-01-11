<?php

namespace app\models\bill;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "channel_bills".
 *
 * @property string $id
 * @property integer $channel_id
 * @property string $total_pen_count
 * @property string $total_money
 * @property string $withdraw_fee
 * @property integer $source
 * @property integer $audit_status
 * @property string $bill_number
 * @property string $create_time
 * @property string $modify_time
 */
class ChannelBills extends \app\models\BaseModel
{
    const AUDIT_STATUS_NO = 1; //1未对账
    //const AUDIT_STATUS_NO = 3; //1未对账
    const AUDIT_STATUS_LOCK = 2; //锁
    const SOURCE_UP = 2; //2，已上传
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'channel_bills';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['channel_id', 'total_pen_count', 'source', 'audit_status', 'bill_number'], 'integer'],
            [['total_money', 'withdraw_fee'], 'number'],
            [['bill_number', 'create_time', 'modify_time'], 'required'],
            [['create_time', 'modify_time'], 'safe'],
            [['channel_file'], 'string',  'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'channel_id' => 'Channel ID',
            'total_pen_count' => 'Total Pen Count',
            'total_money' => 'Total Money',
            'withdraw_fee' => 'Withdraw Fee',
            'source' => 'Source',
            'audit_status' => 'Audit Status',
            'channel_file' => 'Channel File',
            'bill_number' => 'Bill Number',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    public function getSectionData($bill_number)
    {
        if (empty($bill_number)){
            return $this->returnError(false, 400);
        }
        $res = self::find()->where(['bill_number'=>$bill_number])->all();
        return $res;
    }

    public function getChannelTimeData($bill_number, $channel_id)
    {
        if (empty($bill_number) || empty($channel_id)){
            return $this->returnError(false, 400);
        }
        $res = self::find()->where(['bill_number'=>$bill_number, 'channel_id'=>$channel_id])->all();
        return $res;
    }

    public function saveChannelData($data_set)
    {
        if (empty($data_set)){
            return $this->returnError(false, 400);
        }
        $data = [
            'channel_id' => ArrayHelper::getValue($data_set, 'channel_id', ''), //出款通道:1:融宝 2:宝付;3:畅捷
            'total_pen_count' => ArrayHelper::getValue($data_set, 'total_pen_count', 0), //总笔数',
            'total_money' => ArrayHelper::getValue($data_set, 'total_money', 0), //总金额/元',
            'withdraw_fee' => ArrayHelper::getValue($data_set, 'withdraw_fee', 0), //手续费/元',
            'source' => ArrayHelper::getValue($data_set, 'source', '2'), //来源：1未下载，2，已上传，3已下载',
            'audit_status' => ArrayHelper::getValue($data_set, 'audit_status', '1'), //对账状态：1未对账，2锁定， 3已对账',
            'bill_number' => ArrayHelper::getValue($data_set, 'bill_number', ''), //账单编号',
            'channel_file' => ArrayHelper::getValue($data_set, 'channel_file', ''), //上传文件名',
            'create_time' => date("Y-m-d H:i:s", time()), //创建时间',
            'modify_time' => date("Y-m-d H:i:s", time()), //更新时间',
        ];
        if ($errors = $this->chkAttributes($data)) {
            return $this->returnError(null, implode('|', $errors));
        }
        $result = $this->save();
        return $result;
    }

    public function getChannelBillData()
    {
        return self::find()->where(['audit_status'=>self::AUDIT_STATUS_NO, 'source'=>self::SOURCE_UP])->all();
    }

    public function lockRemit($ids) {
        if (!is_array($ids) || empty($ids)) {
            return 0;
        }
        $field = ['audit_status' => static::AUDIT_STATUS_LOCK];
        $where = ['id' => $ids, 'audit_status' => static::AUDIT_STATUS_NO];
        $ups = static::updateAll($field, $where);
        return $ups;
    }

    public function updateChannelBill($data_set)
    {
        if (empty($data_set)){
            return false;
        }
        foreach($data_set as $key=>$value){
            $this->$key = $value;
        }
        $this->modify_time = date("Y-m-d H:i:s", time());
        $res = $this->save();
        return $res;
    }
}