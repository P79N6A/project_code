<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_video_auth".
 *
 * @property string $id
 * @property string $user_id
 * @property string $request_id
 * @property integer $video_auth_status
 * @property integer $liveness_score
 * @property string $image_url
 * @property integer $return_code
 * @property string $return_msg
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $version
 */
class Video_auth extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_video_auth';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'request_id', 'create_time', 'last_modify_time', 'version'], 'required'],
            [['user_id', 'video_auth_status', 'return_code', 'version'], 'integer'],
            [['liveness_score'], 'number'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['request_id'], 'string', 'max' => 32],
            [['image_url'], 'string', 'max' => 128],
            [['return_msg'], 'string', 'max' => 64]
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
            'request_id' => 'Request ID',
            'video_auth_status' => 'Video Auth Status',
            'liveness_score' => 'Liveness Score',
            'image_url' => 'Image Url',
            'return_code' => 'Return Code',
            'return_msg' => 'Return Msg',
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
            'version' => 'Version',
        ];
    }

    /**
     * 获取当天最后一次视频认证信息
     * @param $user_id
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getAuthByUserID($user_id)
    {
        $begin_time = date('Y-m-d' . ' 00:00:00');
        $end_time = date('Y-m-d' . ' 23:59:59');
        $where = [
            'AND',
            ['>=',self::tableName().'.create_time',$begin_time],
            ['<',self::tableName().'.create_time',$end_time],
            [self::tableName().'.user_id'=>$user_id],
        ];
        return self::find()->where($where)->orderBy('id desc')->one();
    }

    /**
     * 获取视频认证信息
     * @param $request_id
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getAuthByReqID($request_id)
    {
        return self::find()->where(['request_id' => $request_id])->one();
    }

    /**
     * 统计当天视频认证次数
     * @param $user_id
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getAuthCount($user_id)
    {
        $begin_time = date('Y-m-d' . ' 00:00:00');
        $end_time = date('Y-m-d' . ' 23:59:59');
        $where = [
            'AND',
            ['>=',self::tableName().'.create_time',$begin_time],
            ['<',self::tableName().'.create_time',$end_time],
            [self::tableName().'.user_id'=>$user_id],
        ];
        return self::find()->where($where)->orderBy('id desc')->count();
    }

    /**
     * 保存认证信息
     * @param $data
     * @return bool
     */
    public function saveVideo($data){
        if(empty($data) || !is_array($data)){
            return false;
        }
        $condition = $data;
        $now = date('Y-m-d H:i:s');
        $condition['create_time'] = $now;
        $condition['last_modify_time'] = $now;
        $condition['version'] = 0;
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    public function updateAuth($data){
        if(empty($data) || !is_array($data)){
            return false;
        }
        $condition = $data;
        $condition['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 改为中间态
     * @return bool
     */
    public function updateMid()
    {
        try{
            $this->video_auth_status = -1;
            $this->last_modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        }catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * 改为失败
     * @return bool
     */
    public function updateFail(){
        try{
            $this->video_auth_status = 2;
            $this->last_modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        }catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * 改为认证成功
     * @return bool
     */
    public function updateSuccess(){
        try{
            $this->video_auth_status = 3;
            $this->last_modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        }catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }
}
