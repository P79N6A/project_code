<?php

namespace app\modules\mall\controllers;

use app\common\ApiClientCrypt;
use app\common\ApiCrypt;
use app\commonapi\Apihttp;
use app\commonapi\Common;
use app\commonapi\Crypt3Des;
use app\commonapi\ImageHandler;
use app\commonapi\Keywords;
use app\commonapi\Logger;
use app\models\news\Areas;
use app\models\news\Goods_address;
use app\models\news\Goods_address_flows;
use app\models\news\Goods_attribute_value;
use app\models\news\Goods_category_list;
use app\models\news\Goods_list;
use app\models\news\Goods_order_terms;
use app\models\news\Goods_pic;
use app\models\news\GoodsOrder;
use app\models\news\Mall_banner;
use app\models\news\MallOrder;
use app\models\news\MallOrderAddress;
use app\models\news\MallOrderPay;
use app\models\news\ScanTimes;
use app\models\news\Setting;
use app\models\news\User;
use app\models\news\Juxinli;
use app\models\news\User_bank;
use app\models\news\User_credit;
use app\models\news\UserCreditList;
use app\models\news\User_loan;
use Yii;
use yii\data\Pagination;

class StoreController extends MallController {

    public $layout = "store";
    private $trial = '3.6.0';
    private $user_id_encryption = '';
    private $user_id;
    private $o_user = NULL;
    private $is_inspect_open = FALSE; //true：进场 false：离场
    private $is_trial = FALSE; //true：过审中 false：过审后
    private $is_white = FALSE; //true：白名单 false：普通用户
    private $is_app = FALSE; //true：app端 false：非app端
    private $is_android = FALSE; //true：安卓 false：非安卓
    private $is_zhirongyaoshi = FALSE; //true：显示贷超 false：不显示贷超
    private $is_goloan = TRUE; //true：显示引导去借款 false：不显示引导去借款

    public function behaviors() {
        return [];
    }

    public function init() {
        $this->isApp();
        if ($this->is_app) {
            $userId = $this->get('user_id_store', '');
            if (!empty($userId)) {
                $this->user_id_encryption = urlencode($userId);
                $api = new ApiClientCrypt();
                $this->user_id = Crypt3Des::decrypt($userId, $api->getKey()); //24BEFILOPQRUVWXcdhntvwxy
                Logger::dayLog('weixin/mall', $userId, $this->user_id_encryption, $this->user_id);
                if (empty($this->user_id)) {
                    exit('用户信息不存在');
                }
                $this->o_user = (new User())->getById($this->user_id);
                if (!$this->o_user) {
                    exit('用户信息不存在');
                }
                Yii::$app->newDev->login($this->o_user, 1);
            }
        }
        $this->o_user = $this->getUser();
        $this->user_id = !empty($this->o_user) ? $this->o_user->user_id : '';
        $this->isTrial();
        $this->isWhite();
        $this->isAndroid();
        $this->isZhirongyaoshi();
    }

    public function actionIndex() {
        $this->getView()->title = "商城";
        //未登陆
        if (empty($this->o_user)) {
            return $this->viewHomeNoLogin();
        }
        //ios过审中、ios&安卓白名单=>过审页面
        //检查进场=>过审页面
        if ($this->is_inspect_open || $this->is_white) {
            return $this->viewHomeTrial();
        }
        return $this->viewHome();
    }

    //商品列表页
    public function actionList() {
        $this->isWhite();
        $oUser=$this->getUser();
        $is_login=0;
        if(!empty($oUser)){
            $is_login=1;
        }
        $this->layout = "index";
        $type = $this->get('type');
        $supermarketOpen = Keywords::supermarketOpen();
        //获取全部商品分类
        $categoryModel = new Goods_category_list();
        $allGoodsTypes = $categoryModel->listCategory(0, 1, 1, [0, 1], 10);
        $this->getView()->title = $categoryModel->getNameById($type);
        $goodsListModel = new Goods_list();
        $model = $goodsListModel->getGoodsByCid($type, 30);
        $count = $goodsListModel->find()->where(['cid' => $type])->count();
        $pageTotal = ceil($count / 30);
        $user = $this->getUser();
        $userid = "";
        if (!empty($user)) {
            $userid = $user->user_id;
        }
        $this->isApp();
        $this->isAndroid();
        return $this->render('list', [
                   'is_android' => $this->is_android,
                    'is_app' => $this->is_app,
                    'model' => $model,
                    'is_login'=>$is_login,
                    'type' => $type,
                    'allGoodsTypes' => $allGoodsTypes,
                    'pageTotal' => $pageTotal,
                    'user_id_store' => $this->user_id_encryption,
                    'userid' => $userid,
                    'supermarketOpen' => $supermarketOpen,
                    'is_white'=>$this->is_white,
                    'csrf' => $this->getCsrf(),
        ]);
    }

    //商品详情
    public function actionDetail() {
        if ($this->is_inspect_open || $this->is_white) {
            return $this->doShopDetail();
        } else {
            return $this->doDetail();
        }
    }

    //获取订单转售商城链接
    public function actionXhshopajax() {
        $category_id = $this->post('category_id', '');
        $oUser = $this->getUser();
        if (empty($oUser)) {
            return json_encode(['rsp_code' => '0001', 'rsp_msg' => '请登录!']);
        }
        $settingModel = new Setting();
        $shop_setting = $settingModel->getShop();
        $is_work = $this->isWork();
        if (!$is_work) {
            if (!$shop_setting || $shop_setting->status != 0) { //商城开关已关闭
                return json_encode(['rsp_code' => '0007', 'rsp_msg' => '商城暂未开放，敬请期待']);
            }
        }
        //待支付订单
        $waitOrder = $this->getShoporder($oUser, 1);
        $xhshop_url = (new User())->getShopurl($oUser, 2);
        if ($waitOrder && !empty($waitOrder['rsp_data'])) {
            $time = empty($waitOrder['rsp_data']['time']) ? 0 : ($waitOrder['rsp_data']['time']['hour'] . ':' . $waitOrder['rsp_data']['time']['min'] . ':' . $waitOrder['rsp_data']['time']['sec']);
            return json_encode(['rsp_code' => '0003', 'rsp_msg' => '您有一笔待支付的订单', 'rsp_data' => ['url' => $xhshop_url, 'time' => $time]]);
        }
        if ($waitOrder && empty($waitOrder['rsp_data'])) { //数据异常
            return json_encode(['rsp_code' => '0005', 'rsp_msg' => $waitOrder['rsp_msg'], 'rsp_data' => '']);
        }
        //进行中订单
        $doingOrder = $this->getShoporder($oUser, 2);
        if ($doingOrder && !empty($doingOrder['rsp_data'])) {
            return json_encode(['rsp_code' => '0004', 'rsp_msg' => '您有一笔进行中的订单', 'rsp_data' => ['url' => $xhshop_url]]);
        }
        if ($doingOrder && empty($doingOrder['rsp_data'])) { //数据异常
            return json_encode(['rsp_code' => '0006', 'rsp_msg' => $doingOrder['rsp_msg'], 'rsp_data' => '']);
        }
        //进行中借款
        $loaning_result = $this->getLoaning($oUser);
        $bill_url = '/borrow/billlist/index'; //账单url
        if ($loaning_result) {
            return json_encode(['rsp_code' => '0002', 'rsp_msg' => '您有一笔进行中的借款', 'rsp_data' => ['url' => $bill_url]]);
        }
        //先花商城地址
        $xhshop_url_index = (new User())->getShopurl($oUser, 1, $category_id);
        $o_credit = (new User_credit())->getUserCreditByUserId($oUser->user_id);
        $credit_status = '未获取额度';
        if (!empty($o_credit)) {
            if ($o_credit->status == 2 && $o_credit->res_status == 2) {
                $credit_status = '审核不通过';
            }
            if ($o_credit->status == 2 && $o_credit->res_status == 1) {
                $credit_status = '已获取额度';
            }
            if ($o_credit->invalid_time < date('Y-m-d H:i:s')) {
                $credit_status = '额度失效';
            }
        }
        return json_encode(['rsp_code' => '0000', 'rsp_msg' => '前往先花商城', 'rsp_data' => ['url' => $xhshop_url_index, 'credit_status' => $credit_status]]);
    }

