<?php
/**
 *
 * 精准营销定时--短信推送
 *    d:\xampp\php\php.exe d:\www\yiyiyuan\yii accurate
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/14
 * Time: 10:38
 */
namespace app\commands;
use app\commonapi\Common;
use app\commonapi\Http;
use app\commonapi\Logger;
use app\models\dev\Coupon_apply;
use app\models\dev\Sms;
use app\models\news\Accurate;
use app\models\news\User;
use yii\console\Controller;
use Yii;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');
class AccurateController extends Controller
{
    private $down_app = 'http://t.cn/R4K2tn5 ';
    private $accurate;

    public function actionIndex()
    {
        $this->accurate = new Accurate();
        $start = time();
        $this->logicUserId();
        $end = time() - $start;
        Logger::errorLog(print_r(array('total'=>$end), true), 'Incompletedatauser', 'precision');
    }
    private function logicUserId()
    {
        $start_time = date("Y-m-d 00:00:00", time());
        $end_time = date("Y-m-d H:i:s", time());
        $whereconfig = [
            'AND',
            ['is_coupon' => 0],
            [ 'sms_type'=>5],
            ['between', 'create_time', $start_time, $end_time],
            //['in', 'sms_type', [2, 5]] //未完成资料用户1天和14天
        ];
        $sql = Accurate::find()->where($whereconfig);
        $total = $sql->count();
        if ($total > 1000){
            $total = 1000;
        }
        $limit = 500;
        $pages = ceil($total / $limit);
        for($i=0; $i<$pages;$i++){
            $accurate_data = $sql->limit($limit)->asArray()->select('id, user_id, sms_type')->all();
            if (!empty($accurate_data)){
                $user_id_string = Common::ArrayToString($accurate_data, 'user_id');
                Accurate::updateAll(['is_coupon' => -1], ['is_coupon' => 0, 'user_id' => explode(',', $user_id_string)]);
                foreach($accurate_data as $value){
                    $user_info = $this->getUserInfo($value['user_id']);
                    if (empty($user_info))
                        continue;
                    $message_coupon_info = $this->formatMessage($value['sms_type'], $user_info);
                    $accurate = new Accurate();
                    $sendsms_ret = $this->sendSms($user_info->mobile, $message_coupon_info['sms_message']);
                    //$sendsms_ret = $this->sendSms(18911532550, $message_coupon_info['sms_message']);exit;
                    if ($sendsms_ret == 100){
                        $accurate->updateIsCoupon($value['id'], 1);
                        if (!empty($message_coupon_info['coupon_list'])) {
                            $coupon_title = $message_coupon_info['coupon_list']['money'] . "元优惠券";
                            $coupon_apply = new Coupon_apply();
                            $coupon_apply->sendcouponactivity($value['user_id'], $coupon_title, 2, $message_coupon_info['coupon_list']['days'], $message_coupon_info['coupon_list']['money']);

                        }
                    }
                }
            }
        }
    }

    /**
     * 获取用户信息
     * @param $user_id
     * @return array|bool|null|\yii\db\ActiveRecord
     */
    private function getUserInfo($user_id)
    {
        if (empty($user_id)) return false;
        $user_info = User::find()->where(['user_id'=>$user_id])->one();
        if (!empty($user_id)){
            return $user_info;
        }
        return false;
    }

