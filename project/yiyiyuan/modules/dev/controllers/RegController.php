<?php

namespace app\modules\dev\controllers;

use app\commands\SubController;
use app\common\ApiClientCrypt;
use app\commonapi\Apihttp;
use app\commonapi\Common;
use app\commonapi\Http;
use app\commonapi\ImageHandler;
use app\commonapi\Keywords;
use app\commonapi\Logger;
use app\models\dev\Account;
use app\models\dev\ApiSms;
use app\models\dev\Areas;
use app\models\dev\Attention;
use app\models\dev\Black_list;
use app\models\dev\Contacts_flows;
use app\models\dev\Favorite_contacts;
use app\models\dev\Fraudmetrix_return_info;
use app\models\dev\Friends;
use app\models\dev\Newuser_red_packets_receive;
use app\models\dev\Pictype;
use app\models\dev\Red_packets_receive;
use app\models\dev\Register_event;
use app\models\dev\School;
use app\models\dev\Score;
use app\models\dev\Sms;
use app\models\dev\User;
use app\models\dev\User_amount_list;
use app\models\dev\User_extend;
use app\models\dev\User_history_info;
use app\models\dev\User_password;
use app\models\dev\User_temporary_quota;
use app\models\dev\Userwx;
use app\models\dev\Userxhh;
use app\models\yyy\XhhApi;
use Yii;
use yii\filters\AccessControl;

class RegController extends SubController {

    public $layout = 'main';
    public $enableCsrfValidation = false;

    public function actionLogin() {
        return $this->redirect('/borrow/reg/login');
//        Yii::$app->user->logout();
        $openid = $this->getVal('openid');
        $from_code = Yii::$app->request->get('from_code');
        $this->layout = 'inv';
        //获取code
        if (empty($openid)) {
            if (isset($_GET['code'])) {
                $code = $_GET['code'];
                $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . self::$_appid . "&secret=" . self::$_appSecret . "&code=" . $code . "&grant_type=authorization_code";
                $data = Http::getCurl($url);
                $resultArr = json_decode($data, true); //转为数组
                if (isset($resultArr['openid']) && !empty($resultArr['openid'])) {
                    $isUser = $this->isOpenidReg($resultArr['openid']);
                    if (!$isUser) {
                        $usinfo = $this->getWebAuthThree($resultArr);
                        //保存新用户
                        if ($this->openidRegSave($usinfo)) {
                            $this->setVal('openid', $usinfo['openid']);
                            //@TODO 2.6.0
                            Yii::$app->userWap->login($usinfo, 1);
                        } else {
                            //保存微信用户失败，去出错页面
                            return $this->redirect('/dev/site/error');
                        }
                    } else {
                        $this->setVal('openid', $resultArr['openid']);
                    }
                } else {
                    //没有取到token值和openid，去错误页面
                    return $this->redirect('/dev/site/error');
                }
            }
            $openid = $this->getVal('openid');
        }
        //是否关注先花一亿元
        if (isset($_GET['atten']) && $_GET['atten'] == '1') {
            $isAtten = $this->isAtten($openid);
            if (!$isAtten) {
                return $this->redirect('http://mp.weixin.qq.com/s?__biz=MzA4OTM2NTU5NQ==&mid=203536992&idx=1&sn=682dd78456a5d0cd8e843b0a14243389#rd');
            }
        }
        $type = isset($_GET['type']) ? $_GET['type'] : '';
        if (isset($_GET['url'])) {
            $redirUrl = urldecode($_GET['url']);
            if ($redirUrl) {
                $this->setVal('nextPageUrl_login', $redirUrl);
            }
        }
        $userinfo = User::find()->select(array('mobile'))->where(['openid' => $openid])->one();
        $this->getView()->title = "登录";
        $jsinfo = $this->getWxParam();
        return $this->render('login', ['type' => $type, 'userinfo' => $userinfo, 'jsinfo' => $jsinfo, 'from_code' => $from_code]);
    }