    //获取商城商品链接
    public function actionShopgoodsajax() {
        $oUser = $this->getUser();
        if (empty($oUser)) {
            return json_encode(['rsp_code' => '0001', 'rsp_msg' => '请登录!']);
        }
        //待支付订单
        $waitOrder = $this->getShoporder($oUser, 1);
        $xhshop_url = (new User())->getShopurl($oUser, 2);
        if ($waitOrder && !empty($waitOrder['rsp_data'])) {
            $time = empty($waitOrder['rsp_data']['time']) ? 0 : ($waitOrder['rsp_data']['time']['hour'] . ':' . $waitOrder['rsp_data']['time']['min'] . ':' . $waitOrder['rsp_data']['time']['sec']);
            return json_encode(['rsp_code' => '0003', 'rsp_msg' => '您有一笔待支付的订单', 'rsp_data' => ['url' => $xhshop_url, 'time' => $time]]);
        }
        if ($waitOrder && empty($waitOrder['rsp_data'])) { //数据异常
            return json_encode(['rsp_code' => '0005', 'rsp_msg' => $waitOrder['rsp_msg'], 'rsp_data' => '']);
        }
        //先花商城进行中订单
        $doingOrder = $this->getShoporder($oUser, 2);
        if ($doingOrder && !empty($doingOrder['rsp_data'])) {
            return json_encode(['rsp_code' => '0004', 'rsp_msg' => '您有一笔进行中的订单', 'rsp_data' => ['url' => $xhshop_url]]);
        }
        if ($doingOrder && empty($doingOrder['rsp_data'])) { //数据异常
            return json_encode(['rsp_code' => '0006', 'rsp_msg' => $doingOrder['rsp_msg'], 'rsp_data' => '']);
        }
        //商城分期订单
        $oGoodsOrder=(new Goods_order_terms())->isOrderlist($oUser->user_id);
        if(!empty($oGoodsOrder)){
            return json_encode(['rsp_code' => '0004', 'rsp_msg' => '您已有一笔进行中的订单', 'rsp_data' => ['url' => '/mall/store/goodsrecord']]);
        }

        $creditModel = new User_credit();
        $user_credit = $creditModel->checkYyyUserCredit($oUser->user_id);
        $oUserCredit =User_credit::find()->where(['user_id' => $oUser->user_id])->one();
        $user_credit_status=$user_credit['user_credit_status'];
        if($user_credit_status==3){
            return json_encode(['rsp_code' => '0007', 'rsp_msg' =>'您的额度正在快速获取中，请稍后...', 'rsp_data' => '']);
        }
        //进行中借款
        $loaning_result = $this->getLoaning($oUser);
        $bill_url = '/borrow/billlist/index'; //账单url
        if ($loaning_result) {
            return json_encode(['rsp_code' => '0002', 'rsp_msg' => '您有一笔进行中的借款', 'rsp_data' => ['url' => $bill_url]]);
        }

        if($user_credit_status==5){
            return json_encode(['rsp_code' => '0008', 'rsp_msg' => '您有一笔进行中的借款', 'rsp_data' => '']);
        }
        if($user_credit_status==4){//已测评可借未购买
            return json_encode(['rsp_code' => '0008', 'rsp_msg' => '您暂不可购买商品', 'rsp_data' => '']);
        }
        return json_encode(['rsp_code' => '0000', 'rsp_msg' => '前往商城商品']);
    }

    public function actionAjaxconfirmationold() {
        $user_id = $this->post('user_id');
        if (!$user_id) {
            echo $this->returnMsg('10001');
            exit();
        }
        $goods_info = $this->post();
        $goods_name = isset($goods_info['goods_name']) ? $goods_info['goods_name'] : $this->getCookieVal('goods_name');
        $goods_id = isset($goods_info['goods_id']) ? $goods_info['goods_id'] : $this->getCookieVal('goods_id');
        $goods_price = isset($goods_info['goods_price']) ? $goods_info['goods_price'] : $this->getCookieVal('goods_price');
        $colour = isset($goods_info['colour']) ? $goods_info['colour'] : $this->getCookieVal('colour');
        $bb = isset($goods_info['bb']) ? $goods_info['bb'] : $this->getCookieVal('bb');
        $pic_url = isset($goods_info['pic_url']) ? $goods_info['pic_url'] : $this->getCookieVal('pic_url');
        //把商品信息存到cookie里
        $this->setCookieVal('goods_name', $goods_name);
        $this->setCookieVal('goods_id', $goods_id);
        $this->setCookieVal('goods_price', $goods_price);
        $this->setCookieVal('bb', $bb);
        $this->setCookieVal('colour', $colour);
        $this->setCookieVal('pic_url', $pic_url);

        echo $this->returnMsg('0000');
    }
    public function actionConfirmationold() {
        $this->layout = "index";
        $this->getView()->title = "确认订单";
        $user = $this->getUser();
        $user_model = new User();
        $userInfo = $user_model->getUserinfoByUserId($user->user_id);
        $address_model = new Goods_address();
        $address_info = $address_model->getAddress($user->user_id);
        $attr = new Goods_attribute_value();

        $goods_name = $this->getCookieVal('goods_name');
        $goods_id = $this->getCookieVal('goods_id');
        $goods_price = $this->getCookieVal('goods_price');
        $colour = $this->getCookieVal('colour');
        $bb = $this->getCookieVal('bb');
        $pic_url = $this->getCookieVal('pic_url');
        $attr_info = $attr->getAttribute($goods_id);
        return $this->render('confirmationold', [
            'userInfo' => $userInfo,
            'goods_name' => $goods_name,
            'goods_id' => $goods_id,
            'goods_price' => $goods_price,
            'attr_info' => $attr_info,
            'address_info' => $address_info,
            'colour' => $colour,
            'bb' => $bb,
            'pic_url' => $pic_url,
            'csrf' => $this->getCsrf(),
        ]);
    }

