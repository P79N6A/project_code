<?php

namespace app\models\news;

use Yii;

/**
 * Class Juneactivity
 * @package app\models\news
 */
class Juneactivity extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'june_activity';
    }

 /**
 * @param bool $uid
 * @param array|null $sel
 * @return bool|int
 * 抽奖总次数修改(签到)
 */
   public function Handlesave($uid,$sel)
   {
       $result = Yii::$app->db->createCommand()->update('june_activity',[
           'total_num'=>$sel['total_num']+1,
           'sign_time'=>time(),
           'last_update_time'=>time(),
       ],['user_id'=>$uid])->execute();
       $this->sign_log($uid,"签到",'1');
       return $result;
   }

     /**
     * @param bool $uid
     * @param array|null $sel
     * @return bool|int
     * 已抽奖次数修改
     */
    public function Alreadysave($uid,$sel)
    {
        $result = Yii::$app->db->createCommand()->update('june_activity',[
            'already_num'=>$sel['already_num']+1,
            'last_update_time'=>time(),
        ],['user_id'=>$uid])->execute();
        return $result;
    }

    /**
     * @param $uid
     * @return bool|void
     * 添加数据
     */
   public function Handleadd($uid)
   {
       $result = Yii::$app->db->createCommand()->insert('june_activity',[
               'user_id'=>$uid,
               'create_time'=>time(),
               'sign_time'=>time(),
               'total_num'=>1,
               'last_update_time'=>time(),
               'version'=>1,
           ])->execute();
          $this->sign_log($uid,"签到",'1');
          return $result;
   }

    /**
     * @param $uid
     * 增加日志
     */
   public function sign_log($uid,$msg,$type)
   {
       //纪录日志
       Yii::$app->db->createCommand()->insert('june_activity_log',[
           'user_id'=>$uid,
           'create_time'=>time(),
           'action_result'=>$msg,
           'type'=>$type,
           'version'=>1,
       ])->execute();
   }

    /**
     * @param $uid
     * @return mixed
     * 查询用户的抽奖次数
     */
   public function luck_draw($uid)
   {
       $sel = $this::find()->select(['total_num','already_num','sign_time'])->where(['user_id'=>$uid])->asArray()->one();
       return $sel;
   }



}
