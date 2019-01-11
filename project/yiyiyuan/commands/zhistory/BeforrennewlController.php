<?php

namespace app\commands\sysloan;

use app\commands\BaseController;
use app\common\Curl;
use app\commonapi\Logger;
use app\models\news\Renewal_payment_record;
use app\models\news\Insure;
use Yii;

/**
 * 分期还款推送  10分钟一次
 * Class BeforloanrepayController
 * @package app\commands\sysloan
 * 测试  D:\phpStudy\php\php-7.0.12-nts\php.exe D:\work\yiyiyuanOnline\yii sysloan/beforrennewl
 */

class BeforrennewlController extends BaseController {


    public function actionIndex(){
        $start_time                  = date("Y-m-d 00:00:00",strtotime("+3 day"));
        $end_time                    = date("Y-m-d 00:00:00",strtotime("+1 day"));
        $stime                       = date("Y-m-d H:i:00",strtotime("-10 minute"));
        $etime                       = date("Y-m-d H:i:00");
        $where                       = [
            'and',
            ['=','yi_insure.status',1],
            ['=','yi_insure.type',3],
            ['between','b.end_date',$end_time,$start_time],
            ['>=','yi_insure.last_modify_time',$stime],
            ['<','yi_insure.last_modify_time',$etime],
            ['in','b.business_type',[1,4]],
            ['in','b.status',[8 ,9 ,11]],
        ];
        $list       = (new Insure())->find()->leftJoin('yi_user_loan AS b', 'yi_insure.loan_id = b.loan_id')->where($where)->all();
        if(empty($list)){
            exit();
        }
        foreach ($list as $key => $val){
            $data                     = [];
            $data['version']          = '1.0';
            $data['loan_id']          = isset($val['loan_id']) ? $val['loan_id'] : '';
            $data['order_id']         = isset($val['order_id']) ? $val['order_id'] : '';
            $data['business_type']    = $val -> loan-> business_type;
            $data['product_source']   = $this ->getProductsource($val -> loan);
            $data['status']           = $val -> loan-> status;
            $data['user_id']          = isset($val['user_id']) ? $val['user_id']: '';
            $data['bank_id']          = 0;
            $data['platform']         = 0;
            $data['source']           = isset($val['source']) ? $val['source']: '';
            $data['pic_repay1']       = '';
            $data['pic_repay2']       = '';
            $data['pic_repay3']       = '';
            $data['amount']           = isset($val['actual_money']) ? $val['actual_money']: '';
            $data['pay_key']          = '';
            $data['realname']         = isset($val->user->realname) ? $val->user->realname : '';
            $data['mobile']           = isset($val->user->mobile) ? $val->user->mobile : '';
            $data['identity']         = isset($val->user->identity) ? $val->user->identity : '';
            $data['paybill']          =  '';
            $data['repay_time']       = '00-00-00 00:00:00';
            $data['repay_mark']       = isset($val['repay_mark']) ? $val['repay_mark']: '';
            $data['repay_status']     = isset($val['status']) ? $val['status']: '';
            $data['repay_type']       = 3;
            $data['sign']             = $this->encrySign($data);
            $url                      = Yii::$app->params['daihou_api_url'] . "/api/loan/savebeforrepay";
//            $url                      = "http://www.xianhuahua.com/api/loan/savebeforrepay";
            $result                   = (new Curl())->post($url, $data);
            $resultArr                = json_decode($result, true);
            if ($resultArr['rsp_code'] != '0000') {
                Logger::dayLog('sysloan', '同步分期账单', $data);
            }
        }
    }
}