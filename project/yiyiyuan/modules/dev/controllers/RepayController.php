<?php

namespace app\modules\dev\controllers;

use app\commands\SubController;
use app\common\yeepay\QuickYeepay;
use app\models\dev\Coupon_list;
use app\models\dev\Coupon_use;
use app\models\dev\Loan_repay;
use app\models\dev\User;
use app\models\dev\User_bank;
use app\models\dev\User_loan;
use app\models\dev\CardLimit;
use app\models\dev\Loan_renew_user;
use Yii;
use app\common\ApiClientCrypt;
use app\commonapi\Common;
use app\commonapi\Logger;


class RepayController extends SubController {

    public $layout = 'loan';
    public $enableCsrfValidation = false;
    private $quickYeepay;

    public function init() {
        //parent::init();
        $this->quickYeepay = new QuickYeepay();
    }

    public function actionIndex() {
        return $this->render('index');
    }

    public function actionCards() {
        $this->layout = 'data';
        $this->getView()->title = "还款";
        $loan_id = intval($_GET['loan_id']);
//        return $this->redirect('/dev/loan/repay?loan_id='.$loan_id);
        if (!empty($loan_id)) {
            $loaninfo = User_loan::find()->where(['loan_id' => $loan_id])->one();
            if ($loaninfo['status'] == 8 || $loaninfo['status'] == 11) {
                return $this->redirect('/dev/loan/succ?l=' . $loan_id);
            }
            $huankuan = $loaninfo->getRepaymentAmount($loaninfo->loan_id, $loaninfo->status, $loaninfo->chase_amount, $loaninfo->collection_amount, $loaninfo->like_amount, $loaninfo->amount, $loaninfo->current_amount, $loaninfo->interest_fee, $loaninfo->coupon_amount, $loaninfo->withdraw_fee);
            $loaninfo->huankuan_amount = $huankuan;
            $info = User_bank::find()->where(['user_id' => $loaninfo['user_id'], 'status' => 1])->orderBy(' default_bank desc,last_modify_time desc');
            $user_banks = $info->asArray()->all();
            $count = $info->count();
            if (empty($user_banks)) {
                echo "系统错误";
                exit;
            }
            $user = User::find()->where(['user_id' => $loaninfo['user_id']])->one();
            /***************记录访问日志beigin********************/
            $ip = Common::get_client_ip();
            $result_log = Common::saveLog('repay', 'repay_button', $ip, 'weixin', $user->user_id);
            /***************记录访问日志end********************/
            $jsinfo = $this->getWxParam();
            
            
            //查询还款限制卡信息
//            $repay_cards = CardLimit::find()->select(['bank_name','card_type'])->where(['type' => 2, 'status' => 1])->asArray()->all();
//            if(empty($repay_cards)){
//                $repay_cards = array( 
//                    array('bank_name'=>"MMMMM",'card_type'=>"m"),
//                    );
//            }
            
            //是否只有一张卡并且被限制
            $flag = 1;
            
            if($count > 1){
                $userbank =(new User_bank())->limitCardsSort($loaninfo['user_id'],1);
            }
            if($count == 1){
                $userbank =(new User_bank())->limitCardsSort($loaninfo['user_id'],1);
                if($userbank[0]['sign'] == 1){
                    $flag = 2;
                }
            }
            return $this->render('cards', [
                        'flag' => $flag,
//                        'bank' => $repay_cards,
                        'userbank' => $userbank,
                        'loaninfo' => $loaninfo,
                        'user' => $user,
                        'jsinfo' => $jsinfo,
            ]);
        } else {
            echo '系统错误';
            exit;
        }
    }

