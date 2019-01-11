<?php

namespace app\models\day;

use app\models\BaseModel;
use Yii;

/**
 * This is the model class for table "qj_juxinli".
 *
 * @property string $id
 * @property string $user_id
 * @property string $requestid
 * @property string $process_code
 * @property string $status
 * @property string $response_type
 * @property integer $type
 * @property string $user_name
 * @property string $password
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $source
 */
class Juxinli_guide extends BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'qj_juxinli';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'last_modify_time', 'create_time'], 'required'],
            [['user_id', 'requestid', 'type', 'source'], 'integer'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['process_code', 'status'], 'string', 'max' => 6],
            [['response_type'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'requestid' => 'Requestid',
            'process_code' => 'Process Code',
            'status' => 'Status',
            'response_type' => 'Response Type',
            'type' => 'Type',
            'user_name' => 'User Name',
            'password' => 'Password',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'source' => 'Source',
        ];
    }

    public function getJuxinli($user_id) {
        if (empty($user_id) || !is_numeric($user_id)) {
            return NULL;
        }
        $mobile = self::find()->where(['user_id' => $user_id, 'process_code' => '10008'])->one();
        return $mobile;
    }

    public function saveRecord($user_id) {
        $time = date('Y-m-d H:i:s');
        $this->user_id = $user_id;
        $this->process_code = '10008';
        $this->status = (string) 1;
        $this->last_modify_time = $time;
        $this->create_time = $time;
        $this->source = 1;
        $result = $this->save();
        return $result;
    }

}
