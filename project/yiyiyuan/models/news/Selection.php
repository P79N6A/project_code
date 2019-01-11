<?php

namespace app\models\news;

use app\commonapi\Logger;
use app\models\BaseModel;
use Exception;

/**
 * This is the model class for table "yi_selection".
 *
 * @property string $id
 * @property string $user_id
 * @property integer $requestid
 * @property string $task_id
 * @property string $process_code
 * @property string $response_msg
 * @property integer $type
 * @property integer $source
 * @property integer $req_source
 * @property string $start_date
 * @property string $end_date
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class Selection extends BaseModel
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_selection';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'type', 'source', 'last_modify_time', 'create_time'], 'required'],
            [['user_id', 'requestid', 'type', 'source', 'req_source', 'version'], 'integer'],
            [['start_date', 'end_date', 'last_modify_time', 'create_time'], 'safe'],
            [['task_id'], 'string', 'max' => 64],
            [['process_code'], 'string', 'max' => 6]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'requestid' => 'Requestid',
            'task_id' => 'Task ID',
            'process_code' => 'Process Code',
            'response_msg' => 'Response Msg',
            'type' => 'Type',
            'source' => 'Source',
            'req_source' => 'Req Source',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }

    public function getSelectionByRequestId($request_id)
    {
        if (empty($request_id)) {
            return NULL;
        }
        $selection = self::find()->where(['requestid' => $request_id])->one();
        return $selection;
    }

    public function saveSucc($reason, $time)
    {
        try {
            $this->process_code = '10008';
            $this->response_msg = $reason;
            $this->start_date = $time;
            $this->end_date = date('Y-m-d H:i:s', strtotime('+3 months', strtotime($time)));
            $this->last_modify_time = $time;
            return $this->save();
        } catch (Exception $ex) {
            Logger::dayLog('selectionModel', $this->id, $ex->getMessage());
            return FALSE;
        }
    }

    public function saveFail($reason, $time)
    {
        try {
            $this->process_code = '30000';
            $this->response_msg = $reason;
            $this->last_modify_time = $time;
            return $this->save();
        } catch (Exception $ex) {
            Logger::dayLog('selectionModel', $this->id, $ex->getMessage());
            return FALSE;
        }
    }

    public function saveGetting()
    {
        try {
            $this->process_code = '10002';
            $this->last_modify_time = date('Y-m-d H:i:s');
            return $this->save();
        } catch (Exception $ex) {
            Logger::dayLog('selectionModel', $this->id, $ex->getMessage());
            return FALSE;
        }
    }

    public function getByUserIdAndTpey($userId, $type)
    {
        if (empty($userId) || empty($type)) {
            return null;
        }
        $where = [
            'user_id' => $userId,
            'type' => $type
        ];
        return self::find()->where($where)->one();
    }

    //是否在有效期
    public function getValidity()
    {
        if (empty($this) || !is_object($this)) {
            return false;
        }
        if ($this->process_code != '10008') {
            return false;
        }
        $time = $this->last_modify_time;
        if (date('Y-m-d H:i:s', strtotime('+3 months', strtotime($time))) < date('Y-m-d H:i:s')) {
            return false;
        }
        return true;
    }

    public function addRecord($condition)
    {
        if (empty($condition) || !is_array($condition)) {
            return false;
        }
        $data = $condition;
        $time = date('Y-m-d H:i:s');
        $data['last_modify_time'] = $time;
        $data['create_time'] = $time;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    public function updateRecord($condition)
    {
        if (empty($condition) || !is_array($condition)) {
            return false;
        }
        $data = $condition;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    public function getSelectiontype($userId, $type)
    {
        if (empty($userId) || empty($type)) {
            return null;
        }
        $where = [
            'user_id' => $userId,
            'type' => $type
        ];
        return self::find()->where($where)->one();
    }

    /**
     * 获取最近一条认证成功记录
     * @param $userId
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getNewestHistory($userId)
    {
        if (empty($userId)) {
            return [];
        }
        $where = [
            'AND',
            ['user_id' => $userId],
            ['process_code' => '10008'],
            ['type' => [1, 2, 3, 4, 6, 7]],
        ];
        $selectionObj = self::find()->where($where)->orderBy(['last_modify_time' => SORT_DESC])->one();
        if (!empty($selectionObj)) {
            return $selectionObj;
        }
        return [];
    }

    /**
     * 检测选填资料是都完成
     * @param $userId
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getSelection($userId)
    {
        if (empty($userId)) {
            return false;
        }
        $where = [
            'AND',
            ['user_id' => $userId],
            ['process_code' => '10008'],
            ['type' => [1, 2, 3]],
        ];
        $SelectionCount = self::find()->where($where)->count();
        if ($SelectionCount>=3) {
            return true;
        }
        return false;
    }
	
	/**
	 * 获取用户是否存在认证成功的社保/公积金数据（贷后）
	 * @param type $userId
	 * @param type $type
	 * @return boolean
	 */
	public function getSocialByUserId($userId, $type) {
		if (empty($userId)) {
            return false;
        }
        $where = [
            'AND',
            ['user_id' => $userId],
            ['process_code' => '10008'],
            ['type' => $type],
        ];
        $SelectionCount = self::find()->where($where)->one();
        return !empty($SelectionCount) ? 1 : 2;
	}
}