    /**
     * 待还款金额
     * @param type $loan_id
     * @return type
     */
    public function Amount($loaninfo) {//$loaninfo
        $loan_id = $loaninfo['loan_id'];
        $loan_coupon_sql = "select l.id,l.limit,l.end_date,l.val,l.status from " . Coupon_list::tableName() . " as l," . Coupon_use::tableName() . " as u where l.id=u.discount_id and u.loan_id=" . $loan_id;
        $loan_coupon = Yii::$app->db->createCommand($loan_coupon_sql)->queryOne();
        if ($loan_id <= 38841) {
            if (!empty($loaninfo['chase_amount'])) {
                //$loaninfo['huankuan_amount'] = $loaninfo['chase_amount']+$loaninfo['collection_amount']-$loaninfo['like_amount']-$loaninfo['coupon_amount'];
                $loaninfo['huankuan_amount'] = $loaninfo['chase_amount'] + $loaninfo['collection_amount'] - $loaninfo['like_amount'];
                //
            } else {
                if ($loaninfo['current_amount'] < $loaninfo['amount']) {
                    //$loaninfo['huankuan_amount'] = $loaninfo['current_amount']+$loaninfo['interest_fee']+$loaninfo['withdraw_fee']+$loaninfo['collection_amount']-$loaninfo['like_amount']-$loaninfo['coupon_amount'];
                    $loaninfo['huankuan_amount'] = $loaninfo['current_amount'] + $loaninfo['interest_fee'] + $loaninfo['collection_amount'] - $loaninfo['like_amount'];
                } else {
                    //$loaninfo['huankuan_amount'] = $loaninfo['amount']+$loaninfo['interest_fee']+$loaninfo['withdraw_fee']+$loaninfo['collection_amount']-$loaninfo['like_amount']-$loaninfo['coupon_amount'];
                    $loaninfo['huankuan_amount'] = $loaninfo['amount'] + $loaninfo['interest_fee'] + $loaninfo['collection_amount'] - $loaninfo['like_amount'];
                }
            }
        } else {
            if (!empty($loan_coupon) && ($loan_coupon['val'] == 0) && ($loan_coupon['status'] == 2)) {
                if (!empty($loaninfo['chase_amount'])) {
                    $loaninfo['huankuan_amount'] = $loaninfo['chase_amount'] + $loaninfo['collection_amount'];
                } else {
                    if ($loaninfo['current_amount'] < $loaninfo['amount']) {
                        $loaninfo['huankuan_amount'] = $loaninfo['current_amount'] + $loaninfo['collection_amount'] - $loaninfo['like_amount'] - $loaninfo['coupon_amount'];
                    } else {
                        $loaninfo['huankuan_amount'] = $loaninfo['amount'] + $loaninfo['collection_amount'] - $loaninfo['like_amount'] - $loaninfo['coupon_amount'];
                    }
                }
            } else {
                if (!empty($loaninfo['chase_amount'])) {
                    $loaninfo['huankuan_amount'] = $loaninfo['chase_amount'] + $loaninfo['collection_amount'];
                } else {
                    if ($loaninfo['current_amount'] < $loaninfo['amount']) {
                        $loaninfo['huankuan_amount'] = $loaninfo['current_amount'] + $loaninfo['interest_fee'] + $loaninfo['withdraw_fee'] + $loaninfo['collection_amount'] - $loaninfo['like_amount'] - $loaninfo['coupon_amount'];
                    } else {
                        $loaninfo['huankuan_amount'] = $loaninfo['amount'] + $loaninfo['interest_fee'] + $loaninfo['withdraw_fee'] + $loaninfo['collection_amount'] - $loaninfo['like_amount'] - $loaninfo['coupon_amount'];
                    }
                }
            }
        }
        //loan_id
        //status=1
        //paybill
        $repay_sql = "SELECT SUM(actual_money) AS actual_money FROM " . Loan_repay::tableName() . " WHERE `loan_id` = '$loan_id' AND `status` = '1'";
        $actual_money = Yii::$app->db->createCommand($repay_sql)->queryOne();
        if ($actual_money['actual_money'] != 0) {
            $loaninfo['huankuan_amount'] = round($loaninfo['huankuan_amount'], 2) - round($actual_money['actual_money'], 2);
        }
        return $loaninfo;
    }

