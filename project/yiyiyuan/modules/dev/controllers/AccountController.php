<?php

namespace app\modules\dev\controllers;

use app\commands\SubController;
use app\commonapi\apiInterface\Remit;
use app\commonapi\Common;
use app\commonapi\Crypt3Des;
use app\commonapi\Http;
use app\commonapi\Logger;
use app\models\dev\Account;
use app\models\dev\Account_settlement;
use app\models\dev\Coupon_list;
use app\models\dev\Favorite_contacts;
use app\models\dev\Friends;
use app\models\dev\Juxinli;
use app\models\dev\Red_packets_receive;
use app\models\dev\Scan_times;
use app\models\dev\School;
use app\models\dev\Score;
use app\models\dev\Standard_account;
use app\models\dev\Standard_coupon_list;
use app\models\dev\Standard_information;
use app\models\dev\Standard_statistics;
use app\models\dev\User;
use app\models\dev\User_amount_list;
use app\models\dev\User_bank;
use app\models\dev\User_credit_income_record;
use app\models\dev\User_credit_invest;
use app\models\dev\User_credit_reback;
use app\models\dev\User_credit_stat;
use app\models\dev\User_invest;
use app\models\dev\User_loan;
use app\models\dev\User_refresh;
use app\models\dev\User_remit_list;
use app\models\dev\Userwx;
use Yii;

class AccountController extends SubController {

    public $layout = 'main';
    public $enableCsrfValidation = false;

    public function actionIndex() {
        $this->getView()->title = "账户";
// $this->layout = 'new_invest';
        $this->layout = 'inv';
        $openid = $this->getVal('openid');
        $mobile = $this->getVal('mobile');
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
//是否关注先花一亿元
        if (isset($_GET['atten']) && $_GET['atten'] == '1') {
            $isAtten = $this->isAtten($openid);
            if (!$isAtten) {
                return $this->redirect('http://mp.weixin.qq.com/s?__biz=MzA4OTM2NTU5NQ==&mid=203536992&idx=1&sn=682dd78456a5d0cd8e843b0a14243389#rd');
            }
        }

//判断openid和mobile
        if (empty($openid) || empty($mobile)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/one&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }

//验证用户是否手机验证
        $ischeckmobile = $this->isCheckMobile($openid);
        if (!$ischeckmobile) {
            $url = Yii::$app->request->getUrl(); //当前访问url
            $url1 = urlencode($url);
            return $this->redirect('/dev/reg/login?url=' . $url1);
        }
        $jsinfo = $this->getWxParam();
//获取用户账户信息
        $userinfo = User::find()->joinWith('userwx', true, 'LEFT JOIN')->joinWith('account', true, 'LEFT JOIN')->where([User::tableName() . '.openid' => $openid])->one();
        /*         * *************记录访问日志beigin******************* */
        $ip = Common::get_client_ip();
        $result_log = Common::saveLog('account', 'account_menu', $ip, 'weixin', $userinfo->user_id);
        /*         * *************记录访问日志end******************* */
// $status= Scan_times::find()->where(['type' => 3, 'mobile' => $userinfo->mobile])->count();
// echo $status;exit;
        if ($userinfo->user_type == 4) {
            $url = Yii::$app->request->getUrl(); //当前访问url
            $url1 = urlencode($url);
            return $this->redirect('/dev/guarantoraccount/index?url=' . $url1);
        } else if ($userinfo->user_type == 0) {
            $url = Yii::$app->request->getUrl(); //当前访问url
            $url1 = urlencode($url);
            return $this->redirect('/dev/reg/sfen?url=' . $url1);
        }
        $bank = User_bank::find()->where(['user_id' => $userinfo->user_id, 'status' => 1])->count();
//是否显示弹出层
        $scan_times = Scan_times::find()->where(['mobile' => $userinfo->mobile])->all();
        $needType = array('4', '5', '3'); //需要弹出提示的页面，可以调整顺序
        $oldType = array();
        $showType = array();
        if (!empty($scan_times)) {
            foreach ($scan_times as $key => $val) {
                if (in_array($val['type'], $needType)) {
                    $oldType[] = $val['type'];
                }
            }
        }
        if (!empty($needType)) {
            foreach ($needType as $kk) {
                if (!in_array($kk, $oldType)) {
                    $showType[] = $kk;
                    $scan = new Scan_times();
                    $scan->mobile = $userinfo->mobile;
                    $scan->type = $kk;
                    $scan->create_time = date('Y-m-d H:i:s');
                    $scan->save();
                }
            }
        }

        $now_time = date('Y-m-d H:i:s');
//获取优惠券张数=========================================
        $couponcount = Coupon_list::find()->where(['status' => 1, 'mobile' => $userinfo->mobile])->andFilterWhere(['>=', 'end_date', $now_time])->count();        
//获取收益总额===========================================
//投资好友收益
        $incomefr = Account::find()->select(array('total_income'))->where(['user_id' => $userinfo->user_id])->one();
//先花宝收益
        $incomexh = User_credit_stat::find()->select(array('total_income'))->where(['user_id' => $userinfo->user_id])->one();
//理财收益
        $incomest = Standard_account::find()->select(array('total_historyinterest'))->where(['user_id' => $userinfo->user_id])->one();
//历史总收益
        $totalIncome = floatval($incomefr['total_income']) + floatval($incomexh['total_income']) + floatval($incomest['total_historyinterest']);
        $totalIncome = sprintf('%.2f', $totalIncome);
//已提现收益
        $outsql = "select sum(amount) as outincome from " . Account_settlement::tableName() . " where user_id = " . $userinfo->user_id . " and status='SUCCESS' and (type=2 or type=5)";
        $outIncome = Yii::$app->db->createCommand($outsql)->queryOne();
        $outIncome = sprintf('%.2f', $outIncome['outincome']);
//担保额度===============================================
        if(!isset($userinfo->account->real_guarantee_amount) && empty($userinfo->account->real_guarantee_amount)){
            $gua_num = '';
        }else{
            $gua_num = sprintf('%.2f', ($userinfo->account->real_guarantee_amount));
        }
//邀请熟人分享页面
        $time = time();
        $shareurl = Yii::$app->request->hostInfo . "/dev/share/myinvite?u=" . $userinfo->user_id . "&t=" . $time . "&s=" . md5($time . $userinfo->user_id) . "&from=account";
        return $this->render('index', [
                    'userinfo' => $userinfo,
                    'jsinfo' => $jsinfo,
                    'shareurl' => $shareurl,
                    'bank' => $bank,
                    'couponcount' => $couponcount,
                    'totalIncome' => ($totalIncome - $outIncome),
                    'totalGuarant' => $gua_num,
                    'needType' => json_encode($showType),
        ]);
    }

