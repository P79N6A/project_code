<?php

namespace app\modules\background\controllers;

use Yii;
use app\commands\SubController;
use app\models\dev\User;
use app\models\dev\User_loan;
use app\models\dev\User_bank;
use app\models\dev\Webunion_account;
use app\models\dev\Account_settlement;
use app\models\dev\User_remit_list;
use app\models\dev\Webunion_flow_settlement;
use app\commonapi\Apihttp;
use app\commonapi\apiInterface\Remit;
use app\commonapi\Common;
use app\commonapi\Http;

class ReceiveController extends SubController {

    public $layout = "index_n";
    public $enableCsrfValidation = false;

    private function getUser() {
        return Yii::$app->newDev->identity;
    }

    //提现
    public function actionIndex() {
        $user = $this->getUser();
        $open_id = $this->getVal('openid');
        if (empty($open_id) || empty($user)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/new/reg?url=/background/webunion/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }

        $user_id = $user->user_id;
        $accountinfo = Webunion_account::find()->where(['user_id' => $user_id])->one();
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
//查一下 银行卡
        $returnUrl = '/background/wallet/index';
        $user_bank = User_bank::find()->where(['user_id' => $user_id, 'status' => 1])->all();
        $jsinfo = $this->getWxParam();
        $this->getView()->title = "提现";
        return $this->render('index', [
                    'user_id' => $user_id,
                    'user_bank' => $user_bank,
                    'accountinfo' => $accountinfo,
                    'limitStatus' => $limitStatus,
                    'jsinfo' => $jsinfo,
                    'returnUrl' => $returnUrl
        ]);
    }