    public function actionAjaxconfirmation() {
        $user_id = $this->post('user_id');
        $terms = $this->post('terms');
        $days = $this->post('days');
        $shuxing = $this->post('shuxing',"");
        if (!$user_id) {
            echo $this->returnMsg('10001');
            exit();
        }
        $userInfo = User::find()->where(['user_id'=>$user_id])->one();
        $jxl_result = false;
//        $jxl_result = (new Juxinli())->isAuthYunyingshang($user_id);
        //判断必填资料是否已全部完成
        $info = (new User())->getRequireData($userInfo);
//        echo json_encode($info);die;
        if($info['identify_valid']==2 && $info['contacts_valid']==2 && $info['pic_valid']==2 && $info['juxinli_valid']==2 ){
            $jxl_result = true;
        }
        if(!$jxl_result){
            echo $this->returnMsg('10002');
            exit();
        }
        //是否可评测

        $is_reject_timein = false;
        //是否24小时冷却期
        if(!empty($user_id)){
            $creditRes = $this->getCanloan($user_id,"",1,2);
//            print_r($creditRes);die;
            if(!empty($creditRes)){
                $creInfo = json_decode($creditRes,true);
                if(isset($creInfo['rsp_code']) && $creInfo['rsp_code'] == "0000"){
                    $is_reject_timein = true;
                }
            }
        }
        if(!$is_reject_timein){
            echo $this->returnMsg('10003');
            exit();
        }
        $goods_info = $this->post();
        $goods_name = isset($goods_info['goods_name']) ? $goods_info['goods_name'] : $this->getCookieVal('goods_name');
        $goods_id = isset($goods_info['goods_id']) ? $goods_info['goods_id'] : $this->getCookieVal('goods_id');
        $goods_price = isset($goods_info['goods_price']) ? $goods_info['goods_price'] : $this->getCookieVal('goods_price');
        $colour = isset($goods_info['colour']) ? $goods_info['colour'] : $this->getCookieVal('colour');
        $bb = isset($goods_info['bb']) ? $goods_info['bb'] : $this->getCookieVal('bb');
        $pic_url = isset($goods_info['pic_url']) ? $goods_info['pic_url'] : $this->getCookieVal('pic_url');
        //把商品信息存到cookie里
        $this->setCookieVal('goods_name', $goods_name);
        $this->setCookieVal('goods_id', $goods_id);
        $this->setCookieVal('goods_price', $goods_price);
        $this->setCookieVal('bb', $bb);
        $this->setCookieVal('colour', $colour);
        $this->setCookieVal('pic_url', $pic_url);
        $this->setCookieVal('terms', $terms);
        $this->setCookieVal('days', $days);
        $this->setCookieVal('shuxing', $shuxing);

        echo $this->returnMsg('0000');
    }

    public function actionConfirmation() {
        $this->layout = "index";
        $this->getView()->title = "确认订单";
        $user = $this->getUser();
        $user_model = new User();
        $userInfo = $user_model->getUserinfoByUserId($user->user_id);
        $address_model = new Goods_address();
        $address_info = $address_model->getAddress($user->user_id);
        $attr = new Goods_attribute_value();

        $goods_name = $this->getCookieVal('goods_name');
        $goods_id = $this->getCookieVal('goods_id');
        $goods_price = $this->getCookieVal('goods_price');
        $colour = $this->getCookieVal('colour');
        $bb = $this->getCookieVal('bb');
        $pic_url = $this->getCookieVal('pic_url');
        $terms = $this->getCookieVal('terms');
        $days = $this->getCookieVal('days');
        $attr_info = $attr->getAttribute($goods_id);
        $shuxing = $this->getCookieVal('shuxing');
        $goodtermModel = (New Goods_order_terms())->ByTerms($goods_price,$days,$terms,date('Y-m-d H:i:s'));
//        print_r($goodtermModel);die;
        return $this->render('confirmation', [
                    'userInfo' => $userInfo,
                    'goods_name' => $goods_name,
                    'goods_id' => $goods_id,
                    'goods_price' => $goods_price,
                    'attr_info' => $attr_info,
                    'shuxing' => $shuxing,
                    'address_info' => $address_info,
                    'colour' => $colour,
                    'bb' => $bb,
                    'pic_url' => $pic_url,
                    'terms' => $terms,
                    'days' => $days,
                    'csrf' => $this->getCsrf(),
                    'goodtermModel' => $goodtermModel,
        ]);
    }

