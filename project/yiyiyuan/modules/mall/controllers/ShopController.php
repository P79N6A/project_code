<?php

namespace app\modules\mall\controllers;

use app\common\ApiClientCrypt;
use app\commonapi\Common;
use app\commonapi\Crypt3Des;
use app\commonapi\ImageHandler;
use app\commonapi\Keywords;
use app\commonapi\Logger;
use app\models\news\Areas;
use app\models\news\Goods_attribute;
use app\models\news\Goods_attribute_value;
use app\models\news\Goods_list;
use app\models\news\Goods_category_list;
use app\models\news\Goods_order_terms;
use app\models\news\Goods_pic;
use app\models\news\MallOrder;
use app\models\news\MallOrderAddress;
use app\models\news\MallOrderPay;
use app\models\news\User;
use app\models\news\User_bank;
use app\models\news\User_loan;
use app\models\news\User_rate;
use yii\data\Pagination;
use app\models\news\Goods_address;
use app\models\news\Goods_address_flows;
use Yii;

class ShopController extends MallController {

    private $pageSize = 30;
    private $user_id_store = '';
    private $user_id = '';

    public function behaviors() {
        $userId = $this->get('user_id_store', '');
        if (!empty($userId)) {
            $this->user_id_store = urlencode($userId);
            $api = new ApiClientCrypt();
            $this->user_id = Crypt3Des::decrypt($userId, $api->getKey()); //24BEFILOPQRUVWXcdhntvwxy
            $trial = strpos($_SERVER['HTTP_USER_AGENT'], '3.1.0');
            Logger::dayLog('mallshop', $userId, $this->user_id, $trial);
            if (!empty($trial)) {
                $this->user_id = $userId;
            }
            Logger::dayLog('weixin/mall', $userId, $this->user_id_store, $this->user_id);
            if (empty($this->user_id)) {
                exit('用户信息不存在');
            }
            $userInfo = User::findIdentity($this->user_id);
            if (!$userInfo) {
                exit('用户信息不存在');
            }
            Yii::$app->newDev->login($userInfo, 1);
        }
        return [];
    }

    public function init() {
        $this->layout = "index";
    }

    public function actionIndex() {
        $this->getView()->title = "商城";
        $categoryModel = new Goods_category_list();
        //获取全部商品分类
        $allGoodsTypes = $categoryModel->getAllCategory(5);
        //获取推荐商品分类
        $tjGoodsTypes = $categoryModel->getTjCategory(5);
        $showLoan = 1;
        $mobile = '';
        $trial = strpos($_SERVER['HTTP_USER_AGENT'], '3.5.0');
        $userInfo = User::find()->where(['user_id' => $this->user_id])->one();
        if ($userInfo) {
            $mobile = $userInfo->mobile;
            $testList = array('13466604662', '13439660605', '18500310315', '18500597522', '15910690412', '18610291548', '17600664664');
            if (in_array($mobile, $testList)) {
                $showLoan = 0;
            }
        }

        if (!$trial && $showLoan == 1) { //已过审且不是白名单
            return $this->redirect('/mall/index?user_id_store=' . $this->user_id_store);
        }
        //如果是安卓 并且不是白名单泽跳走
        $android = strpos($_SERVER['HTTP_USER_AGENT'], 'Android');
        if ($android && $showLoan == 1) {
            return $this->redirect('/mall/index?user_id_store=' . $this->user_id_store);
        }


        $user_id = 0;
        $userObj = $this->getUser();
        if ($userObj) {
            $user_id = $userObj->user_id;
        }

        $is_app = 0;
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')) {
            $is_app = 1;
        }

