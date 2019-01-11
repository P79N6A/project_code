<?php

namespace app\models;

use app\common\Logger;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "soup_stateless".
 *
 * @property string $id
 * @property string $video_file
 * @property string $request_id
 * @property string $code
 * @property integer $passed
 * @property string $liveness_score
 * @property string $image_timestamp
 * @property string $base64_image
 * @property string $message
 * @property string $create_time
 * @property string $modify_time
 */
class SoupStateless extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'soup_stateless';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['passed'], 'integer'],
            [['liveness_score', 'image_timestamp'], 'number'],
            [['create_time', 'modify_time'], 'required'],
            [['create_time', 'modify_time'], 'safe'],
            [['request_id', 'message'], 'string', 'max' => 64],
            [['code'], 'string', 'max' => 10],
            [['yirequestid', 'video_file'], 'string', 'max' => 200],
            [['base64_image'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'video_file' => Yii::t('app', 'Video File'),
            'request_id' => Yii::t('app', 'Request ID'),
            'code' => Yii::t('app', 'Code'),
            'passed' => Yii::t('app', 'Passed'),
            'liveness_score' => Yii::t('app', 'Liveness Score'),
            'image_timestamp' => Yii::t('app', 'Image Timestamp'),
            'base64_image' => Yii::t('app', 'Base64 Image'),
            'message' => Yii::t('app', 'Message'),
            'create_time' => Yii::t('app', 'Create Time'),
            'modify_time' => Yii::t('app', 'Modify Time'),
            'yirequestid'   =>  Yii::t('app', 'Yirequestid'),
        ];
    }

    public function saveData($data_set)
    {
        if (empty($data_set)){
            return false;
        }
        $cur_time = date("Y-m-d H:i:s", time());
        $passed = ArrayHelper::getValue($data_set, 'passed') ? 1 : 0;
        $save_data = [
            'video_file'            => ArrayHelper::getValue($data_set, 'video_file'), //图片地址
            'request_id'            => ArrayHelper::getValue($data_set, 'request_id'), //本次请求的id
            'code'                  => ArrayHelper::getValue($data_set, 'code'), //响应状态
            'passed'                => $passed, //是否通过活体检测1:通过  0没有通过
            'liveness_score'        => ArrayHelper::getValue($data_set, 'liveness_score', 0.00), //静默活体检测得分（供参考）
            'image_timestamp'       => ArrayHelper::getValue($data_set, 'image_timestamp', 0.00), //视频选帧时间戳
            'base64_image'          => ArrayHelper::getValue($data_set, 'base64_image'), //base64编码后的图片文件流(可选，默认不返回)
            'message'               => ArrayHelper::getValue($data_set, 'message'), //响应信息
            'yirequestid'           => ArrayHelper::getValue($data_set, 'yirequestid'), 
            'create_time'           => $cur_time, //添加时间
            'modify_time'           => $cur_time, //最后修改时间
        ];
        $errors = $this->chkAttributes($save_data);
        if ($errors){
            Logger::dayLog('soup/stateless', '保存数据错误', json_encode($errors));
            return $this->returnError(null, implode('|', $errors));
        }
        return $this->save();
    }


    public function getOne($request_id)
    {
        if (empty($request_id)){
            return false;
        }
        return self::find()->where(['yirequestid'=> $request_id]) -> orderBy("id desc") -> limit(1) -> one();
    }
}