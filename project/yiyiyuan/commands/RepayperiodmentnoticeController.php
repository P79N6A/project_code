<?php

namespace app\commands;

use app\common\Logger;
use app\commonapi\ApiSms;

/**
 * 还款提醒通知
 */
/**
 * 1 注意这里引入文件必须是绝对路径。相对路径容易出错
 * 2 使用
 *   linux : /data/wwwroot/yiyiyuan/yii getloanover > /data/wwwroot/yiyiyuan/log/income.log (修改根目录下yii文件的php的解析路径)
 *   window : d:\xampp\php\php.exe D:\www\yiyiyuan\yii income
 */
use app\commonapi\Apihttp;
use app\commonapi\sms\CSms;
use app\models\dev\Sms;
use app\models\dev\User;
use app\models\news\GoodsBill;
use app\models\news\User_loan;

require(dirname(dirname(__FILE__)) . '/' . 'notification/android/AndroidUnicast.php');
require(dirname(dirname(__FILE__)) . '/' . 'notification/android/AndroidListcast.php');
require(dirname(dirname(__FILE__)) . '/' . 'notification/ios/IOSUnicast.php');
require(dirname(dirname(__FILE__)) . '/' . 'notification/ios/IOSListcast.php');

/**
 * 这个包含地址需要根据个人文件路径进行设置绝对路径
 */
class RepayperiodmentnoticeController extends BaseController {

