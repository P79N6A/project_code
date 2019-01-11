<?php
/**
 * 计划任务处理:反欺诈异步通知
 * 这个是反欺诈异步通知的逻辑类,相当于控制器功能
 * @author 
 */
namespace app\modules\api\common;
use app\common\ApiCrypt;
use app\common\Logger;
use app\common\ApiSign;
use app\models\antifraud\AfResult;
use app\models\StCreditRequest;
use app\models\StCreditResult;
use app\models\StNotify;
use app\models\StrategyRequest;
use app\models\Result;
use app\models\Request;
use yii\helpers\ArrayHelper;

set_time_limit(0);

class Notify {
	protected $request;
	protected $result;
	protected $StNotify;
	protected $logname;
	protected $res_status;
	private static $auth_key = "CUSO8YSu%TAusi@Q098x735E";
	/**
	 * 初始化接口
	 */
	public function __construct() {
		$this->request = new StrategyRequest;
		$this->StNotify = new StNotify;
		$this->result = new Result;
		$this->logname = 'antifraud';
		$this->res_status = [ 
					'1' => 'approval', //安全
					'2' => 'manual',//人工
					'3' => 'reject',//欺诈
				];
	}
	/**
	 * 一般是每几分钟执行
	 */
	public function runMinute($start_time, $end_time ) {
		//1 获取需要通知的数据
		$dataList = $this->StNotify->getClientNotifyList($start_time, $end_time);
		return $this->runNotify($dataList);
	}


	public function rundHappyMinute($start_time, $end_time)
    {
        //1 获取需要通知的数据
        $dataList = $this->StNotify->getClientNotifyHappy($start_time, $end_time);
        return $this->runNotifyHappy($dataList);
    }

	/**
	 * 执行所有通知
	 * 暂不开放
	 */
	protected function runAll() {
		//1 获取需要通知的数据
		$dataList = $this->StNotify->getClientNotifyList('0000-00-00', date('Y-m-d H:i:s'));
		return $this->runNotify($dataList);
	}
	/**
	 * 暂时五分钟跑一批:
	 * 处理异步通知
	 */
	public function runNotify($dataList) {
		//1 验证
		if (!$dataList) {
			return false;
		}

		//2 锁定状态为处理中
		$ids = ArrayHelper::getColumn($dataList, 'id');
		$ups = $this->StNotify->lockNotify($ids); // 锁定异步接口的请求
		if (!$ups) {
			return false;
		}

		//4 逐条处理
		$total = count($dataList);
		$success = 0;
		foreach ($dataList as $oNotify) {
			$result = $this->doNotify($oNotify);
			if ($result) {
				$success++;
			} else {
				// $oNotify->saveNotifyStatus($this->StNotify->gStatus('STATUS_INIT'), "未知错误");
				Logger::dayLog($this->logname, 'AntiNotify/runNotify', '处理失败', $oNotify);
			}
		}

		//5 返回结果
		return $success;
	}

