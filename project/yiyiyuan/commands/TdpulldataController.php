<?php

namespace app\commands;

use Yii;
use app\common\Curl;
use app\commonapi\Common;
use app\commonapi\Logger;
use app\common\TdIvrService;
use yii\console\Controller;
use app\models\news\User;
use app\models\news\User_loan;
use app\models\news\TdReport;
use yii\helpers\ArrayHelper;

/**
同盾数据推送任务
 * 每小时执行一次
 * windows d:\phpStudy\php\php-5.5.38\php.exe d:\phpStudy\WWW\yiyiyuan_short\yii tdpulldata/report
 * linux  /data/wwwroot/guide/yii tdpulldata/sendinfo
 */
set_time_limit(0);
ini_set('memory_limit', '-1');

class TdpulldataController extends Controller {

    //获取要发送语音的用户
    public function actionAddsendtdinfo(){
        $limit      = 500; //每次循环处理数量
        $start_time = date('Y-m-d', time());
        $end_time = date('Y-m-d', time() + 86400 * 1);//推送还有两天到期的
        $whereconfig = [
            'AND',
            ['status' => 9],
            ['<=', 'end_date', $end_time],
            ['>', 'end_date', $start_time],
            ['IN', 'business_type', array(1,4,5,6)],
        ];
        $total = User_loan::find()->where($whereconfig)->count();
        $pages = ceil($total / $limit);
        for ($i = 0; $i < $pages; $i++) {
            $userLoan = User_loan::find()->where($whereconfig)->offset($i * $limit)->limit($limit)->all();
            if (empty($userLoan)) {
                break;
            }
            foreach ($userLoan as $key => $val){
                $data = [
                    'loan_id' => $val['loan_id'],
                    'user_id' => $val['user_id'],
                    'report_status' => 4,//初始状态
                ];
                $td_info = TdReport::find()->where($data)->one();
                if(!empty($td_info)){
                    continue;
                }
                $save = (new TdReport())->addinit($data);
                if (!$save) {
                    Logger::dayLog('td/add_pull', 'error：保存要发送语音用户失败:user_id : ' . $val->user_id . PHP_EOL);
                }
            }
        }
    }

    //发送请求获取同盾id
    public function actionSendinfo() {
        $limit = 500; //每次循环处理数量
        $start_date = date('Y-m-d');
        $whereconfig = [
            'AND',
            [TdReport::tableName() . '.report_status' => [4,-3,-1,-5]],
            ['>', TdReport::tableName() . '.create_time', $start_date]
        ];
        $TdReport = TdReport::find()->where($whereconfig);
        $total = $TdReport->count();
        $pages = ceil($total / $limit);
        for ($i = 0; $i < $pages; $i++) {
            $td_report = $TdReport->offset($i * $limit)->limit($limit)->all();
            if (empty($td_report)) {
                break;
            }
            $td_id_data = ArrayHelper::getColumn($td_report,'id');
            TdReport::updateAll(['report_status' => 2], ['id' => $td_id_data]);
            $ivrModel = new TdIvrService();
            $loanModel = new User_loan();
            foreach ($td_report as $key => $val) {
                if (empty($val)){
                    continue;
                }
                $userinfo = User::find()->where(['user_id' => $val->user_id])->one();
                $loaninfo = User_loan::find()->where(['loan_id' => $val->loan_id])->one();
                if($loaninfo->status == 8){
                    $data = [
                        'id' => $val->id,
                        'report_status' => -5,
                    ];
                    $re = $val->upstatus($data);
                    if(!$re){
                        Logger::dayLog('td/repay', 'error : '. $val->loan_id .'修改还款状态失败'. PHP_EOL);
                    }
                }else{
                    $amount = $loanModel->getRepaymentAmount($loaninfo);
                    $payment_date = date('Y-m-d', strtotime($loaninfo->end_date."-1 day"));
                    $postData = [
                        'customer_name' => $userinfo->realname, //姓名
                        'customer_id_number' => $userinfo->identity, //身份证号
                        'phone_num' => $userinfo->mobile, //手机
                        'bill_payment_amount' => $amount, //应还款金额
                        'payment_date' => $payment_date,//到期还款日
                        'product_name' => '先花一亿元',//产品名称
                    ];
                    //推送数据
                    $res = $ivrModel->pull_data($postData);
                    Logger::dayLog('td/send_return', '用户id : '.$val->user_id.'原始同盾信息 : '. $res.  PHP_EOL);
                    $result = json_decode($res, TRUE);
                    if (isset($result['success']) && $result['success'] == true) {
                        //保存同盾信息表 成功后保存返回的collection_id
                        $data = [
                            'id' => $val->id,
                            'collection_id' => $result['collection_id'],
                            'product_source' => '1',
                            'report_status' => -3,
                        ];
                        $save = $val->upData($data);
                        if (!$save) {
                            Logger::dayLog('td/add_return', 'error：保存同盾id失败:loan_id : '. $val->loan_id . PHP_EOL);
                        }
                    }else{
                        $data = [
                            'id' => $val->id,
                            'report_status' => -1,
                        ];
                        $val->upstatus($data);
                        Logger::dayLog('td/pull', 'error : '. $result['reason_desc'] . PHP_EOL);
                    }
                }
            }
        }
        TdReport::updateAll(['report_status'=>5],['report_status'=>-5]);
        TdReport::updateAll(['report_status'=>3],['report_status'=>-3]);
        TdReport::updateAll(['report_status'=>1],['report_status'=>-1]);
    }

