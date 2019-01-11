<?php

namespace app\models\xs;

use Yii;
use app\common\Logger;

/**
 * This is the model class for table "sync_id_list".
 *
 * @property string $id
 * @property string $start_id
 * @property string $end_id
 * @property integer $sync_status
 * @property string $sync_type
 * @property string $modify_time
 * @property string $create_time
 */
class SyncIdList extends \app\models\repo\CloudBase
{
    const SYNC_INIT = 0;
    const SYNC_DOING = 1;
    const SYNC_SUCCESS = 2;
    const SYNC_ERROR = 9;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sync_id_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start_id', 'end_id', 'sync_status'], 'integer'],
            [['modify_time', 'create_time'], 'required'],
            [['modify_time', 'create_time'], 'safe'],
            [['sync_type'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'start_id' => '开始id',
            'end_id' => '结束id',
            'sync_status' => '脚本状态 0 初始  1 进行中 2 成功 9异常',
            'sync_type' => '脚本类型',
            'modify_time' => '修改时间',
            'create_time' => '创建时间',
        ];
    }
    public function getOne($where = null,$fields = null){
        $condition =  $this->find();
        if ($where) {
           $condition  = $condition->where($where);
        }

        if ($fields) {
        $condition  = $condition->select($fields);
        }
        return $condition->orderBy('id DESC')->limit(1)->one();
    }
    /*
     * 保存借款数据
     */
    public function saveData($postData) {
        $time = date("Y-m-d H:i:s");
        $postData['modify_time'] = $time;
        $postData['create_time'] = $time;
        $error = $this->chkAttributes($postData);
        if ($error) {
            Logger::dayLog('syncIdList','save fail',$error,$postData);
            return false;
        }
        return $this->save();;
    }
}