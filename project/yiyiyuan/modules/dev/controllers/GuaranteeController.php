<?php

namespace app\modules\dev\controllers;

use app\commands\SubController;
use app\common\ApiClientCrypt;
use app\common\yeepay\QuickYeepay;
use app\commonapi\apiInterface\Remit;
use app\commonapi\Crypt3Des;
use app\commonapi\Http;
use app\commonapi\Logger;
use app\models\dev\Account;
use app\models\dev\Card_bin;
use app\models\dev\Guarantee_card;
use app\models\dev\Guarantee_card_order;
use app\models\dev\Guarantee_reback;
use app\models\dev\Sms;
use app\models\dev\User;
use app\models\dev\User_amount_list;
use app\models\dev\User_bank;
use app\models\dev\User_remit_list;
use Yii;

class GuaranteeController extends SubController {

    public $layout = 'loan';
    public $enableCsrfValidation = false;
    private $quickYeepay;

    public function init() {
        //parent::init();
        $this->quickYeepay = new QuickYeepay();
    }

    public function actionIndex() {
        $this->getView()->title = "我的担保卡";
        $this->layout = 'newmain';
        $openid = $this->getVal('openid');
        $user_id = User::find()->select('user_id')->where(['openid' => $openid])->one();
        $guarantee = Guarantee_card_order::find()->where(['user_id' => $user_id->user_id, 'status' => 1])->orderBy(' pay_time desc')->all();
//         $loan = User_loan::find()->where(['business_type' => 2, 'user_id' => $user_id->user_id, 'status' => array('1', '2', '5', '6', '9', '10', '11', '12', '13')])->sum('amount') / 0.99;
        //print_r($loan);
        $account = Account::find()->where(['user_id' => $user_id->user_id])->one();
        $gua_num = $account->real_guarantee_amount;

        //当前用户绑定的银行卡信息
        $userBank = User_bank::find()->where(['user_id' => $user_id->user_id, 'status' => 1])->all();
        $userBankArr = array();
        if (!empty($userBank)) {
            foreach ($userBank as $val) {
                $userBankArr[] = $val->id;
            }
        }

        $start_time = '2016-02-05 12:00:00';
        $end_time = '2016-02-15 10:00:00';
        $now_time = date('Y-m-d H:i:s');
        $jsinfo = $this->getWxParam();
        if (count($guarantee) > 0) {
            return $this->render('index', [
                        'guarantee' => $guarantee,
                        'userBankArr' => $userBankArr,
                        'gua_num' => $gua_num,
                        'start_time' => $start_time,
                        'end_time' => $end_time,
                        'now_time' => $now_time,
                        'jsinfo' => $jsinfo,
            ]);
        } else {
            $this->layout = 'loan';
            return $this->render('guarantee', [
                        'start_time' => $start_time,
                        'end_time' => $end_time,
                        'now_time' => $now_time,
                        'jsinfo' => $jsinfo,
            ]);
        }
    }

    public function actionGuacard() {
        $this->getView()->title = "担保卡详情";
        $jsinfo = $this->getWxParam();
        return $this->render('guacard', [
                    'jsinfo' => $jsinfo,
        ]);
    }

    public function actionBuycard() {
        $this->getView()->title = '购买担保卡';
        $openid = $this->getVal('openid');
        $user = User::find()->where(['openid' => $openid])->one();
        if (empty($user->realname) || empty($user->identity)) {
            return $this->redirect('/dev/reg/two?user_id=' . $user->user_id . '&l=/dev/guarantee/buycard');
        }

        $guaranteeCard = Guarantee_card::find()->where(['status' => 1])->orderBy(' var desc')->all();
        $start_time = '2016-02-05 12:00:00';
        $end_time = '2016-02-15 10:00:00';
        $now_time = date('Y-m-d H:i:s');
        $limitStatus = 0;
        if ($now_time >= $start_time && $now_time <= $end_time) {
            $limitStatus = 4;
        }

        $jsinfo = $this->getWxParam();
        return $this->render('buycard', [
                    'guaranteeCard' => $guaranteeCard,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'now_time' => $now_time,
                    'limitStatus' => $limitStatus,
                    'jsinfo' => $jsinfo,
        ]);
    }

    public function actionBuy() {
        $startTime = Yii::$app->params['newyear_start_time'];
        $endTime = Yii::$app->params['newyear_end_time'];
        $time = time();
        if ($time >= $startTime && $time <= $endTime) {
            $this->redirect('/dev/loan');
        }

        $this->getView()->title = '购买担保卡';
        $post_data = Yii::$app->request->post();
        $post_data['card_id'] = isset($post_data['card_id']) ? $post_data['card_id'] : $_GET['card_id'];
        $post_data['num'] = isset($post_data['num']) ? $post_data['num'] : $_GET['num'];
        $openid = $this->getVal('openid');
        $user_id = User::find()->select('user_id')->where(['openid' => $openid])->one();
        $bank = User_bank::find()->where(['status' => 1, 'user_id' => $user_id->user_id])->count();
        $jsinfo = $this->getWxParam();
        if ($bank > 0) {
            $guarantee = Guarantee_card::findOne($post_data['card_id']);
            $card = User_bank::find()->where(['user_id' => $user_id->user_id, 'status' => 1])->orderBy(' type desc,last_modify_time desc')->all();
            return $this->render('buy', [
                        'card' => $card,
                        'post_data' => $post_data,
                        'guarantee' => $guarantee,
                        'jsinfo' => $jsinfo,
            ]);
        } else {
            return $this->redirect("/dev/bank/addcard?card_id=" . $post_data['card_id'] . "&num=" . $post_data['num'] . "&url=" . $_SERVER['REQUEST_URI']);
            //return $this->redirect($url, $statusCode)
            /*  return $this->render('addcard', [
              'post_data' => $post_data,
              'jsinfo' => $jsinfo,
              ]); */
        }
    }

