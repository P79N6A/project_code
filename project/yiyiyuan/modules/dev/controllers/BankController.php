<?php

namespace app\modules\dev\controllers;

use app\commands\SubController;
use app\common\ApiClientCrypt;
use app\common\yeepay\QuickYeepay;
use app\commonapi\Apihttp;
use app\commonapi\Bank;
use app\commonapi\Common;
use app\commonapi\Crypt3Des;
use app\commonapi\Http;
use app\commonapi\Keywords;
use app\commonapi\Logger;
use app\models\dev\Account;
use app\models\dev\ApiSms;
use app\models\news\Areas;
use app\models\dev\Card_bin;
use app\models\dev\Sms;
use app\models\dev\User;
use app\models\dev\User_amount_list;
use app\models\dev\User_bank;
use app\models\dev\User_bincard_list;
use app\models\dev\User_loan;
use app\models\news\CardLimit;
use Exception;
use Yii;

class BankController extends SubController {

    public $layout = 'loan';
    public $enableCsrfValidation = false;
    private $quickYeepay;

    public function init() {
        //parent::init();
        $this->quickYeepay = new QuickYeepay();
    }

    public function actionIndex() {
        $this->getView()->title = "银行卡";
        $openid = $this->getVal('openid');
        $user_id = User::find()->select('user_id')->where(['openid' => $openid])->one();
        /*         * *************记录访问日志beigin******************* */
        $ip = Common::get_client_ip();
        $result_log = Common::saveLog('bank', 'bank_list', $ip, 'weixin', $user_id->user_id);
        /*         * *************记录访问日志end******************* */
        $banks = User_bank::find()->where(['user_id' => $user_id->user_id, 'status' => 1])->orderBy(' default_bank desc,create_time desc')->all();

        $jsinfo = $this->getWxParam();
        return $this->render('index', [
                    'banks' => $banks,
                    'user_id' => $user_id->user_id,
                    'jsinfo' => $jsinfo,
        ]);
    }

    public function actionDelbank() {
        $this->getView()->title = "银行卡详情";
        $id = intval($_GET['id']);
        $userbank = User_bank::findOne($id);
        if ($userbank->type = 1 && !empty($userbank->cvv2)) {
            $des3key = Yii::$app->params['des3key'];
            $userbank->cvv2 = Crypt3Des::decrypt($userbank->cvv2, $des3key);
        }
        $jsinfo = $this->getWxParam();
        return $this->render('delbank', [
                    'userbank' => $userbank,
                    'jsinfo' => $jsinfo,
        ]);
    }

    //银行卡限额页面
    public function actionQuota() {
        $this->view->title = '支持银行列表';
        $jsinfo = $this->getWxParam();
        $credit_limit = (new CardLimit())->find()->where(['type'=>3,'card_type'=>0,'status'=>2])->orderBy('id asc')->asArray()->all();
        $debit_limit = (new CardLimit())->find()->where(['type'=>3,'card_type'=>1,'status'=>2])->orderBy('id asc')->asArray()->all();
        return $this->render('quota', [
                    'credit_limit' => $credit_limit,
                    'debit_limit' => $debit_limit,
                    'jsinfo' => $jsinfo,
        ]);
    }

    public function actionDelcard() {
        $id = intval($_GET['id']);
        $userbank = User_bank::findOne($id);
        $loan = User_loan::find()->where(['user_id' => $userbank->user_id, 'status' => array('1', '2', '5', '6', '9', '10', '11', '12', '13'), 'bank_id' => $id])->count();
        if ($loan > 0) {
            return json_encode(array('code' => '1', 'message' => '您存在正在进行中的借款,暂时不能解绑该卡'));
        }
        if ($userbank->type == 0) {
            $bankModel = new User_bank();
            $userbanks = $bankModel->getBankByUserId($userbank->user_id, 0);
            if (count($userbanks) <= 1) {
                return json_encode(array('code' => '1', 'message' => '您目前只有一张借记卡，暂时不能解绑该卡'));
            }
        }
        $times = date('Y-m-d H:i:s');
        $userbank->status = 0;
        $userbank->last_modify_time = $times;
        $userbank->default_bank = 0;
        if ($userbank->save()) {
            return json_encode(array('code' => '0', 'message' => '解绑成功！'));
        } else {
            return json_encode(array('code' => '2', 'message' => '解绑失败！'));
        }
    }