    public function getCanloan($user_id,$black_box="",$type=1,$get_type = 1) {
        if(empty($user_id)){
            return json_encode(['rsp_code' => '99994']);
            exit();
        }
        $userInfo = User::findOne($user_id);
        $jxl_result = false;
//        $jxl_result = (new Juxinli())->isAuthYunyingshang($user_id);
        //判断必填资料是否已全部完成
        $info = (new User())->getRequireData($userInfo);
        if($info['identify_valid']==2 && $info['contacts_valid']==2 && $info['pic_valid']==2 && $info['juxinli_valid']==2 ){
            $jxl_result = true;
        }
        if(!$jxl_result){
            //必填资料不完整
            return json_encode(['rsp_code' => '00001']);
            exit();
        }
        if($type == 2){
            //跳过信用卡
            $oCard = ScanTimes::find()->where(['mobile' => $userInfo->mobile, 'type' => 24])->one();
            if (empty($oCard)) {
                $sacnTimesModel = new ScanTimes();
                $sacnTimesModel->save_scan(['mobile' => $userInfo->mobile, 'type' => 24]);
            }
        }else{
            $oUserbank = User_bank::find()->where(['user_id' => $user_id, 'status' => 1, 'type' => 1])->one();
            $oCard = ScanTimes::find()->where(['mobile' => $userInfo->mobile, 'type' => 24])->one();
            if ( empty($oUserbank) && empty($oCard) ) { //必填资料未完善或者信用卡未跳过且未绑定
                return json_encode(['rsp_code' => '00002']);
                exit();
            }
        }

        //判断一亿元的测评状态
        $creditModel = new User_credit();
        $oUserCredit = $creditModel->getUserCreditByUserId($user_id);
        $user_credit = (new User_credit())->checkYyyUserCredit($user_id);

        //修改资料(true:已完善 false:未完善)
        $UserCreditByTimeRes = (new User_loan())->getUserCreditByTime($userInfo->user_id, $oUserCredit['invalid_time']);
        //是否在24小时之内 (true:超过24小时 false:未超过)
        $is_or_time = $this->getIsortime($oUserCredit['invalid_time']);
        if($oUserCredit['status'] == 2 && $oUserCredit['res_status'] == 2 && !$is_or_time && !$UserCreditByTimeRes){
            return json_encode(['rsp_code' => '1000','rsp_msg' => '冻结期','is_change' => 0]);
        }
        //判断是否允许评测
        $CreditTime = $oUserCredit['last_modify_time'];
        $user_credit_status = $user_credit['user_credit_status'];
        $loan_id = empty($oUserCredit->loan_id) ? '' : $oUserCredit->loan_id;
        if ($user_credit_status == 3) {
            return json_encode(['rsp_code' => '1000','rsp_msg' => '很抱歉，额度正在获取中，请10分钟后重试','is_change' => 0]);
            exit();
        }
        if ($user_credit_status == 1) {
            $repeatNum = (new User_loan())->isRepeatUser($user_id);
            if ($repeatNum == 0) {
                $oUserRejectLoan = (new User_loan())->getLastRejectLoan($user_id);
                if (!empty($oUserRejectLoan)) {
                    $CreditTime = $oUserRejectLoan->last_modify_time;//如果他是借款被驳回时间
                }
            }
        }

        if($user_credit_status == 6  ){
            $shop_res = (new User_credit())->getshopOrder($userInfo);
            if(!$shop_res){
                return json_encode(['rsp_code' => '1001','rsp_msg' => '您已有一笔商城订单，暂不可发起','is_change' => 0]);
                exit();
            }
        }
        //判断评测有效期内智融钥匙是否有 购卡记录
//        print_r($user_credit_status);die;
        if($user_credit_status!=6  && !empty($oUserCredit) && $oUserCredit->pay_status==1){
            $zrys_credit_result = [
                'rsp_code' => '1002',
                'rsp_msg' => '很抱歉，你有一笔进行中的借款',
                'is_change' => 0,
            ];
            return json_encode($zrys_credit_result);
            exit();
        }

//        $yyyCredit = $creditModel->getYyyCredit($oUserCredit);
        if (!empty($CreditTime) && ($user_credit_status != 6) ) {
            $fillIn = (new User_credit())->chkCreditByMaterial($user_id, $CreditTime);
            $result = (new User_credit())->chkCredit($fillIn, $user_id, $loan_id, $user_credit_status);
            if ($result === false) {
                return json_encode(['rsp_code' => '1003','rsp_msg' => '很抱歉，额度获取失败请10分钟后重试.','is_change' => 0]);
                exit();
            }
            $shopCredit=(new User_credit())->getShopCredit($oUserCredit);
            if(!empty($oUserCredit) && $oUserCredit->status == 2 && $oUserCredit->res_status == 1  && !$shopCredit){
                //向智融钥匙推送失效信息
                return json_encode(['rsp_code' => '1004','rsp_msg' => '已有进行中的评测']);
                exit();
            }
        }
        //判断存在未完成的借款&&借款不是'INIT', 'TB-AUTHED', 'TB-SUCCESS'
        $userLoanId = (new User_loan())->getHaveinLoan($user_id,$business_type = [1,4,5,6,9,10]);
        if (!empty($userLoanId)) {
            $oExtend = (new User_loan_extend())->checkUserLoanExtend($userLoanId);
            if (!empty($oExtend) && !in_array($oExtend->status, ['INIT', 'TB-AUTHED', 'TB-SUCCESS'])) {
                return json_encode(['rsp_code' => '1004','rsp_msg' => '很抱歉，额度获取失败请10分钟后重试!','is_change' => 0]);
                exit();
            }
        }
        if($get_type == 2){
            return json_encode(['rsp_code' => '0000','rsp_msg' => '']);
            exit();
        }

        $oJuXinLi = (new Juxinli())->getJuxinliByUserId($userInfo->user_id);
        $yyy_credit = $this->getYyyCredit($oJuXinLi, $userInfo);
        $credit_data_result = $this->yyyAddOrUpdateCredit($yyy_credit, $userInfo->user_id, $oUserCredit, $black_box);
        $oUserCreditnew = $creditModel->getUserCreditByUserId($user_id);
        if ($credit_data_result) { //请求评测成功并且新增或修改评测记录成功
            $list_result = (new UserCreditList())->synchro($oUserCreditnew['req_id']);//credit_list添加一条记录
            if (empty($list_result)) {
                Logger::dayLog('store/getcanloan', '评测表记录失败', $result['res_data']['strategy_req_id'], $list_result);
            }
            $newCreditListInfo = UserCreditList::find()->where(['req_id'=>$oUserCreditnew['req_id']])->orderBy('id DESC')->one();
            $listId = "";
            if(!empty($newCreditListInfo)){
                $listId = $newCreditListInfo->id;
            }
            $source_result = (new User())->getShopRedisResult('shop_info_',$userInfo,2); //判断是否跳回先花商城
            if($source_result){
                return json_encode(['rsp_code' => '0000','rsp_msg' => '','is_change' => 2,'shop_url'=>$source_result,'shop_mark'=>1,'listId'=>$listId]);
                exit();
            }
            return json_encode(['rsp_code' => '0000','rsp_msg' => '','is_change' => 2,'listId'=>$listId]);
            exit();
        } else {
            Logger::dayLog('store/getcanloan', '一亿元请求评测结果失败:', $userInfo->user_id, $yyy_credit);
            return json_encode(['rsp_code' => '1000','rsp_msg' => '很抱歉，额度获取失败请10分钟后重试','is_change' => 0]);
        }
    }

    private function getIsortime($last_times) {

        $last_time = strtotime($last_times);
        //超过24小时
        if (date('Y-m-d H:i:s', $last_time) < date('Y-m-d H:i:s')) {
            return true;
        }
        return false;
    }

    /**
     * 向有信令推送失效
     * @param  [type] $req_id [description]
     * @param  [type] $source [description]
     * @return [type]         [description]
     */
    private function getZrysres($req_id,$source){
        if(in_array($source,[1,3])){
            $source = 1;
        }
        $contacts = [
            'req_id' => $req_id,
            'source' => $source,
            'status' => 2,
        ];

        $api = new Apihttp();
        $result = $api->postSignal($contacts,4);
        if(!empty($result['rsp_code']) && $result['rsp_code'] == '0000'){
            return true;
        }
        Logger::dayLog('signal/signalpush', '有信令推送失败', 'req_id：' . $req_id, $contacts, $result);
        return false;

    }
    /**
     * 一亿元发起评测
     * @param type $apiHttp
     * @param type $oJuXinLi
     * @param type $userInfo
     * @return type
     */
    public function getYyyCredit($oJuXinLi, $userInfo) {
        $parms = [
            'aid' => 1,
            'req_id' => $oJuXinLi->requestid,
            'user_id' => $userInfo->user_id,
            'callbackurl' => Yii::$app->request->hostInfo . '/new/notifycredit',
        ];
        $yyy_credit = json_decode((new Apihttp())->postCredit($parms));

        return $yyy_credit;
    }

