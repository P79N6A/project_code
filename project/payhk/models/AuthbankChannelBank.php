<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "authbank_channel_bank".
 *
 * @property integer $id
 * @property integer $channel_id
 * @property string $std_bankname
 * @property string $bankname
 * @property string $bankcode
 * @property integer $card_type
 * @property integer $status
 * @property string $create_time
 */
class AuthbankChannelBank extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'authbank_channel_bank';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['channel_id', 'std_bankname', 'bankname', 'bankcode', 'create_time'], 'required'],
            [['channel_id', 'card_type', 'status'], 'integer'],
            [['create_time'], 'safe'],
            [['std_bankname', 'bankname', 'bankcode'], 'string', 'max' => 30]
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
            'std_bankname' => 'Std Bankname',
            'bankname' => 'Bankname',
            'bankcode' => 'Bankcode',
            'card_type' => 'Card Type',
            'status' => 'Status',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function getAuthbankChannel(){
        return $this->hasOne(AuthbankChannel::className(),['id' => 'channel_id']);
    }


    /**
     * Undocumented function
     *
     * @param [] $cardInfo
     * @param int $aid
     * @return void
     */
    public function supportChannel($cardInfo,$aid)
    {
        $where = [
            'and',
            [AuthbankChannelBank::tableName().'.card_type' => $cardInfo['cardType']],
            [AuthbankChannelBank::tableName().'.status' => 1],
            [AuthbankChannelBank::tableName().'.bankcode' => $cardInfo['bankCode']],
            // [AuthbankChannelBank::tableName().'.std_bankname' => $cardInfo['bankName']],
            [AuthbankChannel::tableName().'.status' => 1],
            [AuthbankChannel::tableName().'.aid' => $aid],
        ];
        $channels = static::find()->innerJoinWith(['authbankChannel'])
                ->where($where)
                ->orderBy('sort_num asc')
                ->asArray()
                ->all();
        if (empty($channels)) {
            return [];
        }
        return \yii\helpers\ArrayHelper::getColumn($channels, 'channel_id');
    }
}