    /**
     * 短信内容格式
     * @param $code
     * @param $user_info
     * @return array
     */
    private function formatMessage($code, $user_info)
    {
        $amount = $user_info->getUserLoanAmount($user_info);
        $msg_code_data = [];
        switch($code) {
            //1.未完成资料
            case 1://30分钟
                //$msg = "【先花一亿元】亲，您在资料提交过程中遇到问题了吗？提交完就能得到1500元借款额度了哦！提交时如仍有疑问，您可联系客服处理";
                $msg = "【先花一亿元】亲，您在资料提交过程中遇到问题了吗？提交完就能得到1500元借款额度了哦！提交时如仍有疑问，您可联系客服{$this->down_app}，退订回T";
                $msg_code_data = ['sms_message'=>$msg];
                break;
            case 2://1天
                //$msg = "【先花一亿元】亲，您的1500元贷款额度已到账，补充资料即可领取，立即领取：{$this->down_app}提交时如仍有疑问，您可通过APP联系客服处理，退订回T";
                $msg = "【先花一亿元】亲，您的1500元贷款额度已到账，补充资料即可领取，立即领取：{$this->down_app}，提交时如仍有疑问，您可通过APP联系客服处理，退订回T";
                $msg_code_data = ['sms_message'=>$msg];
                break;
            case 3://3天
                //$msg = "【先花一亿元】亲，您的5元免息券和1500元贷款额度已到账，有效期3天，补充资料即可提现，立即领取：{$this->down_app}退订回T";
                $msg = "【先花一亿元】亲，您的5元免息券和1500元贷款额度已到账，有效期3天，补充资料即可提现，立即领取：{$this->down_app}退订回T";
                $msg_code_data = ['sms_message'=>$msg,'coupon_list'=>['money'=>5, 'days'=>3]];
                break;
            case 4://7天
                //$msg  = "【先花一亿元】亲，您的10元免息券和1500元贷款额度已到账，有效期3天，补充资料即可提现，立即领取：{$this->down_app}退订回T";
                $msg = "【先花一亿元】亲，您的10元免息券和1500元贷款额度已到账，有效期3天，补充资料即可提现，立即领取：{$this->down_app}，退订回T";
                $msg_code_data = ['sms_message'=>$msg,'coupon_list'=>['money'=>10, 'days'=>3]];
                break;
            case 5:///14天
                //$msg  = "【先花一亿元】亲，您的20元免息券和1500元贷款额度已到账，有效期3天，补充资料即可提现，立即领取：{$this->down_app}退订回T";
                $msg = "【先花一亿元】亲，您的20元免息券和1500元贷款额度已到账，有效期3天，补充资料即可提现，立即领取：{$this->down_app} ，退订回T";
                $msg_code_data = ['sms_message'=>$msg,'coupon_list'=>['money'=>20, 'days'=>3]];
                break;
            case 6://30天
                //$msg  = "【先花一亿元】亲，您的30元免息券和1500元贷款额度已到账，有效期3天，补充资料即可提现，立即领取：{$this->down_app}退订回T";
                $msg = "【先花一亿元】亲，您的30元免息券和1500元贷款额度已到账，有效期3天，补充资料即可提现，立即领取：{$this->down_app}，退订回T";
                $msg_code_data = ['sms_message'=>$msg,'coupon_list'=>['money'=>30, 'days'=>3]];
                break;
            //未完成首次借款（驳回用户召回）
            case 7://1天
                //$msg = "【先花一亿元】亲，您有一笔1500元的贷款额度可以提现，当前有效，赶紧领取：{$this->down_app}退订回T";
                $msg = "【先花一亿元】亲，您有一笔1500元的贷款额度可以提现，当前有效，赶紧领取：{$this->down_app}，退订回T";
                $msg_code_data = ['sms_message'=>$msg];
                break;
            case 8:// //3天
                //$msg = "【先花一亿元】亲，30元免息已收到，领取1500元的贷款额度即可免息，赶紧领取：{$this->down_app}退订回T";
                $msg = "【先花一亿元】亲，30元免息已收到，领取1500元的贷款额度即可免息，赶紧领取：{$this->down_app}，退订回T";
                $msg_code_data = ['sms_message'=>$msg, 'coupon_list'=>['money'=>30, 'days'=>7]];
                break;
            case 9://5天
                //$msg = "【先花一亿元】尊敬的{$user_info->realname}，您的1500元现金和30元免息券尚未领取，赶紧领取：{$this->down_app}退订回T";
                $msg = "【先花一亿元】尊敬的{$user_info->realname}，您的1500元现金和30元免息券尚未领取，赶紧领取：{$this->down_app}，退订回T";
                $msg_code_data = ['sms_message'=>$msg];
                break;
            case 10://14天
                //$msg  = "【先花一亿元】尊敬的{$user_info->realname}，老用户60元免息券已到账 ，领取1500元的贷款额度即可免息，赶紧领取：{$this->down_app}退订回T";
                $msg = "【先花一亿元】尊敬的{$user_info->realname}，老用户60元免息券已到账 ，领取1500元的贷款额度即可免息，赶紧领取：{$this->down_app}，退订回T";
                $msg_code_data = ['sms_message'=>$msg, 'coupon_list'=>['money'=>60, 'days'=>7]];
                break;
            case 11://30天
                //$msg  = "【先花一亿元】尊敬的{$user_info->realname}，老用户80元免息券已到账 ，领取1500元的贷款额度即可免息，赶紧领取：{$this->down_app}退订回T";
                $msg = "【先花一亿元】尊敬的{$user_info->realname}，老用户80元免息券已到账 ，领取1500元的贷款额度即可免息，赶紧领取：{$this->down_app}，退订回T";
                $msg_code_data = ['sms_message'=>$msg, 'coupon_list'=>['money'=>80, 'days'=>7]];
                break;
            case 12://90天
                //$msg  = "【先花一亿元】尊敬的{$user_info->realname}，老用户100元免息券已到账 ，领取1500元的贷款额度即可免息，赶紧领取：{$this->down_app}退订回T";
                $msg = "【先花一亿元】尊敬的{$user_info->realname}，老用户100元免息券已到账 ，领取1500元的贷款额度即可免息，赶紧领取：{$this->down_app}，退订回T";
                $msg_code_data = ['sms_message'=>$msg, 'coupon_list'=>['money'=>100, 'days'=>7]];
                break;
            //长时间未借款
            case 13://1天
                //$msg = "【先花一亿元】亲，您有一笔{$user_info->account->amount}元的贷款额度可以提现，当前有效，赶紧领取：{$this->down_app}退订回T";
                $msg = "【先花一亿元】亲，您有一笔{$amount}元的贷款额度可以提现，当前有效，赶紧领取：{$this->down_app}，退订回T";
                $msg_code_data = ['sms_message'=>$msg];
                break;
            case 14:// //3天
                //$msg = "【先花一亿元】亲，30元免息已收到，领取{$user_info->account->amount}元的贷款额度即可免息，赶紧领取：{$this->down_app }退订回T";
                $msg = "【先花一亿元】亲，30元免息已收到，领取{$amount}元的贷款额度即可免息，赶紧领取：{$this->down_app}，退订回T";
                $msg_code_data = ['sms_message'=>$msg, 'coupon_list'=>['money'=>30, 'days'=>7]];
                break;
            case 15://5天
                //$msg = "【先花一亿元】尊敬的{$user_info->realname}，您的{$user_info->account->amount}元现金和30元免息券尚未领取，赶紧领取：{$this->down_app }退订回T";
                $msg = "【先花一亿元】尊敬的{$user_info->realname}，您的{$amount}元现金和30元免息券尚未领取，赶紧领取：h{$this->down_app}退订回T";
                $msg_code_data = ['sms_message'=>$msg];
                break;
            case 16://14天
                //$msg  = "【先花一亿元】尊敬的{$user_info->realname}，老用户60元免息券已到账 ，领取{$user_info->account->amount}元的贷款额度即可免息，赶紧领取：{$this->down_app }退订回T";
                $msg = "【先花一亿元】尊敬的{$user_info->realname}，老用户60元免息券已到账 ，领取{$amount}元的贷款额度即可免息，赶紧领取：{$this->down_app}，退订回T";
                $msg_code_data = ['sms_message'=>$msg, 'coupon_list'=>['money'=>60, 'days'=>7]];
                break;
            case 17://30天
                //$msg  = "【先花一亿元】尊敬的{$user_info->realname}，老用户80元免息券已到账 ，领取{$user_info->account->amount}元的贷款额度即可免息，赶紧领取：{$this->down_app }退订回T";
                $msg = "【先花一亿元】尊敬的{$user_info->realname}，老用户80元免息券已到账 ，领取{$amount}元的贷款额度即可免息，赶紧领取：{$this->down_app}，退订回T";
                $msg_code_data = ['sms_message'=>$msg, 'coupon_list'=>['money'=>80, 'days'=>7]];
                break;
            case 18://90天
                //$msg  = "【先花一亿元】尊敬的{$user_info->realname}，老用户100元免息券已到账 ，领取{$user_info->account->amount}元的贷款额度即可免息，赶紧领取：{$this->down_app }退订回T";
                $msg = "【先花一亿元】尊敬的{$user_info->realname}，老用户100元免息券已到账 ，领取{$amount}元的贷款额度即可免息，赶紧领取：{$this->down_app}，退订回T";
                $msg_code_data = ['sms_message'=>$msg, 'coupon_list'=>['money'=>100, 'days'=>7]];
                break;
        }
        return $msg_code_data;
    }

    /**
     * 发送短信
     * @param $mobile
     * @param $content
     * @return mixed
     */
    private function sendSms($mobile, $content)
    {
        $data = [
            'uid'=>'501312',
            'pwd'=>md5('xi12yi32g'),
            'extnum'=>'1214',
            'encode'=>'GBK',
            'mobile'=>$mobile, //可以是多个13900008888,13900009999,13100006666,0218882228
        ];
        $content_gbk =  iconv("utf-8", "GBK", $content);
        //$url = 'http://202.85.221.42:9883/c123/sendsms?'.http_build_query($data).'&content='.$content_gbk;
        //$ret = Http::getCurl($url);
        //var_dump($ret);exit;
        $ret = Http::interface_post("http://202.85.221.42:9883/c123/sendsms", http_build_query($data).'&content='.$content_gbk);
        Logger::errorLog(print_r(array('sms_code'=>$ret), true), 'Accurate', 'precision');
        if ($ret == 100){
            $sms = new Sms();
            $sms->addSms($mobile, $content, 41, '');
        }
        return $ret;
    }

}