<?php
namespace app\commands;
use app\common\Logger;
use app\models\JxlStat;
use app\modules\api\common\juxinli\Jxlcheck;
use Yii;

/**
 * 聚信立检查重新下载程序
 * 由于一天前文件会同步到其它机器上.
 * 此程序仅仅检测一天内的文件报告.
 */
class JxlcheckController extends BaseController {
	/**
	 * windows d:\xampp\php\php.exe D:\www\open\yii jxlcheck  index
	 * linux /data/wwwroot/open/yii jxlcheck  index
	 */
	public function index($start_time = null, $end_time=null) {
		// 默认前 10 - 40分
		$start_time = $start_time ? strtotime($start_time) : time() - 2400;
		$end_time = $end_time ? strtotime($end_time) : $start_time + 1800;

		$timeStart = date('Y-m-d H:i:s', $start_time);
		$timeEnd = date('Y-m-d H:i:s', $end_time);

		// 导入到jxl_stat表中
		$import_total = $this->importToStat($timeStart, $timeEnd);
		$chk_total = $this->batchFromDb($timeStart, $timeEnd);
		Logger::dayLog('jxlcheck', 'index', '新导入条数', $import_total, '检测条数', $chk_total);
		return true;
	}

	/**
	 * 获取统计数据;查找10008, 但没有文件的数据 
	 * @param  [type] $timeStart [description]
	 * @param  [type] $timeEnd   [description]
	 * @return [type]            [description]
	 */
	private function importToStat($timeStart, $timeEnd){
		//1 查询范围
		$tStart = strtotime($timeStart);
		$tEnd = strtotime($timeEnd);

		$sql = "SELECT
				  aid,
				  id AS requestid,
				  `name`,
				  idcard,
				  phone,
				  website,
				  FROM_UNIXTIME(create_time) AS create_time,
				  CONCAT('/ofiles/jxl/', FROM_UNIXTIME(create_time,'%Y%m/%d/'), id, '.json') AS url
				FROM jxl_request r
				WHERE create_time >='{$tStart}' and create_time < '{$tEnd}'
				    AND process_code = '10008'
				    AND NOT EXISTS(SELECT
				                     1
				                   FROM jxl_stat s
				                   WHERE r.id = s.requestid
				                       AND  create_time >= '{$timeStart}' and create_time < '{$timeEnd}')";
	       $data = $this->getAllBySql($sql);
	       $total = is_array($data) ? count($data) : 0;
	       Logger::dayLog('jxlcheck', 'importToStat', '需导入条数', $total);
		if (!$total) {
			return false;
		}

		//2 导入到统计报告表
		$oJxlStat = new JxlStat;
		$total = $oJxlStat -> insertBatch($data);
		Logger::dayLog('jxlcheck', 'importToStat', '新导入条数', $total);

		return $total;
	}
	/**
	 * 从数据库中检测
	 */
	private function batchFromDb($timeStart, $timeEnd){
		//1 获取统计数据
		$where = [
			'AND',
			['>=', 'create_time', $timeStart],
			['<', 'create_time', $timeEnd],
			['!=', 'is_valid', 3],

		];
		$data = JxlStat::find()->where($where)->limit(5000)->orderBy("id ASC")->all();
		if (empty($data)) {
			return false;
		}

		//2 检测每一条纪录
		$success = 0;
		foreach ($data as $model) {
			$obj = new Jxlcheck;
			$model->is_valid = $obj->chkValid($model);
			$result = $model->save();

			if ($model->is_valid == 3) {
				$success++;
			}
		}
		Logger::dayLog('jxlcheck', 'check', '成功/总条数', $success, count($data));
		return $success;
	}
	/**
	 * 批量更新数据
	 * @return [type] [description]
	 */
	public function batchFromFile(){
		$path = Yii::$app->basePath . '/log/jxl_requests.txt';
		$content = file_get_contents($path);
		$arr = explode("\n",$content);
		if( !is_array($arr) || empty($arr) ){
			echo "jxl_uids是空的";
			return null;
		}

		$newarr = [];
		foreach ($arr as $k=>$requestid) {
			$requestid = trim($requestid);
			if(is_numeric($requestid)){
				$newarr[] = $requestid;
			}
		}
		foreach ($newarr as $requestid) {
			$msgs = $this->_runbyid($requestid);
			if($msgs['status']){
				echo $msgs['sync'] . "\n";	
			}
		}
		return true;
	}
	public function runbyid($requestid){
		$msg = $this->_runbyid($requestid);
		print_r($msg);
	}
	/**
	 * 按requestid 生成数据
	 * @param  [type] $requestid [description]
	 * @return [type]            [description]
	 */
	public function _runbyid($requestid) {
		$model = JxlStat::find()->where(['requestid' => $requestid])->orderBy("id DESC")->limit(1)->one();
		if (empty($model)) {
			return [ 'status' => false, 'error'=>"#{$requestid}不存在\n", ];
		}
		$obj = new Jxlcheck;
		$is_valid = $obj->chkValid($model);
		$model->is_valid = $is_valid;
		$result = $model->save();

		$report_path = $model['url'];
		$detail_path = str_replace(".json", "_detail.json", $model['url']);

		$requestid = "#{$requestid}:{$is_valid}\n";

		$open = '';
		$open .= "#开放平台查看命令\n";
		$open .= "cat " . Yii::$app->basePath . '/web' . $report_path;
		$open .= "\n";
		$open .= "cat " . Yii::$app->basePath . '/web' . $detail_path;
		$open .= "\n\n";

		$$local= '';
		$local .= "#{$requestid}备份机12查看命令\n";
		$local .= "cat /home/wwwroot/open/web" . $report_path;
		$local .= "\n";
		$local .= "cat /home/wwwroot/open/web" . $detail_path;
		$local .= "\n\n";

		$sync = '';
		$sync .= "#同步到备份机12命令\n";
		$sync .= $this->rsync($report_path);
		$sync .= "\n";
		$sync .= $this->rsync($detail_path);
		$sync .= "\n";

		$msgs = [
			'status' => true,
			'requestid'=>$requestid,
			'open'=>$open,
			'local' => $local,
			'sync' => $sync,
		];
		return $msgs;
	}
	/**
	 * 同步命令
	 * @param  [type] $path [description]
	 * @return [type]       [description]
	 */
	private function rsync($path) {
		// 同步命令
		$file_path = Yii::$app->basePath . '/web' . $path;
		$rsync_path = str_replace("/ofiles/jxl", "", $path);
		$str = "sudo -u www /usr/bin/rsync -vzrtopg --password-file=/data/shell_script/rsync_jxl.pass {$file_path} company@124.193.149.180::jxl{$rsync_path}";
		return $str;
	}
}