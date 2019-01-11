<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "balance_channel".
 *
 * @property string $id
 * @property string $cpid
 * @property string $channelid
 * @property string $mechart_num
 * @property integer $status
 */
class BalanceChannel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'balance_channel';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cpid', 'channelid', 'mechart_num'], 'required'],
            [['cpid','aid','cid', 'channelid', 'status'], 'integer'],
            [['mechart_num'], 'string', 'max' => 30]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cpid' => 'Cpid',
            'aid' => 'Aid',
            'cid' => 'Cid',
            'channelid' => 'Channelid',
            'mechart_num' => 'Mechart Num',
            'status' => 'Status',
        ];
    }
    /**
     * Undocumented function
     * 查询通道列表
     * @return void
     */
    public static function getChannelList(){
        $where = [
            'and',
            [BalanceType::tableName().'.status' => 1],
            [BalanceChannel::tableName().'.status' => 1],
        ];

        $data = static::find()->select('balance_channel.*,balance_type.cp_name')->leftJoin(BalanceType::tableName(),BalanceType::tableName().'.id='.BalanceChannel::tableName().'.cpid')
                        ->where($where)->asArray()
                        ->all();

        return $data;
    }
} 