    public function actionSavecard() {
        $this->getView()->title = '购买担保卡';
        $post_data = Yii::$app->request->post();
        //print_r($post_data);exit;
        $openid = $this->getVal('openid');
        $user = User::find()->select(array('user_id', 'realname', 'identity'))->where(['openid' => $openid])->one();
        $amount = $guarantee = Guarantee_card::findOne($post_data['guarantee_id']);
        if (isset($_GET['type']) && $_GET['type'] == 'very') {
            $card_num = str_replace(' ', '', $post_data['cards']);
            $counts = User_bank::find()->where(['card' => $card_num, 'status' => 1])->all();
            if (count($counts) > 0) {
                return json_encode(array('code' => 1, 'message' => '该卡已经被绑定'));
                exit;
            }
            $length = strlen($card_num);
            $sql = "SELECT * FROM " . Card_bin::tableName() . " WHERE card_length = " . $length . " AND prefix_value=left(" . $card_num . ",prefix_length) order by prefix_length desc";
            $cardbin = Yii::$app->db->createCommand($sql)->queryOne();
            if ($cardbin) {
                return json_encode(array('code' => 0, 'card_type' => $cardbin['card_type']));
                exit;
            } else {
                return json_encode(array('code' => 1, 'message' => '错误的卡号!'));
                exit;
            }
        } else {
            $jsinfo = $this->getWxParam();
            return $this->render('savecard', [
                        'user' => $user,
                        'post_data' => $post_data,
                        'amount' => $amount,
                        'jsinfo' => $jsinfo,
            ]);
        }
    }

    public function actionAddpayyibao() {
        $post_data = Yii::$app->request->post();
        $user = User::findOne($post_data['userid']);
        $user_id = $user['user_id'];
        $orderid = date('YmdHis') . rand(1000, 9999);
        $times = date('Y-m-d H:i:s');
        $guarantee_card = Guarantee_card::findOne($post_data['guarantee_id']);
        $money = $guarantee_card->var * $post_data['guatantee_num'] * 100;
        $guarantee = new Guarantee_card_order();
        $guarantee->order_id = $orderid;
        $guarantee->user_id = $user_id;
        $guarantee->card_id = $post_data['guarantee_id'];
        $guarantee->num = $post_data['guatantee_num'];
        $guarantee->total_amount = $guarantee_card->var * $post_data['guatantee_num'];
        $guarantee->remain_amount = $guarantee_card->var * $post_data['guatantee_num'];
        $guarantee->status = 3;
        $guarantee->pay_time = $times;
        $guarantee->create_time = $times;
        $guarantee->platform = 2;
        $cardbin = (new Card_bin())->getCardBinByCard($post_data['card']);
        if ($guarantee->save() && !empty($cardbin)) {
            $user = User::findOne($user_id);
            $card_type = ($cardbin['card_type'] == 0) ? 1 : 2;
            $postData = array(
                'orderid' => $orderid, // 请求唯一号
                'identityid' => (string) $user_id, // 用户标识
                'bankname' => $cardbin['bank_name'], //银行名称
                'bankcode' => $cardbin['bank_abbr'], //银行编码
                'card_type' => $card_type, // 卡类型
                'cardno' => isset($post_data['card']) ? $post_data['card'] : '', // 银行卡号
                'idcard' => $user->identity, // 身份证号
                'username' => $user->realname, // 姓名
                'phone' => $user->mobile, // 预留手机号
                'productcatalog' => '7', // 商品类别码
                'productname' => '购买电子产品', // 商品名称
                'productdesc' => '购买产品', // 商品描述
                'amount' => $money, // 交易金额
                'orderexpdate' => 60, // 交易金额
                'business_code' => 'YYYWX',
                'userip' => $_SERVER["REMOTE_ADDR"], // 交易金额
                'callbackurl' => Yii::$app->params['yibao_addguarantee'], // 交易金额
            );
            $openApi = new ApiClientCrypt;
            $res = $openApi->sent('payroute/pay', $postData, 2);
            $result = $openApi->parseResponse($res);
            Logger::errorLog(print_r($result, true), 'openguabankpay');
            $card = User_bank::find()->where(['card' => $post_data['card'], 'user_id' => $user_id])->one();
            $user = User::findOne($user_id);
            if (!empty($card)) {
                $card->status = 1;
                $card->last_modify_time = $times;
                $card->save();
                //$this->upAccount($user, 500, 13);
                $sql = "update " . Guarantee_card_order::tableName() . " set bank_id=" . $card->id . " where id=" . $guarantee->id;
                $ret = Yii::$app->db->createCommand($sql)->execute();
            } else {
                $length = strlen($post_data['card']);
                $sql = "SELECT * FROM " . Card_bin::tableName() . " WHERE card_length = " . $length . " AND prefix_value=left(" . $post_data['card'] . ",prefix_length) order by prefix_length desc";
                $cardbin = Yii::$app->db->createCommand($sql)->queryOne();
                $users_bank = new User_bank();
                $users_bank->user_id = $user->user_id;
                $users_bank->type = $cardbin['card_type'];
                $users_bank->bank_abbr = $cardbin['bank_abbr'];
                $users_bank->bank_name = $cardbin['bank_name'];
                $users_bank->card = $post_data['card'];
                $users_bank->bank_mobile = $user->mobile;
                $users_bank->status = 1;
                $users_bank->last_modify_time = $times;
                $users_bank->create_time = $times;
                $users_bank->save();
                //$this->upAccount($user, 500, 13);

                $sql = "update " . Guarantee_card_order::tableName() . " set bank_id=" . $users_bank->id . " where id=" . $guarantee->id;
                $ret = Yii::$app->db->createCommand($sql)->execute();
            }
            if ($result['res_code'] == 0) {
                return $this->redirect($result['res_data']['url']);
            } else {
                return $this->redirect('/dev/guarantee/error');
            }
        } else {
            return $this->redirect('/dev/guarantee/error');
        }
    }

