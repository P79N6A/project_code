<?php

namespace app\modules\balance\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "up_bill_file".
 *
 * @property string $id
 * @property integer $channel_id
 * @property integer $source
 * @property string $bill_file
 * @property integer $file_status
 * @property integer $uid
 * @property string $create_time
 * @property string $last_modify_time
 */
class UpPaymentFile extends \app\models\BaseModel
{
    const FILE_STATUS_INIT = 0; //0:初始
    const FILE_STATUS_LOCK = 1; //1:锁定
    const FILE_STATUS_SUCCESS = 2; //2:成功
    const FILE_STATUS_FAIL = 3; //3:失败',
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cg_up_payment_file';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['channel_id', 'source', 'file_status', 'uid','type'], 'integer'],
            [['create_time'], 'required'],
            [['create_time', 'last_modify_time','start_time','end_time'], 'safe'],
            [['bill_file'], 'string', 'max' => 255]
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
            'source' => 'Source',
            'bill_file' => 'Bill File',
            'file_status' => 'File Status',
            'uid' => 'Uid',
            'type' =>'Type',
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
            'start_time'  => 'Start Time',
            'end_time'  => 'End Time',
        ];
    }

    /**
     * 保存数据
     * @param $data_set
     * @return bool|false|null
     */
    public function savePassagewayFile($data_set)
    {
        if (empty($data_set)){
            $this->returnError(false);
        }
        $curTime = date("Y-m-d H:i:s", time());
        $saveData = [
            'channel_id' => ArrayHelper::getValue($data_set, 'channel_id', 0),
            'source' => ArrayHelper::getValue($data_set, 'source', 0), //来源：0:初始,1:已上传,2:已下载
            'bill_file' => ArrayHelper::getValue($data_set, 'bill_file', ''), //上传文件名
            'file_status' => ArrayHelper::getValue($data_set, 'file_status', 0), //来源：0:初始, 1:锁定, 2:成功, 3:失败
            'uid' => ArrayHelper::getValue($data_set, 'uid', 0), //用户uid
            'return_channel' => ArrayHelper::getValue($data_set, 'return_channel', 0), //回款通道
            'type'   => ArrayHelper::getValue($data_set, 'type', 1), //上传账单类型
            'create_time' => $curTime, //创建时间
            'last_modify_time' => $curTime, //更新时间
            'start_time'    => ArrayHelper::getValue($data_set, 'start_time'),
            'end_time'     => ArrayHelper::getValue($data_set, 'end_time'),
        ];
        if ($errors = $this->chkAttributes($saveData)) {
            return $this->returnError(false, implode('|', $errors));
        }
        $result = $this->save();
        return $result;
    }

    /**
     * 计算时间区间条数
     * @param $filter_where
     * @return int
     */
    public function countPaymentData($filter_where)
    {
        if (empty($filter_where)){
            return 0;
        }
        $result = self::find();
        if (!empty($filter_where['channel_id'])){
            $result->andWhere(['channel_id' => $filter_where['channel_id']]);
        }
        if (!empty($filter_where['start_time'])){
            $result->andWhere(['>=', 'create_time', $filter_where['start_time']. ' 00:00:00']);
        }
        if (!empty($filter_where['end_time'])){
            $result->andWhere(['<=', 'create_time', $filter_where['end_time']. ' 23:59:59']);
        }
        if (!empty($filter_where['type'])){
            $result->andWhere([ 'type'=> $filter_where['type']]);
        }
        return $result->count();
    }

    /**
     * 获取时间区间的数据
     * @param $pages
     * @param $filter_where
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    public function getAllData($pages, $filter_where)
    {
        if (empty($pages)){
            return false;
        }
        $result = self::find();
        if (!empty($filter_where['channel_id'])){
            $result->andWhere(['channel_id' => $filter_where['channel_id']]);
        }
        if (!empty($filter_where['type'])){
            $result->andWhere(['type' => $filter_where['type']]);
        }
        if (!empty($filter_where['start_time'])){
            $result->andWhere(['>=', 'create_time', $filter_where['start_time']. ' 00:00:00']);
        }
        if (!empty($filter_where['end_time'])){
            $result->andWhere(['<=', 'create_time', $filter_where['end_time']. ' 23:59:59']);
        }
        return $result->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy('create_time desc')
            ->all();
    }

    /**
     * 锁定
     * @return bool
     */
    public function lockFileStatus($value)
    {
        $value->file_status = 1;
        $value->last_modify_time = date('Y-m-d H:i:s');
        return $value->save();

    }

    /**
     * 获取初始化数据
     * @param int $limit
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getRunData($limit = 100)
    {
        return  self::find()->where(['file_status'=>0])->limit($limit)->all();

    }

    public function successFileStatus($value)
    {
        $value->file_status = 2;
        $value->last_modify_time = date('Y-m-d H:i:s');
        return $value->save();
    }
}