<?php

namespace app\commands\sysloan;

use app\commands\BaseController;
use app\common\Curl;
use app\commonapi\Logger;
use app\models\news\Renewal_payment_record;
use app\models\news\Insure;
use Yii;

/**
 * 同步续期还款手续费  每10分钟执行一次
 * 1 注意这里引入文件必须是绝对路径。相对路径容易出错
 * windows D:phpStudy\php56n\php.exe D:WWW\yyy_loan\yii sysloan/sysloanrepay/index
 */
// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class SysoutrenewalController extends BaseController {

    public $enableCsrfValidation = false;

    public function actionIndex($startTime = '' , $endTime = ''){
        $time                               = time();
        if(empty($startTime)){
            $startTime                          = date('Y-m-d H:i:00', strtotime('-10 minutes'));
        }
        if(empty($endTime)){
            $endTime                            = date('Y-m-d H:i:00', $time);
        }
        $ids                                = [];
        $RenewalModel                       = new Renewal_payment_record();
        $renewal_payment                    = $RenewalModel->getRenewalByTime($startTime, $endTime);
        if(empty($renewal_payment)) {
            exit();
        }
        foreach ($renewal_payment as $key => $val){
			$product = $this ->getProductsource( $val-> loan);
            $ids[]                          = $val['loan_id'];
            $data                           = [];
            $data['loan_id']                = $val['loan_id'];
            $data['order_id']               = $val['order_id'];
            $data['parent_loan_id']         = $val['parent_loan_id'];
            $data['user_id']                = $val['user_id'];
            $data['bank_id']                = 0;//
            $data['platform']               = 0;//
            $data['source']                 = $val['source'];
            $data['money']                  = $val['money'];
            $data['actual_money']           = $val['actual_money'];
            $data['paybill']                = $val['paybill'];
            $data['status']                 = $val['status'];
            $data['last_modify_time']       = $val['last_modify_time'];
            $data['create_time']            = $val['create_time'];
			$data['product_source']            = $product;
            $data['sign']                   = $this->encrySign($data);
            //调用贷后接口
            $url                            = Yii::$app->params['daihou_api_url'] . "/api/loan/setrenewal";
//            $url                            = "http://www.xianhuahua.com/api/loan/setrenewal";
            $result                         = (new Curl())->post($url, $data);
            $resultArr                      = json_decode($result, true);
            if ($resultArr['rsp_code'] != '0000'){
                Logger::dayLog('sysloan', '同步还款记录失败', $data);
            }
        }
        Logger::dayLog('returnloanids', '同步还款数据loanID记录','开始时间为'.$startTime,'结束时间为'.$endTime, '条数'.count($ids),$ids);


    }
}