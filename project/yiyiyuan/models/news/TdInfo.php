<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;

/**
 * This is the model class for table "td_info".
 *
 * @property integer $id
 * @property string $date
 * @property string $voice_password
 * @property string $voice_record_paths
 * @property integer $base_service_count
 * @property integer $manual_service_count
 * @property integer $complement_service_count
 * @property string $summary_result
 * @property string $phone_summary_result
 * @property string $message_summary_result
 */
class TdInfo extends BaseModel {
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_td_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
//            [['id'], 'required'],
            [['id', 'base_service_count', 'manual_service_count', 'complement_service_count'], 'integer'],
            [['date','create_time','modify_time'], 'safe'],
            [['voice_password'], 'string', 'max' => 20],
            [['voice_record_paths'], 'string', 'max' => 1000],
            [['summary_result', 'message_summary_result'], 'string', 'max' => 255],
            [['phone_summary_result'], 'string', 'max' => 1500]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => '账单日子',
            'voice_password' => 'Voice密码',
            'voice_record_paths' => 'Voice路径',
            'base_service_count' => 'Base Service Count',
            'manual_service_count' => 'Manual Service Count',
            'complement_service_count' => 'Complement Service Count',
            'summary_result' => 'Summary Result',
            'phone_summary_result' => 'Phone Summary Result',
            'message_summary_result' => 'Message Summary Result',
            'create_time' => 'create_time',
            'modify_time' => 'modify_time',
        ];
    }
    
    public function addData($data){
        $data['create_time'] = date('Y-m-d H:i:s',time());
        $data['modify_time'] = date('Y-m-d H:i:s',time());
        $error = $this->chkAttributes($data);
        if ($error)
            return false;
        $this->attributes = $data;
        return $this->save();
    }
}
