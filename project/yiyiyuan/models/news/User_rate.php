<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;
use app\models\news\User;
use app\models\news\Rate_setting;

/**
 * This is the model class for table "yi_user_rate".
 *
 * @property string $id
 * @property string $mobile
 * @property string $label
 * @property string $create_time
 */
class User_rate extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_user_rate';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'type', 'user_rate_id'], 'integer'],
            [['create_time', 'last_modify_time'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User Id',
            'type' => 'Type',
            'user_rate_id' => 'User Rate Id',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
        ];
    }

    public function addUserRate($user_id,$user_rate_id,$type){
        if (empty($user_id) || empty($user_rate_id) || empty($type)) {
            return false;
        }
        $now_time = date('Y-m-d H:i:s');
        $data = [
            'user_id'          => $user_id,
            'user_rate_id'     => $user_rate_id,
            'type'             => $type,
            'create_time'      => $now_time,
            'last_modify_time' => $now_time,
        ];
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        try {
            $result = $this->save();
            return $result;
        } catch (\Exception $ex) {
            return FALSE;
        }
    }

    /**
     * 添加用户费率/日息记录并获取费率、日息
     * @param $user_id
     * @return array|bool
     */
    public function getRate($user_id){
        if (empty($user_id)) return false;
        $rate_info   = self::find()->where(['user_id' => $user_id,'type'=>1])->one();
        $user_repeat = (new User)->isRepeatUser($user_id);
        $type = 1;
        if(!empty($rate_info)){
            if($rate_info->user_rate_id == 1 && $user_repeat!=0){
                $user_rate_id = 3;
                $re = $rate_info->updateRate($user_rate_id);
                if(!$re){
                    return false;
                }
            }else{
                $user_rate_id = $rate_info->user_rate_id;
            }
        }else{
            if($user_repeat == 0){
                $user_rate_id = 1;
            }else{
                $user_rate_id = 2;
            }
            $addUserRate = $this->addUserRate($user_id,$user_rate_id,$type);
            if(!$addUserRate){
                return false;
            }
        }
        $fee = $this->getFee($user_rate_id);
        if(!$fee){
            return false;
        }
        return $fee;
    }

    /**
     * 获取当前用户费率、日息
     * @param $user_rate_id
     * @param $type
     * @return array
     */
    public function getFee($user_rate_id,$type = 1){
        $rate = Rate_setting::find()->where(['rate_id' => $user_rate_id,'type'=>$type])->asArray()->all();
        //默认值服务费10% 日息0.05%
        $days = [7,14,21,28,56,60,84];
        if(empty($rate)){
            foreach ($days as $value){
                $withdraw[$value] = 0.1;
                $interest[$value] = 0.0005;
            }
        }else{
            foreach ($rate as $k=>$v){
                $withdraw[$v['day']] = $v['rate']/100;
                $interest[$v['day']] = $v['interest']/100;
            }
        }
        $rate_day = array_keys($withdraw);
        $lack_day = array_diff($days, $rate_day);
        if(!empty($lack_day)){
            foreach ($lack_day as $v){
                $withdraw[$v] = 0.0;
                $interest[$v] = 0.00098;
            }
        }
        $rateInfo = [
            'withdraw' => $withdraw,
            'interest' => $interest
        ];
        return $rateInfo;
    }

    public function updateRate($user_rate_id){
        if (empty($user_rate_id)) {
            return false;
        }
        $data = [
            'user_rate_id'     => $user_rate_id,
            'last_modify_time' => date('Y-m-d H:i:s'),
        ];
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 获取用户单条用户利率及日息
     * @param $user_id
     * @param $day
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getrateone($user_id, $day){
        $rate = User_rate::find()->where(['user_id'=>$user_id])->one();
        if(empty($rate)){
            return false;
        }
        $where = [
            'rate_id'=>$rate->user_rate_id,
            'day'=>$day,
        ];
        $res = Rate_setting::find()->where($where)->asArray()->one();
        $rate_setting['interest'] = 0.00098;
        $rate_setting['rate'] = 0.0;
        if(!empty($res)){
            $rate_setting['interest'] = $res['interest']/100;
            $rate_setting['rate'] = $res['rate']/100;
        }
        return $rate_setting;
    }

    /**
     * 返回利率和日息
     * @param $user_id
     * @param $day
     * @return array
     */
    public function getUserFee($user_id, $day){
        $user_id = intval($user_id);
        $day = intval($day);
        if(!$user_id || !$day){
            return [];
        }
       $userRate =  self::find()->where(['user_id'=>$user_id])->one();
        if(!$userRate){
            return [];
        }
        return $this->getFee($userRate->user_rate_id,1);
    }
}