    public function actionPayyibao() {
        $post_data = \Yii::$app->request->post();
        $loan_id = isset($post_data['loan_id']) ? intval($post_data['loan_id']) : 0;
        if (!$loan_id) {
            echo "数据错误,请刷新页面重新获取";
            exit;
        }
        
        $loaninfo = User_loan::find()->where(['loan_id' => $loan_id])->one();
        if (!empty($loaninfo)) {
            $user = User::find()->where(['user_id' => $loaninfo['user_id']])->one();
            $user_id = $user['user_id'];
            $orderid = date('YmdHis') . rand(1000, 9999);
            $money = isset($post_data['money_order']) ? floatval($post_data['money_order']) * 100 : '';
//            $money = 200;
            $card_id = $post_data['card_id'];
            $bank = User_bank::findOne($card_id);
            $card_no = $bank->card;
            $times = date('Y-m-d H:i:s');
            $loan_repay = new Loan_repay();
            $loan_repay->repay_id = $orderid;
            $loan_repay->user_id = $user_id;
            $loan_repay->loan_id = $loan_id;
            $loan_repay->bank_id = $card_id;
            $loan_repay->money = isset($post_data['money_order']) ? floatval($post_data['money_order']) : '';
            $loan_repay->last_modify_time = $times;
            $loan_repay->createtime = $times;
            $loan_repay->platform = 2;
            $ret = $loan_repay->save();
            if ($ret) {
                $card_type = ($bank->type == 0) ? 1 : 2;
                $phone = isset($bank->bank_mobile) ? $bank->bank_mobile : $user->mobile;
                $postData = array(
                    'orderid' => $orderid, // 请求唯一号
                    'identityid' => (string) $user_id, // 用户标识
                    'bankname' => $bank->bank_name, //银行名称
                    'bankcode' => $bank->bank_abbr, //银行编码
                    'card_type' => $card_type, // 卡类型
                    'cardno' => $bank->card, // 银行卡号
                    'idcard' => $user->identity, // 身份证号
                    'username' => $user->realname, // 姓名
                    'phone' => $phone, // 预留手机号
                    'productcatalog' => '7', // 商品类别码
                    'productname' => '购买电子产品', // 商品名称
                    'productdesc' => '购买电子产品', // 商品描述
                    'amount' => $money, // 交易金额
                    'orderexpdate' => 60, // 交易金额
                    'business_code' => 'YYYWX',
                    'userip' => $_SERVER["REMOTE_ADDR"], // 交易金额
                    'callbackurl' => Yii::$app->params['yibao_repay'], // 交易金额
                );
                $openApi = new ApiClientCrypt;
                Logger::errorLog(print_r($postData, true), 'openpay');
                $res = $openApi->sent('payroute/pay', $postData,2);
                $result = $openApi->parseResponse($res);
                Logger::errorLog(print_r($result, true), 'openpay');
                if ($result['res_code'] == 0) {
                    return $this->redirect($result['res_data']['url']);
                } else {
                    return $this->redirect('/dev/repay/error');
                }
//                 $quickYeepay = $this->quickYeepay;
//                 $result = $quickYeepay->payRequest([
//                     'orderid' => $orderid, //		//客户订单号   √   string  商户生成的唯一订单号，最长50位
//                     'transtime' => time(), //交易时间    √   int     时间戳，例如：1361324896，精确到秒
//                     'currency' => 156, //交易币种      int     默认156人民币(当前仅支持人民币)
//                     'amount' => $money, //交易金额    √   int     以"分"为单位的整型，必须大于零
//                     'productcatalog' => '7', //商品类别码   √   string  详见商品类别码表
//                     'productname' => '购买电子产品', //商品名称    √   string  最长50位，出于风控考虑，请按下面的格式传递值：'应用商品名称，如“诛仙-3阶成品天琊”，此商品名在发送短信校验的时候会发给用户，所以描述内容不要加在此参数中，以提高用户的体验度。
//                     'productdesc' => '', //商品描述     最长200位
//                     'identityid' => (string)$user_id, //用户标识    √   string  最长50位，商户生成的用户账号唯一标识
//                     'identitytype' => 0, //用户标识类型  √   int     详见用户标识类型码表
//                     'terminaltype' => 3, //终端类型    √   int     0、IMEI；1、MAC；2、UUID；3、other
//                     'terminalid' => (string)$user_id, //终端ID    √ string  
//                     'orderexpdate' => 60, //订单有效期时间       int     以分为单位
//                     'userip' => $_SERVER["REMOTE_ADDR"], //用户IP    √   string  用户支付时使用的网络终端IP
//                     'userua' => '', //终端UA    √   string  用户使用的移动终端的UA信息
//                     'callbackurl' => Yii::$app->params['yibao_repay'], //商户后台系统的回调地址       string  用来通知商户支付结果，前后台回调地址的回调内容相同
//                     'fcallbackurl' => Yii::$app->params['app_url'] . '/dev/repay/success', //商户前台系统提供的回调地址     string  '用来通知商户支付结果，前后台回调地址的回调内容相同。用户在网页支付成功页面，点击“返回商户”时的回调地址
//                     'version' => 0, //网页收银台版本        int     商户可以使用此参数定制调用的网页收银台版本，目前只支持wap版本（参数传值“0”或不传值）
//                     'paytypes' => '1|2', //支付方式      string  格式：1|2|3|4 1- 借记卡支付；2- 信用卡支付；3- 手机充值卡支付；4- 游戏点卡支付注：'该参数若不传此参数，则默认选择运营后台为该商户开通的支付方式。
//                     'cardno' => $card_no, //6214830119208287		//银行卡序列号   在进行网页支付请求的时候，如果传此参数会把银行卡号直接在银行信息界面显示卡号，注意：P2P商户此参数须必填
//                     'idcardtype' => '01', //证件类型      01：身份证，注意：证件类型和证件号必须同时为空或者同时不为空
//                     'idcard' => $user->identity, //证件号     注意：P2P商户此参数须必填
//                     'owner' => $user->realname, //持卡人姓名      注意：P2P商户此参数须必填
//                 ]);
//                 return $this->redirect($result);
            } else {
                return $this->redirect('/dev/guarantee/error');
            }
        } else {
            return $this->redirect('/dev/guarantee/error');
        }
    }
    
