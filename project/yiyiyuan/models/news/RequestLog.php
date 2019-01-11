<?php

namespace app\models\news;

use app\common\Logger;
use app\models\BaseModel;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "yi_request_log".
 *
 * @property integer $id
 * @property string $user_id
 * @property integer $salf
 * @property string $key
 * @property integer $source
 * @property string $create_time
 * @property string $modify_time
 */
class RequestLog extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_request_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'salf', 'source'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['key'], 'string', 'max' => 32]
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
            'salf' => 'Salf',
            'key' => 'Key',
            'source' => 'Source',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    public function saveData($postData) {
        if (empty($postData)) {
            return false;
        }
        $data_set = [
            'user_id'           => ArrayHelper::getValue($postData, 'user_id'),
            'salf'              => ArrayHelper::getValue($postData, 'salf'),
            'key'               => ArrayHelper::getValue($postData, 'key'),
            'source'            => ArrayHelper::getValue($postData, 'source'),
            'create_time'       => date("Y-m-d H:i:s", time()),
            'modify_time'       => date("Y-m-d H:i:s", time()),
        ];
        $error = $this->chkAttributes($data_set);
        if ($error) {
            Logger::dayLog("gettinginfo", "save_data:", json_encode($error)."\n");
            return $this->returnError(false, implode("|", $error));
        }
        return $this->save();
    }

    public function getData($user_id)
    {
        if (empty($user_id)){
            return false;
        }
        return self::find()->where(['user_id'=>$user_id])->orderBy("id desc")->one();
    }

    public function updateData($data_set)
    {
        if (empty($data_set)){
            return false;
        }

        $this->salf = ArrayHelper::getValue($data_set, 'salf');
        $this->key = ArrayHelper::getValue($data_set, 'key');
        $this->modify_time = date("Y-m-d H:i:s");

        $res = $this->save();
        return $res;
    }

    public function getDataForKey($key)
    {
        if (empty($key)){
            return false;
        }
        return self::find()->where(['key'=>$key])->asArray()->orderBy("id desc")->one();
    }
}