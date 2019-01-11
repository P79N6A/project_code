<?php

namespace app\models\news;
use app\models\news\User;
use app\models\news\Prize;

use Yii;

/**
 * This is the model class for table "yi_prize_list".
 *
 * @property integer $id
 * @property integer $activity_id
 * @property integer $user_id
 * @property integer $prize_id
 * @property string $title
 * @property string $start_time
 * @property string $end_time
 * @property integer $source
 * @property integer $status
 * @property string $mobile
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class PrizeList extends \app\models\BaseModel 
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_prize_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['activity_id', 'user_id', 'prize_id', 'source', 'status', 'version','use_status'], 'integer'],
            [['user_id', 'prize_id', 'title'], 'required'],
            [['start_time', 'end_time', 'last_modify_time', 'create_time'], 'safe'],
            [['title', 'mobile'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'activity_id' => 'Activity ID',
            'user_id' => 'User ID',
            'prize_id' => 'Prize ID',
            'title' => 'Title',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'source' => 'Source',
            'status' => 'Status',
            'mobile' => 'Mobile',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'use_status' => 'Use Status',
            'version' => 'Version',
        ];
    }

    public function getActivitynew(){
        return $this->hasOne(Activitynew::classname(),['id' => 'activity_id']);
    }

    public function getUser(){
        return $this->hasOne(User::classname(),['id' => 'user_id']);
    }

    public function getPirze(){
        return $this->hasOne(Prize::classname(),['id' => 'prize_id']);
    }

    public function addPirzeList($user_id,$prize_id){
        if (empty($prize_id) || empty($user_id)) {
             return false;
         }
        $user = User::findOne($user_id);
        $prize = Prize::findOne($prize_id);
        $activitynew = $prize->activitynew;


        $time_now = date('Y-m-d H:i:s');
        $data = array(
                'user_id' => $user_id,
                'prize_id' => $prize_id,
                'activity_id' => $prize->activity_id,
                'title' => $prize->title,
                'start_time' => $activitynew->start_date,
                'end_time' => $activitynew->end_date,
                'source' => 1,
                'status' => 0,
                'mobile' => $user->mobile ? $user->mobile : '',
                'last_modify_time' => $time_now,
                'create_time' => $time_now,
                'use_status' => 0
        );

        if($prize->type == 1){
            //优惠券
            $condition = array(
                    'title' => $prize->title,
                    'val' => (int)$prize->val,
                    'type' => 1,
                    'start_date' => date("Y-m-d",strtotime($activitynew->start_date)),
                    'end_date' => date("Y-m-d",strtotime($activitynew->end_date."+ 1 day - 1 second")),
                    'mobile' => $user->mobile ? $user->mobile : '',
                );
            if((new Coupon_list())->addCoupon($condition)){
                $data['status'] = 6;
                $data['use_status'] = 1;
            }
        }elseif($prize->type == 2){
            $data['status'] = 6;
            $data['use_status'] = 1;
        }

        $error = $this->chkAttributes($data);
        if($error){
            return false;
        }

        if(!$this->save()){
            return false;
        }
        return $this->id;
    }

}
