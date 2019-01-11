<?php

namespace app\commands\sysloan;


use app\models\news\User_loan;
use app\commonapi\Logger;
use app\common\Curl;
use app\commands\BaseController;
use Yii;
use yii\console\Controller;

/**
 * 逾前结清订单推送  十分钟执行一次
 * Class CharuserloanController
 * @package app\commands
 * 测试  D:\phpStudy\php\php-7.0.12-nts\php.exe D:\work\yiyiyuanOnline\yii sysloan/beforsetstatus
 */
class BeforsetstatusController extends BaseController {


    public function actionIndex(){
        $startTime                   = date("Y-m-d 00:00:00",strtotime("+1 day"));
        $endTime                     = date("Y-m-d 00:00:00",strtotime("+3 day"));
        $stime                       = date("Y-m-d H:i:00",strtotime("-10 minute"));
        $etime                       = date("Y-m-d H:i:00");

        $where                       = [
            'and',
            ['>=','end_date',$startTime],
            ['<=','end_date',$endTime],
            ['=','status',8],
            ['>=','last_modify_time',$stime],
            ['<','last_modify_time',$etime],
        ];

        $userLoan   = User_loan::find()->where($where)->asArray()->all();

        if(empty($userLoan)){
            exit();
        }
        $loanIds    = [];
        foreach ($userLoan as $key => $val){
			$loanIds[] = $this ->getPrefixByDays($val).$val['loan_id'];
        }
        $data['loan_id']          = json_encode($loanIds);
        $data['version']          = '1.0';
        $data['sign']             = $this->encrySign($data);
        $url                      = Yii::$app->params['daihou_api_url'] . "/api/loan/clrearbeforloan";
        $result                   = (new Curl())->post($url, $data);
        $resultArr                = json_decode($result, true);
        if ($resultArr['rsp_code'] != '0000') {
            Logger::dayLog('sysloan', '结清账单', $data);
        }
    }
}