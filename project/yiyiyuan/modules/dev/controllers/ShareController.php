<?php

namespace app\modules\dev\controllers;

use app\commands\SubController;
use app\models\dev\Coupon_list;
use app\models\dev\Coupon_use;
use app\models\dev\Loan_like_stat;
use app\models\dev\Loan_record;
use app\models\dev\Standard_information;
use app\models\dev\User;
use app\models\dev\User_loan;
use app\models\dev\User_share_click;
use app\models\dev\Userwx;
use app\models\news\User_wx;
use Yii;
use app\commonapi\Http;
use app\commonapi\Logger;
use app\commonapi\Common;


class ShareController extends SubController {

    public $layout = 'main';
    public $enableCsrfValidation = false;

    public function beforeaction($action) {
        return true;
        $url = Yii::$app->request->hostInfo . Yii::$app->request->getUrl();
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

    public function actionLoaning() {
        //$this->layout = 'loan';
        $this->layout = 'share';
        $this->getView()->title = "快用你的信用点投资我 ,还有收益哦!";
        //获取借款记录的ID
        $loan_id = intval($_GET['d']);
        //获取时间
        $t = intval($_GET['t']);
        $s = $_GET['s'];
        if ($s == md5($t . $loan_id)) {
            //查询借款记录的信息
            $loaninfo = User_loan::find()->where(['loan_id' => $loan_id])->one();
            //借款用户信息
//             $sql = "select u.openid,u.user_id,u.status,u.realname,w.nickname,w.head from " . Userwx::tableName() . " as w," . User::tableName() . " as u where w.openid=u.openid and u.user_id=" . $loaninfo->user_id;
//             $loanuserinfo = Yii::$app->db->createCommand($sql)->queryOne();

            $sql = "select u.openid,u.user_id,u.status,u.realname,w.nickname,w.head from " . User::tableName() . " as u left join " . Userwx::tableName() . " as w on u.openid=w.openid where u.user_id=" . $loaninfo->user_id;
            $loanuserinfo = Yii::$app->db->createCommand($sql)->queryOne();

            $click_openid = $this->getVal('openid');

            if ($loanuserinfo['openid'] != $click_openid) {
                $user_click = new User_share_click();
                $click_id = Userwx::find()->select('id')->where(['openid' => $click_openid])->one();
                $array_click = array(
                    'user_id' => $loaninfo->user_id,
                    'loan_id' => $loan_id,
                    'click_id' => $click_id['id'],
                    'type' => 1,
                );
                $user_click->createClick($array_click);
            }
            //判断借款用户是否是黑名单用户
            if ($loanuserinfo['status'] == 5) {
                //跳转到黑名单用户页面
                return $this->redirect('/dev/account/black');
            }
            //剩余小时
            if (floor((strtotime($loaninfo['open_end_date']) - time()) / 3600) > 0) {
                $lefthour = floor((strtotime($loaninfo['open_end_date']) - time()) / 3600);
                if ($lefthour < 10) {
                    $lefthour = '0' . $lefthour;
                }
            } else {
                $lefthour = '00';
            }
            //剩余分钟
            if (floor(floor(((strtotime($loaninfo['open_end_date']) - time()) % 3600) / 60)) > 0) {
                $leftminute = floor(((strtotime($loaninfo['open_end_date']) - time()) % 3600) / 60);
                if ($leftminute < 10) {
                    $leftminute = '0' . $leftminute;
                }
            } else {
                $leftminute = '00';
            }
            //剩余秒
            if (((strtotime($loaninfo['open_end_date']) - time()) % 3600) % 60 >= 0) {
                $leftseconds = ((strtotime($loaninfo['open_end_date']) - time()) % 3600) % 60;
                if ($leftseconds < 10) {
                    $leftseconds = '0' . $leftseconds;
                }
            } else {
                $leftseconds = '00';
            }
            $Url1 = urlencode(Yii::$app->request->hostInfo . "/dev/invest/detail?loan_id=" . $loaninfo->loan_id . "&atten=1");
            $Url = urlencode(Yii::$app->request->hostInfo . "/dev/share/loaning?t=" . $t . "&d=" . $loan_id . "&s=" . md5($t . $loan_id));
            $shareUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . self::$_appid . "&redirect_uri=" . $Url . "&response_type=code&scope=snsapi_userinfo&state=xhh123#wechat_redirect";
            $jsinfo = $this->getWxParam();
            //查询投资该借款的记录信息
            $sql1 = "(select u.user_id,i.amount,i.create_time,i.type,u.realname,w.head,w.nickname from " . Loan_record::tableName() . " as i left join " . User::tableName() . " as u on i.invest_user_id=u.user_id left join " . Userwx::tableName() . " as w on u.openid=w.openid where i.loan_id=$loan_id and u.status!=6 and i.type=2)";
            $sql2 = "(select u.user_id,i.amount,i.create_time,i.type,u.realname,w.head,w.nickname from " . Loan_record::tableName() . " as i left join " . Userwx::tableName() . " as w on i.invest_user_id=w.id left join " . User::tableName() . " as u on w.openid=u.openid and u.status!=6 where i.loan_id=$loan_id and i.type=1)";
            $sql_invest = $sql1 . " union all " . $sql2 . " order by create_time desc";
//     		$sql_invest = "select i.invest_user_id as user_id,i.amount,i.create_time,w.head,w.nickname from ".Loan_record::tableName()." as i,yi_user as u,yi_user_wx as w where i.loan_id=$loan_id and i.invest_user_id=u.user_id and u.openid=w.openid";
            $investlist = Yii::$app->db->createCommand($sql_invest)->queryAll();
            //获取登录用户的信息
            $openid = $this->getVal('openid');
            $sql = "select w.id,w.nickname,w.head,u.user_id from " . Userwx::tableName() . " as w left join " . User::tableName() . " as u on w.openid=u.openid where w.openid='$openid'";
            $logininfo = Yii::$app->db->createCommand($sql)->queryOne();
            //随机获取推送模板
            $template = Common::push_template($loanuserinfo['nickname']);

            $phpsessid = isset($_COOKIE['PHPSESSID']) ? $_COOKIE['PHPSESSID'] : rand(100000000, 999999999);

            return $this->render('loaning', [
                        'loaninfo' => $loaninfo,
                        'loanuserinfo' => $loanuserinfo,
                        'investlist' => $investlist,
                        'logininfo' => $logininfo,
                        'lefthour' => $lefthour,
                        'leftminute' => $leftminute,
                        'leftseconds' => $leftseconds,
                        'shareUrl' => $shareUrl,
                        'Url1' => $Url1,
                        'phpsessid' => $phpsessid,
                        'jsinfo' => $jsinfo,
                        'template' => $template,
            ]);
        } else {
            echo '系统错误';
            exit;
        }
    }

    public function actionLoaningstat() {
        if (!empty($_POST)) {
            $loan_id = $_POST['loan_id'];
            //wx表中的id
            $invest_user_id = $_POST['user_id'];
            $openid = $this->getVal('openid');
            //查询登录用户是否是黑名单用户
            $logininfo = User::find()->select(array('status', 'user_id', 'user_type'))->where(['openid' => $openid])->one();

            if ((!empty($logininfo) && ($logininfo['status'] == 5))) {
                //跳转到黑名单错误提示页面
                $retArr = array('ret' => '4', 'amt' => 0);
                echo json_encode($retArr);
                exit;
            }
            //借款信息
            //$loaninfo = User_loan::find()->where(['loan_id'=>$loan_id])->one();
            $sql_loaninfo = "select u.openid,u.realname,u.mobile,u.identity,u.school,u.edu,u.school_time,u.birth_year,b.card,l.status,l.user_id,l.loan_no,l.amount,l.current_amount,l.interest_fee,l.start_date,l.end_date,l.desc,l.open_end_date,l.days,l.version from yi_user_loan as l,yi_account as a,yi_user as u,yi_user_bank as b where l.loan_id=$loan_id and l.user_id=a.user_id and l.user_id=u.user_id and l.bank_id=b.id";
            $loaninfo = Yii::$app->db->createCommand($sql_loaninfo)->queryOne();
            //查看用户是投资过
            $investinfo = Loan_record::find()->where(['loan_id' => $loan_id, 'invest_user_id' => $invest_user_id, 'type' => 1])->one();
            if (isset($investinfo->id)) {
                $retArr = array('ret' => '1', 'amt' => 0);
                echo json_encode($retArr);
                exit;
            } else {
                //查看借款用户是否是黑名单用户
                $sql_loanuserinfo = "select u.status from " . User::tableName() . " as u," . User_loan::tableName() . " as l where u.user_id=l.user_id and l.loan_id=" . $loan_id;
                $loanuserinfo = Yii::$app->db->createCommand($sql_loanuserinfo)->queryOne();
                if ($loanuserinfo['status'] == 5) {
                    //跳转到黑名单错误提示页面
                    $retArr = array('ret' => '4', 'amt' => 0);
                    echo json_encode($retArr);
                    exit;
                }
                //获取一亿元送金额次数
                $clickCount = Loan_record::find()->where(['loan_id' => $loan_id, 'type' => 1])->count();
                $amt = Http::clickLike($clickCount);
                $remainAmt = ( $loaninfo['amount'] - $loaninfo['current_amount'] );
                if ($remainAmt <= 0) {
                    //已满
                    $retArr = array('ret' => '2', 'amt' => 0);
                    echo json_encode($retArr);
                    exit;
                }
                if ($amt > $remainAmt) {
                    $isSucc = 1;
                    $amt = $remainAmt;
                } else {
                    $isSucc = 0;
                }
                $time = date('Y-m-d H:i:s', time());
                $transaction = Yii::$app->db->beginTransaction();
                $sql = "insert into " . Loan_record::tableName() . "(loan_id,invest_user_id,amount,type,create_time) value($loan_id,$invest_user_id,$amt,1,'$time')";
                $ret = Yii::$app->db->createCommand($sql)->execute();
                //更新借款已借金额、借款投资记录
                if ($ret) {
                    if ($isSucc) {
                        //点赞筹满需要更新借款状态
                        $time = time();
                        $create_time = date('Y-m-d H:i:s');
                        $loan = User_loan::find()->where(['loan_id' => $loan_id, 'version' => $loaninfo['version']])->one();
                        $loan->current_amount = $loaninfo['current_amount'] + $amt;
                        $loan->withdraw_time = $create_time;
                        $loan->status = 5;
                        $loan->version = $loaninfo['version'] + 1;
                        if (!$loan->save()) {
                            $transaction->rollBack();
                            $retArr = array('ret' => '3', 'amt' => 0);
                            echo json_encode($retArr);
                            exit;
                        } else {
                            $transaction->commit();
                            $retArr = array('ret' => '0', 'amt' => round($amt, 2));
                            echo json_encode($retArr);
                            exit;
                        }
                    } else {
                        $loan_sql = "update " . User_loan::tableName() . " set current_amount=" . ($loaninfo['current_amount'] + $amt) . ",version=" . ($loaninfo['version'] + 1) . " where loan_id=$loan_id and version=" . $loaninfo['version'];
                        $ret_loan = Yii::$app->db->createCommand($loan_sql)->execute();
                        if ($ret_loan) {
                            $transaction->commit();
                            $retArr = array('ret' => '0', 'amt' => round($amt, 2));
                            echo json_encode($retArr);
                            exit;
                        } else {
                            $transaction->rollBack();
                        }
                    }
                } else {
                    $transaction->rollBack();
                }
            }
        }
        $retArr = array('ret' => '3', 'amt' => 0);
        echo json_encode($retArr);
        exit;
    }

    public function actionLikestat() {
        exit('点赞减息已关闭');
        $this->layout = 'share';
        //$this->layout='loan';
        $this->getView()->title = "点赞减息";
//        //获取借款记录的ID
//        $loan_id = intval($_GET['d']);
//        //获取时间
//        $t = intval($_GET['t']);
//        $s = $_GET['s'];
        $loan_id = Yii::$app->request->get('d');
        $t = Yii::$app->request->get('t');
        $s = Yii::$app->request->get('s');
        if(empty($loan_id) || empty($t) || empty($s)){
            echo '';
            exit;
        }
        $loan_id = intval($loan_id);
        $t = intval($t);
        if ($s == md5($t . $loan_id)) {
            //查询登录用户是否是黑名单用户
            //查询借款记录的信息
            $loaninfo = User_loan::find()->where(['loan_id' => $loan_id])->one();
            //借款用户信息
            $loanusers = User::findOne($loaninfo->user_id);
            $loanuserinfo  = $loanusers;
            $head = null;
            if($loanusers->openid){
                $userwx = (new User_wx())->find()->where(['openid'=>$loanusers->openid])->one();
                if($userwx && $userwx->nickname && $userwx->head){
                    $loanuserinfo['realname'] = $userwx->nickname;
                    $head = $userwx->head;
                }
            }


            //判断借款用户是否是黑名单用户
            if ($loanuserinfo['status'] == 5) {
                //跳转到黑名单错误提示页面
                return $this->redirect('/new/account');
            }

            $Url = urlencode(Yii::$app->request->hostInfo . "/dev/share/likestat?t=" . $t . "&d=" . $loan_id . "&s=" . md5($t . $loan_id));
            $shareUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . self::$_appid . "&redirect_uri=" . $Url . "&response_type=code&scope=snsapi_userinfo&state=xhh123#wechat_redirect";
            $jsinfo = $this->getWxParam();

            //查询借款点赞记录
            $sql_like_list = "select i.like_user_id,i.amount,i.create_time,w.realname,w.openid from " . Loan_like_stat::tableName() . " as i," . User::tableName() . " as w where i.loan_id=$loan_id and  i.type=1 and i.amount !=0 and i.like_user_id=w.user_id";
            $likelist = Yii::$app->db->createCommand($sql_like_list)->queryAll();
            $num = count($likelist);
            if($num > 0){
                $userwxModel = new User_wx();
                foreach ($likelist as $k => $v){
                    if($v['openid']){
                        $userwx = $userwxModel->find()->where(['openid'=>$v['openid']])->one();
                        if($userwx && $userwx->nickname && $userwx->head){
                            $likelist[$k]['realname'] = $userwx->nickname;
                            $likelist[$k]['head'] = $userwx->head;
                        }
                    }
                }
            }
            $total = 0;
            foreach ($likelist as $v) {
                $total+=$v['amount'];
            }

            //查询被踩过的记录
            $sql_cai = "select i.like_user_id,i.amount,i.create_time,w.realname,w.openid from " . Loan_like_stat::tableName() . " as i," . User::tableName() . " as w where i.loan_id=$loan_id and  i.type=2 and i.like_user_id=w.user_id";
            $likelists = Yii::$app->db->createCommand($sql_cai)->queryAll();
            $nums = count($likelists);

            $logininfo = Yii::$app->newDev->identity;
            $user_type = $logininfo['user_type'];
            $los = Loan_like_stat::find()->where(['like_user_id' => $loaninfo->user_id, 'loan_id' => $loan_id, 'type' => 1])->one();
            if (isset($los->id) && !empty($los->id)) {
                $stats = 1;
            } else {
                $stats = 0;
            }

            $loss = Loan_like_stat::find()->where(['like_user_id' => $loaninfo->user_id, 'loan_id' => $loan_id, 'type' => 2])->one();
            if (isset($loss->id) && !empty($loss->id)) {
                $stats1 = 1;
            } else {
                $stats1 = 0;
            }

            //判断登录用户是否是黑名单用户
            if ($logininfo['status'] == 5) {
                //跳转到黑名单错误提示页面
                return $this->redirect('/new/account');
            }
            //剩余小时
            if (floor((strtotime($loaninfo['end_date']) - time()) / 3600) > 24) {
                //大于24小时显示天数
                $leftdays = floor((strtotime($loaninfo['end_date']) - time()) / (3600 * 24));
                $lefttime = 'day';
                return $this->render('likestat', [
                            'loaninfo' => $loaninfo,
                            'loanuserinfo' => $loanuserinfo,
                            'user_type' => $user_type,
                            'likelist' => $likelist,
                            'logininfo' => $logininfo,
                            'shareUrl' => $shareUrl,
                            'jsinfo' => $jsinfo,
                            'num' => $num,
                            'nums' => $nums,
                            'total' => $total,
                            'stats' => $stats,
                            'stats1' => $stats1,
                            'leftdays' => $leftdays,
                            'lefttime' => $lefttime,
                            'head' => $head,
                ]);
            } else {
                $lefttime = 'hour';
                //24小时内，显示时分秒
                if (floor((strtotime($loaninfo['end_date']) - time()) / 3600) > 0 && floor((strtotime($loaninfo['end_date']) - time()) / 3600) < 24) {
                    $lefthour = floor((strtotime($loaninfo['end_date']) - time()) / 3600);
                    if ($lefthour < 10) {
                        $lefthour = '0' . $lefthour;
                    }
                } else {
                    $lefthour = '00';
                }
                //剩余分钟
                if (floor(floor(((strtotime($loaninfo['end_date']) - time()) % 3600) / 60)) > 0) {
                    $leftminute = floor(((strtotime($loaninfo['end_date']) - time()) % 3600) / 60);
                    if ($leftminute < 10) {
                        $leftminute = '0' . $leftminute;
                    }
                } else {
                    $leftminute = '00';
                }
                //剩余秒
                if (((strtotime($loaninfo['end_date']) - time()) % 3600) % 60 >= 0) {
                    $leftseconds = ((strtotime($loaninfo['end_date']) - time()) % 3600) % 60;
                    if ($leftseconds < 10) {
                        $leftseconds = '0' . $leftseconds;
                    }
                } else {
                    $leftseconds = '00';
                }
                return $this->render('likestat', [
                            'loaninfo' => $loaninfo,
                            'loanuserinfo' => $loanuserinfo,
                            'user_type' => $user_type,
                            'likelist' => $likelist,
                            'logininfo' => $logininfo,
                            'lefthour' => $lefthour,
                            'num' => $num,
                            'nums' => $nums,
                            'total' => $total,
                            'stats' => $stats,
                            'stats1' => $stats1,
                            'leftminute' => $leftminute,
                            'leftseconds' => $leftseconds,
                            'shareUrl' => $shareUrl,
                            'jsinfo' => $jsinfo,
                            'lefttime' => $lefttime,
                            'head' => $head,
                ]);
            }
        } else {
            echo '系统错误';
            exit;
        }
    }

    public function actionLoanlikestat_hlz() {
        exit('点赞减息已关闭');
        if (!empty($_POST)) {
            $loan_id = $_POST['loan_id'];
            //wx表中的id
            $like_user_id = $_POST['user_id'];
            $openid = $this->getVal('openid');
            //查询登录用户是否是黑名单用户
            $logininfo = User::find()->select(array('status', 'user_type'))->where(['openid' => $openid])->one();
            if ((!empty($logininfo) && ($logininfo['status'] == 5))) {
                //跳转到黑名单错误提示页面
                $retArr = array('ret' => '4', 'amt' => 0);
                echo json_encode($retArr);
                exit;
            }
            $time = date('Y-m-d H:i:s', time());
            $sql = "insert into " . Loan_like_stat::tableName() . "(loan_id,like_user_id,amount,type,create_time) value($loan_id,$like_user_id,0,2,'$time')";
            $ret = Yii::$app->db->createCommand($sql)->execute();
            if ($ret) {
                $retArr = array('ret' => '1', 'amt' => 0);
                echo json_encode($retArr);
                exit;
            }
        }
        $retArr = array('ret' => '3', 'amt' => 0);
        echo json_encode($retArr);
        exit;
    }

    public function actionLoanlikestat() {
        exit('点赞减息已关闭');
        if (!empty($_POST)) {
            $loan_id = $_POST['loan_id'];
            //wx表中的id
            $like_user_id = $_POST['user_id'];
            $openid = $this->getVal('openid');
            //查询登录用户是否是黑名单用户
            $logininfo = User::find()->select(array('status', 'user_type'))->where(['openid' => $openid])->one();

            $loaninfo = User_loan::find()->select(array('status'))->where(['loan_id' => $loan_id])->one();

            if ((!empty($loaninfo) && ($loaninfo['status'] == 8)) || (!empty($loaninfo) && ($loaninfo['status'] == 11))) {
                $retArr = array('ret' => '12', 'amt' => 0);
                echo json_encode($retArr);
                exit;
            }

            if ((!empty($loaninfo) && ($loaninfo['status'] == 12)) || (!empty($loaninfo) && ($loaninfo['status'] == 13))) {
                $retArr = array('ret' => '13', 'amt' => 0);
                echo json_encode($retArr);
                exit;
            }

            if ((!empty($logininfo) && ($logininfo['status'] == 5))) {
                $time = date('Y-m-d H:i:s', time());
                $sql = "insert into " . Loan_like_stat::tableName() . "(loan_id,like_user_id,amount,type,create_time) value($loan_id,$like_user_id,0,1,'$time')";
                $rets = Yii::$app->db->createCommand($sql)->execute();
                //跳转到黑名单错误提示页面
                $retArr = array('ret' => '4', 'amt' => 0);
                echo json_encode($retArr);
                exit;
            }
            //查看用户是否点过赞
            $Likeinfo = Loan_like_stat::find()->where(['loan_id' => $loan_id, 'like_user_id' => $like_user_id, 'type' => 1])->one();
            if (isset($Likeinfo->id)) {
                //已点完
                $retArr = array('ret' => '1', 'amt' => 0);
                echo json_encode($retArr);
                exit;
            } else {
                $loaninfo = User_loan::find()->where(['loan_id' => $loan_id])->one();
                //查询借款用户是否是黑名单用户
                $loanuserinfo = User::find()->select(array('status'))->where(['user_id' => $loaninfo['user_id']])->one();
                if ($loanuserinfo['status'] == 5) {
                    //跳转到黑名单错误提示页面
                    $time = date('Y-m-d H:i:s', time());
                    $sql = "insert into " . Loan_like_stat::tableName() . "(loan_id,like_user_id,amount,type,create_time) value($loan_id,$like_user_id,0,1,'$time')";
                    $rets = Yii::$app->db->createCommand($sql)->execute();
                    $retArr = array('ret' => '4', 'amt' => 0);
                    echo json_encode($retArr);
                    exit;
                }
                //获取点赞金额
                $clickCount = Loan_like_stat::find()->where(['loan_id' => $loan_id])->count();
                $amt = Http::clickLike($clickCount);

                $loan_coupon_sql = "select l.id,l.limit,l.end_date,l.val,l.status from " . Coupon_list::tableName() . " as l," . Coupon_use::tableName() . " as u where l.id=u.discount_id and u.loan_id=" . $loan_id;
                $loan_coupon = Yii::$app->db->createCommand($loan_coupon_sql)->queryOne();
                if ((!empty($loan_coupon) && ($loan_coupon['val'] == 0) && ($loan_coupon['status'] == 2)) || (!empty($loan_coupon) && $loaninfo['is_calculation'] == 0 && (($loaninfo['interest_fee'] + $loaninfo['withdraw_fee']) <= $loaninfo['coupon_amount'] )) || (!empty($loan_coupon) && $loaninfo['is_calculation'] == 1 && (($loaninfo['interest_fee']) <= $loaninfo['coupon_amount'] ))) {
                    $retArr = array('ret' => '2', 'amt' => 0);
                    echo json_encode($retArr);
                    exit;
                } else {
                    if (!empty($loan_coupon)) {
                        if ($loaninfo['is_calculation'] == 1) {
                            $loaninfo->interest_fee = $loaninfo->interest_fee - $loaninfo['coupon_amount'];
                        }
                    }
                }
                $remainAmt = ($loaninfo->interest_fee / 2 - $loaninfo->like_amount );
                if ($remainAmt <= 0) {
                    $time = date('Y-m-d H:i:s', time());
                    $sql = "insert into " . Loan_like_stat::tableName() . "(loan_id,like_user_id,amount,type,create_time) value($loan_id,$like_user_id,0,1,'$time')";
                    $rets = Yii::$app->db->createCommand($sql)->execute();
                    $retArr = array('ret' => '2', 'amt' => 0);
                    echo json_encode($retArr);
                    exit;
                }
                if ($amt > $remainAmt) {
                    $amt = $remainAmt;
                }
                $time = date('Y-m-d H:i:s', time());
                $transaction = Yii::$app->db->beginTransaction();
                $sql = "insert into " . Loan_like_stat::tableName() . "(loan_id,like_user_id,amount,create_time) value($loan_id,$like_user_id,$amt,'$time')";
                $ret = Yii::$app->db->createCommand($sql)->execute();
                //更新借款已借金额、借款投资记录
                if ($ret) {
                    $loan_sql = "update " . User_loan::tableName() . " set like_amount=" . ($loaninfo->like_amount + $amt) . ",version=" . ($loaninfo->version + 1) . " where loan_id=$loan_id and version=" . $loaninfo->version;
                    $ret_loan = Yii::$app->db->createCommand($loan_sql)->execute();
                    if ($ret_loan) {
                        $transaction->commit();
                        $retArr = array('ret' => '0', 'amt' => $amt);
                        echo json_encode($retArr);
                        exit;
                    } else {
                        $transaction->rollBack();
                    }
                }
            }
        }
        $retArr = array('ret' => '3', 'amt' => 0);
        echo json_encode($retArr);
        exit;
    }

    public function actionInvite() {
        $this->layout = 'loan';
        $this->getView()->title = "邀请熟人";
        $openid = $this->getVal('openid');
        $mobile = $this->getVal('mobile');
        $from = isset($_GET['from']) ? $_GET['from'] : 'weixin';
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
                            $openid = $usinfo["openid"];
//     						$sql_xhh = "select u.mobile from yi_user as u,yi_user_xhh as x where u.openid='$openid' and u.mobile=x.mobile";
//     						$xhhuserinfo = Yii::$app->db->createCommand( $sql_xhh )->queryOne();
//     						if(empty($xhhuserinfo))
//     						{
// 	    						//是否有邀请码
// 	    						$ret = $this->isHaveInvite( $usinfo["openid"]) ;
// 	    						if( !$ret ){
// 	    							return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid='.self::$_appid.'&redirect_uri='.Yii::$app->params['app_url'].'/dev/invite&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect') ;
// 	    						}
//     						}
                        } else {
                            //保存微信用户失败，去出错页面
                            return $this->redirect('/dev/site/error');
                        }
                    } else {
                        $this->setVal('openid', $resultArr['openid']);
                        $openid = $resultArr['openid'];
//     					$sql_xhh = "select u.mobile from yi_user as u,yi_user_xhh as x where u.openid='$openid' and u.mobile=x.mobile";
//     					$xhhuserinfo = Yii::$app->db->createCommand( $sql_xhh )->queryOne();
//     					if(empty($xhhuserinfo))
//     					{
// 	    					//是否有邀请码
// 	    					$ret = $this->isHaveInvite( $resultArr["openid"]) ;
// 	    					if( !$ret ){
// 	    						return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid='.self::$_appid.'&redirect_uri='.Yii::$app->params['app_url'].'/dev/invite&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect') ;
// 	    					}
//     					}
                    }
                } else {
                    //没有取到token值和openid，去错误页面
                    return $this->redirect('/dev/site/error');
                }
            }
            $openid = $this->getVal('openid');
        } else {

            //判断openid和mobile
            if (empty($openid) || empty($mobile)) {
                return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
            }

//     		$sql_xhh = "select u.mobile from yi_user as u,yi_user_xhh as x where u.openid='$openid' and u.mobile=x.mobile";
//     		$xhhuserinfo = Yii::$app->db->createCommand( $sql_xhh )->queryOne();
//     		if(empty($xhhuserinfo))
//     		{
// 	    		//是否有邀请码
// 	    		$ret = $this->isHaveInvite( $openid ) ;
// 	    		if( !$ret ){
// 	    			return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid='.self::$_appid.'&redirect_uri='.Yii::$app->params['app_url'].'/dev/invite&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect') ;
// 	    		}
//     		}
        }
        //验证用户是否手机验证
        $ischeckmobile = $this->isCheckMobile($openid);
        if (!$ischeckmobile) {
            $url = Yii::$app->request->getUrl(); //当前访问url
            $url = urlencode($url);
            return $this->redirect('/dev/reg/login?url=' . $url);
        }
        $jsinfo = $this->getWxParam();
        $userinfo = User::find()->joinWith('userwx', true, 'LEFT JOIN')->where([User::tableName() . '.openid' => $openid])->one();
        if ($userinfo->user_type == 4) {
            $url = Yii::$app->request->getUrl(); //当前访问url
            $url1 = urlencode($url);
            return $this->redirect('/dev/guarantoraccount/act?url=' . $url1);
        }
        //判断用户是否是黑名单用户
        if ($userinfo['status'] == 5) {
            //如果是黑名单用户则直接跳转到黑名单用户页面
            return $this->redirect('/dev/account/black');
        }
        $time = time();
        $shareUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . self::$_appid . "&redirect_uri=" . urlencode(Yii::$app->request->hostInfo . "/dev/share/myinvite?u=" . $userinfo->user_id . "&t=" . $time . "&s=" . md5($time . $userinfo->user_id)) . "&response_type=code&scope=snsapi_userinfo&state=xhh123#wechat_redirect";
        return $this->render('invite', ['userinfo' => $userinfo, 'jsinfo' => $jsinfo, 'shareurl' => $shareUrl, 'from' => $from]);
    }

    public function actionFreecoupon() {
        $this->layout = 'loan';
        $this->getView()->title = "我的分享";
        $loan_id = intval($_GET['loan_id']);
        $user_id = intval($_GET['uid']);
        if (!empty($user_id)) {
            $loaninfo = User_loan::find()->where(['loan_id' => $loan_id])->one();
            $userinfo = User::find()->joinWith('userwx', true, 'LEFT JOIN')->where([User::tableName() . '.user_id' => $user_id])->one();
            $jsinfo = $this->getWxParam();
            //获取登录用户的信息
            $openid = $this->getVal('openid');
            $sql = "select w.id,w.nickname,w.head,u.user_id from " . Userwx::tableName() . " as w left join " . User::tableName() . " as u on w.openid=u.openid where w.openid='$openid'";
            $logininfo = Yii::$app->db->createCommand($sql)->queryOne();
            if ($openid != $userinfo->openid) {
                $user_click = new User_share_click();
                $array_click = array(
                    'user_id' => $user_id,
                    'loan_id' => $loan_id,
                    'click_id' => $logininfo['id'],
                    'type' => 4,
                );
                $user_click->createClick($array_click);
            }
            $shareurl = Yii::$app->request->hostInfo . "/dev/share/freecoupon?uid=$user_id&loan_id=$loan_id";
            $shareUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . self::$_appid . "&redirect_uri=" . urlencode($shareurl) . "&response_type=code&scope=snsapi_userinfo&state=xhh123#wechat_redirect";
            return $this->render('freecouponnew', ['userinfo' => $userinfo, 'jsinfo' => $jsinfo, 'shareurl' => $shareUrl, 'loaninfo' => $loaninfo, 'logininfo' => $logininfo]);
        } else {
            echo '系统错误';
            exit;
        }
    }

    public function actionMyinvite() {
        $this->layout = 'loan';
        $this->getView()->title = "我的邀请码";
        //获取借款记录的ID
        $user_id = intval($_GET['u']);
        //获取时间
        $t = intval($_GET['t']);
        $s = $_GET['s'];
        $from = isset($_GET['from']) ? $_GET['from'] : 'account';
        if ($s == md5($t . $user_id)) {
            //获取用户信息
            $userinfo = User::find()->joinWith('userwx', true, 'LEFT JOIN')->where(['user_id' => $user_id])->one();
            $click_openid = $this->getVal('openid');
            if ($userinfo['openid'] != $click_openid) {
                $user_click = new User_share_click();
                $click_id = Userwx::find()->select('id')->where(['openid' => $click_openid])->one();
                $array_click = array(
                    'user_id' => $user_id,
                    'click_id' => $click_id['id'],
                    'type' => 3,
                );
                $user_click->createClick($array_click);
            }
            if ($userinfo->user_type == 4) {
                $url = Yii::$app->request->getUrl(); //当前访问url
                $url1 = urlencode($url);
                return $this->redirect('/dev/guarantoraccount/act?url=' . $url1);
            }
            //判断用户是否是黑名单用户
            if ($userinfo['status'] == 5) {
                //如果是黑名单用户则直接跳转到黑名单用户页面
                return $this->redirect('/dev/account/black');
            }
            $jsinfo = $this->getWxParam();
            $time = time();
            $url = urlencode(Yii::$app->request->hostInfo . "/dev/share/myinvite?u=" . $user_id . "&t=" . $time . "&s=" . md5($time . $user_id));
            $shareUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . self::$_appid . "&redirect_uri=" . $url . "&response_type=code&scope=snsapi_userinfo&state=xhh123#wechat_redirect";

            return $this->render('invite', ['userinfo' => $userinfo, 'jsinfo' => $jsinfo, 'shareurl' => $shareUrl, 'from' => $from]);
        } else {
            echo '系统错误';
            exit;
        }
    }

    public function actionError() {
        return $this->render('error');
    }

    public function actionShare() {
        $this->layout = 'loan';
        $jsinfo = $this->getWxParam();
        $user_id = isset($_GET['open_id']) ? $_GET['open_id'] : "";
        $type = isset($_GET['type']) ? $_GET['type'] : '';
        $userinfo = User::find()->select(array('user_id', 'realname', 'status'))->where(['openid' => $user_id])->one();
        //判断用户是否是黑名单用户
        if ($userinfo['status'] == 5) {
            //如果是黑名单用户则直接跳转到黑名单用户页面
            return $this->redirect('/dev/account/black');
        }
        $this->getView()->title = "分享";
        $url = urlencode(Yii::$app->params['app_url'] . '/dev/share/comefrom?uid=' . $userinfo->user_id);
        $shareUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . self::$_appid . "&redirect_uri=" . $url . "&response_type=code&scope=snsapi_userinfo&state=xhh123#wechat_redirect";
        return $this->render('share', [
                    'shareUrl' => $shareUrl,
                    'userinfo' => $userinfo,
                    'user_id' => $user_id,
                    'type' => $type,
                    'jsinfo' => $jsinfo
        ]);
    }

    public function actionComefrom() {
        $openid = $this->getVal('openid');
        $mobile = $this->getVal('mobile');
//        $user_id = intval($_GET['uid']);
        $user_id = Yii::$app->request->get('uid',0);
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

        if (empty($openid)) {
            return $this->redirect('/dev/share/comefrom?uid =' . $user_id);
        }
        $from_user = User::find()->select('openid')->where(['user_id' => $user_id])->one();
        if (empty($from_user) || $from_user->openid != $openid) {
            $user_click = new User_share_click();
            $click_id = Userwx::find()->select('id')->where(['openid' => $openid])->one();
            $array_click = array(
                'user_id' => $user_id,
                'click_id' => $click_id['id'],
                'type' => 6,
            );
            $user_click->createClick($array_click);
        }
        //判断用户是否注册
        $userinfo = User::find()->where(['openid' => $openid])->one();
        //判断用户是否是黑名单用户
        if ($userinfo['status'] == 5) {
            //如果是黑名单用户则直接跳转到黑名单用户页面
            return $this->redirect('/dev/account/black');
        }
        if (empty($userinfo)) {
            $isAtten = $this->isAtten($openid);
            if (!$isAtten) {
                return $this->redirect('http://mp.weixin.qq.com/s?__biz=MzA4OTM2NTU5NQ==&mid=203536992&idx=1&sn=682dd78456a5d0cd8e843b0a14243389#rd');
            } else {
                return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
            }
        } else {
            if (empty($openid) || empty($mobile)) {
                return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
            }

            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/invest&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
    }

    public function actionGetcoupon() {
        $this->layout = 'new_invest';
        $this->getView()->title = "领取优惠券";
        $from_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : '';
        $standard_id = isset($_GET['standard_id']) ? intval($_GET['standard_id']) : '';
        $openid = $this->getVal('openid');
        if (empty($from_user_id) || empty($standard_id) || empty($openid)) {
            echo '系统错误';
            exit;
        }
        $userinfo = User::find()->where(['openid' => $openid])->one();
        if ($userinfo->user_id == $from_user_id) {
            return $this->redirect('/dev/standard/couponsuccess?type=my&user_id=' . $from_user_id . '&standard_id=' . $standard_id . '&mobile=' . $userinfo->mobile);
        } else {
            $standard_information = Standard_information::findOne($standard_id);
            $jsinfo = $this->getWxParam();
            return $this->render('getcoupon', [
                        'jsinfo' => $jsinfo,
                        'from_user_id' => $from_user_id,
                        'standard_id' => $standard_id,
                        'standard_information' => $standard_information,
                        'userinfo' => $userinfo
            ]);
        }
    }

}
