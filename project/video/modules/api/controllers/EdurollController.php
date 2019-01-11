<?php
/**
 * 学籍接口
 */
namespace app\modules\api\controllers;

use Yii;
use app\modules\api\common\ApiController;
use app\common\Func;
use app\common\Logger;
use app\modules\api\common\eduroll\EduRollCrypt;

use app\models\Eduroll;
use app\models\EdurollLog;

class EdurollController extends ApiController
{
	/**
	 * 服务id号
	 */
	protected $server_id = 6;
	
	private $eduRollCrypt;
	
	private $eduModel;
	
	private $eduLogModel;
	
	private $fromtype = 0;//1 db中 2 日志中 11 神州融接口中
	
	// 最终查询结果
	private $queryResult = null;
	
	/**
	 * 初始化
	 */
	public function init(){
		parent::init();
		$env = YII_ENV_DEV ? 'dev' : 'prod';
		$this-> eduRollCrypt = new EduRollCrypt($env);
		
		$this-> eduModel = new Eduroll();
		$this-> eduLogModel =new EdurollLog();
	}
	public function actionIndex(){
		return $this->resp(6000, "暂停服务");
		
		//1 设置身份证为大写例如x->X
		if( $this->reqData && isset($this->reqData['idcode'])){
			$this->reqData['idcode'] = strtoupper($this->reqData['idcode']);
		}

		$postData = $this->reqData;
		$postData['create_time'] = time();
		
		//2 参数检证是否有错
		if ( $errors = $this->eduModel->chkAttributes($postData) ) {
			return $this->parseData(6001,implode('|',$errors));
		}
		
		//3 验证是否超限
		$result = $this->eduLogModel -> chkQueryNum($postData['idcode']);
		if(!$result){
			return $this->parseData(6002,'您查询的次数过多');
		}
		
		//5 @todo 设置最终查询结果
		/*$this->queryResult = [
			'status' => 1,
			'gradudate' => '', // 预计毕业日期
			'enroldate_check' => 1,
			'graduate_check' => 1,
			'educationdegree_check' =>1,
			'studystyle_check' => 1,
			'fromtype' => 1,
		];

		// 返回输出结果
		return $this->parseData(6000, $this->queryResult);*/
		
		
		//4 检测本地是否存在数据
		$queryResult = $this->getDbInfo($postData);
		$fromtype = 1; // 从db访问
		//5 接口中获取数据，并更新到数据库中
		if( !$queryResult ){
			$queryResult = $this->getInterfaceInfo($postData);
			if( empty($queryResult) ){
				return $this->parseData(6010,"没有查找到数据");
			}
			
			// 更新数据中的数据
			$result = $this->eduModel->saveByNewData($queryResult);
			if( !$result ){
				return $this->parseData(6004,"保存失败");
			}
			$this->fromtype = 3;// 从接口访问
		}
		
		if( !$queryResult ){
			return $this->parseData(6005,"没有匹配的数据");
		}
		
		//5  设置最终查询结果
		$this->queryResult = [
			'status' => $queryResult['status'],
			'gradudate' => $queryResult['gradudate'], // 预计毕业日期
			'enroldate_check' => intval($queryResult['enroldate_check']),
			'graduate_check' => intval($queryResult['graduate_check']),
			'educationdegree_check' =>intval($queryResult['educationdegree_check']),
			'studystyle_check' => intval($queryResult['studystyle_check']),
			'fromtype' => $this->fromtype,
		];
		if( isset($queryResult['name_check']) ){
			$this->queryResult['name_check'] = $queryResult['name_check'];
		}

		// 返回输出结果
		return $this->parseData(0, $this->queryResult);
	}
	/**
	 * 纪录日志接口，必须调用父类的方法
	 */
	protected function saveLog(){
		// 查询数据
		$postData = $this->reqData;
		if( !is_array($postData) ){
			return FALSE;
		}

		$dbData = [
			'log_id' => 0, 
			'name' => $postData['name'], 
			'idcode' => strtoupper($postData['idcode']), 
			'enroldate' => $postData['enroldate'], 
			'graduate' => $postData['graduate'], 
			'educationdegree' => $postData['educationdegree'], 
			'studystyle' => intval($postData['studystyle']), 
			'create_time' => time(),
		];
		
		// 结果数据
		$queryResult = $this->queryResult;
		if( is_array($queryResult) ){
			$dbData['status'] = $queryResult['status']; 
			$dbData['gradudate'] = $queryResult['gradudate'];  
			$dbData['enroldate_check'] = $queryResult['enroldate_check']; 
			$dbData['graduate_check'] = $queryResult['graduate_check']; 
			$dbData['educationdegree_check'] = $queryResult['educationdegree_check']; 
			$dbData['studystyle_check'] = $queryResult['studystyle_check'];
		}


		$this->eduLogModel -> attributes = $dbData;
		if (!$this->eduLogModel->validate()) {
			$this->dayLog(
				'eduroll',
				'saveLog',
				'error',$this->eduLogModel->errors
			);
			
			return false;
		}
		$result = $this->eduLogModel-> save();
		return $result;
	}
	/**
	 * 获取查询信息
	 */
	private function getDbInfo($postData){
		if( !is_array($postData) ){
			return null;
		}
		
		// 从真实学籍表中查询数据，检查是否存在了原纪录: 单条过滤
		$one = $this->eduModel -> chkData($postData);
		if( $one ){
			$this->fromtype = 1;
			return $one;
		}
		
		// 从日记表里面获取它曾经提交过的纪录: 多条过滤
		$one = $this->eduLogModel ->chkLogs($postData);
		if( $one ){
			$this->fromtype = 2;
			return $one;
		}
		
		return null;
	}
	/**
	 * 从神州融接口获取数据
	 */
	private function getInterfaceInfo($postData){
		if( !is_array($postData) ){
			return null;
		}
		if( YII_ENV_DEV ){
			return null;
		}
		// 从神州融获取数据
		$res = $this->eduRollCrypt -> get($postData);
		// 是否有错误信息 纪录访问日志
		if( $res === false ){
			$response_error = $this->eduRollCrypt -> getError();
			$this->apiLogAcess($response_error, $postData);
			return null;
		}
		$this->apiLogAcess(null, $postData, $res);
		
		//$one = $this->eduModel->getByIdcode($postData['idcode']);
		$postData['enroldate_check'] = intval($res['RXRQCHECKRS']);
		$postData['graduate_check'] = intval($res['YXMCCHECKRS']);
		$postData['educationdegree_check'] = intval($res['CCCHECKRS']);
		$postData['studystyle_check'] = intval($res['XLLBCHECKRS']);
		$postData['gradudate'] = (string)($res['YJBYRQ']);
		$postData['status'] =$this->eduModel->getStatus($postData);
		return $postData;
	}
	/**
	 * 纪录api访问日志
	 */
	private function apiLogAcess($response_error, &$postData, &$res=null){
		if( $response_error ){
			// 有错误时
			$this->dayLog(
				'eduroll',
				'apiLogAcess',
				'error',$response_error,
				'提交数据',$postData
			);
			
		}else{
			// 成功时
			$this->dayLog(
				'eduroll',
				'apiLogAcess',
				'sucess',$res,
				'提交数据',$postData
			);
			
			
		}
	}
	
	/**
	 * 转换数据格式到开发平台的形式
	 */
	protected function parseData( $status, $data ){
		// 纪录本次日志，可纪录成功，则不在纪录系统日志
		$result = $this->saveLog();
		if( $result ){
			$this->isLog = FALSE;
		}
		
		return $this->resp($status, $data);
	}

}
