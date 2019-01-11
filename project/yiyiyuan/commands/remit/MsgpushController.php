<?php

/**
 * 出款发短信通知
 */
/**
 *   linux : /data/wwwroot/yiyiyuan/yii remit/msgpush runNotify
 *   window : d:\xampp\php\php.exe D:\www\yiyiyuan\yii remit/msgpush runNotify
 */

namespace app\commands\remit;

use app\commonapi\Http;
use app\commonapi\Logger;
use app\models\news\Accesstoken;
use app\models\news\Coupon_list;
use app\models\news\Coupon_use;
use app\models\news\Sms;
use app\models\news\User;
use app\models\news\User_bank;
use app\models\news\User_loan;
use app\models\news\CommonNotify;
use Yii;
use yii\helpers\ArrayHelper;

class MsgpushController extends \app\commands\BaseController {

    /**
     * 出款通知
     *
     * @return str
     */
    public function runNotify() {
        $initRet = $this->_runNotify();
        print_r($initRet);
    }

    /**
     * 出款通知
     * @return []
     */
    private function _runNotify() {
        //1. 查询要处理的通知
        $initRet = ['total' => 0, 'success' => 0];
        $oCNotify = new CommonNotify;
        $notifys = $oCNotify->getInitData(1, 100);
        if (!$notifys) {
            Logger::dayLog("msgpush", "无数据");
            return $initRet;
        }
        //2 悲观锁定状态
        $ids = ArrayHelper::getColumn($notifys, 'id');
        $ups = $oCNotify->lockNotifys($ids);
        if (!$ups) {
            Logger::dayLog("msgpush", "锁定失败");
            return $initRet;
        }
        //3 计算处理总数
        $initRet['total'] = count($ids);

        //4 逐条处理
        foreach ($notifys as $oNotify) {
            //1. 乐观锁定, 悲观锁定双重防止重复
            $result = $oNotify->lock();
            if (!$result) {
                Logger::dayLog("msgpush", $oNotify->id, "无法锁定, 可能已被其它进程处理");
                continue;
            }

            //2. 处理单条纪录
            $result = $this->push($oNotify->notify_id);
            if (!$result) {
                Logger::dayLog("msgpush", $oNotify->notify_id, "处理失败");
                continue;
            }
            $ret = $oNotify->saveSuccess();

            $initRet['success'] ++;
        }
        return $initRet;
    }

    /**
     * 推送通知
     * @param  int $loan_id
     * @return   bool
     */
    private function push($loan_id) {
        $loan_id = intval($loan_id);
        if (!$loan_id) {
            return false;
        }
        $loan = User_loan::findOne($loan_id);
        if (!$loan) {
            return false;
        }
        $user = User::findOne($loan->user_id);
        if (!$user) {
            return false;
        }
        $result = $this->setNewsPush($user, $loan);
        return $result;
    }

    //消息推送
    private function setNewsPush($user, $loan) {
        $huankuan_date = !empty($loan->end_date) ? date('Y-m-d', strtotime('-1 days', strtotime($loan->end_date))) : date('Y-m-d', (time() + ($loan->days) * 24 * 3600));
        //发送推送短信
        $mobile = $user->mobile;
        $type = 5;
        $current_amount = sprintf("%.2f", ($loan->amount));
        //还款金额
        $huankuan_amount = $loan->getRepaymentAmount($loan);
        $result_loan = $this->sendSmsToLoanUser($mobile, $type, $huankuan_date, $current_amount, $loan->is_calculation, $huankuan_amount, $loan);

        //发送多客服的微信推送信息
        $openid = $user->openid;
        if (!empty($openid)) {
            $result = $this->sendServiceSms($loan->loan_id, $user->user_id, $loan->amount, $loan->interest_fee, $loan->withdraw_fee, $loan->coupon_amount, $loan->collection_amount, $loan->like_amount, $current_amount, $loan->bank_id, $loan->business_type, $huankuan_date, $openid, $loan->desc, $loan->is_calculation);
        }
        return true;
    }

