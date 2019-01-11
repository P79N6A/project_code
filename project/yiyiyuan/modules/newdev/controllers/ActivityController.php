<?php

namespace app\modules\newdev\controllers;

/**
 * 6月活动
 */
use app\models\news\App;
use app\models\news\Coupon_apply;
use app\models\news\Coupon_list;
use app\models\dev\Activity_prize;
use app\models\news\User;
use app\commonapi\Logger;
use app\models\news\Activity_share;
use app\models\news\User_bank;
use app\models\news\User_extend;
use app\models\news\User_loan;
use app\models\dev\Activity_newyear;
use app\models\dev\Coupon_apply as Coupon_apply_dev;
use app\commonapi\Common;
use Yii;

class ActivityController extends NewdevController
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
        $this->getView()->title = '先花狂欢节';
        $user_id = !empty(Yii::$app->request->get('user_id')) ? Yii::$app->request->get('user_id') : '';
//        $user_id = 14;
        if (!empty($user_id)) {
            $user_count = Activity_share::find()->where(['user_id' => $user_id])->all();
            $count = count($user_count);
            $type = "app";
        }else{
            $count = 0;
            $type = "app";
        }
        return $this->render('index', [
            'type' => $type,
            'count' => $count
        ]);
    }

    /**
     * 提额活动; 
     * @return string
     */
    public function actionProamount(){
        $this->getView()->title = '提额直通车';
        return $this->render('proamount');
    }

    /**
     * 邀请填写手机号页面;
     * @return string
     */
    public function actionShareregister()
    {
        $this->getView()->title = '先花狂欢节';
        $type = "app";
        $username = !empty($_GET['name']) ? $_GET['name'] : '';
        return $this->render('shareregister', [
            'type' => $type,
            'username' => $username
        ]);
    }

    public function actionShareapp()
    {
        $this->getView()->title = '先花狂欢节';
        $type = "app";
        $phone = Yii::$app->request->get('phone');
        $type = $this->get_device_type();
        $mobile = substr_replace($phone, '*****', 3, 5);
        return $this->render('shareapp', [
            'type' => $type,
            'phone' => $mobile
        ]);
    }

    public function actionAdd()
    {
        $date = date("Y-m-d H:i:s");
        $phone = Yii::$app->request->post('phone');
        $from_code = Yii::$app->request->post('from_code');
        $activity = Activity_share::find()->where(['mobile' => $phone])->all();
        $user = User::find()->select('user_id')->where(['invite_code' => $from_code])->asArray()->one();
        $count = Activity_share::find()->where(['user_id' => $user['user_id']])->all();
        $num = count($count);
        if ($num >= 3) {
            echo "1:" . $phone;
        } else {
            if (!empty($activity[0]->mobile)) {
                echo 2;
            } else {
                $val = [
                    'user_id' => $user['user_id'],
                    'mobile' => $phone,
                    'create_time' => $date
                ];
                $customer = Yii::$app->db->createCommand()->insert(Activity_share::tableName(), $val)->execute();
                if ($customer) {
                    echo "1:" . $phone;
                } else {
                    echo 0;
                }
            }
        }
    }

    /**
     * 判断是什么手机型号;
     */

    function get_device_type()
    {
        //全部变成小写字母
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $type = 'other';
        //分别进行判断
        if (strpos($agent, 'iphone') || strpos($agent, 'ipad')) {
            $type = 'ios';
        }

        if (strpos($agent, 'android')) {
            $type = 'android';
        }
        return $type;
    }

    //“借钱不用还”活动首页
    public function actionNewforce()
    {
        $this->layout = 'newforce';
        $this->getView()->title = '借钱不用还';
        $user_id = !empty(Yii::$app->request->get('user_id')) ? Yii::$app->request->get('user_id') : '';
        $ios = 'loanViewController';
        $android = 'com.business.main.MainActivity';
        $position = '0';

        $bankCount = 0;
        if (!empty($user_id)) {
            $where = [
                'user_id' => $user_id,
                'type' => 1,
                'status' => 1
            ];
            $bankCount = User_bank::find()->where($where)->count();
        }
        $phoneArr = $this->listPhone();
        return $this->render('newforce', [
            'ios' => $ios,
            'android' => $android,
            'position' => $position,
            'bankCount' => $bankCount,
            'phoneArr' => $phoneArr
        ]);
    }

    public function actionSendcoupon()
    {
        $userId = $this->post('userId');
        $startActivityData = '2017-08-02 00:00:00';
        $endActivityData = '2017-08-24 00:00:00';
        $this->sendCoupon($userId, $title = '99元借款免息券', $startActivityData, $endActivityData, $val = 99, $days = 7, $type = 3);
        $this->sendCoupon($userId, $title = '66元借款免息券', $startActivityData, $endActivityData, $val = 66, $days = 14, $type = 3);
        $this->sendCoupon($userId, $title = '33元借款免息券', $startActivityData, $endActivityData, $val = 33, $days = 21, $type = 3);
    }

    //“借钱不用还”微信分享页
    public function actionNewforceshare()
    {
        $this->layout = 'newforce';
        $this->getView()->title = '借钱不用还';
        return $this->render('newforceshare');
    }

    //“借钱不用还”app下载页
    public function actionNewforceapp()
    {
        $this->layout = 'newforce';
        $this->getView()->title = '借钱不用还';
        $phone = Yii::$app->request->get('phone');
        $type = $this->get_device_type();
        $mobile = substr_replace($phone, '*****', 3, 5);
        return $this->render('newforceapp', [
            'type' => $type,
            'phone' => $mobile
        ]);
    }

    //“借钱不用还”邀请用户处理
    public function actionNewforceadd()
    {
        $phone = Yii::$app->request->post('phone');
        $isPhone = $this->chkPhone($phone);
        if ($isPhone) {
            echo "1:" . $phone;
        } else {
            echo 0;
        }
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

    //构造手机号码数据
    private function listPhone()
    {
        $array = [
            '188******00', '132******57', '136******11', '186******43', '186******62',
            '152******60', '182******44', '186******61', '133******15', '151******17',
            '182******25', '151******21', '135******46', '186******62', '189******51',
            '186******89', '138******19', '186******64', '186******65', '186******66',
            '186******67', '151******44', '151******43', '189******50', '157******24',
            '187******99', '187******95', '159******14', '131******25', '181******66',
        ];
        shuffle($array);
        return $array;
    }

    /**
     * 发送优惠卷
     * @param $userId       用户id
     * @param $title        优惠卷标题
     * @param $startData    开始时间
     * @param $val          优惠卷金额
     * @param $days         优惠卷有效天数
     * @param $type         注册自动发券：1，输入手机号自动发券：2，分享成功自动发券：3
     * @return bool
     */
    private function sendCoupon($userId, $title, $startActivityData, $endActivityData, $val, $days, $type)
    {
        if (empty($userId) || empty($title) || empty($startActivityData) || empty($endActivityData) || empty($val) || empty($days) || empty($type)) {
            return false;
        }

        if (time() >= strtotime($endActivityData) ||time() < strtotime($startActivityData) ) {
            return false;
        }
        $today = date('Y-m-d 00:00:00');
        $endDate = date('Y-m-d 00:00:00', strtotime("+$days days", strtotime($today)));
        $ret = $this->processCoupon($userId, $title, $endDate, $val, $days, $type);
        if ($ret) {
            return true;
        } else {
            return false;
        }
    }

    private function processCoupon($userId, $title, $endDate, $val, $days, $type, $number = 10000)
    {
        $userinfo = User::find()->where(['user_id' => $userId])->one();
        if (!$userinfo) {
            return false;
        }
        $where = [
            'title' => $title,
            'type' => $type,
            'val' => $val,
            'end_date' => $endDate,
            'apply_depart' => -1,
            'apply_user' => -1,
            'audit_person' => -1,
            'status' => 3
        ];
        $standard = Coupon_apply::find()->where($where)->one();
        if (empty($standard)) {
            (new Coupon_apply())->createCoupon($title, $type, 0, $val, $number, $endDate, $purpose = 0);
        }
        (new Coupon_list())->sendCoupon($userId, $title, $type, $days, $val);
    }

    //活动页按钮返回app
    public function actionPagetest()
    {
        $this->getView()->title = '七夕活动';
        $ios = 'loanViewController';
        $android = 'com.business.main.MainActivity';
        $position = '0';
        return $this->renderPartial('evening',[
            'ios'=>$ios,
            'android'=>$android,
            'position'=>$position
        ]);
    }

    /**
     * 七夕页面
     */

    public function actionEveningindex(){
        $this->getView()->title = '七夕活动';
        $user_id = Yii::$app->request->get('user_id',0);
        $ios = 'loanViewController';
        $android = 'com.business.main.MainActivity';
        $position = '0';
        $type = 'app';
        return $this->render('evening',[
            'user_id' => $user_id,
            'ios' => $ios,
            'android' => $android,
            'position' => $position,
            'type' => $type
            ]);



    }
    public function actionShareevening(){
        $this->getView()->title = '七夕活动';
        return $this->render('shareevening');
    }
    /*
     * 七夕活动发放优惠券;
     */

    public function actionSendeveningcoupon(){
        $this->getView()->title = '七夕活动';
        $user_id = Yii::$app->request->get('user_id');
        if(!isset($user_id) && empty($user_id)){
            return false;
        }
        $last_time = strtotime('2017-09-14 00:00:00');
        $date = date('Y-m-d 00:00:00');
        $date = strtotime($date);
        $day = round(($last_time - $date) / 86000);
        $couponModel = new Coupon_list();
        $res = $couponModel->sendCoupon($user_id, '77元七夕优惠券', 3, $day, 77);
        if($res){
            return 1;
        }else{
            return 0;
        }
    }

    /**
     * 九月活动发放优惠券
     * @return bool|int
     */
    public function actionSeptembersend(){
        $this->getView()->title = '九月活动';
        $user_id = Yii::$app->request->get('user_id');
        if(!isset($user_id) && empty($user_id)){
            return false;
        }
        $last_time = strtotime('2017-10-09 00:00:00');
        $date = date('Y-m-d 00:00:00');
        $date = strtotime($date);
        $day = round(($last_time - $date) / 86000);
        $user = User::findOne($user_id);
        $send = User::find()->andWhere(['from_code'=>$user['invite_code']])->andWhere(['between','create_time',"2017-09-19 00:00:00","2017-10-09 00:00:00"])->count('user_id');
        $recall = Activity_prize::find()->andWhere(['type'=>5])->andWhere(['user_id'=>$user_id])->all();
        $counp = Coupon_list::find()->andWhere(['title'=>'88元免息券'])->andWhere(['mobile'=>$user['mobile']])->all();
        if(($recall && empty($counp)) || ($send >= 2 && empty($counp))){
            $couponModel = new Coupon_list();
            $res = $couponModel->sendCoupon($user_id, '88元免息券', 3, $day, 88);
            if($res){
                return 1;
            }else{
                return 3;
            }
        }else{
            return 3;
        }

    }

    /**
     * 九月活动展示页面;
     * @return string
     */
    public function actionSeptember(){
        $this->getView()->title = '九月活动';
        $user_id = Yii::$app->request->get('user_id');
        if(!isset($user_id) && empty($user_id)){
            return false;
        }
        $user = User::findOne($user_id);
        $count = User::find()->andWhere(['from_code'=>$user['invite_code']])->andWhere(['between','create_time',"2017-09-19 00:00:00","2017-10-09 00:00:00"])->count('user_id');
        $counp = Coupon_list::find()->andWhere(['title'=>'88元免息券'])->andWhere(['mobile'=>$user['mobile']])->all();
        if(empty($counp)){
            $counp = 0;
        }else{
            $counp =1;
        }
        $ios = 'loanViewController';
        $android = 'com.business.main.MainActivity';
        $position = '0';
        $type = 'app';
        return $this->render('september',[
            'count' => $count,
            'user_id' => $user_id,
            'counp' => $counp,
            'ios' => $ios,
            'android' => $android,
            'position' => $position,
            'type' => $type
        ]);
    }
    /**
     * 分享页面
     */
    public function actionSeptemberinfo(){
        $this->getView()->title = '九月活动';
        $from_code = Yii::$app->request->get('from_code');
        return $this->render('septemberinfo',[
            'from_code'=>$from_code
        ]);
    }


    /**
     * 十月活动发放优惠券;
     * @return bool|int
     */
    public function actionOctober(){
        $this->getView()->title = '十月活动';
        $user_id = Yii::$app->request->get('user_id');
        if(!isset($user_id) && empty($user_id)){
            return false;
        }
        $last_time = strtotime('2017-10-31 00:00:00');
        $date = date('Y-m-d 00:00:00');
        $date = strtotime($date);
        $day = round(($last_time - $date) / 86000);
        $user = User::findOne($user_id);
        $where = [
            'user_id' => $user_id,
            'type' => 1,
            'status' => 1,
        ];
        $user_bank = User_bank::find()->andWhere($where)->andWhere(['between','create_time',"2017-10-10 00:00:00","2017-10-31 00:00:00"])->one();
        $counp = Coupon_list::find()->andWhere(['title'=>'99元免息券'])->andWhere(['mobile'=>$user['mobile']])->all();
        if($user_bank && empty($counp)){
            $couponModel = new Coupon_list();
            $res = $couponModel->sendCoupon($user_id, '99元免息券', 3, $day, 99);
            if($res){
                return 1;
            }else{
                return 3;
            }
        }else{
            return 3;
        }
    }

    /**
     * 十月活动展示页面
     * @return bool|string
     */
    public function actionOctoberindex(){
        $this->getView()->title = '十月活动';
        $user_id = Yii::$app->request->get('user_id');
        if(!isset($user_id) && empty($user_id)){
            return false;
        }
        $where = [
            'user_id' => $user_id,
            'type' => 1,
            'status' => 1,
        ];
        $user_bank = User_bank::find()->andWhere($where)->andWhere(['between','create_time',"2017-10-10 00:00:00","2017-10-31 00:00:00"])->one();
        if(empty($user_bank)){
            $bank = 0;
        }else{
            $bank = 1;
        }
        $ios = 'loanViewController';
        $android = 'com.business.main.MainActivity';
        $position = '0';
        $type = 'app';
        return $this->render("octoberindex",[
            'bank' => $bank,
            'ios' => $ios,
            'android' => $android,
            'position' => $position,
            'type' => $type,
            'user_id'=>$user_id
        ]);
    }

    /**
     * 十月活动分享页面
     * @return bool|string
     */
    public function actionOctoberinfo(){
        $this->getView()->title = "十月活动";
        return $this->render("octoberinfo");
    }



    public function actionHolidays(){
        $this->getView()->title = '放款不休息';
        return $this->render('holidays');
    }
    
    /**
     * 双十二app展示页面
     * @return type
     */
    public function actionTwelve(){
        $this->getView()->title = '双十二活动';
        $type = 'app';
        return $this->render('twelve',['type' => $type]);
    }
    
    /**
     *双十二下载引导页面
     * @return type
     */
    public function actionTwelvedetail(){
        $this->getView()->title = '双十二活动';
        return $this->render('twelvedetail');
    }
    
    public function actionYxjp(){
        $this->getView()->title = '严选精品';
        return $this->render('yxjp');
    }
    /**
     * 注册协议
     * @return string
     */
    public function actionLogin() {
        $this->getView()->title = "注册协议";
        $this->layout = 'agreement';
        return $this->render('login');
    }
    
    /**
     * 愚人节活动--首页
     * @return string
     */
    public function actionFoolsday() {
        $this->getView()->title = "首借礼“愚”你有缘";
        $type = "";
        $type = $this->get('type');
        $this->layout = 'agreement';
        $server_name = $_SERVER['SERVER_NAME'];
        return $this->render('foolsday',[
                'server_name' => $server_name,
                'type' => $type,
                ]);
    }
    
        /**
     * 愚人节活动--领取页
     * @return string
     */
    public function actionFoolsdayget() {
        $this->getView()->title = "首借礼“愚”你有缘";
        $this->layout = 'agreement';
        $type = $this->get('type');
        $ios = 'loanViewController';
        $android = 'com.business.main.MainActivity';
        $position = '0';
        $sql = "select * from yi_app_version ORDER BY id desc";
    	$model = Yii::$app->db->createCommand($sql)->queryOne();
        return $this->render('foolsdayget',[
                    'type' => $type,
                    'ios' => $ios,
                    'android' => $android,
                    'position' => $position,
                    'downurl' => $model['download_url'],
                ]);
    }
    
    /*
     * 判断是否是首借
     */
    private function firstLoan($user_id = ""){
        if(empty($user_id)){
            return FALSE;
        }
        $loan_info = User_loan::find()->where(['user_id' => $user_id])->all();
        $status_string = Common::ArrayToString($loan_info, 'status');
        $status_arr = explode(',', $status_string);
        $loan_status = array(8,9,11,12,13);
        $res = array_intersect($loan_status, $status_arr);
        if(!empty($res)){
            return 0;
            exit();
        }
        return 1;
    }

    public function actionDofoolsdayinfo(){
        $phone = Yii::$app->request->post('phone');
        $phone_res = $this->chkPhone($phone);
        if(!$phone_res){
            echo $this->showMessage(1, '*请填写正确的手机号码', 'json');
            exit;
        }
        $data_info = array('mobile'=>$phone);
        $userModel = new User();
        $find_res = $userModel->find()->where($data_info)->one();
        $couponModel = new Coupon_list();
        $coupon = Coupon_list::find()->where(['mobile' => $phone,'status' => 1,'val' => 38])->one();
        if(empty($find_res) && empty($coupon)){
            $transaction = Yii::$app->db->beginTransaction();
            $user_res = $userModel->addUser($data_info);
            if (!$user_res) {
                $transaction->rollBack();
                echo $this->showMessage(1, '注册失败', 'json');
                exit;
            }
            $user_id = $userModel->user_id;
            $userExtendModel = new User_extend();
            $extend = [
                'user_id' => $user_id,
                'reg_ip' => Common::get_client_ip(),
            ];
            $user_extend_res = $userExtendModel->save_extend($extend);
            if (!$user_extend_res) {
                $transaction->rollBack();
                echo $this->showMessage(1, '注册失败', 'json');
                exit;
            }
            $transaction->commit();
            $res = $couponModel->sendCoupon($userModel->user_id, '38元首借礼券', 2, 17, 38);
            echo $this->showMessage(0, '成功', 'json');
            exit;
        }
        if($coupon){
            echo $this->showMessage(3, '*请勿重复领取', 'json');
            exit;
        }
        $first_res = $this->firstLoan($find_res['user_id']);
        if($first_res == 0){
            echo $this->showMessage(2, '不是首贷', 'json');
            exit;
        }
        $res = $couponModel->sendCoupon($find_res->user_id, '38元首借礼券', 2, 17, 38);
        echo $this->showMessage(0, '成功', 'json');
        exit;
            
    }
    
    /*
     * 四月降息活动
     */
    public function actionAprildrop(){
        $this->getView()->title = "息费直降，放款率提升";
        $this->layout = 'agreement';
        return $this->render('aprildrop');
    }
    
    /*
     * 四月转盘抽奖活动
     */
    public function actionAprilluckydraw(){
        $this->getView()->title = "息费直降，放款率提升";
        $err_code = "0000";
        $luckydraw_num = 0;
        $friend_loan_num = 0;
        $coupon_num = 0;
        $user_id = Yii::$app->request->get('user_id');
        if(empty($user_id)){
            $err_code = "0001";
        }
        $this->layout = 'agreement';
        if(!empty($user_id)){
            $luckydraw_info = Activity_newyear::find()->where(['type' => 5,'user_id' => $user_id])->one();
            $luckydraw_num = empty($luckydraw_info['invite_num'])?0:$luckydraw_info['invite_num'];
            $friend_loan_num = empty($luckydraw_info['friend_loan_num'])?0:$luckydraw_info['friend_loan_num'];
            $coupon_num = empty($luckydraw_info['coupon_num'])?0:$luckydraw_info['coupon_num'];
        }
        $start_time = date("Y-m-d 00:00:00", time());
        $end_time = date("Y-m-d H:i:s", time());
        if($start_time <"2018-04-12 00:00:00" || $end_time> "2018-04-27 00:00:00"){
            $luckydraw_num = 0;
        }
//        print_r($luckydraw_num);die;
        return $this->render('aprilluckydraw',[
                    'luckydraw_num' => $luckydraw_num,
                    'err_code' => $err_code,
                    'user_id' => $user_id,
                    'friend_loan_num' => $friend_loan_num,
                    'coupon_num' => $coupon_num,
                ]);
    }

    public function actionSendcouponday(){
        $user_id = Yii::$app->request->post('user_id');
        $val = Yii::$app->request->post('val',5);
        $days = Yii::$app->request->post('days',60);
        if(empty($user_id)){
            return false;
        }
        $couponModel = new Coupon_apply_dev();
        $res = $couponModel->sendcoupon($user_id, $val.'元优惠券', 1, $days, $val, 50000);//名称
        $model = Activity_newyear::find()->where(['user_id' => $user_id,'type' => 5])->one();
        $res = $model->updateNum("coupon_num", 5);
    }
    
    /*
     * 抽奖减一次
     */
    public function actionSubtractionactivenum(){
        $user_id = Yii::$app->request->post('user_id');
        $model = Activity_newyear::find()->where(['user_id' => $user_id,'type' => 5])->one();
        $res = $model->updateNum("invite_num", 5,'-1');
//         Logger::dayLog( '投保支付失败', $res);
    }
    
    /*
     * 再抽一次
     */
    public function actionAgainnum(){
        $user_id = Yii::$app->request->post('user_id');
        $model = Activity_newyear::find()->where(['user_id' => $user_id,'type' => 5])->one();
        $res = $model->updateNum("friend_loan_num", 5);
    }

    /*
     * 如何关注我们
     */
    public function actionAttention(){
        $this->layout = 'agreement';
        $this->getView()->title = "如何关注我们";
        return $this->render('attention',[
                ]);
    }
    
    
}