    public function actionPaylian() {
        $post_data = \Yii::$app->request->post();
        $loan_id = isset($post_data['loan_id']) ? intval($post_data['loan_id']) : 0;
        if (!$loan_id) {
            echo "数据错误,请刷新页面重新获取";
            exit;
        }
        $loaninfo = User_loan::find()->where(['loan_id' => $loan_id])->one();
        if (!empty($loaninfo)) {
            $user = User::find()->where(['user_id' => $loaninfo['user_id']])->one();
            $user_id = $user['user_id'];
            $no_order = date('YmdHis') . rand(1000, 9999);
            //@TODO:暂时连连支付属于互联网还款  entity：实名类,互联网还款
            $merchant_type = 'entity';
            //@TODO:连连目前只支持wap  app_request=1
            $app_request = 1;
            $dt_order = date('YmdHis');

            $name_goods = '一亿元还款';
            //@TODO:正式上线改为实际获取的金额，没有默认
            $money_order = isset($post_data['money_order']) ? floatval($post_data['money_order']) : '';

            $id_no = $user['identity'];
            $acct_name = $user['realname'];
            $card_id = $post_data['card_id'];
            $bank = User_bank::findOne($card_id);
            $pay_type = $bank->type + 2;
            $card_no = $bank->card;
            $bind_mob = !empty($bank->bank_mobile) ? $bank->bank_mobile : $user->mobile;
//             if ($pay_type == 3) {
//                 $des3key = Yii::$app->params['des3key'];
//                 $validate = $bank->validate;
//                 $cvv2 = \Crypt3Des::decrypt($bank->cvv2, $des3key);
//             }

            $notify_url = Yii::$app->params['ll_notify_url'];
            $url_return = Yii::$app->params['app_url'] . '/dev/repay/success';

            $risk['frms_ware_category'] = $merchant_type == 'entity' ? '1010 ' : '2010';
            $risk['user_info_mercht_userno'] = $user_id;
            $risk['user_info_dt_register'] = date('YmdHis', strtotime($user['create_time']));
            $risk_item = json_encode($risk);
            $createtime = date('Y-m-d H:i:s', strtotime($dt_order));
            $times = date('Y-m-d H:i:s');
            $sql = "INSERT INTO " . Loan_repay::tableName() . " (`repay_id`, `user_id`, `loan_id`, `bank_id`,`money`,`last_modify_time`, `createtime`) ";
            $sql .= " VALUES ('$no_order', '$user_id', '$loan_id', '$card_id','$money_order','$times', '$createtime')";
            $ret = Yii::$app->db->createCommand($sql)->execute();
            if ($ret) {
                if ($pay_type == 2) {
                    $result = \Http::payLianLian($user_id, $merchant_type, $app_request, $no_order, $dt_order, $name_goods, $money_order, $notify_url, $url_return, $risk_item, $pay_type, $id_no, $acct_name, $card_no);
                } else {
                    $result = \Http::payLianLian($user_id, $merchant_type, $app_request, $no_order, $dt_order, $name_goods, $money_order, $notify_url, $url_return, $risk_item, $pay_type, $id_no, $acct_name, $card_no);
                }
                if ($result) {
//                     $arr = $this->classToArray($result);
//                     $arr['repay_id'] = $no_order;
//                     return json_encode($arr);
                    $redirect_url = trim($result->lianlian_payment);
                    return $this->redirect($redirect_url);
                } else {
//                     return json_encode(array(
//                         'rsp_code' => '99990',
//                         'rsp_msg' => '操作失败',
//                     ));
                    return $this->redirect('/dev/guarantee/error');
                }
            } else {
//                 return json_encode(array(
//                     'rsp_code' => '99990',
//                     'rsp_msg' => '操作失败',
//                 ));
                return $this->redirect('/dev/guarantee/error');
            }
            //$result = Loan_repay;
        } else {
//             return json_encode(array(
//                 'rsp_code' => '99990',
//                 'rsp_msg' => '借款记录不存在',
//             ));
            return $this->redirect('/dev/guarantee/error');
        }
    }

