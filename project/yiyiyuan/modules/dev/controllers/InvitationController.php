<?php

namespace app\modules\dev\controllers;

use app\commands\SubController;
use app\commonapi\Apihttp;
use app\commonapi\apiInterface\Remit;
use app\commonapi\Http;
use app\commonapi\Logger;
use app\commonapi\ImageHandler;
use app\models\dev\Account;
use app\models\dev\Account_settlement;
use app\models\dev\Card_bin;
use app\models\dev\Friends;
use app\models\dev\Newuser_red_packets_receive;
use app\models\dev\Red_packets_grant;
use app\models\dev\Red_packets_receive;
use app\models\dev\User;
use app\models\dev\User_amount_list;
use app\models\dev\User_auth;
use app\models\dev\User_auth_relation;
use app\models\dev\User_bank;
use app\models\dev\User_remit_list;
use app\models\dev\Userwx;
use app\models\dev\Coupon_apply;
use app\commonapi\Common;
use Yii;

class InvitationController extends SubController {

    public $layout = 'inv';
    public $enableCsrfValidation = false;

    public function beforeaction($action) {
        $url = Yii::$app->request->hostInfo . Yii::$app->request->getUrl();
//        $this->setVal('openid', 'oLbbGs1pxlOBeRw1VN-LV2DgXf1M');
        //do something
        if (!$this->getVal("openid")) {
            if (isset($_GET['code'])) {
                $code = $_GET['code'];
                //获取openid
                $wxinfo = $this->getWebAuthTwo($code);
                if (isset($wxinfo['openid'])) {
                    $openid = $wxinfo['openid'];
                    //判断用户是否保存
                    $isUser = $this->isOpenidReg($openid);
                    if (!$isUser) {
                        $usinfo = $this->getWebAuthThree($wxinfo);
                        //保存新用户
                        if ($this->openidRegSave($usinfo)) {
                            //将openid保存值session，后期切换至memcache
                            $this->setVal('openid', $usinfo["openid"]);
                        }
                    } else {
                        $this->setVal('openid', $wxinfo["openid"]);
                    }
                } else {
                    return $this->redirect($url);
                }
            } else {

                $httpurl = $this->getWebAuthOne($url);
                Logger::errorLog($httpurl, 'toshareurl');
                return $this->redirect($httpurl);
            }
        }
        return true;
    }

