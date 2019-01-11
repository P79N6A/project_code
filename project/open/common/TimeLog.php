<?php
namespace app\common;
use Yii;

// 纪录访问时间
class TimeLog {
	private $startTime;
	private $endTime;
	private $logPath;
	
	public function __construct(){
		$this->startTime = $this->mtime();
		$this->logPath =  Yii::$app->basePath.'/log/times/';
	} 
	/**
	 * 精确到毫秒的时间戳
	 */
	private function mtime(){ 
	    list($usec, $sec) = explode(" ",microtime()); 
	    return ((float)$usec + (float)$sec); 
	}
	public function save($file, $datas){
		if( !is_array($datas) || empty($datas)  ){
			return false;
		}
		$datas[1] = json_encode($datas[1]);
		$logPath = $this->logPath . $file . '.log';
		$this->makedir(dirname($logPath));
		
		$execTime = $this->mtime() - $this->startTime;
		$execTime = round($execTime,2);
		array_unshift($datas, date('Y-m-d H:i:s'), $execTime);

		$content = implode("\t\t", $datas);
		return file_put_contents($logPath, $content."\n", FILE_APPEND);
	}
	//建立文件夹，并且可以选择是否建立默认的index.html文件
	private function makedir($param) {
		if(!file_exists($param)) {
			$this->makedir(dirname($param));
			mkdir($param);
		}
	}
}