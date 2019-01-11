<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "pay_channel_bank".
 *
 * @property integer $id
 * @property integer $channel_id
 * @property string $std_bankname
 * @property string $bankname
 * @property string $bankcode
 * @property integer $card_type
 * @property integer $status
 * @property string $limit_max_amount
 * @property string $limit_day_amount
 * @property integer $limit_day_total
 * @property integer $limit_type
 * @property string $limit_start_time
 * @property string $limit_end_time
 * @property string $create_time
 */
class ChannelBank extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pay_channel_bank';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['channel_id', 'std_bankname', 'bankname', 'bankcode', 'create_time'], 'required'],
            [['channel_id', 'card_type', 'status', 'limit_day_total', 'limit_type'], 'integer'],
            [['limit_max_amount', 'limit_day_amount'], 'number'],
            [['create_time'], 'safe'],
            [['std_bankname', 'bankname', 'bankcode'], 'string', 'max' => 30],
            [['limit_start_time', 'limit_end_time'], 'string', 'max' => 50]
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
            'limit_max_amount' => 'Limit Max Amount',
            'limit_day_amount' => 'Limit Day Amount',
            'limit_day_total' => 'Limit Day Total',
            'limit_type' => 'Limit Type',
            'limit_start_time' => 'Limit Start Time',
            'limit_end_time' => 'Limit End Time',
            'create_time' => 'Create Time',
        ];
    }
    

    public function getBusinesschan(){
        return $this->hasOne(BusinessChan::className(),['channel_id' => 'channel_id']);
    }

    public function getChannel(){
        return $this->hasOne(Channel::className(),['id' => 'channel_id']);
    }

    public static function getStatus(){
        return [
            0 => '未开通',
            1 => '已开通',
            2 => '临时关闭',
        ];
    }
    public static function getLimitType(){
        return [
            0 => '不限',
            1 => '时间段',
            2 => '每日限',
            3 => '周末限',
        ];
    }
    
    public function getBankBychannelId($channel_id){
        if(empty($channel_id)){
            return null;
        }
        return $data = self::find()->where(['channel_id' => $channel_id])->all();
    }
    
    public function getBankByConditions($conditions , $one= false){
        $where = [];
        if(!empty($conditions)){
            $where = $conditions;
        }
        if($one){
            return $data = self::find()->where($where)->one();
        }else{
            return $data = self::find()->where($where)->all();
        }
    }
    
    public function createData($data){
        $data['create_time'] = date("Y-m-d H:i:s", time());
        $error = $this->chkAttributes($data);
        if ($error) {
            return $this->returnError(null, current($error));
        }
        //3 保存数据
        $result = $this->save();
        if (!$result) {
            return $this->returnError(null, '保存失败');
        }else{
            return $result;
        }
    }
    
    public function updateData($data){
        $error = $this->chkAttributes($data);
        if ($error) {
            return $this->returnError(null, current($error));
        }
        //3 保存数据
        $result = $this->save();
        if (!$result) {
            return $this->returnError(null, '保存失败');
        }else{
            return $result;
        }
    }


    public function getBanks($business_code,$bank_name,$card_type){
        $channelIds = (new BusinessChan)->getByBusiness($business_code);
        if (!$channelIds) {
            return null;
        }

        $where = [
            'and',
            [BusinessChan::tableName().'.status' => 1],
            [Channel::tableName().'.status' => 1],
            [ChannelBank::tableName().'.status' => 1],
            [ChannelBank::tableName().'.std_bankname' => $bank_name],
            [ChannelBank::tableName().'.card_type' => $card_type],
            ['in',BusinessChan::tableName().'.id',$channelIds]
        ];

        $banks = static::find()->joinWith('businesschan',true,'LEFT JOIN')
                        ->joinWith('channel',true,'LEFT JOIN')
                        ->where($where)
                        ->orderBy('sort_num asc')
                        ->all();

        if (empty($banks)) {
            return null;
        }
        return $banks;
    }

//微信支付宝路由
    public function getChannelInfo($business_code){
        $channelIds = (new BusinessChan)->getByBusiness($business_code);
        if (!$channelIds) {
            return null;
        }

        $where = [
            'and',
            [BusinessChan::tableName().'.status' => 1],
            [Channel::tableName().'.status' => 1],
            [ChannelBank::tableName().'.status' => 1],
            ['in',BusinessChan::tableName().'.id',$channelIds]
        ];

        $channels = static::find()->joinWith('businesschan',true,'LEFT JOIN')
                        ->joinWith('channel',true,'LEFT JOIN')
                        ->where($where)
                        ->orderBy('sort_num asc')
                        ->all();

        if (empty($channels)) {
            return null;
        }
        return $channels;
    }

    //-------------------------------京东快捷支付使用
    /**
     * 通过channel_id，银行名称获取对应的银行编码
     * @param $bank_name
     * @param $channel_id
     * @param $bank_name
     * @return mixed|null
     */
    public function getBankCode($channel_id,$bank_name){
        $where = [
            'and',
            ['channel_id' => $channel_id],
            ['bankname' => $bank_name],
        ];
        $channels = static::find()
            ->where($where)
            ->one();

        if (empty($channels)) {
            return false;
        }
        return ArrayHelper::getValue($channels,'bankcode');
    }
}
