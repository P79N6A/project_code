<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;

/**
 * This is the model class for table "td_report".
 *
 * @property integer $id
 * @property integer $loan_id
 * @property integer $collection_id
 * @property integer $product_source
 * @property string $call_record_list
 * @property string $sms_record_list
 * @property string $sys_call_status
 * @property string $create_time
 * @property string $modify_time
 */
class TdReport extends BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_td_report';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
//            [['id'], 'required'],
            [['id', 'product_source', 'report_status', 'loan_id', 'user_id'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['call_record_list'], 'string', 'max' => 1500],
            [['sms_record_list'], 'string', 'max' => 1000],
            [['sys_call_status'], 'string', 'max' => 15],
            [['collection_id'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id'               => 'ID',
            'loan_id'          => 'Loan ID',
            'collection_id'    => 'Collection ID',
            'product_source'   => 'Product Source',
            'call_record_list' => 'Call Record List',
            'sms_record_list'  => 'Sms Record List',
            'sys_call_status'  => 'Sys Call Status',
            'create_time'      => 'Create Time',
            'modify_time'      => 'Modify Time',
        ];
    }

    public function upData($data) {
        $time = date('Y-m-d H:i:s', time());
        $data = [
            'id' => $data['id'],
            'collection_id' => $data['collection_id'],
            'product_source' => $data['product_source'],
            'report_status' => $data['report_status'],
            'modify_time' => $time
        ];
        $error = $this->chkAttributes($data);
        if ($error)
            return false;

        return $this->save();
    }

    public function upstatus($data) {
        $time = date('Y-m-d H:i:s', time());
        $data = [
            'id' => $data['id'],
            'report_status' => $data['report_status'],
            'modify_time' => $time
        ];
        $error = $this->chkAttributes($data);
        if ($error)
            return false;

        return $this->save();
    }

    public function addinit($data) {
        $time = date('Y-m-d H:i:s', time());
        $data = [
            'loan_id' => $data['loan_id'],
            'user_id' => $data['user_id'],
            'report_status' => $data['report_status'],
            'create_time' => $time,
            'modify_time' => $time
        ];
        $error = $this->chkAttributes($data);
        if ($error)
            return false;

        return $this->save();
    }

    public function updateData($data) {
        $time = date('Y-m-d H:i:s', time());
        $data = [
            'call_record_list' => $data['call_record_list'],
            'sms_record_list' => $data['sms_record_list'],
            'sys_call_status' => $data['sys_call_status'],
            'report_status' => $data['report_status'],
            'modify_time' => $time
        ];
        $error = $this->chkAttributes($data);
        if ($error)
            return false;

        return $this->save();
    }

    public function getTdReport($collection_id) {
        if (!$collection_id) {
            return false;
        }
        return self::find()->select('call_record_list')->where(['collection_id' => $collection_id])->asArray()->one();
    }

    public function getTdReportByLoanid($loan_id) {
        if (!$loan_id) {
            return false;
        }
        return self::find()->where(['loan_id' => $loan_id])->orderBy('id desc')->asArray()->one();
    }

}
