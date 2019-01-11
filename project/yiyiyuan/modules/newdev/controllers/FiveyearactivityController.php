<?php
namespace app\modules\newdev\controllers;

use app\models\news\App;
use app\models\news\Coupon_apply;
use app\models\news\Coupon_list;
use app\models\news\User;
use app\commonapi\Logger;
use app\models\news\Activity_share;
use app\models\news\User_bank;
use app\models\news\User_loan;
use app\commonapi\Common;
use app\models\news\TemQuota;
use app\models\service\UserloanService;
use app\models\news\Common as Common2;

use Yii;


/**
** 五周年三重活动
**/
class FiveyearactivityController extends NewdevController
{
	public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [];
    }

    /**
    ** 五周年活动入口
    **/
    public function actionIndex(){
    	
        $this->getView()->title = '重酬四周年';
        $user_id = Yii::$app->request->get('user_id'); //判断当前用户的id
        $invite_qcode = Yii::$app->request->get('invite_qcode');  //判断当前用户的上级邀请码

        $err_code = "0000";

        if(empty($user_id)){ //提醒登录
            $err_code = "0001";
            
        }
    	
         //获取微信分享接口所需相关参数
        $jsinfo = $this->getWxParam();

    	return $this->render('index',[
            'user_id' => $user_id,
            'err_code' => $err_code,
            'invite_qcode' =>  $invite_qcode,
            'jsinfo' =>$jsinfo
        ]);

    }

  
  
     /**
     * 活动入口点击时间判断接口;
     * @return string
     */
    public function actionEnter(){

    	$activity_type = yii::$app->request->get('activity_type');

        if($activity_type == '1' ){
           
            $startTime = strtotime('2018-04-28 00:00:00'); //活动开始时间
           // $startTime = strtotime('2018-04-20 00:00:00'); //活动开始时间
            $endTime   = strtotime('2018-05-15 00:00:00'); //活动结束时间
        	
        	
        }
        if($activity_type =='2' ){
           // $startTime = strtotime('2018-04-25 00:00:00'); //活动开始时间
            $startTime = strtotime('2018-05-15 00:00:00'); //活动开始时间
            $endTime   = strtotime('2018-06-01 00:00:00'); //活动结束时间
           
            
        }
        if($activity_type =='3' ){
            $startTime = strtotime('2018-04-28 00:00:00'); //活动开始时间
            //$startTime = strtotime('2018-04-20 00:00:00'); //活动开始时间
            $endTime   = strtotime('2018-06-01 00:00:00'); //活动结束时间
           
           
        }

    	$nowTime   = time();
        $back_code = '0000';
    	if($nowTime < $startTime ){
    		//活动尚未开始
            $back_code = '0001';
          
    	}
    	if($nowTime > $endTime){
    		 //一重活动已经结束请参与二重活动
            $back_code = '0002';
           
    	}

       return  json_encode(['back_code'=>$back_code]);
     
    }

    public function actionFirstactivity(){

        $user_id = Yii::$app->request->get('user_id');
        $invite_qcode = Yii::$app->request->get('invite_qcode');
        $err_code = '0000';
        $coupon_num = 888; //默认页面显示888元
        $coupon_status = 1;  //页面默认显示限时领取

        //$startTime = '2018-04-28 00:00:00'; //活动开始时间  记得改28号
        $startTime = '2018-04-28 00:00:00'; //活动开始时间  记得改28号
        $endTime   = '2018-05-15 00:00:00'; //活动结束时间
        $user = $this->getUser();

        if(empty($user_id)){

           if(empty($user)){
             $err_code = '0001';
            }else{
                 $user_id = $user->user_id;
            }

           
        }

        //判断是否是新用户
        $userModel = new User();

        $find_res = $userModel->find()->where(['user_id'=>$user_id])->one();
        $business_type = ''; 
        $orderInfo = '';

        if(empty($find_res)){
            $err_code = "0002"; //提醒注册
            
        }else{
            $user_id = $find_res->user_id;
        

        

        if(!empty($user_id)){  
            $nextPage = $business_type == 4 ? $this->nextPage($user_id, 4, 14) : $this->nextPage($user_id, 4, 1);
            $orderInfo = $nextPage['orderinfo'];

            $userModel = new User();
            $find_res = $userModel->find()->where(['user_id'=>$user_id])->one();

            //判断是否已领取优惠券，以及优惠券面值多少
            $couponModel = new Coupon_list();

            $counp = Coupon_list::find()->Where(['mobile'=>$find_res['mobile']])->andWhere(['>=','start_date',$startTime])->andWhere(['<=','end_date',$endTime])->andWhere(['like', 'title', '%元感恩券', false])->one();
            
            if(!empty($counp)){
                $coupon_num = $counp['val']; //默认页面显示888元
                $coupon_status = 2;  //页面默认显示限时领取
            }

           
            }
        }
        

        if($user){
            $user_mark = 1;
        }else{
             $user_mark = 0;
        }

      
        $endt = '2018-05-15 00:00:00';
      
        if(time()>strtotime($endt)){
            $end = 1;
        }else{
            $end = 2;
        }

          //获取微信分享接口所需相关参数
        $jsinfo = $this->getWxParam();


        //跳转至一重活动详情页
        return $this->render('firstactivity',[
            'err_code' => $err_code,
            'user_id' => $user_id,
            'invite_qcode' => $invite_qcode,
            'orderinfo' => $orderInfo,
            'coupon_val' =>$coupon_num,
            'coupon_status' => $coupon_status,
            'user' => $user_mark,
            'end' => $end,
            'jsinfo' => $jsinfo
            
        ]);
    }

    /**
     * 获取nextPage
     * @param int $user_id
     * @return string
     */
    private function nextPage($user_id, $from, $type) {
        $UserModel = new User();
        $order = $UserModel->getPerfectOrder($user_id, $from, $type);
        $nextPage = $order['nextPage'];
        $orderJson = (new Common2())->create3Des(json_encode($order, true));
        if ($nextPage != '') {
            $str = substr($nextPage, strrpos($nextPage, '/') + 1);
            if (strpos($str, "?")) {
                $url = $nextPage . '&orderinfo=' . urlencode($orderJson);
                return [
                    'status' => 1,
                    'url' => $url,
                    'orderinfo' => urlencode($orderJson)
                ];
            } else {
                $url = $nextPage . '?orderinfo=' . urlencode($orderJson);
                return [
                    'status' => 1,
                    'url' => $url,
                    'orderinfo' => urlencode($orderJson)
                ];
            }
        } else {
            return [
                'status' => 0,
                'orderinfo' => urlencode($orderJson)
            ];
        }
    }

     /**
     * 一重活动领券接口;
     * @return string
     */   
     public function actionFirstgetcoupon(){

     	$phone = Yii::$app->request->get('phone');
     	$user_id = Yii::$app->request->get('user_id');
       

       
     	$startTime = '2018-04-28 00:00:00'; //活动开始时间  记得改28号
    	$endTime   = '2018-05-15 00:00:00'; //活动结束时间

     	$err_code = "0000";


     	if(empty($user_id)){ //提醒登录
            $user = $this->getUser();//从session中获取user
            if(empty($user)){
               $err_code = "0001";
                return  self::returns($err_code); 
            }
            $user_id = $user->user_id;


     		
     	}



     	if(empty($phone)){
     		$err_code = "0002"; //提醒输入手机号
     		return  self::returns($err_code);
     	}

     	$phone_res = $this->chkPhone($phone);
        if(!$phone_res){
           $err_code = "0003"; //提醒输入正确的手机号
     		return  self::returns($err_code);
        }

     	//判断是否是新用户
     	$userModel = new User();

        $find_res = $userModel->find()->where(['user_id'=>$user_id])->one();
       
        if(empty($find_res)){
        	$err_code = "0004"; //提醒注册
        	return  self::returns($err_code);
        }

        if($find_res['mobile'] != $phone){
            $err_code = "0005"; //输入手机号与注册手机号不一致
            return  self::returns($err_code);
        }
       
        //判断是否已实名认证
        if($find_res['identity_valid'] != 2){
        	$err_code = "0006"; //提醒实名认证

        	return  self::returns($err_code);
        }

       	
       	 //根据当前用户的可贷额度判断对应的感恩券额度
        
        $quotaModel = new UserloanService();
        $quota_num = (int) max($quotaModel->getCreditArrayAmount( $find_res) );

        $coupon_val = 16;
        if($quota_num == 1000){
        	$coupon_val = 16;
        }
        if($quota_num == 1500){
        	$coupon_val = 26;
        }
         if($quota_num == 2000){
        	$coupon_val = 36;
        }
         if($quota_num == 2500){
        	$coupon_val = 46;
        }
         if($quota_num == 3000){
        	$coupon_val = 56;
        }
        
        //发放优惠券
         $couponModel = new Coupon_list();

        $counp = Coupon_list::find()->Where(['mobile'=>$phone])->andWhere(['>=','start_date',$startTime])->andWhere(['<=','end_date',$endTime])->andWhere(['like', 'title', '%元感恩券', false])->all();

        if(empty($counp)){ 
          
        	 $day = ceil((strtotime($endTime) - time()) / 86000) ; //有效天数
             

        	$res =  $couponModel ->sendCoupon($find_res['user_id'], "{$coupon_val}元感恩券",2,$day,$coupon_val);
        	if($res){
        		$err_code = "0000"; //发券成功
	        	 return  json_encode(['back_code'=>$err_code,'coupon_val'=>$coupon_val]);
        	}else{
        		$err_code = "0007"; //发券失败
        		self::returns($err_code);
        	}

        }else{
        	$err_code = "0008"; //已领取过优惠券
        	
        	return  self::returns($err_code);
        }
        	
	
     }

     private static function returns($err_code){
     	return json_encode(['back_code'=>$err_code]);
     	
     }
        
         //手机号码检测
    private function chkPhone($phone)
    {
        if (empty($phone)) {
            return false;
        }
        if (!preg_match('/^0\d{2,3}\-?\d{7,8}$/', $phone)) {
            if (!preg_match('/^1(([34578][0-9])|(47))\d{8}$/', $phone)) {
                return false;
            }
        }
        return true;
    } 

     //二重礼活动（list页点击 ）
    public function actionSecondactivity()
    {
        $user_id = Yii::$app->request->get('user_id');
        $invite_qcode = Yii::$app->request->get('invite_qcode'); //注册时用到邀请人的邀请码

        $err_code = "0000";
        $friend_num = 0;
        $coupon_val = 0;

      
        $users = $this->getUser();
        if(empty($user_id)){
            if(empty($users)){
                $err_code = "0001";
            }else{
                $user_id = $users->user_id;
            }
        }





         $invitation_code = '';
         $activity_type = '2'; //四周年第二个活动
         $activity_types = '10009';
        if(!empty($user_id)){ //判断当前用户邀请的好友
            $user_info = User::find()->where(['user_id'=>$user_id])->one();
            if(empty($user_id)){
                $err_code = "0002"; //用户表中不存在该用户，请注册
            }
            $invitation_code = $user_info['invite_code'];
            if(!empty($invitation_code)){
                //活动关系表中取出邀请码和当前用户邀请码一致并且实名认证已通过的用户
                $friend_num =  User::find()->where(['come_from'=>$activity_types,'from_code'=>$invitation_code,'identity_valid'=>2])->count();
            }

           if($friend_num >5){
                $friend_num = 5;
           }
           $coupon_val = 8 * $friend_num ;
        }

        //获取微信分享接口所需相关参数
        $jsinfo = $this->getWxParam();
      
        $shareUrl = yii::$app->request->hostInfo.'/new/fiveyearactivity/index?invite_qcode='.$invitation_code;//分享到首页带上自己的邀请码

        $imgUrl = yii::$app->request->hostInfo.'/newdev/images/fiveactivity/sharewx.jpg';//分享到首页带上自己的邀请码

        $user = $this->getUser();
        if($user){
            $user_mark = 1;
        }else{
             $user_mark = 0;
        }

        $startt= '2018-05-15 00:00:00';
        //$startt= '2018-04-25 00:00:00';
         $endt = '2018-06-01 00:00:00';
        if(time()< strtotime($startt)){
            $start = 1;
        }else{
            $start = 2;
        }
        if(time()>strtotime($endt)){
            $end = 1;
        }else{
            $end = 2;
        }

       // var_dump([$friend_num,$coupon_val]);die;
        return $this->render('secondactivity',[
                'err_code'=> $err_code,
                'user_id'=> $user_id,
                'friend_num' => $friend_num,
                'coupon_val' => $coupon_val,
                 'invitation_code' => $invitation_code,
                 'invite_qcode' => $invite_qcode, 
                 'jsinfo' => $jsinfo,
                 'shareUrl' => $shareUrl,
                 'activity_type' => $activity_type,
                 'user' => $user_mark,
                 'imgUrl' => $imgUrl,
                 'start' => $start,
                 'end' => $end

        ]);

    }

    //二重活动领取优惠券
    public function actionSecondgetcoupon(){

        $user_id = Yii::$app->request->get('user_id');
       
        $friend_num = 0;
        $coupon_val = 0;
        $err_code = '0000';
        if(empty($user_id)){
            $user = $this->getUser();
            if(empty($user)){
                $err_code = '0001';
                return  self::returns($err_code );
            }
            $user_id = $user->user_id;
           
        }

        if(!empty($user_id)){ //判断当前用户邀请的好友
            $user_info = User::find()->where(['user_id'=>$user_id])->one();
            $invitation_code = $user_info['invite_code'];
            
            $activity_type = '10009'; //四周年第二个活动

            //活动关系表中取出邀请码和当前用户邀请码一致的用户
           $friend_num =  User::find()->where(['come_from'=>$activity_type,'from_code'=>$invitation_code,'identity_valid'=>2])->count();
           if( $friend_num <=0){
                $err_code = '0002';
              return  self::returns('0002'); //尚未邀请好友
            }
        }


        $start_time = date("Y-m-d 00:00:00", time());
        $end_time = date("Y-m-d H:i:s", time());

        
        $startTime = '2018-05-15 00:00:00';
        $endTime = '2018-06-01 00:00:00';
      


        $coupon_val = 8 * $friend_num ;

        //发放优惠券
        $couponModel = new Coupon_list();

        $counp = Coupon_list::find()->Where(['mobile'=>$user_info['mobile']])->andWhere(['>=','start_date',$startTime])->andWhere(['<=','end_date',$endTime])->andWhere(['like', 'title', '%元助力券', false])->count();

        if(empty($counp)){ 
          
             $day = ceil((strtotime($endTime) - time()) / 86000); //有效天数
            $res =  $couponModel ->sendCoupon($user_info['user_id'], "{$coupon_val}元助力券",2,$day,$coupon_val);
            if($res){
                $err_code = '0000';
               return  self::returns($err_code); //发券成功
                
            }else{
                $err_code = '0003';
               return  self::returns($err_code); //发券失败
                
            }

        }else{
             $err_code = '0004';
             return  self::returns($err_code); //已领取过优惠券
            
        }

    }
    




}