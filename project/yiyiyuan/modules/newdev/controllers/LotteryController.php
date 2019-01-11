<?php

namespace app\modules\newdev\controllers;

use app\commonapi\Common;
use app\commonapi\ErrorCode;
use app\commonapi\Apihttp;
use app\models\news\ActivityCondition;
use app\models\news\Cg_remit;
use app\models\news\User;
use app\commonapi\Logger;
use app\models\news\Activitynew;
use app\models\news\Prize;
use app\models\news\PrizeList;
use app\models\news\Activity_times;
use app\models\news\Activity_times_list;
use app\models\news\Coupon_list;
use app\models\news\User_loan;
use Yii;
use yii\helpers\Url;
use app\commonapi\Crypt3Des;
use app\common\ApiClientCrypt;

class LotteryController extends NewdevController {

    public function behaviors() {
        return [];
    }

    public function actionIndex() {
        $this->layout = false;
        $activity_id = $this->get('activity_id');
        if (empty($activity_id)) {
            return $this->redirect('/new/loan');
        }

        //加密的手机号登陆
        $encrypted_mobile = $this->get('mobile');
        
        $user = $this->getUser();
        if(empty($user)){
            if($encrypted_mobile){
                $mobile = Crypt3Des::decrypt($encrypted_mobile, (new ApiClientCrypt())->getKey());
                $userModel = new User();
                $user = $userModel->getUserinfoByMobile($mobile);
                if($user){
                    Yii::$app->newDev->login($user, 1);
                    Yii::$app->session->set('login_success',true);
                }
            }else{
                return $this->redirect(['/new/regactivity','atype' => 4,'activity_id' => $activity_id]);
            }
        }

        //活动详情
        $activity = Activitynew::findOne($activity_id);
        if(empty($activity)){
            return $this->redirect('/new/loan');
        }

        $time_now = date('Y-m-d H:i:s');

        //是否是预览请求
        $is_preview = $this->get('is_preview',false);

        if(!$is_preview){
            if($activity->status != 1 || $activity->start_date > $time_now || $activity->end_date < $time_now){
                return $this->redirect('/new/loan');
            }
        }

        //初始化抽奖次数
        $activity_condition = $activity->condition;
        $start_date=$activity->start_date;
        $end_date=$activity->end_date;
        if(empty($activity_condition)){
            return $this->redirect('/new/loan');
        }
        $this->addActivityTimeList($activity_condition->is_accumulation,$user->id,$activity_id,$start_date,$end_date,$activity_condition->rule_condition,$activity_condition->rule_num);

        //获取抽奖类型
        $lottery_type = Activitynew::getTitle($activity->type);
        if(empty($lottery_type)){
            return $this->redirect('/new/loan');
        }
        $this->getView()->title = $activity->title.' - '.$lottery_type['cn'];

        //获取抽奖次数
        $lottery_number = Activity_times::getLotteryNum($activity_id,$user->id);

        //中奖轮播信息
        $broadcast_list = $this->getBroadcastList($activity_id,10);

        //获取奖品信息
        $prizes = Prize::find()->where(['activity_id'=>$activity_id,'status' => 1])->orderBy('id asc')->limit(8)->all();

        //分享信息
        $share_info = array();
        $share_info['title'] = $activity->title;
        $share_info['desc'] = $activity->title.' - '.$lottery_type['cn'];
        $share_info['imgUrl'] = yii::$app->request->hostInfo.$lottery_type['img'];
        $share_info['link'] = yii::$app->request->hostInfo."/new/lottery?activity_id=".$activity->id;

        return $this->render($lottery_type['en'], [
            'user' => $user,
            'activity' => $activity,
            'prizes' => $prizes,
            'lottery_number' => $lottery_number,
            'broadcast_list' => $broadcast_list,
            'img_url' => Yii::$app->params['img_url'],
            'csrf' => $this->getCsrf(),
            'jsinfo' => $this->getWxParam(),
            'share_info' => $share_info
            ]);
    }

    private function getBroadcastList($activity_id,$length){
        $data = array();
        $prizeList = PrizeList::find()->where(['activity_id' => $activity_id])->orderBy('create_time desc')->limit(10)->all();

        foreach($prizeList as $prize){
            $data[] = '鸿运当头，用户'.($prize->mobile ? substr_replace($prize->mobile,'****',3,4) : 'XXX').'抽中'.$prize->title;
        }
        return $data;
    }

