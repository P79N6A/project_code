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
use app\models\dev\Coupon_list;
use app\models\dev\Coupon_use;
use app\models\dev\Favorite_contacts;
use app\models\dev\Fraudmetrix_return_info;
use app\models\dev\Guarantee_card_order;
use app\models\dev\Juxinli;
use app\models\dev\Loan_event;
use app\models\dev\Loan_record;
use app\models\dev\Loan_renew_user;
use app\models\dev\Loan_repay;
use app\models\dev\Payaccount;
use app\models\dev\Rate;
use app\models\dev\Renewal_payment_record;
use app\models\dev\Scan_times;
use app\models\dev\Statistics;
use app\models\dev\User;
use app\models\dev\User_auth;
use app\models\dev\User_bank;
use app\models\dev\User_guarantee_loan;
use app\models\dev\User_guarantee_school;
use app\models\dev\User_invest;
use app\models\dev\User_loan;
use app\models\news\User_loan_extend;
use app\models\dev\User_loan_flows;
use app\models\dev\User_password;
use app\models\dev\User_remit_list;
use app\models\dev\Userwx;
use app\models\dev\White_list;
use app\models\Flow;
use app\models\yyy\XhhApi;
use Yii;

class LoanController extends SubController {

    public $layout = 'main';
    public $enableCsrfValidation = false;

    public function actionIndex() {
        $this->layout = "loan";
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
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }

        return $this->redirect('/new/loan');
        $this->getView()->title = "借款";
        //1.验证用户是否手机验证
        $ischeckmobile = $this->isCheckMobile($openid);
        if (!$ischeckmobile) {
            return $this->redirect('/dev/reg/login');
        }

        //判断用户是否存在进行中的借款
        $userinfo = User::find()->where(['openid' => $openid])->one();
        /*         * *************记录访问日志beigin******************* */
        $ip = Common::get_client_ip();
        $result_log = Common::saveLog('loan', 'loan_menu', $ip, 'weixin', $userinfo->user_id);
        /*         * *************记录访问日志end******************* */
        if ($userinfo->user_type == 4) {
            $url = Yii::$app->request->getUrl(); //当前访问url
            $url = urlencode($url);
            return $this->redirect('/dev/sponsor/index?url=' . $url);
        } else if ($userinfo->user_type == 0) {
            $url = Yii::$app->request->getUrl(); //当前访问url
            $url = urlencode($url);
            return $this->redirect('/dev/reg/sfen?url=' . $url);
        }

        //判断是否被认证3次以上
        $authModel = new User_auth();
        $userIds = $authModel->getAuthByUserId($userinfo->user_id);
        $is_auth = count($userIds);

        $status = array('5', '6', '9', '10', '11', '12', '13'); //如果用户存在借款状态为1、2、5、6、8
        $User_loan = User_loan::find()->where(['user_id' => $userinfo->user_id, 'status' => $status, 'business_type' => array(1, 3, 4)])->one();
        $loan_status = 0;
        if (isset($User_loan->loan_id)) {
            return $this->redirect('/dev/loan/succ?l=' . $User_loan->loan_id);
        } else {
            $User_loan = User_loan::find()->where(['user_id' => $userinfo->user_id, 'prome_status' => 1])->one();
            //return $this->redirect('/dev/loan/succ?l=' . $User_loan->loan_id);
            if (!empty($User_loan)) {
                $isexist = 1;
                $loan_status = $User_loan->status;
            } else {
                $isexist = 0;
            }
        }
        $show = (new Scan_times())->getScanCount($userinfo->mobile, 21, 1);

        if (!empty($show)) {
            $is_show = 0;
        } else {
            $is_show = 1;
        }

        $accountinfo = Account::find()->where(['user_id' => $userinfo->user_id])->one();
        $user_loaninfo = User_loan::find()->where(['user_id' => $userinfo->user_id, 'status' => $status, 'business_type' => 2])->all();
        $total = 0;
        foreach ($user_loaninfo as $v) {
            $total += $v->amount;
        }

        //用户的充值额度
        $recharge_amount = $accountinfo->recharge_amount;

        //担保额度最后计算结果number_format("50000",2,".","");
        $dtotal = number_format($recharge_amount - ($total / 0.99), 2, ".", "");
        //echo $dtotal;exit;
        if ($dtotal < '100.0000') {
            $exist = 0;
        } else {
            $exist = 1;
        }

        //这里判断用户有没有借机卡
        $user_banks = User_bank::find()->where(['user_id' => $userinfo->user_id, 'type' => 0, 'status' => 1])->all();
        if (empty($user_banks)) {
            $is_bank = 1;
        } else {
            $is_bank = 0;
        }

        //判断该用户是否有优惠券
        $nowtime = date('Y-m-d H:i:s');
        $couponlist = array();
        $couponVal = 0;
        $coupon[0] = Coupon_list::find()->select(array('id', 'title', 'type', 'val', 'limit', 'end_date', 'status'))->where(['mobile' => $mobile, 'status' => 1, 'val' => 0])->andWhere("start_date < '$nowtime' and end_date > '$nowtime'")->orderBy('end_date desc')->all();
        $coupon[1] = Coupon_list::find()->select(array('id', 'title', 'type', 'val', 'limit', 'end_date', 'status'))->where(['mobile' => $mobile, 'status' => 1])->andWhere("val>0")->andWhere("start_date < '$nowtime' and end_date > '$nowtime'")->orderBy('val desc,end_date desc')->all();
        foreach ($coupon as $key => $v) {
            if (!empty($v)) {
                $couponlist = array_merge($couponlist, $v);
            } else {
                continue;
            }
        }
        $couponVal = !empty($couponlist) ? ($couponlist[0]['val'] == 0 ? '-1' : $couponlist[0]['val']) : 0;

        $maxAmount = $userinfo->getUserLoanAmount($userinfo);
        if ($maxAmount >= 1000) {
            $value = 1000;
        } else {
            $value = 500;
        }
        if ($couponVal == -1) {
            $repay = $value;
        } else {
            $repay = $value * 0.0005 * 28 > $couponVal ? $value * 0.0005 * 28 - $couponVal + $value : $value;
        }
        $desc = Keywords::getLoanDesc();
        $jsinfo = $this->getWxParam();