    public function actionIndex() {
        $start_time = date('Y-m-d', time() - 86400);
        $end_time = date('Y-m-d', time() + 86400 * 4);
        $condition = [
            'AND',
            [">", GoodsBill::tableName() . ".end_time", $start_time],
            ["<", GoodsBill::tableName() . ".end_time", $end_time],
            [GoodsBill::tableName() . ".bill_status" => '9'],
        ];
        $total = GoodsBill::find()->where($condition)->count();
        $limit = 100;
        $pages = ceil($total / $limit);
        $nowtime = time();
        $loan_type = 3;

        $this->log("\n" . date('Y-m-d H:i:s') . "......................");
        $this->log("\n共{$total}条数据:每次处理{$limit},需要要处理{$pages}次\n");

        $mobile_w = '13466685413';
        $content_w = "鲁殿海先生/女士，今天是您在先花一亿元分期业务的最后还款日，本期应偿付金额1000元，请您尽快前往先花一亿元微信公众号或APP进行还款。完成多次借款可以提升额度哦！请注意：先花一亿元从未授权任何第三方向你收取任何费用！";
        $this->sendMessage($mobile_w, $content_w, $loan_type);
        $content_w = "鲁殿海先生/女士，您在先花一亿元的分期业务还有一天到达最后还款日，还款金额1000元，请您尽快前往先花一亿元微信公众号或APP进行还款。完成多次借款可以提升额度哦！请注意：先花一亿元从未授权任何第三方向你收取任何费用！";
        $this->sendMessage($mobile_w, $content_w, $loan_type);
        $content_w = "鲁殿海先生/女士，您在先花一亿元的分期业务还有两天到达最后还款日，还款金额1000元，请您尽快前往先花一亿元微信公众号或APP进行还款。完成多次借款可以提升额度哦！请注意：先花一亿元从未授权任何第三方向你收取任何费用！";
        $this->sendMessage($mobile_w, $content_w, $loan_type);

        $mobile_w = '18510509700';
        $content_w = "XXX先生/女士，今天是您在先花一亿元分期业务的最后还款日，本期应偿付金额1000元，请您尽快前往先花一亿元微信公众号或APP进行还款。完成多次借款可以提升额度哦！请注意：先花一亿元从未授权任何第三方向你收取任何费用！";
        $this->sendMessage($mobile_w, $content_w, $loan_type);
        $content_w = "XXX先生/女士，您在先花一亿元的分期业务还有一天到达最后还款日，还款金额1000元，请您尽快前往先花一亿元微信公众号或APP进行还款。完成多次借款可以提升额度哦！请注意：先花一亿元从未授权任何第三方向你收取任何费用！";
        $this->sendMessage($mobile_w, $content_w, $loan_type);
        $content_w = "XXX先生/女士，您在先花一亿元的分期业务还有两天到达最后还款日，还款金额1000元，请您尽快前往先花一亿元微信公众号或APP进行还款。完成多次借款可以提升额度哦！请注意：先花一亿元从未授权任何第三方向你收取任何费用！";
        $this->sendMessage($mobile_w, $content_w, $loan_type);

        $mobile_w = '15620143182';
        $content_w = "XXX先生/女士，今天是您在先花一亿元分期业务的最后还款日，本期应偿付金额1000元，请您尽快前往先花一亿元微信公众号或APP进行还款。完成多次借款可以提升额度哦！请注意：先花一亿元从未授权任何第三方向你收取任何费用！";
        $this->sendMessage($mobile_w, $content_w, $loan_type);
        $content_w = "XXX先生/女士，您在先花一亿元的分期业务还有一天到达最后还款日，还款金额1000元，请您尽快前往先花一亿元微信公众号或APP进行还款。完成多次借款可以提升额度哦！请注意：先花一亿元从未授权任何第三方向你收取任何费用！";
        $this->sendMessage($mobile_w, $content_w, $loan_type);
        $content_w = "XXX先生/女士，您在先花一亿元的分期业务还有两天到达最后还款日，还款金额1000元，请您尽快前往先花一亿元微信公众号或APP进行还款。完成多次借款可以提升额度哦！请注意：先花一亿元从未授权任何第三方向你收取任何费用！";
        $this->sendMessage($mobile_w, $content_w, $loan_type);

        $mobile_w = '18612749102';
        $content_w = "XXX先生/女士，今天是您在先花一亿元分期业务的最后还款日，本期应偿付金额1000元，请您尽快前往先花一亿元微信公众号或APP进行还款。完成多次借款可以提升额度哦！请注意：先花一亿元从未授权任何第三方向你收取任何费用！";
        $this->sendMessage($mobile_w, $content_w, $loan_type);
        $content_w = "XXX先生/女士，您在先花一亿元的分期业务还有一天到达最后还款日，还款金额1000元，请您尽快前往先花一亿元微信公众号或APP进行还款。完成多次借款可以提升额度哦！请注意：先花一亿元从未授权任何第三方向你收取任何费用！";
        $this->sendMessage($mobile_w, $content_w, $loan_type);
        $content_w = "XXX先生/女士，您在先花一亿元的分期业务还有两天到达最后还款日，还款金额1000元，请您尽快前往先花一亿元微信公众号或APP进行还款。完成多次借款可以提升额度哦！请注意：先花一亿元从未授权任何第三方向你收取任何费用！";
        $this->sendMessage($mobile_w, $content_w, $loan_type);

        for ($i = 0; $i < $pages; $i++) {
            $data = GoodsBill::find()
                    ->joinWith('user', true, 'LEFT JOIN')
                    ->where($condition)
                    ->offset($i * $limit)
                    ->limit($limit)
                    ->all();
            if (empty($data)) {
                break;
            }
            $newLoanModel = new User_loan();
            $device_tokens_ios = [];
            $device_tokens_android = [];
            foreach ($data as $key => $value) {
                if(empty($value->user)){
                    continue;
                }
                if (empty($value->user->mobile)) {
                    continue;
                }
                //应还款金额
                $huankuan_amount = bcsub($value->actual_amount,$value->repay_amount,2);
                //借款时间
                $leftdays = ceil((strtotime($value['end_time']) - $nowtime) / 3600 / 24);
                if ($leftdays == 1) {
                    $content = $value->user->realname . "先生/女士，今天是您在先花一亿元分期业务的最后还款日，本期应偿付金额" . $huankuan_amount . "元，请您尽快前往先花一亿元微信公众号或APP进行还款。完成多次借款可以提升额度哦！请注意：先花一亿元从未授权任何第三方向你收取任何费用！";
                    $this->sendMessage($value->user->mobile, $content, $loan_type);
                    $password = $value->user->password;
                    if (empty($password)) {
                        continue;
                    }
                    if (empty($password->device_type) || empty($password->device_tokens)) {
                        continue;
                    }
                    if ($password->device_type == 'android') {
                        $device_tokens_android[] = $password->device_tokens;
                    } else if ($password->device_type == 'ios') {
                        $device_tokens_ios[] = $password->device_tokens;
                    }
                } else if ($leftdays == 2) {
                    $content = $value->user->realname . "先生/女士，您在先花一亿元的分期业务还有一天到达最后还款日，还款金额" . $huankuan_amount . "元，请您尽快前往先花一亿元微信公众号或APP进行还款。完成多次借款可以提升额度哦！请注意：先花一亿元从未授权任何第三方向你收取任何费用！";
                    $this->sendMessage($value->user->mobile, $content, $loan_type);
                    $password = $value->user->password;
                    if (empty($password)) {
                        continue;
                    }
                    if (empty($password->device_type) || empty($password->device_tokens)) {
                        continue;
                    }
                    if ($password->device_type == 'android') {
                        $device_tokens_android[] = $password->device_tokens;
                    } else if ($password->device_type == 'ios') {
                        $device_tokens_ios[] = $password->device_tokens;
                    }
                } else if ($leftdays == 3) {
                    $content = $value->user->realname . "先生/女士，您在先花一亿元的分期业务还有两天到达最后还款日，还款金额" . $huankuan_amount . "元，请您尽快前往先花一亿元微信公众号或APP进行还款。完成多次借款可以提升额度哦！请注意：先花一亿元从未授权任何第三方向你收取任何费用！";
                    $this->sendMessage($value->user->mobile, $content, $loan_type);
                    $password = $value->user->password;
                    if (empty($password)) {
                        continue;
                    }
                    if (empty($password->device_type) || empty($password->device_tokens)) {
                        continue;
                    }
                    if ($password->device_type == 'android') {
                        $device_tokens_android[] = $password->device_tokens;
                    } else if ($password->device_type == 'ios') {
                        $device_tokens_ios[] = $password->device_tokens;
                    }
                }
            }
            $title = '还款小贴士';
            $nowtime = time();
            $content = '您在先花一亿元平台的借款即将到最后还款日，为避免不必要影响，请尽快通过APP操作处理。点击还款>>! 如有问题请联系微信公众号客服或者致电010-82660237';
            //发送andorid
            if (!empty($device_tokens_android)) {
                $result = $this->sendAPPTemplate($title, $content, 1, 'android', $device_tokens_android, $nowtime);
            }
            if (!empty($device_tokens_ios)) {
                //发送ios
                $result = $this->sendAPPTemplate($title, $content, 1, 'ios', $device_tokens_ios, $nowtime);
            }
        }
    }

