<?php

namespace app\models\news;

use app\commonapi\Logger;
use Yii;

/**
 * This is the model class for table "yi_api_return".
 *
 * @property string $id
 * @property string $from_code
 * @property string $api
 * @property string $return_code
 * @property string $return_msg
 * @property string $return_result
 * @property string $create_time
 */
class Api_return extends \app\models\BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_api_return';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['from_code', 'api'], 'required'],
            [['return_result'], 'string'],
            [['create_time'], 'safe'],
            [['from_code', 'api', 'return_code'], 'string', 'max' => 32],
            [['return_msg','unique'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'from_code' => 'From Code',
            'api' => 'Api',
            'unique' => 'Unique',
            'return_code' => 'Return Code',
            'return_msg' => 'Return Msg',
            'return_result' => 'Return Result',
            'create_time' => 'Create Time',
        ];
    }

    public function save_list($condition) {
        $data = $condition;
        $data['create_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            Logger::dayLog('channelapi/save_api_return', $error);
            return FALSE;
        }
        $result = $this->save();
        return $result;
    }

}