    /**
     * @param int user_id
     * @param int activity_id
     * @param int rule_condition: 1还款成功 2借款成功 3首次转发活动 4在活动页面首次登陆 5每次登陆活动页面
     * @param int rule_num
     */
    private function addActivityTimeList($is_accumulation,$user_id,$activity_id,$start_date,$end_date,$rule_condition,$rule_num){
        $Activity_times_one=Activity_times::find()->where(['activity_id'=>$activity_id,'user_id'=>$user_id])->one();
        if(empty($Activity_times_one)){//添加
            $condition_one=array(
                'user_id' => $user_id,
                'activity_id' => $activity_id,
                'total_times' => 0,
                'use_times' => 0,
            );
            $res_times=(new Activity_times())->addActivitytimes($condition_one);
            if(!$res_times){
                return false;
            }
        }
        $activity_times_list = Activity_times_list::find()->where(['user_id' => $user_id,'activity_id' => $activity_id,'rule_condition' => $rule_condition])->one();
        $condition = array(
                        'user_id' => $user_id,
                        'activity_id' => $activity_id,
                        'type' => 1,
                        'rule_condition' => $rule_condition,
                        'num' => $rule_num
                    );


        if($rule_condition == 1){
            $nowtime=date('Y-m-d H:i:s');
            $user_loan=User_loan::find()->andwhere(['user_id'=>$user_id,'status'=>8])->andWhere(['>=', 'last_modify_time',$start_date])->andWhere(['<', 'last_modify_time',$end_date])->all();
            $i=0;
            if(empty($user_loan)){
                return false;
            }
            if($is_accumulation==0){//不累加
                $loan_all= Activity_times_list::find()->where(['user_id'=>$user_id,'activity_id'=>$activity_id,'type'=>1,'rule_condition'=>$rule_condition])->one();
                if(empty($loan_all)){
                    $i=1;
                    $timelistValue[] =[$user_id,$activity_id,1,$rule_condition,$rule_num,$user_loan[0]->loan_id,$nowtime,$nowtime];
                }
            }else{//累加
                foreach($user_loan as $n=>$val){
                    $loan_one= Activity_times_list::find()->where(['loan_id'=>$val->loan_id,'activity_id'=>$activity_id,'type'=>1,'rule_condition'=>$rule_condition])->one();
                    if(empty($loan_one)){
                        $i++;
                        $timelistValue[] =[$user_id,$activity_id,1,$rule_condition,$rule_num,$val->loan_id,$nowtime,$nowtime];
                    }
                }
            }

            $timelistKey=['user_id','activity_id','type','rule_condition','num','loan_id','last_modify_time','create_time'];
            if(!empty($timelistValue)){
                $ress= Yii::$app->db->createCommand()->batchInsert(Activity_times_list::tableName(), $timelistKey, $timelistValue)->execute();
                //修改抽奖次数记录
                $activity_times = Activity_times::find()->where(['user_id' =>$user_id,'activity_id' =>$activity_id])->one();
                if(!empty($activity_times)){
                    //修改抽奖次数
                    if($i>0){
                            $activity_times->total_times += $i*$rule_num;
                            $activity_times->last_modify_time = date('Y-m-d H:i:s');
                    }
                    $activity_times->save();

                }
            }
        }
        if($rule_condition == 2){
            $nowtime=date('Y-m-d H:i:s');
//            $user_loan = (new User_loan())->getUserLoan($user_id,9);//借款
            $user_loan_jg=Cg_remit::find()->andwhere(['user_id'=>$user_id,'remit_status'=>'SUCCESS'])->andWhere(['>=', 'last_modify_time',$start_date])->andWhere(['<', 'last_modify_time',$end_date])->all();
            $i=0;
            if(empty($user_loan_jg)){
                return false;
            }
            if($is_accumulation==0) {//不累加
                    $loan_one= Activity_times_list::find()->where(['user_id'=>$user_id,'activity_id'=>$activity_id,'type'=>1,'rule_condition'=>$rule_condition])->one();
                    if(empty($loan_one)){
                        $i=1;
                        $timelistValue[] =[$user_id,$activity_id,1,$rule_condition,$rule_num,$user_loan_jg[0]->loan_id,$nowtime,$nowtime];
                    }
            }else{
                foreach($user_loan_jg as $n=>$val){
                    $loan_one= Activity_times_list::find()->where(['loan_id'=>$val->loan_id,'activity_id'=>$activity_id,'type'=>1,'rule_condition'=>$rule_condition])->one();
                    if(empty($loan_one)){
                        $i++;
                        $timelistValue[] =[$user_id,$activity_id,1,$rule_condition,$rule_num,$val->loan_id,$nowtime,$nowtime];
                    }
                }
            }


            $timelistKey=['user_id','activity_id','type','rule_condition','num','loan_id','last_modify_time','create_time'];

            if(!empty($timelistValue)){
                $ress= Yii::$app->db->createCommand()->batchInsert(Activity_times_list::tableName(), $timelistKey, $timelistValue)->execute();
                //修改抽奖次数记录
                $activity_times = Activity_times::find()->where(['user_id' =>$user_id,'activity_id' =>$activity_id])->one();
                if(!empty($activity_times)){
                    //修改抽奖次数
                    if($i>0){
                            $activity_times->total_times += $i*$rule_num;
                            $activity_times->last_modify_time = date('Y-m-d H:i:s');
                    }
                    $activity_times->save();
                }
            }

        }

        if($rule_condition == 4){
            if(empty($activity_times_list)){
                (new Activity_times_list())->addActivityTimeList($condition);
            }
        }

        if(Yii::$app->session->get('login_success')){
            if($rule_condition == 5){
                (new Activity_times_list())->addActivityTimeList($condition);
            }
            Yii::$app->session->remove('login_success');
        }
    }