    public function actionOrdersuccess(){
        $this->getView()->title = "处理中";
        $orderId = $this->get("order_id","");
        return $this->render('ordersuccess', ["order_id" => $orderId]);
    }
    public function yyyAddOrUpdateCredit($yyy_credit, $user_id, $oUserCredit, $black_box) {

        if ($yyy_credit->res_code === 0 && !empty($yyy_credit->res_data->strategy_req_id)) {
            //从未评测过
            if (empty($oUserCredit)) {
                $data = [
                    'user_id' => $user_id,
                    'req_id' => $yyy_credit->res_data->strategy_req_id,
                    'status' => 1,
                    'source'=>4,
                    'pay_status'=>0,
                    'black_box' =>$black_box,
                    // 'uuid' => $uuid,
                    //'device_tokens' => $deviceTokens,
                    'device_type' => 1, //1:微信公众号
                    'device_ip' => Common::get_client_ip(),
                ];
                $creditResult = (new User_credit())->addUserCredit($data);
                if (empty($creditResult)) {
                    Logger::dayLog('weixin/store/yyyAddOrUpdateCredit', '一亿元评测表记录新增失败', $data, $creditResult);
                    return false;
                }
                return true;
            }

            //评测过
            $creditArray = [
                'req_id' => $yyy_credit->res_data->strategy_req_id,
                'loan_id' => '',
                'source'=>4,
                'pay_status'=>0,
                'black_box' =>$black_box,
                'device_type' => 1,
                'device_ip' => Common::get_client_ip(),
            ];
            $creditResult = $oUserCredit->updateInit($creditArray);
            if (empty($creditResult)) {
                Logger::dayLog('weixin/borrow/yyyAddOrUpdateCredit', '一亿元评测表记录更新失败', $yyy_credit->res_data->strategy_req_id, $creditResult);
                return false;
            }
            return true;
        }
        return false;
    }

    public function actionEditaddress() {
        $this->layout = "index";
        $this->getView()->title = "编辑地址";
        $address_id = $this->get('address_id');
        $user_id = $this->get('user_id');
        $address = new Goods_address();
        $address_info = $address->getAddressById($address_id);
        $list = Areas::getAllAreas();
        $address_code = (new Areas)->getProCityArea($address_info['area_code']);
        $list = json_encode(array_merge(array($this->defaultArea()), json_decode($list, TRUE)));
        return $this->render('editaddress', [
                    'user_id' => $user_id,
                    'address_info' => $address_info,
                    'address_code' => $address_code,
                    'list' => $list,
                    'csrf' => $this->getCsrf(),
        ]);
    }

    public function actionAddressajax() {
        $post_data = $this->post();
        //验证post提交的数据
        if (empty($post_data['district'])) {
            return $this->showMessage(1, "请选择相关信息");
        }
        if (empty($post_data['address']) || empty($post_data['user_name'])) {
            return $this->showMessage(2, "请完整输入信息");
        }
        $phone_chk = $this->chkPhone($post_data['mobile']);
        if (!$phone_chk) {
            return $this->showMessage(3, '请输入正确的单位电话');
        }
        $user_info = User::find()->where(['user_id' => $post_data['user_id']])->one();
        if (!$user_info) {
            return $this->showMessage(4, '未找到对应用户信息');
        }
        $address = new Goods_address();
        $address_info = $address->getAddressById($post_data['address_id']);
        if (!empty($address_info)) {
            $set_address = $address_info->editAddress($post_data);
        } else {
            $set_address = $address->setAddress($post_data);
        }
        if (!$set_address) {
            return $this->showMessage(5, $set_address);
        }
        $address_flows = new Goods_address_flows();
        $set_address_flows = $address_flows->setAddressflows($post_data);
        return $this->showMessage(0, $post_data['mobile']);
    }

    public function actionPreorder() {
        $post_data = $this->post();
        if (empty($post_data) || empty($post_data['goods_price']) || empty($post_data['goods_name']) || empty($post_data['pic_url']) || empty($post_data['a_id']) || empty($post_data['goods_id']) || empty($post_data['user_id'])) {
            return $this->showMessage(2, '参数不能为空');
        }
        $data = [
            'money' => $post_data['goods_price'],
            'colour' => $post_data['colour'],
            'edition' => $post_data['bb'],
            'goods_name' => $post_data['goods_name'],
            'pic_url' => $post_data['pic_url']
        ];
        $post_data['description'] = json_encode($data);
        $mallOrderModel = new MallOrder();
        $order_id = date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        $data = [
            'order_id' => $order_id,
            'money' => $post_data['goods_price'],
            'goods_content' => $post_data['description'],
            'user_id' => $post_data['user_id'],
            'goods_id' => $post_data['goods_id'],
            'a_id' => $post_data['a_id'],
        ];
        $res = $mallOrderModel->addOrder($data);
        if (!$res) {
            return $this->showMessage(1, '生成订单失败');
        }
        $addRess = Goods_address::findOne($data['a_id']);
        if (!$addRess) {
            return $this->showMessage(3, '保存地址失败');
        }
        $condition = [
            'user_id' => $post_data['user_id'],
            'order_id' => $order_id,
            'receive_name' => !empty($addRess->receive_name) ? $addRess->receive_name : '',
            'receive_mobile' => !empty($addRess->receive_mobile) ? $addRess->receive_mobile : '',
            'area_code' => !empty($addRess->area_code) ? $addRess->area_code : '',
            'address_detail' => !empty($addRess->address_detail) ? $addRess->address_detail : '',
        ];
        $orderAddrModel = new MallOrderAddress();
        $res = $orderAddrModel->setAddress($condition);
        if (!$res) {
            return $this->showMessage(3, '保存地址失败');
        }

        return $this->showMessage(0, $order_id);
    }


