<?php

namespace app\modules\newdev\controllers;

/**
 * 6月活动
 */
use app\models\news\Activity_rate;
use app\models\news\App;
use app\models\news\Cg_remit;
use app\models\news\Coupon_apply;
use app\models\news\Coupon_list;
use app\models\dev\Activity_prize;
use app\models\news\User;
use app\commonapi\Logger;
use app\models\news\Activity_share;
use app\models\news\User_bank;
use app\models\news\User_loan;
use app\models\dev\Activity_newyear;
use app\models\dev\Coupon_apply as Coupon_apply_dev;
use app\commonapi\Common;
use Yii;

class ActivitynewController extends NewdevController
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [];
    }

    /**
     * 活动页面;
     * @return string
     */
    public function actionIndex()
    {
        $this->getView()->title = '三重好礼';
//        $user = $this->getUser();//从session中获取user
//
//        if (!empty($user)) {
//            $user_id = $user->user_id;
//            //判断一亿元产品中是否有进行中的借款
//            $haveinLoanId = (new User_loan())->getHaveinLoan($user_id);
//            $loanInfo = null;
//            if (!empty($haveinLoanId)) {
//               $url="/new/loan";
//            }
//        }else{
//            $url = 'new/reg/loginloan';//跳转到注册登录界面
//        }
        //活动逻辑


        echo '4周年列表';
        exit;
        return $this->render('index', [
            'url' => $url
        ]);
    }
    public function  actionOne(){
        echo '一重好礼';
    }

    public function  actionTwo(){
        $user = $this->getUser();
        var_dump($user);
        echo '二重好礼';
    }

    public function  actionThree(){
        $nowtime=strtotime(date('Y-m-d'));
        $endtime=strtotime(date('2018-05-31'));
        if($nowtime>$endtime){
            $is_activity='end';
        }else{
            $is_activity='ing';
        }
        $user = $this->getUser();//从session中获取user
//        var_dump($user);
        $url='';
        $count=0;
        $score=0;
        $is_jk=0;
        $user_id='';
        $fcode=Yii::$app->request->get('fcode');//邀请码
        $user_id=Yii::$app->request->get('user_id');//邀请码
        if (empty($user)) {
            if($user_id){
                $user=User::find()->where(['user_id'=>$user_id])->one();
            }
        }
        if (!empty($user)) {
            $user_id = $user->user_id;
            //判断一亿元产品中是否有进行中的借款
            $haveinLoanId = (new User_loan())->getHaveinLoan($user_id);
            if (empty($haveinLoanId)) {
                $url="/new/loan";
                $is_jk=1;//没有借款
            }
             //判断是否是首次登陆
            $where = [
                'user_id' => $user_id,
                'type' => 1,
            ];
            $islogin = Activity_rate::find()->where($where)->one();
            if(!$islogin){
               $condition = [
                   "user_id" => $user_id,
                   "type" =>1,
               ];
               $result_sql = (new Activity_rate()) ->save_address($condition);
             }

            //计算加速次数
            $count=Activity_rate::find()->andWhere(['user_id'=>$user_id,'status'=>0])->count();//未使用
            $score=Activity_rate::find()->andWhere(['user_id'=>$user_id,'status'=>1])->count();//已使用

        }else{
            if(empty($fcode)){
                $url = '/new/regactivity?atype=3';//跳转到注册登录界面
            }else{
                $url = '/new/regactivity?atype=3&fcode='.$fcode;//跳转到注册登录界面
            }

        }

        //获取微信分享接口所需相关参数
        $jsinfo = $this->getWxParam();
        if( strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')){
            $isapp= 1;  //app端
        }else {
            $isapp= 2;  //h5端
        }
        $shareUrl = yii::$app->request->hostInfo.'/new/fiveyearactivity/index?invite_qcode='.$fcode;//分享到首页带上自己的邀请码
        $imgUrl= yii::$app->request->hostInfo.'/newdev/images/fiveactivity/sharewx.jpg';
        return $this->render('three',[
            'count' => $count,
            'imgUrl' => $imgUrl,
            'score' => $score,
            'user_id' => $user_id,
            'url' => $url,
            'fcode'=>$fcode,
            'shareUrl'=>$shareUrl,
            'is_jk' => $is_jk,
            'jsinfo'=>$jsinfo,
            'isapp'=>$isapp,
            'is_activity' => $is_activity,
        ]);

    }

    //判断是否是首次登陆 type=1


    /*
     * 判断好友首次登陆给加速机会是在好友登陆的时候传一个atype=3 + 要父级的邀请码id
     * */


    /*点立即分享的时候触发添加 加速机会
    * type=2 用户首次转发状态
    *type=4 好友首次转发状态
    */
    public function actionAdd_jihui(){
        $user_id=Yii::$app->request->get('user_id');
        if(empty($user_id)){
            $resultArr = array('code' => '0001', 'message' => 'user_id不能为空');
            echo json_encode($resultArr);
            exit;
        }
        $this->fxcount_add($user_id);
        $jsb=Activity_rate::find()->andWhere(['user_id'=>$user_id,'status'=>0])->count();
        $resultArr = array('code' => '0000','jsb'=>$jsb,'message' => '分享成功');
        echo json_encode($resultArr);
    }

    /*
     * 使用机会
     * */

    public function actionUse_jihui(){
        $user_id=Yii::$app->request->get('user_id');
        if(empty($user_id)){
            $resultArr = array('code' => '0003', 'message' => 'user_id不能为空');
            echo json_encode($resultArr);
            exit;
        }
        $haveinLoanId = (new User_loan())->getHaveinLoan($user_id);
        if (empty($haveinLoanId)) {
            $url="/new/loan";
            $resultArr = array('code' => '0004','url'=>$url, 'message' => '还没有发起借款！');
            echo json_encode($resultArr);
            exit;
        }

        if($haveinLoanId){
            $cgremit = Cg_remit::find()->where(['loan_id'=>$haveinLoanId])->one();
            if(!empty($cgremit) &&($cgremit->remit_status=='WILLREMIT'|| $cgremit->remit_status=='SUCCESS')){
                $resultArr = array('code' => '0005', 'message' => '有未完成的借款');
                echo json_encode($resultArr);
                exit;
            }
        }

        $count=Activity_rate::find()->andWhere(['user_id'=>$user_id,'status'=>0])->count();
       if($count<=0){
           $this->fxcount_add($user_id);
           $resultArr = array('code' => '0002','message' => '还没有加速机会');
           echo json_encode($resultArr);
           exit;
       }
       $update= Activity_rate::updateAll(['status' => 1], ['user_id' => $user_id, 'status' => 0]);
       if($update){
           $score=Activity_rate::find()->where(['user_id'=>$user_id,'status'=>1])->count();
           $resultArr = array('code' => '0000','score' => $score,'message' => '使用成功');
       }else{
           $resultArr = array('code' => '0001','message' => '使用失败');
       }
        echo json_encode($resultArr);
    }

   /*
    * 添加加速包
    * */
    public function fxcount_add($user_id){
        $activity_login=new Activity_rate();
        //判断上下级关系
        $is_gx= User::find()->where(['user_id' => $user_id,'come_from'=>10010])->one();//判断是否当前用户
        if($is_gx){//如果有父id那么给父ID加速机会
            $from_code= $is_gx ->from_code;
            if($from_code){
                $user_info= User::find()->andWhere(['invite_code'=>$from_code])->one();//判断是上级邀请码确实存在
                if($user_info){

                    $user_hysz= Activity_rate::find()->where(['frined_id' => $user_id,'type'=>4])->one();//判断好友是否首次转发
                    if(empty($user_hysz)){
                        $conditionone = [
                            "user_id" => $user_info->user_id,
                            "frined_id" => $user_id,
                            "type" =>4,
                        ];
                        $result_sql = $activity_login ->save_address($conditionone);
                    }
                }

            }

        }

        $is_zf = Activity_rate::find()->where(['user_id' => $user_id,'type'=>2])->one();//判断是否首次转发
        if(!$is_zf){
            $conditiontwo = [
                "user_id" => $user_id,
                "type" =>2,
            ];
            $result_sql =(new Activity_rate()) ->save_address($conditiontwo);
        }
    }

}