    public function actionAddpay() {
        $post_data = Yii::$app->request->post();
        $user = User::findOne($post_data['userid']);
        $user_id = $user['user_id'];
        $biancard_id = date('YmdHis') . rand(1000, 9999);
        //@TODO:暂时连连支付属于互联网还款  entity：实名类,互联网还款 virtual:小额虚拟,话费充值
        $merchant_type = 'entity';
        //@TODO:连连目前只支持wap  app_request=1
        $app_request = 1;
        $dt_order = date('YmdHis');

        $name_goods = '一亿元购买担保卡';
        $guarantee = Guarantee_card::findOne($post_data['guarantee_id']);
        //@TODO:正式上线改为实际获取的金额，没有默认
        $money_order = $guarantee->var * $post_data['guatantee_num'];
        //@TODO:目前只支持借记卡 pay_type=2     3：快捷支付（信用卡）
        $pay_type = $post_data['pay_type'];

        $bind_mob = isset($post_data['mobile']) ? $post_data['mobile'] : '';
        $id_no = $user['identity'];
        $acct_name = $user['realname'];
        //@TODO:正式上线改为实际获取的身份证，没有默认
        $card_no = isset($post_data['card']) ? $post_data['card'] : '';

        $notify_url = Yii::$app->params['addgua_notify_url'];
        $url_return = Yii::$app->params['app_url'] . '/dev/guarantee/success?money=' . $money_order;

        $risk['frms_ware_category'] = $merchant_type == 'entity' ? '1010 ' : '2010';
        $risk['user_info_mercht_userno'] = $user_id;
        $risk['user_info_dt_register'] = date('YmdHis', strtotime($user['create_time']));
        $risk_item = json_encode($risk);
        $times = date('Y-m-d H:i:s', strtotime($dt_order));
        $createtime = $times;
        $guarantee = new Guarantee_card_order();
        $guarantee->order_id = $biancard_id;
        $guarantee->user_id = $user_id;
        $guarantee->card_id = $post_data['guarantee_id'];
        $guarantee->num = $post_data['guatantee_num'];
        $guarantee->total_amount = $money_order;
        $guarantee->remain_amount = $money_order;
        $guarantee->status = 3;
        $guarantee->pay_time = $times;
//         if ($pay_type == 3) {
//             $validate = $post_data['month'] . $post_data['year'];
//             $cvv2 = $post_data['cvv2'];
//         }
        $guarantee->create_time = $times;
        if ($guarantee->save()) {
            if ($pay_type == 2) {
                $result = Http::payLianLian($user_id, $merchant_type, $app_request, $biancard_id, $dt_order, $name_goods, $money_order, $notify_url, $url_return, $risk_item, $pay_type, $id_no, $acct_name, $card_no);
            } else {
                $result = Http::payLianLian($user_id, $merchant_type, $app_request, $biancard_id, $dt_order, $name_goods, $money_order, $notify_url, $url_return, $risk_item, $pay_type, $id_no, $acct_name, $card_no);
            }
            if ($result) {
//                 $arr = $this->classToArray($result);
//                 $arr['order_id'] = $biancard_id;
//                 return json_encode($arr);
                if ($result->rsp_code == '0000') {
//             			$account = Account::find()->where(['user_id' => $guarantee->user_id])->one();
//             			$account->recharge_amount = $account->recharge_amount + $result['money_order'];
//             			$account->guarantee_amount = $account->guarantee_amount + ($result['money_order'] * 0.99);
//             			$account->save();
                    $card = User_bank::find()->where(['card' => $post_data['card'], 'user_id' => $post_data['userid']])->one();
                    $user = User::findOne($guarantee->user_id);
                    if (!empty($card)) {
                        $card->status = 1;
                        $card->last_modify_time = $times;
                        $card->save();
                        $this->upAccount($user, 500, 13);
                        $sql = "update " . Guarantee_card_order::tableName() . " set bank_id=" . $card->id . " where id=" . $guarantee->id;
                        $ret = Yii::$app->db->createCommand($sql)->execute();

                        $redirect_url = trim($result->lianlian_payment);
                        return $this->redirect($redirect_url);
                    } else {
                        $length = strlen($post_data['card']);
                        $sql = "SELECT * FROM " . Card_bin::tableName() . " WHERE card_length = " . $length . " AND prefix_value=left(" . $post_data['card'] . ",prefix_length) order by prefix_length desc";
                        $cardbin = Yii::$app->db->createCommand($sql)->queryOne();
                        $users_bank = new User_bank();
                        $users_bank->user_id = $user->user_id;
                        $users_bank->type = $cardbin['card_type'];
                        $users_bank->bank_abbr = $cardbin['bank_abbr'];
                        $users_bank->bank_name = $cardbin['bank_name'];
                        $users_bank->card = $post_data['card'];
                        $users_bank->bank_mobile = $user->mobile;
                        $users_bank->status = 1;
                        $users_bank->last_modify_time = $times;
                        $users_bank->create_time = $times;
                        $users_bank->save();
                        $this->upAccount($user, 500, 13);

                        $sql = "update " . Guarantee_card_order::tableName() . " set bank_id=" . $users_bank->id . " where id=" . $guarantee->id;
                        $ret = Yii::$app->db->createCommand($sql)->execute();

                        $redirect_url = trim($result->lianlian_payment);
                        return $this->redirect($redirect_url);
                    }
                }
            } else {
//                 return json_encode(array(
//                     'rsp_code' => '99990',
//                     'rsp_msg' => '操作失败2',
//                 ));
                return $this->redirect('/dev/guarantee/error');
            }
        } else {
//             return json_encode(array(
//                 'rsp_code' => '99990',
//                 'rsp_msg' => '操作失败1',
//             ));
            return $this->redirect('/dev/guarantee/error');
        }
    }