        //判断活动弹窗是否显示
        $active_show = (new Scan_times())->isShow("2017-04-27 11:00:00", 15, $mobile, 18, 19);
        $active_show = $active_show ? 1 : 0;
        return $this->render('index', [
                    'user_status' => $userinfo->status,
                    'loan_status' => $loan_status,
                    'couponlist' => $couponlist,
                    'loandesc' => $desc,
                    'userinfo' => $userinfo,
                    'is_bank' => $is_bank,
                    'isexist' => $isexist,
                    'exist' => $exist,
                    'loan_id' => $User_loan['loan_id'],
                    'dayratestr' => 0.0005,
                    'is_auth' => $is_auth,
                    'current_amount' => $accountinfo->current_amount,
                    'maxAmount' => $maxAmount,
                    'repay' => $repay,
                    'value' => $value,
                    'is_show' => $is_show,
                    'jsinfo' => $jsinfo,
                    'active_show' => $active_show
        ]);
    }

    /*
     * 点击担保卡借款 跳转的控制器 方法
     */

    public function actionBorrowing() {
        $this->getView()->title = "担保卡借款";
        $this->layout = 'borrow';
        $mobile = $this->getVal('mobile');
        $openid = $this->getVal('openid');
        if (empty($openid)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $userinfo = User::find()->where(['openid' => $openid])->one();
        $user_id = $userinfo->user_id;

        //判断用户是否存在进行中的借款
        $status = array('1', '2', '5', '6', '9', '10', '11', '12', '13'); //如果用户存在借款状态为1、2、5、6、8
        $User_loan = User_loan::find()->where(['user_id' => $user_id, 'status' => $status, 'business_type' => 2])->one();
        if (isset($User_loan->loan_id)) {
            $isexist = 1;
        } else {
            $isexist = 0;
        }

        //这里判断用户有没有借机卡
        $user_banks = User_bank::find()->where(['user_id' => $user_id, 'type' => 0, 'status' => 1])->all();
        //print_r($user_banks);exit;
        if (empty($user_banks)) {
            $is_bank = 1;
        } else {
            $is_bank = 0;
        }
        if (empty($_POST)) {
//            $status = array(1, 2, 5, 6, 9, 10, 11, 12, 13);
            $accountinfo = Account::find()->where(['user_id' => $user_id])->one();
//             $user_loaninfo = User_loan::find()->where(['user_id' => $user_id, 'status' => $status, 'business_type' => 2])->all();
//             $total = 0;
//             foreach ($user_loaninfo as $v) {
//                 $total += $v->amount;
//             }
            //用户的充值额度
//             $recharge_amount = $accountinfo->recharge_amount;
            //担保额度最后计算结果number_format("50000",2,".","");
            // $dtotal = number_format($recharge_amount - ($total/0.99),2,".","");
//             $dtotal = floor($recharge_amount - ($total / 0.99));
            //用户担保额度
            //$guarantee_amount = number_format($dtotal*0.99,2,".","" );
//             $guarantee_amount = floor($dtotal * 0.99);
            //担保额度==========================================
            $dtotal = number_format($accountinfo->real_guarantee_amount, 2, ".", "");
            $guarantee_amount = floor($dtotal * 0.99);
            //================================================
            $jsinfo = $this->getWxParam();
            return $this->render('borrowing', ['dtotal' => $dtotal, 'is_bank' => $is_bank, 'guarantee_amount' => $guarantee_amount, 'jsinfo' => $jsinfo, 'isexist' => $isexist, 'user_id' => $user_id]);
        } else {
            print_r($_POST);
        }
    }

    public function actionQd() {
        $this->newyear('/dev/loan');
        //print_r($_GET);
        $this->getView()->title = "借款确认";
        $this->layout = 'borrow';
        $mobile = $this->getVal('mobile');
        $openid = $this->getVal('openid');
        if (empty($openid)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }

        if (!empty($_GET['desc']) && !empty($_GET['amount']) && !empty($_GET['amount'])) {
            $desc = $_GET['desc'];
            $amount = $_GET['amount'];
            //$onoffswitch = $_GET['onoffswitch'];
            $days = $_GET['days'];

            if ($days == 'on') {
                $days = 1;
            }
            $userinfo = User::find()->where(['openid' => $openid])->one();
            $user_id = $userinfo->user_id;
            $mobile = $userinfo->mobile;
            // echo $user_id;exit;
            $user_bankinfo = User_bank::find()->where(['user_id' => $user_id, 'status' => 1, 'default_bank' => 1, 'type' => 0])->one();
            //var_dump($user_bankinfo);exit;
            if (empty($user_bankinfo)) {
                $user_bankinfo = User_bank::find()->where(['user_id' => $user_id, 'status' => 1, 'type' => 0])->orderby('create_time')->one();
                //print_r($user_bankinfo);exit;
                if (empty($user_bankinfo) && !isset($user_bankinfo)) {
                    return $this->redirect("/dev/bank/index");
                }
            }

            $user_bankinfo1 = User_bank::find()->where(['user_id' => $user_id, 'status' => 1, 'type' => 0])->all();

            $User_loan = Scan_times::find()->where(['mobile' => $mobile, 'type' => 2])->one();
            if (empty($User_loan)) {
                $isexist = 1;
            } else {
                //0为取消过
                $isexist = 0;
            }
            $status = array(1, 2, 5, 6, 9, 10, 11, 12, 13);
            $accountinfo = Account::find()->where(['user_id' => $user_id])->one();
            $user_loaninfo = User_loan::find()->where(['user_id' => $user_id, 'status' => $status, 'business_type' => 2])->all();
            $total = 0;
            foreach ($user_loaninfo as $v) {
                $total += $v->amount;
            }

            //用户的充值额度
//             $recharge_amount = $accountinfo->recharge_amount;
            //担保额度最后计算结果number_format("50000",2,".","");
            //$dtotal = number_format($recharge_amount - ($total/0.99),2,".","");
//             $dtotal = floor($recharge_amount - ($total / 0.99));
            $dtotal = number_format($accountinfo->real_guarantee_amount, 2, ".", "");
            //用户担保额度
            //$guarantee_amount = number_format($dtotal*0.99,2,".","" );
            $guarantee_amount = floor($dtotal * 0.99);
            $jsinfo = $this->getWxParam();
            return $this->render('qd', ['desc' => $desc, 'guarantee_amount' => $guarantee_amount, 'isexist' => $isexist, 'days' => $days, 'amount' => $amount, 'user_bankinfo' => $user_bankinfo, 'user_bankinfo1' => $user_bankinfo1, 'jsinfo' => $jsinfo]);
        } else {
            return $this->redirect('/dev/loan/borrowing');
        }
    }

    //ajax请求插入数据库
    public function actionAjax() {
        $openid = $this->getVal('openid');
        $mobile = $this->getVal('mobile');
        $users = User::find()->where(['openid' => $openid])->one();
        $mobile = $users['mobile'];
        $type = 2;
        $create_time = date('Y-m-d H:i:s', time());
        $sql = "insert into " . Scan_times::tableName() . "(mobile,type,create_time) ";
        $sql .= "value('$mobile','$type','$create_time')";

        /* $retinsert = Yii::$app->db->createCommand($sql)->execute();
          if($retinsert){
          echo '成功';
          }else{
          echo '失败';
          } */
        $sacn = Scan_times::find()->where(['mobile' => $mobile])->all();
        //echo $create_time;exit;
        if (empty($sacn)) {
            $sql = "insert into " . Scan_times::tableName() . "(mobile,type,create_time) ";
            $sql .= "value('$mobile','$type','$create_time')";
            //echo $sql;exit;
            $retinsert = Yii::$app->db->createCommand($sql)->execute();
            if ($retinsert) {
                echo 1;
            } else {
                echo 0;
            }
        }
    }

    //提交 页面 写入数据库
    public function actionQued() {
//        print_r($_POST);exit;

        $ip = Common::get_client_ip();
        $mobile = $this->getVal('mobile');
        $openid = $this->getVal('openid');
        $userinfo = User::find()->joinWith('account', true, 'LEFT JOIN')->where(['openid' => $openid])->one();
        // $user_id = $userinfo->user_id;

        $user_id = $userinfo->user_id;
        $status1 = array(1, 2, 5, 6, 9, 10, 11, 12, 13);
        $accountinfo = Account::find()->where(['user_id' => $user_id])->one();
        $user_loaninfo = User_loan::find()->where(['user_id' => $user_id, 'status' => $status1, 'business_type' => 2])->all();
        $total = 0;
        foreach ($user_loaninfo as $v) {
            $total += $v->amount;
        }

        //用户的充值额度
//         $recharge_amount = $accountinfo->recharge_amount;
        //担保额度最后计算结果number_format("50000",2,".","");
//         $dtotal = number_format($recharge_amount - ($total / 0.99), 2, ".", "");
        $dtotal = number_format($accountinfo->real_guarantee_amount, 2, ".", "");
        $desc = $_POST['desc'];
        $days = $_POST['days'];
        $amount = $_POST['amount'];

        if ($amount > $dtotal) {
            return $this->redirect('/dev/loan/borrowing');
        }
        $bank_id = $_POST['bank_id'];
        //$user_ids = $_POST['user_ids'];
        //echo $user_ids;exit;
        $is_d = isset($_POST['is_d']) ? $_POST['is_d'] : 0;
        if ($is_d == 1) {
            //写入数据库
            $users = User::find()->where(['openid' => $openid])->one();
            $mobile = $users['mobile'];
            $type = 2;
            $create_time = date('Y-m-d H:i:s', time());

            $sacn = Scan_times::find()->where(['mobile' => $mobile, 'type' => 2])->all();

            if (empty($sacn)) {
                $sql = "insert into " . Scan_times::tableName() . "(mobile,type,create_time) ";
                $sql .= "value('$mobile','$type','$create_time')";
                Yii::$app->db->createCommand($sql)->execute();
            }
        }
        $open_start_date = date('Y-m-d H:i:s', time());
        $open_end_date = date('Y-m-d H:i:s', time());
        $create_time = date('Y-m-d H:i:s', time());
        $last_modify_time = date('Y-m-d H:i:s', time());
        $withdraw_time = date('Y-m-d H:i:s', time());
        $current_amount = $amount;
        $type = 2;
        $status = 6;
        $version = 1;
        $business_type = 2;
        $loan_sql = "insert into " . User_loan::tableName() . "(user_id,version,business_type,amount,current_amount,days,open_start_date,open_end_date,type,status,`desc`,create_time,last_modify_time,bank_id,withdraw_time) value";
        $loan_sql .= "($user_id,$version,$business_type,$amount,$current_amount,$days,'$open_start_date','$open_end_date',$type,$status,'$desc','$create_time','$last_modify_time','$bank_id','$withdraw_time')";
        //echo $loan_sql;
        $transaction = Yii::$app->db->beginTransaction();
        $ret = Yii::$app->db->createCommand($loan_sql)->execute();
        if ($ret) {
            //$transaction->commit();
            $loan_id = Yii::$app->db->getLastInsertID();
            $loan_extendModel = new User_loan_extend();
            $extend_condition = [
                'user_id' => $user_id,
                'loan_id' => $loan_id,
                'outmoney' => 1,
                'payment_channel' => 2,
                'extend_type' => 2,
                'userIp' => $ip,
            ];
            $loan_extendModel->addList($extend_condition);
            $suffix = $loan_id;
            $size = 6;
            for ($i = 1; $i < $size; $i++) {
                if (strlen($suffix) < $size)
                    $suffix = '0' . $suffix;
            }
            $loan_no = date("Ymd") . $suffix;
            $sql_loan_no = "update " . User_loan::tableName() . " set loan_no='$loan_no' where loan_id=" . $loan_id;
            Yii::$app->db->createCommand($sql_loan_no)->execute();
            //创建订单成功 记录日志
            $loan_flows_sql = "insert into " . Flow::tableName() . " (loan_id,admin_id,loan_status,create_time) value($loan_id,0,$status,'$create_time')";
            $ret_flows = Yii::$app->db->createCommand($loan_flows_sql)->execute();

            if ($ret_flows) {
                //修改默认银行卡
                if (isset($bank_id) && !empty($bank_id)) {
                    $sql_bank = "update " . User_bank::tableName() . " set default_bank=0 where user_id=" . $user_id;

                    $ret_bank = Yii::$app->db->createCommand($sql_bank)->execute();
                    if ($ret_bank >= 0) {
                        $sql = "update " . User_bank::tableName() . " set default_bank=1 where id=" . $bank_id;
                        Yii::$app->db->createCommand($sql)->execute();
                    }
                }
                //更新担保卡可用额度和担保卡购买记录剩余额度==============================================
                $realAmount = round($amount / 0.99);
                $sql_account = "update " . Account::tableName() . " set real_guarantee_amount=(real_guarantee_amount-$realAmount) where user_id=" . $user_id;
                $ret_account = Yii::$app->db->createCommand($sql_account)->execute();
                //修改购买记录里剩余的担保额度=========================
                $cardList = Guarantee_card_order::find()->where("user_id=$user_id and status=1 and remain_amount > 0")->orderBy('create_time asc')->all();
                $allAmount = $realAmount;
                $countRet = 1;
                if (!empty($cardList)) {
                    while ($allAmount > 0) {
                        if (!$countRet) {
                            break;
                        }
                        foreach ($cardList as $val) {
                            if ($val->remain_amount > $allAmount) {
                                $cardsql = "update " . Guarantee_card_order::tableName() . " set remain_amount=remain_amount-$allAmount where id=" . $val->id;
                                $cardret = Yii::$app->db->createCommand($cardsql)->execute();
                                if ($cardret >= 0) {
                                    $countRet = 1;
                                } else {
                                    $countRet = 0;
                                }
                                $allAmount = 0;
                                break;
                            } else {
                                $cardsql = "update " . Guarantee_card_order::tableName() . " set remain_amount=0 where id=" . $val->id;
                                $cardret = Yii::$app->db->createCommand($cardsql)->execute();
                                if (!$cardret) {
                                    $countRet = 0;
                                    break;
                                }
                                $allAmount = $allAmount - $val->remain_amount;
                            }
                        }
                    }
                }
                //==============================================================================
                if ($countRet) {
                    $transaction->commit();
                    return $this->redirect("/dev/loan/qsuccess?amount=$amount&&bank_id=$bank_id");
                } else {
                    $transaction->rollBack();
                    return $this->redirect('/dev/loan/borrowing');
                }
            } else {
                $transaction->rollBack();
                return $this->redirect('/dev/loan/borrowing');
            }
        } else {
            $transaction->rollBack();
            return $this->redirect('/dev/loan/borrowing');
        }
    }

    public function actionQsuccess() {
        //return $this->redirect('/app/loan/qsuccess');
        $this->getView()->title = "借款成功";
        $this->layout = 'borrow';
        $openid = $this->getVal('openid');
        $userinfo = User::find()->where(['openid' => $openid])->one();
        $user_id = $userinfo->user_id;
        $amount = $_GET['amount'];
        $bank_id = $_GET['bank_id'];
        //echo $bank_id;exit;
        $status = array(1, 2, 5, 6, 9, 10, 11, 12, 13);
        $accountinfo = Account::find()->where(['user_id' => $user_id])->one();
        $user_loaninfo = User_loan::find()->where(['user_id' => $user_id, 'status' => $status, 'business_type' => 2])->all();
        $total = 0;
        foreach ($user_loaninfo as $v) {
            $total += $v->amount;
        }

        //用户的充值额度
//         $recharge_amount = $accountinfo->recharge_amount;
        //担保额度最后计算结果number_format("50000",2,".","");
        //$dtotal = number_format($recharge_amount - ($total/0.99),2,".","");
//         $dtotal = floor($recharge_amount - ($total / 0.99));
        $dtotal = number_format($accountinfo->real_guarantee_amount, 2, ".", "");
        //用户担保额度
        //$guarantee_amount = number_format($dtotal*0.99,2,".","" );
        $guarantee_amount = floor($dtotal * 0.99);
        $user_bankinfo = User_bank::find()->where(['id' => $bank_id])->one();
        //print_r($user_bankinfo);exit;
        $jsinfo = $this->getWxParam();
        return $this->render('qsuccess', ['amount' => $amount, 'gu' => $guarantee_amount, 'jsinfo' => $jsinfo, 'user_bankinfo' => $user_bankinfo]);
    }

    public function actionQhelp() {
        $this->getView()->title = "担保卡借款";
        $this->layout = 'borrow';
        $jsinfo = $this->getWxParam();
        return $this->render('qhelp', ['jsinfo' => $jsinfo]);
    }

    private function newyear($url) {
        $startTime = Yii::$app->params['newyear_start_time'];
        $endTime = Yii::$app->params['newyear_end_time'];
        $time = time();
        if ($time >= $startTime && $time <= $endTime) {
            $this->redirect($url);
        }
    }

    //没有担保卡 跳转的页面
    public function actionMdbk() {
        $this->newyear('/dev/loan/borrowing');
        $this->getView()->title = "担保卡借款-无担保卡";
        $this->layout = 'borrow';
        $openid = $this->getVal('openid');
        $userinfo = User::find()->where(['openid' => $openid])->one();
        $user_id = $userinfo->user_id;
        if (empty($openid)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $status = array(1, 2, 5, 6, 9, 10, 11, 12, 13);
        $accountinfo = Account::find()->where(['user_id' => $user_id])->one();
        $user_loaninfo = User_loan::find()->where(['user_id' => $user_id, 'status' => $status, 'business_type' => 2])->all();
        $total = 0;
        foreach ($user_loaninfo as $v) {
            $total += $v->amount;
        }

        //用户的充值额度
//         $recharge_amount = $accountinfo->recharge_amount;
        //担保额度最后计算结果number_format("50000",2,".","");
        //$dtotal = number_format($recharge_amount - ($total/0.99),2,".","");
//         $dtotal = floor($recharge_amount - ($total / 0.99));
        $dtotal = number_format($accountinfo->real_guarantee_amount, 2, ".", "");
        //echo $dtotal;exit;
        if ($dtotal <= 0) {
            $dtotal = 0;
        }
        //用户担保额度
        // $guarantee_amount = number_format($dtotal*0.99,2,".","" );
        $guarantee_amount = floor($dtotal * 0.99);
        $gstatus = Guarantee_card_order::find()->where(['user_id' => $user_id])->all();
        if (empty($gstatus)) {
            $gstatus = 1;
        } else {
            $gstatus = 0;
        }
        //echo $gstatus;exit;
        $jsinfo = $this->getWxParam();
        return $this->render('mdbk', ['jsinfo' => $jsinfo, 'dtotal' => $dtotal, 'guarantee_amount' => $guarantee_amount, 'status' => $gstatus]);
    }

    //没有担保卡 一会去的跳转页面
    public function actionMemen() {
        $this->newyear('/dev/loan');
        $this->getView()->title = "担保卡借款-无担保卡";
        $this->layout = 'borrow';
        $mobile = $this->getVal('mobile');
        $openid = $this->getVal('openid');
        $userinfo = User::find()->where(['openid' => $openid])->one();
        $user_id = $userinfo->user_id;
        if (empty($openid)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $status = array(1, 2, 5, 6, 9, 10, 11, 12, 13);
        $accountinfo = Account::find()->where(['user_id' => $user_id])->one();
        $user_loaninfo = User_loan::find()->where(['user_id' => $user_id, 'status' => $status, 'business_type' => 2])->all();
        $total = 0;
        foreach ($user_loaninfo as $v) {
            $total += $v->amount;
        }

        //用户的充值额度
//         $recharge_amount = $accountinfo->recharge_amount;
        //担保额度最后计算结果number_format("50000",2,".","");
        //$dtotal = number_format($recharge_amount - ($total/0.99),2,".","");
//         $dtotal = floor($recharge_amount - ($total / 0.99));
        $dtotal = number_format($accountinfo->real_guarantee_amount, 2, ".", "");
        if ($dtotal <= 0) {
            $dtotal = 0;
        }
        //用户担保额度
        //$guarantee_amount = number_format($dtotal*0.99,2,".","" );
        $guarantee_amount = floor($dtotal * 0.99);
        $jsinfo = $this->getWxParam();
        return $this->render('memen', ['jsinfo' => $jsinfo, 'dtotal' => $dtotal, 'guarantee_amount' => $guarantee_amount]);
    }

    public function actionSecond() {
        $rand_num = rand(1000, 9999);
        $ip = Common::get_client_ip();
        $this->layout = "loan";
        $openid = $this->getVal('openid');
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

        $desc = isset($_POST['desc']) ? $_POST['desc'] : $this->getCookieVal('loan_desc');
        $days = isset($_POST['day']) ? $_POST['day'] : $this->getCookieVal('loan_days');
        $amount = isset($_POST['amount']) ? $_POST['amount'] : $this->getCookieVal('loan_amount');
        $coupon_id = isset($_POST['coupon_id']) ? $_POST['coupon_id'] : $this->getCookieVal('coupon_id');
        $coupon_amount = isset($_POST['coupon_amount']) ? $_POST['coupon_amount'] : $this->getCookieVal('coupon_amount');
        //把借款信息存到cookie里
        if (isset($_POST['desc'])) {
            $this->setCookieVal('loan_desc', $desc);
        }
        if (isset($_POST['day'])) {
            $this->setCookieVal('loan_days', $days);
        }
        if (isset($_POST['amount'])) {
            $this->setCookieVal('loan_amount', $amount);
        }
        if (isset($_POST['coupon_id'])) {
            $this->setCookieVal('coupon_id', $coupon_id);
        }
        if (isset($_POST['coupon_amount'])) {
            $this->setCookieVal('coupon_amount', $coupon_amount);
        }
        //2.判断用户信息是否完善（如果是大学生验证是否学籍验证，如果是上班族是否完善资料）
        $userinfo = User::find()->where(['openid' => $openid])->one();
        /*         * *************记录访问日志beigin******************* */
        $ip = Common::get_client_ip();
        $result_log = Common::saveLog('loan', 'loan_button', $ip, 'weixin', $userinfo->user_id);
        /*         * *************记录访问日志end******************* */
        //判断用户是否是黑名单用户
        if ($userinfo['status'] == 5) {
            //如果是黑名单用户则直接跳转到黑名单用户页面
            return $this->redirect('/dev/account/black');
        }
        if ($userinfo->realname == '' || ($userinfo->identity_valid != 2 && $userinfo->identity_valid != 4) || empty($userinfo->extend) || empty($userinfo->extend->marriage)) {
            return $this->redirect('/dev/reg/personals?user_id=' . $userinfo->user_id . '&url=/dev/loan/second');
        } else if (empty($userinfo->extend) || empty($userinfo->extend->company_area)) {
            return $this->redirect('/dev/reg/company?user_id=' . $userinfo->user_id . '&url=/dev/loan/second');
        } else if ($userinfo->pic_identity == '' || ($userinfo->pic_identity != '' && $userinfo->status == '4') || $userinfo->status != 3) {
            return $this->redirect('/dev/reg/pic?user_id=' . $userinfo->user_id . '&url=/dev/loan/second');
        }
        $create_time = date('Y-m-d H:i:s');
        $loan_no_keys = $userinfo->user_id . "_loan_no";
        $loan_no = Yii::$app->redis->get($loan_no_keys);
        Logger::errorLog(print_r($userinfo->user_id . '------' . $loan_no, true), 'loan_no_loan');
        if (empty($loan_no)) {
            $suffix = $userinfo->user_id . rand(100000, 999999);
            $loan_no = date("YmdHis") . $suffix;
            Yii::$app->redis->setex($loan_no_keys, 43200, $loan_no);//修改loan_no 有效时间为12小时
            $whiteModel = new White_list();
            $api = new XhhApi();
            if (!$whiteModel->isWhiteList($userinfo->identity)) {
                $limit = $api->runDecisions($userinfo, 1, 'loan', $amount, $days, $desc);
                if (!empty($limit)) {
                    $loanAll = User_loan::find()->where(['user_id' => $userinfo->user_id, 'business_type' => [1,4]])->count();
                    if (!(count($limit) == 1 && isset($limit['one_more_loan_value']) && $loanAll == 0)) {
                        $condition = $limit;
                        $condition['loan_no'] = $loan_no;
                        $event = Loan_event::addRecord($userinfo->user_id, $condition);
                        if (isset($condition['is_black']) && $condition['is_black'] == 1) {
                            $userinfo->setBlack();
                            return $this->redirect('/dev/loan');
                        } else {
                            $userLoanModel = new User_loan();
                            $result = $userLoanModel->addRejectLoan($userinfo, $loan_no, $amount, $days, $desc, 3, 0, $coupon_id, $coupon_amount, 1, 0);
                            if ($result) {
                                Yii::$app->redis->del($loan_no_keys);
                            }
                            $loan = User_loan::find()->where(['user_id' => $userinfo->user_id, 'prome_status' => 1])->one();
                            return $this->redirect('/dev/loan/succ?l=' . $loan->loan_id);
                        }
                    }
                }
            } else {
                $limit = $api->runDecisions($userinfo, 1, 'loan', $amount, $days, $desc);
                if (!empty($limit)) {
                    $condition = $limit;
                    $condition['loan_no'] = $loan_no;
                    $condition['type'] = 2;

                    $event = Loan_event::addRecord($userinfo->user_id, $condition);
                }
            }
        }
        $favorite = new Favorite_contacts();
        $fav = $favorite->getFavoriteByUserId($userinfo->user_id);
        if (empty($fav) || empty($fav['relation_common']) || empty($fav['relation_family'])) {
            return $this->redirect('/dev/reg/contacts?user_id=' . $userinfo->user_id . '&url=/dev/loan/second');
        }
        //4.用户是否绑定银行卡
        $userbank = User_bank::find()->where(['user_id' => $userinfo->user_id, 'status' => 1, 'default_bank' => 1, 'type' => 0])->one();
        if (empty($userbank)) {
            $userbank = User_bank::find()->where(['user_id' => $userinfo->user_id, 'status' => 1, 'type' => 0])->orderby('create_time')->one();
            if (empty($userbank) && !isset($userbank)) {
                return $this->redirect("/dev/bank/index");
            }
        }
        //验证码是否正确
//        $key = $userinfo['user_id'] . "_loan_juxinli";
//        $juxinlis = Yii::$app->redis->get($key);
//        if (empty($juxinlis)) {
        $juxinliModel = new Juxinli();
        $juxinli = $juxinliModel->getJuxinliByUserId($userinfo->user_id);
        if (empty($juxinli) || $juxinli->process_code != '10008' || ($juxinli->process_code == '10008' && date('Y-m-d H:i:s', strtotime('-4 month')) >= $juxinli->last_modify_time)) {
            return $this->redirect('/new/mobileauth/phoneauth?from=1');
        }
//        }
//        $rate = new Rate();
//        $day_rate = $rate->getRateByDay($days);
        $day_rate = 0.0005;
        //利息
        $interest_fee = round($amount * $day_rate * $days, 2);
        //服务费
        $withdraw_fee = (round($amount * 0.1, 2) > 5) ? round($amount * 0.1, 2) : 5;
        //新服务费
        $service_fee = $interest_fee + $withdraw_fee;
        //判断优惠券的金额是否大于借款的服务费，如果优惠券的金额大于借款的服务费，则优惠券只能优惠服务费的金额，多余的金额作废
        if ($interest_fee < $coupon_amount) {
            $coupon_amount = $interest_fee;
        }
        //到期应还
        if ((isset($coupon_amount)) && (!empty($coupon_id))) {
            if ($coupon_amount == 0) {
                $repay_amount = $amount;
            } else {
                $repay_amount = $amount + $interest_fee - $coupon_amount;
            }
        } else {
            $repay_amount = $amount + $interest_fee;
        }
        $count = User_bank::find()->where(['user_id' => $userinfo->user_id, 'status' => 1, 'type' => 0])->count();
        $bank_count = User_bank::find()->where(['user_id' => $userinfo->user_id, 'status' => 1])->count();
        $this->getView()->title = "借款确认";
        $jsinfo = $this->getWxParam();


        //是否只有一张卡并且被限制
        $flag = 1;
        if ($count > 1) {
            $user_bankinfo1 = (new User_bank())->limitCardsSort($userinfo->user_id, 0);
        }
        if ($count == 1) {
            $user_bankinfo1 = (new User_bank())->limitCardsSort($userinfo->user_id, 0);
            if ($user_bankinfo1[0]['sign'] == 1) {
                $flag = 2;
            }
        }
        return $this->render('confirm', ['bank_count' => $bank_count, 'flag' => $flag, 'desc' => $desc, 'days' => $days, 'amount' => $amount, 'coupon_id' => $coupon_id, 'coupon_amount' => $coupon_amount, 'repay_amount' => $repay_amount, 'userbank' => $userbank, 'withdraw_fee' => $withdraw_fee, 'interest_fee' => $interest_fee, 'service_fee' => $service_fee, 'jsinfo' => $jsinfo, 'user_bankinfo1' => $user_bankinfo1]);
    }

    //处理前台传来的阿加西
    public function actionAjax1() {
        $id = $_GET['id'];
        $user_id = $_GET['user_id'];
        //echo $user_id;
        $sql_bank = "update " . User_bank::tableName() . " set default_bank=0 where user_id=" . $user_id;
        $transaction = Yii::$app->db->beginTransaction();
        $ret = Yii::$app->db->createCommand($sql_bank)->execute();
        if ($ret) {
            $transaction->commit();
            $sql = "update " . User_bank::tableName() . " set default_bank=1 where id=" . $id;
            $r = Yii::$app->db->createCommand($sql)->execute();
            if ($r) {
                $transaction->commit();
                //echo '选卡成功';
            } else {
                $transaction->rollBack();
            }
        } else {
            $transaction->rollBack();
        }
    }

    public function actionInvestloanapp($loan_id, $type = 'loan', $loan_type = 'friend') {
        $loan = User_loan::findOne($loan_id);
        $this->layout = 'agreement';
        $user_id = $loan->user_id;
        $desc = $loan->desc;
        $days = $loan->days;
        $this->getView()->title = "先花一亿元居间服务及借款投资协议（三方）";
        $huankuandate = date('Y-m-d', strtotime('+' . $days . 'days'));
        $user = User::findOne($user_id);
        $userwx = $user->userwx;
        $bank = $loan->bank;
        return $this->render('investloanapp', [
                    'user' => $user,
                    'userwx' => $userwx,
                    'bank' => $bank,
                    'type' => $type,
                    'loan_type' => $loan_type,
                    'desc' => $desc,
                    'days' => $days,
                    'huankuandate' => $huankuandate
        ]);
    }

    public function actionAgreeloanapp($user_id, $desc, $days, $amount, $repay_amount, $type = 'loan', $loan_type = 'friend') {
        $all_desc = [
            '1' => '购买原材料',
            '2' => '进货',
            '3' => '购买设备',
            '4' => '购买家具或家电',
            '5' => '学习',
            '6' => '个人或家庭消费',
            '7' => '资金周转',
            '8' => '租房',
            '9' => '物流运输',
            '10' => '其他',
        ];
        $rule = '/^\d{1,2}$/';
        $result = preg_match($rule, $desc);
        if ($result == 1) {
            $desc = $all_desc[$desc];
        }
        $this->getView()->title = "先花一亿元借款协议";
        $this->layout = 'agreement';
        $daxie_amount = Common::get_amount($amount);
        $daxie_repay_amount = Common::get_amount($repay_amount);
        $daxie_repay_amount_num = Common::get_amount_num($repay_amount);
        $daxie_amount_num = Common::get_amount_num($amount);
        $huankuandate = date('Y-m-d', strtotime('+' . $days . 'days'));
        $user = User::findOne($user_id);
        $userwx = $user->userwx;
        $bank = User_bank::find()->where(['user_id' => $user_id, 'type' => 0, 'status' => 1])->orderBy('default_bank desc,last_modify_time desc')->one();
        return $this->render('agreeloanapp', [
                    'user' => $user,
                    'userwx' => $userwx,
                    'bank' => $bank,
                    'type' => $type,
                    'loan_type' => $loan_type,
                    'desc' => $desc,
                    'days' => $days,
                    'amount' => $amount,
                    'daxie_amount' => $daxie_amount,
                    'daxie_repay_amount' => $daxie_repay_amount,
                    'daxie_repay_amount_num' => $daxie_repay_amount_num,
                    'daxie_amount_num' => $daxie_amount_num,
                    'huankuandate' => $huankuandate
        ]);
    }

    public function actionJiufu($come_from = '') {
        $this->getView()->title = "融资文件";
        $this->layout = 'agreement';
        $url = '/dev/loan/second';
        return $this->render('jiufu', [
                    'come_from' => $come_from,
                    'url' => $url,
        ]);
    }

    public function actionAgreeloan() {
        $this->getView()->title = "先花一亿元居间服务及借款协议（三方）";
        $this->layout = 'agreement';
        $openid = $this->getVal('openid');
        $type = isset($_GET['type']) ? $_GET['type'] : 'loan';
        $loan_type = isset($_GET['loan_type']) ? $_GET['loan_type'] : 'friend';
        $loan_id = isset($_GET['loan_id']) ? $_GET['loan_id'] : '';
        $desc = isset($_GET['desc']) ? $_GET['desc'] : '';
        $days = isset($_GET['days']) ? $_GET['days'] : '';
        $amount = isset($_GET['amount']) ? $_GET['amount'] : '';
        $daxie_amount = Common::get_amount($amount);
        $daxie_amount_num = Common::get_amount_num($amount);
        $repay_amount = isset($_GET['repay_amount']) ? $_GET['repay_amount'] : '';
        $daxie_repay_amount = Common::get_amount($repay_amount);
        $daxie_repay_amount_num = Common::get_amount_num($repay_amount);
        $huankuandate = date('Y-m-d', (time() + $days * 24 * 3600));
        $sql = "select w.nickname,u.realname,u.identity,b.bank_name,b.card from yi_user_wx as w,yi_user as u,yi_user_bank as b where w.openid='$openid' and u.openid='$openid' and u.user_id=b.user_id";
        $loaninfo = Yii::$app->db->createCommand($sql)->queryOne();
        if (!empty($loan_id)) {
            $url = '/dev/loan/conwd?l=' . $loan_id;
        } else {
            if ($loan_type == 'friend') {
                $url = '/dev/loan/second';
            } else {
                $url = "/dev/loan/qd?desc=$desc&days=on&amount=$amount";
            }
        }
        return $this->render('agreeloan', [
                    'url' => $url,
                    'loaninfo' => $loaninfo,
                    'type' => $type,
                    'loan_type' => $loan_type,
                    'loan_id' => $loan_id,
                    'desc' => $desc,
                    'days' => $days,
                    'amount' => $amount,
                    'daxie_amount' => $daxie_amount,
                    'daxie_amount_num' => $daxie_amount_num,
                    'daxie_repay_amount' => $daxie_repay_amount,
                    'daxie_repay_amount_num' => $daxie_repay_amount_num,
                    'huankuandate' => $huankuandate,
        ]);
    }

    public function actionConfirm() {
        $ip = Common::get_client_ip();
        if (isset($_POST) && !empty($_POST)) {
            $openid = $this->getVal('openid');
            $userinfo = User::find()->joinWith('account', true, 'LEFT JOIN')->where(['openid' => $openid])->one();
            //判断是否存在驳回订单
            $loan_info = new User_loan();
            $judgment = $loan_info->LoanJudgment($userinfo->user_id);
            if (!$judgment) {
                $resultArr = array('ret' => '7', 'url' => '/new/loan');
                echo json_encode($resultArr);
                exit;
            }

            if ($userinfo->status != 3) {
                return $this->redirect('/dev/reg/personals?user_id=' . $userinfo->user_id . '&url=/new/loan');
            }
            /*             * *************记录访问日志beigin******************* */
            $result_log = Common::saveLog('loan', 'loan_confirm_button', $ip, 'weixin', $userinfo->user_id);
            /*             * *************记录访问日志end******************* */
            //判断用户是否是黑名单用户
            if ($userinfo['status'] == 5) {
                //如果是黑名单用户则直接跳转到黑名单用户页面
                $resultArr = array('ret' => '6', 'url' => '/dev/loan');
                echo json_encode($resultArr);
                exit;
            }
            //判断用户是否有借款
            $statu = array('1', '2', '5', '6', '9', '10', '11', '12', '13'); //如果用户存在借款状态为1、2、5、6、8
            $User_loan = User_loan::find()->where(['user_id' => $userinfo->user_id, 'status' => $statu, 'business_type' => array(1, 3)])->one();
            if (!empty($User_loan)) {
                $resultArr = array('ret' => '4', 'url' => '/new/loan');
                echo json_encode($resultArr);
                exit;
            }

            if ($userinfo->status == '4') {
                $resultArr = array('ret' => '5', 'url' => '/new/loan');
                echo json_encode($resultArr);
                exit;
            }
            $status = 5;
            $time = date('Y-m-d H:i:s', time());
            $user_id = $userinfo->user_id;
            $desc = $_POST['desc'];
            $days = $_POST['days'];
            $amount = $_POST['amount'];
            $coupon_id = $_POST['coupon_id'];
            $coupon_amount = $_POST['coupon_amount'];
            $bank_id = $_POST['bank_id'];
            $recharge_amount = 0;
            $loan_amount = $amount;
            $type = 2;
            $credit_amount = 0;
            $day_rate = 0.0005;
            $interest_fee = round($amount * $day_rate * $days, 2);
            $withdraw_fee = (round($amount * 0.1, 2) > 5) ? round($amount * 0.1, 2) : 5;
            $loanModel = new User_loan();
            $loan_no_keys = $user_id . "_loan_no";
            $loan_no = Yii::$app->redis->get($loan_no_keys);
            $condition = array(
                'user_id' => $user_id,
                'loan_no' => $loan_no,
                'real_amount' => $amount,
                'amount' => $amount,
                'credit_amount' => $credit_amount,
                'recharge_amount' => $recharge_amount,
                'current_amount' => $amount,
                'days' => $days,
                'type' => $type,
                'status' => $status,
                'interest_fee' => $interest_fee,
                'withdraw_fee' => $withdraw_fee,
                'desc' => $desc,
                'bank_id' => $bank_id,
                'withdraw_time' => $time,
                'is_calculation' => 1,
            );
            if (empty($loan_no)) {
                $condition['status'] = 3;
            }
            $whiteModel = new White_list();
            $white = $whiteModel->isWhiteList($userinfo->identity);
            if ($white) {
                $condition['final_score'] = -1;
            }
            if (!empty($coupon_id)) {
                $condition['coupon_amount'] = $coupon_amount;
            }
            $transaction = Yii::$app->db->beginTransaction();
            $loan_id = $loanModel->addUserLoan($condition);
            if (!$loan_id || $condition['status'] == 3) {
                $transaction->rollBack();
                Yii::$app->redis->del($loan_no_keys);
                $resultArr = array('ret' => '3', 'url' => '/new/loan');
                echo json_encode($resultArr);
                exit;
            }
            if (!$white) {
                $loan = User_loan::findOne($loan_id);
                $fr = Fraudmetrix_return_info::find()->where(['loan_id' => $loan->loan_no])->one();
                if (!empty($fr)) {
                    $fr->loan_id = $loan_id;
                    $fr->save();
                    $loan->final_score = $fr->final_score;
                    $loan->save();
                }
            }

            $key = $user_id . "_loan_juxinli";
            Yii::$app->redis->del($key);
            Yii::$app->redis->del($loan_no_keys);
            //创建订单成功 记录日志
            $loan_flows_sql = "insert into " . Flow::tableName() . " (loan_id,admin_id,loan_status,create_time) value($loan_id,0,$status,'$time')";
            $ret_flows = Yii::$app->db->createCommand($loan_flows_sql)->execute();

            if (!empty($coupon_id)) {
                //记录优惠券使用情况
                $loan_coupon_use_sql = "insert into " . Coupon_use::tableName() . " (user_id,discount_id,loan_id,create_time,version) value($user_id,$coupon_id,$loan_id,'$time',1)";
                $ret_coupon_use = Yii::$app->db->createCommand($loan_coupon_use_sql)->execute();

                //修改优惠券的使用状态
                $loan_coupon_list_sql = "update " . Coupon_list::tableName() . " set status=2,use_time='$time' where id=" . $coupon_id;
                $ret_coupon_list = Yii::$app->db->createCommand($loan_coupon_list_sql)->execute();
            }
            //更新用户账户信息
            //用户审核通过，需要更新账户            
            if (isset($bank_id) && !empty($bank_id)) {
                $sql_bank = "update " . User_bank::tableName() . " set default_bank=0 where user_id=" . $user_id;
                $ret_bank = Yii::$app->db->createCommand($sql_bank)->execute();
                if ($ret_bank >= 0) {
                    $sql = "update " . User_bank::tableName() . " set default_bank=1 where id=" . $bank_id;
                    Yii::$app->db->createCommand($sql)->execute();
                }
            }
            $loanextendModel = new User_loan_extend(); //news
            $loan = User_loan::findOne($loan_id);
            $success_num = User_loan::find()->where(['user_id' => $loan->user_id, 'status' => 8])->count();
            $extend = array(
                'user_id' => $loan->user_id,
                'loan_id' => $loan->loan_id,
                'outmoney' => 0,
                'payment_channel' => 0,
                'userIp' => $ip,
                'extend_type' => '1',
                'success_num' => $success_num,
                'status' => 'INIT',
            );
            $extendId = $loanextendModel->addList($extend);
            if ($extendId) {
                $transaction->commit();
                $resultArr = array('ret' => '1', 'url' => '/new/loanrecord/creditdetails?loan_id=' . $loan_id);
                echo json_encode($resultArr);
                exit;
            } else {
                $transaction->rollBack();
                $resultArr = array('ret' => '3', 'url' => '/new/loan');
                echo json_encode($resultArr);
                exit;
            }
        } else {
            //return $this->redirect('/dev/loan') ;
            $resultArr = array('ret' => '3', 'url' => '/newloan');
            echo json_encode($resultArr);
            exit;
        }
    }

    //未满提现确认
    public function actionConwd() {
        if (isset($_POST) && !empty($_POST)) {
            $loan_id = $_POST['loan_id'];
            $loan = User_loan::find()->where(['loan_id' => $loan_id])->one();
            if ($loan['status'] != 2) {
                return $this->redirect('/dev/loan/succ?l=' . $loan_id);
            }
            $user = $loan->user;
            $userinfo = User::find()->joinWith('account', true, 'LEFT JOIN')->where([User::tableName() . '.user_id' => $user['user_id']])->one();
            $amount = $loan['current_amount'];
            //未筹满的金额
            $notfull_amount = $loan['amount'] - $amount;
            //如果是免息用户
            $days = $loan['days'];
            if ($loan->is_calculation == 1) {
                $day_rate = 0.0005;
                $interest_fee = round($amount * $day_rate * $days, 2);
                $withdraw_fee = (round($amount * 0.1, 2) > 5) ? round($amount * 0.1, 2) : 5;
            } else {
                $rate = new Rate();
                $day_rate = $rate->getRateByDay($days);
                $interest_fee = round($amount * $day_rate * $days, 2);
                $withdraw_fee = (round($amount * 0.01, 2) > 5) ? round($amount * 0.01, 2) : 5;
            }

            $coupon_use = Coupon_use::find()->where(['loan_id' => $loan_id])->one();
            if (!empty($coupon_use)) {
                $loan_coupon = Coupon_list::find()->where(['id' => $coupon_use->discount_id])->one();
                $coupon_amount = $loan->getLoanCouponAmount($loan->loan_id, $loan->current_amount, $interest_fee, $withdraw_fee, $loan_coupon);
            } else {
                $coupon_amount = NULL;
            }

            $last_modify_time = date('Y-m-d H:i:s', time());
            $create_time = date('Y-m-d H:i:s');
            $status = 5; //提现申请
            $loan_sql = "update " . User_loan::tableName() . " set amount=" . $amount . ",interest_fee=" . $interest_fee . ",last_modify_time='" . $last_modify_time . "',status=" . $status . ",withdraw_fee=" . $withdraw_fee . ",withdraw_time='$create_time',coupon_amount='$coupon_amount',version=version+1 where loan_id=" . $loan_id . " and version=" . $loan['version'];
            $acc_sql = "update " . Account::tableName() . " set current_loan=current_loan-" . $notfull_amount . ",version=version+1 where user_id=" . $userinfo['account']->user_id . " and version=" . $userinfo['account']->version;
            $transaction = Yii::$app->db->beginTransaction();
            $ret_sql = Yii::$app->db->createCommand($loan_sql)->execute();
            $ret = Yii::$app->db->createCommand($acc_sql)->execute();
            // 手动提现日志记录
            $flowdata = (object) array('loan_id' => $loan_id, 'status' => 5);
            $flow = new Flow();
            $flow->CreateFlow($flowdata, 0);

            //查询该笔借款是否有使用优惠券，如果有使用优惠券，则判断优惠券是否过期，没有过期则返还给用户
            $coupon_use = Coupon_use::find()->where(['loan_id' => $loan_id])->one();
            if (!empty($coupon_use)) {
                $loan_coupon_sql = "select l.id,l.limit,l.end_date,l.val from " . Coupon_list::tableName() . " as l," . Coupon_use::tableName() . " as u where l.id=u.discount_id and u.loan_id=" . $loan_id;
                $loan_coupon = Yii::$app->db->createCommand($loan_coupon_sql)->queryOne();
                if (!empty($loan_coupon)) {
                    if (($loan_coupon['limit'] != 0) && ($loan_coupon['limit'] > $amount)) {
                        //先将用户使用的优惠券金额改为0
                        $sql_coupon = "update " . User_loan::tableName() . " set coupon_amount=0 where loan_id=" . $loan_id;
                        $ret_coupon = Yii::$app->db->createCommand($sql_coupon)->execute();
                        if ($create_time < $loan_coupon['end_date']) {
                            $sql_coupon = "update " . Coupon_list::tableName() . " set status=1 where id=" . $loan_coupon['id'];
                            $sql_coupon_ret = Yii::$app->db->createCommand($sql_coupon)->execute();
                        } else {
                            $sql_coupon = "update " . Coupon_list::tableName() . " set status=3 where id=" . $loan_coupon['id'];
                            $sql_coupon_ret = Yii::$app->db->createCommand($sql_coupon)->execute();
                        }
                    }
                }
            }
            if ($ret) {
                $transaction->commit();
                $loaninfo_bynow = User_loan::find()->where(['loan_id' => $loan_id])->one();
                if ($loaninfo_bynow['status'] == 5 && is_null($loaninfo_bynow['final_score'])) {//如果借款状态为5，并且final_score为NULL
                    $whiteModel = new White_list();
                    if (!$whiteModel->isWhiteList($userinfo['identity'])) {
                        //调用同盾接口
                        switch ($userinfo['edu']) {
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
                        $token_id = isset($_COOKIE['PHPSESSID']) ? $_COOKIE['PHPSESSID'] : '';
                        if (empty($token_id)) {
                            $userpass = User_password::find()->select('device_tokens')->where(['user_id' => $userinfo['user_id']])->one();
                            $token_id = !empty($userpass->device_tokens) ? $userpass->device_tokens : rand(100000000, 999999999);
                        }
                        $params = array(
                            'account_name' => $userinfo['realname'],
                            'mobile' => $userinfo['mobile'],
                            'id_number' => $userinfo['identity'],
                            'seq_id' => $loaninfo_bynow['loan_no'],
                            'ip_address' => \Yii::$app->request->getUserIP(),
                            'type' => 1,
                            'token_id' => $token_id,
                            'ext_school' => $userinfo['school'],
                            'ext_diploma' => $ext_diploma,
                            'ext_start_year' => $userinfo['school_time'],
                            'card_number' => $loaninfo_bynow->bank->card,
                            'pay_amount' => $loaninfo_bynow['amount'],
                            'event_occur_time' => $create_time,
                            'ext_birth_year' => $userinfo['birth_year']
                        );
                        $api = new Apihttp();
                        $result_loan = $api->riskLoanValid($params);
                        $fraudmetrix = new Fraudmetrix_return_info();
                        $fraudmetrix->CreateFraudmetrix($result_loan, $loaninfo_bynow['user_id'], $loan_id);
                        if (isset($result_loan->rsp_code) && $result_loan->rsp_code == '0000') {
                            $final_score = trim($result_loan->finalScore);
                            $final_result = trim($result_loan->result);
                            if (isset($final_score)) {
                                if ($final_result == 'Reject') {
                                    $sql_score = "update " . User_loan::tableName() . " set status=3,final_score='$final_score',version=version+1 where loan_id=" . $loan_id;
                                    $ret_score = Yii::$app->db->createCommand($sql_score)->execute();
                                    $flowdata = (object) array('loan_id' => $loan_id, 'status' => 3);
                                    $flow = new Flow();
                                    $flow->CreateFlow($flowdata, 0);

                                    //借款人账户当前借款和总借款减
                                    if ($loaninfo_bynow['credit_amount'] > 0) {
                                        $loan_acc = "update " . Account::tableName() . " set current_amount=current_amount+" . $loaninfo_bynow['credit_amount'] . ", current_loan=current_loan-" . $loaninfo_bynow['amount'] . ",total_loan=total_loan-" . $loaninfo_bynow['amount'] . ",version=version+1 where user_id=" . $userinfo['user_id'];
                                    } else {
                                        $loan_acc = "update " . Account::tableName() . " set current_loan=current_loan-" . $loaninfo_bynow['amount'] . ",total_loan=total_loan-" . $loaninfo_bynow['amount'] . ",version=version+1 where user_id=" . $userinfo['user_id'];
                                    }
                                    $ret_loan_acc = Yii::$app->db->createCommand($loan_acc)->execute();

                                    //查询该笔借款是否有使用优惠券，如果有使用优惠券，则判断优惠券是否过期，没有过期则返还给用户
                                    $coupon_use = Coupon_use::find()->where(['loan_id' => $loan_id])->one();
                                    if (!empty($coupon_use)) {
                                        $nowtime = date('Y-m-d H:i:s');
                                        $loan_coupon_sql = "select l.id,l.end_date from " . Coupon_list::tableName() . " as l," . Coupon_use::tableName() . " as u where l.id=u.discount_id and u.loan_id=" . $loan_id;
                                        $loan_coupon = Yii::$app->db->createCommand($loan_coupon_sql)->queryOne();
                                        if (!empty($loan_coupon)) {
                                            if ($nowtime < $loan_coupon['end_date']) {
                                                $sql_coupon = "update " . Coupon_list::tableName() . " set status=1 where id=" . $loan_coupon['id'];
                                                $sql_coupon_ret = Yii::$app->db->createCommand($sql_coupon)->execute();
                                            } else {
                                                $sql_coupon = "update " . Coupon_list::tableName() . " set status=3 where id=" . $loan_coupon['id'];
                                                $sql_coupon_ret = Yii::$app->db->createCommand($sql_coupon)->execute();
                                            }
                                        }
                                    }

                                    //借款投资人信息
                                    $investUser = User_invest::find()->where(['loan_id' => $loan_id])->all();
                                    if ($ret_loan_acc) {
                                        //投资人投资额度返回，同时投资人账户当前投资和总投资减
                                        foreach ($investUser as $key => $val) {
                                            $invest_sql = "update " . User_invest::tableName() . " set status=2 ,version=version+1 where invest_id=" . $val->invest_id . " and version=" . $val->version;
                                            $ret_invest_sql = Yii::$app->db->createCommand($invest_sql)->execute();

                                            if (!$ret_invest_sql) {
                                                //记录错误日志
                                                Logger::errorLog($invest_sql, 'withreject');
                                            }
                                            $invest_acc_sql = "update " . Account::tableName() . " set current_invest=current_invest-" . $val->amount . ",total_invest=total_invest-" . $val->amount . ",current_amount=current_amount+" . $val->amount . ",version=version+1 where user_id=" . $val->user_id;
                                            $ret_invest_acc_sql = Yii::$app->db->createCommand($invest_acc_sql)->execute();
                                            if (!$ret_invest_acc_sql) {
                                                //记录错误日志
                                                Logger::errorLog($invest_acc_sql, 'withreject');
                                            }
                                        }
                                    }
                                } else {
                                    if ($final_score >= 60) {
                                        $sql_score = "update " . User_loan::tableName() . " set status=3,final_score='$final_score',version=version+1 where loan_id=" . $loan_id;
                                        $ret_score = Yii::$app->db->createCommand($sql_score)->execute();
                                        $flowdata = (object) array('loan_id' => $loan_id, 'status' => 3);
                                        $flow = new Flow();
                                        $flow->CreateFlow($flowdata, 0);

                                        //借款人账户当前借款和 总借款减
                                        if ($loaninfo['credit_amount'] > 0) {
                                            $loan_acc = "update " . Account::tableName() . " set current_amount=current_amount+" . $loaninfo_bynow['credit_amount'] . ",current_loan=current_loan-" . $loaninfo_bynow['amount'] . ",total_loan=total_loan-" . $loaninfo_bynow['amount'] . ",version=version+1 where user_id=" . $loaninfo_bynow['user_id'];
                                        } else {
                                            $loan_acc = "update " . Account::tableName() . " set current_loan=current_loan-" . $loaninfo_bynow['amount'] . ",total_loan=total_loan-" . $loaninfo_bynow['amount'] . ",version=version+1 where user_id=" . $loaninfo_bynow['user_id'];
                                        }
                                        $ret_loan_acc = Yii::$app->db->createCommand($loan_acc)->execute();

                                        //查询该笔借款是否有使用优惠券，如果有使用优惠券，则判断优惠券是否过期，没有过期则返还给用户
                                        $coupon_use = Coupon_use::find()->where(['loan_id' => $loan_id])->one();
                                        if (!empty($coupon_use)) {
                                            $nowtime = date('Y-m-d H:i:s');
                                            $loan_coupon_sql = "select l.id,l.end_date from " . Coupon_list::tableName() . " as l," . Coupon_use::tableName() . " as u where l.id=u.discount_id and u.loan_id=" . $loan_id;
                                            $loan_coupon = Yii::$app->db->createCommand($loan_coupon_sql)->queryOne();
                                            if (!empty($loan_coupon)) {
                                                if ($nowtime < $loan_coupon['end_date']) {
                                                    $sql_coupon = "update " . Coupon_list::tableName() . " set status=1 where id=" . $loan_coupon['id'];
                                                    $sql_coupon_ret = Yii::$app->db->createCommand($sql_coupon)->execute();
                                                } else {
                                                    $sql_coupon = "update " . Coupon_list::tableName() . " set status=3 where id=" . $loan_coupon['id'];
                                                    $sql_coupon_ret = Yii::$app->db->createCommand($sql_coupon)->execute();
                                                }
                                            }
                                        }

                                        //借款投资人信息
                                        $investUser = User_invest::find()->where(['loan_id' => $loan_id])->all();
                                        if ($ret_loan_acc) {
                                            //投资人投资额度返回，同时投资人账户当前投资和总投资减
                                            foreach ($investUser as $key => $val) {
                                                $invest_sql = "update " . User_invest::tableName() . " set status=2 ,version=version+1 where invest_id=" . $val->invest_id . " and version=" . $val->version;
                                                $ret_invest_sql = Yii::$app->db->createCommand($invest_sql)->execute();

                                                if (!$ret_invest_sql) {
                                                    //记录错误日志
                                                    Logger::errorLog($invest_sql, 'withreject');
                                                }
                                                $invest_acc_sql = "update " . Account::tableName() . " set current_invest=current_invest-" . $val->amount . ",total_invest=total_invest-" . $val->amount . ",current_amount=current_amount+" . $val->amount . ",version=version+1 where user_id=" . $val->user_id;
                                                $ret_invest_acc_sql = Yii::$app->db->createCommand($invest_acc_sql)->execute();
                                                if (!$ret_invest_acc_sql) {
                                                    //记录错误日志
                                                    Logger::errorLog($invest_acc_sql, 'withreject');
                                                }
                                            }
                                        }
                                    } else {
                                        $sql_score = "update " . User_loan::tableName() . " set final_score='$final_score',version=version+1 where loan_id=" . $loan_id;
                                        $ret_score = Yii::$app->db->createCommand($sql_score)->execute();
                                    }
                                }
                            }
                        }
                    } else {
                        $sql_score = "update " . User_loan::tableName() . " set final_score='-1',version=version+1 where loan_id=" . $loan_id;
                        $ret_score = Yii::$app->db->createCommand($sql_score)->execute();
                    }
                }
                return $this->redirect('/dev/loan/succ?l=' . $loan_id);
            } else {
                $transaction->rollBack();
                return $this->redirect('/dev/loan/succ?l=' . $loan_id);
            }
        } else {
            if (isset($_GET['l'])) {
                $loan_id = $_GET['l'];
                $loaninfo = User_loan::find()->where(['loan_id' => $loan_id])->one();
                $userinfo = User::find()->joinWith('account', true, 'LEFT JOIN')->where([User::tableName() . '.user_id' => $loaninfo->user_id])->one();
                //判断用户是否是黑名单用户
                if ($userinfo['status'] == 5) {
                    //如果是黑名单用户则直接跳转到黑名单用户页面
                    return $this->redirect('/dev/account/black');
                }
                $this->getView()->title = "提现确认";
                //用户绑定银行卡
                $userbank = User_bank::find()->where(['id' => $loaninfo['bank_id']])->one();
                if ($loaninfo->is_calculation == 1) {
                    $day_rate = 0.0005;
                    $interest_fee = round($loaninfo->current_amount * $day_rate * $loaninfo->days, 2);
                    $withdraw_fee = (round($loaninfo->current_amount * 0.1, 2) > 5) ? round($loaninfo->current_amount * 0.1, 2) : 5;
                } else {
                    $rate = new Rate();
                    $day_rate = $rate->getRateByDay($loaninfo->days);
                    $interest_fee = round($loaninfo->current_amount * $day_rate * $loaninfo->days, 2);
                    $withdraw_fee = (round($loaninfo->current_amount * 0.01, 2) > 5) ? round($loaninfo->current_amount * 0.01, 2) : 5;
                }
                $coupon_use = Coupon_use::find()->where(['loan_id' => $loan_id])->one();
                if (!empty($coupon_use)) {
                    $loan_coupon = Coupon_list::find()->where(['id' => $coupon_use->discount_id])->one();
                    $coupon_amount = $loaninfo->getLoanCouponAmount($loaninfo->loan_id, $loaninfo->current_amount, $interest_fee, $withdraw_fee, $loan_coupon);
                    if ($loaninfo->is_calculation == 1) {
                        if (!empty($loan_coupon) && $loan_coupon['val'] == 0) {
                            $coupon_amount = $interest_fee;
                        }
                        $repay_amount = $loaninfo->current_amount + $interest_fee - $coupon_amount;
                    } else {
                        if (!empty($loan_coupon) && $loan_coupon['val'] == 0) {
                            $coupon_amount = $interest_fee + $withdraw_fee;
                        }
                        $repay_amount = $loaninfo->current_amount + $interest_fee + $withdraw_fee - $coupon_amount;
                    }
                } else {
                    $coupon_amount = 0;
                    if ($loaninfo->is_calculation == 1) {
                        $repay_amount = $loaninfo->current_amount + $interest_fee - $coupon_amount;
                    } else {
                        $repay_amount = $loaninfo->current_amount + $interest_fee + $withdraw_fee - $coupon_amount;
                    }
                }
                $jsinfo = $this->getWxParam();
                $this->layout = "loan";
                return $this->render('conwd', ['withdraw_fee' => $withdraw_fee, 'interest_fee' => $interest_fee, 'coupon_amount' => $coupon_amount, 'loaninfo' => $loaninfo, 'desc' => $loaninfo->desc, 'days' => $loaninfo->days, 'amount' => $loaninfo->current_amount, 'repay_amount' => $repay_amount, 'loan_id' => $loan_id, 'userbank' => $userbank, 'jsinfo' => $jsinfo]);
            } else {
                return $this->redirect('/dev/loan/succ?l=' . $loan_id);
            }
        }
    }

    public function actionAudit() {
        $this->layout = "loan";
        $this->getView()->title = "信息审核中";
        if (isset($_GET['l'])) {
            $loan_id = $_GET['l'];
        } else {
            $loan_id = '';
        }
        $jsinfo = $this->getWxParam();
        return $this->render('audit', ['loan_id' => $loan_id, 'jsinfo' => $jsinfo]);
    }

    public function actionRefresh() {
        $loan_id = $_POST['loan_id'];
        if (!$loan_id) {
            echo 'faild123';
            exit;
        }
        $userloan = User_loan::find()->where(['loan_id' => $loan_id])->one();
        if (isset($userloan->loan_id) && $userloan->status != '1') {
            echo 'success';
            exit;
        } else {
            echo 'faild';
            exit;
        }
    }

    public function actionSucc() {
        if (isset($_GET['l'])) {
            $this->getView()->title = "借款详情";
            $jsinfo = $this->getWxParam();
            $loan_id = $_GET['l'];

            $status = array('1', '2', '5', '6', '9', '10', '11', '12', '13');
            $loaninfo = User_loan::find()->where(['loan_id' => $loan_id])->one();
            $userinfo_stu = User::find()->where(['user_id' => $loaninfo->user_id])->one();

            if ($loaninfo->business_type == 3) {
                return $this->redirect('/dev/loan/guasucc?l=' . $loan_id);
            }
            $business_type = $loaninfo->business_type;
            $userinfo = Userwx::find()->joinWith('user', true, 'LEFT JOIN')->where(['user_id' => $loaninfo->user_id])->one();

            //print_r($userinfo);exit;
            //借款投资记录//////////////////
            $sql1 = "(select u.user_id,i.amount,i.create_time,i.type,u.realname,w.head,w.nickname from " . Loan_record::tableName() . " as i left join " . User::tableName() . " as u on i.invest_user_id=u.user_id left join " . Userwx::tableName() . " as w on u.openid=w.openid where i.loan_id=$loan_id and u.status!=6 and i.type=2)";
            $sql2 = "(select u.user_id,i.amount,i.create_time,i.type,u.realname,w.head,w.nickname from " . Loan_record::tableName() . " as i left join " . Userwx::tableName() . " as w on i.invest_user_id=w.id left join " . User::tableName() . " as u on w.openid=u.openid and u.status!=6 where i.loan_id=$loan_id and i.type=1)";
            $sql_record_loan = $sql1 . " union all " . $sql2 . " order by create_time desc";
            ////////////////////////////
            $time = time();
            //根据当前时间计算还剩余的小时数
            $endtime = strtotime($loaninfo->open_end_date);
            $loaninfo->status = $loaninfo->prome_status == 1 ? 5 : $loaninfo->status;
            $accountinfo = Account::find()->where(['user_id' => $loaninfo->user->user_id])->one();
            $user_loaninfo = User_loan::find()->where(['user_id' => $loaninfo->user->user_id, 'status' => $status, 'business_type' => 2])->all();
            $total = 0;
            foreach ($user_loaninfo as $v) {
                $total += $v->amount;
            }

            //用户的充值额度
            $recharge_amount = $accountinfo->recharge_amount;

            //担保额度最后计算结果number_format("50000",2,".","");
            $dtotal = number_format($recharge_amount - ($total / 0.99), 2, ".", "");
            //echo $dtotal;exit;
            if ($dtotal < '100.0000') {
                $exist = 0;
            } else {
                $exist = 1;
            }


            $show = (new Scan_times())->getScanCount($loaninfo->user->mobile, 21, 1);

            if (!empty($show)) {
                $is_show = 0;
            } else {
                $is_show = 1;
            }

            //判断活动弹窗是否显示
            $active_show = (new Scan_times())->isShow("2017-04-27 11:00:00", 15, $loaninfo->user->mobile, 18, 19);
            $active_show = $active_show ? 1 : 0;

            if (($loaninfo->status == '4') || ($loaninfo->status == '7') || ($loaninfo->status == '3') || ($loaninfo->status == '15') || ($loaninfo->status == '17')) {
                $this->layout = "loan";
                $loan_coupon_sql = "select l.id,l.limit,l.end_date,l.val,l.status from " . Coupon_list::tableName() . " as l," . Coupon_use::tableName() . " as u where l.id=u.discount_id and u.loan_id=" . $loan_id;
                $loan_coupon = Yii::$app->db->createCommand($loan_coupon_sql)->queryOne();
                $loaninfo['huankuan_amount'] = $loaninfo->getRepaymentAmount($loan_id, $loaninfo->status, $loaninfo->chase_amount, $loaninfo->collection_amount, $loaninfo->like_amount, $loaninfo->amount, $loaninfo->current_amount, $loaninfo->interest_fee, $loaninfo->coupon_amount, $loaninfo->withdraw_fee);
                $service_amount = $loaninfo->getServiceAmount($loan_id, $loaninfo->status, $loaninfo->interest_fee, $loaninfo->withdraw_fee, $loaninfo->coupon_amount, $loaninfo->is_calculation);

                //查询驳回的理由
                $loan_flows = User_loan_flows::find()->select(array('reason'))->where(['loan_id' => $loan_id, 'loan_status' => $loaninfo->status])->one();

                if ($loaninfo->business_type == 3) {
                    return $this->render('succfail', ['loaninfo' => $loaninfo, 'guater' => $guater, 'userinfo' => $userinfo, 'business_type' => $business_type, 'service_amount' => $service_amount, 'jsinfo' => $jsinfo]);
                }
                $loanrecord = Yii::$app->db->createCommand($sql_record_loan)->queryAll();

                return $this->render('succfail', ['loan_coupon' => $loan_coupon, 'loanrecord' => $loanrecord, 'loaninfo' => $loaninfo, 'loan_flows' => $loan_flows, 'business_type' => $business_type, 'userinfo' => $userinfo, 'service_amount' => $service_amount, 'jsinfo' => $jsinfo]);
            }
            //6.申请提现
            else if ($loaninfo->status == '5') {
                $loan_coupon_sql = "select l.id,l.limit,l.end_date,l.val,l.status from " . Coupon_list::tableName() . " as l," . Coupon_use::tableName() . " as u where l.id=u.discount_id and u.loan_id=" . $loan_id;
                $loan_coupon = Yii::$app->db->createCommand($loan_coupon_sql)->queryOne();
                $loaninfo['huankuan_amount'] = $loaninfo->getRepaymentAmount($loan_id, $loaninfo->status, $loaninfo->chase_amount, $loaninfo->collection_amount, $loaninfo->like_amount, $loaninfo->amount, $loaninfo->current_amount, $loaninfo->interest_fee, $loaninfo->coupon_amount, $loaninfo->withdraw_fee);
                //服务费
                $service_amount = $loaninfo->getServiceAmount($loan_id, $loaninfo->status, $loaninfo->interest_fee, $loaninfo->withdraw_fee, $loaninfo->coupon_amount, $loaninfo->is_calculation);
                if ($loaninfo->business_type == 1) {
                    $this->layout = "data";

                    return $this->redirect('/new/loanrecord/creditdetails?loan_id=' . $loan_id);
                    return $this->render('loansuccapply', ['user_status' => $userinfo_stu->status, 'loan_coupon' => $loan_coupon, 'loaninfo' => $loaninfo, 'service_amount' => $service_amount, 'jsinfo' => $jsinfo, 'exist' => $exist, 'is_show' => $is_show, 'active_show' => $active_show]);
                } else {
                    $this->layout = "loan";
                    return $this->render('succapply', ['loan_coupon' => $loan_coupon, 'loaninfo' => $loaninfo, 'service_amount' => $service_amount, 'jsinfo' => $jsinfo, 'exist' => $exist]);
                }
            }
            //7.提现成功
            else if (($loaninfo->status == '6') || ($loaninfo->status == '9') || ($loaninfo->status == '10')) {
                //用户绑定银行卡
                $userbank = User_bank::find()->where(['id' => $loaninfo['bank_id']])->one();
                $loan_coupon_sql = "select l.id,l.limit,l.end_date,l.val,l.status from " . Coupon_list::tableName() . " as l," . Coupon_use::tableName() . " as u where l.id=u.discount_id and u.loan_id=" . $loan_id;
                $loan_coupon = Yii::$app->db->createCommand($loan_coupon_sql)->queryOne();
                $loaninfo['huankuan_amount'] = $loaninfo->getRepaymentAmount($loan_id, $loaninfo->status, $loaninfo->chase_amount, $loaninfo->collection_amount, $loaninfo->like_amount, $loaninfo->amount, $loaninfo->current_amount, $loaninfo->interest_fee, $loaninfo->coupon_amount, $loaninfo->withdraw_fee);
                //服务费
                $service_amount = $loaninfo->getServiceAmount($loan_id, $loaninfo->status, $loaninfo->interest_fee, $loaninfo->withdraw_fee, $loaninfo->coupon_amount, $loaninfo->is_calculation);
                //借款投资记录
                $loanrecord = Yii::$app->db->createCommand($sql_record_loan)->queryAll();
                $shareUrl = Yii::$app->request->hostInfo . "/dev/share/likestat?t=" . $time . "&d=" . $loan_id . "&s=" . md5($time . $loan_id);
                $paystatus = (new Payaccount())->getPaystatusByUserId($loaninfo->user_id, 1, 2);
                $sina_show = 0;
                if ($loaninfo->status == 9) {
                    // $loanextend = $loaninfo->loanextend;
                    // if ($loanextend->status == 'WAITREMIT') {
                    //     $sina_show = 1;
                    // } else {
                    //     $sina_show = 0;
                    // }
                    $remit = User_remit_list::find()->where(['loan_id' => $loaninfo->parent_loan_id])->orderBy('create_time desc')->one();
                    if (empty($remit) || $remit->remit_status != 'SUCCESS') {
                        $loaninfo->status = 6;
                    }
                }
                if ($loaninfo->business_type == 1) {
                    $this->layout = "data";
                    //判断是否符合续期条件
                    $loan_renew_user_model = new Loan_renew_user();
                    $user_allow = $loan_renew_user_model->chooseRenewUser($loaninfo);
                    return $this->render('loansuccok', ['user_status' => $userinfo_stu->status, 'user_allow' => $user_allow, 'loan_coupon' => $loan_coupon, 'loaninfo' => $loaninfo, 'userinfo' => $userinfo, 'userbank' => $userbank, 'loanrecord' => $loanrecord, 'shareurl' => $shareUrl, 'business_type' => $business_type, 'service_amount' => $service_amount, 'jsinfo' => $jsinfo, 'exist' => $exist, 'sinashow' => $sina_show, 'is_show' => $is_show, 'active_show' => $active_show]);
                } else {
                    $this->layout = "loan";
                    return $this->render('succok', ['loan_coupon' => $loan_coupon, 'loaninfo' => $loaninfo, 'userinfo' => $userinfo, 'userbank' => $userbank, 'loanrecord' => $loanrecord, 'shareurl' => $shareUrl, 'business_type' => $business_type, 'service_amount' => $service_amount, 'jsinfo' => $jsinfo]);
                }
            }
            //8.已还款
            else if ($loaninfo->status == '8') {
                $this->layout = "loan";
                $loan_coupon_sql = "select l.id,l.limit,l.end_date,l.val,l.status from " . Coupon_list::tableName() . " as l," . Coupon_use::tableName() . " as u where l.id=u.discount_id and u.loan_id=" . $loan_id;
                $loan_coupon = Yii::$app->db->createCommand($loan_coupon_sql)->queryOne();
                $already_amount = $loaninfo->getRepayAmount(2);
                $loaninfo['huankuan_amount'] = $already_amount === NULL ? 0 : $already_amount;

                //查询用户还款信息
                $repayinfo = Loan_repay::find()->select(array('createtime'))->where(['loan_id' => $loan_id])->orderBy('createtime')->one();
                //服务费
                $service_amount = $loaninfo->getServiceAmount($loan_id, $loaninfo->status, $loaninfo->interest_fee, $loaninfo->withdraw_fee, $loaninfo->coupon_amount, $loaninfo->is_calculation);
                if ($loaninfo->settle_type == 2) {
                    $repay_time = Renewal_payment_record::find()->select(array('last_modify_time'))->where(['loan_id' => $loan_id, 'status' => 1])->one();
                    return $this->render('succrenewal', ['loan_coupon' => $loan_coupon, 'loaninfo' => $loaninfo, 'repayinfo' => $repayinfo, 'business_type' => $business_type, 'service_amount' => $service_amount, 'jsinfo' => $jsinfo, 'repay_time' => $repay_time['last_modify_time']]);
                } else {
                    return $this->render('succend', ['loan_coupon' => $loan_coupon, 'loaninfo' => $loaninfo, 'repayinfo' => $repayinfo, 'business_type' => $business_type, 'service_amount' => $service_amount, 'jsinfo' => $jsinfo]);
                }
            } else if ($loaninfo->status == '12' || $loaninfo->status == '13' || ($loaninfo->chase_amount > 0 && $loaninfo->status != 11)) {
                $loaninfo['huankuan_amount'] = $loaninfo->getRepaymentAmount($loan_id, $loaninfo->status, $loaninfo->chase_amount, $loaninfo->collection_amount, $loaninfo->like_amount, $loaninfo->amount, $loaninfo->current_amount, $loaninfo->interest_fee, $loaninfo->coupon_amount, $loaninfo->withdraw_fee);
                //服务费
                $service_amount = $loaninfo->getServiceAmount($loan_id, $loaninfo->status, $loaninfo->interest_fee, $loaninfo->withdraw_fee, $loaninfo->coupon_amount, $loaninfo->is_calculation);
                //$Url = urlencode(Yii::$app->request->hostInfo . "/dev/share/likestat?t=" . $time . "&d=" . $loan_id . "&s=" . md5($time . $loan_id));
                //$shareUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . self::$_appid . "&redirect_uri=" . $Url . "&response_type=code&scope=snsapi_userinfo&state=xhh123#wechat_redirect";
                $shareUrl = Yii::$app->request->hostInfo . "/dev/share/likestat?t=" . $time . "&d=" . $loan_id . "&s=" . md5($time . $loan_id);
                if ($loaninfo->business_type == 1) {
                    $this->layout = "data";
                    return $this->render('loansuccother', [
                                'user_status' => $userinfo_stu->status,
                                'exist' => $exist,
                                'loaninfo' => $loaninfo,
                                'userinfo' => $userinfo,
                                'business_type' => $business_type,
                                'shareurl' => $shareUrl,
                                'service_amount' => $service_amount,
                                'jsinfo' => $jsinfo,
                                'is_show' => $is_show,
                                'active_show' => $active_show
                    ]);
                } else {
                    $this->layout = "loan";
                    return $this->render('succother', [
                                'exist' => $exist,
                                'loaninfo' => $loaninfo,
                                'userinfo' => $userinfo,
                                'business_type' => $business_type,
                                'shareurl' => $shareUrl,
                                'service_amount' => $service_amount,
                                'jsinfo' => $jsinfo
                    ]);
                }
            } else if ($loaninfo->status == 11) {
                $loaninfo['huankuan_amount'] = $loaninfo->getRepaymentAmount($loan_id, $loaninfo->status, $loaninfo->chase_amount, $loaninfo->collection_amount, $loaninfo->like_amount, $loaninfo->amount, $loaninfo->current_amount, $loaninfo->interest_fee, $loaninfo->coupon_amount, $loaninfo->withdraw_fee);
                //服务费
                $service_amount = $loaninfo->getServiceAmount($loan_id, $loaninfo->status, $loaninfo->interest_fee, $loaninfo->withdraw_fee, $loaninfo->coupon_amount, $loaninfo->is_calculation);
                //$Url = urlencode(Yii::$app->request->hostInfo . "/dev/share/likestat?t=" . $time . "&d=" . $loan_id . "&s=" . md5($time . $loan_id));
                //$shareUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . self::$_appid . "&redirect_uri=" . $Url . "&response_type=code&scope=snsapi_userinfo&state=xhh123#wechat_redirect";
                $shareUrl = Yii::$app->request->hostInfo . "/dev/share/likestat?t=" . $time . "&d=" . $loan_id . "&s=" . md5($time . $loan_id);
                if ($loaninfo->business_type == 1) {
                    $this->layout = "data";
                    return $this->render('loanqud', [
                                'user_status' => $userinfo_stu->status,
                                'exist' => $exist,
                                'loaninfo' => $loaninfo,
                                'userinfo' => $userinfo,
                                'business_type' => $business_type,
                                'shareurl' => $shareUrl,
                                'service_amount' => $service_amount,
                                'jsinfo' => $jsinfo
                    ]);
                } else {
                    $this->layout = "loan";
                    return $this->render('succother', [
                                'exist' => $exist,
                                'loaninfo' => $loaninfo,
                                'userinfo' => $userinfo,
                                'business_type' => $business_type,
                                'shareurl' => $shareUrl,
                                'service_amount' => $service_amount,
                                'jsinfo' => $jsinfo
                    ]);
                }
            } else {
                $this->layout = "loan";
                $loan_coupon_sql = "select l.id,l.limit,l.end_date,l.val,l.status from " . Coupon_list::tableName() . " as l," . Coupon_use::tableName() . " as u where l.id=u.discount_id and u.loan_id=" . $loan_id;
                $loan_coupon = Yii::$app->db->createCommand($loan_coupon_sql)->queryOne();
                $loaninfo['huankuan_amount'] = $loaninfo->getRepaymentAmount($loan_id, $loaninfo->status, $loaninfo->chase_amount, $loaninfo->collection_amount, $loaninfo->like_amount, $loaninfo->amount, $loaninfo->current_amount, $loaninfo->interest_fee, $loaninfo->coupon_amount, $loaninfo->withdraw_fee);

                $start_time = '2016-02-05 12:00:00';
                $end_time = '2016-02-15 10:00:00';
                $now_time = date('Y-m-d H:i:s');
                $loanrecord = Yii::$app->db->createCommand($sql_record_loan)->queryAll();
                //服务费
                $service_amount = $loaninfo->getServiceAmount($loan_id, $loaninfo->status, $loaninfo->interest_fee, $loaninfo->withdraw_fee, $loaninfo->coupon_amount, $loaninfo->is_calculation);
                //$Url = urlencode(Yii::$app->request->hostInfo . "/dev/share/likestat?t=" . $time . "&d=" . $loan_id . "&s=" . md5($time . $loan_id));
                //$shareUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . self::$_appid . "&redirect_uri=" . $Url . "&response_type=code&scope=snsapi_userinfo&state=xhh123#wechat_redirect";
                $shareUrl = Yii::$app->request->hostInfo . "/dev/share/likestat?t=" . $time . "&d=" . $loan_id . "&s=" . md5($time . $loan_id);
                return $this->render('succother', [
                            'loan_coupon' => $loan_coupon,
                            'loanrecord' => $loanrecord,
                            'loaninfo' => $loaninfo,
                            'userinfo' => $userinfo,
                            'business_type' => $business_type,
                            'shareurl' => $shareUrl,
                            'start_time' => $start_time,
                            'end_time' => $end_time,
                            'now_time' => $now_time,
                            'service_amount' => $service_amount,
                            'jsinfo' => $jsinfo
                ]);
            }        
        } else {
            return $this->redirect('/dev/loan');
        }
    }

    public function actionSinapaybackurl($type = 'weixin') {
        $this->layout = 'data';
        $this->getView()->title = '设置成功';
        $openApi = new ApiClientCrypt;
        if (isset($_GET['res_data'])) {
            $data = Yii::$app->request->get('res_data');
        } else {
            $data = Yii::$app->request->post('res_data');
        }
        if (empty($data)) {
            echo '非法请求';
            exit;
        }
        Logger::errorLog(print_r($data, true), 'sinayuanshijihuo');
        $isPost = Yii::$app->request->isPost;
        if ($isPost) {
            $nofify_type = 'post';
        } else {
            $nofify_type = 'get';
        }
        $parr = $openApi->parseReturnData($data);
//        print_r($parr);
        Logger::errorLog(print_r($parr, true), 'sinajihuo');
        $succ = 0;
        if ($parr['res_code'] == 0) {
            $payStatus = new Payaccount();
            if (isset($parr['res_data']['password_valid']) && $parr['res_data']['password_valid'] == 1) {
                $condition = array(
                    'user_id' => $parr['res_data']['user_id'],
                    'type' => 1,
                    'step' => 2,
                    'activate_result' => 1,
                );
                $result = $payStatus->addList($condition);
                $user_loan = User_loan::find()->where(['user_id' => $parr['res_data']['user_id'], 'status' => 9])->one();
                if (!empty($user_loan)) {
                    $user_extend = User_loan_extend::find()->where(['loan_id' => $user_loan->loan_id])->one();
                    if (!empty($user_extend) && $user_extend->status == 'WAITREMIT') {
                        $array = array(
                            'outmoney' => 1,
                            'status' => 'WILLREMIT',
                        );
                        $user_extend->updateUserLoanSubsidiary($array);
                    }
                }
                if ($result) {
                    $succ = 1;
                }
            } else {
                $condition = array(
                    'user_id' => $parr['res_data']['user_id'],
                    'type' => 1,
                    'step' => 2,
                    'activate_result' => 0,
                );
                $result = $payStatus->addList($condition);
            }
        }
        if ($nofify_type == 'get') {
            return $this->render('sinapaybackurl', array('succ' => $succ, 'user_id' => $parr['res_data']['user_id'], 'type' => $type));
        } else {
            echo 'SUCCESS';
            exit;
        }
    }

    public function actionSinaactivate() {
        $user_id = Yii::$app->request->post('user_id');
        $loan = User_loan::find()->where(['user_id' => $user_id, 'status' => 6])->one();
        if (empty($loan)) {
            echo json_encode(array('code' => 2));
            exit;
        }
        $postData = [
            'user_id' => $user_id,
            'passwordurl' => Yii::$app->request->hostInfo . '/dev/loan/sinapaybackurl?type=weixin', // 回调
            'op' => 'set_pay_password', //设置:set_pay_password | 修改:modify_pay_password | 找回 find_pay_password
        ];
//        $payaccount = (new \app\models\dev\Payaccount())->getPaystatusByUserId($user_id, 1, 2);
//        if (empty($payaccount)) {
//            $postData['op'] = 'set_pay_password';
//        }
        $openApi = new ApiClientCrypt;
        $res = $openApi->sent('sinapay/paypassword', $postData);
        $result = $openApi->parseResponse($res);
        Logger::errorLog(print_r($result, true), 'sinajihuo_first');
        if ($result['res_code'] == 0) {
            echo json_encode(array('code' => 0, 'url' => $result['res_data']['redirect_url']));
            exit;
        } else if ($result['res_code'] == "150104") {
            $payStatus = new Payaccount();
            $condition = array(
                'user_id' => $user_id,
                'type' => 1,
                'step' => 2,
                'activate_result' => 1,
            );
            $result = $payStatus->addList($condition);
            $user_extend = User_loan_extend::find()->where(['loan_id' => $loan->loan_id])->one();
            if (!empty($user_extend)) {
                $user_extend->updateUserLoanSubsidiary(array('outmoney' => 1));
            } else {
                (new User_loan_extend())->addList(array('user_id' => $loan->user_id, 'loan_id' => $loan->loan_id, 'outmoney' => 1, 'payment_channel' => 1));
            }
            echo json_encode(array('code' => 11));
            exit;
        } else {
            echo json_encode(array('code' => 1));
            exit;
        }
    }

    //还款
    public function actionRepay() {
        $jsinfo = $this->getWxParam();
        $loan_id = intval($_GET['loan_id']);
        //判断借款的状态，如果是已完成状态，则直接跳转
        $loaninfo = User_loan::find()->where(['loan_id' => $loan_id])->one();
        if ($loaninfo['status'] == 8 || $loaninfo['status'] == 11) {
            return $this->redirect('/dev/loan/succ?l=' . $loan_id);
        }
        $this->getView()->title = "还款方式";
        $loaninfo['huankuan_amount'] = $loaninfo->getRepaymentAmount($loaninfo->loan_id, $loaninfo->status, $loaninfo->chase_amount, $loaninfo->collection_amount, $loaninfo->like_amount, $loaninfo->amount, $loaninfo->current_amount, $loaninfo->interest_fee, $loaninfo->coupon_amount, $loaninfo->withdraw_fee);
        //春节期间，禁止提现
        $start_time = '2016-02-05 12:00:00';
        $end_time = '2016-02-15 10:00:00';
        $now_time = date('Y-m-d H:i:s');

        return $this->render('repay', [
                    'encrypt' => ImageHandler::encryptKey($loaninfo->user_id, 'repay'),
                    'jsinfo' => $jsinfo,
                    'loan_id' => $loan_id,
                    'loaninfo' => $loaninfo,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'now_time' => $now_time,
                    'saveMsg' => '',
        ]);
    }

    public function actionRepaysave() {
        $dir_name = date("Y-m-d");
        $openid = $this->getVal('openid');
        if (empty($openid)) {
            return $this->redirect('/dev/reg/login');
        }
        $userinfo = User::find()->select(array('user_id'))->where(['openid' => $openid])->one();
        $user_id = $userinfo->user_id;
        if (isset($_POST['loan_id'])) {
            $postdata = Yii::$app->request->post();
            $loan_id = $postdata['loan_id'];
            $nowtime = date('Y-m-d H:i:s');
            $transaction = Yii::$app->db->beginTransaction();
            $loan_repay = new Loan_repay();
            $loan_repay->repay_id = date('Ymdhis') . rand(1000, 9999);
            $loan_repay->user_id = $user_id;
            $loan_repay->loan_id = $loan_id;
            foreach ($postdata['supplyUrl'] as $name => $up_info) {
                if (!empty($up_info)) {
                    $name = 'pic_repay' . $name;
                    $loan_repay->$name = $up_info;
                }
            }
            $loan_repay->createtime = $nowtime;
            $loan_repay->status = 3;
            $loan_result = $loan_repay->save();
            if (!$loan_result) {
                $transaction->rollBack();
                return $this->redirect('/dev/loan/loanlist');
            }
            //修改借款记录的状态为11
            $sql = "update " . User_loan::tableName() . " set status=11,last_modify_time='$nowtime',repay_time='$nowtime',version=(version+1) where loan_id=" . $loan_id;
            $ret = Yii::$app->db->createCommand($sql)->execute();
            if (!$ret) {
                $transaction->rollBack();
                return $this->redirect('/dev/loan/loanlist');
            }
            // 还款记录日志
            $loan = User_loan::findOne($loan_id);
            if ($loan->business_type == 3) {
                $gua_sql = "update " . User_guarantee_loan::tableName() . " set status=11,version=(version+1) where loan_id=" . $loan_id;
                $gua_ret = Yii::$app->db->createCommand($gua_sql)->execute();
                if (!$gua_ret) {
                    $transaction->rollBack();
                    return $this->redirect('/dev/loan/loanlist');
                }
            }
            $flow = new Flow();
            $flow->CreateFlow($loan, 0);
            $transaction->commit();
            return $this->redirect('/dev/loan/verify?loan_id=' . $loan_id);
        } else {
            return $this->redirect('/dev/loan/loanlist');
        }
    }

    public function actionVerify($source = 'weixin') {
        $this->getView()->title = "提交审核中";
        $this->layout = 'data';
        $jsinfo = $this->getWxParam();

        return $this->render('verify', [
                    'source' => $source,
                    'jsinfo' => $jsinfo
        ]);
    }

    public function actionLoanlist() {
        $this->getView()->title = "借款记录";
        //$this->layout = 'loan';
        $this->layout = 'loanlist';
        $jsinfo = $this->getWxParam();
        $openid = $this->getVal('openid');
        $userinfo = User::find()->where(['openid' => $openid])->one();
        /*         * *************记录访问日志beigin******************* */
        $ip = Common::get_client_ip();
        $result_log = Common::saveLog('loan', 'loan_list', $ip, 'weixin', $userinfo->user_id);
        /*         * *************记录访问日志end******************* */
        $card_length = strlen($userinfo->identity);
        $sex = $card_length == 15 ? substr($userinfo->identity, 14) : substr($userinfo->identity, 16, 1);
        $time = time();
        $loanlist = User_loan::find()->where(['user_id' => $userinfo->user_id])->orderBy('create_time desc')->all();
        //echo '<pre>';
        // print_r($loanlist);exit;
        foreach ($loanlist as $key => $value) {
            if (($value->status == 9) || ($value->status == 12) || ($value->status == 13)) {
                $loan_coupon_sql = "select l.id,l.limit,l.end_date,l.val,l.status from " . Coupon_list::tableName() . " as l," . Coupon_use::tableName() . " as u where l.id=u.discount_id and u.loan_id=" . $value['loan_id'];
                $loan_coupon = Yii::$app->db->createCommand($loan_coupon_sql)->queryOne();
                if (!empty($loan_coupon) && ($loan_coupon['val'] == 0) && ($loan_coupon['status'] == 2)) {
                    $loanlist[$key]['shareurl'] = Yii::$app->request->hostInfo . "/dev/loan/succ?l=" . $value['loan_id'];
                } else {
                    if ($value->business_type == 1) {
                        $loanlist[$key]['shareurl'] = Yii::$app->request->hostInfo . "/dev/share/likestat?t=" . $time . "&d=" . $value->loan_id . "&s=" . md5($time . $value->loan_id);
                    } else {
                        $loanlist[$key]['shareurl'] = Yii::$app->request->hostInfo . "/dev/loan/succ?l=" . $value['loan_id'];
                    }
                }
            } else {
                $loanlist[$key]['shareurl'] = '';
            }
            $loanlist[$key]['status'] = $value->prome_status == 1 ? 5 : $value->status;
            if ($loanlist[$key]['status'] == 9) {
                $remit = User_remit_list::find()->where(['loan_id' => $value->loan_id])->orderBy('create_time desc')->one();
                if (empty($remit) || $remit->remit_status != 'SUCCESS') {
                    $loanlist[$key]['status'] = 6;
                }
            }
            //判断借款初次发生时间
            if ($value->loan_id != $value->parent_loan_id && !empty($value->parent_loan_id)) {
                $loanlist[$key]['create_time'] = $value->start_date;
            }
        }

        $creditRecord = array();//信用借口
        $securedLoanRecord = array();//担保借款记录
        if (!empty($loanlist)){
            foreach($loanlist as $value){
                if ($value['business_type'] == 1 || $value['business_type'] == 4){
                    $creditRecord[] = $value;
                }else{
                    $securedLoanRecord[] = $value;
                }
            }
        }
        $loanlist  = array(
            'credit' => $creditRecord, //信用借款记录
            'secured' => $securedLoanRecord, //担保借款记录
        );

        return $this->render('loanlist', ['loanlist' => $loanlist, 'sex' => $sex, 'jsinfo' => $jsinfo]);
        //return $this->render('list', ['loanlist' => $loanlist, 'sex' => $sex, 'jsinfo' => $jsinfo]);
    }

    public function actionGuarantee() {
        $this->getView()->title = '担保人借款';
        $this->layout = 'loan';
        $openid = $this->getVal('openid');
        if (empty($openid)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $user = User::find()->select('user_id')->where(['openid' => $openid])->one();
        $loaninfo = User_loan::find()->select('loan_id')->where(['user_id' => $user->user_id, 'status' => array('1', '2', '5', '6', '9', '10', '11', '12', '13'), 'business_type' => array('1', '3', '4')])->one();
        $userbank = User_bank::find()->select(['id', 'type', 'card', 'bank_name', 'bank_abbr'])->where(['user_id' => $user->user_id, 'type' => 0, 'status' => 1])->orderBy('default_bank desc')->all();
        $loan_desc = $this->getCookieVal('loan_desc');
        $loan_days = $this->getCookieVal('loan_days');
        $loan_amount = $this->getCookieVal('loan_amount');
        $card_id = $this->getCookieVal('card_id');
        if ($card_id) {
            $card = User_bank::findOne($card_id);
        } else {
            $card = $userbank[0];
        }
        $jsinfo = $this->getWxParam();
//        var_dump($bank);exit;
        return $this->render('guarantee', [
                    'userbank' => $userbank,
                    'loaninfo' => $loaninfo,
                    'loan_desc' => $loan_desc,
                    'loan_days' => $loan_days,
                    'loan_amount' => $loan_amount,
                    'card' => $card,
                    'user_id' => $user->user_id,
                    'jsinfo' => $jsinfo,
        ]);
    }

    public function actionGuatwo() {
        $this->getView()->title = '担保人借款';
        $this->layout = 'loan';
        $post_data = \Yii::$app->request->post();
//        print_r($post_data);
        $desc = isset($post_data['desc']) ? $post_data['desc'] : $this->getCookieVal('loan_desc');
        $days = isset($post_data['days']) ? $post_data['days'] : $this->getCookieVal('loan_days');
        $amount = isset($post_data['amount']) ? $post_data['amount'] : $this->getCookieVal('loan_amount');
        $card_id = isset($post_data['card_id']) ? $post_data['card_id'] : $this->getCookieVal('card_id');
        //把借款信息存到cookie里
        if (isset($post_data['desc'])) {
            $this->setCookieVal('loan_desc', $desc);
        }
        if (isset($post_data['days'])) {
            $this->setCookieVal('loan_days', $days);
        }
        if (isset($post_data['amount'])) {
            $this->setCookieVal('loan_amount', $amount);
        }
        if (isset($post_data['card_id'])) {
            $this->setCookieVal('card_id', $card_id);
        }
        $openid = $this->getVal('openid');
        //2.判断用户信息是否完善（如果是大学生验证是否学籍验证，如果是上班族是否完善资料）
        $userinfo = User::find()->where(['openid' => $openid])->one();
        //判断用户是否是黑名单用户
        if ($userinfo['status'] == 5) {
            //如果是黑名单用户则直接跳转到黑名单用户页面
            return $this->redirect('/dev/account/black');
        }
        if ($userinfo->school == '' || $userinfo->school_time == '') {
            return $this->redirect('/dev/reg/two?user_id=' . $userinfo->user_id . '&f=loan');
        }
        //3.用户是否拍照
        if ($userinfo->pic_identity == '' || ($userinfo->pic_identity != '' && $userinfo->status == '4')) {
            return $this->redirect('/dev/reg/pic?user_id=' . $userinfo->user_id . '&f=loan');
        }
        $guater = User_guarantee_school::find()->joinWith('user', true, 'LEFT JOIN')->select([User_guarantee_school::tableName() . '.user_id'])->where([User_guarantee_school::tableName() . '.guarantee_school_id' => $userinfo->school_id, User::tableName() . '.user_type' => 4])->all();
        if (count($guater) > 1) {
            $guater_id = $guater[rand(0, count($guater) - 1)]->user_id;
        } else {
            $guater_id = $guater[0]->user_id;
        }
        $card = User_bank::findOne($card_id);
        $withdraw_amount = $amount * 0.01;
        $withdraw_amounts = $withdraw_amount < 5 ? 5 : $withdraw_amount;

        $money = $amount + $amount * 0.002 * $days + $withdraw_amount + $withdraw_amounts;
        $jsinfo = $this->getWxParam();
        return $this->render('guatwo', [
                    'desc' => $desc,
                    'days' => $days,
                    'amount' => $amount,
                    'card' => $card,
                    'money' => $money,
                    'guater_id' => $guater_id,
                    'user_id' => $userinfo->user_id,
                    'jsinfo' => $jsinfo,
        ]);
    }

    public function actionGuaconfirm() {
        $post_data = \Yii::$app->request->post();
        if (!empty($post_data)) {
            $openid = $this->getVal('openid');
            $userinfo = User::find()->where(['openid' => $openid])->one();
            $account = Account::find()->where(['user_id' => $userinfo->user_id])->one();
            //判断用户是否是黑名单用户
            if ($userinfo['status'] == 5) {
                //如果是黑名单用户则直接跳转到黑名单用户页面
                $resultArr = array('ret' => '6', 'url' => '/dev/loan');
                echo json_encode($resultArr);
                exit;
            }
            //判断用户是否有借款
            $status = array('1', '2', '5', '6', '9', '10', '11', '12', '13'); //如果用户存在借款状态为1、2、5、6、8
            $User_loan = User_loan::find()->where(['user_id' => $userinfo->user_id, 'status' => $status, 'business_type' => array(1, 3, 4)])->one();
            if (!empty($User_loan)) {
                $resultArr = array('ret' => '4', 'url' => '/dev/loan');
                echo json_encode($resultArr);
                exit;
            }

            if ($userinfo->status == '4') {
                $resultArr = array('ret' => '5', 'url' => '/dev/loan');
                echo json_encode($resultArr);
                exit;
            }

            //如果用户照片没有审核通过，借款状态为初始（需要审核）
            if ($userinfo->status == '3') {
                $status = 2;
            } else {
                $status = 1;
            }
            //desc: desc, days: days, amount: amount, card_id: card_id, guater_id: guater_id
            $time = date('Y-m-d H:i:s', time());
            $user_id = $userinfo->user_id;
            $desc = $post_data['desc'];
            $days = $post_data['days'];
            $amount = $post_data['amount'];
            $bank_id = $post_data['card_id'];
            $guater_id = $post_data['guater_id'];
            $recharge_amount = 0;
            $current_amount = 0;
            $loan_amount = $amount;
            $open_start_date = $time;
            $open_end_date = date('Y-m-d H:i:s', time() + 3600 * 48);
            $type = 2;
            //如果是免息用户
            $day_rate = Yii::$app->params['day_rate'];
            $interest_fee = round($amount * $day_rate * $days, 2);
            $withdraw_num = (round($amount * 0.01, 2) > 5) ? round($amount * 0.01, 2) : 5;
            $withdraw_fee = round($amount * 0.01, 2) + $withdraw_num;
            $last_modify_time = $time;
            $create_time = $time;
            $loan_sql = "insert into " . User_loan::tableName() . "(user_id,amount,recharge_amount,current_amount,days,open_start_date,open_end_date,type,status,interest_fee,withdraw_fee,`desc`,create_time,last_modify_time,bank_id,business_type) value";
            $loan_sql .= "($user_id,$amount,$recharge_amount,$current_amount,$days,'$open_start_date','$open_end_date',$type,$status,$interest_fee,$withdraw_fee,'$desc','$create_time','$last_modify_time','$bank_id',3)";

            $transaction = Yii::$app->db->beginTransaction();
            $ret = Yii::$app->db->createCommand($loan_sql)->execute();
            if ($ret) {
                $loan_id = Yii::$app->db->getLastInsertID();
                $suffix = $loan_id;
                $size = 6;
                for ($i = 1; $i < $size; $i++) {
                    if (strlen($suffix) < $size)
                        $suffix = '0' . $suffix;
                }
                $loan_no = date("Ymd") . $suffix;

                $sql_loan_no = "update " . User_loan::tableName() . " set loan_no='$loan_no' where loan_id=" . $loan_id;
                $ret_loan_no = Yii::$app->db->createCommand($sql_loan_no)->execute();

                $gua_loan = new User_guarantee_loan();
                $gua_loan->user_id = $user_id;
                $gua_loan->user_guarantee_id = $guater_id;
                $gua_loan->loan_id = $loan_id;
                $gua_loan->status = $status;
                $gua_loan->create_time = date('Y-m-d H:i:s');
                $gua_loan->save();

                //创建订单成功 记录日志
                $loan_flows_sql = "insert into " . Flow::tableName() . " (loan_id,admin_id,loan_status,create_time) value($loan_id,0,$status,'$time')";
                $ret_flows = Yii::$app->db->createCommand($loan_flows_sql)->execute();

                //更新用户账户信息
                if ($status == 2 || $status == 6) {
                    //用户审核通过，需要更新账户
                    $account->recharge_amount -= $recharge_amount;
                    $account->current_amount -=$current_amount;
                    $account->current_loan +=$loan_amount;
                    $account->total_loan +=$loan_amount;
                    $account->version +=1;
                    $ret_acc = $account->save();
                    if ($ret_acc) {
                        if (isset($bank_id) && !empty($bank_id)) {
                            $sql_bank = "update " . User_bank::tableName() . " set default_bank=0 where user_id=" . $user_id;
                            $ret_bank = Yii::$app->db->createCommand($sql_bank)->execute();
                            if ($ret_bank >= 0) {
                                $sql = "update " . User_bank::tableName() . " set default_bank=1 where id=" . $bank_id;
                                Yii::$app->db->createCommand($sql)->execute();
                            }
                        }
                        $transaction->commit();
                        $resultArr = array('ret' => '1', 'url' => '/dev/loan/guasuccess?l=' . $loan_id);
                        echo json_encode($resultArr);
                        exit;
                    } else {
                        $transaction->rollBack();
                        $resultArr = array('ret' => '3', 'url' => '/dev/loan');
                        echo json_encode($resultArr);
                        exit;
                    }
                } else {
                    //用户待审核，借款需要审核
                    $transaction->commit();
                    //return $this->redirect('/dev/loan/audit?l='.$loan_id) ;
                    $resultArr = array('ret' => '2', 'url' => '/dev/loan/audit?l=' . $loan_id);
                    echo json_encode($resultArr);
                    exit;
                }
            } else {
                $transaction->rollBack();
                //return $this->redirect('/dev/loan') ;
                $resultArr = array('ret' => '3', 'url' => '/dev/loan');
                echo json_encode($resultArr);
                exit;
            }
        } else {
            //return $this->redirect('/dev/loan') ;
            $resultArr = array('ret' => '3', 'url' => '/dev/loan');
            echo json_encode($resultArr);
            exit;
        }
    }

    /**
     * 担保人借款成功页
     */
    public function actionGuasuccess() {
        $this->getView()->title = '担保人借款';
        $this->layout = 'loan';
        $loan_id = isset($_GET['l']) ? $_GET['l'] : 0;
        if ($loan_id) {
            $loan = User_guarantee_loan::find()->where(['loan_id' => $loan_id])->one();
            $openid = $this->getVal('openid');
            $user = User::find()->where(['openid' => $openid])->one();
            if ($loan->user_id != $user->user_id) {
                return $this->redirect('/dev/reg/login');
            }
//            print_r($loan);
            $jsinfo = $this->getWxParam();
            return $this->render('guasuccess', [
                        'loan' => $loan,
                        'user_id' => $user->user_id,
                        'jsinfo' => $jsinfo
            ]);
        } else {
            return $this->redirect('/dev/loan');
        }
    }

    public function actionCancle() {
        $loan_id = $_POST['loan_id'];
        if ($loan_id) {
            $loan = User_loan::findOne($loan_id);
            if (empty($loan) || $loan->business_type != 3) {
                $resultArr = array('ret' => '2', 'url' => '/dev/loan', 'msg' => '该借款不存在');
                echo json_encode($resultArr);
                exit;
            } elseif ($loan->status == 17) {
                $resultArr = array('ret' => '4', 'url' => '/dev/loan', 'msg' => '不能重复取消');
                echo json_encode($resultArr);
                exit;
            }
            if ($loan->status == 1 || $loan->status == 2) {
                $transaction = Yii::$app->db->beginTransaction();
                //修改借款状态
                $loan->status = 17;
                $nowtime = date('Y-m-d H:i:s');
                $loan->last_modify_time = $nowtime;
                $loan->version +=1;
                $loan->save();
                $flows = new Flow();
                $flows->CreateFlow($loan, 0);
                //修改借款担保人表
                $gua_loan = User_guarantee_loan::find()->where(['loan_id' => $loan_id])->one();
                $gua_loan->status = 17;
                $gua_loan->version +=1;
                $ret = $gua_loan->save();
                if ($ret) {
                    $transaction->commit();
                    $resultArr = array('ret' => '0', 'url' => '/dev/loan', 'msg' => '取消成功');
                    echo json_encode($resultArr);
                    exit;
                } else {
                    $transaction->rollBack();
                    $resultArr = array('ret' => '3', 'url' => '', 'msg' => '取消失败');
                    echo json_encode($resultArr);
                    exit;
                }
            } else {
                $resultArr = array('ret' => '1', 'url' => '', 'msg' => '该借款暂时不能取消');
                echo json_encode($resultArr);
                exit;
            }
        } else {
            $resultArr = array('ret' => '2', 'url' => '/dev/loan', 'msg' => '该借款不存在');
            echo json_encode($resultArr);
            exit;
        }
    }

    public function actionSendmobile() {
        $loan_id = $_POST['loan_id'];
        if ($loan_id) {
            $loan = User_guarantee_loan::find()->where(['loan_id' => $loan_id])->one();
            if (empty($loan)) {
                $resultArr = array('ret' => '2', 'url' => '/dev/loan', 'msg' => '该借款不存在');
                echo json_encode($resultArr);
                exit;
            } else {
                $guater = User::findOne($loan->user_guarantee_id);
                $mobile = $guater->mobile;
                $loaner = User::findOne($loan->user_id);
                $type = 13;
                $send_redis = Yii::$app->redis->get($loan_id . '_' . $mobile . '_Loan');
                if ($send_redis != 'send') {
                    $content = '亲，' . $loaner->school . '的校友向您发起了一笔借款等待您确认，觉得借款人靠谱请果断投资，如有问题也可拒绝投资，以免给您的账户带来损失。【先花一亿元】';
                    $sendRet = $this->sendMessage($mobile, $content, 13, $loaner->mobile);
                    if ($sendRet) {
                        Yii::$app->redis->setex($loan_id . '_' . $mobile . '_Loan', 43200, 'send');
                        $resultArr = array('ret' => '0', 'url' => '', 'msg' => '发送成功');
                        echo json_encode($resultArr);
                        exit;
                    } else {
                        $resultArr = array('ret' => '1', 'url' => '', 'msg' => '发送失败，请重新发送');
                        echo json_encode($resultArr);
                        exit;
                    }
                } else {
                    $resultArr = array('ret' => '3', 'url' => '', 'msg' => '每12个小时只能发一次');
                    echo json_encode($resultArr);
                    exit;
                }
            }
        } else {
            $resultArr = array('ret' => '2', 'url' => '/dev/loan', 'msg' => '该借款不存在');
            echo json_encode($resultArr);
            exit;
        }
    }

    public function actionGuasucc() {
        $this->layout = "loan";
        if (isset($_GET['l'])) {
            $this->getView()->title = "借款详情";
            $jsinfo = $this->getWxParam();
            $loan_id = $_GET['l'];
            $loaninfo = User_loan::find()->where(['loan_id' => $loan_id])->one();
            $guater = User_guarantee_loan::find()->where(['loan_id' => $loan_id])->one();
            //print_r($guater);exit;
            $userinfo = Userwx::find()->joinWith('user', true, 'LEFT JOIN')->where(['user_id' => $loaninfo->user_id])->one();
            $time = time();
            //根据当前时间计算还剩余的小时数
            $endtime = strtotime($loaninfo->open_end_date);
            if ($loaninfo->status == '1') {
                return $this->redirect('/dev/loan/audit?l=' . $loan_id);
            }
            //2.筹款中
            else {
                return $this->render('guasuccing', ['loaninfo' => $loaninfo, 'guater' => $guater, 'jsinfo' => $jsinfo]);
            }
        }
    }

    /**
     * 判断是否有银行卡或者本校是否有担保人
     */
    public function actionVerifys() {
        $openid = $this->getVal('openid');
        if ($openid) {
            $user = User::find()->where(['openid' => $openid])->one();
            $bank = User_bank::find()->select('id')->where(['user_id' => $user->user_id, 'type' => 0, 'status' => 1])->count();
            if ($bank == 0) {
                $resultArr = array('ret' => '2', 'url' => '/dev/bank/addcard', 'msg' => '您还未绑定借记卡，请去绑定!');
                echo json_encode($resultArr);
                exit;
            }
            //$guater = User_guarantee_school::find()->select('id')->where(['guarantee_school_id' => $user->school_id])->count();
            $guater = User_guarantee_school::find()->joinWith('user', true, 'LEFT JOIN')->select([User_guarantee_school::tableName() . '.user_id'])->where([User_guarantee_school::tableName() . '.guarantee_school_id' => $user->school_id, User::tableName() . '.user_type' => 4])->count();
            if ($guater == 0) {
                $resultArr = array('ret' => '3', 'url' => '', 'msg' => '贵校还未有担保人，请联系先花客服!');
                echo json_encode($resultArr);
                exit;
            } else {
                $resultArr = array('ret' => '0', 'url' => '/dev/loan/guarantee', 'msg' => '');
                echo json_encode($resultArr);
                exit;
            }
        } else {
            $resultArr = array('ret' => '1', 'url' => '/dev/reg/login', 'msg' => '您还未登录，请登录后重进!');
            echo json_encode($resultArr);
            exit;
        }
    }

    public function actionCancleloan() {
        $loan_id = $_POST['loan_id'];
        $openid = $this->getVal('openid');
        if (empty($loan_id) || empty($openid)) {
            $resultArr = array('ret' => '1', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }

        $loaninfo = User_loan::findOne($loan_id);
        if ($loaninfo->status != 2 && $loaninfo->status != 5) {
            $resultArr = array('ret' => '3', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }
        $user = User::find()->where(['openid' => $openid])->one();

        $result = $this->cancleLoan($user, $loaninfo);
        if ($result) {
            $resultArr = array('ret' => '0', 'url' => '');
            echo json_encode($resultArr);
            exit;
        } else {
            $resultArr = array('ret' => '2', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }
    }

    private function cancleLoan($user, $loan) {
        if ($loan->status != 2 && $loan->status != 5) {
            return false;
        }
        $status = 17;
        $loan = $loan->changeStatus($status, $user->user_id);
        if ($loan) {
            return true;
        } else {
            return false;
        }
    }

    public function actionRemark() {
        $this->layout = "inv";
        $this->getView()->title = "借款详情";
        $jsinfo = $this->getWxParam();
        return $this->render('remark', [
                    'jsinfo' => $jsinfo,
        ]);
    }

    public function actionSpring() {
        $this->layout = "inv";
        $this->getView()->title = "重要通知";
        $jsinfo = $this->getWxParam();
        return $this->render('spring', [
                    'jsinfo' => $jsinfo,
        ]);
    }

    public function actionError() {
        return $this->render('error');
    }

    public function actionLoanerror() {
        return $this->render('loanerror');
    }

    /**
     * 判断关键词是否在输入的语句中
     */
    private function strstring($keyword, $reject = array()) {
        $mark = 0;
        foreach ($reject as $val) {
            if (strstr($keyword, $val)) {
                $mark = 1;
                break;
            }
        }
        return $mark;
    }

}
