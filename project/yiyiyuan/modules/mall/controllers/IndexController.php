<?php

namespace app\modules\mall\controllers;

use app\common\ApiClientCrypt;
use app\commonapi\Apihttp;
use app\commonapi\Crypt3Des;
use app\commonapi\ApiSign;
use app\commonapi\ImageHandler;
use app\commonapi\Keywords;
use app\commonapi\Logger;
use app\models\news\Coupon_list;
use app\models\dev\UserCredit;
use app\models\news\Areas;
use app\models\news\Goods_attribute;
use app\models\news\Goods_attribute_value;
use app\models\news\Goods_list;
use app\models\news\Goods_category_list;
use app\models\news\Goods_order_terms;
use app\models\news\Goods_pic;
use app\models\news\Mall_banner;
use app\models\news\ScanTimes;
use app\models\news\Setting;
use app\models\news\User;
use app\models\news\User_credit_qj;
use app\models\news\User_credit;
use app\models\news\User_loan;
use app\models\news\User_rate;
use app\models\news\Activitynew;
use app\models\news\ActivityElasticLayer;
use yii\data\Pagination;
use app\models\news\Goods_address;
use app\models\news\Goods_address_flows;
use Yii;
use app\models\news\Loan_repay;

class IndexController extends MallController {

    private $pageSize = 30;
    private $userid = '';
    private $activity_time_jg;

    public function behaviors() {
        $userId = $this->get('user_id_store');
        if ($userId) {
            $this->userid = urlencode($userId);
            $api = new ApiClientCrypt();
            $userid = Crypt3Des::decrypt($userId, $api->getKey()); //24BEFILOPQRUVWXcdhntvwxy
            Logger::dayLog('mallindex', $userId, $userid);
            if (!$userid) {
                exit('用户信息不存在');
            }
            $userInfo = User::findIdentity($userid);
            if (!$userInfo) {
                exit('用户信息不存在');
            }
            Yii::$app->newDev->login($userInfo, 1);
        }
        return [];
    }

    public function init() {
        $this->layout = "index";
        $this->activity_time_jg = 2 * 86400;
    }

