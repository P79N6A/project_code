<?php
/**
 * 统计量化派和银行四联成功与失败情况
 */
namespace app\commands;
use app\common\ArrayDas;
use app\models\remit\Remit;
use Yii;
/**
 * windows d:\xampp\php\php.exe D:\www\open\yii peanut-remit  week
 * linux /data/wwwroot/open/yii peanut-remit  week
 */
class PeanutRemitController extends \app\commands\BaseController {

	public function week($day=null){
		if($day){
			$time = strtotime($day);
		}else{
			$time = time() - 7 * 86400;// 上周
		}

		$t = date('N', $time);
		$start_time = date('Y-m-d', $time - ($t-1) * 86400);
		$end_time  = date('Y-m-d', $time + (7-$t) * 86400);
		$msg = $this->send($start_time, $end_time);
		echo $msg;
	}
	/**
	 * 根据时间范围发送邮件
	 * @param  string $start_time
	 * @param  string $end_time 
	 * @return string
	 */
	public function send($start_time, $end_time) {
		//1 获取数据
		$remit = $this->getRemit($start_time, $end_time);

		//2 发送邮件
		$mail = Yii::$app->mailer->compose('peanutremit/remit', [
			'start_time' => $start_time, 
			'end_time' => $end_time, 
			"remit" => $remit,
		]);
		$mail->setTo([
			'hanyongguo@ihsmf.com',
			'gaolian@ihsmf.com',
			'lijin@ihsmf.com',
		]);
		$mail->setSubject($day . "花生米米富出款统计报告");
		if ($mail->send()) {
			return "success";
		} else {
			return "fail";
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
	 * 出款统计
	 */
	public function getRemit($start_time, $end_time) {
		$end_time = date('Y-m-d', strtotime($end_time) + 86400);
		$sql = "SELECT
				  DATE(modify_time)    create_day,
				  COUNT(1) AS num,
				  SUM(settle_amount) AS money,
				  SUM(IF(`remit_status`=6,settle_amount,0)) AS success_money
				FROM xhh_open.rt_remit
				WHERE aid = 4
			        AND modify_time>='{$start_time}' 
			        AND modify_time < '{$end_time}'
				GROUP BY create_day
				ORDER BY create_day
				";
		$data = $this->getAllBySql($sql);
		return $data;
	}

}