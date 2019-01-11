<?php

namespace app\models\bill;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "bill_original".
 *
 * @property string $id
 * @property string $client_id
 * @property string $guest_account_bank
 * @property string $settle_amount
 * @property string $settle_fee
 * @property string $identityid
 * @property string $user_mobile
 * @property string $guest_account_name
 * @property string $guest_account
 * @property integer $status
 * @property integer $type
 * @property string $bill_type
 * @property string $bill_time
 * @property string $create_time
 * @property string $update_time
 */
class BillOriginal extends \app\models\BaseModel
{
    const STATUS_INIT = 0;  //初始
    const STATUS_LOCK = 2;  //锁定状态
    const STATUS_SUCCESS = 1; //成功
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'bill_original';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['client_id', 'create_time'], 'required'],
            [['settle_amount', 'settle_fee'], 'number'],
            [['status', 'type'], 'integer'],
            [['bill_time', 'create_time', 'update_time'], 'safe'],
            [['client_id'], 'string', 'max' => 50],
            [['guest_account_bank', 'identityid'], 'string', 'max' => 20],
            [['user_mobile', 'guest_account_name'], 'string', 'max' => 60],
            [['guest_account', 'bill_type'], 'string', 'max' => 30],
            [['client_id'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'client_id' => 'Client ID',
            'guest_account_bank' => 'Guest Account Bank',
            'settle_amount' => 'Settle Amount',
            'settle_fee' => 'Settle Fee',
            'identityid' => 'Identityid',
            'user_mobile' => 'User Mobile',
            'guest_account_name' => 'Guest Account Name',
            'guest_account' => 'Guest Account',
            'status' => 'Status',
            'type' => 'Type',
            'bill_type' => 'Bill Type',
            'bill_time' => 'Bill Time',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }

    public function getCount()
    {
        return self::find()->where(['status'=>self::STATUS_INIT])->count();
    }

    /**
     * 获取数据
     * @param $limit
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getData($limit)
    {
        return self::find()->where(['status'=>self::STATUS_INIT])->limit($limit)->orderBy('id asc')->asArray()->all();
    }

    public function lockStatus($ids)
    {
        return BillOriginal::updateAll(['status' => self::STATUS_LOCK], ['status' => self::STATUS_INIT, 'id' => explode(',', $ids)]);
    }


    public function getInfo($id)
    {
        if (empty($id)) {
            return false;
        }
        return self::find()->where(['id'=>$id])->one();
    }

    public function successStatus()
    {
        try{
            $this->status =  static::STATUS_SUCCESS;
            $this->update_time = date("Y-m-d H:i:s", time());
            return $this->save();
        }catch(\Exception $e){
            return false;
        }
    }

    public function getOneData($client_id, $type)
    {
        if (empty($client_id) || empty($type)){
            return false;
        }
        return self::find()->where(['client_id'=>$client_id, 'type'=> $type])->one();
    }

    public function saveData($data_set)
    {
        if (empty($data_set)){
            return false;
        }
        $data = [
            'client_id'=>ArrayHelper::getValue($data_set, 'client_id', 0), //商户订单号',
            'guest_account_bank'=>ArrayHelper::getValue($data_set, 'guest_account_bank', 0), //$data_set[2], //收款人开户行',
            'settle_amount'=>ArrayHelper::getValue($data_set, 'settle_amount', 0), //$data_set[6], //金额(单位：元)',
            'settle_fee'=>ArrayHelper::getValue($data_set, 'settle_fee', 0), //$data_set[7], //手续费',
            'identityid'=>ArrayHelper::getValue($data_set, 'identityid', 0), //$data_set[4], //收款人证件号',
            'user_mobile'=>ArrayHelper::getValue($data_set, 'user_mobile', ''), //$data_set[5], //收款人手机号',
            'guest_account_name'=>ArrayHelper::getValue($data_set, 'guest_account_name', ''), //$data_set[1], //收款人姓名',
            'guest_account'=>ArrayHelper::getValue($data_set, 'guest_account', 0), //$data_set[3], //收款人银行卡号',
            'status'=>ArrayHelper::getValue($data_set, 'status', 0), //状态:0:初始;1:成功3:重试;11:失败',
            'type'=>ArrayHelper::getValue($data_set, 'type', 0), //状态:1:融宝 2:宝付;3:畅捷',
            'bill_type'=>ArrayHelper::getValue($data_set, 'bill_type', ''), //$data_set[8], //付款状态',
            'bill_time'=>ArrayHelper::getValue($data_set, 'bill_time', ''), //$data_set[9], //账单日期',
            'create_time'=>date("Y-m-d H:i:s", time()), //创建时间',
            'update_time'=>date("Y-m-d H:i:s", time()), //修改时间',
        ];
        $errors = $this->chkAttributes($data);
        if ($errors) {
            return false;
        }
        return $this->save();
    }
}