    //提现收益
    public function actionOutincome() {
        $user_id = Yii::$app->request->post('user_id');
        $outincome = Yii::$app->request->post('outincome');
        $bank_id = Yii::$app->request->post('bank_id');
        $day = date('d');
        if ($day != 9 && $day != 10) {
            $ret = array('ret' => 1, 'msg' => '每月9、10日为提现日，详情请查看消息公告');
            echo json_encode($ret);
            exit;
        }
        $black_user = \app\commonapi\Keywords::getBlackUserId();
        if (in_array($user_id, $black_user)) {
            $ret = array('ret' => 1, 'msg' => '账户存在异常，请联系客服');
            echo json_encode($ret);
            exit;
        }
//        $shouxf = Yii::$app->request->post('shouxf');
//        if ($user_id == 1105150 || $user_id == 1120049 || $user_id == 1198815 || $user_id == 1199637 || $user_id == 1200976 || $user_id == 1201100 || $user_id == 1202237 || $user_id == 1203353 || $user_id == 1207765 || $user_id == 1207825 || $user_id == 1209410 || $user_id == 1209621 || $user_id == 1210247 || $user_id == 1219605 || $user_id == 1219605 || $user_id == 1219630 || $user_id == 1224331 || $user_id == 1227338 || $user_id == 1227611 || $user_id == 1236675 || $user_id == 1237239 || $user_id == 1238087 || $user_id == 1188452 || $user_id == 1231750 || $user_id == 1261385 || $user_id == 1259719 || $user_id == 1255018 || $user_id == 1258575) {
//            $ret = array('ret' => 1, 'msg' => '账户存在异常，请联系客服');
//            echo json_encode($ret);
//            exit;
//        }

        if (empty($user_id) || $outincome < 100) {
            $ret = array('ret' => 1, 'msg' => '请返回重试！');
            echo json_encode($ret);
            exit;
        }
        //0点到7点不能提现
        $time_1 = "18:00";
        $time_2 = "10:30";
        if (date('H:i') > $time_1 || date('H:i') < $time_2) {
            $ret = array('ret' => 11, 'msg' => '18:00到次日10:30暂停提现');
            echo json_encode($ret);
            exit;
        }
        //每个用户每天只能操作1次@Todo 老规则不确定还要不要
        $begintime = date('Y-m-d' . ' 00:00:00');
        $endtime = date('Y-m-d' . ' 23:59:59');
        $count = Account_settlement::find()->where("user_id=$user_id and create_time >= '$begintime' and create_time <= '$endtime' and type=4 and status IN('INIT','SUCCESS')")->count();
        if ($count >= 1) {
            $ret = array('ret' => 3, 'msg' => '您今天已经提过了，请明天再来~~');
            echo json_encode($ret);
            exit;
        }


        //当前时间
        $now_time = date('Y-m-d H:i:s');
        //今天
        $today_begin = date('Y-m-d 00:00:00');
        //当月
        $month_begin = date('Y-m-01 00:00:00');
        $today_amount = Account_settlement::find()->where(['user_id' => $user_id, 'type' => 4])->andFilterWhere(['>=', 'create_time', $today_begin])->andFilterWhere(['<=', 'create_time', $now_time])->andFilterWhere(['IN', 'status', ['INIT', 'SUCCESS']])->sum('amount');
        $month_amount = Account_settlement::find()->where(['user_id' => $user_id, 'type' => 4])->andFilterWhere(['>=', 'create_time', $month_begin])->andFilterWhere(['<=', 'create_time', $now_time])->andFilterWhere(['IN', 'status', ['INIT', 'SUCCESS']])->sum('amount');
        if ($today_amount + $outincome > 2500) {
            $ret = array('ret' => 3, 'msg' => '单日最多可提2500元，请明天再来~~');
            echo json_encode($ret);
            exit;
        } else if ($month_amount + $outincome > 5000) {
            $ret = array('ret' => 3, 'msg' => '单月最多可提5000元，请下月再来~~');
            echo json_encode($ret);
            exit;
        }
        //可提现余额
        $remain = Webunion_account::find()->where(['user_id' => $user_id])->one();
        if (bccomp(floatval($remain->total_history_interest - $remain->total_on_interest), floatval($outincome), 2) == -1) {
            $ret = array('ret' => 3, 'msg' => '您提现金额大于可提现总收益');
            echo json_encode($ret);
            exit;
        }
//        if ($remain->total_history_interest - $remain->total_on_interest < $outincome) {
//            
//        }
        $shouxf = 2;
        if ($outincome * 0.01 > 2 && $outincome * 0.01 < 20) {
            $shouxf = round($outincome * 0.01, 2);
        } else if ($outincome * 0.01 > 20) {
            $shouxf = 20.00;
        } else {
            $shouxf = 2.00;
        }
        // 测试使用
//         $ret = array('ret' => 0, 'msg' => '成功');
//         echo json_encode($ret);
//         exit;
//1.生成提现记录
        $model = new Account_settlement();
        $model->settlement_id = date('Ymdhis') . rand(1000, 9999);
        $model->user_id = $user_id;
        $model->bank_id = $bank_id;
        $model->amount = $outincome;
        $model->fee = $shouxf;
        $model->type = 4;
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
            $settle_amount = $outincome - $shouxf;
            $order_id = date('Ymdhis') . rand(100000, 999999);
//            if ($settle_amount >= 500) {
            $loan_id = $account_settlement_id;
            $admin_id = -1;
            $settle_request_id = $order_id;
            $real_amount = $settle_amount;
            $settle_fee = 0;
            $rsp_code = '0000';
            $remit_status = 'INIT';
            $create_time = date('Y-m-d H:i:s', time());

            $sql = "insert into " . User_remit_list::tableName() . "(loan_id,admin_id,settle_request_id,real_amount,settle_fee,settle_amount,rsp_code,remit_status,create_time,bank_id,type,user_id,order_id) ";
            $sql .= "value('" . $loan_id . "',$admin_id,'$settle_request_id','$real_amount ','$settle_fee','$settle_amount','$rsp_code','$remit_status','$create_time','$bank_id',5,'$user_id','$order_id')";
            $retinsert = Yii::$app->db->createCommand($sql)->execute();

            if ($retinsert >= 0) {
                //打款成功，修改收益提现记录状态
                $sql = "update " . Account_settlement::tableName() . " set status='SUCCESS' ,version=version+1 where id=" . $account_settlement_id;
                Yii::$app->db->createCommand($sql)->execute();
                $sql = "update " . Webunion_account::tableName() . " set total_on_interest = total_on_interest+$outincome ,version=version+1  where user_id= $user_id";
                Yii::$app->db->createCommand($sql)->execute();
                $ret = array('ret' => 0, 'msg' => '成功');
                echo json_encode($ret);
                exit;
            } else {
                //记录一下日志,出款记录日志
                \Logger::errorLog($sql, 'ccount_settlement_failed');
                $ret = array('ret' => 0, 'msg' => '成功咯');
                echo json_encode($ret);
                exit;
            }
//            } else {
//                $params = [
//                    'req_id' => $order_id,
//                    'remit_type' => 3,
//                    'identityid' => $userinfo->identity,
//                    'user_mobile' => $user_mobile,
//                    'guest_account_name' => $user_name,
//                    'guest_account_bank' => $guest_account_bank,
//                    'guest_account_province' => '北京',
//                    'guest_account_city' => '北京',
//                    'guest_account_bank_branch' => $guest_account_bank,
//                    'guest_account' => $guest_account,
//                    'settle_amount' => $settle_amount,
//                    'callbackurl' => Yii::$app->params['remit_repay'],
//                ];
//                $apihttp = new Remit();
//                $res = $apihttp->outBlance($params);
//                if ($res['res_code'] == '0000') {
//                    //更新收益提现记录表状态
//                    $loan_id = $account_settlement_id;
//                    $admin_id = -1;
//                    $settle_request_id = $res['res_msg']['client_id'];
//                    $real_amount = $res['res_msg']['settle_amount'];
//                    $settle_fee = 0;
//                    $settle_amount = $res['res_msg']['settle_amount'];
//                    $rsp_code = $res['res_code'];
//                    $remit_status = 'INIT';
//                    $create_time = date('Y-m-d H:i:s', time());
//                    //给数据库的user_remit_list 插入一条数据
//                    $sql = "insert into " . User_remit_list::tableName() . "(loan_id,admin_id,settle_request_id,real_amount,settle_fee,settle_amount,rsp_code,remit_status,create_time,bank_id,type,user_id,order_id) ";
//                    $sql .= "value('" . $loan_id . "',$admin_id,'$settle_request_id','$real_amount ','$settle_fee','$settle_amount','$rsp_code','$remit_status','$create_time','$bank_id',5,'$user_id','$order_id')";
//                    $retinsert = Yii::$app->db->createCommand($sql)->execute();
//
//                    if ($retinsert >= 0) {
//                        //打款成功，修改收益提现记录状态
//                        $sql = "update " . Account_settlement::tableName() . " set status='SUCCESS' ,version=version+1 where id=" . $account_settlement_id;
//                        Yii::$app->db->createCommand($sql)->execute();
//                        $sql = "update " . Webunion_account::tableName() . " set total_on_interest = total_on_interest+$outincome ,version=version+1  where user_id= $user_id";
//                        Yii::$app->db->createCommand($sql)->execute();
//                        $ret = array('ret' => 0, 'msg' => '成功');
//                        echo json_encode($ret);
//                        exit;
//                    } else {
//                        //记录一下日志,出款记录日志
//                        \Logger::errorLog($sql, 'ccount_settlement_failed');
//                        $ret = array('ret' => 0, 'msg' => '成功咯');
//                        echo json_encode($ret);
//                        exit;
//                    }
//                } else if ($res['res_code'] == '13003') {
//                    $sql = "update " . Account_settlement::tableName() . " set status='FAILED' where id=" . $account_settlement_id;
//                    Yii::$app->db->createCommand($sql)->execute();
//                    $ret = array('ret' => 2, 'msg' => $res['res_msg']);
//                    echo json_encode($ret);
//                    exit;
//                } else {
//                    //打款失败，修改收益提现记录状态
//                    $sql = "update " . Account_settlement::tableName() . " set status='FAILED' where id=" . $account_settlement_id;
//                    Yii::$app->db->createCommand($sql)->execute();
//                    $ret = array('ret' => 2, 'msg' => '请稍候再试~~');
//                    echo json_encode($ret);
//                    exit;
//                }
//            }
        } else {
            $ret = array('ret' => 3, 'msg' => '请检查你的网络');
            echo json_encode($ret);
            exit;
        }
    }

    //提现列表
    public function actionWithlist() {
        $user = $this->getUser();
        $open_id = $this->getVal('openid');
        if (empty($open_id) || empty($user)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/new/reg?url=/background/webunion/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $user_id = $user->user_id;
        $accountinfo = Account_settlement::find()->where(['user_id' => $user_id, 'type' => 4, 'status' => 'SUCCESS'])->orderBy('create_time desc')->all();
        $jsinfo = $this->getWxParam();
        $returnUrl = '/background/receive/index';
        $this->getView()->title = "提现列表";
        return $this->render('withlist', [
                    'accountinfo' => $accountinfo,
                    'jsinfo' => $jsinfo,
                    'returnUrl' => $returnUrl
        ]);
    }

    //提现详情
    public function actionWithdetail() {
        $user = $this->getUser();
        $open_id = $this->getVal('openid');
        if (empty($open_id) || empty($user)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/new/reg?url=/background/webunion/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $id = $_GET['id'] + 0;

        $userinfo = Account_settlement::find()->where(['id' => $id])->one();

        $jsinfo = $this->getWxParam();
        $returnUrl = '/background/receive/withlist';
        $this->getView()->title = "提现详情";
        return $this->render('withdetail', [
                    'userinfo' => $userinfo,
                    'jsinfo' => $jsinfo,
                    'returnUrl' => $returnUrl
        ]);
    }

    //领取流量
    public function actionFlow() {
        $user = $this->getUser();
        $open_id = $this->getVal('openid');
        if (empty($open_id) || empty($user)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/new/reg?url=/background/webunion/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $user_id = $user->user_id;
        $accountinfo = Webunion_account::find()->where(['user_id' => $user_id])->one();
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
        $returnUrl = '/background/wallet/index';
        $jsinfo = $this->getWxParam();
        $this->getView()->title = "流量领取";
        return $this->render('flow', [
                    'user_id' => $user_id,
                    'accountinfo' => $accountinfo,
                    'limitStatus' => $limitStatus,
                    'jsinfo' => $jsinfo,
                    'returnUrl' => $returnUrl
        ]);
    }

    //领取流量列表
    public function actionFlowlist() {
        $user = $this->getUser();
        $open_id = $this->getVal('openid');
        if (empty($open_id) || empty($user)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/new/reg?url=/background/webunion/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $user_id = $user->user_id;
        $flowinfo = Webunion_flow_settlement::find()->where(['user_id' => $user_id, 'status' => 'SUCCESS'])->all();
        $jsinfo = $this->getWxParam();
        $returnUrl = '/background/receive/flow';
        $this->getView()->title = "流量列表";
        return $this->render('flowlist', [
                    'flowinfo' => $flowinfo,
                    'jsinfo' => $jsinfo,
                    'returnUrl' => $returnUrl
        ]);
    }

    //异步返回各个供应商能提流量的数额
    public function actionFlowsave() {
        $mobile = (new Common)->post('mobile');
        //$mobile = '15101151220';
        $arr = Http::mobileHome($mobile, 'json');
        if (!empty($arr) && isset($arr['catName'])) {
            if ($arr['catName'] == '中国移动') {
                //$yd = array(10, 30, 70);
                $yd = array(30, 70, 150);
                echo json_encode($yd);
                exit;
            } else if ($arr['catName'] == '中国联通') {
                $lt = array(20, 50, 100);
                echo json_encode($lt);
                exit;
            } else if ($arr['catName'] == '中国电信') {
                //$dx = array(5, 50, 100);
                $dx = array(30, 50, 100);
                echo json_encode($dx);
                exit;
            } else {
                $lt = array(00, 00, 00);
                echo json_encode($lt);
                exit;
            }
        } else {
            $lt = array(00, 00, 00);
            echo json_encode($lt);
            exit;
        }
    }

    //提取流量
    public function actionFlowincome() {
        $user_id = (new Common)->post('user_id');
        $flow_amount = (new Common)->post('flow_amount');
        $mobile = (new Common)->post('mobile');


        $begin_time = '2017-07-31 00:00:00';
        $end_time = '2017-07-31 23:59:59';
        $now_time = date('Y-m-d H:i:s');
        if ($now_time < $begin_time || $now_time > $end_time) {
            $ret = array('ret' => 1, 'msg' => '尊敬的用户，因产品升级维护流量领取暂时延后，可领取时间请关注领取通知，给您造成不便，敬请谅解');
            echo json_encode($ret);
            exit;
        }

        $arr = Http::mobileHome($mobile, 'json');
        if ($arr['catName'] == '中国移动') {
            $type = 1;
        } else if ($arr['catName'] == '中国联通') {
            $type = 2;
        } else if ($arr['catName'] == '中国电信') {
            $type = 3;
        } else {
            $type = 0;
        }

        if (empty($user_id) || empty($flow_amount) || empty($mobile)) {
            $ret = array('ret' => 1, 'msg' => '请返回重试！');
            echo json_encode($ret);
            exit;
        }

        //每个用户每天只能操作1次
        $begintime = date('Y-m-d' . ' 00:00:00');
        $endtime = date('Y-m-d' . ' 23:59:59');
        $count = Webunion_flow_settlement::find()->where("user_id=$user_id and create_time >= '$begintime' and create_time <= '$endtime'")->count();
        if ($count >= 2) {
            $ret = array('ret' => 3, 'msg' => '您今天已经提过2次了，请明天再来~~');
            echo json_encode($ret);
            exit;
        }

        // // 测试使用
        // $ret = array('ret' => 0, 'msg' => '成功');
        // echo json_encode($ret);
        // exit;

        $model = new Webunion_flow_settlement();
        $model->settlement_id = date('Ymdhis') . rand(1000, 9999);
        $model->user_id = $user_id;
        $model->mobile_type = $type;
        $model->flow_amount = $flow_amount;
        $model->mobile = $mobile;
        $model->status = "INIT";
        $model->create_time = date('Y-m-d H:i:s');
        $model->last_modify_time = date('Y-m-d H:i:s');
        $model->version = 1;
        if ($model->save()) {
            //流量充值
            $postdata = array(
                'mobile' => $mobile,
                'package' => $flow_amount,
                'type' => 1
            );
            $openApi = new Apihttp;
            $res = $openApi->mobileRecharge($postdata);
            $id = Yii::$app->db->getLastInsertID();

            if ($res['res_code'] == '0000') {
                $msg_id = $res['res_msg']['msg_id'];
                //更新流量表 状态为成功
                $sql = "update " . Webunion_flow_settlement::tableName() . " set status='PROCESS',flow_id=$msg_id ,version=version+1 where id=" . $id;
                Yii::$app->db->createCommand($sql)->execute();
                $sql = "update " . Webunion_account::tableName() . " set total_on_flow = total_on_flow+$flow_amount ,version=version+1  where user_id= $user_id";
                Yii::$app->db->createCommand($sql)->execute();
                $ret = array('ret' => 0, 'msg' => '成功');
                echo json_encode($ret);
                exit;
            } else {
                //打款失败，修改l流量记录状态
                $sql = "update " . Webunion_flow_settlement::tableName() . " set status='FAILED' where id=" . $id;
                Yii::$app->db->createCommand($sql)->execute();
                $ret = array('ret' => 2, 'msg' => '请稍候再试~~');
                echo json_encode($ret);
                exit;
            }
        } else {

            $ret = array('ret' => 2, 'msg' => '系统错误');
            echo json_encode($ret);
            exit;
        }
    }

}