    //输入银行卡号
    public function actionAddcard() {
        $this->getView()->title = '添加银行卡';
        $this->layout = 'data';
        $openid = $this->getVal('openid');
        $user_id = Yii::$app->request->get('user_id');
        if (!empty($user_id)) {
            $user = User::findOne($user_id);
        } else {
            $user = User::find()->where(['openid' => $openid])->one();
        }
        $url = !empty($_GET['url']) ? $_GET['url'] : '';
        $card_id = !empty($_GET['card_id']) ? $_GET['card_id'] : '';
        $num = !empty($_GET['num']) ? $_GET['num'] : '';
        if (empty($user->realname) || empty($user->identity)) {
            return $this->redirect('/dev/reg/personals?user_id=' . $user->user_id . '&url=/dev/bank/addcard');
        }
        if (isset($_GET['f'])) {
            $f = $_GET['f'];
        } else {
            $f = '';
        }
        $list = Areas::getAllAreas();
        $jsinfo = $this->getWxParam();
        return $this->render('addcard', [
                    'f' => $f,
                    'jsinfo' => $jsinfo,
                    'user' => $user,
                    'url' => $url,
                    'card_id' => $card_id,
                    'num' => $num,
                    'list' => $list,
        ]);
    }

    public function actionActivity() {
        $post_data = \Yii::$app->request->post();
        $mobile = $post_data['mobile'];
        $api = new ApiSms();
        $api->sendBindCard($mobile, 7);
        $resultArr = array('ret' => '0', 'url' => '');
        echo json_encode($resultArr);
        exit;
    }

    public function actionAddeventcard() {
        $post_data = \Yii::$app->request->post();
        if ($post_data['pay_type'] == 1 && (!isset($post_data['cvv2']) || $post_data['cvv2'] == '')) {
            return json_encode(array('ret' => '1', 'msg' => '填写数据错误'));
        }
        $result = Sms::find()->where(['recive_mobile' => $post_data['mobile'], 'code' => $post_data['verifyCode']])->orderBy('create_time desc')->one();
        if (isset($result->id)) {
            $time = strtotime($result['create_time']);
            $nowtime = time();
            $min = ceil(($nowtime - $time) / (60 * 60 * 12));
            if ($min > 12) {//12小时内有效
                return json_encode(array('ret' => '1', 'msg' => '验证码已过期'));
                exit;
            }
            $length = strlen($post_data['card']);
            $sql = "SELECT * FROM " . Card_bin::tableName() . " WHERE card_length = " . $length . " AND prefix_value=left(" . $post_data['card'] . ",prefix_length) order by prefix_length desc";
            $cardbin = Yii::$app->db->createCommand($sql)->queryOne();
            $transaction = Yii::$app->db->beginTransaction();
            $user_bank = new User_bank();
            $user_bank->user_id = $post_data['userid'];
            $user_bank->type = $post_data['pay_type'];
            $user_bank->bank_abbr = $cardbin['bank_abbr'];
            $user_bank->bank_name = $cardbin['bank_name'];
            $user_bank->card = $post_data['card'];
            $user_bank->bank_mobile = $post_data['mobile'];
            $user_bank->default_bank = 0;
            if ($post_data['pay_type'] == 1) {
                $des3key = Yii::$app->params['des3key'];
                $user_bank->validate = $post_data['month'] . $post_data['year'];
                $user_bank->cvv2 = Crypt3Des::encrypt($post_data['cvv2'], $des3key);
            }
            $user_bank->status = 1;
            $user_bank->last_modify_time = date('Y-m-d H:i:s', $nowtime);
            $user_bank->create_time = date('Y-m-d H:i:s', $nowtime);
            try {
                $user_bank->save();
                $user = User::findOne($post_data['userid']);
                $this->upAccount($user, 500, 13);
                $transaction->commit();
                return json_encode(array('ret' => '0', 'msg' => '绑定成功'));
            } catch (Exception $ex) {
                $transaction->rollBack();
                return json_encode(array('ret' => '1', 'msg' => '绑定失败，请重新绑定'));
            }
        } else {
            return json_encode(array('ret' => '1', 'msg' => '验证码不正确，请重新填写'));
        }
        exit;
    }