    /**
     * 给借款人推送短信
     * @param type $mobile 用户手机号
     * @param type $type 发送类型
     * @param type $huankuan_date 还款日期
     * @param type $current_amount 借款金额
     * @param type $is_calculation 是否是砍头息
     * @param type $huankuan_amount 还款金额
     * @return type
     */
    private function sendSmsToLoanUser($mobile, $type, $huankuan_date, $current_amount, $is_calculation, $huankuan_amount, $loan) {
            $content = "尊敬的用户，您在先花一亿元有一笔" . $current_amount . "元借款已通过审核，实际出款金额为" . $current_amount . "元，最后还 款日为" . $huankuan_date . "，应还金额" . $huankuan_amount . "元，请在2小时内注意查收您所绑定的银行卡";

//        if ($is_calculation == 1) {
//            //出款金额
//            $out_amount = sprintf("%.2f", ($current_amount - $loan->withdraw_fee));
//            $content = "尊敬的用户，您在先花一亿元有一笔" . $current_amount . "元借款已通过审核，扣除" . ($loan->withdraw_fee / $current_amount * 100) . "%保险费后，实际出款金额为" . $out_amount . "元，最后还 款日为" . $huankuan_date . "，应还金额" . $huankuan_amount . "元，请在2小时内注意查收您所绑定的银行卡";
//        } else {
//            $content = "尊敬的用户，您在先花一亿元有一笔" . $current_amount . "元借款已通过审核，实际出款金额为" . $current_amount . "元，最后还 款日为" . $huankuan_date . "，应还金额" . $huankuan_amount . "元，请在2小时内注意查收您所绑定的银行卡";
//        }

        $resultmessage = $this->sendMessage($mobile, $content, $type);
        return $resultmessage;
    }