    public function actionPay() {
        $post_data = \Yii::$app->request->post();
        //@TODO:暂时连连支付属于互联网还款  entity：实名类,互联网还款
        $merchant_type = 'entity';
        //@TODO:连连目前只支持wap  app_request=1
        $app_request = 1;
        $loan_id = intval($post_data['loan_id']);
        $loaninfo = User_loan::find()->where(['loan_id' => $loan_id])->one();
        $times = date('Y-m-d H:i:s');
        if (!empty($loaninfo)) {
            $bank = User_bank::findOne($post_data['card_id']);
            $bind_mob = $bank->bank_mobile;

            $pay_key = isset($post_data['pay_key']) ? $post_data['pay_key'] : '';
            $verifyCode = isset($post_data['verifyCode']) ? $post_data['verifyCode'] : '';
            $isrecord = isset($post_data['isrecord']) ? $post_data['isrecord'] : 'no';
            $loan_repay = Loan_repay::find()->where(['repay_id' => $post_data['repay_id']])->one();
            $loan_repay->pay_key = $post_data['pay_key'];
            $loan_repay->code = $post_data['verifyCode'];
            $loan_repay->last_modify_time = $times;
            $ret = $loan_repay->save();
            if ($ret) {
                $result = \Http::subPayLian($merchant_type, $app_request, $pay_key, $bind_mob, $verifyCode, $isrecord);
                if ($result) {
                    $parr = $this->classToArray($result);
//                    if (isset($parr['result_pay']) && $parr['rsp_code'] == '0000') {
//                        if (!empty($loan_repay) && empty($loan_repay->paybill)) {
//                            $transaction = Yii::$app->db->beginTransaction();
//                            $loan_id = $loan_repay->loan_id;
//                            $times = date('Y-m-d H:i:s');
//                            $loan_repay->status = 1;
//                            $loan_repay->actual_money = $parr['money_order'];
//                            $loan_repay->paybill = $parr['oid_paybill'];
//                            $loan_repay->last_modify_time = $times;
//                            $ret = $loan_repay->save();
//                            if ($ret) {
//                                $loaninfo = User_loan::find()->where(['loan_id' => $loan_id])->one();
//                                $money_act = $this->Amount($loaninfo);
//                                if ($money_act['huankuan_amount'] <= 0) {
//                                    $status = 8;
//                                    $loaninfo->status = $status;
//                                    $loaninfo->repay_type = 2;
//                                    $loaninfo->last_modify_time = $times;
//                                    $loaninfo->repay_time = $times;
//                                    $loaninfo->save();
//                                    //查询借款人姓名
//                                    $userinfo = User::find()->select(array('realname'))->where(['user_id' => $loaninfo['user_id']])->one();
//                                    $realname = $userinfo['realname'];
//                                    if ($loaninfo->business_type == 1) {
//                                        //借款人账户当前借款和总借款减
//                                        $loan_acc = "update " . Account::tableName() . " set current_loan=current_loan-" . $loaninfo->amount . ",total_loan=total_loan-" . $loaninfo->amount . ",version=version+1 where user_id=" . $loaninfo->user_id;
//                                        $ret_loan_acc = Yii::$app->db->createCommand($loan_acc)->execute();
//
//                                        //该笔借款已经完成，把投资人的收益返还至投资人的账户
//                                        $nowtime = date('Y' . '年' . 'm' . '月' . 'd' . '日' . ' H:i');
//                                        $sql = "select l.days,i.amount,i.yield,i.user_id,i.status,i.invest_id,i.create_time,u.openid from yi_user_invest as i,yi_user_loan as l,yi_user as u where l.loan_id='$loan_id' and i.loan_id='$loan_id' and i.user_id=u.user_id";
//                                        $investlist = Yii::$app->db->createCommand($sql)->queryAll();
//                                        foreach ($investlist as $key => $value) {
//                                            //修改投资记录的状态
//                                            $sql_invest = "update yi_user_invest set status = 3,version=(version+1) where invest_id=" . $value['invest_id'];
//                                            Yii::$app->db->createCommand($sql_invest)->execute();
//                                            //计算投资人的收益
//                                            $profit = ($value['amount'] * ($value['yield'] / 100) / 365) * $value['days'];
//                                            $income_amount = sprintf("%.2f", $profit);
//                                            //更新投资人的额度和收益
//                                            $sql_profit = "update yi_account set current_invest=current_invest-" . $value['amount'] . ",total_invest=total_invest-" . $value['amount'] . ",current_amount=current_amount+" . $value['amount'] . ",total_income=(total_income+" . $income_amount . "),version=(version+1) where user_id=" . $value['user_id'];
//                                            Yii::$app->db->createCommand($sql_profit)->execute();
//
//                                            //向投资人推送模板消息
//                                            $invest_time = date('m' . '月' . 'd' . '日', strtotime($value['create_time']));
//                                            $template_id = Yii::$app->params['profit_template_id'];
//                                            if (!empty($value['openid'])) {
//                                                $openid = $value['openid'];
//                                                $url = Yii::$app->request->hostInfo . "/dev/account/investdetail?user_id=" . $value['user_id'] . "&invest_id=" . $value['invest_id'];
//
//                                                $data = '{
//                                                                                                       "touser":"' . $openid . '",
//                                                                                                       "template_id":"' . $template_id . '",
//                                                                                                       "url":"' . $url . '",
//                                                                                                       "topcolor":"#FF0000",
//                                                                                                       "data":{
//                                                                                                               "first": {
//                                                                                                                   "value":"恭喜您在' . $invest_time . '投资' . $realname . '的收益已经到账啦！",
//                                                                                                                   "color":"#173177"
//                                                                                                               },
//                                                                                                               "income_amount":{
//                                                                                                                   "value":"' . $income_amount . '元",
//                                                                                                                   "color":"#173177"
//                                                                                                               },
//                                                                                                               "income_time": {
//                                                                                                                   "value":"' . $nowtime . '",
//                                                                                                                   "color":"#173177"
//                                                                                                               },
//                                                                                                               "remark":{
//                                                                                                                   "value":"快去投资更多靠谱的小伙伴吧！！首次借款还有更多惊喜呦！>>点击查看",
//                                                                                                                   "color":"#173177"
//                                                                                                               }
//                                                                                                       }
//                                                                                                  }';
//                                                //print_r($data);exit;
//                                                $resulttemplate = $this->sendTemplatetouser($data);
//                                                Logger::errorLog(print_r($resulttemplate, true), 'sendtemplatetouserbyshouyi');
//                                            }
//                                        }
//
//                                        //如果第一次在正常时间内还款，则提升与借款额度相同的信用额度，后面的所有借款在正常时间内还款，都提升借款额度50%的信用值
//                                        //首先判断是否是第一次在还款期内成功还款
//                                        //判断此次还款是否逾期,如果未逾期则提额，否则不提额
//                                        if (empty($loaninfo['chase_amount'])) {
//                                            $loaninfonotthis = User_loan::find()->where(['user_id' => $loaninfo['user_id']])->andWhere(['status' => 8])->andWhere("loan_id != $loan_id and chase_amount is NULL")->one();
//                                            if (!empty($loaninfonotthis)) {
//                                                //不是第一次，提升50%的额度
//                                                if ($loaninfo['current_amount'] < $loaninfo['amount']) {
//                                                    $up_amount = floor($loaninfo['current_amount'] / 2);
//                                                } else {
//                                                    $up_amount = floor($loaninfo['amount'] / 2);
//                                                }
//                                                //提升额度
//                                                $sql_up = "update " . Account::tableName() . " set remain_amount=(remain_amount-" . $up_amount . "),amount=(amount+" . $up_amount . "),current_amount=(current_amount+" . $up_amount . ") where user_id=" . $loaninfo['user_id'];
//                                                Yii::$app->db->createCommand($sql_up)->execute();
//
//                                                //记录提额的日志
//                                                $amount_date = array(
//                                                    'type' => 8,
//                                                    'user_id' => $loaninfo['user_id'],
//                                                    'amount' => $up_amount
//                                                );
//                                                $user_amount = new User_amount_list();
//                                                $user_amount->CreateAmount($amount_date);
//                                            } else {
//                                                //是第一次，则提升与借款额度相同的信用额度
//                                                if ($loaninfo['current_amount'] < $loaninfo['amount']) {
//                                                    $up_amount = floor($loaninfo['current_amount']);
//                                                } else {
//                                                    $up_amount = floor($loaninfo['amount']);
//                                                }
//                                                //提升额度
//                                                $sql_up = "update " . Account::tableName() . " set remain_amount=(remain_amount-" . $up_amount . "),amount=(amount+" . $up_amount . "),current_amount=(current_amount+" . $up_amount . ") where user_id=" . $loaninfo['user_id'];
//                                                Yii::$app->db->createCommand($sql_up)->execute();
//
//                                                //记录提额的日志
//                                                $amount_date = array(
//                                                    'type' => 7,
//                                                    'user_id' => $loaninfo['user_id'],
//                                                    'amount' => $up_amount
//                                                );
//                                                $user_amount = new User_amount_list();
//                                                $user_amount->CreateAmount($amount_date);
//                                            }
//                                        }
//                                    }
//
//                                    $sql_flow = "INSERT INTO  " . User_loan_flows::tableName() . "  (`loan_id`, `admin_id`, `loan_status`, `create_time`) VALUES ('$loan_id', '-1', '$status', '$times')";
//                                    $ret_flows = Yii::$app->db->createCommand($sql_flow)->execute();
//                                    if ($ret_flows) {
//                                        $transaction->commit();
//                                    } else {
//                                        $transaction->rollBack();
//                                    }
//                                } else {
//                                    $transaction->commit();
//                                }
//                            } else {
//                                $transaction->rollBack();
//                            }
//                        }
//                    }

                    return json_encode($parr);
                } else {
                    return json_encode(array(
                        'rsp_code' => '99990',
                        'rsp_msg' => '操作失败',
                    ));
                }
            } else {
                return json_encode(array(
                    'rsp_code' => '99990',
                    'rsp_msg' => '数据错误',
                ));
            }
        } else {
            return json_encode(array(
                'rsp_code' => '99990',
                'rsp_msg' => '借款记录不存在',
            ));
        }
    }