    public function actionPreorderterm() {
        $post_data = $this->post();
        $data = [
            'money' => $post_data['goods_price'],
            'colour' => $post_data['colour'],
            'bb' => $post_data['bb'],
            'edition' => $post_data['bb'],
            'goods_name' => $post_data['goods_name'],
            'pic_url' => $post_data['pic_url'],
        ];
        //发起评测
        $creditRes = $this->getCanloan($post_data['user_id'],$post_data['black_box']);
        if(!empty($creditRes)){
            $creInfo = json_decode($creditRes,true);
            if(isset($creInfo['rsp_code']) && $creInfo['rsp_code'] != "0000"){
                return $creditRes;
            }
        }
        $listId = $creInfo['listId'];
        $post_data['description'] = json_encode($data);
        $post_data['listId'] = $listId;
        $goodsListModel = new Goods_list();
        $goods_info = $goodsListModel->getGoodsById($post_data['goods_id']);
        $goods_order = new Goods_order_terms();
        $res = $goods_order->addOrder($goods_info, $post_data);
        if (!$res) {
            return $this->showMessage(1, '生成订单失败');
        }
        return $this->showMessage(0, $res);
    }
    public function actionOrderdetailsold() {
        $this->layout = "index";
        $this->getView()->title = "订单详情";
        $order_id = $this->get('order_id');
        $goodsOrderModel = new MallOrder();
        $orderInfo = $goodsOrderModel->getGoodsOrderByOrderId($order_id);
        if (!$orderInfo && empty($orderInfo->address)) {
            exit('收货地址数据不全');
        }
        $orderInfo->address->address_detail = $this->getAddDetail($orderInfo->address->address_detail, $orderInfo->address->area_code);
        $goodsContentJson = $orderInfo->goods_content;
        $goodsContent = json_decode($goodsContentJson, TRUE);
        $user_info = $this->getUser();
        $bankModel = new User_bank();
        $bank_count = User_bank::find()->where(['user_id' => $user_info->user_id, 'status' => 1])->count();
        return $this->render('orderdetailsold', [
            'orderInfo' => $orderInfo,
            'goodsContent' => $goodsContent,
            'bank_count' => $bank_count,
            '_csrf' => $this->getCsrf(),
        ]);
    }
    public function actionOrderdetails() {
        $this->getView()->title = "订单详情";
        $this->isApp();
        $order_id = $this->get('order_id');
        $goodsOrderModel = new Goods_order_terms();
        $orderInfo = $goodsOrderModel->getGoodsOrderByOrderId($order_id);
        if (!$orderInfo && empty($orderInfo->address)) {
            exit('收货地址数据不全');
        }
        $orderInfo->address->address_detail = $this->getAddDetail($orderInfo->address->address_detail, $orderInfo->address->area_code);
        $user_credit_data=(new User_credit())->checkYyyUserCredit($orderInfo->user_id);
        $terms_data=(new Goods_order_terms())->ByTerms($orderInfo->money,$orderInfo->term_days,$orderInfo->terms,$orderInfo->create_time);
//        var_dump($terms_data);die;
        $goodsContentJson = $orderInfo->goods_content;
        $goodsContent = json_decode($goodsContentJson, true);
        return $this->render('orderdetails', [
            'orderInfo' => $orderInfo,
            'user_credit_data'=>$user_credit_data,
            'goodsContent' => $goodsContent,
            'terms_data'=>$terms_data,
            'is_goloan'=>$this->is_goloan,
            'is_app' => $this->is_app,
        ]);
    }
    public function actionGetadd() {
        $postData = $this->post();
        if (empty($postData) || !is_array($postData) || empty($postData['orderId'])) {
            return $this->showMessage(1, '参数错误');
        }
        $orderModel = (new MallOrder())->getGoodsOrderByOrderId($postData['orderId']);
        $res = $orderModel->over();
        if (!$res) {
            return $this->showMessage(1, '确认收货失败');
        }
        return $this->showMessage(0, '成功');
    }

    public function actionRepaychoose() {
        $this->layout = '_data';
        $this->getView()->title = "支付";
        $user = $this->getUser();
        $userInfo = User::findOne($user->user_id);
        $orderId = $this->get('order_id');
        if (!$orderId) {
            exit('参数错误');
        }
        $orderModel = (new MallOrder())->getGoodsOrderByOrderId($orderId);
        if (!$orderModel) {
            exit('参数错误');
        }
        $amount = $orderModel->money;

        $bankModel = new User_bank();
        $bank_count = User_bank::find()->where(['user_id' => $userInfo->user_id, 'status' => 1])->count();
        $banklist = $bankModel->limitCardsSort($userInfo->user_id, 1);
        $bank_str = Common::ArrayToString($banklist, 'sign');
        $bank_arr = explode(',', $bank_str);
        if (!in_array('2', $bank_arr)) {
            //无可用卡
            $flag = 2;
        } else {
            $flag = 1;
        }
        return $this->render('repaychoose', [
                    'flag' => $flag,
                    'banklist' => $banklist,
                    'bank_count' => $bank_count,
                    'amount' => $amount,
                    'orderId' => $orderId,
                    'csrf' => $this->getCsrf(),
        ]);
    }

    public function actionPayyibao() {
        $post_data = $this->post();
        $orderId = $post_data['orderId'];
        if (!$orderId) {
            return $this->showMessage(1, '数据错误');
        }
        $orderinfo = MallOrder::find()->where(['order_id' => $orderId])->one();
        if (!$orderId) {
            return $this->showMessage(1, '该笔账单不存在');
        }
        $user = User::findOne($orderinfo['user_id']);
        $user_id = $user->user_id;
        $money = isset($post_data['money']) ? floatval($post_data['money']) * 100 : '';
        $bank_id = $post_data['bank_id'];
        $bank = User_bank::findOne($bank_id);
        $platform = 17;

        $loan_repay = new MallOrderPay();
        $reqID = 'M' . date('mdHis') . $user->user_id;
        $condition = [
            'req_id' => $reqID,
            'user_id' => $user_id,
            'm_id' => $orderId,
            'source' => 1,
            'loan_id' => '',
            'bank_id' => $bank_id,
            'money' => isset($money) ? floatval($money) : '',
            'platform' => $platform,
        ];
        Logger::errorLog(print_r($condition, TRUE), 'zhifuMall');
        $ret = $loan_repay->save_repay($condition);
        if (!$ret) {
            if (!$orderId) {
                return $this->showMessage(1, '网络错误');
            }
        }
        $card_type = ($bank->type == 0) ? 1 : 2;
        $phone = isset($bank->bank_mobile) ? $bank->bank_mobile : $user->mobile;

        $postData = array(
            'orderid' => $reqID, // 请求唯一号
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
            'orderexpdate' => 60,
            'business_code' => 'YYYWX',
            'userip' => $_SERVER["REMOTE_ADDR"], //ip
            'callbackurl' => Yii::$app->params['mall_notify_url'], // 异步回调地址
        );
        Logger::errorLog(print_r($postData, TRUE), 'mallPay');
        $openApi = new ApiClientCrypt;
        $res = $openApi->sent('payroute/pay', $postData, 2);
        $result = $openApi->parseResponse($res);
        Logger::errorLog(print_r($result, TRUE), 'mallPayR');
        if ($result['res_code'] != 0 || !isset($result['res_data']['url']) || empty($result['res_data']['url'])) {
            return $this->showMessage(1, '支付失败');
        }
        $redirect_url = (string) $result['res_data']['url'];
        return $this->showMessage(0, $redirect_url);
    }

    public function actionAjaxpage() {
        $type = $this->get('type');
        if (!$type) {
            echo $this->returnMsg(10001);
            exit();
        }
        $goodsList = (new Goods_list())->find()->where(['cid' => $type]);
        $pages = new Pagination(['totalCount' => $goodsList->count(), 'pageSize' => 30]);
        $model = $goodsList->offset($pages->offset)->limit($pages->limit)->orderBy('create_time desc')->asArray()->all();
        foreach ($model as $k => $v) {
            $picUrl = (new Goods_pic())->getPicUrlByGid($v['id']);
            $model[$k]['pic_url'] = ImageHandler::getUrl($picUrl);
        }
        echo $this->returnMsg('0000', ['page' => $this->get('page'), 'list' => $model]);
    }