        return $this->render('index', [
                    'mobile' => $mobile,
                    'allGoodsTypes' => $allGoodsTypes,
                    'tjGoodsTypes' => $tjGoodsTypes,
                    'user_id_store' => $this->user_id_store,
                    'showLoan' => $showLoan,
                    'user_id' => $user_id,
                    'is_app' => $is_app,
        ]);
    }

    public function actionList() {
        $type = $this->get('type');
        if (!$type) {
            $this->render('/shop/index');
        }
        //获取全部商品分类
        $categoryModel = new Goods_category_list();
        $allGoodsTypes = $categoryModel->getAllCategory(5);
        $this->getView()->title = $categoryModel->getNameById($type);
        $goodsListModel = new Goods_list();
        $model = $goodsListModel->getGoodsByCid($type, $this->pageSize);
        $count = $goodsListModel->find()->where(['cid' => $type])->count();
        $pageTotal = ceil($count / $this->pageSize);

        return $this->render('list', [
                    'model' => $model,
                    'type' => $type,
                    'allGoodsTypes' => $allGoodsTypes,
                    'pageTotal' => $pageTotal,
                    'user_id_store' => isset($_GET['user_id_store']) ? $_GET['user_id_store'] : '',
        ]);
    }

    public function actionDetail() {
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
        return $this->render('detail', [
                    'goods_info' => $goods_info,
                    'goods_pics' => $goods_pics,
                    'goods_x' => $goods_x,
                    'goods_d' => $goods_d,
                    'attr_info' => $attr_info_va,
                    'csrf' => $this->getCsrf(),
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

    public function actionConfirmation() {
        $this->getView()->title = "确认订单";
        $user = $this->getUser();
        $user_model = new User();
//        $user->user_id = "5133";
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
        return $this->render('confirmation', [
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

    public function actionOrderdetails() {
        $this->getView()->title = "商品详情";
        $order_id = $this->get('order_id');
        $goodsOrderModel = new MallOrder();
        $orderInfo = $goodsOrderModel->getGoodsOrderByOrderId($order_id);
        if (!$orderInfo && empty($orderInfo->address)) {
            exit('收货地址数据不全');
        }
        $orderInfo->address->address_detail = $this->getAddDetail($orderInfo->address->address_detail, $orderInfo->address->area_code);
        $goodsContentJson = $orderInfo->goods_content;
        $goodsContent = json_decode($goodsContentJson, true);
        $user_info = $this->getUser();
        $bankModel = new User_bank();
        $bank_count = User_bank::find()->where(['user_id' => $user_info->user_id, 'status' => 1])->count();
        return $this->render('orderdetails', [
                    'orderInfo' => $orderInfo,
                    'goodsContent' => $goodsContent,
                    'bank_count' => $bank_count,
                    '_csrf' => $this->getCsrf(),
        ]);
    }

    public function actionGoodsrecord() {
        $this->getView()->title = "商城订单";
        $user_info = $this->getUser();
        //获取订单记录的商品信息
        $goods_list = (new MallOrder())->getGoodsListByUserId($user_info->user_id);
        foreach ($goods_list as $k => $v) {
            $goodsContentJson = $v['goods_content'];
            $goodsContent = json_decode($goodsContentJson, true);
            $goods_list[$k]['goods_money'] = $goodsContent['money'];
            $goods_list[$k]['colour'] = $goodsContent['colour'];
            $goods_list[$k]['goods_name'] = $goodsContent['goods_name'];
            $goods_list[$k]['pic_url'] = ImageHandler::getUrl($goodsContent['pic_url']);
        }
        return $this->render('goodsrecord', [
                    'goods_list' => $goods_list
        ]);
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
        Logger::errorLog(print_r($condition, true), 'zhifuMall');
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
        Logger::errorLog(print_r($postData, true), 'mallPay');
        $openApi = new ApiClientCrypt;
        $res = $openApi->sent('payroute/pay', $postData, 2);
        $result = $openApi->parseResponse($res);
        Logger::errorLog(print_r($result, true), 'mallPayR');
        if ($result['res_code'] != 0 || !isset($result['res_data']['url']) || empty($result['res_data']['url'])) {
            return $this->showMessage(1, '支付失败');
        }
        $redirect_url = (string) $result['res_data']['url'];
        return $this->showMessage(0, $redirect_url);
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
    private function termMoney($user_id, $goods_price, $terms) {
        $rate = new User_rate();
        $rate_info = $rate->getRate($user_id);
        $term_money = [];
        foreach ($terms as $v) {
            if (empty($rate_info['interest'][$v * 28])) {
                $interest = 0.00098;
            } else {
                $interest = $rate_info['interest'][$v * 28];
            }
            $money = ceil(($goods_price * 28 * $interest * $v + $goods_price) / $v * 100) / 100;
            $term_money[$v] = sprintf("%01.2f", $money);
        }
        return $term_money;
    }

    private function getAddDetail($detail, $code) {
        $proCityArea = Areas::getProCityAreaName($code);
        return $proCityArea . $detail;
    }

}
