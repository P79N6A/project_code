<?php

namespace app\models;
use app\common\Logger;
use Yii;

/**
 * This is the model class for table "settle_bill_original".
 *
 * @property integer $id
 * @property string $req_id
 * @property string $settle_amount
 * @property string $identityid
 * @property string $user_mobile
 * @property string $guest_account_name
 * @property string $guest_account
 * @property integer $status
 * @property integer $type
 * @property string $create_time
 */
class SettleBillOriginal extends \app\models\BaseModel
{
    const STATUS_INIT = 0;
    const STATUS_SUCCESS = 1;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'settle_bill_original';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['req_id', 'identityid', 'user_mobile', 'guest_account_name', 'guest_account', 'create_time'], 'required'],
            [['settle_amount'], 'number'],
            [['status', 'type'], 'integer'],
            [['create_time','update_time'], 'safe'],
            [['req_id'], 'string', 'max' => 40],
            [['identityid'], 'string', 'max' => 20],
            [['user_mobile', 'guest_account_name'], 'string', 'max' => 60],
            [['guest_account'], 'string', 'max' => 30],
            [['req_id'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'req_id' => 'Req ID',
            'settle_amount' => 'Settle Amount',
            'identityid' => 'Identityid',
            'user_mobile' => 'User Mobile',
            'guest_account_name' => 'Guest Account Name',
            'guest_account' => 'Guest Account',
            'status' => 'Status',
            'type' => 'Type',
            'create_time' => 'Create Time',
        ];
    }
    public function getDataList($offset,$limit){
        $where = ['status' => static::STATUS_INIT];
        $offset=intval($offset);
        $limit=intval($limit);
        $data = static::find()->where($where)->offset($offset)->limit($limit)->all();
        $id = \yii\helpers\ArrayHelper::getValue($data[0],'id');
        Logger::dayLog('settlebilloriginal','offset',$offset,'limit',$limit,'datacount',count($data),'id',$id);
        return $data;
    }
    public function getDataList1($limit){
        $where = ['status' => static::STATUS_INIT];
        // 按查询时间排序
        $data = static::find()->where($where)->offset(0)->limit($limit)->all();
        return $data;
    }
    public function updateByClientId($client_id){
        $where = ['req_id'=>$client_id];
        $data = [
            'status'=>static::STATUS_SUCCESS,
            'update_time'=>date('Y-m-d H:i:s')
        ];
        $result = static::updateAll($data,$where);
        return $result;
    }
}