    /**
     * 点击抽奖调用方法
     */
    public function actionDraw(){
        if(!Yii::$app->request->isPost){
            $array = $this->errorreback('99997');
            return json_encode($array);
        }

        $activity_id = $this->post('activity_id');
        $user = $this->getUser();

        //获取抽奖次数
        $activity_times = Activity_times::find()->where(['activity_id' => $activity_id,'user_id' => $user->id])->one();
        if(empty($activity_times) || ($activity_times->total_times - $activity_times->use_times) <= 0){
            $array = $this->errorreback('99998','亲，暂无抽奖次数');
            return json_encode($array);
        }

        //奖品列表
        $prizes = Prize::find()->where(['activity_id'=>$activity_id,'status' => 1])->andWhere(['not', ['probability' => null]]) ->orderBy('id asc')->limit(8)->all();
        if(empty($prizes)){
            $array = $this->errorreback('99999','奖品设置有误');
            return json_encode($array);
        }
        $proArr = array();
        $total = 0;
        foreach($prizes as $key => $value){
            $total += $value->probability;
            $proArr[$key] = $value->probability*100;
        }

        if($total > 1){
            $array = $this->errorreback('10000','奖品概率设置有误');
            return json_encode($array);
        }

        $prize_index = $this->getRand($proArr);

        if(empty($prizes[$prize_index])){
            $array = $this->errorreback('10001','奖品设置有误');
            return json_encode($array);
        }

        //增加获奖记录
        $prize_list = new PrizeList();
        $prize_list_id = $prize_list->addPirzeList($user->id,$prizes[$prize_index]->id);

        if(!$prize_list_id){
            $array = $this->errorreback('10002','抽奖记录添加失败');
            return json_encode($array);
        }

        //增加抽奖记录
        $condition = array(
                'user_id' => $user->id,
                'activity_id' => $activity_id,
                'type' => 2,
                'num' => 1,
                'prize_list_id' => $prize_list_id
            );
        (new Activity_times_list())->addActivityTimeList($condition);


        $array = $this->errorreback('0000');
        $array['rsp_data'] = array(
                                'prize_index' => $prize_index,
                                'angle' => $this->getAngle($prize_index),
                                'title' => $prizes[$prize_index]->title,
                                'broad_info' => '鸿运当头，用户'.($user->mobile ? substr_replace($user->mobile,'****',3,4) : 'XXX').'抽中'.$prizes[$prize_index]->title,
                                );
        return json_encode($array);
    }

    /**
     * 大转盘奖品角度,为了定位奖品在转盘上位置，沿逆时针方向，按奖品id递增依次摆放奖品
     * @param int 0~7 奖品索引
     */
    private function getAngle($prize_index){
        $step = 360/8;
        $start_angle = 1 + $prize_index*$step;
        return mt_rand($start_angle,$start_angle+45);
    }

    /**
     * 抽奖算法
     * @param array $proArr
     * @return mixed
     */
    private function getRand($proArr) {
      $result = '';
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
      unset ($proArr);
      return $result;
    }

    /**
     * 微信分享成功调用方法
     */
    public function actionShare(){
        if(!Yii::$app->request->isPost){
            $array = $this->errorreback('99997');
            return json_encode($array);
        }

        $activity_id = $this->post('activity_id');

        $activity = Activitynew::findOne($activity_id);
        if(empty($activity)){
            $array = $this->errorreback('99998','活动不存在');
            return json_encode($array);
        }

        $user = $this->getUser();
        $activity_condition = $activity->condition;

        if($activity_condition->rule_condition == 3){
            // $this->addActivityTimeList($user->id,$activity_id,3,$activity_condition->rule_num);
            $activity_times_list = Activity_times_list::find()->where(['user_id' => $user->id,'activity_id' => $activity_id,'rule_condition' => 3])->one();
            $condition = array(
                        'user_id' => $user->id,
                        'activity_id' => $activity_id,
                        'type' => 1,
                        'rule_condition' => 3,
                        'num' => $activity_condition->rule_num
                    );

            if(empty($activity_times_list)){
                (new Activity_times_list())->addActivityTimeList($condition);
                $array = $this->errorreback('0000');
                $array['num'] = $activity_condition->rule_num;
            }else{
                $array = $this->errorreback('99995','已经分享过');
            }
            return json_encode($array);
        }else{
            $array = $this->errorreback('99995','分享不增加次数');
            return json_encode($array);
        }
    }


    /**
     * 获取csrf
     * @return string
     */
    private function getCsrf() {
        $csrf = Yii::$app->request->getCsrfToken();
        return $csrf;
    }

    /**
     * 获取msg
     * @param $code
     * @param string $msg
     * @return mixed
     */
    private function errorreback($code, $msg = '') {
        $errorCode = new ErrorCode();
        $array['rsp_code'] = $code;
        $array['rsp_msg'] = !empty($msg) ? $msg : $errorCode->geterrorcode($code);
        return $array;
    }
}
