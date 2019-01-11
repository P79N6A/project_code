<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_activity_times_list".
 *
 * @property string $id
 * @property string $user_id
 * @property integer $activity_id
 * @property integer $type
 * @property integer $rule_condition
 * @property integer $num
 * @property string $loan_id
 * @property integer $prize_list_id
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class Activity_times_list extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_activity_times_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'activity_id', 'type', 'num', 'create_time'], 'required'],
            [['user_id', 'activity_id', 'type', 'rule_condition', 'num', 'loan_id', 'prize_list_id', 'version'], 'integer'],
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
            'type' => 'Type',
            'rule_condition' => 'Rule Condition',
            'num' => 'Num',
            'loan_id' => 'Loan ID',
            'prize_list_id' => 'Prize List ID',
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


    public function addActivityTimeList($data)
    {
        if (!is_array($data) || empty($data)) {
            return false;
        }
        $time = date('Y-m-d H:i:s');
        $data = $data;
        $data['last_modify_time'] = $time;
        $data['create_time'] = $time;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        if(!$this->save()){
            return false;
        }

        //修改抽奖次数记录
        $activity_times = Activity_times::find()->where(['user_id' => $data['user_id'],'activity_id' => $data['activity_id']])->one();
        if(!empty($activity_times)){
            if($data['type'] == 1){
                $activity_times->updateCounters(['total_times' => $data['num']]);
            }elseif($data['type'] == 2){
                $activity_times->updateCounters(['use_times' => $data['num']]);
            }
            //修改抽奖次数
            $activity_times->last_modify_time = date('Y-m-d H:i:s');
            $activity_times->save();
        }

        return $this->id;
    }
}