    public function actionInfo() {
        $this->getView()->title = "基本信息";
        $openid = $this->getVal('openid');

//判断openid和mobile
        if (empty($openid)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/one&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $jsinfo = $this->getWxParam();
//验证用户是否手机验证
        $ischeckmobile = $this->isCheckMobile($openid);
        if (!$ischeckmobile) {
            $url = Yii::$app->request->getUrl(); //当前访问url
            $url = urlencode($url);
            return $this->redirect('/dev/reg/login?url=' . $url);
        }

//获取行业、职位信息
        $industry = Score::find()->where(['type' => 'job'])->all();
        $indus = array();
        foreach ($industry as $val) {
            $indus[$val['number']] = $val['name'];
        }
        $job = Score::find()->where(['type' => 'work'])->all();
        $posi = array();
        foreach ($job as $val) {
            $posi[$val['number']] = $val['name'];
        }
//获取用户账户信息
        $userinfo = User::find()->joinWith('userwx', true, 'LEFT JOIN')->where([User::tableName() . '.openid' => $openid])->one();
        $userbank = User_bank::find()->where(['user_id' => $userinfo->user_id])->one();
        return $this->render('info', ['userinfo' => $userinfo, 'userbank' => $userbank, 'indus' => $indus, 'posi' => $posi, 'jsinfo' => $jsinfo]);
    }

    public function actionStandardlist() {
        $this->layout = 'new_invest';
        $user_id = Yii::$app->request->get('user_id');
//查询自己购买的标的
        $standard_list = Standard_statistics::find()->joinWith('information', true, 'LEFT JOIN')->where([Standard_statistics::tableName() . '.user_id' => $user_id, Standard_statistics::tableName() . '.user_type' => 'NORMAL'])->orderBy(Standard_statistics::tableName() . ".create_time desc")->all();
        $jsinfo = $this->getWxParam();
        $this->getView()->title = "投资列表";
        return $this->render('standardlist', [
                    'user_id' => $user_id,
                    'standard_list' => $standard_list,
                    'jsinfo' => $jsinfo
        ]);
    }

    public function actionXhblist() {
        $this->layout = 'new_invest';
        $user_id = Yii::$app->request->get('user_id');
//查询用户购买先花宝的记录
        $sql1 = "select type,amount,create_time from " . User_credit_invest::tableName() . " where user_id=" . $user_id;
        $sql2 = "select type,amount,create_time from " . User_credit_reback::tableName() . " where user_id=" . $user_id;
        $sql = $sql1 . " union " . $sql2 . " order by create_time desc";
        $user_credit_invest = Yii::$app->db->createCommand($sql)->queryAll();
//$user_credit_invest = User_credit_invest::find()->where(['user_id'=>$user_id])->all();
        $jsinfo = $this->getWxParam();
        $this->getView()->title = "投资列表";
        return $this->render('xhblist', [
                    'user_id' => $user_id,
                    'user_credit_invest' => $user_credit_invest,
                    'jsinfo' => $jsinfo
        ]);
    }

    public function actionInvestlist() {
        $this->layout = 'new_invest';
        $this->getView()->title = "投资列表";
        $user_id = Yii::$app->request->get('user_id');
        $jsinfo = $this->getWxParam();
//已收益金额
        $accountinfo = Account::find()->select(array('total_income'))->where(['user_id' => $user_id])->one();
//$sql = "select u.realname,u.user_id,i.amount,h.days,from_unixtime(unix_timestamp(h.start_date), '%Y-%m-%d') as start_date,h.status,h.start_date as begin_date,i.create_time,i.invest_id from yi_user_invest as i,yi_user as u,yi_user_loan as h where i.user_id=" . $user_id . " and i.loan_user_id = u.user_id and h.user_id= " . $user_id . " and i.loan_user_id = h.user_id order by i.create_time desc";
//$investlist = Yii::$app->db->createCommand($sql)->queryAll();
        $investlist = User_invest::find()->joinWith('loan')->joinWith('user')->where([User_invest::tableName() . '.user_id' => $user_id])->orderBy('create_time desc')->asarray()->all();
//print_r($investlist);exit;
        foreach ($investlist as $key => $investinfo) {
            if (empty($investinfo['loan'])) {
                unset($investlist[$key]);
                continue;
            }
            $investlist[$key]['loan']['status'] = $investinfo['loan']['prome_status'] == 1 ? 5 : $investinfo['loan']['status'];
            $investinfo['loan']['status'] = $investinfo['loan']['prome_status'] == 1 ? 5 : $investinfo['loan']['status'];
            if (($investinfo['loan']['status'] == 2) || ($investinfo['loan']['status'] == 4)) {
                $end_date = date('Y-m-d', (time() + ($investinfo['loan']['days']) * 24 * 3600));
            } elseif (empty($investinfo['loan']['start_date'])) {
                $end_date = date('Y-m-d', (strtotime($investinfo['loan']['create_time']) + ($investinfo['loan']['days']) * 24 * 3600));
            } else {
//收益时间(当前时间加上投资时间再减一天)
                $end_date = date('Y-m-d', (strtotime($investinfo['loan']['start_date']) + ($investinfo['loan']['days']) * 24 * 3600));
            }
            $date = date('Y-m-d');
            if (($investinfo['loan']['status'] == 4) || ($investinfo['loan']['status'] == 3) || ($investinfo['loan']['status'] == 7) || ($investinfo['loan']['status'] == 15) || ($investinfo['loan']['status'] == 17)) {
//已失效，借款人取消借款
                $investlist[$key]['profit_status'] = 4;
            } else if (($investinfo['loan']['status'] == 2) || ($investinfo['loan']['status'] == 1) || ($investinfo['loan']['status'] == 5)) {
                $investlist[$key]['profit_status'] = 1;
            } else if (($investinfo['loan']['status'] == 8) || ($investinfo['loan']['status'] == 14)) {
                $investlist[$key]['profit_status'] = 3;
            } else if (($investinfo['loan']['status'] == 6) || ($investinfo['loan']['status'] == 9) || ($investinfo['loan']['status'] == 10) || ($investinfo['loan']['status'] == 11) || ($investinfo['loan']['status'] == 12) || ($investinfo['loan']['status'] == 13)) {
//持续收益中
                $investlist[$key]['profit_status'] = 2;
            } else {
//收益状态
                if ($date < $investinfo['loan']['start_date']) {
//等待获取收益
                    $investlist[$key]['profit_status'] = 1;
                }
                if (($date >= $investinfo['loan']['start_date']) && ($date <= $end_date)) {
//持续收益中
                    $investlist[$key]['profit_status'] = 2;
                }
                if ($date > $end_date) {
//已收益
                    $investlist[$key]['profit_status'] = 3;
                }
            }
            $openidobj = User::find()->select(array('openid'))->where(['user_id' => $investinfo['loan']['user_id']])->one();
            if (!empty($openidobj->openid)) {
                $headobj = Userwx::find()->select(array('head'))->where(['openid' => $openidobj->openid])->one();
                if (empty($headobj) && !isset($headobj)) {
                    $investlist[$key]['head'] = '/images/bigFace.png';
                } else {
                    $investlist[$key]['head'] = $headobj->head;
                }
            } else {
                $investlist[$key]['head'] = '/images/bigFace.png';
            }
            $investlist[$key]['invest_time'] = date('m' . '月' . 'd' . '日 ' . ' H:i', strtotime($investinfo['create_time']));
//$investlist[$key]['head'] = $headobj->head;
        }
//print_r($investlist);exit;
//投资列表
        $this->getView()->title = "投资记录";
        return $this->render('investlist', [
                    'investlist' => $investlist,
                    'accountinfo' => $accountinfo,
                    'user_id' => $user_id,
                    'jsinfo' => $jsinfo
        ]);
    }

    public function actionInvestxhh() {
        $this->layout = 'activity1';
        $user_id = Yii::$app->request->get('user_id');
        $invest = User_credit_stat::find()->where(['user_id' => $user_id])->one();

        if (empty($invest)) {
            $total = 0.00;
            $total_income = 0.00;
        } else {
            $total = $invest->total_amount;
            $p = stripos($total, '.');
            $total = substr($total, 0, $p + 3);
            $total_income = round($invest->total_income, 2);
        }

        $jsinfo = $this->getWxParam();
        $this->getView()->title = "投资先花宝";
        return $this->render('investxhh', [
                    'total' => $total,
                    'jsinfo' => $jsinfo,
                    'total_income' => $total_income
        ]);
    }

    public function actionInvestdetail() {
        $invest_id = isset($_GET['invest_id']) ? intval($_GET['invest_id']) : '';
        $this->getView()->title = "投资详情";
//查询借款的信息
        $sql = "select l.days,l.status,l.prome_status,l.start_date as begin_date,from_unixtime(unix_timestamp(l.start_date), '%Y-%m-%d') as start_date,l.end_date,l.desc,l.days,i.amount,u.realname,u.identity,w.nickname,w.head from yi_user_wx as w,yi_user_loan as l,yi_user_invest as i,yi_user as u where i.invest_id=" . $invest_id . " and i.loan_id=l.loan_id and i.loan_user_id = u.user_id and u.openid = w.openid";
        $investinfo = Yii::$app->db->createCommand($sql)->queryOne();
        $investinfo['status'] = $investinfo['prome_status'] == 1 ? 5 : $investinfo['status'];
        if (($investinfo['status'] == 2) || ($investinfo['status'] == 4)) {
            $end_date = date('Y-m-d', (time() + ($investinfo['days']) * 24 * 3600));
        } else {
//收益时间(当前时间加上投资时间再减一天)
            $end_date = date('Y-m-d', (strtotime($investinfo['begin_date']) + ($investinfo['days']) * 24 * 3600));
        }
//身份证号
        $identity = substr($investinfo['identity'], 0, 4) . '**********' . substr($investinfo['identity'], -4);
        $date = date('Y-m-d');
        if (($investinfo['status'] == 4) || ($investinfo['status'] == 3) || ($investinfo['status'] == 7) || ($investinfo['status'] == 15) || ($investinfo['status'] == 17)) {
//已失效，借款人取消借款
            $investinfo['profit_status'] = 4;
        } else if (($investinfo['status'] == 2) || ($investinfo['status'] == 1) || ($investinfo['status'] == 5)) {
            $investinfo['profit_status'] = 1;
        } else if (($investinfo['status'] == 8) || ($investinfo['status'] == 14)) {
            $investinfo['profit_status'] = 3;
        } else if (($investinfo['status'] == 6) || ($investinfo['status'] == 9) || ($investinfo['status'] == 10) || ($investinfo['status'] == 11) || ($investinfo['status'] == 12) || ($investinfo['status'] == 13)) {
//持续收益中
            $investinfo['profit_status'] = 2;
        } else {
//收益状态
            if ($date < $investinfo['start_date']) {
//等待获取收益
                $investinfo['profit_status'] = 1;
            }
            if (($date >= $investinfo['start_date']) && ($date <= $end_date)) {
//持续收益中
                $investinfo['profit_status'] = 2;
            }
            if ($date > $end_date) {
//已收益
                $investinfo['profit_status'] = 3;
            }
        }
//计算期满收益
        $profit = number_format(($investinfo['amount'] * (Yii::$app->params['rate'] / 100) / 365) * $investinfo['days'], 2, '.', '');
        $jsinfo = $this->getWxParam();
        return $this->render('investdetail', [
                    'investinfo' => $investinfo,
                    'end_date' => $end_date,
                    'profit' => $profit,
                    'identity' => $identity,
                    'jsinfo' => $jsinfo
        ]);
    }

    public function actionCoupon() {
        $this->layout = "newmain";
        $this->getView()->title = "优惠券";
        $openid = $this->getVal('openid');
        $mobile = $this->getVal('mobile');
        $now_time = date('Y-m-d H:i:s');
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
                        if ($this->openidRegSave($usinfo)){
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

//判断openid和mobile
        if (empty($openid) || empty($mobile)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }

//查询可用的优惠券
        $sql = "select id,title,val,`limit`,end_date,status,create_time,@type:=1 from " . Coupon_list::tableName() . " where mobile='" . $mobile . "' and status=1 and end_date>'$now_time' order by create_time desc";
        $data = Yii::$app->db->createCommand($sql)->queryAll();
        $jsinfo = $this->getWxParam();

        if (!empty($data)) {
            return $this->render('coupon', [
                        'couponlist' => $data,
                        'jsinfo' => $jsinfo
            ]);
        } else {
            $this->layout = "loan";
            return $this->render('nocoupon', [
                        'jsinfo' => $jsinfo
            ]);
        }
    }

    public function actionGetinvitecode() {
        $mobile = $this->getVal('mobile');
        $invite_code = $_POST['invite_code'];
        $user = User::find()->where(['invite_code' => "$invite_code"])->andWhere("mobile != '$mobile'")->one();
        if (!empty($mobile) && isset($user->invite_code) && !empty($user->invite_code)) {
            if ($user->status == 5) {
                echo 'black';
            } else {
                echo 'success';
            }
        } else {
            echo 'noexist';
        }
        exit;
    }

    public function actionSetinvitecode() {
        $mobile = $this->getVal('mobile');
        if (empty($mobile)) {
            echo "fail";
            exit;
        }
        $invite_code = $_POST['invite_code'];
//查询用户的user_id
        $user = User::find()->select(array('user_id'))->where(['mobile' => $mobile])->one();
//查询邀请人的user_id
        $user_invite = User::find()->select(array('user_id'))->where(['invite_code' => "$invite_code"])->one();
        if ($user_invite->user_id == $user->user_id) {
            echo 'fail';
            exit;
        }

        $transaction = Yii::$app->db->beginTransaction();
        $friendModel = new Friends();
        $friendModel->refreshFriend($user->user_id, $user_invite->user_id);
//更新来源的邀请码
        $sql_from_code = "update " . User::tableName() . " set from_code='$invite_code' where user_id=" . $user['user_id'];
        $ret_from_code = Yii::$app->db->createCommand($sql_from_code)->execute();

//邀请人提额50点
        $sql_invitecode = "update " . Account::tableName() . " set remain_amount=remain_amount-50,amount=amount+50,current_amount=current_amount+50 where user_id=" . $user_invite['user_id'];
        $user_invitecode = Yii::$app->db->createCommand($sql_invitecode)->execute();

//注册用户提额200点
        $sql_registercode = "update " . Account::tableName() . " set remain_amount=remain_amount-200,amount=amount+200,current_amount=current_amount+200 where user_id=" . $user['user_id'];
        $user_registercode = Yii::$app->db->createCommand($sql_registercode)->execute();

//记录提额的日志
        $amount_date = array(
            'type' => 5,
            'user_id' => $user_invite['user_id'],
            'amount' => 50
        );
        $user_amount = new User_amount_list();
        $user_amount->CreateAmount($amount_date);

//记录提额的日志
        $amount_from_date = array(
            'type' => 12,
            'user_id' => $user['user_id'],
            'amount' => 200
        );
        $user_amount = new User_amount_list();
        $user_amount->CreateAmount($amount_from_date);
        if ($user_registercode) {
            $transaction->commit();
            echo 'success';
            exit;
        } else {
            $transaction->rollBack();
            echo 'fail';
            exit;
        }
    }

    public function actionAmount() {
        $this->layout = 'activity1';
        $this->getView()->title = "我的额度";
        $user_id = Yii::$app->request->get('user_id');
        if (!empty($user_id)) {
            $userinfo = User::find()->joinWith('account', true, 'LEFT JOIN')->where([User::tableName() . '.user_id' => $user_id])->one();
        } else {
            $openid = $this->getVal('openid');
//获取用户账户信息
            $userinfo = User::find()->joinWith('account', true, 'LEFT JOIN')->where([User::tableName() . '.openid' => $openid])->one();
            $user_id = $userinfo->user_id;
        }
//根据用户 的ID去查询投资总额度
        $invest = User_credit_stat::find()->where(['user_id' => $user_id])->one();
//        var_dump($invest);exit;
// echo $invest->total_amount;exit;
        if (empty($invest)) {
            $total = 0.00;
        } else {
            $total = $invest->total_amount;
            $p = stripos($total, '.');
            $total = substr($total, 0, $p + 3);
        }
// echo $total;exit;
        $url = '';
        if ($userinfo->user_type == '1') {
            if ($userinfo->school == '') {
                $url = '/dev/reg/two?user_id=' . $userinfo['user_id'] . '&url=' . urlencode('/dev/account');
            } else if ($userinfo->pic_identity == '') {
                $url = '/dev/reg/pic?user_id=' . $userinfo['user_id'] . '&url=' . urlencode('/dev/account');
            }
        } else {
            if ($userinfo->company == '') {
                $url = '/dev/reg/company?user_id=' . $userinfo['user_id'] . '&url=' . urlencode('/dev/account');
            } else if ($userinfo->identity == '') {
                $url = '/dev/reg/personals?user_id=' . $userinfo['user_id'] . '&url=' . urlencode('/dev/account');
            } else if ($userinfo->school == '') {
                $url = '/dev/reg/shool?user_id=' . $userinfo['user_id'] . '&url=' . urlencode('/dev/account');
            } else if ($userinfo->pic_identity == '') {
                $url = '/dev/reg/pic?user_id=' . $userinfo['user_id'] . '&url=' . urlencode('/dev/account');
            }
        }
//获取借款中的信用
        $status = array('1', '2', '5', '6', '9', '10', '11', '12', '13'); //如果用户存在借款状态为1、2、5、6、8
        $user_loan = User_loan::find()->select(array('sum(amount) as amount'))->where(['user_id' => $userinfo->user_id, 'status' => $status])->all();
        $jsinfo = $this->getWxParam();
        return $this->render('amount', ['userinfo' => $userinfo, 'url' => $url, 'user_loan' => $user_loan, 'jsinfo' => $jsinfo, 'total' => $total]);
    }

//点击刷新获取用户信息，更新头像和昵称
    public function actionGetuserinfo() {
//首先判断用户当天的刷新一次
        $begintime = date('Y-m-d' . ' 00:00:00');
        $endtime = date('Y-m-d' . ' 23:59:59');
        $openid = $this->getVal('openid');
        $user_refresh = User_refresh::find()->where(['openid' => $openid])->andWhere("create_time >= '$begintime'")->andWhere("create_time <= '$endtime'")->all();
        $count = count($user_refresh);
        if ($count >= 1) {
            echo 'moreone';
            exit;
        } else {
            $access_token = $this->getAccessToken();
            $array_user = array(
                'access_token' => $access_token,
                'openid' => $openid
            );
            $userinfo = $this->getUserinfo($array_user);
            $nickname = $userinfo['nickname'];
            $headurl = $userinfo['headimgurl'];
            $sql = "update " . Userwx::tableName() . " set nickname='".addslashes($nickname)."', head='$headurl' where openid='$openid'";
            $ret = Yii::$app->db->createCommand($sql)->execute();
//向用户刷新表中添加一条数据
            $model = new User_refresh();
            $model->openid = $openid;
            $model->create_time = date('Y-m-d H:i:s');
            $model->save();
            echo 'success';
            exit;
        }
    }

    public function actionBlack() {
        $this->layout = "renzhen";
        $this->getView()->title = "您提交的信息不符合规则，该账户已被冻结";
        $jsinfo = $this->getWxParam();
        return $this->render('black', ['jsinfo' => $jsinfo]);
    }

    public function actionAuth() {
        $this->layout = "renzhen";
        $this->getView()->title = "您已认证过该用户，不能重复认证哦！";
        $jsinfo = $this->getWxParam();
        return $this->render('auth', ['jsinfo' => $jsinfo]);
    }

    public function actionAuthfail() {
        $this->layout = "renzhen";
        $this->getView()->title = "您好像不认识Ta哦！";
        $jsinfo = $this->getWxParam();
        return $this->render('authfail', ['jsinfo' => $jsinfo]);
    }

//学校信息页面、
    public function actionSchool() {
        $this->layout = "inv";
        $this->getView()->title = "个人资料";
        $user_id = Yii::$app->request->get('user_id');
        if (!empty($user_id)) {
            $userinfo = User::find()->where(['user_id' => $user_id])->one();
        } else {
            $openid = $this->getVal('openid');
            $userinfo = User::find()->where(['openid' => $openid])->one();
        }
        $school = School::find()->all();
        $jsinfo = $this->getWxParam();
        return $this->render('school', [
                    'userinfo' => $userinfo,
                    'school' => $school,
                    'jsinfo' => $jsinfo,
        ]);
    }

//公司信息页面
    public function actionCompany() {
        $this->layout = "inv";
        $this->getView()->title = "个人资料";
        $user_id = Yii::$app->request->get('user_id');
        if (!empty($user_id)) {
            $userinfo = User::find()->where(['user_id' => $user_id])->one();
        } else {
            $openid = $this->getVal('openid');
            $userinfo = User::find()->where(['openid' => $openid])->one();
        }

        $posi = Score::find()->where(['type' => 'work'])->all();
        $jsinfo = $this->getWxParam();
        return $this->render('company', [
                    'userinfo' => $userinfo,
                    'posi' => $posi,
                    'jsinfo' => $jsinfo,
        ]);
    }

    public function actionPersonals() {
        $this->layout = "inv";
        $this->getView()->title = "个人资料";
        $user_id = Yii::$app->request->get('user_id');
        if (!empty($user_id)) {
            $userinfo = User::find()->where(['user_id' => $user_id])->one();
        } else {
            $openid = $this->getVal('openid');
            $userinfo = User::find()->where(['openid' => $openid])->one();
        }
        $jsinfo = $this->getWxParam();
        return $this->render('personals', [
                    'userinfo' => $userinfo,
                    'jsinfo' => $jsinfo,
        ]);
    }

    /**
     * 个人资料页
     * @return type
     */
    public function actionPeral() {
        $this->layout = "data"; //个人资料头部
        $user_id = Yii::$app->request->get('user_id');
        if (!empty($user_id)) {
            $userinfo = User::find()->where(['user_id' => intval($user_id)])->one();
        } else {
            $openid = $this->getVal('openid');
            $userinfo = User::find()->where(['openid' => $openid])->one();
        }
        if (empty($userinfo)) {
            return $this->redirect('/dev/account');
        }
        /*         * *************记录访问日志beigin******************* */
        $ip = Common::get_client_ip();
        $result_log = Common::saveLog('account', 'account_information', $ip, 'weixin', $userinfo->user_id);
        /*         * *************记录访问日志end******************* */
//查看用户的个人信息是否完善
        if ($userinfo->identity_valid != 2 && $userinfo->identity_valid != 4) {
            $pinfo = '未认证';
        } else {
            $pinfo = '修改';
        }
//判断公司，学籍信息是否完善
        if ($userinfo->company == '') {
            $cinfo = '未认证';
        } else {
            $cinfo = '修改';
        }
//判断学籍信息是否完善
        if ($userinfo->school_id == 0) {
            $sinfo = '未认证';
        } else {
            $sinfo = '已认证';
        }
//判断信用信息是否完善
        if ($userinfo->status == 3 || $userinfo->status == 5) {
            $xinfo = '已完善';
        } else if ($userinfo->status == 2) {
            $xinfo = '审核中';
        } else {
            $xinfo = '未完善';
        }
        if ($userinfo->status == 2) {
            $juli = 1;
        } else {
            $juxinliModel = new Juxinli();
            $juxinli = $juxinliModel->getJuxinliByUserId($userinfo->user_id);
            $juli = 0;
            if (empty($juxinli) || $juxinli->process_code != '10008' || ($juxinli->process_code == '10008' && date('Y-m-d H:i:s', strtotime('-4 month')) >= $juxinli->last_modify_time)) {
                $juli = 1;
            }
        }
        $juxinliModel = new Juxinli();
        $jingdong = $juxinliModel->getJuxinliByUserId($userinfo->user_id, 2);
        if (empty($jingdong) || $jingdong->process_code != '10008') {
            $jing = 1;
        } else {
            $jing = 2;
        }
        $favorite = new Favorite_contacts();
        $fav = $favorite->getFavoriteByUserId($userinfo->user_id);
        $contacts = !empty($fav) ? 1 : 2;
        $this->getView()->title = "个人资料";
        $jsinfo = $this->getWxParam();
        return $this->render('peral', [
                    'userinfo' => $userinfo,
                    'pinfo' => $pinfo,
                    'cinfo' => $cinfo,
                    'sinfo' => $sinfo,
                    'xinfo' => $xinfo,
                    'juli' => $juli,
                    'jing' => $jing,
                    'contacts' => $contacts,
                    'jsinfo' => $jsinfo,
        ]);
    }

//使用说明
    public function actionUsehelp() {
        $this->layout = 'newmain';
        $this->getView()->title = "使用规则";
        $jsinfo = $this->getWxParam();
        return $this->render('usehelp', [
                    'jsinfo' => $jsinfo,
        ]);
    }

//收益=============================
    public function actionIncome() {
        $this->layout = 'newmain';
        $this->getView()->title = "我的收益";
        $user_id = Yii::$app->request->get('user_id');
//投资好友收益
        $incomefr = Account::find()->select(array('total_income'))->where(['user_id' => $user_id])->one();
//先花宝收益
        $incomexh = User_credit_stat::find()->select(array('total_income'))->where(['user_id' => $user_id])->one();
//理财收益
        $incomest = Standard_account::find()->select(array('total_historyinterest'))->where(['user_id' => $user_id])->one();
//历史总收益
        $totalIncome = floatval($incomefr['total_income']) + floatval($incomexh['total_income']) + floatval($incomest['total_historyinterest']);
        $totalIncome = sprintf('%.2f', $totalIncome);
//已提现收益
        $outsql = "select sum(amount) as outincome from " . Account_settlement::tableName() . " where user_id = " . $user_id . " and status='SUCCESS' and (type=2 or type=5)";
        $outIncome = Yii::$app->db->createCommand($outsql)->queryOne();
        $outIncome = sprintf('%.2f', $outIncome['outincome']);
        $remainIncome = $totalIncome - $outIncome;
        $repacket_num = $remainIncome * 2;
        if ($repacket_num * 100 % 500 !== 0) {
            $repacket_num = ceil($repacket_num / 5) * 5;
        }
        $jsinfo = $this->getWxParam();
        return $this->render('income', [
                    'userId' => $user_id,
                    'totalIncome' => $totalIncome,
                    'remainIncome' => $remainIncome,
                    'repacket_num' => $repacket_num,
                    'jsinfo' => $jsinfo,
        ]);
    }

    /**
     * 兑换米富红包
     */
    public function actionGetredpacket() {
        $user_id = Yii::$app->request->post('user_id');
        $money = Yii::$app->request->post('money');
        $userId = intval($user_id);
        $user = User::findOne($userId);
        if (empty($user)) {
            return json_encode(array('ret_code' => 1, 'ret_msg' => '用户不存在'));
        }
//投资好友收益
        $incomefr = Account::find()->select(array('total_income'))->where(['user_id' => $user_id])->one();
//先花宝收益
        $incomexh = User_credit_stat::find()->select(array('total_income'))->where(['user_id' => $user_id])->one();
//理财收益
        $incomest = Standard_account::find()->select(array('total_historyinterest'))->where(['user_id' => $user_id])->one();
//历史总收益
        $totalIncome = floatval($incomefr['total_income']) + floatval($incomexh['total_income']) + floatval($incomest['total_historyinterest']);
        $totalIncome = sprintf('%.2f', $totalIncome); //已提现收益
        $outsql = "select sum(amount) as outincome from " . Account_settlement::tableName() . " where user_id = " . $user_id . " and status='SUCCESS' and (type=2 or type=5)";
        $outIncome = Yii::$app->db->createCommand($outsql)->queryOne();
        $outIncome = sprintf('%.2f', $outIncome['outincome']);
        $remainIncome = $totalIncome - $outIncome;
        $repacket_num = $remainIncome * 2;
        if ($repacket_num * 100 % 500 !== 0) {
            $repacket_num = ceil($repacket_num / 5) * 5;
        }
        if ($repacket_num != $money) {
            return json_encode(array('ret_code' => 2, 'ret_msg' => '兑换金额不正确'));
        }
        $url = 'http://www.yaoyuefu.com/api/redpacket';
        $par = 'val=' . $money . '&mobile=' . $user->mobile;
        $param = Crypt3Des::encrypt($par, '48HfjalQXzNMIHxaNmvAVWd9jfApGD9v');
        $data = array(
            'param' => $param,
        );
        $result = Http::interface_post($url, $data);
        $res = json_decode($result);
        Logger::errorLog(print_r($res, true), 'red_pack');
        if ($res->rsp_code == 0) {
//1.生成提现记录
            $model = new Account_settlement();
            $model->settlement_id = date('Ymdhis') . rand(1000, 9999);
            $model->user_id = $userId;
            $model->amount = $remainIncome;
            $model->type = 2;
            $model->status = "SUCCESS";
            $model->create_time = date('Y-m-d H:i:s');
            $model->save();
            return json_encode(array('ret_code' => 0, 'ret_msg' => ''));
        } else {
            return json_encode(array('ret_code' => 3, 'ret_msg' => $res->rsp_msg));
        }
    }

    /**
     * 认证好友收益
     * @param type $user_id
     */
    public function actionRedpackets($user_id) {
        $this->layout = 'invmain';
        $this->view->title = '认证好友收益';
        $list = Red_packets_receive::find()->where(['user_id' => $user_id, 'status' => array('NORMAL', 'WITHDRAW')])->all();
        return $this->render('redpackets', array(
                    'list' => $list,
        ));
    }

//好友收益
    public function actionIncomefr($user_id) {
        $this->layout = 'newmain';
        $this->getView()->title = "投资好友收益";
//投资好友收益
        $incomefr = Account::find()->select(array('total_income'))->where(['user_id' => $user_id])->one();
        $investlist = User_invest::find()->joinWith('loan')->where([User_invest::tableName() . '.user_id' => $user_id, User_invest::tableName() . '.status' => 3])->orderBy('create_time desc')->asarray()->all();

        $dataList = array();
//投资用户id
        $loanUserIds = array();
        if (!empty($investlist)) {
            foreach ($investlist as $val) {
                $loanUserIds[] = $val['loan_user_id'];
            }
        }
//取投资的借款用户信息
        $users = array();
        if (!empty($loanUserIds)) {
            $userinfo = User::find()->joinWith('userwx')->where(['user_id' => $loanUserIds])->all();
            if (!empty($userinfo)) {
                foreach ($userinfo as $vv) {
                    $users[$vv['user_id']]['realname'] = $vv['realname'];
                    $users[$vv['user_id']]['head'] = $vv['userwx']['head'];
                }
            }
        }

        if (!empty($investlist)) {
            foreach ($investlist as $val) {
                $dataList[$val['invest_id']]['invest_id'] = $val['invest_id'];
                $dataList[$val['invest_id']]['user_id'] = $val['user_id'];
                $dataList[$val['invest_id']]['loan_user_id'] = $val['loan_user_id'];
                $dataList[$val['invest_id']]['amount'] = $val['amount'];
                $dataList[$val['invest_id']]['create_time'] = $val['create_time'];
                $dataList[$val['invest_id']]['income'] = number_format(($val['amount'] * (Yii::$app->params['rate'] / 100) / 365) * $val['loan']['days'], 2, '.', '');
                $dataList[$val['invest_id']]['desc'] = $val['loan']['desc'];
                $dataList[$val['invest_id']]['realname'] = !empty($users[$val['loan_user_id']]['realname']) ? $users[$val['loan_user_id']]['realname'] : '';
                $dataList[$val['invest_id']]['head'] = !empty($users[$val['loan_user_id']]['head']) ? $users[$val['loan_user_id']]['head'] : '';
            }
        }
//     	print_r( $dataList );exit;
        $jsinfo = $this->getWxParam();
        return $this->render('incomefr', [
                    'dataList' => $dataList,
                    'jsinfo' => $jsinfo,
        ]);
    }

//先花宝收益
    public function actionIncomexh($user_id) {
        $this->layout = 'newmain';
        $this->getView()->title = "投资先花宝收益";

        $incomeList = User_credit_income_record::find()->where(['user_id' => $user_id])->orderBy('create_time desc')->all();
        $jsinfo = $this->getWxParam();
        return $this->render('incomexh', [
                    'incomeList' => $incomeList,
                    'jsinfo' => $jsinfo,
        ]);
    }

//理财收益
    public function actionIncomest($user_id) {
        $this->layout = 'newmain';
        $this->getView()->title = "信用理财收益";
//查询自己购买的标的
        $standard_list = Standard_statistics::find()->joinWith('information', true, 'LEFT JOIN')->where([Standard_statistics::tableName() . '.user_id' => $user_id])->andWhere([Standard_information::tableName() . '.status' => 'FINISHED'])->andWhere(Standard_statistics::tableName() . ".total_onInvested_share > 0")->orderBy(Standard_statistics::tableName() . '.create_time desc')->all();
        $jsinfo = $this->getWxParam();
        return $this->render('incomest', [
                    'standardList' => $standard_list,
                    'jsinfo' => $jsinfo,
        ]);
    }

//收益提现
    public function actionIncomewd($user_id) {
        $this->layout = 'newmain';
        $this->getView()->title = "收益提现";

//投资好友收益
        $incomefr = Account::find()->select(array('total_income'))->where(['user_id' => $user_id])->one();
//先花宝收益
        $incomexh = User_credit_stat::find()->select(array('total_income'))->where(['user_id' => $user_id])->one();
//理财收益
        $incomest = Standard_account::find()->select(array('total_historyinterest'))->where(['user_id' => $user_id])->one();
//历史总收益
        $totalIncome = floatval($incomefr['total_income']) + floatval($incomexh['total_income']) + floatval($incomest['total_historyinterest']);
        $totalIncome = sprintf('%.2f', $totalIncome);
//已提现收益
        $outsql = "select sum(amount) as outincome from " . Account_settlement::tableName() . " where user_id = " . $user_id . " and status='SUCCESS' and (type=2 or type=5)";
        $outIncome = Yii::$app->db->createCommand($outsql)->queryOne();
        $outIncome = sprintf('%.2f', $outIncome['outincome']);

        $realIncome = $totalIncome - $outIncome;
//绑定银行卡信息
        $userdefault = User_bank::find()->where(['user_id' => $user_id, 'status' => 1, 'default_bank' => 1, 'type' => 0])->one();
        if (empty($userdefault)) {
            $userdefault = User_bank::find()->where(['user_id' => $user_id, 'status' => 1, 'type' => 0])->orderby('create_time')->one();
            if (empty($userdefault) && !isset($userdefault)) {
                return $this->redirect("/dev/bank/index");
            }
        }
//验证用户是否为限制用户===================================
        $limitStatus = 0;
        $userInfo = User::find()->where(['user_id' => $user_id])->one();
        if ($userInfo->status == '5') {
            $limitStatus = 1; //黑名单用户
        }
        $userLoan = User_loan::find()->where(['user_id' => $user_id, 'status' => array('11', '12', '13')])->all();
        if (!empty($userLoan) && $limitStatus == 0) {
            $limitStatus = 2; //有未还款借款
        }
//春节期间，禁止提现
        $start_time = '2016-02-05 12:00:00';
        $end_time = '2016-02-15 10:00:00';
        $now_time = date('Y-m-d H:i:s');
        if ($now_time >= $start_time && $now_time <= $end_time) {
            $limitStatus = 4;
        }

//         $userLimit = Grey_list::find()->where(['mobile' => $userInfo->mobile])->count();
//         if ($userLimit > 0 && $limitStatus == 0) {
//             $limitStatus = 3; //公司用户限制
//         }
//====================================================
        $user_bankinfo = User_bank::find()->where(['user_id' => $user_id, 'status' => 1])->all();
        $jsinfo = $this->getWxParam();
        return $this->render('incomewd', [
                    'userId' => $user_id,
                    'realIncome' => $realIncome,
                    'bankDefault' => $userdefault,
                    'bankinfo' => $user_bankinfo,
                    'limitStatus' => $limitStatus,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'now_time' => $now_time,
                    'jsinfo' => $jsinfo,
        ]);
    }

//提现收益
    public function actionOutincome() {
        $user_id = $_POST['user_id'];
        $outincome = $_POST['outincome'];
        $bank_id = $_POST['bank_id'];

        $begin_time = '2016-07-31 00:00:00';
        $end_time = '2016-07-31 23:59:59';
        $now_time = date('Y-m-d H:i:s');
        if ($now_time < $begin_time || $now_time > $end_time) {
            $resultArr = array('ret' => '5', 'msg' => '尊敬的用户，平台升级已完成，收益将额外补偿，正在为您计算收益中，将在7月22日前重新开通，敬请关注提现通知，给您造成不便，敬请谅解！');
            echo json_encode($resultArr);
            exit;
        }

        $nowtime = date('G');
        if ($nowtime >= 0 && $nowtime < 7) {
            $resultArr = array('ret' => '5', 'msg' => '0点至6点暂停提现业务');
            echo json_encode($resultArr);
            exit;
        }

        if (empty($user_id) || $outincome < 10) {
            $ret = array('ret' => 1, 'msg' => '请返回重试！');
            echo json_encode($ret);
            exit;
        }
//每个用户每天只能操作三次
        $begintime = date('Y-m-d' . ' 00:00:00');
        $endtime = date('Y-m-d' . ' 23:59:59');
        $count = Account_settlement::find()->where("user_id=$user_id and create_time >= '$begintime' and create_time <= '$endtime' and type=2")->count();
        if ($count >= 1) {
            $ret = array('ret' => 3, 'msg' => '您今天已经提过了，请明天再来~~');
            echo json_encode($ret);
            exit;
        }
//1.生成提现记录
        $model = new Account_settlement();
        $model->settlement_id = date('Ymdhis') . rand(1000, 9999);
        $model->user_id = $user_id;
        $model->bank_id = $bank_id;
        $model->amount = $outincome;
        $model->type = 2;
        $model->status = "INIT";
        $model->create_time = date('Y-m-d H:i:s');
        if ($model->save()) {
            $account_settlement_id = $model->attributes['id'];
//2.调中信出款接口
            $userinfo = User::find()->where(['user_id' => $user_id])->one();
            $userbank = User_bank::find()->where(['user_id' => $user_id, 'id' => $bank_id])->one();
            $user_mobile = $userinfo->mobile;
            $user_name = $userinfo->realname;
//持卡人姓名
            $guest_account_name = $userinfo->realname;
//银行卡号
            $guest_account = $userbank->card;
            $guest_account_bank = $userbank->bank_name;
            $guest_account_province = '北京市';
            $guest_account_city = '北京市';
            $guest_account_bank_branch = $userbank->bank_name;
            $account_type = 0;
            $settle_amount = $outincome;
            $order_id = date('Ymdhis') . rand(100000, 999999);
            if ($outincome >= 500) {
                $loan_id = $account_settlement_id;
                $admin_id = -1;
                $settle_request_id = $order_id;
                $real_amount = $outincome;
                $settle_fee = 0;
                $settle_amount = $outincome;
                $rsp_code = '0000';
                $remit_status = 'INIT';
                $create_time = date('Y-m-d H:i:s', time());

                $sql = "insert into " . User_remit_list::tableName() . "(order_id,loan_id,admin_id,settle_request_id,real_amount,settle_fee,settle_amount,rsp_code,remit_status,create_time,bank_id,user_id,type) ";
                $sql .= "value('" . $order_id . "','" . $loan_id . "',$admin_id,'$settle_request_id','$real_amount ','$settle_fee','$settle_amount','$rsp_code','$remit_status','$create_time','$bank_id','$user_id',4)";
                $retinsert = Yii::$app->db->createCommand($sql)->execute();

                if ($retinsert >= 0) {
//打款成功，修改收益提现记录状态
                    $sql = "update " . Account_settlement::tableName() . " set status='SUCCESS' where id=" . $account_settlement_id;
                    Yii::$app->db->createCommand($sql)->execute();
                    $ret = array('ret' => 0, 'msg' => '成功');
                    echo json_encode($ret);
                    exit;
                } else {
//记录一下日志,出款记录日志
                    Logger::errorLog($sql, 'ccount_settlement_failed');
                    $ret = array('ret' => 0, 'msg' => '成功咯');
                    echo json_encode($ret);
                    exit;
                }
            } else {
                $params = [
                    'req_id' => $order_id,
                    'remit_type' => 3,
                    'identityid' => $userinfo->identity,
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
                    $sql .= "value('" . $order_id . "','" . $loan_id . "',$admin_id,'$settle_request_id','$real_amount ','$settle_fee','$settle_amount','$rsp_code','$remit_status','$create_time','$bank_id','$user_id',4)";
                    $retinsert = Yii::$app->db->createCommand($sql)->execute();

                    if ($retinsert >= 0) {
//打款成功，修改收益提现记录状态
                        $sql = "update " . Account_settlement::tableName() . " set status='SUCCESS' where id=" . $account_settlement_id;
                        Yii::$app->db->createCommand($sql)->execute();
                        $ret = array('ret' => 0, 'msg' => '成功');
                        echo json_encode($ret);
                        exit;
                    } else {
//记录一下日志,出款记录日志
                        Logger::errorLog($sql, 'ccount_settlement_failed');
                        $ret = array('ret' => 0, 'msg' => '成功咯');
                        echo json_encode($ret);
                        exit;
                    }
                } else if ($res['res_code'] == '13003') {
                    $sql = "update " . Account_settlement::tableName() . " set status='FAILED' where id=" . $account_settlement_id;
                    Yii::$app->db->createCommand($sql)->execute();
                    $ret = array('ret' => 2, 'msg' => $res['res_msg']);
                    echo json_encode($ret);
                    exit;
                } else {
//打款失败，修改收益提现记录状态
                    $sql = "update " . Account_settlement::tableName() . " set status='FAILED' where id=" . $account_settlement_id;
                    Yii::$app->db->createCommand($sql)->execute();
                    $ret = array('ret' => 2, 'msg' => '请稍候再试~~');
                    echo json_encode($ret);
                    exit;
                }
            }
        } else {
            $ret = array('ret' => 3, 'msg' => '请检查你的网络');
            echo json_encode($ret);
            exit;
        }
    }

    public function actionJuxinli() {
        $this->layout = 'data';
        $openid = $this->getVal('openid');
        $user_id = Yii::$app->request->get('user_id');
        if (!empty($openid)) {
            $user = User::find()->where(['openid' => $openid])->one();
        } else if (!empty($user_id)) {
            $user = User::find()->where(['user_id' => $user_id])->one();
        } else {
            return $this->redirect('/dev/reg/login');
        }
//记录来源地址
        if (isset($_GET['url'])) {
            $redirUrl = urldecode($_GET['url']);
            if ($redirUrl) {
                $this->setVal('nextPageUrl', $redirUrl);
            }
        }
        if ($user->realname == '' || $user->identity == '') {
            return $this->redirect('/dev/reg/personals?user_id=' . $user_id);
        }
        $nextPage = $this->getVal('nextPageUrl');
        $nextPage = !empty($nextPage) ? $nextPage : '/dev/account/peral?user_id=' . $user_id;
//        $nextPage = $this->getNexturl($user) ? $this->getNexturl($user) : $nextPage;
        return $this->render('juxinli', array(
                    'nextPage' => $nextPage,
                    'user' => $user,
        ));
    }

    public function actionJufirst() {
        $code_array = array(
            '10002' => '',
            '10003' => '服务密码错误',
            '10004' => '短信验证码错误',
            '10006' => '验证码已经失效，请重新获取',
            '10007' => '请到运营商重置服务密码后再试',
            '10008' => '',
            '10009' => '手机号未实名，获取失败',
            '10010' => '服务密码错误',
            '11000' => '',
            '30000' => '无法采集，请稍后重试',
            '31000' => '',
            '0' => '请求超时',
        );
        $data = Yii::$app->request->post();
        $user_id = Yii::$app->request->get('user_id');
        if (!empty($user_id)) {
            $user = User::find()->where(['user_id' => $user_id])->one();
        } else {
            $openid = $this->getVal('openid');
            $user = User::find()->where(['openid' => $openid])->one();
        }
        $juxinliModel = new Juxinli();
        $juxinli = $juxinliModel->getJuxinliByUserId($user->user_id);
        $nextPage = $this->getVal('nextPageUrl');
        $nextPage = !empty($nextPage) ? $nextPage : '/dev/account/peral?user_id=' . $user->user_id;
//        $nextPage = $this->getNexturl($user) ? $this->getNexturl($user) : $nextPage;
        if (!empty($juxinli) && $juxinli->process_code == '10008' && date('Y-m-d H:i:s', strtotime('-4 month')) < $juxinli->last_modify_time) {
            $list = User_amount_list::find()->select('id')->where(['user_id' => $user->user_id, 'type' => 16, 'operation' => 1])->one();
            if (empty($list)) {
                $account = $user->account;
                $account->updateAccount($user, 16, 500);
            }
            $this->delVal('nextPageUrl');
            echo json_encode(array('code' => 3, 'url' => $nextPage));
            exit;
        }

        $postData = array(
            'name' => $user->realname,
            'idcard' => $user->identity,
            'phone' => $user->mobile,
            'password' => $data['password'],
            'captcha' => '',
            'type' => 'SUBMIT_CAPTCHA',
            'callbackurl' => '',
        );
        $favModel = new Favorite_contacts();
        $fav = $favModel->getFavoriteByUserId($user->user_id);
        $contacts = '';
        if (!empty($fav)) {
            $contacts = json_encode(array(
                array(
                    'contact_tel' => $fav->phone,
                    'contact_name' => $fav->relatives_name,
                    'contact_type' => '0', //亲属
                ),
                array(
                    'contact_tel' => $fav->mobile,
                    'contact_name' => $fav->contacts_name,
                    'contact_type' => '6', //常用联系人                      
                )
            ));
            $postData['contacts'] = $contacts;
        }
        $result = Http::juLixin($postData);
        if ($result['res_code'] == 0) {
            $condition['user_id'] = $user->user_id;
            $condition['requestid'] = isset($result['res_data']['requestid']) ? $result['res_data']['requestid'] : '';
            $condition['process_code'] = isset($result['res_data']['process_code']) ? $result['res_data']['process_code'] : '';
            $condition['status'] = isset($result['res_data']['status']) ? $result['res_data']['status'] : '';
            $condition['response_type'] = isset($result['res_data']['response_type']) ? $result['res_data']['response_type'] : '';
            if (!empty($juxinli)) {
                $juxinli->updateJulixin($condition);
            } else {
                $juxinliModel->addList($condition);
            }
            if ($condition['process_code'] == '10008') {//结束采集
                $list = User_amount_list::find()->select('id')->where(['user_id' => $user->user_id, 'type' => 16, 'operation' => 1])->one();
                if (empty($list)) {
                    $account = $user->account;
                    $account->updateAccount($user, 16, 500);
                }
                $this->delVal('nextPageUrl');
                echo json_encode(array('code' => 0, 'url' => $nextPage, 'msg' => ''));
                exit;
            } else if (in_array($condition['process_code'], array('10007', '10009', '10010', '30000', '31000', '0'))) {//结束采集                
                $msg = isset($code_array[$condition['process_code']]) ? $code_array[$condition['process_code']] : '';
                echo json_encode(array('code' => 3, 'url' => $nextPage, 'msg' => $msg));
                exit;
            } else if (in_array($condition['process_code'], array('10002', '10004'))) {//执行第二步               
                $msg = isset($code_array[$condition['process_code']]) ? $code_array[$condition['process_code']] : '';
                echo json_encode(array('code' => 2, 'msg' => $msg));
                exit;
            } else {//重新走第一步        
                $msg = isset($code_array[$condition['process_code']]) ? $code_array[$condition['process_code']] : '';
                echo json_encode(array('code' => 1, 'f' => '0', 'msg' => $msg));
                exit;
            }
        } else {
            echo json_encode(array('code' => 3, 'url' => '2', 'msg' => '采集错误，请稍后重试'));
            exit;
        }
    }

    public function actionJusecond() {
        $code_array = array(
            '10002' => '',
            '10003' => '服务密码错误',
            '10004' => '短信验证码错误',
            '10006' => '验证码已经失效，请重新获取',
            '10007' => '请到运营商重置服务密码后再试',
            '10008' => '',
            '10009' => '手机号未实名，获取失败',
            '10010' => '服务密码错误',
            '11000' => '',
            '30000' => '无法采集，请稍后重试',
            '31000' => '',
            '0' => '请求超时',
        );
        $data = Yii::$app->request->post();
        $user_id = Yii::$app->request->get('user_id');
        if (!empty($user_id)) {
            $user = User::find()->where(['user_id' => $user_id])->one();
        } else {
            $openid = $this->getVal('openid');
            $user = User::find()->where(['openid' => $openid])->one();
        }
        $nextPage = $this->getVal('nextPageUrl');
        $nextPage = !empty($nextPage) ? $nextPage : '/dev/account/peral?user_id=' . $user->user_id;
        $juxinliModel = new Juxinli();
        $juxinli = $juxinliModel->getJuxinliByUserId($user->user_id);
        $postData = array(
            'requestid' => $juxinli->requestid, // 请求唯一号
            'password' => $data['password'], // 服务密码
            'captcha' => $data['captcha'], //验证码
            'type' => 'SUBMIT_CAPTCHA',
        );
        $favModel = new Favorite_contacts();
        $fav = $favModel->getFavoriteByUserId($user->user_id);
        $contacts = '';
        if (!empty($fav)) {
            $contacts = json_encode(array(
                array(
                    'contact_tel' => $fav->phone,
                    'contact_name' => $fav->relatives_name,
                    'contact_type' => '0', //亲属
                ),
                array(
                    'contact_tel' => $fav->mobile,
                    'contact_name' => $fav->contacts_name,
                    'contact_type' => '6', //常用联系人                      
                )
            ));
            $postData['contacts'] = $contacts;
        }
        $result = Http::juLixin($postData, $juxinli);
        if ($result['res_code'] == 0) {
            $condition['user_id'] = $user->user_id;
            $condition['requestid'] = isset($result['res_data']['requestid']) ? $result['res_data']['requestid'] : $juxinli->requestid;
            $condition['process_code'] = isset($result['res_data']['process_code']) ? $result['res_data']['process_code'] : $juxinli->process_code;
            $condition['status'] = isset($result['res_data']['status']) ? $result['res_data']['status'] : $juxinli->status;
            $condition['response_type'] = isset($result['res_data']['response_type']) ? $result['res_data']['response_type'] : $juxinli->response_type;
            $juxinli->updateJulixin($condition);
            if ($condition['process_code'] == '10008') {
                $list = User_amount_list::find()->select('id')->where(['user_id' => $user->user_id, 'type' => 16, 'operation' => 1])->one();
                if (empty($list)) {
                    $account = $user->account;
                    $account->updateAccount($user, 16, 500);
                }
                $this->delVal('nextPageUrl');
                echo json_encode(array('code' => 0, 'url' => $nextPage));
                exit;
            } else if (in_array($condition['process_code'], array('10007', '10009', '10010', '30000', '31000', '0'))) {//结束采集                
                $msg = isset($code_array[$condition['process_code']]) ? $code_array[$condition['process_code']] : '';
                echo json_encode(array('code' => 3, 'url' => $nextPage, 'msg' => $msg));
                exit;
            } else if (in_array($condition['process_code'], array('10002', '10004'))) {//执行第二步               
                $msg = isset($code_array[$condition['process_code']]) ? $code_array[$condition['process_code']] : '';
                echo json_encode(array('code' => 2, 'msg' => $msg));
                exit;
            } else {//重新走第一步        
                $msg = isset($code_array[$condition['process_code']]) ? $code_array[$condition['process_code']] : '';
                echo json_encode(array('code' => 1, 'msg' => $msg));
                exit;
            }
        } else {
            echo json_encode(array('code' => 3, 'url' => $nextPage, 'msg' => '系统错误'));
            exit;
        }
    }

    public function actionJingdong() {
        $this->layout = 'data';
        $user_id = Yii::$app->request->get('user_id');
        $user = User::findOne($user_id);

        return $this->render('jingdong', array(
                    'user' => $user,
        ));
    }

    public function actionJingfirst() {
        $code_array = array(
            '10002' => '',
            '10003' => '请填写正确的京东登陆密码',
            '10004' => '请填写正确的验证码',
            '10006' => '请填写新的验证码',
            '10007' => '请填写正确的京东登陆密码',
            '10008' => '认证成功',
            '10009' => '',
            '10010' => '',
            '11000' => '请重新获取验证码',
            '30000' => '请稍后再试',
            '31000' => '',
            '0' => '请稍后再试',
        );
        $data = Yii::$app->request->post();
        $user_id = $data['user_id'];
        if (!empty($user_id)) {
            $user = User::find()->where(['user_id' => $user_id])->one();
        } else {
            echo json_encode(array('code' => 3, 'msg' => '授权错误，请稍后重试'));
            exit;
        }
        $nextPage = '/dev/account/peral?user_id=' . $user_id;
        $juxinliModel = new Juxinli();
        $juxinli = $juxinliModel->getJuxinliByUserId($user->user_id, 2);
        if (!empty($juxinli) && $juxinli->process_code == '10008') {
            echo json_encode(array('code' => 0, 'url' => $nextPage));
            exit;
        }
        $postData = array(
            'name' => $user->realname,
            'idcard' => $user->identity,
            'phone' => $user->mobile,
            'password' => $data['password'],
            'captcha' => '',
            'type' => 'SUBMIT_CAPTCHA',
            'callbackurl' => '',
            'account' => $data['user_name'],
            'website' => 'jingdong'
        );
        $favModel = new Favorite_contacts();
        $fav = $favModel->getFavoriteByUserId($user->user_id);
        $contacts = '';
        if (!empty($fav)) {
            $contacts = json_encode(array(
                array(
                    'contact_tel' => $fav->phone,
                    'contact_name' => $fav->relatives_name,
                    'contact_type' => '0', //亲属
                ),
                array(
                    'contact_tel' => $fav->mobile,
                    'contact_name' => $fav->contacts_name,
                    'contact_type' => '6', //常用联系人                      
                )
            ));
            $postData['contacts'] = $contacts;
        }
        $result = Http::juLixin($postData);
        if ($result['res_code'] == 0) {
            $key = Yii::$app->params['app_3des_key'];
            $condition['user_id'] = $user->user_id;
            $condition['requestid'] = isset($result['res_data']['requestid']) ? $result['res_data']['requestid'] : '';
            $condition['process_code'] = isset($result['res_data']['process_code']) ? $result['res_data']['process_code'] : '';
            $condition['status'] = isset($result['res_data']['status']) ? $result['res_data']['status'] : '';
            $condition['response_type'] = isset($result['res_data']['response_type']) ? $result['res_data']['response_type'] : '';
            $condition['user_name'] = $data['user_name'];
            $condition['password'] = Crypt3Des::encrypt($data['password'], $key);
            if (!empty($juxinli)) {
                $juxinli->updateJulixin($condition);
            } else {
                $condition['type'] = 2;
                $juxinliModel->addList($condition);
            }
            if ($condition['process_code'] == '10008') {//结束采集
                echo json_encode(array('code' => 0, 'url' => $nextPage, 'msg' => ''));
                exit;
            } else if (in_array($condition['process_code'], array('10007', '10009', '10010', '30000', '31000', '0'))) {//结束采集                
                $msg = isset($code_array[$condition['process_code']]) ? $code_array[$condition['process_code']] : '';
                echo json_encode(array('code' => 3, 'msg' => $msg));
                exit;
            } else if (in_array($condition['process_code'], array('10002', '10004', '10006'))) {//执行第二步               
                $msg = isset($code_array[$condition['process_code']]) ? $code_array[$condition['process_code']] : '';
                echo json_encode(array('code' => 2, 'msg' => $msg));
                exit;
            } else {//重新走第一步        
                $msg = isset($code_array[$condition['process_code']]) ? $code_array[$condition['process_code']] : '';
                echo json_encode(array('code' => 1, 'msg' => $msg));
                exit;
            }
        } else {
            echo json_encode(array('code' => 3, 'msg' => '授权错误，请稍后重试'));
            exit;
        }
    }

    public function actionJingsecond() {
        $code_array = array(
            '10002' => '',
            '10003' => '请填写正确的京东登陆密码',
            '10004' => '请填写正确的验证码',
            '10006' => '请填写新的验证码',
            '10007' => '请填写正确的京东登陆密码',
            '10008' => '认证成功',
            '10009' => '',
            '10010' => '',
            '11000' => '请重新获取验证码',
            '30000' => '请稍后再试',
            '31000' => '',
            '0' => '请稍后再试',
        );
        $data = Yii::$app->request->post();
        $user_id = $data['user_id'];
        if (!empty($user_id)) {
            $user = User::find()->where(['user_id' => $user_id])->one();
        } else {
            echo json_encode(array('code' => 3, 'msg' => '授权错误，请稍后重试'));
            exit;
        }
        $nextPage = '/dev/account/peral?user_id=' . $user->user_id;
        $juxinliModel = new Juxinli();
        $juxinli = $juxinliModel->getJuxinliByUserId($user->user_id, 2);
        $postData = array(
            'requestid' => $juxinli->requestid, // 请求唯一号
            'password' => $data['password'], // 服务密码
            'captcha' => $data['captcha'], //验证码
            'type' => 'SUBMIT_CAPTCHA',
            'account' => $data['user_name'],
            'website' => 'jingdong'
        );
        $favModel = new Favorite_contacts();
        $fav = $favModel->getFavoriteByUserId($user->user_id);
        $contacts = '';
        if (!empty($fav)) {
            $contacts = json_encode(array(
                array(
                    'contact_tel' => $fav->phone,
                    'contact_name' => $fav->relatives_name,
                    'contact_type' => '0', //亲属
                ),
                array(
                    'contact_tel' => $fav->mobile,
                    'contact_name' => $fav->contacts_name,
                    'contact_type' => '6', //常用联系人                      
                )
            ));
            $postData['contacts'] = $contacts;
        }
        $result = Http::juLixin($postData, $juxinli);
        if ($result['res_code'] == 0) {
            $condition['user_id'] = $user->user_id;
            $condition['requestid'] = isset($result['res_data']['requestid']) ? $result['res_data']['requestid'] : $juxinli->requestid;
            $condition['process_code'] = isset($result['res_data']['process_code']) ? $result['res_data']['process_code'] : $juxinli->process_code;
            $condition['status'] = isset($result['res_data']['status']) ? $result['res_data']['status'] : $juxinli->status;
            $condition['response_type'] = isset($result['res_data']['response_type']) ? $result['res_data']['response_type'] : $juxinli->response_type;
            $juxinli->updateJulixin($condition);
            if ($condition['process_code'] == '10008') {
                echo json_encode(array('code' => 0, 'url' => $nextPage));
                exit;
            } else if (in_array($condition['process_code'], array('10007', '10009', '10010', '30000', '31000', '0'))) {//结束采集                
                $msg = isset($code_array[$condition['process_code']]) ? $code_array[$condition['process_code']] : '';
                echo json_encode(array('code' => 3, 'msg' => $msg));
                exit;
            } else if (in_array($condition['process_code'], array('10002', '10004'))) {//执行第二步               
                $msg = isset($code_array[$condition['process_code']]) ? $code_array[$condition['process_code']] : '';
                echo json_encode(array('code' => 2, 'msg' => $msg));
                exit;
            } else {//重新走第一步        
                $msg = isset($code_array[$condition['process_code']]) ? $code_array[$condition['process_code']] : '';
                echo json_encode(array('code' => 1, 'f' => '0', 'msg' => $msg));
                exit;
            }
        } else {
            echo json_encode(array('code' => 3, 'msg' => '授权错误，请稍后重试'));
            exit;
        }
    }

    public function actionError() {
        return $this->render('error');
    }

}
