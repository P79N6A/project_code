<?php

namespace app\models\bill;

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
class UpBillFile extends \app\models\BaseModel
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
        return 'up_bill_file';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['channel_id', 'source', 'file_status', 'uid'], 'integer'],
            [['create_time'], 'required'],
            [['create_time', 'last_modify_time'], 'safe'],
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
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
        ];
    }

    public function saveBillFile($data_set)
    {
        if (empty($data_set)){
            $this->returnError(false);
        }
        $curTime = date("Y-m-d H:i:s", time());
        $saveData = [
            'channel_id' => ArrayHelper::getValue($data_set, 'channel_id', 0), //出款通道id:0:未知,1:融宝,2:宝付,3:畅捷,4:玖富,5:微神马,6:新浪,7:小诺理财
            'source' => ArrayHelper::getValue($data_set, 'source', 0), //来源：0:初始,1:已上传,2:已下载
            'bill_file' => ArrayHelper::getValue($data_set, 'bill_file', ''), //上传文件名
            'file_status' => ArrayHelper::getValue($data_set, 'file_status', 0), //来源：0:初始, 1:锁定, 2:成功, 3:失败
            'uid' => ArrayHelper::getValue($data_set, 'uid', 0), //用户uid
            'create_time' => $curTime, //创建时间
            'last_modify_time' => $curTime, //更新时间
        ];
        if ($errors = $this->chkAttributes($saveData)) {
            return $this->returnError(false, implode('|', $errors));
        }
        $result = $this->save();
        return $result;
    }

    public function countAllData()
    {
        $where_config = [
            'AND',
            ['>=', 'create_time', date("Y-m-01", time())],
            ['<=', 'create_time', date("Y-m-d 23:59:59", time())],
        ];
        return self::find()->where($where_config)->count();
    }
    public function getAllData($pages)
    {
        if (empty($pages)){
            return false;
        }
        $where_config = [
            'AND',
            ['>=', 'create_time', date("Y-m-01", time())],
            ['<=', 'create_time', date("Y-m-d 23:59:59", time())],
        ];
        $result = self::find()
            ->where($where_config)
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy('create_time desc')
            ->all();
        return $result;
    }
    
    public function countFilterData($filter_where)
    {
        $data = self::find();
        if (!empty($filter_where['channel_id'])){
            $data->andWhere(['channel_id' => $filter_where['channel_id']]);
        }
        if (!empty($filter_where['start_time'])){
            $data->andWhere(['>=', 'create_time', $filter_where['start_time']. ' 00:00:00']);
        }
        if (!empty($filter_where['end_time'])){
            $data->andWhere(['<=', 'create_time', $filter_where['end_time']. ' 23:59:59']);
        }
        return $data->count();
    }
    public function getFilterData($pages, $filter_where)
    {
        if (empty($pages)){
            return false;
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
        return $result->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy('create_time desc')
            ->all();
    }

    /**
     * 获取数据
     * @param int $limit
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getData($limit = 100)
    {
        $whereconfig = [
            'file_status' =>self::FILE_STATUS_INIT,
        ];
        return self::find()->where($whereconfig)->limit($limit)->all();
    }

    /**
     * 锁定
     * @param $ids
     * @return int
     */
    public function lockRemit($ids) {
        if (!is_array($ids) || empty($ids)) {
            return 0;
        }
        $field = ['file_status' => static::FILE_STATUS_LOCK];
        $where = ['id' => $ids];
        $ups = static::updateAll($field, $where);
        return $ups;
    }

    /**
     * 成功
     * @return bool
     */
    public function successRemit() {
        $this->last_modify_time = date("Y-m-d H:i:s");
        $this->file_status = self::FILE_STATUS_SUCCESS;
        return $this->save();
    }
}