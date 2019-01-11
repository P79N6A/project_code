<?php
namespace app\modules\api\controllers;

use Yii;
use app\modules\api\common\ApiController;
use app\modules\api\common\chanpay\ChanpayOnline;
use app\models\chanpay\ChanpayOnlineOrder;
use app\common\Logger;

/**
 * 畅捷单笔出款业务
 * 内部错误码范围1000-1999
 */
class ChanpayremitController extends ApiController
{

	/**
	 * 服务id号
	 */
	protected $server_id = 104;
	
	/**
	 * 畅捷接口文档
	 */
	private $chanpay;
	
	/**
	 * 初始化
	 */
	public function init(){
		//parent::init();
		$env = YII_ENV_DEV ? 'dev' : 'prod';
		$this->chanpay = new ChanpayOnline($env);
	}
	
	/**
	 * 畅捷单笔出款业务
	 */
    public function actionIndex()
    {
    	//1 字段检测
    	//$data = $this->reqData;
    	$data = [
    		    'aid' => 1, //订单ID
    			'orderid' => date('YmdHis').rand(10000,99999),  //姓名
    			'amount' => '1', //出款金额
    			'account_no' => '6226880086302882',  //
    			'account_name' => '高炼',
    			'identity' => '429001198507070016',
    			'remit_type' => 1,
    			'account_bank' => '中国工商银行',
    			'mobile' => '13269311057',
    			'callbackurl' => 'http://182.92.80.211:8091/api/chanpayback/onlinenotify',
    	];
    	
    	if( !isset($data['aid']) ){
    		return $this->resp(10001, "应用id不能为空");
    	}
    	if( !isset($data['orderid']) ){
    		return $this->resp(10002, "订单号不能为空");
    	}
    	if( !isset($data['account_no']) ){
    		return $this->resp(10003, "账号不能为空");
    	}
    	if( !isset($data['account_name']) ){
    		return $this->resp(10004, "账户名称不能为空");
    	}
    	if( !isset($data['amount']) ){
    		return $this->resp(10004, "出款金额不能为空");
    	}
    	if( !isset($data['identity']) ){
    		return $this->resp(10004, "身份证号不能为空");
    	}
    	if( !isset($data['callbackurl']) ){
    		return $this->resp(10004, "异步通知URL不能为空");
    	}
    	
    	$client_id = merchant_id.date('ymdHis').rand(10000,99999);
		
		$condition = array(
				'aid' => $data['aid'],
				'req_id' => $data['orderid'],
				'client_id' => $client_id,
				'settle_amount' => $data['amount'],
				'settle_fee' => '0',
				'real_amount' => $data['amount'],
				'remit_type' => $data['remit_type'],
				'remit_status' => 0,
				'rsp_status' => '',
				'rsp_status_text' => '',
				'identityid' => $data['identity'],
				'user_mobile' => $data['mobile'],
				'guest_account_name' => $data['account_name'],
				'account_type' => 0,
				'guest_account_bank' => $data['account_bank'],
				'guest_account' => $data['account_no'],
				'callbackurl' => $data['callbackurl'],
		);
		 
		$chanpayOnlineOrder = new ChanpayOnlineOrder;
		$result = $chanpayOnlineOrder->saveOrder($condition);

// 		$returnData = array(
// 				'rsp_code' => '0000',
// 				'orderid'  => $data['orderid'],
// 				'url' => $url
// 		);
		 
// 		return json_encode($returnData);
		header('Location: '.$url);
		exit;
    } 
}
