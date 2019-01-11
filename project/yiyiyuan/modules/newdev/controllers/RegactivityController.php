<?php

namespace app\modules\newdev\controllers;

use app\commonapi\Common;
use app\commonapi\Http;
use app\commonapi\Logger;
use app\commonapi\ApiSms;
use app\models\news\Activity_rate;
use app\models\news\Coupon_list;
use app\models\news\Sms;
use app\models\news\User;
use app\models\news\User_extend;
use app\models\news\User_temporary_quota;
use app\models\news\User_wx;
use app\models\news\Friends;
use app\models\news\Common as common2;
use Yii;

class RegactivityController extends NewdevController {

    public $layout = 'main';

    public function behaviors() {
        return [];
    }

    public static $activity_type=array(
        '0'=>['name'=>'4周年列表','url'=>'/new/fiveyearactivity/index'],
        '1'=>['name'=>'一重好礼','url'=>'/new/fiveyearactivity/firstactivity'],
        '2'=>['name'=>'二重好礼','url'=>'/new/fiveyearactivity/secondactivity'],
        '3'=>['name'=>'三重好礼','url'=>'/new/activitynew/three'],
        '4'=>['name'=>'活动','url'=>'/new/lottery']
    );
    /**
     * 登录入口
     * @return \yii\web\Response
     */
    public function actionIndex() {
        $atype = $this->get('atype'); //来源url
        $fcode= $this->get('fcode'); //来源邀请码
        $qcode= $this->get('invite_qcode'); //来源邀请码
        $fuser= User::find()->andWhere(['invite_code'=>$fcode])->one();
        if($fuser){//保证上级邀请码存在数据库的时候保存fcode
            $this->setVal('fcode', $fcode);
        }
        $url = !empty($atype) ? self::$activity_type[$atype]['url']: '/new/activitynew';

        $activity_id = $this->get('activity_id'); //活动id
        if($atype == 4){
            $url = self::$activity_type[$atype]['url']."?activity_id=".$activity_id;
        }

        $user = $this->getUser();
        if (!empty($user)) {
            $code = $this->get('code');
//            $url = !empty($atype) ? self::$activity_type[$atype]['url']: '/new/activitynew';
            //获取到code(用户来源是微信公众号菜单)获取微信用户信息并保存，保存失败与否不影响后续操作
            if ($code && empty($user->openid)) {
                $userInfo = $this->getUserInfo($code);
            }
            $user_new = User::findOne($user->user_id);
            if(!empty($user_new->openid)){
                $this->setVal('openid', $user_new->openid);
            }
            return $this->redirect($url);
        }

        $from_code = $this->get('from_code'); //来源邀请码
        $come_from = $this->get('come_from'); //渠道邀请码
        $this->setVal('from_code', $from_code);
        $this->setVal('come_from', $come_from);
        $this->setVal('fcode', $fcode);
        $this->setVal('qcode', $qcode);
        $this->setVal('url', urldecode($url));

        $this->setVal('atype',$atype);
        return $this->wxRedirect($url);
    }

    /**
     * 登录功能分发
     * @param $url
     * @return \yii\web\Response
     */
    private function wxRedirect($url) {
        Logger::dayLog("wx", $this->get(), $this->post());
        $code = $this->get('code');
        $user = $this->getUser();

        //用户已经登录
        if ($user) {
            //获取到code(用户来源是微信公众号菜单)获取微信用s户信息并保存，保存失败与否不影响后续操作
            if ($code) {
                $userInfo = $this->getUserInfo($code);
            }
            return $this->redirect($url);
        }

        return $this->redirect("/new/regactivity/loginloan");
    }

    /**
     * 登录页面显示
     */
    public function actionLoginloan() {
        $from_code = $this->getVal('from_code');
        $come_from = $this->getVal('come_from');
        $this->layout = 'newinv';
        $this->getView()->title = "登录";
        $jsinfo = $this->getWxParam();
        return $this->render('login', [
            'jsinfo' => $jsinfo,
            'from_code' => $from_code,
            'come_from' => $come_from,
            'csrf' => $this->getCsrf(),
        ]);
    }

    /**
     * 登录AJAX接口方法
     */
    public function actionLoginsave() {
        $mobile = $this->post('mobile');
        $code = $this->post('code');
        $from_code = $this->post('from_code');
        $come_from = $this->post('come_from') == '' ? 2 : $this->post('come_from');
        //判断手机是否注册
        $isReg = User::find()->where(['mobile' => $mobile])->one();

        //用户已经存在走登录流程
        if (!empty($isReg->user_id)) {
            $result = $this->doLogin($isReg, $mobile, $code);
        } else {
            $result = $this->doReg($mobile, $code, $from_code, $come_from);
        }
        echo json_encode($result);
        exit;
    }