    //微信推送客服消息
    protected function sendServiceSms($loan_id, $user_id, $amount, $interest_fee, $withdraw_fee, $coupon_amount, $collection_amount, $like_amount, $current_amount, $bank_id, $business_type, $huankuan_date, $openid, $desc, $is_calculation) {
        $loan_coupon_sql = "select l.id,l.limit,l.end_date,l.val,l.status from " . Coupon_list::tableName() . " as l," . Coupon_use::tableName() . " as u where l.id=u.discount_id and u.loan_id=" . $loan_id;
        $loan_coupon = Yii::$app->db->createCommand($loan_coupon_sql)->queryOne();
        if ((!empty($loan_coupon) && ($loan_coupon['val'] == 0) && ($loan_coupon['status'] == 2)) || (!empty($loan_coupon) && (($interest_fee + $withdraw_fee) <= $coupon_amount))) {
            if ($is_calculation == 1) {
                $huankuan_amount = $current_amount;
            } else {
                $huankuan_amount = $current_amount + $withdraw_fee;
            }
            $friend_url = Yii::$app->params['app_url'] . "/dev/share/freecoupon?uid=" . $user_id . "&loan_id=" . $loan_id;
        } else {
            if ($is_calculation == 1) {
                $huankuan_amount = sprintf("%.2f", ($amount + $interest_fee + $collection_amount - $like_amount - $coupon_amount));
            } else {
                $huankuan_amount = sprintf("%.2f", ($amount + $interest_fee + $withdraw_fee + $collection_amount - $like_amount - $coupon_amount));
            }
            $friend_url = Yii::$app->params['app_url'] . "/dev/share/likestat?t=" . time() . "&d=" . $loan_id . "&s=" . md5(time() . $loan_id);
        }
        $nowdate = date('m-d');
        //查询银行卡信息
        $userbank = User_bank::find()->select(array('card'))->where(['id' => $bank_id])->one();
        $cardinfo = substr($userbank['card'], -4);
        $down_url = Yii::$app->params['downurl'];
        if (in_array($business_type, [1,4,5,6])) {
            if ($is_calculation == 1) {
                //出款金额
                $out_amount = sprintf("%.2f", ($current_amount - $withdraw_fee));
                $content = "打款通知\n" . $nowdate . "\n您在先花一亿元有一笔" . $current_amount . "元的借款已经通过了我们的审核，两小时内会自动提现到您尾号为" . $cardinfo . "的银行卡中，请注意查收。<a href='$friend_url'>喊好友来帮忙</a>还能减免50%利息哦！快来喊朋友帮忙吧！\n出款金额：" . $out_amount . "元（扣除" . ($withdraw_fee / $current_amount * 100) . "%服务费）\n借款用途：" . $desc . "\n还款时间：" . $huankuan_date . "\n还款金额：" . $huankuan_amount . "\n如有问题或需要更多帮助请及时咨询微信客服。\n如何快速借到便宜的钱？\n点击<a href='$down_url'>【马上借】</a>下载先花一亿元APP！";
            } else {
                $content = "打款通知\n" . $nowdate . "\n您在先花一亿元有一笔" . $current_amount . "元的借款已经通过了我们的审核，两小时内会自动提现到您尾号为" . $cardinfo . "的银行卡中，请注意查收。<a href='$friend_url'>喊好友来帮忙</a>还能减免50%利息哦！快来喊朋友帮忙吧！\n出款金额：" . $current_amount . "元\n借款用途：" . $desc . "\n还款时间：" . $huankuan_date . "\n还款金额：" . $huankuan_amount . "\n如有问题或需要更多帮助请及时咨询微信客服。\n如何快速借到便宜的钱？\n点击<a href='$down_url'>【马上借】</a>下载先花一亿元APP！";
            }
        } else {
            $content = "打款通知\n" . $nowdate . "\n您在先花一亿元有一笔" . $current_amount . "元的担保借款已经通过了我们的审核，两小时内会自动提现到您尾号为" . $cardinfo . "的银行卡中，请注意查收。<a href='$friend_url'>喊好友来帮忙</a>还能减免50%利息哦！快来喊朋友帮忙吧！\n出款金额：" . $current_amount . "元\n借款用途：" . $desc . "\n还款时间：" . $huankuan_date . "\n还款金额：" . $huankuan_amount . "\n如有问题或需要更多帮助请及时咨询微信客服。\n如何快速借到便宜的钱？\n点击<a href='$down_url'>【马上借】</a>下载先花一亿元APP！";
        }
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $this->getAccessToken();
        $data = '{
                                        "touser":"' . $openid . '",
                                        "msgtype":"text",
                                        "text":
                                        {
                                             "content":"' . $content . '"
                                        }
                                            }';
        $menu = Http::dataPost($data, $url);
        return $menu;
    }

    //发送短信
    private function sendMessage($mobile, $content, $type, $send_mobile = '') {
        $sendRet = Http::sendByMobile($mobile, $content);
        if ($sendRet) {
            $sms = new Sms();
            $condition = [
                'recive_mobile' => $mobile,
                'content' => $content,
                'sms_type' => $type,
            ];
            $ret = $sms->save_sms($condition);
            if ($ret) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    //获取access_token值
    private function getAccessToken() {
        $appId = \Yii::$app->params['AppID']; //，需要在微信公众平台申请自定义菜单后会得到
        $appSecret = \Yii::$app->params['AppSecret']; //需要在微信公众平台申请自定义菜单后会得到
        //先查询对应的数据表是否有token值
        $access_token = Accesstoken::find()->where(['type' => 1])->one();
        if (isset($access_token->access_token)) {
            //判断当前时间和数据库中时间
            $time = time();
            $gettokentime = $access_token->time;
            if (($time - $gettokentime) > 7000) {
                //重新获取token值然后替换以前的token值
                $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appId . "&secret=" . $appSecret;
                $data = Http::getCurl($url); //通过自定义函数getCurl得到https的内容
                $resultArr = json_decode($data, true); //转为数组
                $accessToken = $resultArr["access_token"]; //获取access_token
                //替换以前的token值
                $sql = "update yi_access_token set access_token = '$accessToken',time=$time where type=1";
                $result = Yii::$app->db->createCommand($sql)->execute();

                return $accessToken;
            } else {
                return $access_token->access_token;
            }
        } else {
            //获取token值并把token值保存在数据表中
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appId . "&secret=" . $appSecret;
            $data = Http::getCurl($url); //通过自定义函数getCurl得到https的内容
            $resultArr = json_decode($data, true); //转为数组
            $accessToken = $resultArr["access_token"]; //获取access_token

            $time = time();
            $sql = "insert into " . Accesstoken::tableName() . "(access_token,time) value('$accessToken','$time')";
            $result = Yii::$app->db->createCommand($sql)->execute();

            return $accessToken;
        }
    }

}