    public function actionSavecard() {
        $post_data = \Yii::$app->request->post();
//        print_r($post_data);exit;
        $card_num = str_replace(' ', '', $post_data['card']);
        $num = !empty($post_data['num']) ? str_replace(' ', '', $post_data['num']) : '';
        $card_id = !empty($post_data['card_id']) ? str_replace(' ', '', $post_data['card_id']) : '';
        $url = !empty($post_data['url']) ? str_replace(' ', '', $post_data['url']) : '';
        $user = User::find()->select(array('user_id', 'realname', 'identity', 'mobile'))->where(['user_id' => $post_data['user_id']])->one();

        if (isset($_GET['type']) && $_GET['type'] == 'very') {
            $length = strlen($card_num);
            $sql = "SELECT * FROM " . Card_bin::tableName() . " WHERE card_length = " . $length . " AND prefix_value=left('" . $card_num . "',prefix_length) order by prefix_length desc";
            $cardbin = Yii::$app->db->createCommand($sql)->queryOne();
            if (empty($cardbin) || ($cardbin['card_type'] != 0 && $cardbin['card_type'] != 1)) {
                return json_encode(array('code' => 5, 'message' => '错误的卡号或暂不支持该卡!'));
                exit;
            } else {
                $bank_array = Keywords::getBankAbbr();
                if (!in_array($cardbin['bank_abbr'], $bank_array[$cardbin['card_type']])) {
                    return json_encode(array('code' => 2, 'message' => '暂不支持该卡!'));
                    exit;
                }
            }
            $counts = User_bank::find()->where(['card' => $card_num, 'status' => 1])->all();

            if (count($counts) > 0) {
                return json_encode(array('code' => 1, 'message' => '该卡已经被绑定'));
                exit;
            }
            $count = User_bank::find()->where(['user_id' => $user->user_id, 'card' => $card_num])->one();
            if (count($count) > 0) {
                if ($count->status == 1) {
                    return json_encode(array('code' => 1, 'message' => '该卡已经被绑定'));
                    exit;
                } else {
                    $count->status = 1;
                    $count->last_modify_time = date('Y-m-d H:i:s');
                    if ($count->save()) {
                        return json_encode(array('code' => 3, 'message' => '绑卡成功'));

                        exit;
                    } else {
                        return json_encode(array('code' => 2, 'message' => '绑卡失败，请重新尝试'));
                        exit;
                    }
                }
            } else {
                if ($cardbin) {
                    return json_encode(array('code' => 0, 'card_type' => $cardbin['card_type']));
                    exit;
                } else {
                    return json_encode(array('code' => 2, 'message' => '错误的卡号!'));
                    exit;
                }
            }
        } else {
            $jsinfo = $this->getWxParam();
            $this->getView()->title = '添加银行卡';
            return $this->render('savecard', [
                        'user' => $user,
                        'post_data' => $post_data,
                        'jsinfo' => $jsinfo,
                        'url' => $url,
                        'num' => $num,
                        'card_id' => $card_id,
            ]);
        }
    }