    public function actionIndex() {
        $open_id = $this->getVal('openid');
        $mobile = $this->getVal('mobile');
        if (empty($open_id) || empty($mobile)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login?url=/dev/invitation/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        //判断用户是否是一亿元用户
        $userinfo = User::find()->where(['openid' => $open_id])->one();
        if ($userinfo->realname == '' || $userinfo->identity == '') {
            return $this->redirect('/dev/reg/personals?user_id=' . $userinfo->user_id);
        } else if ($userinfo->company == '') {
            return $this->redirect('/dev/reg/company?user_id=' . $userinfo->user_id);
        }
        //3.用户是否拍照
        if ($userinfo->pic_identity == '' || ($userinfo->pic_identity != '' && $userinfo->status == '4')) {
            return $this->redirect('/dev/reg/pic?user_id=' . $userinfo->user_id);
        }

        $sql_auth = "select count(a.id) as count from " . User_auth::tableName() . " as a," . User::tableName() . " as u where u.openid='$open_id' and u.user_id=a.user_id and a.is_up=2";
        $auth_count = Yii::$app->db->createCommand($sql_auth)->queryOne();

        //查询认证过我的用户
        $sql1 = "(select a.amount,a.use_time,a.create_time,u.realname,w.head,w.nickname from " . User_auth::tableName() . " as a left join " . User::tableName() . " as u on a.from_user_id=u.user_id left join " . Userwx::tableName() . " as w on u.openid=w.openid where a.user_id=" . $userinfo['user_id'] . " and a.is_yyy=1 and a.is_up=2 and (a.type=1 or a.type=2))";
        $sql2 = "(select a.amount,a.use_time,a.create_time,u.realname,w.head,w.nickname from " . User_auth::tableName() . " as a left join " . Userwx::tableName() . " as w on a.from_user_id=w.id left join " . User::tableName() . " as u on w.openid=u.openid where a.user_id=" . $userinfo['user_id'] . " and a.is_yyy=2 and a.is_up=2 and a.type=2)";
        $sql = $sql1 . " union all " . $sql2 . " order by create_time desc";
        $auth_list = Yii::$app->db->createCommand($sql)->queryAll();
        $loanuserinfo = Userwx::find()->where(['openid' => $open_id])->asarray()->one();
        $Url = urlencode(Yii::$app->request->hostInfo . "/dev/invitation/cash?userid=" . $userinfo['user_id']);
        // $Url = urlencode(Yii::$app->request->hostInfo . "/dev/invitation/cash?wid=");
        $shareUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . self::$_appid . "&redirect_uri=" . $Url . "&response_type=code&scope=snsapi_userinfo&state=xhh123#wechat_redirect";

        $jsinfo = $this->getWxParam();
        $this->getView()->title = "邀请认证";
        return $this->render('index', [
                    'auth_count' => $auth_count,
                    'userinfo' => $userinfo,
                    'shareUrl' => $shareUrl,
                    'loanuserinfo' => $loanuserinfo,
                    'user_exist' => 'yes',
                    'auth_list' => $auth_list,
                    'jsinfo' => $jsinfo
        ]);
    }

    public function actionCash() {
        $open_id = $this->getVal('openid');

        $wid = intval(Yii::$app->request->get('userid', 0));
        if (empty($open_id)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $user = User::findOne($wid);
        $userwx = '';
        if (!empty($user->openid)) {
            $userwx = Userwx::find()->where(['openid' => $user->openid])->one();
        }else { 
        	$userwx = '';
        }
        $jsinfo = $this->getWxParam();
        $this->getView()->title = "认证答题";
        return $this->render('cash', [
                    'userwx' => $userwx,
        			'user'=>$user,
                    'wid' => $wid,
                    'jsinfo' => $jsinfo
        ]);
    }

    public function actionHlz() {
        Yii::$app->redis->flushall();
    }

    public function actionFirst() {
        //分享者的wx表id
        $wid = intval($_GET['userid']);
        //认证者的openid
        $openid = $this->getVal('openid');
        if (empty($openid)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        //获取分享者的微信信息
        $user = User::findOne($wid);
        if ($user['openid'] == $openid) {
            return $this->redirect('/dev/invitation/index');
        }
        $jsinfo = $this->getWxParam();

        //判断两人是否曾经认证过,首先判断认证者是否是一亿元用户
        $from_user = User::find()->where(['openid' => $openid])->one();
        //获取认证用户的wx表中的id
        $from_userwx = Userwx::find()->where(['openid' => $openid])->one();
        $userAuthModel = new User_auth();
        if (!empty($from_user)) {
            //判断是否认证过
            $auth = $userAuthModel->isAuth($user->user_id, $from_user->user_id);
            if ($auth) {
                return $this->redirect('/dev/invitation/success?userid=' . $wid);
            } else {
                if ($userAuthModel->authFailNum($user->user_id, $from_user->user_id, 1) >= 2) {
                    return $this->redirect('/dev/invitation/fail?userid=' . $wid);
                }
            }
        } else {
            $auth = User_auth::find()->where(['user_id' => $user->user_id, 'is_up' => 2, 'from_user_id' => $from_userwx->id, 'is_yyy' => 2, 'is_up' => 2])->count();
            if ($auth) {
                return $this->redirect('/dev/invitation/success?userid=' . $wid);
            } else {
                if ($userAuthModel->authFailNum($user->user_id, $from_userwx->id, 2) >= 2) {
                    return $this->redirect('/dev/invitation/fail?userid=' . $wid);
                }
            }
        }

        $userwx = Userwx::find()->where(['id' => $wid])->one();
        //随机取2道问题+照片
        $key = $from_userwx->id . '_' . $user['user_id'] . 'auth_first_title'; //认证人微信id  被认证人user_id auth_first_title
        $first_value = Yii::$app->redis->get($key);
        if (empty($first_value)) {
            $array_key = 2;
            Yii::$app->redis->set($wid, time());
            Yii::$app->redis->set($key, $array_key);
        } else {
            $array_key = $first_value;
            Yii::$app->redis->set($wid, time());
        }

        $user_auth = new User_auth();
        $first_question = $user_auth->getSociologyAuthTitle(2);

        $first_array = array();
        $first_answer = '';


        $keyone = $from_userwx->id . '_' . $user['user_id'] . 'auth_first_name';
        $first_array_key = Yii::$app->redis->hgetall($keyone);
        $first_arrays = array();
        if (empty($first_array_key)) {
            $first_array = $user_auth->getUserRealname($keyone, $user['user_id'], $user['realname']);
            $first_answer = $user['realname'];
        } else {
            $first_arrays = $first_array_key;
            foreach ($first_arrays as $k => $v) {
                if ($k % 2 == 1) {
                    $first_array[$k]['name'] = $v;
                    if ($k == 7) {
                        $first_answer = $v;
                        unset($first_array[7]);
                    }
                }
            }
        }




        $this->getView()->title = $first_question;
        return $this->render('first', [
                    'userinfowx' => $user,
                    'first_array' => $first_array,
                    'first_question' => $first_question,
                    'first_answer' => $first_answer,
                    'jsinfo' => $jsinfo,
                    'userwx' => $userwx,
                    'array_key' => $array_key
        ]);
    }

    public function actionSecond() {

        //分享者的user_id
        $wid = intval($_GET['userid']);
        $key = intval($_GET['key']);
        //认证者的openid
        $openid = $this->getVal('openid');
        $user = User::findOne($wid); //被认证人用户信息
        $jsinfo = $this->getWxParam();
        if (empty($openid)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }

        //判断两人是否曾经认证过,首先判断认证者是否是一亿元用户
        $from_user = User::find()->where(['openid' => $openid])->one();
        //获取认证用户的wx表中的id
        $from_userwx = Userwx::find()->where(['openid' => $openid])->one(); //认证人微信信息
        //$userinfo = array();
        if (!empty($from_user)) {
            //判断是否认证过
//            $authinfo = User_auth::find()->where(['user_id' => $user->user_id, 'from_user_id' => $from_user['user_id'], 'type' => 2, 'is_yyy' => 1, 'is_up' => 1])->one();
            $authinfo_count = User_auth::find()->where(['user_id' => $user->user_id, 'from_user_id' => $from_user['user_id'], 'type' => 2, 'is_yyy' => 1, 'is_up' => 1])->count();
            
            if ($authinfo_count >= 2) {
                return $this->redirect('/dev/invitation/fail?userid=' . $wid);
            } else {
                //判断是否投资认证成功，如果认证成功，则直接提示已经认证过
                $authinfo_invest = User_auth::find()->where(['user_id' => $user->user_id, 'from_user_id' => $from_user['user_id'], 'is_up' => 2])->one();
                if (!empty($authinfo_invest)) {
                    return $this->redirect('/dev/invitation/success?userid=' . $wid);
                }
            }
        } else {
            //判断是否认证过
            $authinfo = User_auth::find()->where(['user_id' => $user->user_id, 'from_user_id' => $from_userwx['id'], 'type' => 2, 'is_yyy' => 2, 'is_up' => 2])->one();
            if (!empty($authinfo)) {
                return $this->redirect('/dev/invitation/success?userid=' . $wid);
            } else {
                //判断是否投资认证成功，如果认证成功，则直接提示已经认证过
                $authinfo_invest = User_auth::find()->where(['user_id' => $user->user_id, 'from_user_id' => $from_userwx['id'], 'type' => 2, 'is_yyy' => 2, 'is_up' => 1])->one();
                if (!empty($authinfo_invest)) {
                    return $this->redirect('/dev/invitation/fail?userid=' . $wid);
                }
            }
        }

        $userwx = Userwx::find()->where(['id' => $wid])->one();

        //随机取2道问题+照片
        $keys = $from_userwx->id . '_' . $user['user_id'] . 'auth_second_title';
        $second_value = Yii::$app->redis->get($keys);
        if (empty($second_value)) {
            if (!empty($user['school'])) {
                $array_key = array_rand(Common::authstudentquestion(), 1);
            } else {
                $array_key = array_rand(Common::authsociologyquestion(), 1);
            }
            Yii::$app->redis->set($keys, $array_key);
        } else {
            $array_key = $second_value;
        }

        $second_array = array();
        $second_answer = '';
        $user_auth = new User_auth();

        $keytwo = $from_userwx->id . '_' . $user['user_id'] . 'auth_second_name';
        $second_array_key = Yii::$app->redis->hgetall($keytwo);

        $second_arrays = array();
        if (empty($second_array_key)) {
            if (!empty($user['school'])) {
                $second_question = $user_auth->getStudentAuthTitle($array_key);
                $second_array = $user_auth->getStudentAnswer($array_key, $keytwo, $user['user_id'], $user['realname'], $user['identity'], $user['school'], $user['school_time'], $user['company'], $user['position']);
            } else {
                $second_question = $user_auth->getSociologyAuthTitle($array_key);
                $second_array = $user_auth->getSociologyAnswer($array_key, $keytwo, $user['user_id'], $user['realname'], $user['identity'], $user['school'], $user['school_time'], $user['company'], $user['position']);
            }
            $second_answer_value = Yii::$app->redis->hmget($keytwo, '3');
            $second_answer = $second_answer_value[0];
        } else {
            if (!empty($user['school'])) {
                $second_question = $user_auth->getStudentAuthTitle($array_key);
            } else {
                $second_question = $user_auth->getSociologyAuthTitle($array_key);
            }
            $second_arrays = $second_array_key;
            foreach ($second_arrays as $k => $v) {
                if ($k % 2 == 1) {
                    $second_array[$k]['name'] = $v;
                    if ($k == 7) {
                        $second_answer = $v;
                        unset($second_array[7]);
                    }
                }
            }
        }


        $this->getView()->title = $second_question;
        return $this->render('second', [
                    'userinfowx' => $user,
                    'second_array' => $second_array,
                    'second_question' => $second_question,
                    'second_answer' => $second_answer,
                    'jsinfo' => $jsinfo,
                    'userwx' => $userwx,
                    'array_key' => $array_key
        ]);
    }

    public function actionThird() {
//分享者的user_id
        $wid = intval($_GET['userid']);
        $key = isset($_GET['key']) ? intval($_GET['key']) : '';
        //认证者的openid
        $openid = $this->getVal('openid');
        $user = User::findOne($wid); //被认证人用户信息
        $jsinfo = $this->getWxParam();
        if (empty($openid)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }

        //判断两人是否曾经认证过,首先判断认证者是否是一亿元用户$loaninfo
        $from_user = User::find()->where(['openid' => $openid])->one();
        //获取认证用户的wx表中的id
        $from_userwx = Userwx::find()->where(['openid' => $openid])->one();
        if (!empty($from_user)) {
            //判断是否认证过
//            $authinfo = User_auth::find()->where(['user_id' => $user->user_id, 'from_user_id' => $from_user['user_id'], 'type' => 2, 'is_yyy' => 1, 'is_up' => 1])->one();
            $authinfo_count = User_auth::find()->where(['user_id' => $user->user_id, 'from_user_id' => $from_user['user_id'], 'type' => 2, 'is_yyy' => 1, 'is_up' => 1])->count();
            if ($authinfo_count >= 2) {
                return $this->redirect('/dev/invitation/fail?userid=' . $wid);
            } else {
                //判断是否投资认证成功，如果认证成功，则直接提示已经认证过
                $authinfo_invest = User_auth::find()->where(['user_id' => $user->user_id, 'from_user_id' => $from_user['user_id'], 'is_up' => 2])->one();
                if (!empty($authinfo_invest)) {
                    return $this->redirect('/dev/invitation/success?userid=' . $wid);
                }
            }
        } else {
//判断是否认证过
            $authinfo = User_auth::find()->where(['user_id' => $user->user_id, 'from_user_id' => $from_userwx['id'], 'type' => 2, 'is_yyy' => 2, 'is_up' => 2])->one();
            if (!empty($authinfo)) {
                return $this->redirect('/dev/invitation/success?userid=' . $wid);
            } else {
//判断是否投资认证成功，如果认证成功，则直接提示已经认证过
                $authinfo_invest = User_auth::find()->where(['user_id' => $user->user_id, 'from_user_id' => $from_userwx['id'], 'type' => 2, 'is_yyy' => 2, 'is_up' => 1])->one();
                if (!empty($authinfo_invest)) {
                    return $this->redirect('/dev/invitation/fail?userid=' . $wid);
                }
            }
        }

        $userwx = Userwx::find()->where(['id' => $wid])->one();

        $third_array = array();
        $third_answer = '';

        $user_auth = new User_auth();
        $keythird = $from_userwx->id . '_' . $user['user_id'] . 'auth_third_name';
        $third_array_key = Yii::$app->redis->hgetall($keythird);
        $third_arrays = array();
        if (empty($third_array_key)) {
            $third_array = $user_auth->getUserHeadUrl($keythird, $user['user_id'], $user['pic_identity']);
//            $third_answer = Yii::$app->params['back_url'] . '/' . $loaninfo['pic_identity'];
            $third_answer = ImageHandler::getUrl($user['pic_identity']);
        } else {
            $third_arrays = $third_array_key;
            foreach ($third_arrays as $k => $v) {
                if ($k % 2 == 1) {
                    $third_array[]['url'] = $v;
                    if ($k == 7) {
                        $third_answer = $v;
                        unset($third_array[7]);
                    }
                }
            }
        }

        $jsinfo = $this->getWxParam();
        $this->getView()->title = '相貌';
        return $this->render('third', [
                    'userinfowx' => $user,
                    'third_array' => $third_array,
                    'third_answer' => $third_answer,
                    'userwx' => $userwx,
                    'jsinfo' => $jsinfo
        ]);
    }

    public function actionInputmobile($userid) {
        $this->getView()->title = '领取红包';
        $openid = $this->getVal('openid');
        if (empty($userid)) {
            return $this->redirect('/dev/invitation/fail');
        }
        $jsinfo = $this->getWxParam();
        return $this->render('inputmobile', [
                    'jsinfo' => $jsinfo,
                    'wid' => $userid
        ]);
    }

    public function actionSuccess() {
        $this->getView()->title = '认证成功';
        $openid = $this->getVal('openid');
        $userid = isset($_GET['userid']) ? intval($_GET['userid']) : '';
        if (empty($userid)) {
            return $this->redirect('/dev/invitation/fail');
        }
        $user = User::findOne($userid);
        $from_user = User::find()->where(['openid' => $openid])->one();
        $userAuthModel = new User_auth();
        if (empty($user) || empty($from_user) ||$userAuthModel->isAuth($user->user_id, $from_user->user_id)) {
            $money = '抢光了';
        } else {
            $auth_keys = $openid . "_" . $user->user_id . "_authredis"; //redis命名规则：认证用户openid_被认证人user_id_authredis
            $auth_redis = Yii::$app->redis->get($auth_keys);
            if (empty($auth_redis)) {
                return $this->redirect('/dev/invitation/fail');
            } else {
                $redis_array = unserialize($auth_redis);
                $model = new User_auth();
                $auth_id = $model->addAuth($redis_array);
                if ($auth_id) {
                    Yii::$app->redis->del($auth_keys);
                    $friendModel = new Friends();
                    $friendModel->refreshFriend($from_user->user_id, $user->user_id);
                    //提额100信用点(被认证用户提额100点，认证用户如果是一亿元用户则提额100点)
                    //被认证用户提额
//                    $user_auth = new User_auth();
//                    $user_auth->setAccountUp($user['user_id'], $from_user['user_id'], 6);
                }
            }
            $begin_time = date('Y-m-d 00:00:00');
            $end_time = date('Y-m-d 23:59:59');
            //判断该用户当天已领取几个红包，如果是老用户，当天最多可认证3人
            $red_packets_today_count = Red_packets_receive::find()->where(['user_id' => $from_user->user_id])->andWhere("create_time >= '$begin_time' and create_time <= '$end_time'")->count();
            if ($red_packets_today_count >= 3) {
                $money = '抢光了';
            } else {
                $standardModel = new Coupon_apply();
                $title = "66元借款减息券";
//                $ret_user = $standardModel->sendcouponactivity($from_user->user_id, $title, 1, 31, 66);
                $ret_user = FALSE;
                if ($ret_user) {
                    $money = "66元";
                }else{
                    $money = '抢光了';
                }
            }
        }

        $jsinfo = $this->getWxParam();
        return $this->render('success', [
                    'jsinfo' => $jsinfo,
                    'money' => $money,
                    'user' => $from_user
        ]);
    }

    /**
     * 将红包的收益发放至个人账户
     */
    private function setRedPacketToAccount($user_id, $red_amount) {
        $condition_account = array(
            'total_income' => $red_amount
        );
        $account = new Account();
        $ret_account = $account->setAccountinfo($user_id, $condition_account);
        if ($ret_account) {
            return true;
        } else {
            return false;
        }
    }

    public function actionRedpacket() {
        $this->getView()->title = '提现';
        $is_yyy = isset($_GET['is_yyy']) ? $_GET['is_yyy'] : '';
        $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';
        $grant_id = isset($_GET['grant_id']) ? $_GET['grant_id'] : '';

        if ($is_yyy == 'yes') {
            $red_packets = Red_packets_receive::find()->select(array('amount', 'auth_user_id'))->where(['user_id' => $user_id, 'grant_id' => $grant_id])->one();
            $left_time = 0;
            $left_hour = 0;
            //查询认证的时间
            $user_auth = User_auth::find()->select(array('use_time'))->where(['user_id' => $red_packets->auth_user_id, 'from_user_id' => $user_id, 'type' => 2, 'is_yyy' => 1])->one();
            $user_time = !empty($user_auth->use_time) ? $user_auth->use_time : 1;
        } else {
            $red_packets = Newuser_red_packets_receive::find()->select(array('amount', 'invalid_time', 'auth_user_id'))->where(['wx_id' => $user_id, 'grant_id' => $grant_id])->one();
            $left_time = strtotime($red_packets['invalid_time']) - time();
            $left_hour = $left_time > 0 ? date('0' . ':' . 'i' . ':' . 'd', $left_time) : '00:00:00';
            //查询认证的时间
            $user_auth = User_auth::find()->select(array('use_time'))->where(['user_id' => $red_packets->auth_user_id, 'from_user_id' => $user_id, 'type' => 2, 'is_yyy' => 2])->one();
            $user_time = !empty($user_auth->use_time) ? $user_auth->use_time : 1;
        }

        //好友榜
        if (!empty($grant_id)) {
            $red_packet_list = $this->getRedPacketsList($grant_id);
        } else {
            $red_packet_list = '';
        }

        $jsinfo = $this->getWxParam();
        return $this->render('redpacket', [
                    'jsinfo' => $jsinfo,
                    'amount' => $red_packets->amount,
                    'user_id' => $user_id,
                    'is_yyy' => $is_yyy,
                    'grant_id' => $grant_id,
                    'red_packet_list' => $red_packet_list,
                    'left_time' => isset($red_packets['invalid_time']) ? strtotime($red_packets['invalid_time']) : 0,
                    'left_hour' => $left_hour,
                    'user_time' => $user_time,
                    'hb_left_time' => isset($left_time) ? $left_time : 0
        ]);
    }

    public function actionWithdrawdetail() {
        $grant_id = isset($_GET['grant_id']) ? $_GET['grant_id'] : '';
        $this->getView()->title = '提现资料';

        if (!empty($grant_id)) {
            $red_packet_list = $this->getRedPacketsList($grant_id);
        } else {
            $red_packet_list = '';
        }

        $jsinfo = $this->getWxParam();
        return $this->render('withdraw_detail', [
                    'jsinfo' => $jsinfo,
                    'grant_id' => $grant_id,
                    'red_packet_list' => $red_packet_list,
        ]);
    }

    public function actionWithsave() {
        $bank_card = $_POST['bank_card'];
        $real_name = $_POST['real_name'];
        $identity = $_POST['identity'];
        $mobile = $_POST['mobile'];
        $code = $_POST['code'];
        $grant_id = $_POST['grant_id'];
        $openid = $this->getVal('openid');
// 		$bank_card = '6222 0203 0206 4426232';
// 		$real_name = '高绿蕾';
// 		$identity = '130125198902163027';
// 		$mobile = '18622818463';
// 		$code = '6421';
// 		$grant_id = 14;
// 		$openid = 'oLbbGs5dLQgKN5F_YYhP9qIAHA4M';
        //判断手机号有没有注册
        $user_count = User::find()->where(['mobile' => $mobile])->count();
        if ($user_count > 0) {
            //手机号码已注册
            $resultArr = array('ret' => '1', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }

        //判断身份证号是否存在
        $user_identity_count = User::find()->where(['identity' => $identity])->count();
        if ($user_identity_count > 0) {
            //身份证号已存在
            $resultArr = array('ret' => '11', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }

        $userinfobyopenid = User::find()->where(['openid' => $openid])->one();
        if (isset($userinfobyopenid)) {
            //该微信已绑定其它的手机号
            $resultArr = array('ret' => '2', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }

        $key = "register_reg_" . $mobile;
        $code_byredis = Yii::$app->redis->get($key);
        if ($code != $code_byredis) {
            //验证码错误
            $resultArr = array('ret' => '3', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }

        $postdata = array(
            'name' => $real_name,
            'idcard' => $identity
        );
        $openApi = new Apihttp;
        $validIdentity = $openApi->idValid($postdata);
        if ($validIdentity['res_code'] == '0000') {
            //获取自己的邀请码
            $invite_code = $this->getCode();
            $now_time = date('Y-m-d H:i:s');
            $user = new User();
            $account = new Account();

            $user_array = array(
                'mobile' => $mobile,
                'openid' => $openid,
                'realname' => $real_name,
                'identity' => $identity,
                'identity_valid' => 2,
                'user_type' => 2,
                'invite_code' => $invite_code,
                'come_from' => 2,
                'create_time' => $now_time,
                'last_login_time' => $now_time,
                'last_login_type' => 'weixin'
            );
            $transaction = Yii::$app->db->beginTransaction();
            $user_id = $user->addUser($user_array);
            if ($user_id) {
                $ret_acc = $account->createAccount($user_id);
                if ($ret_acc) {
                    //绑定银行卡
                    $result_bank = $this->addBank($user_id, $bank_card, $mobile);
                    if ($result_bank == 'system_error') {
                        //系统错误
                        $transaction->rollBack();
                        $resultArr = array('ret' => '5', 'url' => '');
                        echo json_encode($resultArr);
                        exit;
                    } else if ($result_bank == 'exist') {
                        //该银行卡已绑定
                        $transaction->rollBack();
                        $resultArr = array('ret' => '6', 'url' => '');
                        echo json_encode($resultArr);
                        exit;
                    } else if ($result_bank == 'card_error') {
                        //银行卡号错误
                        $transaction->rollBack();
                        $resultArr = array('ret' => '7', 'url' => '');
                        echo json_encode($resultArr);
                        exit;
                    } else {
                        //提现
                        $transaction->commit();
                        //查询红包信息
                        $userinfowx = Userwx::find()->select(array('id'))->where(['openid' => $openid])->one();
                        $red_packets = Newuser_red_packets_receive::find()->select(array('id', 'grant_id', 'auth_user_id', 'amount', 'invalid_time', 'status'))->where(['wx_id' => $userinfowx->id, 'status' => 'NORMAL'])->andWhere("invalid_time > '$now_time'")->all();
                        if (!empty($red_packets)) {
                            $red_packets_receive = new Red_packets_receive();
                            $red_packet_amount = 0;
                            //获取所有应该体现的红包总额
                            foreach ($red_packets as $key => $value) {
                                $red_packet_amount += $value['amount'];
                                //修改红包记录表
                                $sql_new_red = "update " . Newuser_red_packets_receive::tableName() . " set current_amount= " . $value['amount'] . ", status='WITHDRAW', last_modify_time='$now_time', version=version+1 where id=" . $value['id'];
                                Yii::$app->db->createCommand($sql_new_red)->execute();

                                //新增加一条红包发放记录
                                //领取红包
                                $condition = array(
                                    'user_id' => $user_id,
                                    'grant_id' => $value['grant_id'],
                                    'auth_user_id' => $value['auth_user_id'],
                                    'amount' => $value['amount'],
                                    'current_amount' => 0
                                );

                                $ret_receive = $red_packets_receive->addRedPacket($condition);
                            }

                            //账户收益增加
                            $condition_account = array(
                                'total_income' => $red_packet_amount
                            );
                            $ret_account = $account->setAccountinfo($user_id, $condition_account);

                            $model = new Account_settlement();
                            $model->settlement_id = date('Ymdhis') . rand(1000, 9999);
                            $model->user_id = $user_id;
                            $model->bank_id = $result_bank;
                            $model->amount = $red_packet_amount;
                            $model->type = 5;
                            $model->status = "INIT";
                            $model->create_time = date('Y-m-d H:i:s');

                            if ($model->save()) {
                                $begin_time = '2017-07-31 00:00:00';
                                $end_time = '2017-07-31 23:59:59';
                                $now_time = date('Y-m-d H:i:s');
                                if ($now_time < $begin_time || $now_time > $end_time) {
                                    //删除redis的key
                                    Yii::$app->redis->del($key);

                                    //提现成功
                                    $resultArr = array('ret' => '0', 'order_id' => $model->settlement_id);
                                    echo json_encode($resultArr);
                                    exit;
                                }

                                $account_settlement_id = $model->attributes['id'];
                                //2.调中信出款接口
                                $userbank = User_bank::find()->where(['user_id' => $user_id, 'id' => $result_bank])->one();
                                $user_mobile = $mobile;
                                $user_name = $real_name;
                                //持卡人姓名
                                $guest_account_name = $real_name;
                                //银行卡号
                                $guest_account = $bank_card;
                                $guest_account_bank = $userbank->bank_name;
                                $guest_account_province = '北京市';
                                $guest_account_city = '北京市';
                                $guest_account_bank_branch = $userbank->bank_name;
                                $account_type = 0;
                                $settle_amount = $red_packet_amount;
                                $order_id = date('Ymdhis') . rand(100000, 999999);
                                $params = [
                                    'req_id' => $order_id,
                                    'remit_type' => 3,
                                    'identityid' => $identity,
                                    'user_mobile' => $user_mobile,
                                    'guest_account_name' => $user_name,
                                    'guest_account_bank' => $guest_account_bank,
                                    'guest_account_province' => '北京',
                                    'guest_account_city' => '北京',
                                    'guest_account_bank_branch' => $guest_account_bank,
                                    'guest_account' => $guest_account,
                                    'settle_amount' => $settle_amount,
                                    'callbackurl' => Yii::$app->params['remit_repay'],
                                ];
                                $apihttp = new Remit();
                                $res = $apihttp->outBlance($params);
                                if ($res['res_code'] == '0000') {
                                    //更新收益提现记录表状态
                                    $loan_id = $account_settlement_id;
                                    $admin_id = -1;
                                    $settle_request_id = $res['res_msg']['client_id'];
                                    $real_amount = $res['res_msg']['settle_amount'];
                                    $settle_fee = 0;
                                    $settle_amount = $res['res_msg']['settle_amount'];
                                    $rsp_code = $res['res_code'];
                                    $remit_status = 'INIT';
                                    $create_time = date('Y-m-d H:i:s', time());
                                    //给数据库的user_remit_list 插入一条数据
                                    $sql = "insert into " . User_remit_list::tableName() . "(order_id,loan_id,admin_id,settle_request_id,real_amount,settle_fee,settle_amount,rsp_code,remit_status,create_time,bank_id,user_id,type) ";
                                    $sql .= "value('" . $order_id . "','" . $loan_id . "',$admin_id,'$settle_request_id','$real_amount ','$settle_fee','$settle_amount','$rsp_code','$remit_status','$create_time','$result_bank','$user_id',6)";
                                    $retinsert = Yii::$app->db->createCommand($sql)->execute();

                                    if ($retinsert >= 0) {
                                        //打款成功，修改收益提现记录状态
                                        $sql = "update " . Account_settlement::tableName() . " set status='SUCCESS' where id=" . $account_settlement_id;
                                        Yii::$app->db->createCommand($sql)->execute();

                                        //删除redis的key
                                        Yii::$app->redis->del($key);

                                        //提现成功
                                        $resultArr = array('ret' => '0', 'order_id' => $model->settlement_id);
                                        echo json_encode($resultArr);
                                        exit;
                                    } else {
                                        //提现失败
                                        $resultArr = array('ret' => '9', 'url' => '');
                                        echo json_encode($resultArr);
                                        exit;
                                    }
                                } else {
                                    //提现失败
                                    $sql = "update " . Account_settlement::tableName() . " set status='FAILED' where id=" . $account_settlement_id;
                                    Yii::$app->db->createCommand($sql)->execute();

                                    $resultArr = array('ret' => '9', 'url' => '');
                                    echo json_encode($resultArr);
                                    exit;
                                }
                            } else {
                                //系统错误
                                $resultArr = array('ret' => '5', 'url' => '');
                                echo json_encode($resultArr);
                                exit;
                            }
                        } else {
                            //红包已领取
                            $resultArr = array('ret' => '10', 'url' => '');
                            echo json_encode($resultArr);
                            exit;
                        }
                    }
                } else {
                    //系统错误
                    $transaction->rollBack();
                    $resultArr = array('ret' => '5', 'url' => '');
                    echo json_encode($resultArr);
                    exit;
                }
            } else {
                //系统错误
                $transaction->rollBack();
                $resultArr = array('ret' => '5', 'url' => '');
                echo json_encode($resultArr);
                exit;
            }
        } else {
            //身份认证失败
            $resultArr = array('ret' => '4', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }
    }

    public function actionWithdrawsuccess() {
        $order_id = isset($_GET['order_id']) ? $_GET['order_id'] : '';
        if (empty($order_id)) {
            return $this->redirect('/dev/invitation/fail');
        }
        //春节期间，禁止提现
        $start_time = '2016-02-05 12:00:00';
        $end_time = '2016-02-15 10:00:00';
        $now_time = date('Y-m-d H:i:s');
        if ($now_time >= $start_time && $now_time <= $end_time) {
            $account_settle = Account_settlement::find()->joinWith('user', true, 'LEFT JOIN')->where(['settlement_id' => $order_id])->one();
        } else {
            $account_settle = Account_settlement::find()->joinWith('bank', true, 'LEFT JOIN')->where(['settlement_id' => $order_id])->one();
        }
        $this->getView()->title = '提现成功';
        $jsinfo = $this->getWxParam();
        return $this->render('withdraw_success', [
                    'jsinfo' => $jsinfo,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'now_time' => $now_time,
                    'account_settle' => $account_settle
        ]);
    }

    public function actionWithdraw() {
        $this->getView()->title = '拼人品的时候到了';
        $jsinfo = $this->getWxParam();
        return $this->render('withdraw', [
                    'jsinfo' => $jsinfo
        ]);
    }

    public function actionFail($wid = 0) {
        $this->getView()->title = '认证失败';
        $openid = $this->getVal('openid');
        if ($wid != 0) {
            $wuserwx = Userwx::find()->where(['id' => $wid])->one();
            $wuser = User::find()->where(['openid' => $wuserwx->openid])->one();
            $user = Userwx::find()->where(['openid' => $openid])->one();
            $userAuthModel = new User_auth();
            $num = $userAuthModel->authFailNum($wuser->user_id, $user->id, 2);
            if ($num < 2) {
                $shareUrl = '';
                $Url = urlencode(Yii::$app->request->hostInfo . "/dev/invitation/cash?wid=" . $wid);
                $shareUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . self::$_appid . "&redirect_uri=" . $Url . "&response_type=code&scope=snsapi_userinfo&state=xhh123#wechat_redirect";
            } else {
                $num = 2;
                $shareUrl = '';
            }
        } else {
            $num = 2;
            $shareUrl = '';
        }
        $jsinfo = $this->getWxParam();
        return $this->render('fail', [
                    'num' => $num,
                    'shareUrl' => $shareUrl,
                    'jsinfo' => $jsinfo
        ]);
    }

    public function actionSuccesssave() {
        $openid = $this->getVal('openid');
//        $wid = $_POST['userid'];
        $wid = Yii::$app->request->post('userid','');
        if(empty($wid) || empty($openid)){
            $resultArr = array('ret' => '1', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }
        $user = User::findOne($wid); //被认证人
        //判断认证用户是否是一亿元用户,判断是否重复提交
        $from_user = User::find()->where(['openid' => $openid])->one();
        $userAuthModel = new User_auth();
        $time = Yii::$app->redis->get($wid);
        $use_time = time() - $time;
        $redis_array = array(
            'user_id' => $user->user_id,
            'type' => 2,
            'use_time' => $use_time,
            'is_up' => 2,
            'amount' => 0,
            'relation' => 3,
            'create_time' => date('Y-m-d H:i:s')
        );
        $auth_keys = $openid . "_" . $wid . "_authredis"; //redis命名规则：认证用户openid_被认证人user_id_authredis
        //获取认证用户的wx表中的id
        $from_userwx = Userwx::find()->where(['openid' => $openid])->one();
        $keyone = $from_userwx->id . '_' . $user['user_id'] . 'auth_first_name';
        $keytwo = $from_userwx->id . '_' . $user['user_id'] . 'auth_second_name';
        $keythird = $from_userwx->id . '_' . $user['user_id'] . 'auth_third_name';
        $key = $from_userwx->id . '_' . $user['user_id'] . 'auth_first_title';
        $keys = $from_userwx->id . '_' . $user['user_id'] . 'auth_second_title';
        Yii::$app->redis->del($key);
        Yii::$app->redis->del($keys);
        Yii::$app->redis->del($keyone);
        Yii::$app->redis->del($keytwo);
        Yii::$app->redis->del($keythird);
        Yii::$app->redis->del($wid);
        if (!empty($from_user)) {
            $redis_array['is_yyy'] = 1;
            $redis_array['from_user_id'] = $from_user->user_id;
            $auth_redis = Yii::$app->redis->get($auth_keys);
            if (empty($auth_redis)) {
                $auth_redis = serialize($redis_array);
                Yii::$app->redis->setex($auth_keys, 1800, $auth_redis);
            }
            if ($userAuthModel->authFailNum($user->user_id, $from_user->user_id, 1) >= 2) {
                $resultArr = array('ret' => '1', 'url' => '');
                echo json_encode($resultArr);
                exit;
            } else {
                $resultArr = array('ret' => '0', 'url' => '');
                echo json_encode($resultArr);
                exit;
            }
        } else {
            $redis_array['is_yyy'] = 2;
            $redis_array['from_user_id'] = $from_userwx->id;
            $auth_redis = Yii::$app->redis->get($auth_keys);
            if (empty($auth_redis)) {
                $auth_redis = serialize($redis_array);
                Yii::$app->redis->setex($auth_keys, 1800, $auth_redis);
            }
            if ($userAuthModel->authFailNum($user->user_id, $from_userwx->id, 2) >= 2) {
                $resultArr = array('ret' => '1', 'url' => '');
                echo json_encode($resultArr);
                exit;
            } else {
                $resultArr = array('ret' => '2', 'url' => '');
                echo json_encode($resultArr);
                exit;
            }
        }
    }

    public function actionFirstsave() {
//认证用户的openid
        $openid = $this->getVal('openid');
        $wid = $_POST['userid'];
        $user = User::findOne($wid);
        $array_key = $_POST['array_key'];

        if (empty($openid)) {
            echo 'fail';
            exit;
        }
        //判断认证用户是否是一亿元用户,判断是否重复提交
        $from_user = User::find()->where(['openid' => $openid])->one();
        //获取认证用户的wx表中的id
        $from_userwx = Userwx::find()->where(['openid' => $openid])->one();

        if (!empty($from_user)) {
            $is_yyy = 1;
            $from_user_id = $from_user['user_id'];
        } else {
            $is_yyy = 2;
//查询wx表中的id
            $userinfowx = Userwx::find()->where(['openid' => $openid])->one();
            $from_user_id = $userinfowx['id'];
        }

//认证类型
        $type = 2;
        $create_time = date('Y-m-d H:i:s');
//添加一条错误认证记录
        $model = new User_auth();
        $model->user_id = $user->user_id;
        $model->from_user_id = $from_user_id;
        $model->type = $type;
        $model->is_yyy = $is_yyy;
        $model->relation = 3;
        $model->amount = 0;
        $model->page_answer = '1-' . $array_key;
        $model->create_time = $create_time;
        $keyone = $from_userwx->id . '_' . $user->user_id . 'auth_first_name';
        $keytwo = $from_userwx->id . '_' . $user->user_id . 'auth_second_name';
        $keythird = $from_userwx->id . '_' . $user->user_id . 'auth_third_name';
        $key = $from_userwx->id . '_' . $user->user_id . 'auth_first_title';
        $keys = $from_userwx->id . '_' . $user->user_id . 'auth_second_title';
        Yii::$app->redis->del($key);
        Yii::$app->redis->del($keys);
        Yii::$app->redis->del($keyone);
        Yii::$app->redis->del($keytwo);
        Yii::$app->redis->del($keythird);
        Yii::$app->redis->del($wid);
        if ($model->save()) {
            echo 'success';
        } else {
            echo 'fail';
        }
        exit;
    }

    public function actionSecondsave() {
//认证用户的openid
        $openid = $this->getVal('openid');
        $wid = $_POST['userid'];
        $user = User::findOne($wid); //被认证人用户信息
        $array_key = $_POST['array_key'];

        if (empty($openid)) {
            echo 'fail';
            exit;
        }

        //判断认证用户是否是一亿元用户,判断是否重复提交
        $from_user = User::find()->where(['openid' => $openid])->one();
        //获取认证用户的wx表中的id
        $from_userwx = Userwx::find()->where(['openid' => $openid])->one();

        if (!empty($from_user)) {
            $is_yyy = 1;
            $from_user_id = $from_user['user_id'];
        } else {
            $is_yyy = 2;
            $from_user_id = $from_userwx['id'];
        }

//认证类型
        $type = 2;
        $create_time = date('Y-m-d H:i:s');
//添加一条错误认证记录
        $model = new User_auth();
        $model->user_id = $user->user_id;
        $model->from_user_id = $from_user_id;
        $model->type = $type;
        $model->is_yyy = $is_yyy;
        $model->relation = 3;
        $model->amount = 0;
        $model->page_answer = '2-' . $array_key;
        $model->create_time = $create_time;
        $keyone = $from_userwx->id . '_' . $user->user_id . 'auth_first_name';
        $keytwo = $from_userwx->id . '_' . $user->user_id . 'auth_second_name';
        $keythird = $from_userwx->id . '_' . $user->user_id . 'auth_third_name';
        $key = $from_userwx->id . '_' . $user->user_id . 'auth_first_title';
        $keys = $from_userwx->id . '_' . $user->user_id . 'auth_second_title';
        Yii::$app->redis->del($key);
        Yii::$app->redis->del($keys);
        Yii::$app->redis->del($keyone);
        Yii::$app->redis->del($keytwo);
        Yii::$app->redis->del($keythird);
        Yii::$app->redis->del($wid);
        if ($model->save()) {
            echo 'success';
        } else {
            echo 'fail';
        }
        exit;
    }

    public function actionThirdsave() {
//认证用户的openid
        $openid = $this->getVal('openid');
        $wid = $_POST['userid'];
        $user = User::findOne($wid);
        if (empty($openid)) {
            $resultArr = array('ret' => '1', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }

//判断认证用户是否是一亿元用户,判断是否重复提交
        $from_user = User::find()->where(['openid' => $openid])->one();
        $from_userwx = Userwx::find()->where(['openid' => $openid])->one();

        if (!empty($from_user)) {
            $is_yyy = 1;
            $from_user_id = $from_user['user_id'];
        } else {
            $is_yyy = 2;
            $from_user_id = $from_userwx['id'];
        }

//认证类型
        $type = 2;
        $create_time = date('Y-m-d H:i:s');
//添加一条错误认证记录
        $model = new User_auth();
        $model->user_id = $user->user_id;
        $model->from_user_id = $from_user_id;
        $model->type = $type;
        $model->is_yyy = $is_yyy;
        $model->relation = 3;
        $model->amount = 0;
        $model->page_answer = '3-0';
        $model->create_time = $create_time;
        $keyone = $from_userwx->id . '_' . $user->user_id . 'auth_first_name';
        $keytwo = $from_userwx->id . '_' . $user->user_id . 'auth_second_name';
        $keythird = $from_userwx->id . '_' . $user->user_id . 'auth_third_name';
        $key = $from_userwx->id . '_' . $user->user_id . 'auth_first_title';
        $keys = $from_userwx->id . '_' . $user->user_id . 'auth_second_title';
        Yii::$app->redis->del($key);
        Yii::$app->redis->del($keys);
        Yii::$app->redis->del($keyone);
        Yii::$app->redis->del($keytwo);
        Yii::$app->redis->del($keythird);
        Yii::$app->redis->del($wid);
        if ($model->save()) {
            echo 'success';
        } else {
            echo 'fail';
        }
        exit;
    }

    public function actionError() {
        return $this->render('error');
    }

    /**
     * 获取红包列表
     * @param unknown $grant_id
     */
    private function getRedPacketsList($grant_id) {
        $sql1 = "(select w.nickname,w.head,u.realname,r.amount,r.create_time from " . Red_packets_receive::tableName() . " as r left join " . User::tableName() . " as u on r.user_id=u.user_id left join " . Userwx::tableName() . " as w on u.openid=w.openid where r.grant_id = $grant_id)";
        $sql2 = "(select w.nickname,w.head,u.realname,r.amount,r.create_time from " . Newuser_red_packets_receive::tableName() . " as r left join " . Userwx::tableName() . " as w on r.wx_id=w.id left join " . User::tableName() . " as u on w.openid=u.openid where r.grant_id = $grant_id)";
        $sql_hongbao = $sql1 . " union all " . $sql2 . " order by create_time desc";
        $red_packet_list = Yii::$app->db->createCommand($sql_hongbao)->queryAll();

        return $red_packet_list;
    }

    /**
     * 绑卡
     */
    private function addBank($user_id, $card, $bank_mobile) {
        $bank_num = User_bank::find()->where(['card' => $card])->count();
        if ($bank_num > 0) {
            return 'exist';
        }
        $bank_nums = User_bank::find()->where(['default_bank' => 1, 'user_id' => $user_id,])->count();
        if ($bank_nums > 0) {
            $default_bank = 0;
        } else {
            $default_bank = 1;
        }
        $cardbinModel = new Card_bin();
        $card_bin = $cardbinModel->getCardBinByCard($card);
        if (empty($card_bin)) {
            return 'card_error';
        }
        $bank = new User_bank();
        $condition = array(
            'user_id' => $user_id,
            'type' => $card_bin['card_type'],
            'bank_abbr' => $card_bin['bank_abbr'],
            'bank_name' => $card_bin['bank_name'],
            'card' => $card,
            'bank_mobile' => $bank_mobile,
            'default_bank' => $default_bank,
            'status' => 1,
        );
        $result = $bank->addUserBank($condition);
        if ($result) {
            return $result;
        } else {
            return 'system_error';
        }
    }

}