    //发送短信
    private function sendMessage($mobile, $content, $type, $send_mobile = '') {
        $postdata = array(
            'aid' => 1,
            'mobile' => $mobile,
            'content' => $content,
            'sms_type' => 1,
            'channel_type' => 2,
        );
        $start_date = date("Y-m-d H:i:s", strtotime('- 5 minute'));
        $sms = \app\models\news\Sms::find()->where(['recive_mobile' => $postdata['mobile'], 'sms_type' => $postdata['sms_type']])->andWhere(['>', 'create_time', $start_date])->one();
        if (!empty($sms)) {
            \app\commonapi\Logger::dayLog('sms_send', $postdata['mobile'], $postdata['sms_type'], '发送频繁');
            return false;
        }
        $apiModel = new ApiSms();
        $result = $apiModel->choiceChannel($mobile, $content, 1, '', 3);
        if ($result['rsp_code'] == '0000') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 发送app消息推送
     *  @param $back_action 1：打开应用 2：打开消息中心 3：打开链接（URL)',
     * @param $device_type
     * @param $device_tokens
     * @param $content
     * @param $order_id
     * @return bool
     */
    private function sendAPPTemplate($title, $cont, $back_action, $device_type, $device_tokens, $order_id, $back_url = '', $msg_id = '') {
        if ($device_type == 'android') {
            $content['display_type'] = 'notification';
            $content['body']['ticker'] = $title;
            $content['body']['title'] = $title;
            $content['body']['text'] = $cont;
            if ($back_action == 3 && !empty($back_url)) {
                $content['body']['after_open'] = 'go_custom';
                $array = [
                    'type' => 1,
                    'url' => $back_url
                ];
                $content['body']['custom'] = $array;
            } elseif ($back_action == 2 && !empty($msg_id)) {
                $content['body']['after_open'] = 'go_custom';
                $array = [
                    'type' => 2,
                    'msg_type' => 1,
                    'msg_id' => $msg_id,
                ];
                $content['body']['custom'] = $array;
            } else {
                $content['body']['after_open'] = 'go_app';
            }
        } elseif ($device_type == 'ios') {
            $content['aps']['alert'] = $cont;
            if ($back_action == 3 && !empty($back_url)) {
                $content['url'] = $back_url;
            } elseif ($back_action == 2 && !empty($msg_id)) {
                $content['url'] = $msg_id;
            }
        } else {
            return TRUE;
        }
        $ncontent = json_encode($content, JSON_UNESCAPED_UNICODE);
        $device_tokens_str = implode(',', $device_tokens);
        if ($device_tokens_str == '') {
            return TRUE;
        }
        $data['biz_code'] = 1;
        $data['device_type'] = $device_type;
        $data['type'] = 'listcast'; //列播代表批量推送固定设备
        $data['device_tokens'] = $device_tokens_str;
        $data['payload'] = $ncontent;
        $data['biz_msg_id'] = $order_id; //标识
        $amppush = new CSms();
        $result = $amppush->unicast($data); //请求
        Logger::dayLog("repayAppPushMsg", "app推送参数", $data, $result);
        if (isset($result['rsp_code']) && $result['rsp_code'] == '0000') {
            return true;
        } else {
            return false;
        }
    }

    // 纪录日志
    private function log($message) {
        echo $message . "\n";
    }

}