    public function actionBindcard() {
        $user_id = $_POST['userid'];
        $card = $_POST['card'];
        $mobile = $_POST['mobile'];
        $code = $_POST['code'];
        $isyeepay = $_POST['isyeepay'];
        $key = "bind_bank_" . $mobile;
        $key_requestid = 'requestid_bank_' . $mobile;
        $code_byredis = Yii::$app->redis->get($key);
        $requestid_byredis = Yii::$app->redis->get($key_requestid);
        $ownerCode = Yii::$app->redis->get('getcode_bank_' . $mobile);
        if ($ownerCode != $code) {
            $resultArr = array('ret' => '1', 'msg' => '');
            echo json_encode($resultArr);
            exit;
        }else{
            Yii::$app->redis->del('getcode_bank_' . $mobile);
        }
        $counts = User_bank::find()->where(['card' => $card, 'status' => 1])->all();
        if (count($counts) > 0) {
            return json_encode(array('ret' => 2, 'msg' => '该卡已经被绑定'));
            exit;
        }
        $user = User::findOne($user_id);
        $user_id = $user['user_id'];
        $bank_card = $card;

        //判断银行卡是否支持易宝绑定，如果支持优先调用易宝绑定，否则调用天行
        $sql = "SELECT * FROM " . Card_bin::tableName() . " WHERE card_length = " . strlen($bank_card) . " AND prefix_value=left(" . $bank_card . ",prefix_length) order by prefix_length desc";
        $cardbin = Yii::$app->db->createCommand($sql)->queryOne();
        if ($isyeepay == 'yes') {
            $verify = 2;
        } else {
            $bank_code = !empty($cardbin['bank_abbr']) ? $cardbin['bank_abbr'] : '';
            $result = Bank::supportbank($bank_code);
            if ($result && $cardbin['card_type'] == '0') {
                $postdata = array(
                    'requestid' => $requestid_byredis,
                    'validatecode' => $code_byredis
                );

                $openApi = new Apihttp;
                $result = $openApi->confirmbindbankcard($postdata);
                if ($result['res_code'] == '0000') {
                    $verify = 2;
                } else {
                    //调用银行卡验证接口
                    $postdata = array(
                        'username' => $user->realname,
                        'idcard' => $user->identity,
                        'cardno' => $card,
                        'phone' => $mobile
                    );

                    $openApi = new Apihttp;
                    $result = $openApi->bankInfoValid($postdata);
                    if ($result['res_code'] != '0000') {
                        switch ($result['res_msg']) {
                            case 'DIFFERENT':
                                $result['res_msg'] = '请优先确认您输入的手机号码与办理银行卡时预留手机号码一致<br>请确认您的银行卡号是否填写正确';
                                break;
                            case 'ACCOUNTNO_INVALID':
                                $result['res_msg'] = '请核实您的银行卡状态是否有效';
                                break;
                            case 'ACCOUNTNO_NOT_SUPPORT':
                                $result['res_msg'] = '暂不支持此银行，请更换您的银行卡';
                                break;
                            default:
                                $result['res_msg'] = $result['res_msg'];
                        }
                        $resultArr = array('ret' => '2', 'msg' => $result['res_msg']);
                        echo json_encode($resultArr);
                        exit;
                    }
                    $verify = 1;
                }
            } else {
                //调用银行卡验证接口
                $postdata = array(
                    'username' => $user->realname,
                    'idcard' => $user->identity,
                    'cardno' => $card,
                    'phone' => $mobile
                );

                $openApi = new Apihttp;
                $result = $openApi->bankInfoValid($postdata);
                if ($result['res_code'] != '0000') {
                    switch ($result['res_msg']) {
                        case 'DIFFERENT':
                            $result['res_msg'] = '请优先确认您输入的手机号码与办理银行卡时预留手机号码一致<br>请确认您的银行卡号是否填写正确';
                            break;
                        case 'ACCOUNTNO_INVALID':
                            $result['res_msg'] = '请核实您的银行卡状态是否有效';
                            break;
                        case 'ACCOUNTNO_NOT_SUPPORT':
                            $result['res_msg'] = '暂不支持此银行，请更换您的银行卡';
                            break;
                        default:
                            $result['res_msg'] = $result['res_msg'];
                    }
                    $resultArr = array('ret' => '2', 'msg' => $result['res_msg']);
                    echo json_encode($resultArr);
                    exit;
                }
                $verify = 1;
            }
        }
        $postdata = Yii::$app->request->post();
        $area = (new Areas()) -> getAreaOrSubBank(1);
        $times = date('Y-m-d H:i:s');
        $userbank = new User_bank();
        $userbank->user_id = $user_id;
        $userbank->type = $cardbin['card_type'];
        $userbank->bank_abbr = $cardbin['bank_abbr'];
        $userbank->bank_name = $cardbin['bank_name'];
        $userbank->sub_bank = htmlspecialchars((new Areas()) -> getAreaOrSubBank(2));
        $userbank->province = $area['province'];
        $userbank->city = $area['city'];
        $userbank->area = $area['area'];
        $userbank->is_new = 1;
        $userbank->card = $bank_card;
        $userbank->bank_mobile = $user->mobile;
        $userbank->status = 1;
        $userbank->verify = $verify;
        $userbank->create_time = $times;
        $userbank->last_modify_time = $times;

        $ret_userbank = $userbank->save();
        if ($ret_userbank) {
            //绑卡成功，清除redis里的数据
            Yii::$app->redis->del($key);
            Yii::$app->redis->del($key_requestid);
            $resultArr = array('ret' => '0', 'msg' => '');
            echo json_encode($resultArr);
            exit;
        } else {
            $resultArr = array('ret' => '3', 'msg' => '');
            echo json_encode($resultArr);
            exit;
        }
    }