    //获取同盾单个数据信息
    //晚9点执行当天推送的结果
    public function actionReport() {
        //找出符合条件的同盾id
        $limit = 200; //每次循环处理数量
        $start_date = date('Y-m-d', strtotime('-3 day'));
        $ivrModel = new TdIvrService();
        $whereconfig = [
            'AND',
            [TdReport::tableName() . '.report_status' => [3,-6,7]],
            ['>' , 'modify_time',$start_date]
        ];
        $TdReport = TdReport::find()->where($whereconfig);
        $total = $TdReport->count();
        $pages = ceil($total / $limit);
        for ($i = 0; $i < $pages; $i++) {
            $td_report = $TdReport->offset($i * $limit)->limit($limit)->all();
            if (empty($td_report)) {
                break;
            }
            foreach ($td_report as $key => $val) {
                $postData = ['collection_id' => $val->collection_id];
                //获取数据
                $res = $ivrModel->getreport($postData);
                Logger::dayLog('td/report_return', $res . PHP_EOL);
                $res = json_decode($res, TRUE);
//                $res = [
//                    'success'=>true,
//                    'call_record_list'=>'11111',
//                    'sms_record_list'=>'11111',
//                    'sys_call_status' => 'SUCCESS'
//                ];
                if (isset($res['success']) && $res['success'] == true) {
                    $addData['call_record_list'] = (isset($res['call_record_list']) && !empty($res['call_record_list'])) ? json_encode($res['call_record_list']) : '';
                    $addData['sms_record_list']  = (isset($res['sms_record_list']) && !empty($res['sms_record_list'])) ? json_encode($res['sms_record_list']) : '';
                    if(isset($res['sys_call_status'])){
                        $addData['sys_call_status']  = $res['sys_call_status'];
                        if($res['sys_call_status'] == 'SUCCESS'){
                            $addData['report_status'] = -6;
                        }else{
                            $addData['report_status'] = 7;
                        }
                    }
                    $result = $val->updateData($addData);
                    if (!$result) {
                        Logger::dayLog('td/add_report', 'error：添加同盾数据失败:loan_id : ' . $val->loan_id . PHP_EOL);
                    }
                } else {
                    Logger::dayLog('td/report', 'error : ' . $res['reason_desc'] . PHP_EOL);
                }
            }
        }
        TdReport::updateAll(['report_status'=>6],['report_status'=>-6]);
    }

    //获取同盾录音信息定时
    //当天早9点获取前一天数据
    public function actionTdinfo() {
        $start_date = date('Y-m-d', strtotime('-1 day'));//获取前一天日期
        $post_data  = ['date' => $start_date];
        $ivrModel   = new TdIvrService();
        $res_voice  = $ivrModel->getVoicefiles($post_data);
        $res_voice  = json_decode($res_voice, TRUE);
        if ($res_voice['success']) {
            $voice_password     = $res_voice['data']['password']; //解压密码
            $voice_record_paths = $res_voice['data']['voice_record_paths'];//文件路径json串
            //将录音文信息保存到文件
            $date = date("Ym");
            $day = date("d")-1;
            $path = '/data/wwwroot/voice/' . $date;
            if (!is_dir($path)) {
                mkdir($path, 0777, true);  //注意权限问题
            }
            $fp = fopen($path.'/'.$day.'.txt', 'w+');
            foreach ($voice_record_paths as $path) {
                fwrite($fp, 'http://'.$path .',password/'. $voice_password.PHP_EOL);
            }
            fclose($fp);
        } else {
            Logger::dayLog('td/voice', 'error:' . $res_voice['reason_desc'] . PHP_EOL);
            exit();
        }
    }

    /**
     * 拉取同盾IVR语音文件
     */
    public function actionDownvoice() {
//            $url     = 'http://storage-creditcloud.tongdun.cn/creditcloud-repair/voicezip/xianhuahua/20170925/xianhuahua-20170925(1~2255)2490d7.zip';
        $date = date("Ym");
        $day = date("d")-1;
        $voice_content = file_get_contents('/data/wwwroot/voice/'.$date.'/'.$day.'.txt');
        $url_arr       = explode(PHP_EOL, $voice_content);
        $curl          = new Curl();
        foreach ($url_arr as $url) {
            if(empty($url)) continue;
            $url = explode(',', $url);
            $content = $curl->get($url[0]);
            $path = '/data/wwwroot/voice/' . $date;
            if (!is_dir($path)) {
                mkdir($path, 0777, true);  //注意权限问题
            }
            $length   = strrpos($url[0], '/');
            $filename = substr($url[0], $length);
            $filename = str_replace('xianhuahua-', '', $filename);
            $handle   = fopen($path . $filename, "a");
            fwrite($handle, $content);
            fclose($handle);
        }
    }
}