    /**
     * 邀请码AJAX接口方法
     */
    public function actionSetfromcode()
    {
        $mobile = $this->post('mobile');
        $from_code = !empty($this->post('from_code')) ? $this->post('from_code') : '';

        //邀请码未填写
        if (empty($from_code)) {
            $resultArr = array('ret' => '1', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }
        //判断手机是否注册
        $isReg = User::find()->where(['mobile' => $mobile])->one();
        if (empty($isReg)) {
            $resultArr = array('ret' => '2', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }

        $userbyfromcode = User::find()->where(['invite_code' => "$from_code"])->one();
        if (isset($userbyfromcode->invite_code) && !empty($userbyfromcode->invite_code)) {
            if ($userbyfromcode->status == 5) {
                $resultArr = array('ret' => '3', 'url' => '');
                echo json_encode($resultArr);
                exit;
            }
//            $friendModel = new Friends();
//            $friendModel->refreshFriend($isReg->user_id, $userbyfromcode->user_id);
        } else {
            //判断用户填写的邀请码是否是渠道邀请码
            $invite_qrcode = Common::invtecodefrombyqrcode($from_code);
            if (!$invite_qrcode) {
                $resultArr = array('ret' => '4', 'url' => '');
                echo json_encode($resultArr);
                exit;
            }
        }
        $url = $this->getVal('url');
        $atype = $this->getVal('atype');
        if ($url) {
            $this->delVal('url');
            $this->delVal('atype');
            //修改注册的邀请码
            $isReg->invite_qrcode = $from_code;
            $isReg->from_code = $atype;
            if (!$isReg->save()) {
                $resultArr = array('ret' => '5', 'url' => '');
                echo json_encode($resultArr);
                exit;
            }


            $resultArr = array('ret' => '0', 'url' => $url);
            echo json_encode($resultArr);
            exit;
        }
    }

    /**
     * 跳过
     */
    public function actionCanclefromcode() {
        $mobile = isset($_POST['mobile']) ? $_POST['mobile'] : '';
        $url = $this->getVal('url');

        if(empty($mobile)){
            $resultArr = array('ret' => '2', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }

        $isReg = User::find()->where(['mobile' => $mobile])->one();
        if (empty($isReg)) {
            $resultArr = array('ret' => '2', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }

        if ($url) {
            $this->delVal('url');
            $url=$url.'?code='.$isReg->invite_code;
            $resultArr = array('ret' => '0', 'url' => $url);
        }else{
            $url='/new/activitynew';
        }

        $resultArr = array('ret' => '0', 'url' =>$url);
        echo json_encode($resultArr);
        exit;
    }

    /**
     * 同意协议页面
     * @return string
     */
    public function actionAgreement() {
        $this->getView()->title = "注册协议";
        return $this->render('agreement');
    }

    /**
     * 发送验证码(登录时)
     */
    public function actionLoginsend() {
        $mobile = $this->post('mobile');
        //正则验证是不是手机号码
        $is_mobile = (new common2())->isMobile($mobile);
        if (!$is_mobile) {
            $resultArr = array('ret' => '5', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }
        $pic_num = strval($this->post('pic_num'));
        $mark = strval($this->post('mark'));
        $sms_count = (new Sms())->getSmsCount($mobile, 2);
        //超过6次限制
        if ($sms_count >= 6) {
            $resultArr = array('ret' => '2', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }
        //已经发送过验证码，需要显示图形验证码
        if ($sms_count > 0 && $mark == 0) {
            $resultArr = array('ret' => '3', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }
        //提交数据中有图形验证码，需要比对
        if ($mark == 1) {
            if (empty($pic_num) || strtolower($pic_num) != $this->getVal('code_char')) {
                $resultArr = array('ret' => '4', 'url' => '');
                echo json_encode($resultArr);
                exit;
            }
        }

        $sendRet = (new ApiSms())->sendReg($mobile, 2);
        $resultArr = array('ret' => '0', 'url' => '');
        echo json_encode($resultArr);
        exit;
    }

    /**
     * 获取图形验证码
     */
    public function actionImgcode() {
        return $this->getImgCode(4, 60, 20);
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
     * 根据openid验证用户是否保存
     * @param $openid
     * @return bool
     */
    private function isOpenidReg($openid) {
        if (empty($openid)) {
            return false;
        }
        $user = User_wx::find()->where(['openid' => $openid])->one();
        if (isset($user->id)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取用户信息
     * @param $ret
     * @return mixed
     */
    public function getWebAuthThree($ret) {
        $url = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $ret["access_token"] . '&openid=' . $ret["openid"] . '&lang=zh_CN';
        $data = Http::getCurl($url);
        Logger::errorLog($data, 'auththree');
        $resultArr = json_decode($data, true);
        return $resultArr;
    }

    /**
     * 根据openid注册用户
     * @param $userinfo
     * @return bool
     */
    public function openidRegSave($userinfo) {
        if (!isset($userinfo['openid'])) {
            return false;
        }
        $openid = $userinfo['openid'];
        $nickname = $userinfo['nickname'];
        $head = isset($userinfo['headimgurl']) ? $userinfo['headimgurl'] : 'http://mp.yaoyuefu.com/images/dev/s2.png';
        $condition = [
            "openid" => $openid,
            "nickname" => addslashes($nickname),
            "head" => $head,
        ];
        $result_sql = (new User_wx())->addUser($condition);
        if (!$result_sql) {
            return false;
        }
        return true;
    }

    /**
     * 注册方法
     * @param $mobile
     * @param $code
     * @param $from_code
     * @param $come_from
     * @return array
     */
    public function doReg($mobile, $code, $from_code, $come_from) {
        $key = "getcode_register_" . $mobile;
        $code_byredis = $this->getRedis($key);
        if ($code_byredis != $code) {//验证码错误
            $resultArr = array('ret' => '3', 'url' => '');
            return $resultArr;
        }

        //用户自己的邀请码用户自己的邀请码
        $invite_code = $this->getCode();
        $create_time = date('Y-m-d H:i:s');

        //保存用户信息
        $from_invite_code = $this->getVal('fcode');//上级邀请码
        $qcode = $this->getVal('qcode');//上级邀请码
        $atype = $this->getVal('atype');//活动id
        $transaction = Yii::$app->db->beginTransaction();
        $condition_user = [
            'mobile' => $mobile,
            'invite_code' => $invite_code,
//            'from_code' => $from_code,
//            'come_from' => $come_from,
            'last_login_time' => $create_time,
            'last_login_type' => 'weixin',
        ];
        if($atype==3){
            $condition_user['from_code']=$from_invite_code;
            $condition_user['come_from']=10010;
        }else{
            $condition_user['from_code']=$qcode;
            if($atype==1){
                $condition_user['come_from']=10008;
            }
            if($atype==2){
                $condition_user['come_from']=10009;
            }

        }
        $userModel = new User();
        $userRet = $userModel->addUser($condition_user);
        if (!$userRet) {//注册失败
            $transaction->rollBack();
            $resultArr = array('ret' => '1', 'url' => '');
            return $resultArr;
        }
        $user_id = $userModel->user_id;
        $userExtendModel = new User_extend();
        $extend = [
            'user_id' => $user_id,
            'reg_ip' => Common::get_client_ip(),
        ];
        $extendRet = $userExtendModel->save_extend($extend);
        if (!$extendRet) {//注册失败
            $transaction->rollBack();
            $resultArr = array('ret' => '1', 'url' => '');
            return $resultArr;
        }
        $transaction->commit();
        //注册提额
        $quotaRet = (new User_temporary_quota())->setTemporary($user_id, 500, 28, '注册提临额', 1);

        //登录
        $user_info = User::find()->select(['openid', 'user_id', 'mobile'])->where(['mobile' => $mobile])->one();
        Yii::$app->newDev->login($user_info, 1);
        if (!empty($user_info->openid)) {
            $this->setVal('openid', $user_info->openid);
        }
        if(!empty($from_invite_code) && !empty($atype)){
            $this->add_jiangli($atype,$user_info->user_id,$from_invite_code);//按照活动发生奖励
        }
         $endTime = '2018-06-01 00:00:00';
         $startTime = '2018-05-15 00:00:00'; //上线需改
        // $startTime = '2018-04-25 00:00:00'; //上线需改
        if($atype == 2  && (time()< strtotime($endTime)) && (time()>strtotime($startTime))){

             //赠送新手券
            $couponModel = new Coupon_list();
           
            $day = ceil((strtotime($endTime) - time()) / 86000); //有效天数
            $coupon_val = 20;
            $res =  $couponModel ->sendCoupon($user_id, "20元新手礼券",2,$day,$coupon_val);

        }
       

        //删除redis里存储的验证码key
        $this->delRedis($key);
        $this->delVal('from_code');
        $this->delVal('come_from');
        $url = $this->getVal('url');

        if ($url) {
            $this->delVal('url');
            $this->delVal('atype');
            if($atype==3){
                $url=$url.'?user_id='.$user_id.'&fcode='.$invite_code;
            }elseif($atype == 4){
                $url = $url;
                Yii::$app->session->set('login_success',true);
            }else{
                $url=$url.'?user_id='.$user_id.'&invite_qcode='.$qcode;
            }
            $resultArr = array('ret' => '0', 'url' => $url);
        } else {
            $resultArr = array('ret' => '2', 'url' => "/new/activitynew");
        }

        return $resultArr;
    }

    /**
     * 登录方法
     * @param $userInfo
     * @param $mobile
     * @param $code
     * @return array
     */
    public function doLogin($userInfo, $mobile, $code) {
        //禁用
        if ($userInfo->status == 6) {
            $resultArr = array('ret' => '4', 'url' => '');
            return $resultArr;
        }
        //验证码是否正确
        $key = "getcode_register_" . $mobile;
        $code_byredis = $this->getRedis($key);
        //验证码错误
        if ($code_byredis != $code) {
            $resultArr = array('ret' => '3', 'url' => '');
            return $resultArr;
        }

        //删除redis里存储的key
        $this->delRedis($key);

        $this->delVal('from_code');
        $this->delVal('come_from');
        //登录
        $user_info = User::find()->select(['openid', 'user_id', 'mobile','invite_code','invite_qrcode'])->where(['mobile' => $mobile])->one();
        Yii::$app->newDev->login($user_info, 1);
        if (!empty($user_info->openid)) {
            $this->setVal('openid', $user_info->openid);
        }
        $userModel = User::findOne($userInfo->user_id);
        $from_invite_code= $this->getVal('fcode');
        $qcode=$this->getVal('qcode');
        $atype= $this->getVal('atype');


        $update_arr = [
            'last_login_time' => date('Y-m-d H:i:s'),
            'last_login_type' => 'weixin',
        ];
        $ret = $userModel->update_user($update_arr);
        $url = $this->getVal('url');
        if(!empty($from_invite_code) && !empty($atype)){
            $this->add_jiangli($atype,$user_info->user_id,$from_invite_code);//按照活动发生奖励
        }

        if ($url) {
            $this->delVal('url');
            $this->delVal('atype');
            if($atype==3){
                $url=$url.'?user_id='.$userInfo->user_id.'&fcode='.$user_info->invite_code;
            }elseif($atype==4){
                $url=$url;
                Yii::$app->session->set('login_success',true);
            }else{
                $url=$url.'?user_id='.$userInfo->user_id.'&invite_qcode='.$qcode;
            }

            $resultArr = array('ret' => '0', 'url' => $url);
        } else {
            $resultArr = array('ret' => '0', 'url' => "/new/activitynew");
        }

        return $resultArr;
    }

    /**
     * 保存用户wx资料信息
     * @param $code
     * @return bool
     */
    public function getUserInfo($code) {
        $user = $this->getUser();
        if (isset($user->openid) && !empty($user->openid)) {
            return true;
        }
        //获取用户token值和openid
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . self::$_appid . "&secret=" . self::$_appSecret . "&code=" . $code . "&grant_type=authorization_code";
        $data = Http::getCurl($url);
        $resultArr = json_decode($data, true);
        if (!isset($resultArr['openid']) || empty($resultArr['openid'])) {
            return false;
        }

        //检验openid是否在User_wx中存在,并且存在于user表中
        if ($this->isOpenidReg($resultArr['openid'])) {
            $user_isset = User::find()->where(['openid' => $resultArr['openid']])->one();
            if (!empty($user_isset)) {
                return true;
            }
            $userModel = User::findOne($user->user_id);
            $update_arr = [
                'openid' => $resultArr['openid'],
            ];
            $ret = $userModel->update_user($update_arr);
            if ($ret) {
                return true;
            }
        }

        //获取用户微信资料信息并保存
        $usinfo = $this->getWebAuthThree($resultArr);
        if ($this->openidRegSave($usinfo)) {
            $userModel = User::findOne($user->user_id);
            $update_arr = [
                'openid' => $usinfo['openid'],
            ];
            $ret = $userModel->update_user($update_arr);
            if ($ret) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function add_jiangli($atype,$user_id,$fcode){//添加奖励
         $newuser_info= User::find()->where(['invite_code'=>$fcode])->one();
        if(!empty($newuser_info)){
            if($atype==3){
                $is_activity_rate=Activity_rate::find()->where(['user_id'=>$newuser_info->user_id,'frined_id'=>$user_id,'type'=>3])->one();//判断是否是
                if(empty($is_activity_rate)){
                    $condition = [
                        "user_id" =>$newuser_info->user_id,
                        "frined_id" => $user_id,
                        "type" =>3,
                    ];
                    $result_sql = (new Activity_rate()) ->save_address($condition);
                }
            }
        }

    }

}
