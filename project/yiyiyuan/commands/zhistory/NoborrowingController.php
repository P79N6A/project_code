<?php
/**
 * 精准营销定时--2、未完成首次借款（驳回用户召回）(短信类型43：未完成首次借款（驳回用户召回）)
 * d:\xampp\php\php.exe d:\www\yiyiyuan\yii noborrowing
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/13
 * Time: 20:04
 */
namespace app\commands;
use app\models\news\Accurate;
use app\models\news\AccurateDevice;
use app\models\news\AccurateOpenId;
use app\models\news\User;
use app\models\dev\User_loan;
use app\commonapi\Logger;
use Yii;
use yii\console\Controller;
// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');
class NoborrowingController extends Controller
{
    private $down_app = 'http://t.cn/R4K2tn5 ';
    private $accurate_open_id;
    private $accurate_device;
    private $accurate;
    
    
    public function actionIndex()
    {
        $start = time();
        //$this->getFlowsData(1);
        //$this->getFlowsData(2);
        $this->getFlowsData(3);
        $this->getFlowsData(4);
        $this->getFlowsData(5);
        $this->getFlowsData(6);
        $end = time() - $start;
        Logger::errorLog(print_r(array('total'=>$end), true), 'Incompletedatauser', 'precision');
    }

    /**
     * 取出限制时间内的用户
     * 逻辑：
     *      1.条件yi_user_loan_flows表 (loan_status=7 AND (admin >0 or admin =-2) or loan_status=3) and create_ttime between start and end
     *      2.去重yi_user_loan表中 distinct user_id
     * @param $day_code
     * @return array
     */
    private function getFlowsData($day_code)
    {
        $day_code_data = [1, 2, 3, 4, 5, 6];
        if (in_array($day_code, $day_code_data)) {
            $create_time = $this->timeLimit($day_code, 'flows.create_time');
            $this->accurate_open_id = new AccurateOpenId();
            $this->accurate_device = new AccurateDevice();
            $this->accurate = new Accurate();
            $sql = "SELECT count(distinct loan.user_id) as count FROM yi_user_loan_flows as flows " .
                            "INNER JOIN yi_user_loan as loan on(loan.loan_id=flows.loan_id) " .
                            "WHERE ((flows.loan_status=7 " .
                            "AND (flows.admin_id > 0 OR flows.admin_id = -2)) " .
                            "OR flows.loan_status=3) " .$create_time. " LIMIT 30000";
            
            $total = Yii::$app->db->createCommand($sql)->queryOne();
            $limit = 500;
            $pages = ceil($total['count'] / $limit);
            for ($i = 0; $i < $pages; $i++) {
                $offset = ($i != 0) ? "OFFSET ".$i*$limit : '';
                $flows_sql = "SELECT distinct loan.user_id as user_id FROM yi_user_loan_flows as flows " .
                                            "INNER JOIN yi_user_loan as loan on(loan.loan_id=flows.loan_id) " .
                                            "WHERE ((flows.loan_status=7 " .
                                            "AND (flows.admin_id > 0 OR flows.admin_id = -2)) " .
                                            "OR flows.loan_status=3) " . $create_time . " LIMIT $limit ".$offset;
                $user_flows = Yii::$app->db->createCommand($flows_sql)->queryAll();
                if (!empty($user_flows)){
                    foreach($user_flows as $value){
                        $loan_user_data = $this->getLoanUserData($day_code, $value['user_id']);
                        if (!$loan_user_data){
                            continue;
                        }
                        $user_info = $this->getUserInfo($value['user_id']);
                        if (!$user_info)
                            continue;
                        $this->panduanType($day_code, $user_info);
                    }
                }
            }
        }
    }

    /**
     * 取出user_id对应的借款
     * 逻辑：
     *      1条件：yi_user_loan: user_id AND status in(5,6,8,9,11,12,13) AND create_time between cur_time AND 时间点
     * @param $day_code
     * @param $user_id
     * @return bool
     */
    private function getLoanUserData($day_code, $user_id)
    {
        $time_limit = $this->timeLimit($day_code, 'create_time', 2);
        $whereconfig = [
            'AND',
            ['user_id'=>$user_id],
            $time_limit
        ];
        $user_loan = User_loan::find()->where($whereconfig)->count();
        if ($user_loan == 0){
            return true;
        }
        return false;

    }

    /**
     * 通用用户id取出用户信息
     * @param $user_id
     * @return array|null|\yii\db\ActiveRecord
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
     * @param int $limit_type 1返回一天的时间（字符串）， 2返回当前到某一时间点(数组)
     * @return array|string
     */
    private function timeLimit($limit_code, $field_value, $limit_type = 1)
    {
        $time_limit_data = '';
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
            $time_limit_data = "AND $field_value between '$start_time' AND '$end_time'";
        }
        if (!empty($last_n_day) && $limit_type == 2) {
            $cur_time = date("Y-m-d H:i:s", time());
            $end_time = date("Y-m-d 23:59:59", $last_n_day);
            $time_limit_data = ['between', $field_value, $cur_time, $end_time];
        }
        return $time_limit_data;
    }

    /**
     * 短信格式
     * @param $code
     * @param $username
     * @return array
     */
    private function formatMessage($code, $username)
    {
        $msg_code_data = [];
        switch($code) {
            case 1://1天
                $msg = "亲，您有一笔1500元的贷款额度可以提现，当前有效，赶紧领取：{$this->down_app}退订回T";
                $msg_code_data = ['sms_message'=>$msg];
                break;
            case 2:// //3天
                $msg = "亲，30元免息已收到，领取1500元的贷款额度即可免息，赶紧领取：{$this->down_app}退订回T";
                $msg_code_data = ['sms_message'=>$msg, 'coupon_list'=>['money'=>30, 'days'=>7]];
                break;
            case 3://5天
                $msg = "尊敬的{$username}，您的1500元现金和30元免息券尚未领取，赶紧领取：{$this->down_app}退订回T";
                $msg_code_data = ['sms_message'=>$msg];
                break;
            case 4://14天
                $msg  = "尊敬的{$username}，老用户60元免息券已到账 ，领取1500元的贷款额度即可免息，赶紧领取：{$this->down_app}退订回T";
                $msg_code_data = ['sms_message'=>$msg, 'coupon_list'=>['money'=>60, 'days'=>7]];
                break;
            case 5://30天
                $msg  = "尊敬的{$username}，老用户80元免息券已到账 ，领取1500元的贷款额度即可免息，赶紧领取：{$this->down_app}退订回T";
                $msg_code_data = ['sms_message'=>$msg, 'coupon_list'=>['money'=>80, 'days'=>7]];
                break;
            case 6://90天
                $msg  = "尊敬的{$username}，老用户100元免息券已到账 ，领取1500元的贷款额度即可免息，赶紧领取：{$this->down_app}退订回T";
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
        $day_code = $day_code + 6;
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