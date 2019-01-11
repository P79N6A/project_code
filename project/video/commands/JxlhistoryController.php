<?php
namespace app\commands;
use Yii;
use app\common\Logger;
use app\models\JxlStat;
use app\models\JxlRequestModel;

/**
 * windows d:\xampp\php\php.exe D:\www\open\yii jxlhistory import
 * linux /data/wwwroot/open/yii jxlhistory import
 */
class JxlhistoryController extends BaseController
{
	
	/**
	 * 导入测试
	 */
	public function import(){
		//1 获取文件列表
		$files = $this->getFiles();
		if(empty($files)){
			return FALSE;
		}
		
		//2 循环导入
		foreach($files as $filePath){
			$myfile = fopen($filePath, "r");
			while(!feof($myfile)) {
			    $content =  fgets($myfile);
				if(strpos($content,"  'data' => '") !== false){
					$result  = $this->strTodb($content);
				}
			}
			fclose($myfile);
		}
	}
	/**
	 * 获取导入的文件
	 */
	private function getFiles(){
		$dir = Yii::$app->basePath . '/log/juxinliback/201601';
	    if(!is_dir($dir)) {
	    	return null;	
		}
		$dh = opendir($dir);
		if(!$dh){
			return null;
		}
		
		$files = [];
        while (($file = readdir($dh)) !== false){
                if(  $file =="." || $file ==".." ){
                	continue;
                }   
                $filePath = $dir."/".$file;
                if( file_exists($filePath) ){
                	$files[] = $filePath;
                }
		}
        closedir($dh);
		sort($files);
		return $files;
	}
	/**
	 * 获取json数据
	 */
	private function getJsonStr(&$content){
		$content = trim($content);
		$jsonString = substr($content, 11,-2);
		return $jsonString;
	}
	/**
	 * 保存到数据库
	 * @param str $content
	 * @return bool
	 */
	private function strTodb(&$content){
		// 获取 json 数据
		$jsonString  = $this->getJsonStr($content);
		if(!$jsonString){
			Logger::dayLog("jxlhistory", "error", $content );
			return false;
		}
		
		// 2 数据校验
		$data = json_decode($jsonString, true);
		if( !is_array($data) ){
			Logger::dayLog("jxlhistory", "error", "json解析失败" );
			return FALSE;
		}
		
		//3 获取必备参数
		$requestid = isset($data['LOAN_APP_ID']) ? $data['LOAN_APP_ID']: 0;
    	$name  =  isset($data['CUST_NAME']) ? $data['CUST_NAME']: '';
    	$idcard =  isset($data['APP_IDCARD_NO']) ? $data['APP_IDCARD_NO']: '';
    	$phone =  isset($data['APP_PHONE_NO']) ? $data['APP_PHONE_NO']: '';
		
		if(!$phone){
			Logger::dayLog("jxlhistory", "error", "手机号错误" );
			return FALSE;
		}
		
		//4 保存到文件中
		$oJxlStat = new JxlStat;
		$url = $oJxlStat->saveJson($phone, $jsonString);
		
		//5 查询aid是否存在
		$aid = 0;
		if($requestid){
			$oModel =JxlRequestModel::findOne($requestid);
			$aid = $oModel->aid;
			if(!$name){
				$name = $oModel -> name;
			}
			if(!$idcard){
				$idcard = $oModel -> idcard;
			}
		}
		
		//4 组合数据
		$postData = [
		    'aid' => $aid,
	        'requestid' => $requestid ? $requestid : 0,
	        'name' => $name ? $name : '',
	        'idcard' => $idcard ? $idcard : '',
	        'phone' => $phone,
	        'url' => $url
		];
		
		//5 保存到DB中
		$oJxlStat = new JxlStat;
		$result = $oJxlStat -> saveStat($postData);
		if(!$result){
			$this->dayLog(
				'juxinliback',
				'saveStat',
				'保存失败', $postData
			);
			return false;
		}
		
		// 6 返回数据
		return true;
	}
}