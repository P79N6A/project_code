<?php

namespace app\commands\sysloan;

use app\models\dev\User;
use app\models\news\GoodsBill;
use app\models\news\OverdueLoan;
use app\models\news\User_loan;
use app\commonapi\Logger;
use app\common\Curl;
use app\commands\BaseController;
use Yii;
use yii\console\Controller;

/**
 * 逾前未分期订单推送  每天执行一次
 * Class CharuserloanController
 * @package app\commands
 * 测试  D:\phpStudy\php\php-7.0.12-nts\php.exe D:\work\yiyiyuanOnline\yii sysloan/beforovdue
 */
class BeforovdueController extends BaseController {

    public $enableCsrfValidation = false;

    public function actionIndex(){
        $start_time = date("Y-m-d 00:00:00",strtotime("+3 day"));
        $where = [
            'and',
            ['in','status',[9,11]],
            ['in','business_type',[1,4]],
            ['=','end_date',$start_time],
        ];
        $user_loan = User_loan::find()->where($where)->asArray()->all();
        if(empty($user_loan)){
            exit();
        }
        foreach ($user_loan as $key => $val){
            $data                     = [];
            $data['version']          = '1.0';
            $data['loan_id']          = isset($val['loan_id']) ? $val['loan_id'] : '';
            $data['parent_loan_id']   = isset($val['parent_loan_id']) ? $val['parent_loan_id'] : '';
            $data['business_type']    = isset($val['business_type']) ? $val['business_type'] : '';
            $data['status']           = isset($val['status']) ? $val['status'] : '';
            $data['end_date']           = isset($val['end_date']) ? $val['end_date'] : '';
			$data['product_source'] = $this ->getProductsource($val);
            $data['sign']             = $this->encrySign($data);
            $url                      = Yii::$app->params['daihou_api_url'] . "/api/loan/savebeforloan";
//            $url                      = "http://www.xianhuahua.com/api/loan/savebeforloan";
            $result                   = (new Curl())->post($url, $data);
            $resultArr                = json_decode($result, true);
            if ($resultArr['rsp_code'] != '0000') {
                Logger::dayLog('sysloan', '同步逾前账单', $data);
            }
        }

    }

}
