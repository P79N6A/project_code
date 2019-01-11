<?php

namespace app\models;

use app\common\Logger;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "soup_video".
 *
 * @property string $id
 * @property integer $aid
 * @property string $video_file
 * @property string $callbackurl
 * @property integer $notify_status
 * @property string $create_time
 * @property string $modify_time
 */
class SoupVideo extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'soup_video';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'notify_status'], 'integer'],
            [['callbackurl', 'create_time', 'modify_time'], 'required'],
            [['create_time', 'modify_time'], 'safe'],
            //[['video_file'], 'string', 'max' => 64],
            [['callbackurl', 'requestid', 'video_file'], 'string', 'max' => 200]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'aid' => Yii::t('app', 'Aid'),
            'video_file' => Yii::t('app', 'Video File'),
            'callbackurl' => Yii::t('app', 'Callbackurl'),
            'notify_status' => Yii::t('app', 'Notify Status'),
            'create_time' => Yii::t('app', 'Create Time'),
            'modify_time' => Yii::t('app', 'Modify Time'),
            'requestid'     => Yii::t('app', 'Requestid'),
        ];
    }

    public function getVideo()
    {
        $where_config = [
            'notify_status'   => 0,
        ];
        return self::find()->where($where_config)->limit(200)->all();
    }

    public function getRepayData()
    {
        $where_config = [
            'notify_status'   => 3,
        ];
        return self::find()->where($where_config)->limit(200)->all();
    }

    public function clockStatus()
    {
        $this -> notify_status = 1;
        $this -> modify_time = date("Y-m-d H:i:s");
        return $this->save();
    }

    public function successStatus()
    {
        $this -> notify_status = 2;
        $this -> modify_time = date("Y-m-d H:i:s");
        return $this->save();
    }

    public function saveData($data_set)
    {
        if (empty($data_set)){
            return false;
        }
        $cur_time = date("Y-m-d H:i:s", time());
        $save_data = [
                'aid'               => ArrayHelper::getValue($data_set, 'aid'), //应用编号
                'video_file'        => ArrayHelper::getValue($data_set, 'video_file'), //图片地址',
                'callbackurl'       => ArrayHelper::getValue($data_set, 'callbackurl'), //回调地址',
                'notify_status'     => ArrayHelper::getValue($data_set, 'notify_status'), //通知状态:0:初始; 1:通知中; 2:通知成功; 3:重试; 11:通知失败; 13:通知超限',
                'create_time'       => $cur_time, //添加时间',
                'modify_time'       => $cur_time, //最后修改时间',
                'requestid'         => (string)ArrayHelper::getValue($data_set, 'requestid'), //一亿元id',
        ];
        $errors = $this->chkAttributes($save_data);
        if ($errors){
            Logger::dayLog('soup/video', '保存数据错误', json_encode($errors));
            return $this->returnError(null, implode('|', $errors));
        }
        return $this->save();
    }
} 