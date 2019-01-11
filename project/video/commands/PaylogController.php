<?php
namespace app\commands;
use app\models\Payorder;
use app\models\YpBindbank;
use app\models\YpQuickOrder;
use app\models\YpTztOrder;
use Yii;
use yii\db\Query;

class PaylogController extends BaseController {
	/**
	 * windows d:\xampp\php\php.exe D:\www\open\yii paylog  sendMail
	 * linux /data/wwwroot/open/yii paylog  sendMail
	 */
	public function sendMail($day = null) {
		//1 默认昨天
		if (!$day) {
			$day = date('Y-m-d', time() - 86400);
		}

		//2 获取统计数据
		$data = $this->getStatData($day);

		//3 发送邮件
		$mail = Yii::$app->mailer->compose('paylog', $data);
		$mail->setTo([
			'hanyongguo@ihsmf.com',
			'zhangxudong@ihsmf.com',
			'gaolian@ihsmf.com',
			'lijin@ihsmf.com',
		]);
		$mail->setSubject($day . "支付统计");
		if ($mail->send()) {
			echo "success";
		} else {
			echo "fail";
		}
	}
	/**
	 * 统计
	 */
	public function getStatData($day) {
		//1 查询条件
		$timeStart = strtotime($day);
		$timeEnd = $timeStart + 86400;

		$condition = ['and',
			['>=', 'create_time', $timeStart],
			['<', 'create_time', $timeEnd],
		];

		//2 当前笔数
		$query = new Query();
		$allTotal = $query->from(Payorder::tableName())->where($condition)->count();

		//3 分类统计
		$typeTotal = $query->select('count(1) AS total, pay_type, status')
			->from(Payorder::tableName())
			->where($condition)
			->groupBy('pay_type,status')
			->all();

		//4 绑卡失败统计
		$bindCondition = $condition;
		$bindCondition[] = ['!=', 'status', 2];
		//$bindCondition[] = ['>','error_code',0];
		$bindData = $query->select('count(1) AS total,error_code,error_msg')
			->from(YpBindbank::tableName())
			->where($bindCondition)
			->groupBy('error_code')
			->orderBy('total DESC')
			->all();

		//5 投资通失败数
		$tztCondition = $condition;
		$tztCondition[] = ['!=', 'pay_status', 2];
		//$tztCondition[] = ['>','error_code',0];
		$tztData = $query->select('count(1) AS total,error_code,error_msg')
			->from(YpTztOrder::tableName())
			->where($tztCondition)
			->groupBy('error_code')
			->orderBy('total DESC')
			->all();

		//6 一键支付失败数
		$quickCondition = $condition;
		$quickCondition[] = ['!=', 'pay_status', 2];
		//$quickCondition[] = ['>','error_code',0];
		$quickData = $query->select('count(1) AS total,error_code,error_msg')
			->from(YpQuickOrder::tableName())
			->where($quickCondition)
			->groupBy('error_code')
			->orderBy('total DESC')
			->all();

		//7 统计成功总金额
		$successCondition = $condition;
		$successCondition[] = ['status' => 2];
		$successPay = Payorder::find()->select(['sum(amount) AS amount', 'pay_type', 'aid'])
			->where($successCondition)
			->groupBy("aid, pay_type")
			->orderBy("aid ASC,pay_type ASC")
			->all();

		$data = [
			'day' => $day,

			'statusArr' => (new Payorder)->getStatus(),
			'paytypeArr' => [
				Payorder::PAY_TZT => '投资通',
				Payorder::PAY_QUICK => '一键支付',
			],
			'aidNames' => [
				1 => '一亿元',
				2 => 'java',
				3 => '商户贷',
				4 => '花生米富',
			],

			// 当天总笔数,无论成败
			'allTotal' => $allTotal,

			// 成功支付金额
			'successPay' => $successPay,

			// 分类统计
			'typeTotal' => $typeTotal,

			// 绑定失败数
			'bindTotal' => count($bindData),
			'bindData' => $bindData,

			// 投资通失败数
			'tztTotal' => count($tztData),
			'tztData' => $tztData,

			// 一键支付失败数
			'quickTotal' => count($quickData),
			'quickData' => $quickData,

		];
		return $data;
	}
}