<?php
/**
 * Created by PhpStorm.
 * User: wangyongqiang
 * Date: 2017/4/26
 * Time: 15:56
 */
namespace app\modules\borrow\controllers;

use app\commonapi\Logger;
use app\models\news\Coupon_list;
use app\models\news\User_bank;
use app\models\news\User;
use app\models\news\PrizeList;
use app\models\news\User_quota_new;
use app\models\news\SystemMessageList;
use app\models\news\WarnMessageList;
use app\models\news\MessageApply;
use yii\helpers\ArrayHelper;
use Yii;

class AccountController extends BorrowController {


    /**
     * "我"的页面
     */
    public function actionIndex() {

        $this->layout = "_data";
        $this->getView()->title = "个人中心";
        $user = $this->getUser();
        $userinfo = User::findOne($user->user_id);
        //优惠券
        $now_time = date('Y-m-d H:i:s');
        $frcoupon = Coupon_list::find()->where(['status' => 1, 'mobile' => $userinfo->mobile])->andWhere("end_date > '$now_time'")->count();
        //奖品 start_time
        $prize_count = PrizeList::find()->where(['user_id' => $userinfo->user_id, 'status' => 6, 'use_status' => 1])->andWhere("start_time < '$now_time'")->andWhere("end_time > '$now_time'")->count();

        //银行卡
        $bank = User_bank::find()->where(['user_id' => $userinfo->user_id, 'status' => 1, 'type' => '0'])->count();
        //交易记录
        $transaction_record_url = "/new/loan/loanlist";
        //邀请好友
        $invite_friends_url = "/new/invitation/index";
        $jsinfo = $this->getWxParam();

        //额度
        $currentAmount = (new User_quota_new())->getQuotaByUserId($userinfo->user_id);
        $guaranteeAmount = 2500.00;//担保额度（默认2500）

        //提醒消息
        $warnmsg = WarnMessageList::find()->where(['user_id' => $userinfo->user_id, 'read_status' => 0, 'status' => [2, 3], 'is_show' => 1])->count();

        $msg_res = $this->pullMsg($userinfo->user_id);

        //系统消息(未读)
        $sysmsgpart_count = (new SystemMessageList())->getSysmsgcount($userinfo->user_id, 0);
        $unread_message_count = $sysmsgpart_count + $warnmsg;
        
        //我的订单
        $order = (new User())->getOrderList($userinfo);
        $order_show = $order['order_show'];
        $order_list_url = $order['order_list_url'];
        return $this->render('index', [
            'userinfo' => $userinfo,
            'jsinfo' => $jsinfo,
            'couponcount' => $frcoupon, //优惠券
            'bank' => $bank, //银行卡
            'transaction_record_url' => $transaction_record_url, //交易记录
            'invite_friends_url' => $invite_friends_url,//邀请好友
            'current_amount' => $currentAmount,
            'guarantee_amount' => $guaranteeAmount,
            'prize' => $prize_count,
            'unread_message_count' => $unread_message_count,
            'order_show' => $order_show,
            'order_list_url' => $order_list_url,
        ]);

    }
    
    public function getOrderList($userinfo){
        $shop_setting = new Setting();
        $shop_switch_result = $shop_setting->getShop();
        $shop_switch = false;
        if($shop_switch_result && ($shop_switch_result->status == 0)){
            $shop_switch = true;
        }
        $xhshop_url = (new User())->getShopurl($userinfo,2);
        return ['order_show'=>$shop_switch,'order_list_url'=>$xhshop_url];
    }

    /**
     * 读取消息数量
     * @param $mobile
     * @return bool
     */
    public function pullMsg($user_id) {
        if (empty($user_id)) {
            return false;
        }
        $o_user = (new User())->getById($user_id);
        if(empty($o_user)){
            return false;
        }
        $where = [
            'AND',
            ['<', 'send_time', date('Y-m-d H:i:s')],
            ['>', 'send_time', $o_user->create_time],
            ['type' => 2],
            ['apply_status' => 1],
            ['!=', 'exec_status', 3]
        ];
        $applyList = (new MessageApply())->find()->where($where)->all();

        if (empty($applyList)) {
            return true;
        }
        $cidArr = ArrayHelper::getColumn($applyList, 'id');

        $func = function ($str) {
            return intval($str);
        };
        $cidArr = array_map($func, $cidArr);
        $list = SystemMessageList::find()->where(['user_id' => $user_id, 'mid' => $cidArr])->all();
        $alreadyList = ArrayHelper::getColumn($list, 'mid');

        $cidDiff = array_diff($cidArr, $alreadyList);

        if (empty($cidDiff)) {
            return true;
        }
        $data = array();

        foreach ($cidDiff as $key => $value) {
            $apply_val = MessageApply::findOne($value);
            $data[$key][0] = $value;
            $data[$key][1] = $apply_val->title;
            $data[$key][2] = $apply_val->contact;
            $data[$key][3] = $user_id;
            $data[$key][4] = 0;
            $data[$key][5] = date('Y-m-d H:i:s');
            $data[$key][6] = date('Y-m-d H:i:s');
            $data[$key][7] = 0;
            $data[$key][8] = $apply_val->send_time;
        }

        $res_insert = Yii::$app->db->createCommand()->batchInsert(
            SystemMessageList::tableName(), ['mid', 'title', 'contact', 'user_id', 'read_status', 'create_time', 'last_modify_time', 'version', 'send_time'], $data)->execute();
        if (!$res_insert) {
            Logger::dayLog('api/getuserinfo', '写入systemmessagelist数据失败' . $user_id, $res_insert);

        }

        return true;
    }

    /**
     * 冻结页面
     * @return string
     */
    public function actionBlack() {
        $this->layout = "renzhen";
        $this->getView()->title = "您提交的信息不符合规则，该账户已被冻结";
        $jsinfo = $this->getWxParam();
        return $this->render('black', ['jsinfo' => $jsinfo]);
    }

}