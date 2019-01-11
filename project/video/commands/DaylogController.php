<?php
/**
 * 统计量化派和银行四联成功与失败情况
 */
namespace app\commands;
use app\common\ArrayDas;
use app\models\remit\Remit;
use Yii;

class DaylogController extends \app\commands\BaseController {
	/**
	 * windows d:\xampp\php\php.exe D:\www\open\yii daylog  sendMail
	 * linux /data/wwwroot/open/yii daylog  sendMail
	 */
	public function sendMail($day = null) {
		//1 默认
		if (!$day) {
			$day = date('Y-m-d');
		}

		//2 获取统计数据
		// 量化派已关闭
		//$idcards = $this->getIdcards($day);

		// 银联四要素
		$bankvalids = $this->getBankvalids($day);
		$remit = $this->getRemit($day);

		//3 发送邮件
		$mail = Yii::$app->mailer->compose('daylog', [
			"idcards" => isset($idcards) ? $idcards : [],
			"bankvalids" => $bankvalids,
			"remit" => $remit,
			'day' => date('Y-m-d', strtotime($day) - 86400),
		]);
		$mail->setTo([
			'hanyongguo@ihsmf.com',
			'zhangxudong@ihsmf.com',
			'gaolian@ihsmf.com',
			'lijin@ihsmf.com',
		]);
		$mail->setSubject($day . "开发平台统计日志");
		if ($mail->send()) {
			echo "success";
		} else {
			echo "fail";
		}
	}
	/**
	 * 根据sql获取全部数据
	 */
	public function getAllBySql($sql) {
		$connection = Yii::$app->db;
		$command = $connection->createCommand($sql);
		return $command->queryAll();
	}
	/**
	 * 统计量化派身份证
	 */
	public function getIdcards($endday) {
		//1 查询条件
		$timeEnd = strtotime($endday);
		$timeStart = $timeEnd - 7 * 86400;

		$dateStart = date('Y-m-d', $timeStart);
		$where = "create_time>='{$dateStart}' AND create_time < '{$endday}'";

		//2 查询成功统计
		$sql = "
			SELECT
			  DATE_FORMAT(create_time,'%Y-%m-%d') AS create_day,
			  COUNT(1) AS success
			FROM xhh_idcard
			WHERE {$where}
			GROUP BY create_day order by create_day";
		$success = $this->getAllBySql($sql);

		//3 查询失败统计
		$where = "create_time>='{$timeStart}' AND create_time < '{$timeEnd}'";
		$sql = "
			SELECT
			  FROM_UNIXTIME(create_time,'%Y-%m-%d') AS create_day,
			  COUNT(1) AS fail
			FROM xhh_log
			WHERE req_url = '/api/idcard' AND {$where}
			GROUP BY create_day order by create_day";
		$fails = $this->getAllBySql($sql);
		$oArrayDas = new ArrayDas;
		$data = $oArrayDas->outerJoin($success, $fails, 'create_day');
		return $data;
	}
	/**
	 * 统计银行四联
	 */
	public function getBankvalids($endday) {
		//1 查询条件
		$timeEnd = strtotime($endday);
		$timeStart = $timeEnd - 7 * 86400;

		$dateStart = date('Y-m-d', $timeStart);
		$whereTime = "create_time>='{$timeStart}' AND create_time < '{$timeEnd}'";
		$whereDay = "create_time>='{$dateStart}' AND create_time < '{$endday}'";

		//2 查询成功统计
		$sql = "
			SELECT
			  DATE_FORMAT(create_time,'%Y-%m-%d') AS create_day,
			  COUNT(1) AS success
			FROM xhh_bank_valid
			WHERE {$whereDay}
			GROUP BY create_day order by create_day";
		$success = $this->getAllBySql($sql);

		//3 查询总失败统计
		$sql = "
			SELECT
			  FROM_UNIXTIME(create_time,'%Y-%m-%d') AS create_day,
			  COUNT(1) AS fail
			FROM xhh_log
			WHERE req_url = '/api/bankvalid' AND {$whereTime}
			GROUP BY create_day order by create_day";
		$fails = $this->getAllBySql($sql);
		$oArrayDas = new ArrayDas;
		$data = $oArrayDas->outerJoin($success, $fails, 'create_day');

		//4 查询接口失败统计
		$sql = "
			SELECT
			  DATE_FORMAT(create_time,'%Y-%m-%d') AS create_day,
			  COUNT(1) AS apifail
			FROM xhh_bank_valid_log
			WHERE status=11 AND error_code=10007 AND {$whereDay}
			GROUP BY create_day order by create_day";
		$apifails = $this->getAllBySql($sql);
		$oArrayDas = new ArrayDas;
		$data = $oArrayDas->outerJoin($data, $apifails, 'create_day');

		//5 查询接口失败统计
		$sql = "
			SELECT
			  DATE_FORMAT(create_time,'%Y-%m-%d') AS create_day,
			  COUNT(distinct idcard) AS idcards
			FROM xhh_bank_valid_log
			WHERE  {$whereDay}
			GROUP BY create_day ORDER BY create_day";
		$idcards = $this->getAllBySql($sql);
		$oArrayDas = new ArrayDas;
		$data = $oArrayDas->outerJoin($data, $idcards, 'create_day');

		return $data;
	}
	/**
	 * 统计银行四联
	 */
	public function getRemit($dateEnd) {
		// 获取昨天一整天的成功的数据
		$timeStart = strtotime($dateEnd) - 86400;
		$dateStart = date('Y-m-d', $timeStart);
		$where = "WHERE create_time>='{$dateStart}' AND create_time < '{$dateEnd}'";

		//1 按应用分组统计出款总额和个数
		$successStatus = Remit::STATUS_SUCCESS;
		$sql = "SELECT SUM(settle_amount) AS amount, COUNT(1) AS total, aid
				FROM rt_remit  {$where} AND remit_status={$successStatus}
				GROUP BY aid ORDER BY total DESC";
		$aidStat = $this->getAllBySql($sql);

		//2 按状态统计成功与失败
		$sql = "SELECT SUM(settle_amount) AS amount,  COUNT(1) AS total, remit_status
				FROM rt_remit  {$where} 
				GROUP BY remit_status ORDER BY total DESC";
		$statusStat = $this->getAllBySql($sql);

		//3 统计失败原因
		$successStatus = Remit::STATUS_SUCCESS;
		$sql = "SELECT   SUM(settle_amount) AS amount, COUNT(1) AS total, remit_status, rsp_status, rsp_status_text
				FROM rt_remit  {$where} AND remit_status!={$successStatus} 
				GROUP BY rsp_status ORDER BY total DESC";
		$failStat = $this->getAllBySql($sql);

		$remitStatus = (new Remit)->getStatus();
		$data = [
			// 按应用统计
			'aidStat' => $aidStat,
			'aidNames' => [
				1 => '一亿元',
				2 => 'java',
				3 => '商户贷',
				4 => '花生米富',
			],

			// 按状态统计
			'statusStat' => $statusStat,

			//失败原因统计
			'failStat' => $failStat,

			// 失败原因统计
			'remitStatus' => $remitStatus,
		];
		return $data;
	}

}