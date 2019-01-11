<?php
/**
 *  
 */
namespace app\modules\api\common\sjt;

use Yii;
use app\models\SjtRequest;
use app\models\JxlRequestModel;
use app\models\JxlStat;
use app\modules\api\common\sjt\SjtNotify;
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
	public function runQuery(){
		$initRet = ['total' => 0, 'success' => 0];
		$restNum = 100;
		$dataList = (new SjtRequest)->getSjtDetailData($restNum);
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
            $result = $this->reportQuery($oSjt);
            if ($result) {
                $success += $result;               
            }else{
				Logger::dayLog('sjt/reportsjt', 'runQuery', '处理失败',$oSjt->requestid,$result);
			}
        }
        //返回结果
        $initRet = ['total' => $total, 'success' => $success];
        return $initRet;
	}
	/**
	 * Undocumented function
	 * 查询报告
	 * @param [type] $oSjt
	 * @return void
	 */
	private function reportQuery($oSjt){
		if(empty($oSjt)) return false;		
		$task_id = $oSjt->task_id;
		$result = $this->oApi->reportQuery($task_id);
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
			$res = $oSjt->saveDetailSuccess(true);
			Logger::dayLog('sjt/reportsjt','reportQuery','查询报告超时',$oSjt->requestid);
			return $res;
		}
		$code = ArrayHelper::getValue($result,'code');
		$message = ArrayHelper::getValue($result,'msg','');
		$data = ArrayHelper::getValue($result,'data');
		if($code==0){
			//成功
			$data = base64_decode($data);
			$data = gzdecode($data);
			$data = json_decode($data,true);
			$net_time = ArrayHelper::getValue($data,'mobile_info.mobile_net_time');
			if(!empty($net_time)){
				$net_time = date('Y年m月d日',strtotime($net_time));
			}
			$operatorData = [
				'basicInfo'=>[
					'inNetDate'=>$net_time
				]
			];
			$report_data = [
				'code' 		=> 0,
				'message'	=>'成功',
				'failed'	=>[],
				'requestId'	=> $oSjt->requestid,
				'from'		=>'sjt',
				'operatorData'=>json_encode($operatorData),
				'returndata' => $data
			];
			//保存报告json
			$json_data = json_encode($report_data);
			$url = (new JxlStat)->saveJson($oSjt->requestid,$json_data);
			if(!$url){
				$oSjt->saveDetailSuccess(true);
				Logger::dayLog('sjt/reportsjt','saveJson','写入报告json错误',$data,$oSjt->requestid);
				return false;
			}

			$detail_url = Yii::$app->basePath.'/web'. str_replace('.json','_detail.json',$url);//判断详单是否存在
			if( !file_exists( $detail_url )){
				Logger::dayLog('sjt/detail','detail不存在',$oSjt->requestid,'url', $detail_url);
				return false;
			}

			//更新请求状态
			$res = $oSjt->saveReportSuccess();
			if(!$res){
				Logger::dayLog('sjt/reportsjt','saveReportSuccess','更新请求状态失败',$oSjt->requestid);
			}
			$oJxlStat = (new JxlStat)->getByRequestid($oSjt->requestid);
			if (!$oJxlStat) {
				$oJxlStat = new JxlStat;
			}
			//保存到结果表
			$postData = [
				'aid' => $oSjt->aid,
				'requestid' => $oSjt->requestid,
				'name' => $oSjt->name,
				'idcard' => $oSjt->idcard,
				'phone' => $oSjt->phone,
				'website' => $oSjt->website,
				'url' => $url,
				'source' => $oSjt->source
			];
			$result = $oJxlStat->saveStat($postData);
			if(!$result){
				Logger::dayLog('sjt/reportsjt','保存结果集失败',$postData,$oSjt->requestid);
				//更新状态，从新查询报告
				$oSjt->saveDetailSuccess(true);
				return false;
			}
			//post异步通知
			$sjtNotify = new SjtNotify($oSjt);
			$res = $sjtNotify->clientNotify();
			if(!$res){
				Logger::dayLog('sjt/reportsjt','post异步通知失败',$postData,$oSjt->requestid);
			}
			//更新jxl_request状态码process_code
			$jxl_request = (new JxlRequestModel)->getJxlData($oSjt->requestid);
			$res = $jxl_request->upJxlProcesscode($oSjt->code);
			if(!$res){
				Logger::dayLog('sjt/reportsjt','更新jxl_request状态码失败',$postData,$oSjt->requestid);
			}
			return true;
		}else if($code==4001){
			//报告正在生成中
			$res = $oSjt->saveDetailSuccess(true);
			Logger::dayLog('sjt/reportsjt','reportQuery','查询报告失败',$code,$message,$oSjt->requestid);
			return $res;
		}else{
			//失败
			$res = $oSjt->saveReportFailure($code,$message);
			//post异步通知
			$sjtNotify = new SjtNotify($oSjt);
			$sjtNotify->clientNotify();
			Logger::dayLog('sjt/reportsjt','reportQuery','查询报告失败',$code,$message,$oSjt->requestid);
			return $res;
		}
	}
	
}