    public function actionIndex() {
        $supermarketOpen = Keywords::supermarketOpen();
        $user_id_store = $this->get('user_id_store', '');
        $weixin = $this->get('type');
        if ($supermarketOpen == 2) {
            $user_id_store = urlencode($user_id_store);
//            $this->redirect('/mall/index/indexs?type='.$this->get('type'));
            $this->redirect('/mall/index/indexs?user_id_store=' . $user_id_store . '&type=' . $weixin);
        }
        $this->getView()->title = "商城";
        //是否登录
        $user_id_store = $this->get('user_id_store', '');
        $userInfo = $this->getUser();
        $isShow = false;
        $is_display = 0;  //零不弹层
        $this->layout = 'normal';
        $categoryModel = new Goods_category_list();
        //弹窗banner
        $tanchuan_data = Mall_banner::find()->where(['type' => 1, 'status' => 1])->orderBy('product_position asc,id desc')->all();
        //热门推荐-banner
        $daichao_rmtj_banner = Mall_banner::find()->where(['type' => 2, 'category' => 1, 'ads_position' => 1, 'status' => 1])->orderBy('id desc')->one();
        $daichao_rmtj_img = empty($daichao_rmtj_banner['banner_pic_url']) ? '/292/images/images/hot-banner.png' : Yii::$app->params['img_url'] . $daichao_rmtj_banner['banner_pic_url'];
        //贷超导流-banner
        $daichao_dcdl_banner = Mall_banner::find()->where(['type' => 2, 'category' => 2, 'ads_position' => 1, 'status' => 1])->orderBy('id desc')->one();
        $daichao_dcdl_img = empty($daichao_dcdl_banner['banner_pic_url']) ? '/292/images/images/hot-banner.png' : Yii::$app->params['img_url'] . $daichao_dcdl_banner['banner_pic_url'];
        //热门推荐-产品位
        $daichao_rmtj_position = Mall_banner::find()->where(['type' => 2, 'category' => 1, 'ads_position' => 2, 'status' => 1])->orderBy('product_position asc,id desc')->all();
        //贷超导流-产品位
        $daichao_dcdl_position = Mall_banner::find()->where(['type' => 2, 'category' => 2, 'ads_position' => 2, 'status' => 1])->orderBy('product_position asc,id desc')->all();
        //过审期间未登录和白名单用户登录后
//        $user_id_store=204220;
        if (($user_id_store || $this->userid) || ($weixin && $weixin == 'weixin' && $userInfo)) {
            //获取全部商品分类
            $allGoodsTypes = $categoryModel->getAllCategory(3);
            //正常用户过审前后登录
            $view = 'normal';

            //查询这个用户今天是否显示弹层
            $getRedis = $this->getRedis($userInfo->mobile);
            if (empty($getRedis)) {
                //如果为空 ---则添加redis
                $this->setNotRedis($userInfo->mobile, time());
                $is_display = 1;  //等于(1)证明 要给用户弹层
            } else if (!empty($getRedis) && strtotime(date('Y-m-d'), time()) > $getRedis) {
                $this->delRedis($userInfo->mobile);  //删除redis
                $this->setNotRedis($userInfo->mobile, time());  //添加redis
                $is_display = 1;  //等于(1)证明 要给用户弹层
            } else {
                $is_display = 0;
            }
        } elseif (!$user_id_store || !$this->userid) {
            //获取全部商品分类
            $allGoodsTypes = $categoryModel->getAllCategory(3);
            //过审后未登录-----页面
            $view = 'trialindex';
        }
        if (empty($tanchuan_data)) {//如果弹窗没数据就不弹
            $is_display = 0;
        }
        //获取推荐商品分类
        $tjGoodsTypes = $categoryModel->getTjCategory(5);
        $hasRepayingLoan = 0;
        $hasIousing = 0;
        $userLoanInfo = null;
        if ($userInfo) {
            $userLoanInfo = User_loan::find()->where(['user_id' => $userInfo->user_id])->orderBy('create_time desc')->one();
            if ($userLoanInfo && in_array($userLoanInfo->status, [9, 11, 12, 13]) && $userLoanInfo->loanextend && $userLoanInfo->loanextend->status == 'SUCCESS') {
                $hasRepayingLoan = 1;
            }
            $iousResult = (new Apihttp())->getUseriousinfo(['mobile' => $userInfo->mobile]);
            if (empty($iousResult)) {
                Logger::dayLog('app/getUseriousinfo', '获取用户白条信息失败', $userInfo->user_id, $iousResult);
            } elseif (!empty($iousResult)) {
                $hasIousing = 1;
            }
        }
        $encodeUserId = !empty($userInfo) ? $userInfo->user_id : '';

        $mobile = '';
        $dc_mobile = '';
        $user_id = 0;
        if ($userInfo && $userInfo->mobile && ($user_id_store || $this->userid)) {
            $dc_mobile = $userInfo->mobile;
            $user_id = $userInfo->user_id;
            $mobile = urlencode(Crypt3Des::encrypt($userInfo->mobile, (new ApiClientCrypt())->getKey()));
        }
        $is_app = 0;
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')) {
            $is_app = 1;
        }
        $dcMobile = '';
        if ($userInfo) {
            $dcMobile = $userInfo->mobile;
        }
        $zrys_url = Yii::$app->params['youxinDomain'];
        
