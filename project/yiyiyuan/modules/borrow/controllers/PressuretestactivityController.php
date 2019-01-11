<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/9
 * Time: 14:13
 */

namespace app\modules\borrow\controllers;
use app\models\dev\Attention;
use app\models\news\ActivityFalseData;
use app\models\news\Coupon_list;
use app\models\news\User;
use Yii;

class PressuretestactivityController extends BorrowController
{
    public $num = 20; //总共显示条数
    public $prefix = 'redis_key';  //redis  前缀
    public $enableCsrfValidation = false;
    /**
     * 只有登陆帐号才可以访问
     * 子类直接继承
     */
    public function behaviors() {
        return [];
    }

    /**
     * @return string
     * 活动页面
     */
   public function actionIndex()
   {
       $this->layout = "pressuretestactivity";
       $uid = $this->get('user_id', '');
       //获取用户
       if (empty($uid)) {
           $user = $this->getUser();
           if (!empty($user)){
               $uid = $user->user_id;
           }
       } else {
           $userModel = new User();
           $user = $userModel->getUserinfoByUserId($uid);
           Yii::$app->newDev->login($user, 1);
       }
       //邀请码来源
       $invite_code = $this->get('invite_code', '');
       //获取微信分享接口所需相关参数
       if (strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')) {
           $isapp = 1;  //app端
       } else {
           $isapp = 2;  //h5端
       }
       //判断是否登录 invite_code的参数
       if($user){
           $path = "/borrow/pressuretestactivity/index?comeFrom=5&invite_code=".$user->invite_code;
       }else{
           $path = "";
       }

       //分享信息
       $share_info = array();
       $share_info['imgUrl'] = Yii::$app->request->hostInfo . '/borrow/311/images/activity-logo.png';
       $share_info['link'] = Yii::$app->request->hostInfo . $path;
       //控制假数据在上线第二天的显示
       $startTime = strtotime(date('2018-10-17'),time());
       if($startTime<=time()){
           $data = $this->Show($uid);
       }else{
           $data['list'] = [];
           $data['userInfo'] = [];
           $data['middle'] = [];
       }
        return $this->render('index',[
            'isapp' => $isapp,
            'jsinfo' => $this->getWxParam(),
            'share_info' => $share_info,
            'data'=>$data,
            'invite_code'=>$invite_code,
        ]);
   }

    /**
     * 分享
     */
   public function actionShare()
   {
       $user_id = empty($this->getUser()) ? $this->get('user_id') : $this->getUser()->user_id;
       if(empty($user_id)) {
           $data = ['code'=>1,'msg'=>'您未登录,请登录后参加','data'=>[]];
       }else{
           $data = ['code'=>0,'msg'=>'成功','data'=>[]];
       }
       return json_encode($data);
   }

     /**
      * 用户积分排行榜帮显示
      */
    private function Show($user_id)
    {
        //当前时间零点
        $startTime = strtotime(date('Y-m-d'),time());
        $sort['list'] = [];
        //取redis数据
        $getRedis = $this->getRedis($this->prefix);
        $getRedisData = json_decode($getRedis,true);
        if($getRedis && $getRedisData['time']>$startTime){
             $sort['list'] = $getRedisData['list'];
        }else{
            //model
            $activityFalseData = new ActivityFalseData();
            //真数据
            $trueData = $activityFalseData->getTrueData();
            $trueCount = count($trueData);  //真数据有多少条
            $limit = $this->num - $trueCount; //取几条假数据
            //假数据
            $falseData = $activityFalseData->getFalseData($limit);
            if ($trueData && $falseData) {
                //真实数据最大值
                $trueMax = $trueData[0]['integral'];
                //假数据
                $falseMax = $falseData[0];
                $falseSmall = $falseData[2];
                $falseMiddle = $falseData[1];
                //判断大小
                if ($trueMax >= $falseSmall['integral']) {
                    $saveData = ['mobile' => $falseSmall['mobile'], 'integral' => $trueMax + 10];
                    $activityFalseData->Handlesave($saveData);
                }
                if ($trueMax >= $falseMiddle['integral']) {
                    $saveData = ['mobile' => $falseMiddle['mobile'], 'integral' => $trueMax + 20];
                    $activityFalseData->Handlesave($saveData);
                }
                if ($trueMax >= $falseMax['integral']) {
                    $saveData = ['mobile' => $falseMax['mobile'], 'integral' => $trueMax + 30];
                    $activityFalseData->Handlesave($saveData);
                }
            }
            //假数据
            $falseData = $activityFalseData->getFalseData($limit);
            //组合数组
            $array_merge = array_merge($trueData, $falseData);
            if($falseData || $trueData) {
                //数组排序
                $sort['list'] = $this->multi_array_sort($array_merge, 'integral');
                //当前时间存进去
                $sort['time'] = time();
                $arr = json_encode($sort);
                $this->setRedis($this->prefix, $arr);
            }
        }
        //获取当前用户的积分和名次
        $userModel = new User();
        //获取用户总积分
        if($user_id){
            $userInfo = $userModel->getUserinfoByUserId($user_id);
        }
        //处理数组--手机号处理和当前用户积分和排名处理
        $sort['userInfo'] = [];
        $sort['middle'] = [];
        if($sort['list']) {
            foreach ($sort['list'] as $k => $v) {
                //用户登录 显示积分排名
                if ($user_id && $userInfo) {
                    if ($v['mobile'] == $userInfo->mobile) {
                        $sort['userInfo'] = ['integral' => $v['integral'], 'ranking' => $k + 1];
                    }
                }
                //手机*号处理
                $pattern = "/(\d{3})\d\d(\d{2})/";
                $replacement = "\$1****\$3";
                $sort['list'][$k]['mobile'] = preg_replace($pattern, $replacement, $v['mobile']);
                $sort['list'][$k]['ranking'] = $k + 1;  //名次
                //获取前七名
                if ($k > 2 && $k < 7) {
                    $sort['middle'][$k] = $sort['list'][$k];
                    $sort['middle'][$k]['ranking'] = $k + 1;
                }
            }
        }
        return $sort;
    }

    //多维数组排序
    private function multi_array_sort($arr,$shortKey,$short=SORT_DESC,$shortType=SORT_REGULAR)
    {
        foreach ($arr as $key => $data){
            $name[$key] = $data[$shortKey];
        }
        array_multisort($name,$shortType,$short,$arr);
        return $arr;
    }
}