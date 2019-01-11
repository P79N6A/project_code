<?php

namespace app\models\news;

use Yii;

/**
 * Class Juneactivitylog
 * @package app\models\news
 */
class Juneactivitylog extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'june_activity_log';
    }

    /**
     * @param $uid
     * @return array|null|\yii\db\ActiveRecord|static
     * 查询用户今天是否签到
     */
    public function is_Sign_in($uid)
    {
        //获取今天的开始时间
        $starttime = strtotime(date('Ymd'));
        //获取今天的结束时间
        $endtime = strtotime(date('Ymd') . '23:00:00');
        //查询用户今天是否签到
        $log = Juneactivitylog::find()->select('id')->where(['user_id' => $uid]);
        $where = ['and',
            ['>', 'create_time', $starttime],
            ['<', 'create_time', $endtime],
            ['type' => 1],
        ];
        $log = $log->andWhere($where)->asArray()->One();
        return $log;
    }

    /**
     * @param $uid
     * @return array|null|\yii\db\ActiveRecord|static
     * 判断用户是否在18号
     */
    public function logsel18($uid)
    {
        $starttime18 = strtotime('2018-6-18');  //18号活动首次必中
        $endtime18 = strtotime('2018-6-18 23:00:00');  //18号活动首次必中
        $where = ['and',
            ['>', 'create_time', $starttime18],
            ['<', 'create_time', $endtime18],
            ['=', 'user_id', $uid],
            ['=', 'type', 2]
        ];
        $log = $this->find();
        $log = $log->where($where)->asArray()->One();
        return $log;
    }

    /**
     * @param $uid
     * @return array|null|\yii\db\ActiveRecord|static
     * 判断用户是否26号首次抽奖
     */
    public function logsel26($uid)
    {
        $starttime26 = strtotime('2018-6-26');  //26号活动首次必中
        $endtime26 = strtotime('2018-6-26 23:00:00');  //26号活动首次必中
        $Where = ['and',
            ['>', 'create_time', $starttime26],
            ['<', 'create_time', $endtime26],
            ['=', 'user_id', $uid],
            ['=', 'type', 2]
        ];
        $log = $this->find();
        $log = $log->Where($Where)->asArray()->One();
        return $log;
    }

}