    //发送验证码
    public function actionBanksend() {
        $mobile = $_POST['mobile'];
        $user_id = $_POST['user_id'];
        $cardno = $_POST['cardno'];
        $key = "bind_bank_" . $mobile;
        $isbindyeepay = 'no';

        //请求易宝的绑卡接口，如果符合易宝的绑卡条件，则用易宝的绑卡，否则用天行
        $sql = "SELECT * FROM " . Card_bin::tableName() . " WHERE card_length = " . strlen($cardno) . " AND prefix_value=left(" . $cardno . ",prefix_length) order by prefix_length desc";
        $cardbin = Yii::$app->db->createCommand($sql)->queryOne();
        $bank_code = !empty($cardbin['bank_abbr']) ? $cardbin['bank_abbr'] : '';
        $result = Bank::supportbank($bank_code);
        if ($result && $cardbin['card_type'] == '0') {
            $user = User::find()->select(array('realname', 'identity'))->where(['user_id' => $user_id])->one();
            $postdata = array(
                'requestid' => date('Ymdhis') . rand(100000, 999999),
                'identityid' => $this->getPayIdentityid($user_id, $cardno),
                'cardno' => $cardno,
                'idcardtype' => '01',
                'idcardno' => $user->identity,
                'username' => $user->realname,
                'phone' => $mobile,
                'userip' => Yii::$app->request->getUserIP()
            );
            $openApi = new Apihttp;
            $ret = $openApi->invokebindbankcard($postdata);
            if ($ret['res_code'] == '0000') {
                //获取请求号和短信验证码
                $requestid = $ret['res_msg']['requestid'];
                $code = $ret['res_msg']['smscode'];
                $key_requestid = 'requestid_bank_' . $mobile;
                Yii::$app->redis->setex($key, 1800, $code);
                Yii::$app->redis->setex($key_requestid, 1800, $requestid);
            } else {
                //易宝请求绑定未成功，则调用天行验证
                $code_byredis = Yii::$app->redis->get($key);
                if (!empty($code_byredis)) {
                    $code = $code_byredis;
                } else {
                    $length = 6;
                    $code = substr(str_shuffle("012345678901234567890123456789"), 0, $length);
                }
                if ($ret['res_code'] == '2605') {
                    $isbindyeepay = 'yes';
                }
            }
        } else {
            $code_byredis = Yii::$app->redis->get($key);
            if (!empty($code_byredis)) {
                $code = $code_byredis;
            } else {
                $length = 6;
                $code = substr(str_shuffle("012345678901234567890123456789"), 0, $length);
            }
        }

        //一天只能发送6条短信
        $sms = new Sms();
        $sms_count = $sms->getSmsCount($mobile, 7);
        if ($sms_count >= 6) {
            $resultArr = array('ret' => '2', 'url' => '', 'isyeepay' => $isbindyeepay);
            echo json_encode($resultArr);
            exit; //已有用户绑定
        }

        $api = new ApiSms();
        $api->sendBindCard($mobile, 7);

        $resultArr = array('ret' => '0', 'url' => '', 'isyeepay' => $isbindyeepay);
        echo json_encode($resultArr);
        exit;
    }

    /**
     * 转换
     */
    private function getPayIdentityid($identityid, $cardno) {
        if (!$identityid || !$cardno) {
            return '';
        }
        $card_top = substr($cardno, 0, 6);
        $card_last = substr($cardno, -4);
        $identityid = $identityid . '-' . $card_top . $card_last;
        return $identityid;
    }

