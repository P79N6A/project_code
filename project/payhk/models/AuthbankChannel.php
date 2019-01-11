<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "authbank_channel".
 *
 * @property integer $id
 * @property integer $aid
 * @property string $company_name
 * @property string $product_name
 * @property string $mechart_num
 * @property integer $status
 * @property string $create_time
 * @property integer $sort_num
 */
class AuthbankChannel extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'authbank_channel';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'status', 'sort_num'], 'integer'],
            [['company_name', 'mechart_num', 'create_time'], 'required'],
            [['create_time'], 'safe'],
            [['company_name', 'product_name'], 'string', 'max' => 30],
            [['mechart_num'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'aid' => 'Aid',
            'company_name' => 'Company Name',
            'product_name' => 'Product Name',
            'mechart_num' => 'Mechart Num',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'sort_num' => 'Sort Num',
        ];
    }

    public function getAuthbankChannelBank(){
        return $this->hasOne(AuthbankChannelBank::className(),['channel_id' => 'id']);
    }

    /**
     * @param string $channel
     * @return array|object
     */
    public function getByChannelId($channelId=''){
        $where = [];
        if(!empty($channelId)){
            $where = [ 'id' => $channelId ];
        }
        return static::find()->where($where)->all();
    }
    /**
     * @param string $isDict
     * @return array|object
     */
    public function getDictChannels($isDict = 1){
        $where = [ 'is_dict' => $isDict ];
        return static::find()->where($where)->all();
    }
}
