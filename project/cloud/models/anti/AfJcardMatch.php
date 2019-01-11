<?php

namespace app\models\anti;

use Yii;
use app\common\Logger;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "af_jcard_match".
 *
 * @property string $id
 * @property string $jxl_id
 * @property string $status
 * @property string $tag_info
 * @property string $source
 * @property string $create_time
 */
class AfJcardMatch extends AntiBaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'af_jcard_match';
    }

    /** 
     * @inheritdoc 
     */ 
    public function rules() 
    { 
        return [
            [['request_id', 'aid', 'user_id','create_time'], 'required'],
            [['request_id', 'aid', 'user_id'], 'integer'],
            [['create_time'], 'safe'],
            [['jcard_result'], 'string', 'max' => 100],
        ];
    } 

    /** 
     * @inheritdoc 
     */ 
    public function attributeLabels() 
    { 
        return [ 
            'id' => 'ID',
            'request_id' => '请求处理id',
            'aid' => '业务id',
            'user_id' => '用户id',
            'jcard_result' => '间接关系匹配结果',
            'create_time' => '添加时间',
        ];
    }

    /**
     * 保存数据
     * @param $postData
     * @return bool
     */
    public function saveJcard($postData) {
        // 检测数据
        if (!$postData) {
            return $this->returnError(false, '不能为空');
        }
        $time = date('Y-m-d H:i:s');
        $data = [
            'request_id' => isset($postData['request_id']) ? $postData['request_id'] : 0,
            'aid' => isset($postData['aid']) ? $postData['aid'] : 0,
            'user_id' => isset($postData['user_id']) ? $postData['user_id'] : 0,
            'jcard_result' => isset($postData['jcard_result']) ? $postData['jcard_result'] : '',
            'create_time' => $time,
        ];

        $error = $this->chkAttributes($data);
        if ($error) {
            Logger::dayLog('saveJcard','JcardMatch db save fail,reason:',$error);
            return $this->returnError(false, $error);
        }
        $res = $this->save(); 
        if (!$res) {
            return false;
        }
        return $this->id;
    }
}