    public function actionPayyibao() {
        $post_data = \Yii::$app->request->post();
        $user = User::findOne($post_data['userid']);
        $user_id = $user['user_id'];
        $orderid = date('YmdHis') . rand(1000, 9999);
        $money = 2;
        $bind_mob = $user->mobile;
        $userCard = new User_bincard_list();
        $userCard->biancard_id = $orderid;
        $userCard->user_id = $user_id;
        $userCard->money = round($money / 100, 2);
        $userCard->bank_mobile = $bind_mob;
        $userCard->card = $post_data['card'];
        $userCard->createtime = date('Y-m-d H:i:s');
        $userCard->platform = 2;
        $ret = $userCard->save();
        $cardbin = (new Card_bin())->getCardBinByCard($post_data['card']);
        if ($ret && !empty($cardbin)) {
            $user = User::findOne($user_id);
            $card_type = ($cardbin['card_type'] == 0) ? 1 : 2;
            $postData = array(
                'orderid' => $orderid, // 请求唯一号
                'identityid' => (string) $user_id, // 用户标识
                'bankname' => $cardbin['bank_name'], //银行名称
                'bankcode' => $cardbin['bank_abbr'], //银行编码
                'card_type' => $card_type, // 卡类型
                'cardno' => $post_data['card'], // 银行卡号
                'idcard' => $user->identity, // 身份证号
                'username' => $user->realname, // 姓名
                'phone' => $user->mobile, // 预留手机号
                'productcatalog' => '7', // 商品类别码
                'productname' => '验证银行卡', // 商品名称
                'productdesc' => '绑定银行卡验证', // 商品描述
                'amount' => 2, // 交易金额
                'orderexpdate' => 60, // 交易金额
                'business_code' => 'YYYWX',
                'userip' => $_SERVER["REMOTE_ADDR"], // 交易金额
                'callbackurl' => Yii::$app->params['yibao_bank'], // 交易金额
            );
            $openApi = new ApiClientCrypt;
            $res = $openApi->sent('payroute/pay', $postData, 2);
            $result = $openApi->parseResponse($res);
            Logger::errorLog(print_r($result, true), 'openbankpay');
            if ($result['res_code'] == 0) {
                return $this->redirect($result['res_data']['url']);
            } else {
                return $this->redirect('/dev/bank/error');
            }
        } else {
            return $this->redirect('/dev/bank/error');
        }
    }

    public function actionPaylian() {
        $post_data = \Yii::$app->request->post();
        $user = User::findOne($post_data['userid']);
        $user_id = $user['user_id'];
        $biancard_id = date('YmdHis') . rand(1000, 9999);
        //@TODO:暂时连连支付属于互联网还款  entity：实名类,互联网还款 virtual:小额虚拟,话费充值
        $merchant_type = 'entity';
        //@TODO:连连目前只支持wap  app_request=1
        $app_request = 1;
        $dt_order = date('YmdHis');

        $name_goods = '一亿元绑定';
        //@TODO:正式上线改为实际获取的金额，没有默认
        $money_order = 0.01;
        //@TODO:目前只支持借记卡 pay_type=2     3：快捷支付（信用卡）
        $pay_type = isset($post_data['pay_type']) ? floatval($post_data['pay_type']) : '2';

        $bind_mob = $user['mobile'];
        $id_no = isset($post_data['identity']) ? $post_data['identity'] : $user['identity'];
        $acct_name = isset($post_data['realname']) ? $post_data['realname'] : $user['realname'];
        //@TODO:正式上线改为实际获取的身份证，没有默认
        $card_no = isset($post_data['card']) ? $post_data['card'] : '';

        $notify_url = Yii::$app->params['bank_notify_url'];
        $url_return = Yii::$app->params['app_url'] . '/dev/bank/success';

        $risk['frms_ware_category'] = $merchant_type == 'entity' ? '1010 ' : '2010';
        $risk['user_info_mercht_userno'] = $user_id;
        $risk['user_info_dt_register'] = date('YmdHis', strtotime($user['create_time']));
        $risk_item = json_encode($risk);
        $createtime = date('Y-m-d H:i:s', strtotime($dt_order));
        $times = date('Y-m-d H:i:s');
        $userCard = new User_bincard_list();
        $userCard->biancard_id = $biancard_id;
        $userCard->user_id = $user_id;
        $userCard->money = $money_order;
        $userCard->bank_mobile = $bind_mob;
        $userCard->card = $card_no;
        $userCard->createtime = date('Y-m-d H:i:s');
        $ret = $userCard->save();
        if ($ret) {
            if ($pay_type == 2) {
                $result = Http::payLianLian($user_id, $merchant_type, $app_request, $biancard_id, $dt_order, $name_goods, $money_order, $notify_url, $url_return, $risk_item, $pay_type, $id_no, $acct_name, $card_no);
            } else {
                $result = Http::payLianLian($user_id, $merchant_type, $app_request, $biancard_id, $dt_order, $name_goods, $money_order, $notify_url, $url_return, $risk_item, $pay_type, $id_no, $acct_name, $card_no);
            }
            if ($result) {
//                 $arr = $this->classToArray($result);
//                 $arr['biancard_id'] = $biancard_id;
//                 return json_encode($arr);
                $redirect_url = trim($result->lianlian_payment);
                return $this->redirect($redirect_url);
            } else {
//                 return json_encode(array(
//                     'rsp_code' => '99990',
//                     'rsp_msg' => '操作失败',
//                 ));
                return $this->redirect('/dev/bank/error');
            }
        } else {
//             return json_encode(array(
//                 'rsp_code' => '99990',
//                 'rsp_msg' => '操作失败',
//             ));
            return $this->redirect('/dev/bank/error');
        }
    }

