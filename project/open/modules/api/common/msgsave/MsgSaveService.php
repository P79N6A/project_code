<?php
/**
 * 同步一亿元采集的短信数据
 * 逻辑处理层
 * @author 孙瑞
 */
namespace app\modules\api\common\msgsave;

use Yii;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use app\common\Func;
use app\models\yiyiyuan\YiMsgList;
use app\models\yiyiyuan\YiUser;
use app\models\msgsave\MsglistRequest;
use app\models\msgsave\MsglistResult;

class MsgSaveService{
	public $moveTime = 100*24*60*60;

	// 获取配置文件
	private function getConfig(){
		$is_prod = SYSTEM_PROD ? true : false;
		$cfg = $is_prod ? "prod.php" : 'dev.php';
		$configPath = Yii::$app->basePath.'/modules/api/common/msgsave/config/'.$cfg;
		if (!file_exists($configPath)) {
			return [];
		}
		$config = include $configPath;
		return $config;
	}

	public function runOne($mobile, $msgJson){
		if(!$mobile){
			return ['code' => '109001', 'data' => '手机号不能为空'];
		}
		$msgList = json_decode($msgJson, TRUE);
		if(!$msgList || count($msgList)==0){
			return ['code' => '109002', 'data' => '短信列表格式错误'];
		}
		// 校验是否重复请求
		$dataMD5 = md5($mobile.$msgJson);
		$oMsglistRequest = new MsglistRequest();
		$checkRes = $oMsglistRequest->checkExists($dataMD5);
		if(!$checkRes){
			return ['code' => '109003', 'data' => '请求数据已存在'];
		}
		// 保存请求数据
		$saveResult = $oMsglistRequest->addRequest($mobile, $dataMD5);
		if(!$saveResult){
			return ['code' => '109004', 'data' => '请求数据保存失败'];
		}
		$add = 1;
		$oldMsgList = [];
		// 根据手机号获取存储的历史数据
		$oMsglistResult = (new MsglistResult());
		$oResult = $oMsglistResult->getInfoByMobile($mobile, 'mobile');
		if($oResult){
			$add = 0;
			$oldMsgList = $this->getJsonData($oResult);
		}
		// 合并数据存储文件
		$grabTime = date('Y-m-d H:i:s');
		foreach ($msgList as $msgInfo){
			if(!$msgInfo){
				continue;
			}
			$dataMD5 = md5(json_encode($msgInfo));
			if(array_key_exists($dataMD5, $oldMsgList)){
				continue;
			}
			$oldMsgList[$dataMD5] = $msgInfo;
			$oldMsgList[$dataMD5]['grab_time'] = $grabTime;
		}
		$savePath = $this->saveJsonData($mobile, json_encode($oldMsgList));
		if($add){
			$saveResult = $oMsglistResult->addData($mobile, $savePath, $grabTime);
		}else{
			$saveResult = $oMsglistResult->saveData($oResult, $grabTime);
		}
		if(!$saveResult){
			return ['code' => '109005', 'data' => $oMsglistResult->errinfo];
		}
		return ['code' => 0, 'data' => '短信数据存储成功'];
	}

	public function runAll($startId, $stopId) {
		// 查一亿元历史表
		$msgList = (new YiMsgList())->getListByIdRange($startId, $stopId);
		if(!$msgList){
			Logger::dayLog('msgsave','query: 未获取到短信数据');
			return FALSE;
		}
		// 获取用户Id列表
		$userIdList = [];
		$userMsgList = [];
		foreach ($msgList as $msgInfo){
			$userId = ArrayHelper::getValue($msgInfo, 'user_id', "");
			if($userId){
				$userIdList[] = $userId;
				$userMsgList[$userId] = $msgInfo->attributes;
			}
		}
		// 根据用户id获取手机号
		$mobileList = (new YiUser())->getMobilesByUserIds($userIdList);
		if(!$mobileList){
			Logger::dayLog('msgsave','query: 未获取到用户数据');
			return FALSE;
		}
		$userMobileList = [];
		foreach ($mobileList as $mobileInfo){
			$userId = ArrayHelper::getValue($mobileInfo, 'user_id', "");
			if($userId){
				$userMobileList[$userId] = ArrayHelper::getValue($mobileInfo, 'mobile', "");
			}
		}
		// 遍历获取手机号,拆解json存储文件,添加记录
		$success = 0;
		foreach ($userMsgList as $userId => $userMsgInfo){
			$mobile = ArrayHelper::getValue($userMobileList, $userId, "");
			if(!$mobile){
				Logger::dayLog('msgsave','query: 用户Id'.$userId.'未找到手机号');
				continue;
			}
			$grabTime = ArrayHelper::getValue($userMsgInfo, 'last_modify_time', "");
			$msgData = ArrayHelper::getValue($userMsgInfo, 'content', "");
			$msgData = json_decode($msgData, TRUE);
			if(!$msgData){
				Logger::dayLog('msgsave','query: 用户Id'.$userId.'的短信数据缺失,无法解析');
				continue;
			}
			$saveData = [];
			foreach($msgData as $eachMsg){
				$dataMD5 = md5(json_encode($eachMsg));
				$saveData[$dataMD5] = $eachMsg;
				$saveData[$dataMD5]['grab_time'] = $grabTime;
			}
			Logger::dayLog('msgsave','query: 用户Id'.$userId.',手机号'.$mobile.'所拥有短信条数:'.count($msgData));
			$savePath = $this->saveJsonData($mobile, json_encode($saveData));
			$oMsglistResult = new MsglistResult();
			$saveResult = $oMsglistResult->addData($mobile, $savePath, $grabTime);
			if($saveResult){
				Logger::dayLog('msgsave','query: 用户Id'.$userId.',手机号'.$mobile.'数据保存成功');
				$success++;
			}else{
				Logger::dayLog('msgsave','query: 用户Id'.$userId.',手机号'.$mobile.'数据保存失败:'.$oMsglistResult->errinfo);
			}
		}
		return $success;
	}

	// 读取json数据
	private function getJsonData($oResult){
		$savePath = ArrayHelper::getValue($oResult, 'save_path', '');
		$lastGrabTime = ArrayHelper::getValue($oResult, 'modify_time', '');
		if(!$savePath || !$lastGrabTime){
			return [];
		}
		$grabTimeStamp = strtotime($lastGrabTime);
		$nowTimeStamp = time();
		$config = $this->getConfig();
		if(!$config){
			return [];
		}
		if($nowTimeStamp - $grabTimeStamp <= $this->moveTime){
			$domain = ArrayHelper::getValue($config, 'jsonLocalDomain','');
		}else{
			$domain = ArrayHelper::getValue($config, 'jsonRemoteDomain','');
		}
		$oldMsgList = file_get_contents($domain.$savePath);
		$oldMsgList = json_decode($oldMsgList,TRUE);
		return $oldMsgList?$oldMsgList:[];
	}

	// 保存json数据
    private function saveJsonData($mobile, $data){
        // 保存json数据文件
		$preMobile = substr($mobile, 0,3);
        $path = '/ofiles/msglist/'.$preMobile.'/'.$mobile.'.json';
        $filePath = Yii::$app->basePath.'/web'.$path;
        Func::makedir(dirname($filePath));
        file_put_contents($filePath, $data);
		return $path;
    }
}
?>