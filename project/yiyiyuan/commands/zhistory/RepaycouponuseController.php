<?php

namespace app\commands\sysloan;

use app\models\news\RepayCouponUse;
use app\commonapi\Logger;
use app\common\Curl;
use app\commands\BaseController;
use Yii;
use yii\db\Query;
use yii\console\Controller;

/**
 * 还款优惠券推送  10分钟一次
 * Class BeforbillrepayController
 * @package app\commands\sysloan
 * 测试  D:\phpstudy\PHPTutorial\php\php-7.0.12-nts\php.exe D:\WWW\yiyiyuan_after\yii sysloan/repaycouponuse
 */
class RepaycouponuseController extends BaseController {


    public function actionIndex(){
        $stime                       = date("Y-m-d H:i:00",strtotime("-10 minute"));
        $etime                       = date("Y-m-d H:i:00");
        $where      = [
            'and',
            ['=', 'yi_repay_coupon_use.repay_status', 1],
            ['>=', 'yi_repay_coupon_use.last_modify_time', $stime],
            ['<', 'yi_repay_coupon_use.last_modify_time', $etime],
            ['!=', 'b.loan_status', 7],
        ];
        $list       = (new RepayCouponUse())->find()->select('b.days,yi_repay_coupon_use.*')->leftJoin('yi_overdue_loan AS b', 'yi_repay_coupon_use.loan_id = b.loan_id')->where($where)->asArray()->all();
        if (empty($list)) {
            exit();
        }
        foreach ($list as $key => $val) {
            $data                     = [];
            $data['version']          = '1.0';
            $data['user_id']          = isset($val['user_id']) ? $val['user_id'] : '';
            $data['loan_id']          = isset($val['loan_id']) ? $val['loan_id'] : '';
            $data['discount_id']      = isset($val['discount_id']) ? $val['discount_id'] : '';
            $data['repay_id']         = isset($val['repay_id']) ? $val['repay_id'] : '';
            $data['repay_amount']     = isset($val['repay_amount']) ? $val['repay_amount'] : '';
            $data['repay_status']     = isset($val['repay_status']) ? $val['repay_status'] : '';
            $data['coupon_amount']    = isset($val['coupon_amount']) ? $val['coupon_amount'] : '';
			$data['product_source'] = $this ->getProductsource($val);
            $data['sign']             = $this->encrySign($data);
            $url                      = Yii::$app->params['daihou_api_url'] . "/api/loan/saverepaycouponuse";
//            $url                      = "http://www.xianhuahuastage.com/api/loan/saverepaycouponuse";
            $result                   = (new Curl())->post($url, $data);
            $resultArr                = json_decode($result, true);
            if ($resultArr['rsp_code'] != '0000') {
                Logger::dayLog('sysloan', '同步还款使用优惠券', $data);
            }
        }
    }

}