    public function actionGoodsrecord() {
        $this->layout = "index";
        $this->getView()->title = "我的订单";
        $user_info = $this->getUser();
        if (!$user_info) {
            exit('用户信息有误!');
        }
        //获取订单记录的商品信息
        $goods_list = (new Goods_order_terms())->getGoodsListByUserId($user_info->user_id);
        Logger::dayLog('mallindex', $user_info->user_id);
        foreach ($goods_list as $k => $v) {
            $goodsContentJson = $v['goods_content'];
            $goodsContent = json_decode($goodsContentJson, true);
            $goods_list[$k]['goods_money'] = $goodsContent['money'];
            $goods_list[$k]['colour'] = $goodsContent['colour'];
            $goods_list[$k]['goods_name'] = $goodsContent['goods_name'];
            $goods_list[$k]['pic_url'] = ImageHandler::getUrl($goodsContent['pic_url']);
            $goods_list[$k]['credit_status'] =(new Goods_order_terms())->OrderlistStatus($v['order_id'])['user_credit_status'];
        }
//        var_dump($goods_list);die;
        return $this->render('goodsrecord', [
            'goods_list' => $goods_list
        ]);

    }

    //未登陆
    private function viewHomeNoLogin() {
        $m_category = new Goods_category_list();
        $all_goods_types = $m_category->getAllCategory(10); //获取全部商品分类
        $tj_goods_types = $m_category->getTjCategory(10); //获取推荐商品分类
        return $this->render('home_no_login', [
                    'is_trial' => $this->is_trial,
                    'is_white' => $this->is_white,
                    'is_app' => $this->is_app,
                    'is_zhirongyaoshi' => $this->is_zhirongyaoshi,
                    'is_android' => $this->is_android,
                    'user_id' => $this->user_id,
                    'user_id_encryption' => $this->user_id_encryption,
                    'mobile' => !empty($this->o_user) ? $this->o_user->mobile : '',
                    'all_goods_types' => $all_goods_types,
                    'tj_goods_types' => $tj_goods_types,
        ]);
    }

    //登陆&过审中&白名单&ios&安卓
    private function viewHomeTrial() {
        $m_category = new Goods_category_list();
        $all_goods_types = $m_category->getAllCategory(10); //获取全部商品分类
        $tj_goods_types = $m_category->getTjCategory(10); //获取推荐商品分类
        return $this->render('home_trial', [
                    'is_app' => $this->is_app,
                    'user_id' => $this->o_user->user_id,
                    'user_id_encryption' => $this->user_id_encryption,
                    'all_goods_types' => $all_goods_types,
                    'tj_goods_types' => $tj_goods_types,
        ]);
    }

    private function viewHome() {
        $oUser=$this->getUser();
//        $token='';
//        if(!empty($oUser)){
//            $token=$this->makeTokenData($oUser->user_id);
//        }
        $shop_switch = $this->isWork(); //先花商城白名单
        if ($shop_switch) {
            $all_goods_types = (new Goods_category_list())->listCategory(0, [1, 2], [1, 2, 3], [0, 1], 10);
        } else {
            $all_goods_types = (new Goods_category_list())->listCategory(0, 1, 1, [0, 1], 10);
        }
        $tj_goods_types = (new Goods_category_list())->getTjCategory(10);

        //首次引导借款弹窗
        $popup_loan = FALSE;
        $sacnTimesModel = new ScanTimes();
        $result = $sacnTimesModel->getByMobileType($this->o_user->mobile, 22);
        if (empty($result)) {
            $popup_loan = TRUE;
            $sacnTimesModel->save_scan(['mobile' => $this->o_user->mobile, 'type' => 22]);
        }

        //轮播活动弹窗
        $popup_slider = FALSE;
        $tanchuan_data = Mall_banner::find()->where(['type' => 1, 'status' => 1])->orderBy('product_position asc,id desc')->all();
        if (!empty($tanchuan_data)) {
            $getRedis = $this->getRedis('alert' . $this->o_user->mobile);
            if (empty($getRedis)) {
                $this->setNotRedis('alert' . $this->o_user->mobile, time());
                $popup_slider = TRUE;
            } else if (!empty($getRedis) && strtotime(date('Y-m-d'), time()) > $getRedis) {
                $this->delRedis('alert' . $this->o_user->mobile);
                $this->setNotRedis('alert' . $this->o_user->mobile, time());
                $popup_slider = TRUE;
            }
        }

        //是否存在待还账单
        $has_loan_repay = FALSE;
        $has_ious_repay = FALSE;
        $o_user_loan = (new User_loan())->getLoan($this->user_id, [9, 11, 12, 13], [1, 4, 5, 6]);
        if (((!empty($o_user_loan) && !empty($o_user_loan->loanextend) && $o_user_loan->loanextend->status == 'SUCCESS') || (!empty($o_user_loan) && $o_user_loan->settle_type == 3))) {
            $has_loan_repay = TRUE;
        }
        $iousResult = (new Apihttp())->getUseriousinfo(['mobile' => $this->o_user->mobile]);
        if (empty($iousResult)) {
            Logger::dayLog('app/getUseriousinfo', '获取用户白条信息失败', $this->o_user->user_id, $iousResult);
        } elseif (!empty($iousResult)) {
            $has_ious_repay = TRUE;
        }
        
        return $this->render('home', [
                    'is_trial' => $this->is_trial,
                    'is_white' => $this->is_white,
                    'is_app' => $this->is_app,
                    'is_android' => $this->is_android,
                    'is_zhirongyaoshi' => $this->is_zhirongyaoshi,
                    'user_id' => $this->user_id,
                    'user_id_encryption' => $this->user_id_encryption,
                    'mobile' => !empty($this->o_user) ? $this->o_user->mobile : '',
                    'popup_loan' => $popup_loan, //首次登陆引导借款弹窗
                    'popup_slider' => $popup_slider, //轮播活动banner弹窗
                    'tanchuan_data' => $tanchuan_data, //轮播活动banner数据
                    'all_goods_types' => $all_goods_types, //全部商品分类
                    'tj_goods_types' => $tj_goods_types, //推荐商品分类
                    'has_loan_repay' => $has_loan_repay, //存在待还借款
                    'has_ious_repay' => $has_ious_repay, //存在待还白条
                    'csrf' => $this->getCsrf(),
        ]);
    }

    /**
     * 检查开关
     * true:进场
     * false:离场
     * @return bool
     */
    private function isInspectOpen() {
        $is_isInspect_open = Keywords::inspectOpen();
        if ($is_isInspect_open == 2) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 当前是否是过审中版本
     * true:是过审中
     * false:不是过审中
     */
    private function isTrial() {
        $trial = strpos($_SERVER['HTTP_USER_AGENT'], $this->trial);
        $this->is_trial = FALSE;
        if ($trial !== FALSE) {
            $this->is_trial = TRUE;
        }
    }

    /**
     * 是否是白名单用户
     * true:是白名单用户
     * false:不是白名单用户
     */
    private function isWhite() {
        $list = Keywords::listWhiteList();
        $this->is_white = FALSE;
        if (!empty($this->o_user)) {
            $o_user = User::find()->where(['user_id' => $this->o_user->user_id])->one();
            if (!empty($o_user)) {
                $mobile = $o_user->mobile;
                if (in_array($mobile, $list)) {
                    $this->is_white = TRUE;
                }
            }
        }
    }

    /**
     * 是否是工作人员
     * true:是工作人员
     * false:不是工作人员
     * @return bool
     */
    private function isWork() {
        $list = [
            '15120083740',
            '17600664664',
            '15011091226',
            '18401629347',
            '18600578542',
            '15011284013',
            '18905400433',
            '17052088877'
        ];
        $is_work = FALSE;
        if (!empty($this->o_user) && in_array($this->o_user->mobile, $list)) {
            $is_work = TRUE;
        }
        return $is_work;
    }

    /**
     * 是否是安卓端
     * true:是安卓
     * false:不是安卓
     */
    private function isAndroid() {
        $android = strpos($_SERVER['HTTP_USER_AGENT'], 'Android');
        $this->is_android = FALSE;
        if ($android !== FALSE) {
            $this->is_android = TRUE;
        }
    }

    /**
     * 是否是app端
     * true:是app端
     * false:不是app端
     */
    private function isApp() {
        $this->is_app = FALSE;
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')) {
            $this->is_app = TRUE;
        }
        Logger::dayLog('store', $this->is_app, $_SERVER);
    }

