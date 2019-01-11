<?php

namespace app\modules\newdev\controllers;

use app\commonapi\Logger;
use app\models\news\Coupon_apply;
use app\models\news\Juneactivitylog;
use app\models\news\User;
use yii;
use app\models\news\Juneactivity;
use app\models\news\Coupon_list;

/**
 * * 六月份活动
 * */
class JuneactivityController extends NewdevController {

    public $enableCsrfValidation = false;

    /**
     * 只有登陆帐号才可以访问
     * 子类直接继承
     */
    public function behaviors() {
        return [];
    }

    /**
     * 六月份活动入口
     */
    public function actionIndex() {
        $this->layout = 'new/juneactivity';
        $this->getView()->title = '扭蛋机';
        $uid = $this->get('user_id', '');
        if (empty($uid)) {
            $user = $this->getUser();
            if (empty($user)) {
                return $this->redirect('/new/reg?url=/new/juneactivity/index');
            } else {
                $uid = $user->user_id;
            }
        } else {
            $userModel = new User();
            $user = $userModel->getUserinfoByUserId($uid);
            Yii::$app->newDev->login($user, 1);
        }
        //查询用户今天是否签到
        $model = new Juneactivitylog();
        $Juneactivity = new Juneactivity();
        $is = $model->is_Sign_in($uid);
        //查询用户总抽奖次数
        $num_arr = $Juneactivity->luck_draw($uid);
        $num = $num_arr['total_num'] - $num_arr['already_num'];
        //获取时间 前端判断当前时间是否是活动期间
        $time = time();   //当前时间
        $starttime17 = strtotime('2018-6-17 00:00:00'); //17号
        $endtime18 = strtotime('2018-6-18 23:00:00'); //18号
        $starttime25 = strtotime('2018-6-25 00:00:00'); //25号
        $endtime26 = strtotime('2018-6-26 23:00:00'); //26号
        //获取微信分享接口所需相关参数
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')) {
            $isapp = 1;  //app端
        } else {
            $isapp = 2;  //h5端
        }
        //分享信息
        $share_info = array();
        $share_info['imgUrl'] = Yii::$app->request->hostInfo . '/newdev/images/juneactivity/junelogo.png';
        $share_info['link'] = Yii::$app->request->hostInfo . "/new/juneactivity";
        //渲染数据
        return $this->render('index', [
                    'num' => $num,
                    'is' => $is,
                    'isapp' => $isapp,
                    'share_info' => $share_info,
                    'jsinfo' => $this->getWxParam(),
                    'uid' => $uid,
                    'time' => $time,
                    'starttime17' => $starttime17,
                    'endtime18' => $endtime18,
                    'starttime25' => $starttime25,
                    'endtime26' => $endtime26,
        ]);
    }

    /**
     * 签到领币
     */
    public function actionSignin() {
        //六月13号活动开启
        $start = strtotime('2018-6-13 00:00:00');
        $time = time();
        if ($time < $start) {
            $error_code = 3;  //活动未开启
            return json_encode(['error_code' => $error_code]);
        }
        //获取用户id
        $user = $this->getUser();
        $uid = $user->user_id;
        //当前时间
        $date = date('Ymd');
        $june = new Juneactivity(); //调用model
        //查询该用户是否签过到
        $sel = $june->luck_draw($uid);
        if ($sel) {
            $sign_time = date('Ymd', $sel['sign_time']); //最后签到时间
            //判断用户今天是否签到
            if ($date == $sign_time) {
                $error_code = 5;  //今天已经签到
                return json_encode(['error_code' => $error_code]);
            }
            $result = $june->Handlesave($uid, $sel);  //签到天数修改
        } else {
            //用户首次签到 增加纪录
            $result = $june->Handleadd($uid, $sel);  //首次签到 添加数据
        }
        if ($result) {
            $error_code = 1;  //成功
        } else {
            $error_code = 2;   //失败
        }
        return json_encode(['error_code' => $error_code]);
    }

    /**
     * @return string
     * 抽奖条件判断
     */
    public function actionLotteryjudgment() {
        //获取用户id
        $user = $this->getUser();
        $uid = $user->user_id;
        //实例化model
        $model = new Juneactivity();
        //查询用户抽奖次数
        $sel = $model->luck_draw($uid);
        $num = $sel['total_num'] - $sel['already_num'];
        if ($num < 1) {
            return json_encode(['error_code' => 6]);   //抽奖次数不足
        }
        //修改已经抽奖次数
        $arr = $model->Alreadysave($uid, $sel);
        if (!$arr) {
            return json_encode(['error_code' => 7]); //抽奖失败(网络超时)
        }
        $date = date('Ymd',time()); //当前时间
        $time18 = date('Ymd',strtotime('2018-6-18'));
        $time26 = date('Ymd',strtotime('2018-6-26'));
        $logModel = new Juneactivitylog();
        $juneactivity = new Juneactivity();
        //判断当前时间是否是18号和26号
        if ($date == $time18) {
            //用户当天未抽奖 必中25元优惠券
            $result = $logModel->logsel18($uid);
            if (empty($result)) {
                $prize = array('id' => 2, 'prize' => '25元抵用券', 'v' => 35, 'val' => 25);
                //增加日志
                $juneactivity->sign_log($uid, $prize['prize'], '2');
                //发放优惠券
                $this->award($prize['val'], $uid);
                return json_encode(['error_code' => 1, 'result' => $prize]);
            }
        } elseif ($date == $time26) {
            //用户当天未抽奖 必中25元优惠券
            $result = $logModel->logsel26($uid);
            if (empty($result)) {
                $prize = array('id' => 2, 'prize' => '25元抵用券', 'v' => 35, 'val' => 25);
                //增加日志
                $juneactivity->sign_log($uid, $prize['prize'], '2');
                $this->award($prize['val'], $uid); //发放优惠券
                return json_encode(['error_code' => 1, 'result' => $prize]);
            }
        }
        //去抽奖
        $prize = $this->Luckdraw($uid);
        //增加日志
        $juneactivity->sign_log($uid, $prize['prize'], '2');
        if ($prize['val']) {
            $this->award($prize['val'], $uid); //发放优惠券
        }
        return json_encode(['error_code' => 1, 'result' => $prize]);
    }

    /**
     * 发放奖励
     */
    public function award($val, $uid) {
        //用户中奖信息
        $res['val'] = $val;
        //添加优惠券信息判断
        $arr = $this->judge($res['val']);
        if ($arr) {
            //实例化model
            $Coupon_list = new Coupon_list();
           //查询优惠群开始结束时间
            $apply = Coupon_apply::find()->select(['start_date','end_date'])->where(['id'=>$arr['apply_id']])->asArray()->One();
            //查询该用户手机号
            $moboile = User::find()->select(['mobile'])->where(['user_id' => $uid])->asArray()->One();
            $array = ['apply_id' => $arr['apply_id'], 'title' => $arr['prize'], 'type' => 1, 'val' => $res['val'], 'mobile' => $moboile['mobile'], 'start_date' => $apply['start_date'], 'end_date' => $apply['end_date']];
            $Coupon_list->addCoupon($array); //发券
        }else{
            Logger::dayLog('juneactivity',$uid,$arr);
        }
    }

    /**
     * @param $res
     * @return mixed
     * 发放优惠券
     * 添加优惠券信息判断
     */
    public function judge($res) {
        //添加优惠券列表信息判断
        $time = time();  // 当前时间
        $time17 = strtotime('2018-6-17 00:00:00'); //17号
        $time18 = strtotime('2018-6-18 23:00:00'); //18号
        $time25 = strtotime('2018-6-25 00:00:00'); //25号
        $time26 = strtotime('2018-6-26 23:00:00'); //26号
        if ($time > $time17 && $time < $time18) {
            switch ($res) {
                case 15:
                    $arr['prize'] = "15元抵用券";
                    $arr['apply_id'] = 5742;
                    break;
                case 25:
                    $arr['prize'] = "25元抵用券";
                    $arr['apply_id'] = 5747;
                    break;
                case 35:
                    $arr['prize'] = "35元抵用券";
                    $arr['apply_id'] = 5748;
                    break;
            }
        } elseif ($time > $time25 && $time < $time26) {
            switch ($res) {
                case 15:
                    $arr['prize'] = "15元抵用券";
                    $arr['apply_id'] = 5749;
                    break;
                case 25:
                    $arr['prize'] = "25元抵用券";
                    $arr['apply_id'] = 5750;
                    break;
                case 35:
                    $arr['prize'] = "35元抵用券";
                    $arr['apply_id'] = 5751;
                    break;
            }
        }
        if (empty($arr)) {
            return $arr = [];
        }
        return $arr;
    }

    /*
     * 奖项数组
     * 是一个二维数组，记录了所有本次抽奖的奖项信息，
     * 其中id表示中奖等级，prize表示奖品，v表示中奖概率。
     * 注意其中的v必须为整数，你可以将对应的 奖项的v设置成0，即意味着该奖项抽中的几率是0，
     * 数组中v的总和（基数），基数越大越能体现概率的准确性。
     * 如果v的总和是10000，那中奖概率就是万分之一了。
     * val代表面值
     */

    public function Luckdraw($uid) {
        //奖项列表及中奖概率
        $prize_arr = array(
            '0' => array('id' => 1, 'prize' => '15元抵用券', 'v' => 10, 'val' => 15),
            '1' => array('id' => 2, 'prize' => '25元抵用券', 'v' => 20, 'val' => 25),
            '2' => array('id' => 3, 'prize' => '35元抵用券', 'v' => 30, 'val' => 35),
            '3' => array('id' => 4, 'prize' => '45元抵用券', 'v' => 0, 'val' => 45),
            '4' => array('id' => 5, 'prize' => '60元抵用券', 'v' => 0, 'val' => 60),
            '5' => array('id' => 6, 'prize' => '50元京东卡', 'v' => 0, 'val' => 50),
            '6' => array('id' => 7, 'prize' => '100元京东卡', 'v' => 0, 'val' => 100),
            '7' => array('id' => 8, 'prize' => '200元京东卡', 'v' => 0, 'val' => 200),
            '8' => array('id' => 9, 'prize' => '500元京东卡', 'v' => 0, 'val' => 500),
            '9' => array('id' => 10, 'prize' => '谢谢参与', 'v' => 40, 'val' => ''),
        );
        /**
         * 保证抽中奖品之后该奖品不会被再次抽到
         * 把概率放到未中奖上面
         */
        $redis = Yii::$app->redis;
        $arrr = $redis->smembers($uid);
        if ($arrr) {
            foreach ($prize_arr as $k => $v) {
                foreach ($arrr as $kk => $vv) {
                    if ($v['id'] == $vv && $vv < 10) {
                        $prize_arr['9']['v']+=$prize_arr[$k]['v'];
                        unset($prize_arr[$k]);
                    }
                }
            }
        }
        /*
         * 每次前端页面的请求，PHP循环奖项设置数组，
         * 通过概率计算函数get_rand获取抽中的奖项id。
         * 将中奖奖品保存在数组$res['yes']中，
         * 最后输出json个数数据给前端页面。
         */
        foreach ($prize_arr as $key => $val) {
            $arr[$val['id']] = $val['v'];
        }
        $rid = $this->get_rand($arr); //根据概率获取奖项id
        if (empty($rid)) {
            $rid = 10;
        }
        //把用户中奖记录下来
        $redis->sadd($uid, $rid);
        $res = $prize_arr[$rid - 1]; //中奖项
        return $res;
    }

    /*
     * 经典的概率算法，
     * $proArr是一个预先设置的数组，
     * 假设数组为：array(100,200,300，400)，
     * 开始是从1,1000 这个概率范围内筛选第一个数是否在他的出现概率范围之内，
     * 如果不在，则将概率空间，也就是k的值减去刚刚的那个数字的概率空间，
     * 在本例当中就是减去100，也就是说第二个数是在1，900这个范围内筛选的。
     * 这样 筛选到最终，总会有一个数满足要求。
     * 就相当于去一个箱子里摸东西，
     * 第一个不是，第二个不是，第三个还不是，那最后一个一定是。
     * 这个算法简单，而且效率非常 高，
     */

    private function get_rand($proArr) {
        $result = '';
        //概率数组的总概率精度
        $proSum = array_sum($proArr);
        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset($proArr);
        return $result;
    }

}
