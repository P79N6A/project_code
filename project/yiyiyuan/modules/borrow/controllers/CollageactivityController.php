<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/15
 * Time: 16:24
 */
namespace app\modules\borrow\controllers;
use app\commonapi\Logger;
use app\models\news\ActivityFalseData;
use app\models\news\Coupon_list;
use app\models\news\Loan_repay;
use app\models\news\ScanTimes;
use app\models\news\User;
use app\models\news\User_loan;
use Yii;

class CollageactivityController extends BorrowController
{
    public $layout = 'collageactivity';
    /**
     * 只有登陆帐号才可以访问
     * 子类直接继承
     */
    public function behaviors() {
        return [];
    }

    /**
     * @return string
     * 首页展示
     */
    public function actionIndex()
    {
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
        $data = $this->showDate();
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')) {
            $isapp = 1;  //app端
        } else {
            $isapp = 2;  //h5端
        }
        return $this->render('index',['uid'=>$uid,'data'=>$data,'is_app'=>$isapp]);
    }

    /**
     * 显示数据处理
     */
    private function showDate()
    {
        $falseDataModel = new ActivityFalseData();
        $falseData = $falseDataModel->getFalseData(8);
        //排序--打乱顺序
        $key = array_keys($falseData);
        shuffle($key);
        foreach ($key as $k=>$v){
            $data[] = $falseData[$v];
            //手机*号处理
            $pattern = "/(\d{3})\d\d(\d{2})/";
            $replacement = "\$1****\$3";
            $data[$k]['mobile'] = preg_replace($pattern, $replacement, $falseData[$v]['mobile']);
        }
        for ($i=0;$i<4;$i++){
            $rand = rand(1,2);
            $data[$i]['rand'] = $rand; //差几人参团
            $h = rand(1,24);
            $m = rand(1,60);
            $s = rand(1,60);
            $a = rand(1,60);
            $time[$i] = $h*$m*$s*$a;
            $arr['list'][] = $data[$i];
        }
        $arr['time'] = json_encode($time);
        return $arr;
    }

    /**
     * 拼团判断
     */
    public function actionJudge()
    {

//        $user_id = empty($this->getUser()) ? $this->get('user_id') : $this->getUser()->user_id;
        //判断用户是否登录
        $user = $this->getUser();
        if(empty($user)) {
            $data = ['code'=>1,'msg'=>'您未登录,请登录后参加','data'=>[]];
            return json_encode($data);
        }
        //查看用户是否参团过
        $scanTimeModel = new ScanTimes();
        $collage = $scanTimeModel->getByMobileType($user->mobile,26);
        if(!empty($collage)){
            $data = ['code'=>2,'msg'=>'您有进行中拼团，不可重复发起','data'=>[]];
            return json_encode($data);
        }
        $userLoanModel = new User_loan();
        $couponListModel = new Coupon_list();
        $loanRepayModel = new Loan_repay();
        $loan = $userLoanModel->getHaveinLoan($user->user_id);
        if(!empty($loan)){
            $data = ['code'=>3,'msg'=>'您已有借款，不可参加此活动','data'=>[]];
            return json_encode($data);
        }
        //拼团成功--添加该用户信息
        $arr = ['mobile'=>$user->mobile,'type'=>26];
        $add = $scanTimeModel->save_scan($arr);
        //添加失败---增加日志
        if(!$add){
            Logger::dayLog('collageactivity','添加失败'.$user->user_id);
        }
        //查询用户是否在21日之前31天内有过还款记录的
        $isLoan = $loanRepayModel->isLoan($user->user_id);
        if($isLoan){
            $result['val'] =5;
            $result['title']='5元还款券';
        }else{
            $result['val'] =30;
            $result['title']='30元还款券';
        }
        $result['start_date'] = date('Y-m-d 00:00:00');
        $result['end_date'] = date('Y-m-d 00:00:00', strtotime('+7 days'));
        $result['mobile'] = $user->mobile;
        $result['type'] = 2;
        $addCoupon = $couponListModel->addCoupon($result);
        if(!$addCoupon){
            Logger::dayLog('collageactivity','添加优惠券失败'.$user->user_id);
        }
        $data = ['code'=>0,'msg'=>'成功','data'=>['val'=>$result['val']]];
        return json_encode($data);
    }
}