    public function actionLoginsave() {
        $openid = $this->getVal('openid');
        $mobile = $_POST['mobile'];
        $code = $_POST['code'];
        //判断手机是否注册
        $isReg = User::find()->where(['mobile' => $mobile])->one();
        if (!empty($isReg->user_id)) {
            //走登录流程
            //openid不存在
            if (empty($openid)) {
                $resultArr = array('ret' => '5', 'url' => '');
                echo json_encode($resultArr);
                exit;
            }
            //获取的openid和注册的手机号不一致
            if (($isReg->openid != $openid) && !empty($isReg->openid)) {
                $resultArr = array('ret' => '4', 'url' => '');
                echo json_encode($resultArr);
                exit;
            }
            if (empty($isReg->openid)) {
                //判断有没有存在的openid
                $userinfobyopenid = User::find()->where(['openid' => $openid])->one();
                if (isset($userinfobyopenid)) {
                    $resultArr = array('ret' => '4', 'url' => '');
                    echo json_encode($resultArr);
                    exit;
                }
            }
            //禁用
            if ($isReg->status == 6) {
                $resultArr = array('ret' => '4', 'url' => '');
                echo json_encode($resultArr);
                exit;
            }
            //验证码是否正确
            $key = "getcode_register_" . $mobile;
            $code_byredis = Yii::$app->redis->get($key);
            //验证码错误
            if ($code_byredis != $code) {
                $resultArr = array('ret' => '3', 'url' => '');
                echo json_encode($resultArr);
                exit;
            }
            $nextPage = $this->getVal('nextPageUrl_login');
            if (empty($nextPage)) {
                $nextPage = Yii::$app->request->hostInfo . "/dev/loan";
            }
            //判断用户的openid是否存在，如果不存在，则同步更新对应的openid
            if (empty($isReg['openid'])) {
                $sql = "update " . User::tableName() . " set openid='$openid' where mobile='$mobile'";
                $ret = Yii::$app->db->createCommand($sql)->execute();
            }

            //删除redis里存储的key
            Yii::$app->redis->del($key);

            //登录用户的手机号保存在memcache里
            $this->setVal('mobile', $mobile);
            //@TODO 2.6.0
            Yii::$app->userWap->login($isReg, 1);
//            $this->setVal('openid', $isReg['openid']);
            $resultArr = array('ret' => '0', 'url' => $nextPage);
            if ($resultArr['ret'] == 0) {
                $dat = date('Y-m-d H:i:s', time());
                $sql = "update " . User::tableName() . " set last_login_time='$dat' , last_login_type='weixin' where mobile='$mobile'";
                $ret = Yii::$app->db->createCommand($sql)->execute();
            }
            echo json_encode($resultArr);
            exit;
        } else {
            $from_code = Yii::$app->request->post('from_code');
            if (empty($openid)) {
                $resultArr = array('ret' => '5', 'url' => '');
                echo json_encode($resultArr);
                exit;
            }

            //判断该openid是否注册，该微信已绑定其他的手机号
            $userinfobyopenid = User::find()->where(['openid' => $openid])->one();
            if (isset($userinfobyopenid)) {
                $resultArr = array('ret' => '6', 'url' => '');
                echo json_encode($resultArr);
                exit;
            }

            $key = "getcode_register_" . $mobile;
            $code_byredis = Yii::$app->redis->get($key);
            if ($code_byredis == $code) {
                //用户自己的邀请码
                $invite_code = $this->getCode();
                $create_time = date('Y-m-d H:i:s');
                //通过openid获取用户的from_code
                //是否是先花花原有用户
                $userxhh = Userxhh::find()->where(['mobile' => $mobile])->one();
                if (isset($userxhh->user_id)) {
                    //更新先花花用户的openid进行绑定
                    $sql = "insert into " . User::tableName() . "(openid,invite_code,from_code,mobile,user_type,school,school_id,edu,school_time,realname,identity,school_valid,identity_valid,come_from,create_time,last_login_time,last_login_type,is_red_packets) ";
                    $sql .= "value('" . $openid . "','$invite_code','$from_code','$mobile',1,'" . $userxhh->school . "',$userxhh->school_id,$userxhh->edu,'" . $userxhh->school_time . "','" . $userxhh->realname . "','" . $userxhh->identity . "',2,2,1,'" . $create_time . "','" . $create_time . "','weixin','yes')";

                    $transaction = Yii::$app->db->beginTransaction();
                    $retUpdate = Yii::$app->db->createCommand($sql)->execute();
                    if ($retUpdate) {
                        $userid = Yii::$app->db->getLastInsertID();
                        $ip = Common::get_client_ip();
                        $userExtendModel = new User_extend();
                        $extend = [
                            'user_id' => $userid,
                            'reg_ip' => $ip,
                        ];
                        $userExtendModel->addRecord($extend);
                        $userinfo['school_id'] = $userxhh->school_id;
                        $userinfo['identity'] = $userxhh->identity;
                        $userinfo['school_time'] = $userxhh->school_time;
                        $userinfo['edu'] = $userxhh->edu;
                        $ret_acc = $this->createAccount($userid, $userinfo);
                        if ($ret_acc) {
                            $transaction->commit();
                        } else {
                            $transaction->rollBack();
                        }
                    } else {
                        $transaction->rollBack();
                    }
                    $userTemporaryModel = new User_temporary_quota();
                    $userTemporaryModel->setTemporary($userid, 500, 28, '注册提临额', 1);

                    //查询红包信息
                    $userinfowx = Userwx::find()->select(array('id'))->where(['openid' => $openid])->one();
                    $red_packets = Newuser_red_packets_receive::find()->select(array('id', 'grant_id', 'auth_user_id', 'amount', 'invalid_time', 'status'))->where(['wx_id' => $userinfowx->id, 'status' => 'NORMAL'])->andWhere("invalid_time > '$create_time'")->all();
                    if (!empty($red_packets)) {
                        $red_packets_receive = new Red_packets_receive();
                        $red_packet_amount = 0;
                        //获取所有应该体现的红包总额
                        foreach ($red_packets as $key => $value) {
                            $red_packet_amount += $value['amount'];
                            //修改红包记录表
                            $sql_new_red = "update " . Newuser_red_packets_receive::tableName() . " set current_amount= " . $value['amount'] . ", status='WITHDRAW', last_modify_time='$create_time', version=version+1 where id=" . $value['id'];
                            Yii::$app->db->createCommand($sql_new_red)->execute();

                            //新增加一条红包发放记录
                            //领取红包
                            $condition = array(
                                'user_id' => $userid,
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
                        $account = new Account();
                        $ret_account = $account->setAccountinfo($userid, $condition_account);
                    }

                    $this->setVal('mobile', $mobile);
                    $this->setVal('openid', $openid);
                    //@TODO 2.6.0
                    $user_info = User::find()->where(['mobile' => $mobile])->one();
                    Yii::$app->userWap->login($user_info, 1);

                    //删除redis里存储的key
                    Yii::$app->redis->del($key);

                    $nextPage = $this->getVal('nextPageUrl_login');
                    if (empty($nextPage)) {
                        $nextPage = "/dev/loan";
                    }
                    $resultArr = array('ret' => '2', 'url' => $nextPage);
                    echo json_encode($resultArr);
                    exit; //原有先花花过审用户
                } else {
                    //保存用户信息
                    $transaction = Yii::$app->db->beginTransaction();
                    $sql = "insert into " . User::tableName() . "(openid,mobile,user_type,invite_code,from_code,create_time,last_login_time,last_login_type,is_red_packets) value('$openid','$mobile',2,'$invite_code','$from_code','$create_time','$create_time','weixin','yes')";
                    $ret = Yii::$app->db->createCommand($sql)->execute();
                    if ($ret) {
                        $user_id = Yii::$app->db->getLastInsertID();
                        $ip = Common::get_client_ip();
                        $userExtendModel = new User_extend();
                        $extend = [
                            'user_id' => $user_id,
                            'reg_ip' => $ip,
                        ];
                        $userExtendModel->addRecord($extend);
                        //创建账户信息
                        $ret_acc = $this->createAccount($user_id);
                        if ($ret_acc) {
                            $transaction->commit();
                            $userTemporaryModel = new User_temporary_quota();
                            $userTemporaryModel->setTemporary($user_id, 500, 28, '注册提临额', 1);
                            //查询红包信息
                            $userinfowx = Userwx::find()->select(array('id'))->where(['openid' => $openid])->one();
                            $red_packets = Newuser_red_packets_receive::find()->select(array('id', 'grant_id', 'auth_user_id', 'amount', 'invalid_time', 'status'))->where(['wx_id' => $userinfowx->id, 'status' => 'NORMAL'])->andWhere("invalid_time > '$create_time'")->all();
                            if (!empty($red_packets)) {
                                $red_packets_receive = new Red_packets_receive();
                                $red_packet_amount = 0;
                                //获取所有应该体现的红包总额
                                foreach ($red_packets as $key => $value) {
                                    $red_packet_amount += $value['amount'];
                                    //修改红包记录表
                                    $sql_new_red = "update " . Newuser_red_packets_receive::tableName() . " set current_amount= " . $value['amount'] . ", status='WITHDRAW', last_modify_time='$create_time', version=version+1 where id=" . $value['id'];
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
                                $account = new Account();
                                $ret_account = $account->setAccountinfo($user_id, $condition_account);
                            }

                            $this->setVal('mobile', $mobile);
                            $this->setVal('openid', $openid);
                            //@TODO 2.6.0
                            $user_info = User::find()->where(['mobile' => $mobile])->one();
                            Yii::$app->userWap->login($user_info, 1);

                            //删除redis里存储的key
                            Yii::$app->redis->del($key);

                            $nextPage = $this->getVal('nextPageUrl_login');
                            if (empty($nextPage)) {
                                $nextPage = "/dev/loan";
                            }

                            $resultArr = array('ret' => '2', 'url' => $nextPage);
                            echo json_encode($resultArr);
                            exit;
                        } else {
                            $transaction->rollBack();
                            $resultArr = array('ret' => '1', 'url' => '');
                            echo json_encode($resultArr);
                            exit;
                        }
                    } else {
                        $transaction->rollBack();
                        $resultArr = array('ret' => '1', 'url' => '');
                        echo json_encode($resultArr);
                        exit;
                    }
                }
            } else {
                $resultArr = array('ret' => '3', 'url' => '');
                echo json_encode($resultArr);
                exit;
            }
        }
    }

    public function actionOne() {
        $this->getView()->title = "手机验证";
        $this->layout = 'inv';
        $openid = $this->getVal('openid');

        //获取code
        if (empty($openid)) {
            if (isset($_GET['code'])) {
                $code = $_GET['code'];
                $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . self::$_appid . "&secret=" . self::$_appSecret . "&code=" . $code . "&grant_type=authorization_code";
                $data = Http::getCurl($url);
                $resultArr = json_decode($data, true); //转为数组
                if (isset($resultArr['openid']) && !empty($resultArr['openid'])) {
                    $isUser = $this->isOpenidReg($resultArr['openid']);
                    if (!$isUser) {
                        $usinfo = $this->getWebAuthThree($resultArr);
                        //保存新用户
                        if ($this->openidRegSave($usinfo)) {
                            $this->setVal('openid', $usinfo["openid"]);
                        } else {
                            //保存微信用户失败，去出错页面
                            return $this->redirect('/dev/site/error');
                        }
                    } else {
                        $this->setVal('openid', $resultArr['openid']);
                    }
                } else {
                    //没有取到token值和openid，去错误页面
                    return $this->redirect('/dev/site/error');
                }
            }
            $openid = $this->getVal('openid');
        }

        //根据openid查询用户来源的邀请码
        $invite_code = Attention::find()->select(array('qr_id'))->where(['openid' => $openid])->orderBy('id desc')->one();
        if (!empty($invite_code) && (strlen($invite_code['qr_id']) <= 5 && strlen($invite_code['qr_id']) >= 3)) {
            $code = $invite_code['qr_id'];
        } else {
            $code = '';
        }

        $type = isset($_GET['type']) ? $_GET['type'] : '';
        if (isset($_GET['url'])) {
            $redirUrl = urldecode($_GET['url']);
            if ($redirUrl) {
                $this->setVal('nextPageUrl', $redirUrl);
            }
        }

        $userinfo = User::find()->select(array('mobile'))->where(['openid' => $openid])->one();

        $jsinfo = $this->getWxParam();
        return $this->render('one', ['jsinfo' => $jsinfo, 'userinfo' => $userinfo, 'type' => $type, 'invite_code' => $code]);
    }

    public function actionAgreement() {
        $this->getView()->title = "注册协议";
        return $this->render('agreement');
    }

    public function actionCanclefromcode() {
        $content = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '没有来源';
        Logger::dayLog('devsetfromcode', 'canclefromcode', $content);
        $mobile = Yii::$app->request->post('mobile', '');
        if (empty($mobile)) {
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

        $nextPage = $this->getVal('nextPageUrl');
        if (empty($nextPage)) {
            $nextPage = "/dev/loan";
        }
        //删除
        $this->delVal("nextPageUrl");

        $resultArr = array('ret' => '0', 'url' => $nextPage);
        echo json_encode($resultArr);
        exit;
    }

    public function actionSetfromcode() {

        $content = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '没有来源';
        Logger::dayLog('devsetfromcode', 'setfromcode', $content);
        $mobile = Yii::$app->request->post('mobile', '');
        if (empty($mobile)) {
            $resultArr = array('ret' => '2', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }
        $from_code = !empty($_POST['from_code']) ? $_POST['from_code'] : '';

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

        $userid = $isReg->user_id;

        $userbyfromcode = User::find()->where(['invite_code' => "$from_code"])->one();
        if (isset($userbyfromcode->invite_code) && !empty($userbyfromcode->invite_code)) {
            if ($userbyfromcode->status == 5) {
                $resultArr = array('ret' => '3', 'url' => '');
                echo json_encode($resultArr);
                exit;
            }

            $friendModel = new Friends();
            $friendModel->refreshFriend($userid, $userbyfromcode->user_id);
        } else {
            //判断用户填写的邀请码是否是渠道邀请码
            $invite_qrcode = Common::invtecodefrombyqrcode($from_code);
            if (!$invite_qrcode) {
                $resultArr = array('ret' => '4', 'url' => '');
                echo json_encode($resultArr);
                exit;
            }
        }

        //修改注册的邀请码
        $isReg->from_code = $from_code;
        if ($isReg->save()) {
            if (!empty($userbyfromcode)) {
                //提额
                $sum = User_amount_list::getSumByType($userbyfromcode->user_id, 5);
                $num = $sum < 3000 ? (3000 - $sum > 30 ? 30 : 3000 - $sum) : 0;
                if ($num > 0) {
                    $sql_fromcode = "update " . Account::tableName() . " set remain_amount=remain_amount-$num,amount=amount+$num,current_amount=current_amount+$num where user_id=" . $userbyfromcode['user_id'];
                    $user_fromcode = Yii::$app->db->createCommand($sql_fromcode)->execute();
                    //记录提额的日志
                    $amount_date = array(
                        'type' => 5,
                        'user_id' => $userbyfromcode['user_id'],
                        'amount' => $num
                    );
                    $user_amount = new User_amount_list();
                    $user_amount->CreateAmount($amount_date);
                }

                //注册者提额30点
                $sql_registercode = "update " . Account::tableName() . " set remain_amount=remain_amount-30,amount=amount+30,current_amount=current_amount+30 where user_id=" . $userid;
                $user_registercode = Yii::$app->db->createCommand($sql_registercode)->execute();
                //记录提额的日志
                $amount_from_date = array(
                    'type' => 2,
                    'user_id' => $userid,
                    'amount' => 30
                );
                $user_amount = new User_amount_list();
                $user_amount->CreateAmount($amount_from_date);
            } else if ($invite_qrcode == true) {
                //注册者提额30点
                $sql_registercode = "update " . Account::tableName() . " set remain_amount=remain_amount-30,amount=amount+30,current_amount=current_amount+30 where user_id=" . $userid;
                $user_registercode = Yii::$app->db->createCommand($sql_registercode)->execute();
                //记录提额的日志
                $amount_from_date = array(
                    'type' => 2,
                    'user_id' => $userid,
                    'amount' => 30
                );
                $user_amount = new User_amount_list();
                $user_amount->CreateAmount($amount_from_date);
            }

            $nextPage = $this->getVal('nextPageUrl');
            if (empty($nextPage)) {
                $nextPage = "/dev/loan";
            }
            //删除
            $this->delVal("nextPageUrl");

            $resultArr = array('ret' => '0', 'url' => $nextPage);
            echo json_encode($resultArr);
            exit;
        } else {
            $resultArr = array('ret' => '5', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }
    }

    //手机验证，成功开通账户信息
    public function actionOnesave() {
        //
        $mobile = $_POST['mobile'];
        $code = $_POST['code'];
        $user_type = 0;
        $url_type = $_POST['url_type'];
        $from_code = !empty($_POST['from_code']) ? $_POST['from_code'] : '';
        $openid = $this->getVal('openid');
        //echo $openid;exit;
        ///////////////////////////////
        //判断手机是否注册
        $isReg = User::find()->where(['mobile' => $mobile])->one();
        if (isset($isReg->user_id)) {
            $resultArr = array('ret' => '2', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }
        if (empty($openid)) {
            $resultArr = array('ret' => '11', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }

        //判断该openid是否注册
        $userinfobyopenid = User::find()->where(['openid' => $openid])->one();
        if (isset($userinfobyopenid)) {
            $resultArr = array('ret' => '5', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }
        $key = "getcode_register_" . $mobile;
        $code_byredis = Yii::$app->redis->get($key);
        if ($code_byredis == $code) {
            //判断填写的邀请码是否正确
            if (!empty($from_code)) {
                $userbyfromcode = User::find()->where(['invite_code' => "$from_code"])->one();
                if (isset($userbyfromcode->invite_code) && !empty($userbyfromcode->invite_code)) {
                    if ($userbyfromcode->status == 5) {
                        $resultArr = array('ret' => '6', 'url' => '');
                        echo json_encode($resultArr);
                        exit;
                    }
                } else {
                    //判断用户填写的邀请码是否是渠道邀请码
                    $invite_qrcode = Common::invtecodefrombyqrcode($from_code);
                    if (!$invite_qrcode) {
                        $resultArr = array('ret' => '7', 'url' => '');
                        echo json_encode($resultArr);
                        exit;
                    }
                }
            }
            //用户自己的邀请码
            $invite_code = $this->getCode();
            $create_time = date('Y-m-d H:i:s');
            //通过openid获取用户的from_code
            //是否是先花花原有用户
            $userxhh = Userxhh::find()->where(['mobile' => $mobile])->one();
            if (isset($userxhh->user_id)) {
//     			$userinfowx = Userwx::find()->select('from_code')->where(['openid'=>$openid])->one();
//     			$from_code = !empty($userinfowx['from_code']) ? $userinfowx['from_code'] : '999999';
                //更新先花花用户的openid进行绑定
                $sql = "insert into " . User::tableName() . "(openid,invite_code,from_code,mobile,user_type,school,school_id,edu,school_time,realname,identity,school_valid,identity_valid,come_from,create_time,last_login_time,last_login_type,is_red_packets) ";
                $sql .= "value('" . $openid . "','$invite_code','$from_code','$mobile',0,'" . $userxhh->school . "',$userxhh->school_id,$userxhh->edu,'" . $userxhh->school_time . "','" . $userxhh->realname . "','" . $userxhh->identity . "',2,2,1,'" . $create_time . "','" . $create_time . "','weixin','yes')";

                $transaction = Yii::$app->db->beginTransaction();
                $retUpdate = Yii::$app->db->createCommand($sql)->execute();
                if ($retUpdate) {
                    $userid = Yii::$app->db->getLastInsertID();
                    if (!empty($from_code) && (strlen($from_code) >= 6)) {
                        $friendModel = new Friends();
                        $friendModel->refreshFriend($userid, $userbyfromcode->user_id);
                    }
                    if (!empty($userxhh->school)) {
                        $friendModel->refreshFriend($userid);
                    }
                    $userinfo['school_id'] = $userxhh->school_id;
                    $userinfo['identity'] = $userxhh->identity;
                    $userinfo['school_time'] = $userxhh->school_time;
                    $userinfo['edu'] = $userxhh->edu;
                    $ret_acc = $this->createAccount($userid, $userinfo);
                    //查询邀请码的来源
                    if (!empty($from_code)) {
                        //$userinfo_fromcode = User::find()->select(array('user_id'))->where(['invite_code'=>$from_code])->one();
                        if (!empty($userbyfromcode)) {
                            //邀请人提额
                            $userbyfrom_count = User::find()->where(['from_code' => "$from_code"])->count();
                            if ($userbyfrom_count <= 100) {
                                $sql_fromcode = "update " . Account::tableName() . " set remain_amount=remain_amount-30,amount=amount+30,current_amount=current_amount+30 where user_id=" . $userbyfromcode['user_id'];
                                $user_fromcode = Yii::$app->db->createCommand($sql_fromcode)->execute();
                                //记录提额的日志
                                $amount_date = array(
                                    'type' => 5,
                                    'user_id' => $userbyfromcode['user_id'],
                                    'amount' => 30
                                );
                                $user_amount = new User_amount_list();
                                $user_amount->CreateAmount($amount_date);
                            }

                            //注册者提额30点
                            $sql_registercode = "update " . Account::tableName() . " set remain_amount=remain_amount-30,amount=amount+30,current_amount=current_amount+30 where user_id=" . $userid;
                            $user_registercode = Yii::$app->db->createCommand($sql_registercode)->execute();
                            //记录提额的日志
                            $amount_from_date = array(
                                'type' => 2,
                                'user_id' => $userid,
                                'amount' => 30
                            );
                            $user_amount = new User_amount_list();
                            $user_amount->CreateAmount($amount_from_date);
                        } else if ($invite_qrcode == true) {
                            //注册者提额30点
                            $sql_registercode = "update " . Account::tableName() . " set remain_amount=remain_amount-30,amount=amount+30,current_amount=current_amount+30 where user_id=" . $userid;
                            $user_registercode = Yii::$app->db->createCommand($sql_registercode)->execute();
                            //记录提额的日志
                            $amount_from_date = array(
                                'type' => 2,
                                'user_id' => $userid,
                                'amount' => 30
                            );
                            $user_amount = new User_amount_list();
                            $user_amount->CreateAmount($amount_from_date);
                        }
                    }
                    if ($ret_acc) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                    }
                } else {
                    $transaction->rollBack();
                }

                //查询红包信息
                $userinfowx = Userwx::find()->select(array('id'))->where(['openid' => $openid])->one();
                $red_packets = Newuser_red_packets_receive::find()->select(array('id', 'grant_id', 'auth_user_id', 'amount', 'invalid_time', 'status'))->where(['wx_id' => $userinfowx->id, 'status' => 'NORMAL'])->andWhere("invalid_time > '$create_time'")->all();
                if (!empty($red_packets)) {
                    $red_packets_receive = new Red_packets_receive();
                    $red_packet_amount = 0;
                    //获取所有应该体现的红包总额
                    foreach ($red_packets as $key => $value) {
                        $red_packet_amount += $value['amount'];
                        //修改红包记录表
                        $sql_new_red = "update " . Newuser_red_packets_receive::tableName() . " set current_amount= " . $value['amount'] . ", status='WITHDRAW', last_modify_time='$create_time', version=version+1 where id=" . $value['id'];
                        Yii::$app->db->createCommand($sql_new_red)->execute();

                        //新增加一条红包发放记录
                        //领取红包
                        $condition = array(
                            'user_id' => $userid,
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
                    $account = new Account();
                    $ret_account = $account->setAccountinfo($userid, $condition_account);
                }
                $nextPage = $this->getVal('nextPageUrl');
                if (empty($nextPage)) {
                    //$nextPage = "/dev/invest?type=first" ;
                    $nextPage = "/dev/loan";
                }
                //删除
                $this->delVal("nextPageUrl");
                $this->setVal('mobile', $mobile);
                $this->setVal('openid', $openid);

                //删除redis里存储的key
                Yii::$app->redis->del($key);

                $resultArr = array('ret' => '3', 'url' => $nextPage);
                echo json_encode($resultArr);
                exit; //原有先花花过审用户
            } else {
                if (!empty($url_type)) {
                    $resultArr = array('ret' => '4', 'url' => '');
                    echo json_encode($resultArr);
                    exit;
                }
                //保存用户信息
                $transaction = Yii::$app->db->beginTransaction();
                $sql = "insert into " . User::tableName() . "(openid,mobile,user_type,invite_code,from_code,create_time,last_login_time,last_login_type,is_red_packets) value('$openid','$mobile','$user_type','$invite_code','$from_code','$create_time','$create_time','weixin','yes')";
                $ret = Yii::$app->db->createCommand($sql)->execute();
                if ($ret) {
                    $user_id = Yii::$app->db->getLastInsertID();
                    if (!empty($from_code) && (strlen($from_code) >= 6)) {
                        $friendModel = new Friends();
                        $friendModel->refreshFriend($user_id, $userbyfromcode->user_id);
                    }
                    //创建账户信息
                    $ret_acc = $this->createAccount($user_id);
                    //查询邀请码的来源
                    if (!empty($from_code)) {
                        //$userinfo_fromcode = User::find()->select(array('user_id'))->where(['invite_code'=>$from_code])->one();
                        if (!empty($userbyfromcode)) {
                            //提额
                            $userbyfrom_count = User::find()->where(['from_code' => "$from_code"])->count();
                            if ($userbyfrom_count <= 100) {
                                $sql_fromcode = "update " . Account::tableName() . " set remain_amount=remain_amount-30,amount=amount+30,current_amount=current_amount+30 where user_id=" . $userbyfromcode['user_id'];
                                $user_fromcode = Yii::$app->db->createCommand($sql_fromcode)->execute();
                                //记录提额的日志
                                $amount_date = array(
                                    'type' => 5,
                                    'user_id' => $userbyfromcode['user_id'],
                                    'amount' => 30
                                );
                                $user_amount = new User_amount_list();
                                $user_amount->CreateAmount($amount_date);
                            }

                            //注册者提额30点
                            $sql_registercode = "update " . Account::tableName() . " set remain_amount=remain_amount-30,amount=amount+30,current_amount=current_amount+30 where user_id=" . $user_id;
                            $user_registercode = Yii::$app->db->createCommand($sql_registercode)->execute();
                            //记录提额的日志
                            $amount_from_date = array(
                                'type' => 2,
                                'user_id' => $user_id,
                                'amount' => 30
                            );
                            $user_amount = new User_amount_list();
                            $user_amount->CreateAmount($amount_from_date);
                        } else if ($invite_qrcode == true) {
                            //注册者提额30点
                            $sql_registercode = "update " . Account::tableName() . " set remain_amount=remain_amount-30,amount=amount+30,current_amount=current_amount+30 where user_id=" . $user_id;
                            $user_registercode = Yii::$app->db->createCommand($sql_registercode)->execute();
                            //记录提额的日志
                            $amount_from_date = array(
                                'type' => 2,
                                'user_id' => $user_id,
                                'amount' => 30
                            );
                            $user_amount = new User_amount_list();
                            $user_amount->CreateAmount($amount_from_date);
                        }
                    }
                    if ($ret_acc) {
                        $transaction->commit();

                        //查询红包信息
                        $userinfowx = Userwx::find()->select(array('id'))->where(['openid' => $openid])->one();
                        $red_packets = Newuser_red_packets_receive::find()->select(array('id', 'grant_id', 'auth_user_id', 'amount', 'invalid_time', 'status'))->where(['wx_id' => $userinfowx->id, 'status' => 'NORMAL'])->andWhere("invalid_time > '$create_time'")->all();
                        if (!empty($red_packets)) {
                            $red_packets_receive = new Red_packets_receive();
                            $red_packet_amount = 0;
                            //获取所有应该体现的红包总额
                            foreach ($red_packets as $key => $value) {
                                $red_packet_amount += $value['amount'];
                                //修改红包记录表
                                $sql_new_red = "update " . Newuser_red_packets_receive::tableName() . " set current_amount= " . $value['amount'] . ", status='WITHDRAW', last_modify_time='$create_time', version=version+1 where id=" . $value['id'];
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
                            $account = new Account();
                            $ret_account = $account->setAccountinfo($user_id, $condition_account);
                        }

                        //验证跳转页面
                        $nextPage = $this->getVal('nextPageUrl');
                        if (empty($nextPage)) {
                            if (isset($user_type) && !empty($user_type)) {
                                if ($user_type == '1') {
                                    //大学生
                                    //$nextPage = '/dev/invest?type=first' ;
                                    $nextPage = "/dev/loan";
                                } else if ($user_type == '2') {
                                    //社会人
                                    //$nextPage = '/dev/invest?type=first';
                                    $nextPage = "/dev/loan";
                                }
                            } else {
                                $nextPage = "/dev/loan";
                            }
                        }
                        //删除cookie
                        $this->delVal("nextPageUrl");
                        $this->setVal('mobile', $mobile);
                        $this->setVal('openid', $openid);

                        //删除redis里存储的key
                        Yii::$app->redis->del($key);

                        $resultArr = array('ret' => '0', 'msg' => '', 'url' => $nextPage);
                        echo json_encode($resultArr);
                        exit;
                    } else {
                        $transaction->rollBack();
                        $resultArr = array('ret' => '1', 'url' => '');
                        echo json_encode($resultArr);
                        exit;
                    }
                } else {
                    $transaction->rollBack();
                    $resultArr = array('ret' => '1', 'url' => '');
                    echo json_encode($resultArr);
                    exit;
                }
            }
        } else {
            $resultArr = array('ret' => '11', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }
    }

    public function actionSfen() {
        $this->getView()->title = '选择身份';
        $this->layout = 'loan';
        $openid = $this->getVal('openid');
        if (empty($openid)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $userinfo = Userwx::find()->where(['openid' => $openid])->one();
        return $this->render('sfen', ['userinfo' => $userinfo]);
    }

    public function actionLzh() {
        $openid = $this->getVal('openid');
        if (empty($openid)) {
            $resultArr = array('ret' => '0', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }

        $user_type = $_POST['user_type'];
        $userinfo = User::find()->where(['openid' => $openid])->one();
        if (empty($userinfo) && !isset($userinfo)) {
            $resultArr = array('ret' => '2', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }
        $user_id = $userinfo->user_id;
        //echo $user_id;
        $sql = "update " . user::tableName() . " set user_type= '$user_type' where user_id='$user_id'";
        $rets = Yii::$app->db->createCommand($sql)->execute();
        if ($rets) {
            $resultArr = array('ret' => '1', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }
    }

    //大学生学籍验证
    public function actionTwo() {
        //记录来源地址
        if (isset($_GET['url'])) {
            $redirUrl = urldecode($_GET['url']);
            if ($redirUrl) {
                $this->setVal('nextPageUrl', $redirUrl);
            }
        }
        $from = isset($_GET['from']) ? $_GET['from'] : '';
        $f = isset($_GET['f']) ? $_GET['f'] : '';
        $user_id = Yii::$app->request->get('user_id');
        if (!empty($user_id)) {
            $users = User::find()->select(array('realname', 'identity'))->where(['user_id' => $user_id])->one();
        } else {
            $openid = $this->getVal('openid');
            $users = User::find()->select(array('realname', 'identity'))->where(['openid' => $openid])->one();
        }
        ///操作完成后进行跳转
        $school = School::find()->all();
        $jsinfo = $this->getWxParam();
        if (isset($_GET['l']) && !empty($_GET['l'])) {
            $this->getView()->title = "身份验证";
            $l = isset($_GET['l']) ? $_GET['l'] : '';
            return $this->render('paytwo', ['school' => $school, 'from' => $from, 'f' => $f, 'url' => $l, 'jsinfo' => $jsinfo, 'users' => $users]);
        } else {
            $this->getView()->title = "学籍验证";
            return $this->render('two', ['school' => $school, 'from' => $from, 'f' => $f, 'jsinfo' => $jsinfo, 'users' => $users]);
        }
    }

    public function actionGetschool() {
        $school = School::find()->select(['school_id', 'school_name'])->all();
        $sch = array();
        foreach ($school as $key => $val) {
            $sch[$key]['id'] = $val->school_id;
            $sch[$key]['name'] = $val->school_name;
        }
        return json_encode($sch);
    }

    public function actionNamesave() {
        $post_data = Yii::$app->request->post();
        $openid = $this->getVal('openid');
        if (empty($openid)) {
            $resultArr = array('ret' => '1', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }
        $user = User::find()->where(['openid' => $openid])->one();
        $identity = $post_data['identity'];
        $realname = $post_data['realname'];

        if ($user->getIdentityValid($identity)) {
            $postdata = array(
                'name' => $realname,
                'idcard' => $identity
            );
            $openApi = new Apihttp;
            $validIdentity = $openApi->idValid($postdata);
            if ($validIdentity['res_code'] != '0000') {
                $resultArr = array('ret' => '11', 'url' => '');
                echo json_encode($resultArr);
                exit;
            }
            $identity_valid = 2;
        } else {
            $identity_valid = 4;
        }

        $userinfo = User::find()->where(['identity' => $identity])->one();
        if (!empty($userinfo) && $userinfo->openid != $user->openid) {
            $resultArr = array('ret' => '3', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }
        //计算用户的出生年份
        $birthday_year = intval(substr($identity, 6, 4));
        $sql = "update " . User::tableName() . " set realname='$realname',identity='$identity',birth_year='$birthday_year',identity_valid='$identity_valid' where openid='$openid';";
        $ret = Yii::$app->db->createCommand($sql)->execute();
        if ($ret) {
            $user_info = User::find()->select(array('user_id'))->where(['openid' => "$openid"])->one();
            if (!empty($user_info)) {
                $amount = 200;
                $acc_sql = "update " . Account::tableName() . " set remain_amount=remain_amount-" . $amount . ",amount=amount+" . $amount . ",current_amount=current_amount+" . $amount . " where user_id=" . $user_info->user_id;
                $ret_acc = Yii::$app->db->createCommand($acc_sql)->execute();

                //记录提额的日志
                $amount_from_date = array(
                    'type' => 15,
                    'user_id' => $user_info->user_id,
                    'amount' => 200
                );
                $user_amount = new User_amount_list();
                $user_amount->CreateAmount($amount_from_date);
            }
            $resultArr = array('ret' => '0');
            echo json_encode($resultArr);
            exit;
        } else {
            $resultArr = array('ret' => '2', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }
    }

    public function actionNamesaves() {
        $post_data = Yii::$app->request->post();
        if (!isset($post_data['userId']) || empty($post_data['userId'])) {
            $resultArr = array('ret' => '1', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }
        $user_id = intval($post_data['userId']);
        $user = User::find()->where(['user_id' => $user_id])->one();
        $identity = $post_data['identity'];
        $realname = $post_data['realname'];
        $edu = intval($post_data['edu']);

        if ($user->getIdentityValid($identity)) {
            $postdata = array(
                'name' => $realname,
                'idcard' => $identity
            );
            $openApi = new Apihttp;
            $validIdentity = $openApi->idValid($postdata);
            Logger::errorLog(print_r($validIdentity, true), 'identity');
            if ($validIdentity['res_code'] != '0000') {
                $resultArr = array('ret' => '11', 'url' => '');
                echo json_encode($resultArr);
                exit;
            }
            $identity_valid = 2;
        } else {
            $identity_valid = 4;
        }

        $extend_condition = array(
            'user_id' => $user_id,
            'edu' => $post_data['edu'],
            'marriage' => $post_data['marriage'],
            'home_area' => $post_data['district'],
            'home_address' => $post_data['home_address'],
        );
        //保存信息
        $user_extend = User_extend::getUserExtend($user->user_id);
        if (empty($user_extend)) {
            $extendModel = new User_extend();
            $extendModel->addRecord($extend_condition);
        } else {
            $user_extend->updateRecord($extend_condition);
        }
        $userinfo = User::find()->where(['identity' => $identity])->one();
        if (!empty($userinfo) && $userinfo->openid != $user->openid) {
            $resultArr = array('ret' => '3', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }
        if ($user->identity_valid != 2 || $user->identity_valid != 4) {
            //计算用户的出生年份
            $birthday_year = intval(substr($identity, 6, 4));
            $user->edu = $edu;
            $user->realname = $realname;
            $user->identity = $identity;
            $user->birth_year = $birthday_year;
            $user->identity_valid = $identity_valid;
            $ret = $user->save();
        } else {
            $user->edu = $edu;
            $ret = $user->save();
        }
        if ($ret) {
            $user_info = User::find()->where(['user_id' => "$user_id"])->one();
            if (!empty($user_info)) {
                $amount = 200;
                $acc_sql = "update " . Account::tableName() . " set remain_amount=remain_amount-" . $amount . ",amount=amount+" . $amount . ",current_amount=current_amount+" . $amount . " where user_id=" . $user_info->user_id;
                $ret_acc = Yii::$app->db->createCommand($acc_sql)->execute();

                //记录提额的日志
                $amount_from_date = array(
                    'type' => 15,
                    'user_id' => $user_info->user_id,
                    'amount' => 200
                );
                $user_amount = new User_amount_list();
                $user_amount->CreateAmount($amount_from_date);
            }

            $resultArr = array('ret' => '0', 'url' => '');
            //验证跳转页面
            $nextPage = $this->getVal('info_fromurl');
            if (!empty($nextPage)) {
                $resultArr['url'] = $nextPage;
            } else {
                $resultArr['url'] = '/dev/reg/company?user_id=' . $user['user_id'];
            }
            echo json_encode($resultArr);
            exit;
        } else {
            $resultArr = array('ret' => '2', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }
    }

    public function actionTwosave() {
        //学校ID
        $school = $_POST['school'];
        $school_name = $_POST['school_name'];
        $edu = $_POST['edu'];
        $school_time = $_POST['school_time'];
        $realname = $_POST['realname'];
        $identity = $_POST['identity'];
        $from_url = $_POST['from_url'];
        $f_url = $_POST['f_url'];
        $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';


        if (empty($realname) && empty($identity)) {
            $resultArr = array('ret' => '11', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }
        //验证身份证号的唯一性
        $userinfo = User::find()->where(['identity' => $identity])->one();
        if (!empty($user_id)) {
            $users = User::find()->select(array('user_id', 'user_type', 'school_valid'))->where(['user_id' => $user_id])->one();
        } else {
            $openid = $this->getVal('openid');
            $users = User::find()->select(array('user_id', 'user_type', 'school_valid'))->where(['openid' => $openid])->one();
        }
        if (!empty($userinfo) && $userinfo->user_id != $users->user_id) {
            if ($userinfo['status'] != 5) {
                $resultArr = array('ret' => '2', 'url' => '');
                echo json_encode($resultArr);
                exit;
            }
        }

        switch ($edu) {
            case 1:
                $ext_diploma = '博士';
                break;
            case 2:
                $ext_diploma = '硕士';
                break;
            case 3:
                $ext_diploma = '本科';
                break;
            default:
                $ext_diploma = '专科';
        }

        //调用学籍验证接口////////////////////////// 
        if ($school == '194') {
            if (empty($openid)) {
                $resultArr = array('ret' => '1', 'url' => '');
                echo json_encode($resultArr);
                exit;
            }

            //计算用户的出生年份
            $birthday_year = intval(substr($identity, 6, 4));
            $transaction = Yii::$app->db->beginTransaction();
            if ($users->school_valid != 1) {
                $history_info = new User_history_info();
                $history_info->user_id = $users->user_id;
                $history_info->user_type = $users->user_type;
                $history_info->data_type = 1;
                $history_info->company_school = $school_name;
                $history_info->industry_edu = $edu;
                $history_info->position_schooltime = $school_time;
                $history_info->create_time = date('Y-m-d H:i:s');
                $history_info->save();
            }
            if ($users->getIdentityValid($identity)) {
                $identity_valid = 2;
            } else {
                $identity_valid = 4;
            }
            $sql = "update " . User::tableName() . " set school='$school_name',school_id=$school,edu=$edu,school_time='$school_time',realname='$realname',identity='$identity',school_valid='$identity_valid',identity_valid=2,birth_year='$birthday_year' where user_type=1 and openid='$openid';";
            $ret = Yii::$app->db->createCommand($sql)->execute();
            $friendModel = new Friends();
            $friendModel->updateSchool($users->user_id);
            //判断用户是否在黑名单库中
            $blackinfo = Black_list::find()->where(['cred_no' => $identity])->one();
            if (!empty($blackinfo)) {
                //修改用户的状态
                $ret_status = $users->setBlack();
                if ($ret_status) {
                    $transaction->commit();
                    $resultArr = array('ret' => '3', 'url' => '');
                    echo json_encode($resultArr);
                    exit;
                } else {
                    $transaction->rollBack();
                    $resultArr = array('ret' => '3', 'url' => '');
                    echo json_encode($resultArr);
                    exit;
                }
            }
            if ($ret) {
                //取账户信息,重新计算额度
                $uinfo = User::find()->joinWith('account', true, 'LEFT JOIN')->where(['openid' => $openid])->one();
                $friendModel = new Friends();
                $friendModel->updateSchool($uinfo->user_id);
                $uinfo->school = $school_name;
                $uinfo->school_id = $school;
                $uinfo->edu = $edu;
                $uinfo->school_time = $school_time;
                $uinfo->identity = $identity;

                $amount = 200;
                $acc_sql = "update " . Account::tableName() . " set remain_amount=remain_amount-" . $amount . ",amount=amount+" . $amount . ",current_amount=current_amount+" . $amount . " where user_id=" . $users->user_id;
                $upacc = Yii::$app->db->createCommand($acc_sql)->execute();

                //记录提额的日志
                $amount_from_date = array(
                    'type' => 15,
                    'user_id' => $users->user_id,
                    'amount' => 200
                );
                $user_amount = new User_amount_list();
                $user_amount->CreateAmount($amount_from_date);
                //$upacc = $this->updateAccount($uinfo);
                if ($upacc >= 0) {
                    $transaction->commit();
                    $token_id = isset($_COOKIE['PHPSESSID']) ? $_COOKIE['PHPSESSID'] : '';
                    if (empty($token_id)) {
                        $userpass = User_password::find()->select('device_tokens')->where(['user_id' => $uinfo->user_id])->one();
                        $token_id = !empty($userpass->device_tokens) ? $userpass->device_tokens : rand(100000000, 999999999);
                    }
                    $params = array(
                        'account_name' => $realname,
                        'mobile' => $uinfo->mobile,
                        'id_number' => $identity,
                        'ext_school' => $school_name,
                        'ext_diploma' => $ext_diploma,
                        'ext_start_year' => $school_time,
                        'seq_id' => date('YmdHis') . $user->user_id,
                        'ext_birth_year' => $birthday_year,
                        'token_id' => $token_id,
                        'ip_address' => Yii::$app->request->getUserIP(),
                        'type' => 2,
                    );
                    $api = new Apihttp();
                    $result_student = $api->riskLoanValid($params);
                    $fraudmetrix = new Fraudmetrix_return_info();
                    $fraudmetrix->CreateFraudmetrix($result_student, $users->user_id);
                    if ($result_student->rsp_code == '0000') {
                        $final_score = trim($result_student->finalScore);
                        $final_result = trim($result_student->result);
                        if (isset($final_score)) {
                            if ($final_result == 'Reject') {
                                $ret_status = $users->setBlack();
                                $sql_score = "update " . User::tableName() . " set final_score='$final_score' where user_id=" . $uinfo['user_id'];
                            } else {
                                if (($final_score >= 60) && ($final_score < 80)) {
                                    $sql_score = "update " . User::tableName() . " set status=4,final_score='$final_score' where user_id=" . $uinfo['user_id'];
                                } else if ($final_score >= 80) {
                                    $ret_status = $users->setBlack();
                                    $sql_score = "update " . User::tableName() . " set final_score='$final_score' where user_id=" . $uinfo['user_id'];
                                } else {
                                    $sql_score = "update " . User::tableName() . " set final_score='$final_score' where user_id=" . $uinfo['user_id'];
                                }
                            }
                            $ret_score = Yii::$app->db->createCommand($sql_score)->execute();
                        }
                    }

                    //验证跳转页面
                    $nextPage = $this->getVal('nextPageUrl');
                    if (empty($nextPage)) {
                        $nextPage = "/dev/reg/pic?user_id=" . $user_id . "&f=$f_url";
                    } else {
                        if ((empty($from_url) && $nextPage == '/dev/account/remain') || (empty($from_url) && $nextPage == '/dev/account/info')) {
                            if (empty($uinfo['pic_identity'])) {
                                $nextPage = "/dev/reg/pic?user_id=" . $user_id . "&f=$f_url";
                            } else {
                                $nextPage = "/dev/bank?f=$f_url";
                            }
                        }
                    }
                    //删除session
                    $this->delVal("nextPageUrl");
                    $resultArr = array('ret' => '0', 'url' => $nextPage);
                    echo json_encode($resultArr);
                    exit;
                } else {
                    $transaction->rollBack();
                    $resultArr = array('ret' => '1', 'url' => '');
                    echo json_encode($resultArr);
                    exit;
                }
            } else {
                $resultArr = array('ret' => '1', 'url' => '');
                echo json_encode($resultArr);
                exit;
            }
        } else {
            $postData = array(
                'name' => $realname, // 姓名
                'idcode' => $identity, // 身份证
                'educationdegree' => $ext_diploma, //学历 专科  本科  CCCHECKRS 学习层次比对结果
                'graduate' => $school_name, //毕业院校  YXMCCHECKRS 院校比对结果
                'enroldate' => $school_time, // 入学年份 2012 RXRQCHECKRS 入学日期比对结果
            );
            $openApi = new ApiClientCrypt;
            $res = $openApi->sent('eduroll/index', $postData);
            $res = $openApi->parseResponse($res);
            Logger::errorLog(print_r($res, true), 'stuVerfiy');
            //end/////////////////////////////////
            if ($res['res_code'] == 0 && $res['res_data']['status'] == 1) {
                if (empty($openid)) {
                    $resultArr = array('ret' => '1', 'url' => '');
                    echo json_encode($resultArr);
                    exit;
                }
                //计算用户的出生年份
                $birthday_year = intval(substr($identity, 6, 4));
                $transaction = Yii::$app->db->beginTransaction();
                if ($users->school_valid != 1) {
                    $history_info = new User_history_info();
                    $history_info->user_id = $users->user_id;
                    $history_info->user_type = $users->user_type;
                    $history_info->data_type = 1;
                    $history_info->company_school = $school_name;
                    $history_info->industry_edu = $edu;
                    $history_info->position_schooltime = $school_time;
                    $history_info->create_time = date('Y-m-d H:i:s');
                    $history_info->save();
                }
                if ($users->getIdentityValid($identity)) {
                    $identity_valid = 2;
                } else {
                    $identity_valid = 4;
                }
                $sql = "update " . User::tableName() . " set school='$school_name',school_id=$school,edu=$edu,school_time='$school_time',realname='$realname',identity='$identity',school_valid=2,identity_valid='$identity_valid',birth_year='$birthday_year' where user_type=1 and openid='$openid';";
                $ret = Yii::$app->db->createCommand($sql)->execute();

                //判断用户是否在黑名单库中
                $blackinfo = Black_list::find()->where(['cred_no' => $identity])->one();
                if (!empty($blackinfo)) {
                    //修改用户的状态
                    $ret_status = $users->setBlack();
                    if ($ret_status) {
                        $transaction->commit();
                        $resultArr = array('ret' => '3', 'url' => '');
                        echo json_encode($resultArr);
                        exit;
                    } else {
                        $transaction->rollBack();
                        $resultArr = array('ret' => '3', 'url' => '');
                        echo json_encode($resultArr);
                        exit;
                    }
                }
                if ($ret) {
                    //取账户信息,重新计算额度
                    $uinfo = User::find()->joinWith('account', true, 'LEFT JOIN')->where(['openid' => $openid])->one();
                    $friendModel = new Friends();
                    $friendModel->updateSchool($uinfo->user_id);
                    $uinfo->school = $school_name;
                    $uinfo->school_id = $school;
                    $uinfo->edu = $edu;
                    $uinfo->school_time = $school_time;
                    $uinfo->identity = $identity;

                    $amount = 200;
                    $acc_sql = "update " . Account::tableName() . " set remain_amount=remain_amount-" . $amount . ",amount=amount+" . $amount . ",current_amount=current_amount+" . $amount . " where user_id=" . $users->user_id;
                    $upacc = Yii::$app->db->createCommand($acc_sql)->execute();
                    //$upacc = $this->updateAccount($uinfo);
                    //记录提额的日志
                    $amount_from_date = array(
                        'type' => 15,
                        'user_id' => $users->user_id,
                        'amount' => 200
                    );
                    $user_amount = new User_amount_list();
                    $user_amount->CreateAmount($amount_from_date);
                    if ($upacc >= 0) {
                        $transaction->commit();
                        $token_id = isset($_COOKIE['PHPSESSID']) ? $_COOKIE['PHPSESSID'] : '';
                        if (empty($token_id)) {
                            $userpass = User_password::find()->select('device_tokens')->where(['user_id' => $uinfo->user_id])->one();
                            $token_id = !empty($userpass->device_tokens) ? $userpass->device_tokens : rand(100000000, 999999999);
                        }
                        $params = array(
                            'account_name' => $realname,
                            'mobile' => $uinfo->mobile,
                            'id_number' => $identity,
                            'ext_school' => $school_name,
                            'ext_diploma' => $ext_diploma,
                            'ext_start_year' => $school_time,
                            'seq_id' => date('YmdHis') . $user->user_id,
                            'ext_birth_year' => $birthday_year,
                            'token_id' => $token_id,
                            'ip_address' => Yii::$app->request->getUserIP(),
                            'type' => 2,
                        );
                        $api = new Apihttp();
                        $result_student = $api->riskLoanValid($params);
                        $fraudmetrix = new Fraudmetrix_return_info();
                        $fraudmetrix->CreateFraudmetrix($result_student, $users->user_id);
                        if ($result_student->rsp_code == '0000') {
                            $final_score = trim($result_student->finalScore);
                            $final_result = trim($result_student->result);
                            if (isset($final_score)) {
                                if ($final_result == 'Reject') {
                                    $ret_status = $users->setBlack();
                                    $sql_score = "update " . User::tableName() . " set final_score='$final_score' where user_id=" . $uinfo['user_id'];
                                } else {
                                    if (($final_score >= 60) && ($final_score < 80)) {
                                        $sql_score = "update " . User::tableName() . " set status=4,final_score='$final_score' where user_id=" . $uinfo['user_id'];
                                    } else if ($final_score >= 80) {
                                        $ret_status = $users->setBlack();
                                        $sql_score = "update " . User::tableName() . " set final_score='$final_score' where user_id=" . $uinfo['user_id'];
                                    } else {
                                        $sql_score = "update " . User::tableName() . " set final_score='$final_score' where user_id=" . $uinfo['user_id'];
                                    }
                                }
                                $ret_score = Yii::$app->db->createCommand($sql_score)->execute();
                            }
                        }

                        //验证跳转页面
                        $nextPage = $this->getVal('nextPageUrl');
                        if (empty($nextPage)) {
                            $nextPage = "/dev/reg/pic?user_id=" . $user_id . "&f=$f_url";
                        } else {
                            if ((empty($from_url) && $nextPage == '/dev/account/remain') || (empty($from_url) && $nextPage == '/dev/account/info')) {
                                if (empty($uinfo['pic_identity'])) {
                                    $nextPage = "/dev/reg/pic?user_id=" . $user_id . "&f=$f_url";
                                } else {
                                    $nextPage = "/dev/bank?f=$f_url";
                                }
                            }
                        }
                        //删除session
                        $this->delVal("nextPageUrl");
                        $resultArr = array('ret' => '0', 'url' => $nextPage);
                        echo json_encode($resultArr);
                        exit;
                    } else {
                        $transaction->rollBack();
                        $resultArr = array('ret' => '1', 'url' => '');
                        echo json_encode($resultArr);
                        exit;
                    }
                } else {
                    $resultArr = array('ret' => '1', 'url' => '');
                    echo json_encode($resultArr);
                    exit;
                }
            } else {
                //学籍验证没有通过
                $openid = $this->getVal('openid');
                if (empty($openid)) {
                    $resultArr = array('ret' => '11', 'url' => '');
                    echo json_encode($resultArr);
                    exit;
                }
                $sql = "update " . User::tableName() . " set school_valid=3 where user_type=1 and openid='$openid'";
                $ret = Yii::$app->db->createCommand($sql)->execute();
                $resultArr = array('ret' => '1', 'url' => '');
                echo json_encode($resultArr);
                exit;
            }
        }
    }

    public function actionTwosaves() {
        //学校ID
        $school = $_POST['school'];
        $school_name = $_POST['school_name'];
        $edu = $_POST['edu'];
        $school_time = $_POST['school_time'];
        $from_url = isset($_POST['from_url']) ? $_POST['from_url'] : '';
        $f_url = isset($_POST['f_url']) ? $_POST['f_url'] : '';
// 		$school = "12";
// 		$school_name = '北京科技大学';
// 		$edu = "3";
// 		$school_time = "2012";
// 		$realname = "刘臻玮";
// 		$identity = "410402198902115534";
        $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';
        if (!empty($user_id)) {
            $users = User::find()->where(['user_id' => $user_id])->one();
        } else {
            $openid = $this->getVal('openid');
            $users = User::find()->where(['openid' => $openid])->one();
        }
        if ($users->realname == '' || $users->identity == '') {
            $resultArr = array('ret' => '12', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }

        switch ($edu) {
            case 1:
                $ext_diploma = '博士';
                break;
            case 2:
                $ext_diploma = '硕士';
                break;
            case 3:
                $ext_diploma = '本科';
                break;
            default:
                $ext_diploma = '专科';
        }

        //调用学籍验证接口////////////////////////// 
        if ($school == '194') {
            if (empty($openid)) {
                $resultArr = array('ret' => '1', 'url' => '');
                echo json_encode($resultArr);
                exit;
            }

            //计算用户的出生年份
            $birthday_year = intval(substr($users->identity, 6, 4));
            $transaction = Yii::$app->db->beginTransaction();
            if ($users->school_valid != 1) {
                $history_info = new User_history_info();
                $history_info->user_id = $users->user_id;
                $history_info->user_type = $users->user_type;
                $history_info->data_type = 1;
                $history_info->company_school = $school_name;
                $history_info->industry_edu = $edu;
                $history_info->position_schooltime = $school_time;
                $history_info->create_time = date('Y-m-d H:i:s');
                $history_info->save();
            }
            $sql = "update " . User::tableName() . " set school='$school_name',school_id=$school,edu=$edu,school_time='$school_time',realname='$users->realname',identity='$users->identity',school_valid=2,birth_year='$birthday_year' where user_type=1 and openid='$openid';";
            $ret = Yii::$app->db->createCommand($sql)->execute();

            //判断用户是否在黑名单库中
            $blackinfo = Black_list::find()->where(['cred_no' => $users->identity])->one();
            if (!empty($blackinfo)) {
                //修改用户的状态
                $ret_status = $users->setBlack();
                if ($ret_status) {
                    $transaction->commit();
                    $resultArr = array('ret' => '3', 'url' => '');
                    echo json_encode($resultArr);
                    exit;
                } else {
                    $transaction->rollBack();
                    $resultArr = array('ret' => '3', 'url' => '');
                    echo json_encode($resultArr);
                    exit;
                }
            }
            if ($ret) {
                //取账户信息,重新计算额度
                $uinfo = User::find()->joinWith('account', true, 'LEFT JOIN')->where(['openid' => $openid])->one();
                $friendModel = new Friends();
                $friendModel->updateSchool($uinfo->user_id);
                $uinfo->school = $school_name;
                $uinfo->school_id = $school;
                $uinfo->edu = $edu;
                $uinfo->school_time = $school_time;
                $uinfo->identity = $users->identity;

                //$upacc = $this->updateAccount($uinfo);
                $upacc = true;
                if ($upacc) {
                    $transaction->commit();
                    $token_id = isset($_COOKIE['PHPSESSID']) ? $_COOKIE['PHPSESSID'] : '';
                    if (empty($token_id)) {
                        $userpass = User_password::find()->select('device_tokens')->where(['user_id' => $uinfo->user_id])->one();
                        $token_id = !empty($userpass->device_tokens) ? $userpass->device_tokens : rand(100000000, 999999999);
                    }
                    $params = array(
                        'account_name' => $users->realname,
                        'mobile' => $uinfo->mobile,
                        'id_number' => $users->identity,
                        'ext_school' => $school_name,
                        'ext_diploma' => $ext_diploma,
                        'ext_start_year' => $school_time,
                        'seq_id' => date('YmdHis') . $uinfo->user_id,
                        'ext_birth_year' => $birthday_year,
                        'token_id' => $token_id,
                        'ip_address' => Yii::$app->request->getUserIP(),
                        'type' => 2,
                    );
                    $api = new Apihttp();
                    $result_student = $api->riskLoanValid($params);
                    $fraudmetrix = new Fraudmetrix_return_info();
                    $fraudmetrix->CreateFraudmetrix($result_student, $users->user_id);
                    if ($result_student->rsp_code == '0000') {
                        $final_score = trim($result_student->finalScore);
                        $final_result = trim($result_student->result);
                        if (isset($final_score)) {
                            if ($final_result == 'Reject') {
                                $ret_status = $users->setBlack();
                                $sql_score = "update " . User::tableName() . " set final_score='$final_score' where user_id=" . $uinfo['user_id'];
                            } else {
                                if (($final_score >= 60) && ($final_score < 80)) {
                                    $sql_score = "update " . User::tableName() . " set status=4,final_score='$final_score' where user_id=" . $uinfo['user_id'];
                                } else if ($final_score >= 80) {
                                    $ret_status = $users->setBlack();
                                    $sql_score = "update " . User::tableName() . " set final_score='$final_score' where user_id=" . $uinfo['user_id'];
                                } else {
                                    $sql_score = "update " . User::tableName() . " set final_score='$final_score' where user_id=" . $uinfo['user_id'];
                                }
                            }
                            $ret_score = Yii::$app->db->createCommand($sql_score)->execute();
                        }
                    }

                    //验证跳转页面
                    $nextPage = $this->getVal('nextPageUrl');
                    if (empty($nextPage)) {
                        $nextPage = "/dev/reg/pic?user_id=" . $user_id . "&f=$f_url";
                    } else {
                        if ((empty($from_url) && $nextPage == '/dev/account/remain') || (empty($from_url) && $nextPage == '/dev/account/info')) {
                            if (empty($uinfo['pic_identity'])) {
                                $nextPage = "/dev/reg/pic?user_id=" . $user_id . "&f=$f_url";
                            } else {
                                $nextPage = "/dev/bank?f=$f_url";
                            }
                        }
                    }
                    //删除session
                    $this->delVal("nextPageUrl");
                    $resultArr = array('ret' => '0', 'url' => $nextPage);
                    echo json_encode($resultArr);
                    exit;
                } else {
                    $transaction->rollBack();
                    $resultArr = array('ret' => '1', 'url' => '');
                    echo json_encode($resultArr);
                    exit;
                }
            } else {
                $resultArr = array('ret' => '1', 'url' => '');
                echo json_encode($resultArr);
                exit;
            }
        } else {
            $postData = array(
                'name' => $users->realname, // 姓名
                'idcode' => $users->identity, // 身份证
                'educationdegree' => $ext_diploma, //学历 专科  本科  CCCHECKRS 学习层次比对结果
                'graduate' => $school_name, //毕业院校  YXMCCHECKRS 院校比对结果
                'enroldate' => $school_time, // 入学年份 2012 RXRQCHECKRS 入学日期比对结果
            );
            $openApi = new ApiClientCrypt;
            $res = $openApi->sent('eduroll/index', $postData);
            $res = $openApi->parseResponse($res);
            Logger::errorLog(print_r($res, true), 'stuVerfiy');
            //end/////////////////////////////////
            if ($res['res_code'] == 0 && $res['res_data']['status'] == 1) {
                if (empty($openid)) {
                    $resultArr = array('ret' => '1', 'url' => '');
                    echo json_encode($resultArr);
                    exit;
                }
                //计算用户的出生年份
                $birthday_year = intval(substr($users->identity, 6, 4));
                $transaction = Yii::$app->db->beginTransaction();
                if ($users->school_valid != 1) {
                    $history_info = new User_history_info();
                    $history_info->user_id = $users->user_id;
                    $history_info->user_type = $users->user_type;
                    $history_info->data_type = 1;
                    $history_info->company_school = $school_name;
                    $history_info->industry_edu = $edu;
                    $history_info->position_schooltime = $school_time;
                    $history_info->create_time = date('Y-m-d H:i:s');
                    $history_info->save();
                }
                $sql = "update " . User::tableName() . " set school='$school_name',school_id=$school,edu=$edu,school_time='$school_time',realname='$users->realname',identity='$users->identity',school_valid=2,birth_year='$birthday_year' where user_type=1 and openid='$openid';";
                $ret = Yii::$app->db->createCommand($sql)->execute();

                //判断用户是否在黑名单库中
                $blackinfo = Black_list::find()->where(['cred_no' => $users->identity])->one();
                if (!empty($blackinfo)) {
                    //修改用户的状态
                    $ret_status = $users->setBlack();
                    if ($ret_status) {
                        $transaction->commit();
                        $resultArr = array('ret' => '3', 'url' => '');
                        echo json_encode($resultArr);
                        exit;
                    } else {
                        $transaction->rollBack();
                        $resultArr = array('ret' => '3', 'url' => '');
                        echo json_encode($resultArr);
                        exit;
                    }
                }
                if ($ret) {
                    //取账户信息,重新计算额度
                    $uinfo = User::find()->joinWith('account', true, 'LEFT JOIN')->where(['openid' => $openid])->one();
                    $friendModel = new Friends();
                    $friendModel->updateSchool($uinfo->user_id);
                    $uinfo->school = $school_name;
                    $uinfo->school_id = $school;
                    $uinfo->edu = $edu;
                    $uinfo->school_time = $school_time;
                    $uinfo->identity = $users->identity;

                    //$upacc = $this->updateAccount($uinfo);
                    $upacc = true;
                    if ($upacc) {
                        $transaction->commit();
                        $token_id = isset($_COOKIE['PHPSESSID']) ? $_COOKIE['PHPSESSID'] : '';
                        if (empty($token_id)) {
                            $userpass = User_password::find()->select('device_tokens')->where(['user_id' => $uinfo->user_id])->one();
                            $token_id = !empty($userpass->device_tokens) ? $userpass->device_tokens : rand(100000000, 999999999);
                        }
                        $params = array(
                            'account_name' => $users->realname,
                            'mobile' => $uinfo->mobile,
                            'id_number' => $users->identity,
                            'ext_school' => $school_name,
                            'ext_diploma' => $ext_diploma,
                            'ext_start_year' => $school_time,
                            'seq_id' => date('YmdHis') . $uinfo->user_id,
                            'ext_birth_year' => $birthday_year,
                            'token_id' => $token_id,
                            'ip_address' => Yii::$app->request->getUserIP(),
                            'type' => 2,
                        );
                        $api = new Apihttp();
                        $result_student = $api->riskLoanValid($params);
                        $fraudmetrix = new Fraudmetrix_return_info();
                        $fraudmetrix->CreateFraudmetrix($result_student, $users->user_id);
                        if ($result_student->rsp_code == '0000') {
                            $final_score = trim($result_student->finalScore);
                            $final_result = trim($result_student->result);
                            if (isset($final_score)) {
                                if ($final_result == 'Reject') {
                                    $ret_status = $users->setBlack();
                                    $sql_score = "update " . User::tableName() . " set final_score='$final_score' where user_id=" . $uinfo['user_id'];
                                } else {
                                    if (($final_score >= 60) && ($final_score < 80)) {
                                        $sql_score = "update " . User::tableName() . " set status=4,final_score='$final_score' where user_id=" . $uinfo['user_id'];
                                    } else if ($final_score >= 80) {
                                        $sql_score = "update " . User::tableName() . " set final_score='$final_score' where user_id=" . $uinfo['user_id'];
                                    } else {
                                        $sql_score = "update " . User::tableName() . " set final_score='$final_score' where user_id=" . $uinfo['user_id'];
                                    }
                                }
                                $ret_score = Yii::$app->db->createCommand($sql_score)->execute();
                            }
                        }


                        $resultArr = array('ret' => '0', 'url' => '');
                        echo json_encode($resultArr);
                        exit;
                    } else {
                        $transaction->rollBack();
                        $resultArr = array('ret' => '1', 'url' => '');
                        echo json_encode($resultArr);
                        exit;
                    }
                } else {
                    $resultArr = array('ret' => '1', 'url' => '');
                    echo json_encode($resultArr);
                    exit;
                }
            } else {
                //学籍验证没有通过
                $openid = $this->getVal('openid');
                if (empty($openid)) {
                    $resultArr = array('ret' => '11', 'url' => '');
                    echo json_encode($resultArr);
                    exit;
                }
                $sql = "update " . User::tableName() . " set school_valid=3 where user_type=1 and openid='$openid'";
                $ret = Yii::$app->db->createCommand($sql)->execute();
                $resultArr = array('ret' => '1', 'url' => '');
                echo json_encode($resultArr);
                exit;
            }
        }
    }

    //上班族公司信息完善
    public function actionCompany() {
        $this->layout = "data";
        $this->getView()->title = "行业信息";
        //记录来源地址
        $info_fromurl = Yii::$app->request->get('url');
        if (isset($info_fromurl)) {
            $info_fromurl = urldecode($info_fromurl);
            if ($info_fromurl) {
                $this->setVal('info_fromurl', $info_fromurl);
            }
        }
        $from = isset($_GET['from']) ? $_GET['from'] : '';
        $user_id = Yii::$app->request->get('user_id');
        if (!empty($user_id)) {
            $users = User::find()->where(['user_id' => $user_id])->one();
        } else {
            $openid = $this->getVal('openid');
            $users = User::find()->where(['openid' => $openid])->one();
        }
        if (($users->identity_valid != 2 || $users->identity_valid != 4) && empty($users->realname)) {
            return $this->redirect('/dev/reg/personals?user_id=' . $users->user_id);
        }
        $jsinfo = $this->getWxParam();
        $nextPage = $this->getVal('info_fromurl');
        if ($nextPage == '/dev/account/peral?user_id=' . $users->user_id) {
            $next = 0;
        } else {
            $next = 1;
        }
        $list = Areas::getAllAreas();
        $industry = Keywords::getIndustry();
        $profession = Keywords::getProfession();
        $position = Keywords::getPosition();
        $user_extend = $users->extend;
        return $this->render('company', [
                    'jsinfo' => $jsinfo,
                    'from' => $from,
                    'users' => $users,
                    'next' => $next,
                    'industry' => $industry,
                    'profession' => $profession,
                    'position' => $position,
                    'list' => $list,
                    'user_extend' => $user_extend,
        ]);
    }

    //上班族信息修改
    public function actionShmodifytow() {
        $this->getView()->title = "行业信息";
        //记录来源地址
        if (isset($_GET['url'])) {
            $redirUrl = urldecode($_GET['url']);
            if ($redirUrl) {
                $this->setVal('nextPageUrl', $redirUrl);
            }
        }
        $user_id = Yii::$app->request->get('user_id');
        if (!empty($user_id)) {
            $user = User::findOne($user_id);
        } else {
            $openid = $this->getVal('openid');
            $user = User::find()->where(['openid' => $openid])->one();
        }
        $from = isset($_GET['from']) ? $_GET['from'] : '';
        $f = isset($_GET['f']) ? $_GET['f'] : '';
        //获取行业和职位信息
        $indus = Score::find()->where(['type' => 'job'])->all();
        $posi = Score::find()->where(['type' => 'work'])->all();
        $jsinfo = $this->getWxParam();
        return $this->render('shmodifytow', ['indus' => $indus, 'posi' => $posi, 'jsinfo' => $jsinfo, 'from' => $from, 'f' => $f, 'user' => $user]);
    }

    /**
     * 社会人士实名认证
     * @return type
     */
    public function actionPersonals() {
        $this->layout = "inv";
        $this->getView()->title = "个人资料";
        $user_id = Yii::$app->request->get('user_id');
        $info_fromurl = Yii::$app->request->get('url');
        //记录来源地址
        if (isset($info_fromurl)) {
            $info_fromurl = urldecode($info_fromurl);
            if ($info_fromurl) {
                $this->setVal('info_fromurl', $info_fromurl);
            }
        }
        if (!empty($user_id)) {
            $userinfo = User::find()->where(['user_id' => $user_id])->one();
        } else {
            $openid = $this->getVal('openid');
            $userinfo = User::find()->where(['openid' => $openid])->one();
        }
        $identity_valid = 0;
        if (($userinfo->identity_valid == 2 || $userinfo->identity_valid == 4) && !empty($userinfo->realname)) {
            $identity_valid = 1;
        }
        $jsinfo = $this->getWxParam();
        $marriage = Keywords::getMarriage();
        $edu = Keywords::getEdu();
        $list = Areas::getAllAreas();
        $user_extend = User_extend::getUserExtend($userinfo->user_id);
        return $this->render('personals', [
                    'userinfo' => $userinfo,
                    'user_extend' => $user_extend,
                    'marriage' => $marriage,
                    'edu' => $edu,
                    'jsinfo' => $jsinfo,
                    'identity_valid' => $identity_valid,
                    'list' => $list,
        ]);
    }

    public function actionCompanysave() {
        $telephone = $_POST['telephone'];
        $company = $_POST['company'];
        $address = $_POST['address'];
        $industry = $_POST['industry'];
        $email = $_POST['email'];
        $profession = $_POST['profession'];
        $position = $_POST['position'];
        $income = $_POST['income'];
        $district = $_POST['district'];
        $from_url = isset($_POST['from_url']) ? $_POST['from_url'] : '';
        if (!preg_match("/^1(([3578][0-9])|(47))\d{8}$/", $telephone)) {
            if (!preg_match("/^0\d{2,3}\-?\d{7,8}$/", $telephone)) {
                $resultArr = array('ret' => '12', 'url' => '');
                echo json_encode($resultArr);
                exit;
            }
        }
        $user_id = Yii::$app->request->post('user_id');
        if (!empty($user_id)) {
            $user = User::find()->where(['user_id' => $user_id])->one();
            $identity = $user->identity;
        } else {
            $resultArr = array('ret' => '1', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }
        $history = User_history_info::find()->where(['user_id' => $user_id, 'data_type' => 2])->count();
        $mark = $history > 0 ? 1 : 0;

        $transaction = Yii::$app->db->beginTransaction();
        $extend_condition = array(
            'user_id' => $user_id,
            'industry' => $industry,
            'company' => $company,
            'position' => $position,
            'profession' => $profession,
            'telephone' => trim($telephone),
            'email' => trim($email),
            'income' => $income,
            'company_area' => $district,
            'company_address' => trim($address),
        );
        //保存信息
        $user_extend = User_extend::getUserExtend($user->user_id);
        if (empty($user_extend)) {
            $extendModel = new User_extend();
            $extendModel->addRecord($extend_condition);
        } else {
            $user_extend->updateRecord($extend_condition);
        }
        $sql = "update " . User::tableName() . " set telephone='$telephone',company='$company',address='$address' where user_id='$user_id'";
        $ret = Yii::$app->db->createCommand($sql)->execute();

        //判断用户是否在黑名单库中
        $blackinfo = Black_list::find()->where(['cred_no' => $identity])->one();
        if (!empty($blackinfo)) {
            //修改用户的状态
            $ret_status = $user->setBlack();
            if ($ret_status) {
                $transaction->commit();
                $resultArr = array('ret' => '3', 'url' => '');
                echo json_encode($resultArr);
                exit;
            } else {
                $transaction->rollBack();
                $resultArr = array('ret' => '3', 'url' => '');
                echo json_encode($resultArr);
                exit;
            }
        }

        if ($ret >= 0) {
            //取账户信息,重新计算额度
            $uinfo = User::find()->joinWith('account', true, 'LEFT JOIN')->where([User::tableName() . '.user_id' => $user_id])->one();
            $uinfo->industry = 1;
            $uinfo->address = $address;
            $uinfo->identity = $identity;

            $upacc = true;
            if ($upacc) {
                $transaction->commit();
                if ($mark == 0) {
                    $api = new XhhApi();
                    $limit = $api->runDecisions($user, 1);
                    if (!empty($limit)) {
                        $condition = $limit;
                        $condition['old_status'] = $user->status;
                        $user->setBlack();
                        $condition['new_status'] = 5;

                        Logger::errorLog(print_r($condition, true), 'decisionlimit');
                        $event = Register_event::addRecord($user->user_id, $condition);
                        $resultArr = array('ret' => '3', 'url' => '');
                        echo json_encode($resultArr);
                        exit;
                    }
                }
                if (empty($uinfo['pic_identity']) || $user->status == 4) {
                    $nextPage = "/dev/reg/pic?user_id=" . $user_id;
                } else {
                    $nextPage = $this->getVal('info_fromurl');
                    if (empty($nextPage)) {
                        $nextPage = '/dev/account/peral?user_id=' . $user_id;
                    } else {
                        //删除cookie
                        $this->delVal("info_fromurl");
                    }
                }
                $resultArr = array('ret' => '0', 'url' => $nextPage);
                echo json_encode($resultArr);
                exit;
            } else {
                $transaction->rollBack();
                $resultArr = array('ret' => '1', 'url' => '');
                echo json_encode($resultArr);
                exit;
            }
        } else {
            $resultArr = array('ret' => '1', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }
    }

    public function actionShmodifytowsave() {
        $industry = $_POST['industry'];
        $company = $_POST['company'];
        $position = $_POST['position'];
        $from_url = $_POST['from_url'];
        $f_url = $_POST['f_url'];
        $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';
        if (!empty($user_id)) {
            $userinfo = User::find()->where(['user_id' => $user_id])->one();
        } else {
            $openid = $this->getVal('openid');
            $userinfo = User::find()->where(['openid' => $openid])->one();
        }
        $identity = $userinfo->identity;
        $birthday_year = $userinfo->birth_year;
        $realname = $userinfo->realname;
        //判断用户是第一次更新行业信息还是再次更新
        if (!empty($userinfo['realname'])) {
            $update_info_type = 'many';
        } else {
            $update_info_type = 'one';
        }
        $transaction = Yii::$app->db->beginTransaction();
        if ($userinfo->company != '') {
            $history_info = new User_history_info();
            $history_info->user_id = $userinfo->user_id;
            $history_info->user_type = $userinfo->user_type;
            $history_info->data_type = 2;
            $history_info->company_school = $userinfo->company;
            $history_info->industry_edu = $userinfo->industry;
            $history_info->position_schooltime = $userinfo->position;
            $history_info->create_time = date('Y-m-d H:i:s');
            $history_info->save();
        }
        $sql = "update " . User::tableName() . " set industry='$industry',company='$company',position='$position' where user_type=2 and openid='$openid'";
        $ret = Yii::$app->db->createCommand($sql)->execute();

        //判断用户是否在黑名单库中
        $blackinfo = Black_list::find()->where(['cred_no' => $identity])->one();
        if (!empty($blackinfo)) {
            //修改用户的状态
            $ret_status = $userinfo->setBlack();
            if ($ret_status) {
                $transaction->commit();
                $resultArr = array('ret' => '3', 'url' => '');
                echo json_encode($resultArr);
                exit;
            } else {
                $transaction->rollBack();
                $resultArr = array('ret' => '3', 'url' => '');
                echo json_encode($resultArr);
                exit;
            }
        }

        if ($ret) {
            //取账户信息,重新计算额度
            $uinfo = User::find()->joinWith('account', true, 'LEFT JOIN')->where(['openid' => $openid])->one();
            $upacc = true;
            if ($upacc) {
                $transaction->commit();
                $token_id = isset($_COOKIE['PHPSESSID']) ? $_COOKIE['PHPSESSID'] : '';
                if (empty($token_id)) {
                    $userpass = User_password::find()->select('device_tokens')->where(['user_id' => $uinfo->user_id])->one();
                    $token_id = !empty($userpass->device_tokens) ? $userpass->device_tokens : rand(100000000, 999999999);
                }
                $params = array(
                    'account_name' => $uinfo->realname,
                    'mobile' => $uinfo->mobile,
                    'id_number' => $uinfo->identity,
                    'organization' => $company,
                    'ext_position' => '',
                    'seq_id' => date('YmdHis') . $uinfo->user_id,
                    'ext_birth_year' => $uinfo->birth_year,
                    'token_id' => $token_id,
                    'ip_address' => Yii::$app->request->getUserIP(),
                    'type' => 2,
                );
                $api = new Apihttp();
                $result_company = $api->riskLoanValid($params);
                $fraudmetrix = new Fraudmetrix_return_info();
                $fraudmetrix->CreateFraudmetrix($result_company, $userinfo->user_id);
                if ($result_company->rsp_code == '0000') {
                    $final_score = trim($result_company->finalScore);
                    $final_result = trim($result_company->result);
                    if (isset($final_score)) {
                        if ($final_result == 'Reject') {
                            $ret_status = $userinfo->setBlack();
                            $sql_score = "update " . User::tableName() . " set final_score='$final_score' where user_id=" . $uinfo['user_id'];
                        } else {
                            if (($final_score >= 60) && ($final_score < 80)) {
                                if ($uinfo['status'] != 3) {
                                    $sql_score = "update " . User::tableName() . " set status=4,final_score='$final_score' where user_id=" . $uinfo['user_id'];
                                } else {
                                    $sql_score = "update " . User::tableName() . " set final_score='$final_score' where user_id=" . $uinfo['user_id'];
                                }
                            } else if ($final_score >= 80) {
                                $ret_status = $userinfo->setBlack();
                                $sql_score = "update " . User::tableName() . " set final_score='$final_score' where user_id=" . $uinfo['user_id'];
                            } else {
                                $sql_score = "update " . User::tableName() . " set final_score='$final_score' where user_id=" . $uinfo['user_id'];
                            }
                        }
                        $ret_score = Yii::$app->db->createCommand($sql_score)->execute();
                    }
                }

                //验证跳转页面
                $nextPage = $this->getVal('nextPageUrl');
                if (empty($nextPage)) {
                    $nextPage = "/dev/reg/shool?user_id=" . $user_id . "&f=$f_url";
                } else {
                    if ((empty($from_url) && $nextPage == '/dev/account/remain') || (empty($from_url) && $nextPage == '/dev/account/info')) {
                        if (empty($uinfo['school']) && empty($uinfo['pic_identity'])) {
                            $nextPage = "/dev/reg/shool?user_id=" . $user_id . "&f=$f_url";
                        } else if (empty($uinfo['school']) && !empty($uinfo['pic_identity'])) {
                            $nextPage = "/dev/reg/shool?user_id=" . $user_id . "&f=$f_url";
                        } else if (!empty($uinfo['school']) && empty($uinfo['pic_identity'])) {
                            $nextPage = "/dev/reg/pic?user_id=" . $user_id . "&f=$f_url";
                        } else {
                            $nextPage = "/dev/bank?f=$f_url";
                        }
                    }
                }
                //删除cookie
                $this->delVal("nextPageUrl");
                $resultArr = array('ret' => '0', 'url' => $nextPage);
                echo json_encode($resultArr);
                exit;
            } else {
                $transaction->rollBack();
                $resultArr = array('ret' => '1', 'url' => '');
                echo json_encode($resultArr);
                exit;
            }
        } else {
            $nextPage = $this->getVal('nextPageUrl');
            $resultArr = array('ret' => '1', 'url' => $nextPage);
            echo json_encode($resultArr);
            exit;
        }
    }

    //上班族学历信息
    public function actionShool() {
        $this->getView()->title = "学籍信息";
        $this->layout = 'data';
        //记录来源地址
        $info_fromurl = Yii::$app->request->get('url');
        if (isset($info_fromurl)) {
            $info_fromurl = urldecode($info_fromurl);
            if ($info_fromurl) {
                $this->setVal('info_fromurl', $info_fromurl);
            }
        }
        $user_id = Yii::$app->request->get('user_id');
        if (!empty($user_id)) {
            $userinfo = User::find()->where(['user_id' => $user_id])->one();
        } else {
            $openid = $this->getVal('openid');
            $userinfo = User::find()->where(['openid' => $openid])->one();
        }
        if (empty($userinfo->realname) || empty($userinfo->identity)) {
            return $this->redirect('/dev/reg/personals?user_id=' . $userinfo->user_id);
        }
        ///操作完成后进行跳转
        $school = School::find()->all();
        //////////////////
        $jsinfo = $this->getWxParam();
        return $this->render('shool', ['school' => $school, 'userinfo' => $userinfo, 'jsinfo' => $jsinfo]);
    }

    public function actionShthreesave() {
        $school = $_POST['school'];
        $school_name = $_POST['school_name'];
        $edu = $_POST['edu'];
        $school_time = $_POST['school_time'];
        $openid = $this->getVal('openid');
        $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';
        if (empty($openid)) {
            $resultArr = array('ret' => '1', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }
        if (!empty($user_id)) {
            $userinfo = User::find()->where(['user_id' => $user_id])->one();
        } else {
            $userinfo = User::find()->where(['openid' => $openid])->one();
        }
        //判断用户是第一次更新行业信息还是再次更新
        if (!empty($userinfo['school']) || !empty($userinfo['edu']) || !empty($userinfo['school_time'])) {
            $update_info_type = 'many';
        } else {
            $update_info_type = 'one';
        }
        $transaction = Yii::$app->db->beginTransaction();
        if (!empty($userinfo['school']) || !empty($userinfo['edu']) || !empty($userinfo['school_time'])) {
            $history_info = new User_history_info();
            $history_info->user_id = $userinfo->user_id;
            $history_info->user_type = $userinfo->user_type;
            $history_info->data_type = 1;
            $history_info->company_school = !empty($userinfo->school) ? $userinfo->school : ' ';
            $history_info->industry_edu = !empty($userinfo->edu) ? $userinfo->edu : ' ';
            $history_info->position_schooltime = !empty($userinfo->school_time) ? $userinfo->school_time : ' ';
            $history_info->create_time = date('Y-m-d H:i:s');
            $history_info->save();
        }
        $sql = "update " . User::tableName() . " set school='$school_name',school_id='$school',edu='$edu',school_time='$school_time' where openid='$openid'";
        $ret = Yii::$app->db->createCommand($sql)->execute();

        if ($ret >= 0) {
            $userinfo = User::find()->where(['openid' => $openid])->one();
            $friendModel = new Friends();
            $friendModel->updateSchool($userinfo->user_id);

            $acc_ret = true;
            if ($acc_ret) {
                $transaction->commit();
                if (empty($userinfo['pic_identity']) || $userinfo->status == 4) {
                    $nextPage = "/dev/reg/pic?user_id=" . $userinfo->user_id;
                } else {
                    $nextPage = $this->getVal('info_fromurl');
                    if (empty($nextPage)) {
                        $nextPage = '/dev/account/peral?user_id=' . $userinfo->user_id;
                    } else {
                        //删除cookie
                        $this->delVal("info_fromurl");
                    }
                }
                $resultArr = array('ret' => '0', 'url' => $nextPage);
                echo json_encode($resultArr);
                exit;
            } else {
                $transaction->rollBack();
                $resultArr = array('ret' => '1', 'url' => '');
                echo json_encode($resultArr);
                exit;
            }
        } else {
            $transaction->rollBack();
            $resultArr = array('ret' => '10', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }
    }

    //自拍照上传
    public function actionPic() {
        $this->getView()->title = "上传证件照";
        //记录来源地址
        $info_fromurl = Yii::$app->request->get('url');
        if (isset($info_fromurl)) {
            $info_fromurl = urldecode($info_fromurl);
            if ($info_fromurl) {
                $this->setVal('info_fromurl', $info_fromurl);
            }
        }
        $user_id = Yii::$app->request->get('user_id');
        if (!empty($user_id)) {
            $userinfo = User::find()->where(['user_id' => $user_id])->one();
        } else {
            $openid = $this->getVal('openid');
            $userinfo = User::find()->where(['openid' => $openid])->one();
        }
        $user_id = $userinfo['user_id'];
        if ($userinfo->status == 5) {
            //跳转到黑名单错误提示页面
            return $this->redirect('/dev/account/black');
        } else if ($userinfo->status == 2) {
            return $this->redirect('/dev/account');
        }
        if (empty($userinfo->realname) || empty($userinfo->identity)) {
            return $this->redirect('/dev/reg/personals?user_id=' . $userinfo['user_id']);
        }
        if ($userinfo->status == 3) {
            return $this->redirect('/dev/account/peral?user_id=' . $userinfo['user_id']);
        }
        //随即获取牌照类型
        $type_array = array(1, 2, 4, 5, 6, 8);
        $type_id = $type_array[rand(0, 5)];
        $pictype = Pictype::find()->where(['id' => $type_id])->one();
        //获取微信jssdk参数
        $jssdkParam = $this->getWxParam();

        $pic_identity = "/yiyiyuan/identity/" . date("Y/m/d/") . "pic_weixin_{$user_id}.jpg";
//      $miyao = array(
//          'encrypt' => \app\commonapi\ImageHandler::encryptKey($user_id, 'identity'),
//      );
//      \Logger::errorLog(print_r($miyao, true), 'encrypt');
        return $this->render('pic', [
                    'userinfo' => $userinfo,
                    'pictype' => $pictype,
                    'jssdkparam' => $jssdkParam,
                    'encrypt' => ImageHandler::encryptKey($user_id, 'identity'),
                    'access_token' => $this->getAccessToken(),
                    'pic_identity' => $pic_identity,
        ]);
    }

    public function actionPicsave() {
        if ($_POST) {
            $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';
            $openid = $this->getVal('openid');
            if (empty($openid) && empty($user_id)) {
                return $this->redirect('/dev/reg/login');
            }
            if (!empty($user_id)) {
                $userinfo = User::find()->where(['user_id' => $user_id])->one();
            } else {
                $userinfo = User::find()->where(['openid' => $openid])->one();
            }
            //判断用户是否是黑名单用户
            if ($userinfo->status == 5) {
                //跳转到黑名单错误提示页面
                return $this->redirect('/dev/account/black');
            }
            $pic_type = $_POST['pic_type'];
            $serverid = $_POST['serverid'];
            //更新用户照片信息
            $filename = $_POST['pic_identity'];
            if ($userinfo->pic_identity == '') {
                $pic_up_time = date('Y-m-d H:i:s', time());
                if ($userinfo['final_score'] >= 60) {
                    $sql = "update " . User::tableName() . " set pic_identity='" . $filename . "',pic_type=" . $pic_type . ",status=4,serverid='" . $serverid . "',pic_up_time = '" . $pic_up_time . "' where user_id=" . $userinfo->user_id;
                } else {
                    $sql = "update " . User::tableName() . " set pic_identity='" . $filename . "',pic_type=" . $pic_type . ",status=2,serverid='" . $serverid . "',pic_up_time = '" . $pic_up_time . "' where user_id=" . $userinfo->user_id;
                }
                Logger::errorLog($sql, 'sql');
                $ret = Yii::$app->db->createCommand($sql)->execute();
                //提额度100
                if ($ret) {
                    $acc_sql = "update " . Account::tableName() . " set remain_amount=remain_amount-100,amount=amount+100,current_amount=current_amount+100 where user_id=" . $userinfo->user_id;
                    $acc_ret = Yii::$app->db->createCommand($acc_sql)->execute();

                    //记录提额的日志
                    $amount_date = array(
                        'type' => 14,
                        'user_id' => $userinfo->user_id,
                        'amount' => 100
                    );
                    $user_amount = new User_amount_list();
                    $user_amount->CreateAmount($amount_date);
                }
            } else {
                $pic_up_time = date('Y-m-d H:i:s', time());
                if ($userinfo['final_score'] >= 60) {
                    $sql = "update " . User::tableName() . " set pic_identity='" . $filename . "',pic_type=" . $pic_type . ",status=4,serverid='" . $serverid . "',pic_up_time = '" . $pic_up_time . "' where user_id=" . $userinfo->user_id;
                } else {
                    $sql = "update " . User::tableName() . " set pic_identity='" . $filename . "',pic_type=" . $pic_type . ",status=2,serverid='" . $serverid . "',pic_up_time = '" . $pic_up_time . "' where user_id=" . $userinfo->user_id;
                }
                Logger::errorLog($sql, 'sql');
                $ret = Yii::$app->db->createCommand($sql)->execute();
            }
            if ($ret) {
                //验证跳转页面
                $nextPage = $this->getVal('info_fromurl');
                if (empty($nextPage)) {
                    return $this->redirect('/dev/account/peral?user_id=' . $userinfo['user_id']);
                } else {
                    //删除cookie
                    $this->delVal("info_fromurl");
                    return $this->redirect($nextPage);
                }
            } else {
                return $this->redirect('/dev/reg/pic?user_id=' . $userinfo['user_id']);
            }
        } else {
            return $this->redirect('/dev/reg/login');
        }
    }

    public function downloadWeixinFile($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_NOBODY, 0);    //只取body头
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $package = curl_exec($ch);
        $httpinfo = curl_getinfo($ch);
        curl_close($ch);
        $imageAll = array_merge(array('header' => $httpinfo), array('body' => $package));
        return $imageAll;
    }

    public function saveWeixinFile($filename, $filecontent) {
        $local_file = fopen($filename, 'w');
        if (false !== $local_file) {
            if (false !== fwrite($local_file, $filecontent)) {
                fclose($local_file);
            }
        }
    }

    //发送验证码
    public function actionOnesend() {
        $mobile = $_POST['mobile'];

        //判断手机是否注册
        $ret = User::find()->where(['mobile' => $mobile])->one();
        if (isset($ret->user_id)) {
            $resultArr = array('ret' => '1', 'url' => '');
            echo json_encode($resultArr);
            exit; //已有用户绑定
        }

        //一天只能发送6条短信
        $begintime = date('Y-m-d' . ' 00:00:00');
        $endtime = date('Y-m-d' . ' 23:59:59');
        $sms_count = Sms::find()->where("recive_mobile='$mobile' and sms_type=1 and create_time >= '$begintime' and create_time <= '$endtime'")->count();
        if ($sms_count >= 6) {
            $resultArr = array('ret' => '2', 'url' => '');
            echo json_encode($resultArr);
            exit; //已有用户绑定
        }
        $api = new ApiSms();
        $sendRet = $api->sendReg($mobile, 1);

        $resultArr = array('ret' => '0', 'url' => '');
        echo json_encode($resultArr);
        exit;
    }

    //发送验证码(登录时)
    public function actionLoginsend() {
        $mobile = Yii::$app->request->post('mobile', 0);
        $pic_num = Yii::$app->request->post('pic_num', 0);
        $mark = strval(Yii::$app->request->post('mark', 0));

        //一天只能发送6条短信
        $begintime = date('Y-m-d' . ' 00:00:00');
        $endtime = date('Y-m-d' . ' 23:59:59');
        $sms_count = Sms::find()->where("recive_mobile='$mobile' and sms_type=2 and create_time >= '$begintime' and create_time <= '$endtime'")->count();
        if ($sms_count >= 6) {//超过6次限制
            $resultArr = array('ret' => '2', 'url' => '');
            echo json_encode($resultArr);
            exit;
        } else if ($sms_count > 0 && $mark == 0) {//已经发送过一次，需要显示图形验证码
            $resultArr = array('ret' => '3', 'url' => '');
            echo json_encode($resultArr);
            exit; //已有用户绑定
        }
        if ($mark == 1) {//提交数据中有图形验证码，需要比对
            if (empty($pic_num) || strtolower($pic_num) != $this->getVal('code_char')) {
                $resultArr = array('ret' => '4', 'url' => '');
                echo json_encode($resultArr);
                exit; //已有用户绑定
            }
        }

        $api = new ApiSms();
        $sendRet = $api->sendReg($mobile, 2);

        $resultArr = array('ret' => '0', 'url' => '');
        echo json_encode($resultArr);
        exit;
    }

    //判断手机是否注册
    protected function VerifyMobile($mobile) {
        $user = User::find()->where(['mobile' => $mobile])->one();

        if (isset($user->user_id)) {
            return true;
        } else {
            return false;
        }
    }

    //判断验证码是否正确
    protected function VerifyCode($mobile, $code) {
        if (empty($mobile) || empty($code)) {
            return false;
        }

        $result = Sms::find()->where(['recive_mobile' => $mobile, 'code' => $code])->orderBy('create_time desc')->one();

        if (isset($result->id)) {
            $time = strtotime($result['create_time']);
            $nowtime = time();
            $min = ceil(($nowtime - $time) / (60 * 60 * 12));
            if ($min > 12) {//12小时内有效
                return false;
            }
            return true;
        } else {
            return false;
        }
//     	//获取memcache里的房源信息
//     	$usercode = Yii::$app->memcache->get($key);
//     	if(!empty($usercode) && $code == $usercode){
//     		return true;
//     	}else {
//     		return false;
//     	}  
    }

    public function actionContacts() {
        $this->getView()->title = '联系人';
        $this->layout = "data";
        //记录来源地址
        if (isset($_GET['url'])) {
            $redirUrl = urldecode($_GET['url']);
            if ($redirUrl) {
                $this->setVal('contactnextPage', $redirUrl);
            }
        }
        $user_id = Yii::$app->request->get('user_id');
        if (!empty($user_id)) {
            $userinfo = User::find()->where(['user_id' => $user_id])->one();
        } else {
            $openid = $this->getVal('openid');
            $userinfo = User::find()->where(['openid' => $openid])->one();
        }
        $favorite = new Favorite_contacts();
        $fav = $favorite->getFavoriteByUserId($userinfo->user_id);
        $contacts = !empty($fav) ? 1 : 2;
        $jsinfo = $this->getWxParam();
        $result = array(
            'user_id' => $user_id,
            'jsinfo' => $jsinfo,
            'contacts' => $contacts,
        );
        $result['fav'] = $fav;

        return $this->render('contacts', $result);
    }

    public function actionSavecontacts() {
        $data = Yii::$app->request->post();
        $nextPage = $this->getVal('contactnextPage');
        if (empty($nextPage)) {
            $nextPage = "/dev/account/peral";
        }
        if (!empty($data['user_id'])) {
            $contacts_name = trim($data['contacts_name']); //py
            $relation_common = trim($data['relation_common']); //pengy
            $mobile = trim($data['mobile']); //py
            $relatives_name = trim($data['relatives_name']); //配偶
            $relation_family = trim($data['relation_family']);
            $phone = trim($data['phone']);
            if (strlen($contacts_name) == 0 || strlen($relatives_name) == 0) {
                echo json_encode(array('code' => 2, 'url' => $nextPage)); //传参格式不正确
                exit;
            }
            if (!preg_match("/^((1(([3578][0-9])|(47)))\d{8})|((0\d{2,3})\-?\d{7,8}(\-?\d{4})?)$/", $mobile) || !preg_match("/^((1(([3578][0-9])|(47)))\d{8})|((0\d{2,3})\-?\d{7,8}(\-?\d{4})?)$/", $phone)) {
                echo json_encode(array('code' => 2, 'url' => $nextPage)); //传参格式不正确
                exit;
            }
            $favorite = new Favorite_contacts();
            $contacts_flows = new Contacts_flows();
            $fav = $favorite->getFavoriteByUserId($data['user_id']);
            //新加联系人记录
            $condition = array(
                'user_id' => $data['user_id'],
                'contacts_name' => $contacts_name,
                'relation_common' => $relation_common,
                'mobile' => $mobile,
                'relatives_name' => $relatives_name,
                'relation_family' => $relation_family,
                'phone' => $phone
            );
            if (!empty($fav)) {
                if ($fav->contacts_name == $contacts_name && $fav->relatives_name == $relatives_name && $fav->mobile == $mobile && $fav->phone == $phone && $fav->relation_common == $relation_common && $fav->relation_family == $relation_family) {
                    echo json_encode(array('code' => 4, 'url' => $nextPage)); //和原纪录一样
                    exit;
                }
                $result = $fav->updateFavoriteContacts($condition);
                $ret = $contacts_flows->addContactsFlows($condition);
                if ($result && $ret) {
                    $this->delVal('contactnextPage');
                    echo json_encode(array('code' => 0, 'url' => $nextPage)); //传参格式不正确
                    exit;
                } else {
                    echo json_encode(array('code' => 3, 'url' => $nextPage)); //sql执行错误
                    exit;
                }
            } else {
                $result = $favorite->addFavoriteContacts($condition);
                $ret = $contacts_flows->addContactsFlows($condition);
                if ($result && $ret) {
                    $this->delVal('contactnextPage');
                    echo json_encode(array('code' => 0, 'url' => $nextPage)); //传参格式不正确
                    exit;
                } else {
                    echo json_encode(array('code' => 3, 'url' => $nextPage)); //sql执行错误
                    exit;
                }
            }
        } else {
            echo json_encode(array('code' => 1, 'url' => $nextPage)); //传参错误，没有user_id
            exit;
        }
    }

    public function actionError() {
        return $this->render('error');
    }

}