    //回显地址
    public function actionSuccess($source = 'weixin') {
        $this->getView()->title = '提交成功';
        $this->layout = 'data';
        $jsinfo = $this->getWxParam();
        return $this->render('/loan/verify', ['source' => $source,'jsinfo' => $jsinfo]);
    }

    public function actionAgreement() {
        return $this->render('agreement');
    }

    public function classToArray($cla) {
        $arr = array();
        foreach ($cla as $key => $val) {
            $arr[$key] = $val;
        }
        return $arr;
    }

    public function actionError($source = '') {
        $this->getView()->title = '还款失败';
        $jsinfo = $this->getWxParam();
        return $this->render('error', ['source' => $source,'jsinfo' => $jsinfo]);
    }

    public function actionNotifysuccess() {
        $this->layout = 'data';
        $repay_id = $_GET['repay_id'];
        $loan_repay = Loan_repay::find()->where(['repay_id' => $repay_id])->one();
        $this->getView()->title = '提交成功';
        $jsinfo = $this->getWxParam();
        if($loan_repay['source']==1){
            $source = 'weixin';
        }else{
            $source = 'app';
        }
        return $this->render('/loan/verify', ['jsinfo' => $jsinfo, 'source' => $source]);
    }
    
    public function actionRepaychoose(){
        $this->layout = 'data';
        $this->getView()->title = "还款";
        $loan_id = Yii::$app->request->get('loan_id');
        $loan = User_loan::findOne($loan_id);
        if (empty($loan)) {
            return $this->redirect('/dev/loan');
        }
        $huankuan = $loan->getRepaymentAmount($loan->loan_id, $loan->status, $loan->chase_amount, $loan->collection_amount, $loan->like_amount, $loan->amount, $loan->current_amount, $loan->interest_fee, $loan->coupon_amount, $loan->withdraw_fee);
        $loan->huankuan_amount = $huankuan;
        $bankModel = new User_bank();
        $bank_count = User_bank::find()->where(['user_id' => $loan['user_id'], 'status' => 1])->count();
        $banklist = $bankModel->limitCardsSort($loan->user_id, 1);
        $bank_str  = Common::ArrayToString($banklist, 'sign');
        $bank_arr = explode(',', $bank_str);
        if(!in_array('2', $bank_arr)){
            //无可用卡
            $flag = 2;
        }else{
            $flag = 1;
        }
        //用户是否可续期
//        $user_allow = 1;
        $loan_renew_user_model = new Loan_renew_user();
        $user_allow = $loan_renew_user_model->chooseRenewUser($loan);
        //还款时间
        $end_date = (new User_loan())->getHuankuanTime($loan->status, $loan->end_date);
        $jsinfo = $this->getWxParam();
        return $this->render('repaychoose', [
                    'user_allow' => $user_allow,
                    'jsinfo' => $jsinfo,
                    'flag' => $flag,
                    'loan' => $loan,
                    'end_date' => $end_date,
                    'banklist' => $banklist,
                    'bank_count' => $bank_count,
                    'csrf' => $this->getCsrf(),
            ]);
    }

    /**
     * 获取csrf
     * @return string
     */
    private function getCsrf(){
        $csrf = Yii::$app->request->getCsrfToken();
        return $csrf;
    }



}
