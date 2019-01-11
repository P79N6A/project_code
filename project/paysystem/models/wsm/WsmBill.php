<?php

namespace app\models\wsm;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "wsm_bill".
 *
 * @property string $id
 * @property string $shddh
 * @property string $errorcode
 * @property string $msg
 * @property string $pay_time
 * @property string $contract_link
 * @property string $service_charge
 * @property string $bank_rate
 * @property string $bank
 * @property string $createtime
 * @property string $updatetime
 * @property integer $version
 */
class WsmBill extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'wsm_bill';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shddh', 'msg', 'version'], 'required'],
            [['createtime', 'updatetime', 'pay_time'], 'safe'],
            [['contract_link'], 'string'],
            [['version'], 'integer'],
            [['shddh'], 'string', 'max' => 50],
            [['errorcode'], 'string', 'max' => 32],
            [['msg'], 'string', 'max' => 200],
            [['bank'], 'string', 'max' => 100],
            [['service_charge', 'bank_rate'], 'number']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'shddh' => 'Shddh',
            'errorcode' => 'Errorcode',
            'msg' => 'Msg',
            'pay_time' => 'Pay Time',
            'contract_link' => 'Contract Link',
            'service_charge' => 'Service Charge',
            'bank_rate' => 'Bank Rate',
            'bank' => 'Bank',
            'createtime' => 'Createtime',
            'updatetime' => 'Updatetime',
            'version' => 'Version',
        ];
    }
    public function optimisticLock() {
        return "version";
    }
    public function saveData($data)
    {
        if (!is_array($data) || empty($data)) {
            return false;
        }
        $create_time = date("Y-m-d H:i:s", time());
        $data_set = [
            'shddh' => ArrayHelper::getValue($data, 'shddh', 0),//[资产平台系统的商户订单]商户订单号',
            'errorcode' => ArrayHelper::getValue($data, 'errorcode', ''),//错误码',
            'msg' => ArrayHelper::getValue($data, 'msg', ''),//返回信息',
            'pay_time' => ArrayHelper::getValue($data, 'pay_time', ''),//微神马订单放款时间',
            'contract_link' => ArrayHelper::getValue($data, 'contract_link', ''),//合同下载址地',
            'service_charge' => ArrayHelper::getValue($data, 'service_charge', 0),//服务利率',
            'bank_rate' => ArrayHelper::getValue($data, 'bank_rate', 0),//银行利率',
            'bank' => ArrayHelper::getValue($data, 'bank', ''),//放款银行',
            'createtime' => $create_time, //创建时间',
            'updatetime' => $create_time, //更新时间',
            'version' => 0,
        ];
        $errors = $this->chkAttributes($data_set);
        if ($errors) {
            return false;
        }
        $ret = $this->save();
        if (!$ret){
            return false;
        }
        return true;
    }

    /**
     * 通过shddh查找账单
     * @param $client_id
     * @return array|bool|null|\yii\db\ActiveRecord
     */
    public function getBillData($client_id)
    {
        if (empty($client_id)) {
            return false;
        }
        return self::find()->where(['shddh' => $client_id])->one();
    }
}