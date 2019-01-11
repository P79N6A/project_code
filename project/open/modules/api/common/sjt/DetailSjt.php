<?php
/**
 *  定时任务查询任务脚本 
 */
namespace app\modules\api\common\sjt;

use Yii;
use app\models\SjtRequest;
use app\models\JxlStat;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\common\Crypt3Des;
class ReportSjt
{     
    private $oApi;
	public $errorInfo; 
    /**
     * 初始化接口
     */
    public function __construct(){
        $this->oApi = new SjtApi();
    }
	/**
	 * Undocumented function
	 *  查询任务定时任务 @todo 走异步通知不走定时查询
	 * @return void
	 */
	public function runQuery(){
		$initRet = ['total' => 0, 'success' => 0];
		$restNum = 100;
		$dataList = (new SjtRequest)->getSjtDoingData($restNum);
		if(empty($dataList)){
			return $initRet;
		}
		//锁定状态为查询中
        $ids = ArrayHelper::getColumn($dataList, 'id');
        $ups = (new SjtRequest)->lockQuery($ids);
        if (!$ups) {
            return $initRet;
		}
		$total = count($dataList);
        $success = 0;
        foreach ($dataList as $oSjt) {
			$isLock=$oSjt->lockOneQuery();
            if(!$isLock){
                continue;
            }
            $result = $this->taskQuery($oSjt);
            if ($result) {
                $success += $result;               
            }else{
				Logger::dayLog('sjt/detailsjt', 'runQuery', '处理失败',$oSjt->requestid,$result);
			}
        }
        //返回结果
        $initRet = ['total' => $total, 'success' => $success];
        return $initRet;
	}
	/**
	 * Undocumented function
	 * 查询任务
	 * @param [type] $oSjt
	 * @return void
	 */
	private function taskQuery($oSjt){
		if(empty($oSjt)) return false;		
		$task_id = $oSjt->task_id;
		$result = $this->oApi->taskQuery($task_id);
		var_dump($result);
		$res  = $this->parseResult($oSjt,$result);
		return $res;
	}
	/**
	 * Undocumented function
	 * 解析接口返回结果
	 * @param [type] $oSjt
	 * @param [type] $result
	 * @return void
	 */
	private function parseResult($oSjt,$result){
		if(empty($result)){
			//查询超时
			$res = $oSjt->saveToDoing();
			Logger::dayLog('sjt/saveToDoing','taskQuery/saveToDoing','查询任务超时',$oSjt->requestid);
			return $res;
		}
		$code = ArrayHelper::getValue($result,'code');
		$message = ArrayHelper::getValue($result,'message','');
		$task_id = ArrayHelper::getValue($result,'task_id','');
		$data = ArrayHelper::getValue($result,'data','');
		$call_info = ArrayHelper::getValue($result,'data.task_data.call_info','');
		if($code==0){
			//成功
			if(empty($data)||empty($call_info)){
				$oSjt->saveToDoing();
				Logger::dayLog('sjt/saveToDoing','saveToDoing','返回查询数据为空','data',$data,'call_info',$call_info,$oSjt->requestid);
				return false;				
			}
			//生成详单json			
			$res = $this->createDetailJson($oSjt,$call_info);
			if(!$res){
				$oSjt->saveToDoing();
				Logger::dayLog('sjt/saveToDoing','createDetailJson/saveToDoing','生成详单json失败',$oSjt->requestid);
				return false;
			}
			return true;
		}else{
			$oSjt->saveToDoing();
			Logger::dayLog('sjt/saveToDoing','taskQuery/saveToDoing','查询任务失败','code',$code,'message',$message,$oSjt->requestid);
			return false;
		}
	}
	/**
	 * Undocumented function
	 * 生成详单json
	 * @param [type] $oSjt
	 * @param [type] $data 通话详单 每月
	 * @return void
	 */
	private function createDetailJson($oSjt,$data){
		if(empty($data)) return false;
		$calls_data = [];
		foreach($data as $key=>$val){
			$call_record = $val['call_record'];
			if(!empty($call_record)){
				foreach($call_record as $k=>$v){
					$temp['update_time'] = date('Y-m-d H:i:s');
					$temp['start_time'] = $v['call_start_time'];
					$temp['init_type'] = $v['call_type_name'];
					$temp['use_time'] = (int)$v['call_time'];
					$temp['place'] = $v['call_address'];
					$temp['other_cell_phone'] = $v['call_other_number'];
					$temp['cell_phone'] = $oSjt->phone;
					$temp['subtotal'] = $v['call_cost'];
					$temp['call_type'] = $v['call_land_type'];
					array_push($calls_data,$temp);
				}
			}
		}
		$resObj['raw_data']['members']['transactions'][0]['calls'] = $calls_data;
		$sjtJson = json_encode($resObj);
		$requestid = $oSjt->requestid;
		$newPath = $this->writeLog($requestid.'_detail',$sjtJson);
		//保存详单状态
		$res = $oSjt->saveDetailSuccess();
		if(!$res){
			Logger::dayLog('sjt/saveDetailSuccess',$newPath,'requestid',$requestid);
			return false;
		}
		
		return true;
	}
	/**
	 * Undocumented function
	 * json数据存储
	 * @param [type] $filename
	 * @param [type] $data
	 * @return void
	 */
	public function writeLog($filename,$data){//详单、报告为json数据 并存储
        $path = '/ofiles/jxl/' . date('Ym/d/') . $filename . '.json';
        $filePath = Yii::$app->basePath . '/web' . $path;
        Func::makedir(dirname($filePath));
        file_put_contents($filePath, $data);
        return $path;
    }
	
}