    public function runNotifyHappy($dataList) {
        //1 验证
        if (!$dataList) {
            return false;
        }
        //2 锁定状态为处理中
        $ids = ArrayHelper::getColumn($dataList, 'id');
        $ups = $this->StNotify->lockNotify($ids); // 锁定异步接口的请求
        if (!$ups) {
            return false;
        }

        //4 逐条处理
        $total = count($dataList);
        $success = 0;
        foreach ($dataList as $oNotify) {
            $result = $this->doHappyNotify($oNotify);
            if ($result) {
                $success++;
            } else {
                // $oNotify->saveNotifyStatus($this->StNotify->gStatus('STATUS_INIT'), "未知错误");
                Logger::dayLog($this->logname, 'AntiNotify/runNotify', '处理失败', $oNotify);
            }
        }

        //5 返回结果
        return $success;
    }
	/**
	 * 处理单条出款
	 * @param object $oAnti
	 * @return bool
	 */
	protected function doNotify($oNotify) {
		//1 参数验证
		if (!$oNotify) {
			$ret = $oNotify->saveNotifyStatus( $this->StNotify->gStatus('STATUS_FAILURE'), '参数异常');
			return false;
		}

		//2 是否有回调链接地址
		$oAnti = $this->request->findOne($oNotify['st_req_id']);
		if (!$oAnti) {
			// Logger::dayLog($this->logname, 'AntiNotify/doNotify', 'oAnti/findOne', "没有这条纪录");
			$ret = $oNotify->saveNotifyStatus( $this->StNotify->gStatus('STATUS_FAILURE'), '无纪录');
			return false;
		}
		if (!$oAnti['callbackurl']) {
			$ret = $oNotify->saveNotifyStatus( $this->StNotify->gStatus('STATUS_FAILURE'), '无回调地址');
			return false;
		}
		// 按req_id（既st_strategy_request主键）查找request表数据
		$oRequest  = new Request();
		$request = $oRequest->getRequestByReqid($oNotify['st_req_id']);
		if(empty($request)){
			$ret = $oNotify->saveNotifyStatus( $this->StNotify->gStatus('STATUS_FAILURE'), '无请求Java记录');
			return false;
		}
		//3 获取返回结果
		$result_data = $this->result->getResData($request->request_id);
		if (!$result_data) {
			$ret = $oNotify->saveNotifyStatus( $this->StNotify->gStatus('STATUS_FAILURE'), '无决策结果');
			return false;
		}
		Logger::dayLog('notify', 'request_id',$request->request_id,', result_id',$result_data->id);
        //4 获取运营商分析的结果
        $oStResult = new AfResult();
        $result_opera = $oStResult->getByReqId($oAnti['req_id']);
        $result_subject = isset($result_opera['result_subject']) ? $result_opera['result_subject'] : '';
		//5 通知
		$data = [
              'strategy_req_id' => $oAnti['id'],
			  'req_id' => $oAnti['req_id'],
			  'loan_id' => $result_data->loan_id,
			  'user_id'   => $result_data->user_id,
			  'res_status'  => $this->res_status[$result_data->res_status],
              'result_subject' => $result_subject,
              'credit_subject' => $result_data->res_info,
		];
	 	$data_sign = (new ApiSign)->signData($data);
		$response = $this->curlPost($oAnti['callbackurl'], $data_sign);
		if ($response == 'SUCCESS') {
			$nextStatus = $this->StNotify->gStatus('STATUS_SUCCESS');
		} else {
			$nextStatus = $this->StNotify->gStatus('STATUS_RETRY');
			Logger::dayLog($this->logname, 'doNotify', '通知异常', $response, $data);
		}
		$reason = $response === false ? '无响应' : $response;
		if(!$reason){
			$reason="未知错误";
		}

		//4 保存状态
		$result = $oNotify->saveNotifyStatus($nextStatus, $reason);
		if (!$result) {
			Logger::dayLog($this->logname, 'AntiNotify/doNotify', 'AntiNotify/saveNotifyStatus', $oNotify->errors);
			return FALSE;
		}

		return true;
	}