    /**
     * 贷超开关
     * true:开启
     * false:关闭
     */
    private function isZhirongyaoshi() {
        $this->is_zhirongyaoshi = FALSE;
        $supermarketOpen = Keywords::supermarketOpen();
        if ($supermarketOpen == 1) {
            $this->is_zhirongyaoshi = TRUE;
        }
    }

    /**
     * 判断先花商城的订单状态
     * @param $user
     * @param $type 1:待支付订单 2：进行中订单
     * @return bool
     */
    private function getShoporder($user, $type) {
        $apiHttp = new Apihttp();
        $payResult = $apiHttp->getShoporder(['mobile' => $user->mobile, 'type' => $type, 'source' => 1]);
        if ($payResult['rsp_code'] == '0000' && !empty($payResult['rsp_data']) && $payResult['rsp_data']['status'] == 1) {
            return $payResult;
        } elseif ($payResult['rsp_code'] == '0004') {  //或数据异常
            return $payResult;
        }
        return FALSE;
    }

    /**
     * 查询一亿元进行中的借款
     * @param type $user
     * @return boolean
     */
    private function getLoaning($user) {
        //智融钥匙
        $iousResult = (new Apihttp())->getUseriousinfo(['mobile' => $user->mobile]);
        //一亿元
        $o_user_loan = (new User_loan())->getHaveinLoan($user->user_id, [1, 4, 5, 6, 9]);
        $is_loaning = FALSE;
        if (!empty($iousResult)) {
            $is_loaning = TRUE;
        }
        if (!empty($o_user_loan)) {
            $is_loaning = TRUE;
        }
        return $is_loaning;
    }

    /**
     * 计算分期金额
     * @param $user_id
     * @param $goods_price
     * @param $terms
     * @return string
     */
    private function termMoney($goods_price, $terms) {
        $term_money = [];
        foreach ($terms as $v) {
            $interest = 0.00098;
            $money = ceil(($goods_price * 28 * $interest * $v + $goods_price) / $v * 100) / 100;
            $term_money[$v] = sprintf("%01.2f", $money);
        }
        return $term_money;
    }

    //普通详情页
    private function doDetail() {
        $this->layout = "index";
        $this->getView()->title = '商品详情';
        $gid = $this->get('gid');
        if (!empty($this->user_id)) {
            $user = $this->getUser();
            $haveLoan = (new User_loan)->getHaveinLoan($user->user_id);
            $haveOrder = (new Goods_order_terms())->getHaveinOrder($user->user_id);
        } else {
            $haveLoan = FALSE;
            $haveOrder = FALSE;
        }
        $goods_info = Goods_list::find()->where(['id' => $gid])->one();
        $goods_pic = new Goods_list();
        $goods_pics = $goods_pic->getSlpic($gid);
        $goods_x = $goods_pic->getXpic($gid);
        $goods_d = $goods_pic->getDpic($gid);
        //计算单期金额
        $terms = [3, 6, 9, 12];
        $term_money = $this->termMoney($goods_info->goods_price, $terms);
        $attr = new Goods_attribute_value();
        $attr_info = $attr->getAttribute($gid);
        $attr_info_va = [];
        foreach ($attr_info as $k => $v) {
            $attr_info_va[$v->attr->attribute][] = $v->value;
        }
        $user = $this->getUser();
        $user_id = "";
        if (!empty($user)) {
            $user_id = $this->user_id;
        }
        return $this->render('detail', [
                    'goods_info' => $goods_info,
                    'goods_pics' => $goods_pics,
                    'goods_x' => $goods_x,
                    'goods_d' => $goods_d,
                    'attr_info' => $attr_info_va,
                    'term_money' => $term_money,
                    'haveLoan' => $haveLoan ? 1 : 2, //1有借款 2：无借款
                    'haveOrder' => $haveOrder ? 1 : 2, //1有订单 2：无订单
                    'csrf' => $this->getCsrf(),
                    'user_id' => $user_id,
                    'is_app' => $this->is_app,
        ]);
    }

    //购物详情页
    private function doShopDetail() {
        $this->layout = "index";
        $this->getView()->title = '商品详情';
        $gid = $this->get('gid');
        $user = $this->getUser();
        $goods_info = Goods_list::find()->where(['id' => $gid])->one();
        $goods_pic = new Goods_list();
        $goods_pics = $goods_pic->getSlpic($gid);
        $goods_x = $goods_pic->getXpic($gid);
        $goods_d = $goods_pic->getDpic($gid);
        $attr = new Goods_attribute_value();
        $attr_info = $attr->getAttribute($gid);
        $attr_info_va = [];
        foreach ($attr_info as $k => $v) {
            $attr_info_va[$v->attr->attribute][] = $v->value;
        }
        return $this->render('detail_shop', [
                    'goods_info' => $goods_info,
                    'goods_pics' => $goods_pics,
                    'goods_x' => $goods_x,
                    'goods_d' => $goods_d,
                    'attr_info' => $attr_info_va,
                    'csrf' => $this->getCsrf(),
        ]);
    }

    private function returnMsg($code, $data = []) {
        $errMsg = $this->getErrorMsg($code);
        return json_encode(['code' => $code, 'msg' => $errMsg, 'data' => $data]);
    }

    /**
     * 地区默认
     */
    private function defaultArea() {
        return array(
            'code' => 0,
            'name' => '省',
            'area' =>
            array(
                0 =>
                array(
                    'code' => 0,
                    'name' => '市',
                    'area' =>
                    array(
                        0 =>
                        array(
                            'code' => 0,
                            'name' => '区/县',
                        )
                    ),
                ),
            ),
        );
    }

    private function getAddDetail($detail, $code) {
        $proCityArea = Areas::getProCityAreaName($code);
        return $proCityArea . $detail;
    }
//    //生成token
//    private function makeTokenData($user_id)
//    {
//        if (empty($user_id)){
//            return false;
//        }
//        $time = time();
//        $time = mb_substr($time, 4, 5);
//        $user_str = $user_id.$time;
//        $oApiCrypt = new ApiCrypt();
//        $data_set = $oApiCrypt->encrypt($user_str, "wx476bb3649401c450");
//        return urlencode($data_set);
//    }
}
