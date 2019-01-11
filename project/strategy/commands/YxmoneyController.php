<?php
// 	当日购卡金额总数统计
//	D:\phpstudy\php55\php.exe D:\phpstudy\WWW\strategy_new\yii yxmoney runMoney
namespace app\commands;

use Yii;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use app\models\StAmount;
use app\models\credit\YxOrder;
use app\models\yyy\UserLoan;

class YxmoneyController extends BaseController {
	
	  /**
   * 当日购卡金额总数统计
   * 每五分钟执行一次
   */
  public function runMoney() {
    #1，set time
    $start_time = date('Y-m-d 00:00:00');
    $end_time = date('Y-m-d 23:59:59');
    $oYxOrder = new YxOrder();
    $where = ['and',
      ['in','status',[1,3,5]],
      ['>=','create_time',$start_time],
      ['<=','create_time',$end_time],
    ];
    $field = 'loan_id';
    #2,get order
    $orderList = $oYxOrder->getAllOrder($where,$field);
    if (empty($orderList)) {
        Logger::dayLog('yxmoney','today has no order');
        return false;
    }
    #3,get loan_ids
    $loan_ids = ArrayHelper::getColumn($orderList,'loan_id',[]);
    if (empty($loan_ids)) {
        Logger::dayLog('yxmoney','loan_ids is empty');
        return false;
    }

    #4,get loan_info
    $loan_where = ['in','loan_id',$loan_ids];
    $oUserLoan = new UserLoan();
    $loan_info = $oUserLoan->getAllLoan($loan_where);
    #5,数据重组
    $days_loan_array = ArrayHelper::map($loan_info, 'loan_id', 'amount', 'days');
    $seven_days = ArrayHelper::getValue($days_loan_array,7,[]);
    $fourteen_days = ArrayHelper::getValue($days_loan_array,14,[]);
    $twenty_eighth_days = ArrayHelper::getValue($days_loan_array,28,[]);
    $fifty_six_days = ArrayHelper::getValue($days_loan_array,56,[]);
    $save_arr = [
        'day7_sum_amount' => empty($seven_days) ? 0 : array_sum($seven_days),
        'day14_sum_amount' => empty($fourteen_days) ? 0 : array_sum($fourteen_days),
        'day28_sum_amount' => empty($twenty_eighth_days) ? 0 : array_sum($twenty_eighth_days),
        'day56_sum_amount' => empty($fifty_six_days) ? 0 : array_sum($fifty_six_days),
    ];
    #save 
    $res = (new StAmount)->setAmount($save_arr);
    return true;
  }
}