    public function actionPay() {
        $post_data = \Yii::$app->request->post();
//        print_r($post_data);
        //@TODO:暂时连连支付属于互联网还款  entity：实名类,互联网还款
        $merchant_type = 'entity';
        //@TODO:连连目前只支持wap  app_request=1
        $app_request = 1;
        $user = User::findOne($post_data['userid']);
        $bind_mob = $user['mobile'];
        $pay_key = isset($post_data['pay_key']) ? $post_data['pay_key'] : '';
        $verifyCode = isset($post_data['verifyCode']) ? $post_data['verifyCode'] : '';
        $isrecord = isset($post_data['isrecord']) ? $post_data['isrecord'] : 'yes';
        $userCard = User_bincard_list::find()->where(['biancard_id' => $post_data['biancard_id']])->one();
        $userCard->pay_key = $post_data['pay_key'];
        $userCard->code = $post_data['verifyCode'];
        $ret = $userCard->save();
        if ($ret) {
            $result = Http::subPayLian($merchant_type, $app_request, $pay_key, $bind_mob, $verifyCode, $isrecord);
            if ($result) {
                $arr = $this->classToArray($result);
                if ($arr['rsp_code'] == '0000') {
                    $usercard = User_bincard_list::find()->where(['biancard_id' => $arr['no_order']])->one();
                    if (!empty($usercard) && empty($usercard->paybill)) {
                        $transaction = Yii::$app->db->beginTransaction();
                        $times = date('Y-m-d H:i:s');
                        $usercard->status = 1;
                        $usercard->paybill = $arr['oid_paybill'];
                        $usercard->actual_money = $arr['money_order'];
                        $ret = $usercard->save();
                        if ($ret) {
                            $sql = "SELECT * FROM " . Card_bin::tableName() . " WHERE card_length = " . strlen($usercard->card) . " AND prefix_value=left(" . $usercard->card . ",prefix_length) order by prefix_length desc";
                            $cardbin = Yii::$app->db->createCommand($sql)->queryOne();
                            $userbank = new User_bank();
                            $userbank->user_id = $usercard->user_id;
                            $userbank->type = $cardbin['card_type'];
                            $userbank->bank_abbr = $cardbin['bank_abbr'];
                            $userbank->bank_name = $cardbin['bank_name'];
                            $userbank->card = $usercard->card;
                            $userbank->bank_mobile = $usercard->bank_mobile;
                            $userbank->status = 1;
                            $userbank->create_time = $times;
                            $userbank->last_modify_time = $times;
                            if ($userbank->type == 1) {
                                $userbank->validate = $usercard->validate;
                                $userbank->cvv2 = $usercard->cvv2;
                            }
                            $ret_userbank = $userbank->save();
                            if ($ret_userbank) {
                                //绑卡人账户额度提升500
                                $account = Account::find()->where(['user_id' => $usercard->user_id])->one();
                                $account['remain_amount'] -= 500;
                                $account['amount'] += 500;
                                $account['current_amount'] += 500;
                                $account['version'] += 1;
                                $account['total_income'] += $arr['money_order'];
                                try {
                                    $account->save();
                                    $amount_date = array(
                                        'type' => 13,
                                        'user_id' => $usercard->user_id,
                                        'amount' => 500
                                    );
                                    $user_amount = new User_amount_list();
                                    $user_amount->CreateAmount($amount_date);
                                    $transaction->commit();
                                } catch (Exception $ex) {
                                    $transaction->rollBack();
                                }
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

    //回显地址
    public function actionSuccess() {
        $old = isset($_GET['old']) ? intval($_GET['old']) : 0;
        $parr = array();
        $jsinfo = $this->getWxParam();
        if ($old == 0) {
            $amount = Yii::$app->request->get('repay_id');
            return $this->render('success', [
                        'old' => $old,
                        'account' => round($amount / 100, 2),
                        'jsinfo' => $jsinfo,
            ]);
        } else {
            return $this->render('success', [
                        'old' => $old,
                        'jsinfo' => $jsinfo,
            ]);
        }
    }

    //回显地址
    public function actionError() {
        $jsinfo = $this->getWxParam();
        return $this->render('error', [
                    'jsinfo' => $jsinfo,
        ]);
    }

    //绑卡服务器异步通知地址
    public function actionBanknotify() {
        if (isset($_GET)) {
            Logger::errorLog(print_r($_GET, true), 'bank_Notifyurl');
            $parr = $_GET;
            unset($parr['s']);
        } else {
            $data_url = file_get_contents("php://input");
            Logger::errorLog(print_r($data_url, true), 'bank_Notifyurl');
            parse_str($data_url, $parr);
        }
        Logger::errorLog(print_r($parr, true), 'BankNotify');
        $md5_key = Yii::$app->params['xianhua_key'];
        $md = Http::createMd5($parr, $md5_key, 1);
        if (isset($parr['sign']) && $md == $parr['sign']) {
            if ($parr['result_pay'] == 'SUCCESS') {
                $usercard = User_bincard_list::find()->where(['biancard_id' => $parr['no_order']])->one();
                if (!empty($usercard) && empty($usercard->paybill)) {
                    $transaction = Yii::$app->db->beginTransaction();
                    $times = date('Y-m-d H:i:s');
                    $usercard->status = 1;
                    $usercard->paybill = $parr['oid_paybill'];
                    $usercard->actual_money = $parr['money_order'];
                    $ret = $usercard->save();
                    if ($ret) {
                        $sql = "SELECT * FROM " . Card_bin::tableName() . " WHERE card_length = " . strlen($usercard->card) . " AND prefix_value=left(" . $usercard->card . ",prefix_length) order by prefix_length desc";
                        $cardbin = Yii::$app->db->createCommand($sql)->queryOne();
                        $userbank = new User_bank();
                        $userbank->user_id = $usercard->user_id;
                        $userbank->type = $cardbin['card_type'];
                        $userbank->bank_abbr = $cardbin['bank_abbr'];
                        $userbank->bank_name = $cardbin['bank_name'];
                        $userbank->card = $usercard->card;
                        $userbank->bank_mobile = $usercard->bank_mobile;
                        $userbank->status = 1;
                        $userbank->create_time = $times;
                        $userbank->last_modify_time = $times;
//                         if ($userbank->type == 1) {
//                             $des3key = Yii::$app->params['des3key'];
//                             $userbank->validate = $usercard->validate;
//                             $userbank->cvv2 = \Crypt3Des::encrypt($usercard->cvv2, $des3key);
//                         }
                        $ret_userbank = $userbank->save();
                        if ($ret_userbank) {
                            //绑卡人账户额度提升500
                            $account = Account::find()->where(['user_id' => $usercard->user_id])->one();
                            $account['remain_amount'] -= 500;
                            $account['amount'] += 500;
                            $account['current_amount'] += 500;
                            $account['version'] += 1;
                            $account['total_income'] += $parr['money_order'];
                            try {
                                $account->save();
                                $amount_date = array(
                                    'type' => 13,
                                    'user_id' => $usercard->user_id,
                                    'amount' => 500
                                );
                                $user_amount = new User_amount_list();
                                $user_amount->CreateAmount($amount_date);
                                $transaction->commit();
                                $arr = array(
                                    'rsp_code' => '0000',
                                    'rsp_msg' => '交易成功',
                                );
                                print_r(json_encode($arr));
                                exit;
                            } catch (Exception $ex) {
                                $transaction->rollBack();
                            }
                        } else {
                            $transaction->rollBack();
                        }
                    } else {
                        $transaction->rollBack();
                    }
                } else {
                    $arr = array(
                        'rsp_code' => '0000',
                        'rsp_msg' => '交易成功',
                    );
                    print_r(json_encode($arr));
                    exit;
                }
            }
        }
    }

    public function classToArray($cla) {
        $arr = array();
        foreach ($cla as $key => $val) {
            $arr[$key] = $val;
        }
        return $arr;
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

}