        //先花商城
        $shop_switch_datda=['18810436326','18401629347','18600578542','15011284013','15011091226','18905400433','17052088877'];
        $shop_setting = new Setting();
        $shop_switch_result = $shop_setting->getShop();
        $shop_switch = false;
        if($userInfo && in_array($userInfo->mobile,$shop_switch_datda)){
            $shop_switch = true;
        }
        if($shop_switch_result && ($shop_switch_result->status == 0)){
            $shop_switch = true;
        }
        return $this->render($view, [
            'allGoodsTypes' => $allGoodsTypes,
            'tjGoodsTypes' => $tjGoodsTypes,
            'user_id_store' => $this->userid,
            'isShow' => $isShow,
            'hasRepayingLoan' => $hasRepayingLoan,
            'hasIousing' => $hasIousing,
            'encodeUserId' => $encodeUserId,
            'userLoanInfo' => $userLoanInfo,
            'img_url' => Yii::$app->params['img_url'],
            'mobile' => $mobile,
            'dc_mobile' => $dc_mobile,
            'user_id' => $user_id,
            'is_app' => $is_app,
            'zrys_url' => $zrys_url,
            'is_display' =>$is_display,
            'dcMobile' => $dcMobile,
            'tanchuan_data'=>$tanchuan_data,
            'daichao_rmtj_banner'=>$daichao_rmtj_banner,
            'daichao_dcdl_banner'=>$daichao_dcdl_banner,
            'daichao_rmtj_img'=>$daichao_rmtj_img,
            'daichao_dcdl_img'=>$daichao_dcdl_img,
            'daichao_rmtj_position'=>$daichao_rmtj_position,
            'daichao_dcdl_position'=>$daichao_dcdl_position,
            'supermarketOpen'=>$supermarketOpen,
            'shop_switch'=>$shop_switch,
            'csrf' => $this->getCsrf(),
        ]);
    }

    public function actionIndexs() {
        $this->getView()->title = "商城";
        $userInfo = $this->getUser();
        $user_id_store = $this->get('user_id_store', '');
        $user_id_store = urldecode($user_id_store);
        $weixin = $this->get('type');
        $isShow = false;
        $is_display = 0;  //零不弹层
        if (!empty($userInfo)) {
            //查询这个用户今天是否显示弹层
            $getRedis = $this->getRedis('alert' . $userInfo->mobile);
            if (empty($getRedis)) {
                //如果为空 ---则添加redis
                $this->setNotRedis('alert' . $userInfo->mobile, time());
                $is_display = 1;  //等于(1)证明 要给用户弹层
            } else if (!empty($getRedis) && strtotime(date('Y-m-d'), time()) > $getRedis) {
                $this->delRedis('alert' . $userInfo->mobile);  //删除redis
                $this->setNotRedis('alert' . $userInfo->mobile, time());  //添加redis
                $is_display = 1;  //等于(1)证明 要给用户弹层
            } else {
                $is_display = 0;
            }
        }
        $is_show = 0;   //显示7天活动banner
        //用户已经登录
        if ($userInfo && $userInfo->mobile) {
            $sacnTimesModel = new ScanTimes();
            $result = $sacnTimesModel->getByMobileType($userInfo->mobile, 22);
            if (empty($result)) {
                $isShow = true;
                $sacnTimesModel->save_scan(['mobile' => $userInfo->mobile, 'type' => 22]);
            }
        }
        //弹窗banner
        $tanchuan_data = Mall_banner::find()->where(['type' => 1, 'status' => 1])->orderBy('product_position asc,id desc')->all();
        if (empty($tanchuan_data)) {//如果弹窗没数据就不弹
            $is_display = 0;
        }
        $categoryModel = new Goods_category_list();
        //获取全部商品分类
        $allGoodsTypes = $categoryModel->listCategory(0,1,1,[0,1],5);
        //获取推荐商品分类
        $tjGoodsTypes = $categoryModel->getTjCategory(5);
        $hasRepayingLoan = 0;
        $hasIousing = 0;
        $userLoanInfo = null;
        if ($userInfo) {
            $userLoanInfo = User_loan::find()->where(['user_id' => $userInfo->user_id])->orderBy('create_time desc')->one();
            if ($userLoanInfo && in_array($userLoanInfo->status, [9, 11, 12, 13]) && $userLoanInfo->loanextend && $userLoanInfo->loanextend->status == 'SUCCESS') {
                $hasRepayingLoan = 1;
            }
        }
        if ($userInfo) {
            $iousResult = (new Apihttp())->getUseriousinfo(['mobile' => $userInfo->mobile]);
            if (empty($iousResult)) {
                Logger::dayLog('app/getUseriousinfo', '获取用户白条信息失败', $userInfo->user_id, $iousResult);
            } elseif (!empty($iousResult)) {
                $hasIousing = 1;
            }
        }

//        $api = new ApiClientCrypt();
        $encodeUserId = !empty($userInfo) ? $userInfo->user_id : '';
//        if($userInfo){
//            $encodeUserId= urlencode(Crypt3Des::encrypt($userInfo->user_id,$api->getKey()));
//        }
        //商城首页活动入口弹层
        $activity_res = $this->getActivity($userInfo);
        //$activity_show_remark =  $activity_res['remark'];
        $activity_img_url = !empty($activity_res['activity']) ? $activity_res['activity']['alert_url'] : '';
        $activity_id = !empty($activity_res['activity']) ? $activity_res['activity']['id'] : '';
        $mobile = '';
        $dc_mobile = '';
        $user_id = 0;
        if ($userInfo && $userInfo->mobile && ($user_id_store || $this->userid)) {
            $dc_mobile = $userInfo->mobile;
            $user_id = $userInfo->user_id;
            $mobile = urlencode(Crypt3Des::encrypt($userInfo->mobile, (new ApiClientCrypt())->getKey()));
        }
        $is_app = 0;
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')) {
            $is_app = 1;
        }
        $supermarketOpen = Keywords::supermarketOpen();
        $zrys_url = Yii::$app->params['youxinDomain'];
        
        //先花商城
        $shop_switch_result = (new Setting())->getShop();
        $shop_switch = false;
        $shop_switch_datda=['18810436326','18401629347','18600578542','15011284013','15011091226','18905400433','17052088877'];
        if($userInfo && in_array($userInfo->mobile,$shop_switch_datda)){
            $shop_switch = true;
        }
        if($shop_switch_result && ($shop_switch_result->status == 0)){
            $shop_switch = true;
        }
        
        return $this->render('index', [
            'allGoodsTypes' => $allGoodsTypes,
            'tjGoodsTypes' => $tjGoodsTypes,
            'user_id_store' => $this->userid,
            'isShow' => $isShow,
            'hasRepayingLoan' => $hasRepayingLoan,
            'hasIousing' => $hasIousing,
            'encodeUserId' => $encodeUserId,
            'userLoanInfo' => $userLoanInfo,
            'host' => Yii::$app->request->hostInfo,
            'activity_img_url' => $activity_img_url,
            'activity_show_remark' => $activity_res['remark'],
            'activity_id' => $activity_id,
            'img_url' => Yii::$app->params['img_url'],
            'mobile' => $mobile,
            'dc_mobile' => $dc_mobile,
            'user_id' => $user_id,
            'is_app' => $is_app,
            'zrys_url' => $zrys_url,
            'is_show' => $is_show,
            'is_display' => $is_display,
            'supermarketOpen'=>$supermarketOpen,
            'tanchuan_data'=>$tanchuan_data,
            'shop_switch'=>$shop_switch,
            'csrf' => $this->getCsrf(),
            'type' => $weixin,
        ]);
    }

    private function getActivity($userInfo) {
        $time_jg = $this->activity_time_jg; //活动时间间隔
        $now_time = date('Y-m-d H:i:s');
        $activity = Activitynew::find()->where(['index_input' => 1, 'status' => 1, 'admin_status' => 1,])->andWhere("start_date < '$now_time'")->andWhere("end_date > '$now_time'")->orderBy('create_time desc')->one();

        $activity_show_remark = 0;

        if (!empty($activity) && !empty($userInfo)) {

            $activity_start_time = $activity['start_date'];
            $activity_end_time = $activity['end_date'];

            if ($activity['elastic_layer_rule'] == 1) { //1、活动期间首次打开APP弹一次
                $transaction = Yii::$app->db->beginTransaction();
                $activity_record = ActivityElasticLayer::find()->where(['user_id' => $userInfo->user_id])->andWhere("create_time < '$activity_end_time'")->andWhere("create_time > '$activity_start_time'")->andWhere(['activity_id' => $activity->id])->count();

                if ($activity_record == 0) {
                    $activity_show_remark = self::getRemark($userInfo, $activity, $transaction);
                    return ['remark' => $activity_show_remark, 'activity' => $activity];
                }

                $activity_show_remark = 0;
                return ['remark' => $activity_show_remark, 'activity' => $activity];
            }
            if ($activity['elastic_layer_rule'] == 2) { //2、从第一次弹出算起，每两天弹一次
                $transaction = Yii::$app->db->beginTransaction();
                $activity_record_second = $this->getActRecord($activity, $userInfo, $activity_end_time, $activity_start_time);

                if (!empty($activity_record_second)) {

                    if (((time() - strtotime($activity_record_second['create_time'])) >= $time_jg) && (time() < strtotime($activity_end_time))) {
                        $activity_show_remark = self::getRemark($userInfo, $activity, $transaction);

                        return ['remark' => $activity_show_remark, 'activity' => $activity];
                    }

                    $activity_show_remark = 0;
                    return ['remark' => $activity_show_remark, 'activity' => $activity];
                }

                $activity_show_remark = self::getRemark($userInfo, $activity, $transaction);
                return ['remark' => $activity_show_remark, 'activity' => $activity];
            }

            return ['remark' => $activity_show_remark, 'activity' => $activity];
        }
        return ['remark' => $activity_show_remark, 'activity' => $activity];
    }

    private function getActRecord($activity, $userInfo, $activity_end_time, $activity_start_time) {
        $result = ActivityElasticLayer::find()->where(['user_id' => $userInfo->user_id])->andWhere("create_time < '$activity_end_time'")->andWhere("create_time > '$activity_start_time'")->andWhere(['activity_id' => $activity->id])->orderBy('id desc')->one();
        return $result;
    }

    private function getRemark($userInfo, $activity, $transaction) {
        $condition = [
            'user_id' => $userInfo->user_id,
            'activity_id' => $activity['id'],
            'create_time' => date('Y-m-d H:i:s'),
            'version' => 1
        ];

        $save_res = (new ActivityElasticLayer())->save_record($condition);
        if (!$save_res) {
            $transaction->rollBack();
            $activity_show_remark = 0;
            return $activity_show_remark;
        } else {
            $transaction->commit();
            $activity_show_remark = 1;
            return $activity_show_remark;
        }
    }

    public function actionList() {
        $type = $this->get('type');
        if (!$type) {
            $this->render('/mall/index');
        }
        $supermarketOpen = Keywords::supermarketOpen();
        //获取全部商品分类
        $categoryModel = new Goods_category_list();
        $allGoodsTypes = $categoryModel->getAllCategory(5);
        $this->getView()->title = $categoryModel->getNameById($type);
        $goodsListModel = new Goods_list();
        $model = $goodsListModel->getGoodsByCid($type, $this->pageSize);
        $count = $goodsListModel->find()->where(['cid' => $type])->count();
        $pageTotal = ceil($count / $this->pageSize);
        $user = $this->getUser();
        $userid = "";
        if (!empty($user)) {
            $userid = $user->user_id;
        }
        return $this->render('list', [
                    'model' => $model,
                    'type' => $type,
                    'allGoodsTypes' => $allGoodsTypes,
                    'pageTotal' => $pageTotal,
                    'user_id_store' => $this->userid,
                    'userid' => $userid,
                    'supermarketOpen' => $supermarketOpen,
        ]);
    }

    public function actionDetail() {
        $this->getView()->title = '商品详情';
        $gid = $this->get('gid');
        if (!empty($this->userid)) {
            $user = $this->getUser();
            $haveLoan = (new User_loan)->getHaveinLoan($user->user_id);
            $haveOrder = (new Goods_order_terms())->getHaveinOrder($user->user_id);
        } else {
            $haveLoan = false;
            $haveOrder = false;
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
            $user_id = $user->user_id;
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
        ]);
    }

    public function actionAjaxpage() {
        $type = $this->get('type');
        if (!$type) {
            echo $this->returnMsg(10001);
            exit();
        }
        $goodsList = (new Goods_list())->find()->where(['cid' => $type]);
        $pages = new Pagination(['totalCount' => $goodsList->count(), 'pageSize' => $this->pageSize]);
        $model = $goodsList->offset($pages->offset)->limit($pages->limit)->orderBy('create_time desc')->asArray()->all();
        foreach ($model as $k => $v) {
            $picUrl = (new Goods_pic())->getPicUrlByGid($v['id']);
            $model[$k]['pic_url'] = ImageHandler::getUrl($picUrl);
        }
        echo $this->returnMsg('0000', ['page' => $this->get('page'), 'list' => $model]);
    }

    public function actionAjaxconfirmation() {
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
        $term = isset($goods_info['term']) ? $goods_info['term'] : $this->getCookieVal('term');
        $money = isset($goods_info['money']) ? $goods_info['money'] : $this->getCookieVal('money');
        $pic_url = isset($goods_info['pic_url']) ? $goods_info['pic_url'] : $this->getCookieVal('pic_url');
        //把商品信息存到cookie里
        $this->setCookieVal('goods_name', $goods_name);
        $this->setCookieVal('goods_id', $goods_id);
        $this->setCookieVal('goods_price', $goods_price);
        $this->setCookieVal('term', $term);
        $this->setCookieVal('money', $money);
        $this->setCookieVal('bb', $bb);
        $this->setCookieVal('colour', $colour);
        $this->setCookieVal('pic_url', $pic_url);

        echo $this->returnMsg('0000');
    }

    public function actionConfirmation() {
        $this->getView()->title = "确认订单";
        $user = $this->getUser();
        $user_model = new User();
        $userInfo = $user_model->getUserinfoByUserId($user->user_id);
        $haveLoan = (new User_loan)->getHaveinLoan($user->user_id);
        $haveOrder = (new Goods_order_terms())->getHaveinOrder($user->user_id);
        $address_model = new Goods_address();
        $address_info = $address_model->getAddress($user->user_id);
        $attr = new Goods_attribute_value();

        $goods_name = $this->getCookieVal('goods_name');
        $goods_id = $this->getCookieVal('goods_id');
        $goods_price = $this->getCookieVal('goods_price');
        $colour = $this->getCookieVal('colour');
        $bb = $this->getCookieVal('bb');
        $term = $this->getCookieVal('term');
        $money = $this->getCookieVal('money');
        $pic_url = $this->getCookieVal('pic_url');
        $attr_info = $attr->getAttribute($goods_id);
        return $this->render('confirmation', [
                    'userInfo' => $userInfo,
                    'goods_name' => $goods_name,
                    'goods_id' => $goods_id,
                    'goods_price' => $goods_price,
                    'attr_info' => $attr_info,
                    'address_info' => $address_info,
                    'colour' => $colour,
                    'bb' => $bb,
                    'term' => $term,
                    'money' => $money,
                    'pic_url' => $pic_url,
                    'haveLoan' => $haveLoan ? 1 : 2, //1有借款 2：无借款
                    'haveOrder' => $haveOrder ? 1 : 2, //1有订单 2：无订单
                    'csrf' => $this->getCsrf(),
        ]);
    }

    public function actionEditaddress() {
        $this->getView()->title = "编辑地址";
        $address_id = $this->get('address_id');
        $user_id = $this->get('user_id');
        $address = new Goods_address();
        $address_info = $address->getAddressById($address_id);
        $list = Areas::getAllAreas();
        $address_code = (new Areas)->getProCityArea($address_info['area_code']);
        $list = json_encode(array_merge(array($this->defaultArea()), json_decode($list, true)));
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
        $data = [
            'money' => $post_data['goods_price'],
            'colour' => $post_data['colour'],
            'edition' => $post_data['bb'],
            'goods_name' => $post_data['goods_name'],
            'pic_url' => $post_data['pic_url']
        ];
        $post_data['description'] = json_encode($data);
        $goodsListModel = new Goods_list();
        $goods_info = $goodsListModel->getGoodsById($post_data['goods_id']);
        $goods_order = new Goods_order_terms();
        $res = $goods_order->addOrder($goods_info, $post_data);
        if (!$res) {
            return $this->showMessage(1, '生成订单失败');
        }
        return $this->showMessage(0, $res);
    }

    public function actionOrderdetails() {
        $this->getView()->title = "商品详情";
        $order_id = $this->get('order_id');
        $goodsOrderModel = new Goods_order_terms();
        $orderInfo = $goodsOrderModel->getGoodsOrderByOrderId($order_id);
        if (!$orderInfo && empty($orderInfo->address)) {
            exit('收货地址数据不全');
        }
        $orderInfo->address->address_detail = $this->getAddDetail($orderInfo->address->address_detail, $orderInfo->address->area_code);
        $goodsContentJson = $orderInfo->goods_content;
        $goodsContent = json_decode($goodsContentJson, true);
        return $this->render('orderdetails', [
                    'orderInfo' => $orderInfo,
                    'goodsContent' => $goodsContent,
        ]);
    }

    public function actionGoodsrecord() {
        $this->getView()->title = "商城订单";
        $user_info = $this->getUser();
        if (!$user_info) {
            exit('用户信息有误!');
        }
        $isWhite=$this->isWhite($user_info);
        if(!$isWhite){
            $this->redirect('/mall/store/goodsrecord');
        }else{
            $this->redirect('/mall/shop/goodsrecord');
        }
        //获取订单记录的商品信息
//        $goods_list = (new Goods_order_terms())->getGoodsListByUserId($user_info->user_id,2);
//        Logger::dayLog('mallindex', $user_info->user_id);
//        foreach ($goods_list as $k => $v) {
//            $goodsContentJson = $v['goods_content'];
//            $goodsContent = json_decode($goodsContentJson, true);
//            $goods_list[$k]['goods_money'] = $goodsContent['money'];
//            $goods_list[$k]['colour'] = $goodsContent['colour'];
//            $goods_list[$k]['goods_name'] = $goodsContent['goods_name'];
//            $goods_list[$k]['pic_url'] = ImageHandler::getUrl($goodsContent['pic_url']);
//        }
//
//        return $this->render('goodsrecord', [
//                    'goods_list' => $goods_list
//        ]);
    }

    public function getGoodsorder() {
        $user_info = $this->getUser();
        $where = [
            'AND',
            ['user_id' => $user_info['user_id']],
        ];
        $goods_list = Goods_order_terms::find()->joinWith('goods', true, 'LEFT JOIN')->where($where)->orderBy('create_time desc')->all();
        return $goods_list;
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

    private function returnMsg($code, $data = []) {
        $errMsg = $this->getErrorMsg($code);
        return json_encode(['code' => $code, 'msg' => $errMsg, 'data' => $data]);
    }

    /**
     * 计算分期金额
     * @param $user_id
     * @param $goods_price
     * @param $terms
     * @return string
     */
    private function termMoney($goods_price, $terms) {
//        $rate = new User_rate();
//        $rate_info = $rate->getRate($user_id);
        $term_money = [];
        foreach ($terms as $v) {
//            if(empty($rate_info['interest'][$v*28])){
            $interest = 0.00098;
//            }else{
//                $interest = $rate_info['interest'][$v*28];
//            }
            $money = ceil(($goods_price * 28 * $interest * $v + $goods_price) / $v * 100) / 100;
            $term_money[$v] = sprintf("%01.2f", $money);
        }
        return $term_money;
    }

    private function getAddDetail($detail, $code) {
        $proCityArea = Areas::getProCityAreaName($code);
        return $proCityArea . $detail;
    }

    private function get_area($ip = '') {
        if ($ip == '') {
            $ip = $this->getip();
        }
        $url = 'http://ip.taobao.com/service/getIpInfo.php?ip=' . $ip; //淘宝接口
        $ret = $this->https_request($url);
        return empty($ret) ? '' : $ret['data']['city'];
    }

    //POST请求函数
    private function https_request($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $content = curl_exec($ch);
        return json_decode($content, true);
    }

    // 获取ip
    private function getip() {
        $realip = '';
        $unknown = 'unknown';
        if (isset($_SERVER)) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown)) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                foreach ($arr as $ip) {
                    $ip = trim($ip);
                    if ($ip != 'unknown') {
                        $realip = $ip;
                        break;
                    }
                }
            } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP']) && strcasecmp($_SERVER['HTTP_CLIENT_IP'], $unknown)) {
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR']) && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)) {
                $realip = $_SERVER['REMOTE_ADDR'];
            } else {
                $realip = $unknown;
            }
        } else {
            if (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), $unknown)) {
                $realip = getenv("HTTP_X_FORWARDED_FOR");
            } elseif (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), $unknown)) {
                $realip = getenv("HTTP_CLIENT_IP");
            } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), $unknown)) {
                $realip  =  getenv("REMOTE_ADDR");
            } else {
                $realip = $unknown;
            }
        }
        $realip = preg_match("/[\d\.]{7,15}/", $realip, $matches) ? $matches[0] : $unknown;
        return $realip;
    }

    public function actionXhshopajax(){
        $oUser = $this->getUser();
        $isapp = $this->post('isapp');
        $user_id_store_app = $this->post('user_id_store_app');
        if(empty($oUser) || ($isapp==1 && empty($user_id_store_app) )){
            return json_encode(['rsp_code'=>'0001','rsp_msg'=>'请登录!']);
        }
        $settingModel = new Setting();  
        $shop_setting = $settingModel->getShop();
        $shop_switch_datda=['18810436326','18401629347','18600578542','15011284013','15011091226','18905400433','17052088877'];
        if($oUser && !in_array($oUser->mobile,$shop_switch_datda)){
            if(!$shop_setting || $shop_setting->status !=0  ){ //商城开关已关闭
                return json_encode(['rsp_code'=>'0007','rsp_msg'=>'商城暂未开放，敬请期待。']);
            }
        }
        //待支付订单
        $waitOrder = $this->getShoporder($oUser,1);
        $xhshop_url = (new User())->getShopurl($oUser,2);
        if($waitOrder && !empty($waitOrder['rsp_data'])){
            $time = empty($waitOrder['rsp_data']['time']) ? 0 : ($waitOrder['rsp_data']['time']['hour'].':'.$waitOrder['rsp_data']['time']['min'].':'.$waitOrder['rsp_data']['time']['sec']);
            return json_encode(['rsp_code'=>'0003','rsp_msg'=>'您有一笔待支付的订单','rsp_data'=>['url'=>$xhshop_url,'time'=>$time]]);
        } 
        if($waitOrder && empty($waitOrder['rsp_data'])){ //数据异常
            return json_encode(['rsp_code'=>'0005','rsp_msg'=>$waitOrder['rsp_msg'],'rsp_data'=>'']);
        }
        //进行中订单
        $doingOrder = $this->getShoporder($oUser,2);
        if($doingOrder  && !empty($doingOrder['rsp_data'])){
            return json_encode(['rsp_code'=>'0004','rsp_msg'=>'您有一笔进行中的订单','rsp_data'=>['url'=>$xhshop_url]]);
        }
        if($doingOrder && empty($doingOrder['rsp_data'])){ //数据异常
            return json_encode(['rsp_code'=>'0006','rsp_msg'=>$doingOrder['rsp_msg'],'rsp_data'=>'']);
        }
        
        //进行中借款 
        $loaning_result = $this->getLoaning($oUser);
        $bill_url = '/borrow/billlist/index';//账单url
        if($loaning_result){
            return json_encode(['rsp_code'=>'0002','rsp_msg'=>'您有一笔进行中的借款','rsp_data'=>['url'=>$bill_url]]);
        }
        //先花商城地址
        $xhshop_url_index = (new User())->getShopurl($oUser,1);
        return json_encode(['rsp_code'=>'0000','rsp_msg'=>'前往先花商城','rsp_data'=>['url'=>$xhshop_url_index]]);
        
    }
    
    
    /**
     * 查询一亿元进行中的借款
     * @param type $user
     * @return boolean
     */
    private function getLoaning($user){
        
        //智融钥匙待支付的账单
        $iousResult = (new Apihttp())->getUseriousinfo(['mobile' => $user->mobile]);
        //一亿元(待还款借款)
        $userLoanInfo = User_loan::find()
                        ->where(['user_id' => $user->user_id, 'status' => [9, 11, 12, 13]])
                        ->orderBy('create_time DESC')
                        ->one();
        if ((!empty($userLoanInfo) && ((!empty($userLoanInfo) && ($userLoanInfo->status == 9 && !empty($userLoanInfo->loanextend) && $userLoanInfo->loanextend->status == 'SUCCESS')) || in_array($userLoanInfo->status, [11, 12, 13])))) {
            return true;
        } elseif (!empty($userLoanInfo) && in_array($userLoanInfo->status, [9, 11, 12, 13]) && $userLoanInfo->settle_type == 3) {
            return true;
        } elseif (!empty($iousResult)) {
           return true; // 有待支付的白条且一亿元也处于账单页
        }
        return false;
    }
    
    /**
     * 判断先花商城的订单状态
     * @param type $user 
     * @param type $type 1:待支付订单 2：进行中订单
     */
    private function getShoporder($user,$type){
        $apiHttp = new Apihttp();
        $payResult = $apiHttp->getShoporder(['mobile' => $user->mobile,'type'=>$type,'source'=>1]);
        if($payResult['rsp_code']== '0000' && !empty($payResult['rsp_data']) && $payResult['rsp_data']['status'] == 1 ){
            return $payResult;
        }elseif( $payResult['rsp_code']== '0004'){  //或数据异常
             return $payResult;
        }
        
        return false;
    }
    /**
     * 是否是白名单用户
     * true:是白名单用户
     * false:不是白名单用户
     */
    private function isWhite($user_info) {
        $list = [
            '13466604662',
            '13439660605',
            '18500310315',
            '18500597522',
            '15910690412',
            '18610291548',
            '17600664664',
        ];
        $is_white = FALSE;
        if (!empty($user_info)) {
            $o_user = User::find()->where(['user_id' => $user_info->user_id])->one();
            if (!empty($o_user)) {
                $mobile = $o_user->mobile;
                if (in_array($mobile, $list)) {
                    $is_white = TRUE;
                }
            }
        }
        return $is_white;
    }

}