    public function actionAddsub() {
        $post_data = Yii::$app->request->post();
        //@TODO:暂时连连支付属于互联网还款  entity：实名类,互联网还款
        $merchant_type = 'entity';
        //@TODO:连连目前只支持wap  app_request=1
        $app_request = 1;
        $bind_mob = isset($post_data['mobile']) ? $post_data['mobile'] : '';
        $pay_key = isset($post_data['pay_key']) ? $post_data['pay_key'] : '';
        $verifyCode = isset($post_data['verifyCode']) ? $post_data['verifyCode'] : '';
        $isrecord = isset($post_data['isrecord']) ? $post_data['isrecord'] : 'yes';
        $guarantee = Guarantee_card_order::find()->where(['order_id' => $post_data['order_id']])->one();
        $guarantee->pay_key = $post_data['pay_key'];
        if ($guarantee->save()) {
            $result = Http::subPayLian($merchant_type, $app_request, $pay_key, $bind_mob, $verifyCode, $isrecord);
            if ($result) {
                $arr = $this->classToArray($result);
                if ($arr['rsp_code'] == '0000') {
                    if (empty($guarantee->paybill)) {
                        $transaction = Yii::$app->db->beginTransaction();
                        $times = date('Y-m-d H:i:s');
                        $guarantee->status = 1;
                        $guarantee->pay_time = $times;
                        $guarantee->paybill = $arr['oid_paybill'];
                        $guarantee->actual_money = $arr['money_order'];
                        $account = Account::find()->where(['user_id' => $guarantee->user_id])->one();
                        $account->recharge_amount = $account->recharge_amount + $arr['money_order'];
                        $account->guarantee_amount = $account->guarantee_amount + ($arr['money_order'] * 0.99);
                        $account->real_guarantee_amount = $account->real_guarantee_amount + $arr['money_order'];
                        $account->save();
                        $card = User_bank::find()->where(['card' => $post_data['card'], 'user_id' => $post_data['userid']])->one();
                        $user = User::findOne($guarantee->user_id);
                        if (!empty($card)) {
                            $card->status = 1;
                            $card->last_modify_time = $times;
                            $card->save();
                            $this->upAccount($user, 500, 13);
                            $guarantee->bank_id = $card->id;
                            if ($guarantee->save()) {
                                $transaction->commit();
                            } else {
                                $transaction->rollBack();
                            }
                        } else {
                            $length = strlen($post_data['card']);
                            $sql = "SELECT * FROM " . Card_bin::tableName() . " WHERE card_length = " . $length . " AND prefix_value=left(" . $post_data['card'] . ",prefix_length) order by prefix_length desc";
                            $cardbin = Yii::$app->db->createCommand($sql)->queryOne();
                            $users_bank = new User_bank();
                            $users_bank->user_id = $user->user_id;
                            $users_bank->type = $cardbin['card_type'];
                            $users_bank->bank_abbr = $cardbin['bank_abbr'];
                            $users_bank->bank_name = $cardbin['bank_name'];
                            $users_bank->card = $post_data['card'];
                            $users_bank->bank_mobile = $post_data['mobile'];
                            if ($cardbin['card_type'] == 1) {
                                $des3key = Yii::$app->params['des3key'];
                                $users_bank->validate = $post_data['month'] . $post_data['year'];
                                $users_bank->cvv2 = Crypt3Des::encrypt($post_data['cvv2'], $des3key);
                                ;
                            }
                            $users_bank->status = 1;
                            $users_bank->last_modify_time = $times;
                            $users_bank->create_time = $times;
                            $users_bank->save();
                            $this->upAccount($user, 500, 13);
                            $guarantee->bank_id = $users_bank->id;
                            if ($guarantee->save()) {
                                $transaction->commit();
                            } else {
                                $transaction->rollBack();
                            }
                        }
                    }
                }
                return json_encode($arr);
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
    }

    //提升额度及日志记录
    public function upAccount($userinfo, $num, $type = 13, $column = array()) {
        $account = Account::find()->where(['user_id' => $userinfo->user_id])->one();
        $account['remain_amount'] -= $num;
        $account['amount'] += $num;
        $account['current_amount'] += $num;
        if (!empty($column)) {
            foreach ($column as $val) {
                $account[$val] += $num;
            }
        }
        $account['version'] += 1;
        $ret_account = $account->save();
        if ($ret_account) {
            $amount_date = array(
                'type' => $type,
                'user_id' => $userinfo->user_id,
                'amount' => $num
            );
            if ($num < 0) {
                $amount_date['operation'] = 0;
            }
            $user_amount = new User_amount_list();
            $user_amount->CreateAmount($amount_date);
        }
    }

    public function actionPayyibao() {
        $post_data = Yii::$app->request->post();
        $bank = User_bank::findOne($post_data['card_id']);
        $user = User::findOne($bank['user_id']);
        $user_id = $user['user_id'];
        $times = date('Y-m-d H:i:s');
        $orderid = date('YmdHis') . rand(1000, 9999);
        $guarantees = Guarantee_card::findOne($post_data['guarantee']);
        $money = $guarantees->var * $post_data['guarantee_num'] * 100;
        $guarantee = new Guarantee_card_order();
        $guarantee->order_id = $orderid;
        $guarantee->user_id = $user_id;
        $guarantee->card_id = $post_data['guarantee'];
        $guarantee->bank_id = $post_data['card_id'];
        $guarantee->num = $post_data['guarantee_num'];
        $guarantee->total_amount = $guarantees->var * $post_data['guarantee_num'];
        $guarantee->remain_amount = $guarantees->var * $post_data['guarantee_num'];
        $guarantee->status = 3;
        $guarantee->pay_time = $times;
        $guarantee->create_time = $times;
        $guarantee->platform = 2;
        if ($guarantee->save()) {
            $card_type = ($bank['type'] == 0) ? 1 : 2;
            $postData = array(
                'orderid' => $orderid, // 请求唯一号
                'identityid' => (string) $user_id, // 用户标识
                'bankname' => $bank['bank_name'], //银行名称
                'bankcode' => $bank['bank_abbr'], //银行编码
                'card_type' => $card_type, // 卡类型
                'cardno' => $bank['card'], // 银行卡号
                'idcard' => $user->identity, // 身份证号
                'username' => $user->realname, // 姓名
                'phone' => $user->mobile, // 预留手机号
                'productcatalog' => '7', // 商品类别码
                'productname' => '购买电子产品', // 商品名称
                'productdesc' => '购买产品', // 商品描述
                'amount' => $money, // 交易金额
                'orderexpdate' => 60, // 交易金额
                'business_code' => 'YYYWX',
                'userip' => $_SERVER["REMOTE_ADDR"], // 交易金额
                'callbackurl' => Yii::$app->params['yibao_guarantee'], // 交易金额
            );
            $openApi = new ApiClientCrypt;
            $res = $openApi->sent('payroute/pay', $postData, 2);
            $result = $openApi->parseResponse($res);
            Logger::errorLog(print_r($result, true), 'openguaranteepay');
            if ($result['res_code'] == 0) {
                return $this->redirect($result['res_data']['url']);
            } else {
                return $this->redirect('/dev/guarantee/error');
            }
        } else {
            return $this->redirect('/dev/guarantee/error');
        }
    }

    public function actionPaylian() {
        $post_data = Yii::$app->request->post();
//        print_r($post_data);
        $bank = User_bank::findOne($post_data['card_id']);
//        print_r($bank);
        $user = User::findOne($bank['user_id']);
        $user_id = $user['user_id'];
        $biancard_id = date('YmdHis') . rand(1000, 9999);
        //@TODO:暂时连连支付属于互联网还款  entity：实名类,互联网还款 virtual:小额虚拟,话费充值
        $merchant_type = 'entity';
        //@TODO:连连目前只支持wap  app_request=1
        $app_request = 1;
        $dt_order = date('YmdHis');

        $name_goods = '一亿元购买担保卡';
        //@TODO:正式上线改为实际获取的金额，没有默认

        $guarantees = Guarantee_card::findOne($post_data['guarantee']);
        //@TODO:正式上线改为实际获取的金额，没有默认
        $money_order = $guarantees->var * $post_data['guarantee_num'];
        //@TODO:目前只支持借记卡 pay_type=2     3：快捷支付（信用卡）
        $pay_type = $bank->type + 2;

        $bind_mob = isset($post_data['mobile']) ? $post_data['mobile'] : $bank->bank_mobile;
        $id_no = $user['identity'];
        $acct_name = $user['realname'];
        //@TODO:正式上线改为实际获取的身份证，没有默认
        $card_no = $bank->card;

        $notify_url = Yii::$app->params['guarant_notify_url'];
        $url_return = Yii::$app->params['app_url'] . '/dev/guarantee/success?money=' . $money_order;

        $risk['frms_ware_category'] = $merchant_type == 'entity' ? '1010 ' : '2010';
        $risk['user_info_mercht_userno'] = $user_id;
        $risk['user_info_dt_register'] = date('YmdHis', strtotime($user['create_time']));
        $risk_item = json_encode($risk);
        $times = date('Y-m-d H:i:s', strtotime($dt_order));
        $createtime = $times;
        $guarantee = new Guarantee_card_order();
        $guarantee->order_id = $biancard_id;
        $guarantee->user_id = $user_id;
        $guarantee->card_id = $post_data['guarantee'];
        $guarantee->bank_id = $post_data['card_id'];
        $guarantee->num = $post_data['guarantee_num'];
        $guarantee->total_amount = $guarantees->var * $post_data['guarantee_num'];
        $guarantee->remain_amount = $guarantees->var * $post_data['guarantee_num'];
        $guarantee->status = 3;
        $guarantee->pay_time = $times;
//         if ($pay_type == 3) {
//             $des3key = Yii::$app->params['des3key'];
//             $validate = $bank->validate;
//             $cvv2 = \Crypt3Des::decrypt($bank->cvv2, $des3key);
//         }
        $guarantee->create_time = $times;
        if ($guarantee->save()) {
            if ($pay_type == 2) {
                $result = Http::payLianLian($user_id, $merchant_type, $app_request, $biancard_id, $dt_order, $name_goods, $money_order, $notify_url, $url_return, $risk_item, $pay_type, $id_no, $acct_name, $card_no);
            } else {
                $result = Http::payLianLian($user_id, $merchant_type, $app_request, $biancard_id, $dt_order, $name_goods, $money_order, $notify_url, $url_return, $risk_item, $pay_type, $id_no, $acct_name, $card_no);
            }
            if ($result) {
//                 $arr = $this->classToArray($result);
//                 $arr['order_id'] = $biancard_id;
//                 return json_encode($arr);
                $redirect_url = trim($result->lianlian_payment);
                return $this->redirect($redirect_url);
            } else {
//                 return json_encode(array(
//                     'rsp_code' => '99990',
//                     'rsp_msg' => '操作失败2',
//                 ));
                return $this->redirect('/dev/guarantee/error');
            }
        } else {
//             return json_encode(array(
//                 'rsp_code' => '99990',
//                 'rsp_msg' => '操作失败1',
//             ));
            return $this->redirect('/dev/guarantee/error');
        }
    }

    public function actionPay() {
        $post_data = Yii::$app->request->post();
//        print_r($post_data);
        //@TODO:暂时连连支付属于互联网还款  entity：实名类,互联网还款
        $merchant_type = 'entity';
        //@TODO:连连目前只支持wap  app_request=1
        $app_request = 1;
        $bind_mob = isset($post_data['mobile']) ? $post_data['mobile'] : '';
        $pay_key = isset($post_data['pay_key']) ? $post_data['pay_key'] : '';
        $verifyCode = isset($post_data['verifyCode']) ? $post_data['verifyCode'] : '';
        $isrecord = isset($post_data['isrecord']) ? $post_data['isrecord'] : 'no';
        $guarantee = Guarantee_card_order::find()->where(['order_id' => $post_data['order_id']])->one();
        $guarantee->pay_key = $post_data['pay_key'];
        if ($guarantee->save()) {
            $result = Http::subPayLian($merchant_type, $app_request, $pay_key, $bind_mob, $verifyCode, $isrecord);
            if ($result) {
                $arr = $this->classToArray($result);
                if ($arr['rsp_code'] == '0000') {
                    if (empty($guarantee->paybill)) {
                        $transaction = Yii::$app->db->beginTransaction();
                        $times = date('Y-m-d H:i:s');
                        $guarantee->status = 1;
                        $guarantee->pay_time = $times;
                        $guarantee->paybill = $arr['oid_paybill'];
                        $guarantee->actual_money = $arr['money_order'];
                        if ($guarantee->save()) {
                            $account = Account::find()->where(['user_id' => $guarantee->user_id])->one();
                            $account->recharge_amount = $account->recharge_amount + $arr['money_order'];
                            $account->guarantee_amount = $account->guarantee_amount + ($arr['money_order'] * 0.99);
                            $account->real_guarantee_amount = $account->recharge_amount + $arr['money_order'];
                            if ($account->save()) {
                                $transaction->commit();
                            } else {
                                $transaction->rollBack();
                            }
                        } else {
                            $transaction->rollBack();
                        }
                    }
                }
                return json_encode($arr);
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
    }

    //支付成功页面
    public function actionSuccess() {
        $amount = Yii::$app->request->get('repay_id');
        $old = round($amount / 100, 0);
        $jsinfo = $this->getWxParam();
        return $this->render('success', [
                    'old' => $old,
                    'jsinfo' => $jsinfo,
        ]);
    }

    //担保卡退卡================================================
    public function actionBackcard() {
        $startTime = Yii::$app->params['newyear_start_time'];
        $endTime = Yii::$app->params['newyear_end_time'];
        $time = time();
        if ($time >= $startTime && $time <= $endTime) {
            $this->redirect('/dev/loan');
        }

        $this->layout = 'newmain';
        $this->getView()->title = "我要退卡";
        $card_id = isset($_GET['card_id']) ? intval($_GET['card_id']) : 0;

        $nowtime = time();
        $limitBeginTime = strtotime(date('Y-m-d')); //0点
        $limitEndTime = $limitBeginTime + 6 * 60 * 60; //6点
        if ($nowtime > $limitBeginTime && $nowtime < $limitEndTime) {
            $limitStatus = 1;
        } else {
            $limitStatus = 0;
        }

        //春节期间，禁止提现
        $start_time = '2016-02-05 12:00:00';
        $end_time = '2016-02-15 10:00:00';
        $now_time = date('Y-m-d H:i:s');
        if ($now_time >= $start_time && $now_time <= $end_time) {
            $limitStatus = 4;
        }
        //获取担保卡购买记录
        $cardOrder = Guarantee_card_order::find()->where(['id' => $card_id])->one();
        //获取购买时使用的银行卡
        $cardBank = User_bank::find()->where(['id' => $cardOrder->bank_id, 'status' => 1])->one();
        $jsinfo = $this->getWxParam();
        return $this->render('backcard', [
                    'cardOrder' => $cardOrder,
                    'cardBank' => $cardBank,
                    'limitStatus' => $limitStatus,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'now_time' => $now_time,
                    'jsinfo' => $jsinfo,
        ]);
    }

    //退卡发送短信验证码
    public function actionBackcardsms() {
        $mobile = $_POST['mobile'];
        $remain_mount = $_POST['remain_mount'];
        //判断手机号和银行卡是否对应
        //一天只能发送6条短信
        $begintime = date('Y-m-d' . ' 00:00:00');
        $endtime = date('Y-m-d' . ' 23:59:59');
        $sms_count = Sms::find()->where("recive_mobile='$mobile' and sms_type=18 and create_time >= '$begintime' and create_time <= '$endtime'")->count();
        if ($sms_count >= 6) {
            $resultArr = array('ret' => '2', 'url' => '');
            echo json_encode($resultArr);
            exit; //已有用户绑定
        }

        $code = rand(1000, 9999);
        $sms = new Sms();
        $content = '验证码:' . $code . '(为了资金安全,请勿将验证码告知他人),担保卡退卡，退卡金额' . $remain_mount . '元；如有疑问请联系先花一亿元微信客服。';
        $sms->content = $content;
        $sms->recive_mobile = $mobile;

        $sendRet = Http::sendByMobile($mobile, $content);
        if ($sendRet) {
            $sms->create_time = date('Y-m-d H:i:s', time());
            $sms->sms_type = 18;
            $sms->code = $code;
            $sms->save();
        } else {
            //暂留
        }

        $resultArr = array('ret' => '0', 'url' => '');
        echo json_encode($resultArr);
        exit;
    }

    //退款确认
    public function actionBackcardconfirm() {
        $code = $_POST['code'];
        $coid = $_POST['coid'];

        $nowtime = date('G');
        if ($nowtime >= 0 && $nowtime < 7) {
            $resultArr = array('ret' => '5', 'msg' => '0点至6点暂停退卡业务');
            echo json_encode($resultArr);
            exit;
        }

        //担保卡购买记录
        $cardOrder = Guarantee_card_order::find()->where(['id' => $coid])->one();
        //购买担保卡的银行卡
        $bankInfo = User_bank::find()->where(['id' => $cardOrder->bank_id])->one();
        $mobile = $bankInfo->bank_mobile;
        $user_id = $bankInfo->user_id;
        //判断验证码是否正确
        $begintime = date('Y-m-d' . ' 00:00:00');
        $endtime = date('Y-m-d' . ' 23:59:59');
        //限制每天可以退3次卡
        $count = Guarantee_reback::find()->where("user_id=$user_id and create_time >= '$begintime' and create_time <= '$endtime'")->count();
        if ($count >= 3) {
            $resultArr = array('ret' => '2', 'msg' => '您今天已经提过了，请明天再来~~');
            echo json_encode($resultArr);
            exit;
        }
        $sms = Sms::find()->where("recive_mobile='$mobile' and sms_type=18 and create_time >= '$begintime' and create_time <= '$endtime'")->orderBy('create_time desc')->one();
        if (!empty($sms)) {
            if ($sms->code != $code) {
                $resultArr = array('ret' => '1', 'msg' => '验证码错误');
                echo json_encode($resultArr);
                exit;
            }
        } else {
            $resultArr = array('ret' => '1', 'msg' => '验证码不存在');
            echo json_encode($resultArr);
            exit;
        }
        //退卡操作
        //1.记录退卡记录
        $model = new Guarantee_reback();
        $model->version = 1;
        $model->bank_id = $bankInfo->id;
        $model->user_id = $bankInfo->user_id;
        $model->reback_type = "REMIT";
        $model->reback_amount = $cardOrder->remain_amount;
        $model->card_order_id = $cardOrder->id;
        $model->create_time = date('Y-m-d H:i:s', time());
        $model->status = "INIT";
        if ($model->save()) {
            $guarantee_reback_id = $model->attributes['id'];
            //2.调用出款接口
            $userinfo = User::find()->where(['user_id' => $bankInfo->user_id])->one();
            $user_mobile = $userinfo->mobile;
            $user_name = $userinfo->realname;
            //持卡人姓名
            $guest_account_name = $userinfo->realname;
            //银行卡号
            $guest_account = $bankInfo->card;
            $guest_account_bank = $bankInfo->bank_name;
            $guest_account_province = '北京市';
            $guest_account_city = '北京市';
            $guest_account_bank_branch = $bankInfo->bank_name;
            $account_type = 0;
            $settle_amount = $cardOrder->remain_amount;
            $order_id = date('Ymdhis') . rand(100000, 999999);
            $params = [
                'req_id' => $order_id,
                'remit_type' => 2,
                'identityid' => $userinfo->identity,
                'user_mobile' => $user_mobile,
                'guest_account_name' => $user_name,
                'guest_account_bank' => $guest_account_bank,
                'guest_account_province' => '北京',
                'guest_account_city' => '北京',
                'guest_account_bank_branch' => $guest_account_bank,
                'guest_account' => $guest_account,
                'settle_amount' => $settle_amount,
                'callbackurl' => 'http://weixin.xianhuahua.com/dev/notify/remitbackurl',
            ];
            $apihttp = new Remit();
            $res = $apihttp->outBlance($params);
            if ($res['res_code'] == '0000') {
                //更新退卡记录表状态
                $settle_request_id = $res['res_msg']['client_id'];
                $real_amount = $res['res_msg']['settle_amount'];
                $settle_fee = 0;
                $settle_amount = $res['res_msg']['settle_amount'];
                $rsp_code = $res['res_code'];
                $reback_status = 'INIT';
                $status = "SUCCESS";
                $last_modify_time = date('Y-m-d H:i:s', time());
                $sql = "update " . Guarantee_reback::tableName() . " set settle_request_id='$settle_request_id',real_amount='$real_amount',settle_fee='$settle_fee',settle_amount='$settle_amount',rsp_code='$rsp_code',reback_status='$reback_status',status='$status',last_modify_time='$last_modify_time' where id=" . $guarantee_reback_id;

                //给数据库的user_remit_list 插入一条数据
                $sql_remit = "insert into " . User_remit_list::tableName() . "(order_id,loan_id,admin_id,settle_request_id,real_amount,settle_fee,settle_amount,rsp_code,remit_status,create_time,bank_id,user_id,type) ";
                $sql_remit .= "value('" . $order_id . "','" . $guarantee_reback_id . "','-1','$settle_request_id','$real_amount ','$settle_fee','$settle_amount','$rsp_code','$reback_status','$last_modify_time','$bankInfo->id','$user_id',2)";

                $transaction = Yii::$app->db->beginTransaction();
                $retinsert = Yii::$app->db->createCommand($sql)->execute();
                $retremit = Yii::$app->db->createCommand($sql_remit)->execute();

                if ($retinsert >= 0) {
                    //更新担保卡购买记录的remain_amount值
                    $guasql = "update " . Guarantee_card_order::tableName() . " set remain_amount=0 where id=" . $coid;
                    $retgua = Yii::$app->db->createCommand($guasql)->execute();
                    //更新账户信息
                    $accsql = "update " . Account::tableName() . " set recharge_amount=recharge_amount-$settle_amount,guarantee_amount=guarantee_amount-($settle_amount*0.99),real_guarantee_amount=real_guarantee_amount-$settle_amount where user_id=" . $userinfo->user_id;
                    $retacc = Yii::$app->db->createCommand($accsql)->execute();

                    if ($retgua && $retacc) {
                        //提交更新
                        $transaction->commit();
                        //成功发送模板消息通知
                        $openid = $userinfo->openid;
                        if (!empty($openid)) {
                            $template_id = Yii::$app->params['backcard_template_id'];
                            $url = Yii::$app->request->hostInfo . "/dev/guarantee";
                            $nowdate = date('Y-m-d H:i:s', time());
                            $cardinfo = Guarantee_card::find()->where(['id' => $cardOrder->card_id])->one();
                            $banktype = $bankInfo->type == 0 ? '借记卡' : '信用卡';
                            $data = '{
                                              "touser":"' . $openid . '",
                                              "template_id":"' . $template_id . '",
                                              "url":"' . $url . '",
                                              "topcolor":"#FF0000",
                                              "data":{
                                                      "first": {
                                                                "value":"尊敬的' . $userinfo->realname . '，您的担保卡退款申请已成功提交，预期到帐时间24小时内，请注意查收。",
                                                                "color":"#173177"
                                                                },
                                                       "keyword1":{
                                                                "value":"' . $cardinfo->title . '",
                                                                 "color":"#173177"
                                                                },
                                                       "keyword2": {
                                                                 "value":"' . $nowdate . '",
                                                                 "color":"#173177"
                                                       			},
                                                       "keyword3": {
                                                                 "value":"' . sprintf('%.2f', $cardOrder->remain_amount) . '",
                                                                 "color":"#173177"
                                                       			},
                                                       "keyword4":{
                                                                  "value":"尾号' . substr($bankInfo->card, strlen($bankInfo->card) - 4, 4) . '的' . $bankInfo->bank_name . $banktype . '",
                                                                  "color":"#173177"
                                                                },
                                                        "remark":{
                                                                  "value":"担保借款光速到账，担保投资，借鸡生蛋，越赚越多。",
                                                                  "color":"#173177"
                                                                }
                                                  		}
                                           }';
                            $resulttemplate = $this->sendTemplatetouser($data);
                            Logger::errorLog(print_r($resulttemplate, true), 'sendtemplatetouserbybackcard');
                        }
                    } else {
                        //回滚状态
                        $transaction->rollBack();
                        Logger::errorLog("SUCCESS:" . $sql . "|" . $guasql . "|" . $accsql, 'guarantee_reback');
                    }
                } else {
                    //记录一下日志,出款记录日志
                    Logger::errorLog("SUCCESS:" . $sql, 'guarantee_reback');
                }
                $ret = array('ret' => 0, 'msg' => '出款成功');
                echo json_encode($ret);
                exit;
            } else if ($res['res_code'] == '13003' || $res['res_code'] == '13002') {
                $ret = array('ret' => 3, 'msg' => $res['res_msg']);
                echo json_encode($ret);
                exit;
            } else {
                //打款失败，修改收益提现记录状态
                $settle_request_id = $res['res_msg']['client_id'];
                $real_amount = $res['res_msg']['settle_amount'];
                $settle_fee = 0;
                $settle_amount = $res['res_msg']['settle_amount'];
                $rsp_code = $res['res_code'];
                $reback_status = 'INIT';
                $status = "FAILED";
                $last_modify_time = date('Y-m-d H:i:s', time());
                $sql = "update" . Guarantee_reback::tableName() . " set settle_request_id='$settle_request_id',real_amount='$real_amount',settle_fee='$settle_fee',settle_amount='$settle_amount',rsp_code='$rsp_code',reback_status='$reback_status',status='$status',last_modify_time='$last_modify_time' where id=" . $guarantee_reback_id;

                //给数据库的user_remit_list 插入一条数据
                $sql_remit = "insert into " . User_remit_list::tableName() . "(order_id,loan_id,admin_id,settle_request_id,real_amount,settle_fee,settle_amount,rsp_code,remit_status,create_time,bank_id,user_id,type) ";
                $sql_remit .= "value('" . $order_id . "','" . $guarantee_reback_id . "','-1','$settle_request_id','$real_amount ','$settle_fee','$settle_amount','$rsp_code','$reback_status','$last_modify_time','$bankInfo->id','$user_id',2)";

                $retinsert = Yii::$app->db->createCommand($sql)->execute();
                $retremit = Yii::$app->db->createCommand($sql_remit)->execute();

                if ($retinsert) {
                    //记录一下日志,出款记录日志
                    Logger::errorLog("FAILED:" . $sql, 'guarantee_reback');
                }
                $ret = array('ret' => 3, 'msg' => '出款失败');
                echo json_encode($ret);
                exit;
            }
        } else {
            $ret = array('ret' => 4, 'msg' => '记录失败');
            echo json_encode($ret);
            exit;
        }
    }

    //退卡结果页
    public function actionBackcardret() {
        $this->layout = 'newmain';
        $ret = $_GET['ret'];
        $coid = isset($_GET['coid']) ? $_GET['coid'] : '';
        $jsinfo = $this->getWxParam();
        if ($ret == 'success') {
            $this->getView()->title = "退卡成功";
            //担保卡购买记录
            $cardOrder = Guarantee_card_order::find()->where(['id' => $coid])->one();
            //购买担保卡的银行卡
            $bankInfo = User_bank::find()->where(['id' => $cardOrder->bank_id])->one();

            return $this->render('back_succ', [
                        'bankInfo' => $bankInfo,
                        'jsinfo' => $jsinfo,
            ]);
        } else {
            $this->getView()->title = "退卡失败";

            return $this->render('back_fail', [
                        'jsinfo' => $jsinfo,
            ]);
        }
    }

    //支付失败页面
    public function actionError() {
        $jsinfo = $this->getWxParam();
        return $this->render('error', [
                    'jsinfo' => $jsinfo,
        ]);
    }

    public function classToArray($cla) {
        $arr = array();
        foreach ($cla as $key => $val) {
            $arr[$key] = $val;
        }
        return $arr;
    }

}
