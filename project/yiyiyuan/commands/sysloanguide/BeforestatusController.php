<?php

namespace app\commands\sysloanguide;

use app\commands\BaseController;
use app\common\Curl;
use app\commonapi\Logger;
use app\models\day\User_loan_guide;
use Yii;

/**
 * 逾前结清订单推送  十分钟执行一次
 * Class CharuserloanController
 * @package app\commands
 * 测试  D:\phpStudy\php\php-7.0.12-nts\php.exe D:\work\yiyiyuanOnline\yii sysloanguide/beforsetstatus
 */
class BeforestatusController extends BaseController {


    public function actionIndex(){
		$beforeDays	 = $this->getBeforeDays();
		$startTime	 = date("Y-m-d 00:00:00", strtotime("+$beforeDays day"));
		$endTime	 = date("Y-m-d 00:00:00", strtotime("+1 day"));
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

        $userLoan   = User_loan_guide::find()->where($where)->asArray()->all();
        if(empty($userLoan)){
            exit();
        }
        $resArr = array_chunk($userLoan, 500);
		foreach ($resArr as $rk => $rv) {
			$data		 = $postData	 = [];
			foreach ($res as $k => $val) {
				$data[$k]['loan_id']		 = $val['loan_id'];
				$data[$k]['product_source']	 = $this->getProductsource($val);
			}
			$postData['data']	 = json_encode($data);
			$postData['sign']	 = $this->encrySign($postData);
			$url				 = Yii::$app->params['daihou_api_url'] . "/api/loan/beforesetstatus";
			$result				 = (new Curl())->post($url, $postData);
			$resultArr			 = json_decode($result, true);
			if ($resultArr['rsp_code'] != '0000') {
				Logger::dayLog('sysloanguide/beforestatus', '同步逾前结清数据', $postData, $result);
			}
		}
    }
}