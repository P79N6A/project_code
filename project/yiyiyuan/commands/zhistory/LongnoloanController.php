<?php
/**
 *
 * 精准营销定时--3、长时间未借款(短信类型44：长时间未借款)
 *    d:\xampp\php\php.exe d:\www\yiyiyuan\yii longnoloan
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/14
 * Time: 10:38
 */
namespace app\commands;
use app\models\news\Accurate;
use app\models\news\AccurateDevice;
use app\models\news\AccurateOpenId;
use app\models\news\User;
use app\models\news\User_loan;
use app\commonapi\Logger;
use yii\console\Controller;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');
class LongnoloanController extends Controller
{
    private $down_app = 'http://t.cn/R4K2tn5 ';

    public function actionIndex()
    {
        $start = time();
        $this->getLoanUserData(1);
        $this->getLoanUserData(2);
        $this->getLoanUserData(3);
        $this->getLoanUserData(4);
        $this->getLoanUserData(5);
        $this->getLoanUserData(6);
        $end = time() - $start;
        Logger::errorLog(print_r(array('total'=>$end), true), 'Incompletedatauser', 'precision');
    }

    /**
     *
     * @param $day_code
     */
    private function getLoanUserData($day_code)
    {
        $day_code_data = [1, 2, 3, 4, 5, 6];
        if (in_array($day_code, $day_code_data)) {
            $time_limit = $this->timeLimit($day_code, 'repay_time');
            $whereconfig = [
                'AND',
                ['status' => 8],
                $time_limit
            ];
            $sql = User_loan::find()->select("user_id")->distinct()->where($whereconfig);
            $total = $sql->count();
            $limit = 500;
            $pages = ceil($total / $limit);
            for ($i = 0; $i < $pages; $i++) {
                $user_loan = $sql->offset($i * $limit)->limit($limit)->all();
                if (!empty($user_loan)) {
                    foreach ($user_loan as $value) {
                        $loan_data = $this->getLoanInfo($day_code, $value->user_id);
                        if ($loan_data) {
                            $user_info = $this->getUserInfo($value->user_id);
                            if (!$user_info)
                                continue;
                            $this->panduanType($day_code, $user_info);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $day_code
     * @param $user_id
     * @return bool
     */
    private function getLoanInfo($day_code, $user_id)
    {
        $time_limit = $this->timeLimit($day_code, 'create_time', 2);
        $whereconfig = [
            'AND',
            ['user_id'=>$user_id],
            $time_limit
        ];
        $loan_info = User_loan::find()->where($whereconfig)->count();
        if ($loan_info == 0)
            return true;
        return false;
    }

    /**
     * 获取用户信息
     * @param $user_id
     * @return array|bool|null|\yii\db\ActiveRecord
     */
    private function getUserInfo($user_id)
    {
        if (empty($user_id)) return [];
        $user_info = User::find()->where(['user_id'=>$user_id])->one();
        if (!empty($user_info))
            return $user_info;
        return [];
    }

    /**
     * 时间限制
     * @param $limit_code
     * @param $field_value
     * @param int $limit_type
     * @return array
     */
    private function timeLimit($limit_code, $field_value, $limit_type = 1)
    {
        $time_limit_data = [];
        switch ($limit_code){
            case 1:  //1天
                $last_n_day = strtotime("-1 days");
                break;
            case 2:  //3天
                $last_n_day = strtotime("-3 days");
                break;
            case 3:  //5天
                $last_n_day = strtotime("-5 days");
                break;
            case 4:  //14天
                $last_n_day = strtotime("-14 days");
                break;
            case 5:  //30天
                $last_n_day = strtotime("-30 days");
                break;
            case 6:  //90天
                $last_n_day = strtotime("-90 days");
                break;
        }
        if (!empty($last_n_day) && $limit_type == 1) {
            $start_time = date("Y-m-d 00:00:00", $last_n_day);
            $end_time = date("Y-m-d 23:59:59", $last_n_day);
            $time_limit_data = ['between', $field_value, $start_time, $end_time];
        }
        if (!empty($last_n_day) && $limit_type == 2) {
            $cur_time = date("Y-m-d 00:00:00", time());
            $end_time = date("Y-m-d 23:59:59", $last_n_day);
            $time_limit_data = ['between', $field_value, $cur_time, $end_time];
        }
        return $time_limit_data;
    }

    /**
     * 短信格式
     * @param $code
     * @param $username
     * @param $loan_money  贷款额度
     * @return array
     */
    private function formatMessage($code, $username, $loan_money)
    {
        $msg_code_data = [];
        switch($code) {
            case 1://1天
                $msg = "亲，您有一笔{$loan_money}元的贷款额度可以提现，当前有效，赶紧领取：{$this->down_app }退订回T";
                $msg_code_data = ['sms_message'=>$msg];
                break;
            case 2:// //3天
                $msg = "亲，30元免息已收到，领取{$loan_money}元的贷款额度即可免息，赶紧领取：{$this->down_app }退订回T";
                $msg_code_data = ['sms_message'=>$msg, 'coupon_list'=>['money'=>30, 'days'=>7]];
                break;
            case 3://5天
                $msg = "尊敬的{$username}，您的{$loan_money}元现金和30元免息券尚未领取，赶紧领取：{$this->down_app }退订回T";
                $msg_code_data = ['sms_message'=>$msg];
                break;
            case 4://14天
                $msg  = "尊敬的{$username}，老用户60元免息券已到账 ，领取{$loan_money}元的贷款额度即可免息，赶紧领取：{$this->down_app }退订回T";
                $msg_code_data = ['sms_message'=>$msg, 'coupon_list'=>['money'=>60, 'days'=>7]];
                break;
            case 5://30天
                $msg  = "尊敬的{$username}，老用户80元免息券已到账 ，领取{$loan_money}元的贷款额度即可免息，赶紧领取：{$this->down_app }退订回T";
                $msg_code_data = ['sms_message'=>$msg, 'coupon_list'=>['money'=>80, 'days'=>7]];
                break;
            case 6://90天
                $msg  = "尊敬的{$username}，老用户100元免息券已到账 ，领取{$loan_money}元的贷款额度即可免息，赶紧领取：{$this->down_app }退订回T";
                $msg_code_data = ['sms_message'=>$msg, 'coupon_list'=>['money'=>100, 'days'=>7]];
                break;
        }
        return $msg_code_data;
    }

    /**
     * 接收用户信息判断类型插入相应表
     * @param $day_code
     * @param $user_info
     * @return bool
     */
    private function panduanType($day_code, $user_info)
    {
        $day_code = $day_code + 6 + 6;
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
}