    protected function doHappyNotify($oNotify) {
        //1 参数验证
        if (!$oNotify) {
            $ret = $oNotify->saveNotifyStatus( $this->StNotify->gStatus('STATUS_FAILURE'), '参数异常');
            return false;
        }

        //2 是否有回调链接地址
        $oAnti = $this->request->findOne($oNotify['st_req_id']);
        if (!$oAnti) {
            // Logger::dayLog($this->logname, 'AntiNotify/doNotify', 'oAnti/findOne', "没有这条纪录");
            $ret = $oNotify->saveNotifyStatus( $this->StNotify->gStatus('STATUS_FAILURE'), '无纪录');
            return false;
        }

        if (!$oAnti['callbackurl']) {
            $ret = $oNotify->saveNotifyStatus( $this->StNotify->gStatus('STATUS_FAILURE'), '无回调地址');
            return false;
        }
        Logger::dayLog('aa', 'ss', $oAnti['callbackurl']);
        // 按req_id（既st_strategy_request主键）查找request表数据
        $oRequest  = new Request();
        $request = $oRequest->getRequestByReqid($oNotify['st_req_id']);
        if(empty($request)){
            $ret = $oNotify->saveNotifyStatus( $this->StNotify->gStatus('STATUS_FAILURE'), '无请求Java记录');
            return false;
        }

        //3 获取返回结果
        $result_data = $this->result->getResDataHappy($request->request_id);
        if (!$result_data) {
            $ret = $oNotify->saveNotifyStatus( $this->StNotify->gStatus('STATUS_FAILURE'), '无决策结果');
            return false;
        }

        Logger::dayLog('notify', 'request_id',$request->request_id,', result_id',$result_data->id);


        $ost_credit_request = new StCreditRequest();

        $credit_request_data = $ost_credit_request->getOne(ArrayHelper::getValue($result_data, "user_id", 0));
        if (!$credit_request_data) {
            $ret = $oNotify->saveNotifyStatus( $this->StNotify->gStatus('STATUS_FAILURE'), '无测评结果');
            return false;
        }

//        //4 获取运营商分析的结果
//        $oStResult = new AfResult();
//        $result_opera = $oStResult->getByReqId($oAnti['req_id']);
//        $result_subject = isset($result_opera['result_subject']) ? $result_opera['result_subject'] : '';
        $res_info = json_decode(ArrayHelper::getValue($result_data, "res_info", ""), true);
        $credit_data = json_decode(ArrayHelper::getValue($credit_request_data, "credit_data", ""), true);
        //5 通知
        $data = [
            "mobile"				=> ArrayHelper::getValue($credit_request_data, "mobile", ""),
            "identity"				=> ArrayHelper::getValue($credit_data, "identity", ""),
            "credit_id"				=> ArrayHelper::getValue($oNotify, "st_req_id", ""),
            "credit_result"			=> ArrayHelper::getValue($result_data, "res_status", ""),
            "amount"				=> ArrayHelper::getValue($res_info, "AMOUNT", 0),
            "days"					=> ArrayHelper::getValue($res_info, "DAYS", 0),
        ];
        //$data_sign = (new ApiSign)->signData($data);
        $data_sign = (new ApiCrypt())->buildData($data, self::$auth_key);
        $result = [
            'res_code' => "0",
            'res_data'=> $data_sign,
        ];
        Logger::dayLog('aa', 'result', $data);

        $response = $this->curlPost($oAnti['callbackurl'], $result);
        Logger::dayLog('aa', '通知异常', $response);
        if ($response == 'SUCCESS') {
            $nextStatus = $this->StNotify->gStatus('STATUS_SUCCESS');
        } else {
            $nextStatus = $this->StNotify->gStatus('STATUS_RETRY');
            Logger::dayLog($this->logname, 'doNotify', '通知异常', $response, $data);
        }
        $reason = $response === false ? '无响应' : $response;
        if(!$reason){
            $reason="未知错误";
        }

        //4 保存状态
        $result = $oNotify->saveNotifyStatus($nextStatus, $reason);
        if (!$result) {
            Logger::dayLog($this->logname, 'AntiNotify/doNotify', 'AntiNotify/saveNotifyStatus', $oNotify->errors);
            return FALSE;
        }

        return true;
    }
	/**
	 * 提交数据
	 * @param array $data
	 * @param str data
	 * @return null
	 */
	protected function curlPost($url, $data) {
		// 1 计算log
		// $timeLog = new \app\common\TimeLog();

		//2 提前请求
		$curl = new \app\common\Curl();
		$curl->setOption(CURLOPT_CONNECTTIMEOUT, 20);
		$curl->setOption(CURLOPT_TIMEOUT, 20);
		$res = $curl->post($url, $data);
		$httpStatus = $curl->getStatus();

		//3 详细纪录请求与响应的结果
		// $timeLog->save($this->logname, [$url, $data, $httpStatus, $res]);

		return $res;
	}
	/**
	 * 加密
	 */
	protected function encryptData($aid, $data){
		// 加密信息
		try{
			$app = new App();			
			$encryptData =  $app->encryptData($aid, $data);
			return [ 'res_data' => $encryptData, 'res_code'=> 0];
		}catch(\Exception $e){
			// log_here
			return '';
		}
	}
}