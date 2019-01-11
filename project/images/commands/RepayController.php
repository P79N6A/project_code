<?php
namespace app\commands;
use Yii;
use app\common\Func;
use app\models\Repay;
use app\models\Loan;
/**
 * windows d:\xampp\php\php.exe D:\www\merchant\yii repay updateStatus
 * linux /data/wwwroot/merchant/yii repay updateStatus
 *
 */
class RepayController extends BaseController
{
	/**
	 * 每天凌晨将当天的还款列表中的状态批量更新
	 * 1 未到期 -> 还款中
	 * 2 还款中 -> 逾期状态
	 */
	public function updateStatus(){
		//1 处理逾期的状态
		$oRepay = new Repay;
		$num1 = $oRepay -> updatePaying();
		$num2 = $oRepay -> updateOverdue();

		$oLoan = new Loan;
		$num3 = $oLoan -> updateOverdue();

		$this->dayLog(
			'crontab/repay',
			'未到期->还款中',$num1,
			'还款中->逾期状态',$num2,
			'LOAN表->逾期状态',$num3
		);

		//2 处理当天逾期纪录
		$this->saveOverdue();
	}
	/**
	 * 生成当天逾期结果
	 * @return [type] [description]
	 */
	public function saveOverdue(){
		//1 获取当天的逾期纪录
	}
}
