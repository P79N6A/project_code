<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_activity_times".
 *
 * @property string $id
 * @property string $user_id
 * @property integer $activity_id
 * @property integer $total_times
 * @property integer $use_times
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class Activity_times extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_activity_times';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'user_id', 'total_times', 'use_times', 'last_modify_time', 'create_time'], 'required'],
            [[ 'user_id', 'activity_id', 'total_times', 'use_times', 'version'], 'integer'],
            [['user_id', 'total_times', 'use_times', 'last_modify_time', 'create_time'], 'required'],
            [['user_id', 'activity_id', 'total_times', 'use_times', 'version'], 'integer'],
            [['last_modify_time', 'create_time'], 'safe']
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
            'activity_id' => 'Activity ID',
            'total_times' => 'Total Times',
            'use_times' => 'Use Times',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }

    /**
     * 乐观所版本号
     * @return string
     */
    public function optimisticLock()
    {
        return "version";
    }

    public static function getLotteryNum($activity_id,$user_id){
        if (empty($activity_id) || !is_numeric($user_id)) {
            return 0;
        }
        $activity_times = self::find()->where(['activity_id' => $activity_id,'user_id' => $user_id])->one();
        if(!$activity_times){
            return 0;
        }
        return ($activity_times->total_times - $activity_times->use_times)>0 ? $activity_times->total_times - $activity_times->use_times: 0;
    }

    /*
     * 新增
     * */
    public function addActivitytimes($condition){
        if( !is_array($condition) || empty($condition) ){
            return false;
        }
        $data = $condition;
        $time = date('Y-m-d 00:00:00');
        $data['user_id'] = $condition['user_id'];
        $data['activity_id'] = $condition['activity_id'];
        $data['total_times'] = $condition['total_times'];
        $data['use_times'] = $condition['use_times'];
        $data['last_modify_time'] = $time;
        $data['create_time'] = $time;
        $error = $this->chkAttributes($data);
        if($error){
            return false;
        }
        return $this->save();
    }
}
