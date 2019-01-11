<?php
/**
 * 精准营销定时--1.未完成资料(短信类型41：精准营销用户状态不为3和5的短信内容（未完成资料）)
 *      d:\xampp\php\php.exe d:\www\yiyiyuan_short\yii incompletedatauser
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/13
 * Time: 15:55
 */
namespace app\commands;
use app\models\news\Accurate;
use app\models\news\AccurateDevice;
use app\models\news\AccurateOpenId;
use app\models\news\User;
use app\commonapi\Logger;
use Yii;
use yii\console\Controller;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');
class IncompletedatauserController extends Controller
{
    private $down_app = 'http://t.cn/R4K2tn5 ';

    public function actionIndex()
    {
        $start = time();
        $this->getUserData(2);
        $this->getUserData(3);
        $this->getUserData(4);
        $this->getUserData(5);
        $this->getUserData(6);
        $end = time() - $start;
        Logger::errorLog(print_r(array('total'=>$end), true), 'Incompletedatauser', 'precision');
    }

    /**
     * 取所有符合条件的用户
     * 逻辑：
     *      1.用户status为不为3和5时
     * @param $day_code
     */
    private function getUserData($day_code)
    {
        $day_code_data = [1, 2, 3, 4, 5, 6];
        if (in_array($day_code, $day_code_data)) {
            $timeLimit = $this->timeLimit($day_code, 'create_time');
            $whereconfig = [
                'AND',
                ['not in', 'status', [3, 5]], //
                $timeLimit
            ];
            $sql = User::find()->where($whereconfig);
            $total = $sql->count();
            $limit = 500;
            $pages = ceil($total / $limit);
            for ($i = 0; $i < $pages; $i++) {
                $user_info = $sql->offset($i * $limit)->limit($limit)->all();
                if (!empty($user_info)){
                    foreach($user_info as $value){
                        $this->panduanType($day_code, $value);
                    }
                }
            }
        }
    }

    /**
     * 时间限制
     * @param $limit_code
     * @param $field_value
     * @return array
     */
    private function timeLimit($limit_code, $field_value)
    {
        $time_limit_data = [];
        switch ($limit_code){
            case 1:  //30分
                $last_n_day = strtotime("-30 minutes");
                break;
            case 2:  //1天
                $last_n_day = strtotime("-1 days");
                break;
            case 3:  //3天
                $last_n_day = strtotime("-3 days");
                break;
            case 4:  //7天
                $last_n_day = strtotime("-7 days");
                break;
            case 5:  //14天
                $last_n_day = strtotime("-14 days");
                break;
            case 6:  //30天
                $last_n_day = strtotime("-30 days");
                break;
        }
        if (!empty($last_n_day)) {
            $start_time = date("Y-m-d 00:00:00", $last_n_day);
            $end_time = date("Y-m-d 23:59:59", $last_n_day);
            $time_limit_data = ['between', $field_value, $start_time, $end_time];
        }
        return $time_limit_data;
    }

    /**
     * 接收用户信息判断类型插入相应表
     * @param $day_code
     * @param $user_info
     * @return bool
     */
    private function panduanType($day_code, $user_info)
    {
        if (empty($user_info)) return false;
        if (!empty($user_info->openid)){
            $open_id_data = ['user_id'=>$user_info->user_id, 'openid'=>$user_info->openid, 'sms_type'=>$day_code];
            $accurate_open_id = new AccurateOpenId();
            return $accurate_open_id->addList($open_id_data);
        }
        //判断device
        if (!empty($user_info->password->device_tokens) && !empty($user_info->password->device_type)){
            $device_data = [
                'user_id'=>$user_info->user_id,
                'device_tokens'=>$user_info->password->device_tokens,
                'device_type'=>$user_info->password->device_type,
                'sms_type'=>$day_code
            ];
            $accurate_device = new AccurateDevice();
            return $accurate_device->addList($device_data);
        }
        $accurate = new Accurate();
        return $accurate->addList(['user_id'=>$user_info->user_id, 'sms_type'=>$day_code]);
    }
    /**
     * 短信通知内容
     * @param $code
     * @return array
     */
    private function formatMessage($code)
    {
        $msg_code_data = [];
        switch($code) {
            case 1://30分钟
                $msg = "亲，您在资料提交过程中遇到问题了吗？提交完就能得到1500元借款额度了哦！提交时如仍有疑问，您可联系客服处理";
                $msg_code_data = ['sms_message'=>$msg];
                break;
            case 2://1天
                $msg = "亲，您的1500元贷款额度已到账，补充资料即可领取，立即领取：{$this->down_app}提交时如仍有疑问，您可通过APP联系客服处理，退订回T";
                $msg_code_data = ['sms_message'=>$msg];
                break;
            case 3://3天
                $msg = "亲，您的5元免息券和1500元贷款额度已到账，有效期3天，补充资料即可提现，立即领取：{$this->down_app}退订回T";
                $msg_code_data = ['sms_message'=>$msg,'coupon_list'=>['money'=>5, 'days'=>3]];
                break;
            case 4://7天
                $msg  = "亲，您的10元免息券和1500元贷款额度已到账，有效期3天，补充资料即可提现，立即领取：{$this->down_app}退订回T";
                $msg_code_data = ['sms_message'=>$msg,'coupon_list'=>['money'=>10, 'days'=>3]];
                break;
            case 5:///14天
                $msg  = "亲，您的20元免息券和1500元贷款额度已到账，有效期3天，补充资料即可提现，立即领取：{$this->down_app}退订回T";
                $msg_code_data = ['sms_message'=>$msg,'coupon_list'=>['money'=>20, 'days'=>3]];
                break;
            case 6://30天
                $msg  = "亲，您的30元免息券和1500元贷款额度已到账，有效期3天，补充资料即可提现，立即领取：{$this->down_app}退订回T";
                $msg_code_data = ['sms_message'=>$msg,'coupon_list'=>['money'=>30, 'days'=>3]];
                break;
        }
        return $msg_code_data;
    }

    
}