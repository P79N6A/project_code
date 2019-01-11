<?php
namespace app\modules\api\controllers;

use Yii;
use app\models\BankSupport;
use app\modules\api\common\ApiController;
use app\modules\api\common\chanpay\ChanpayOnline;
use app\models\chanpay\ChanpayOnlineOrder;
use app\common\Logger;

/**
 * 畅捷网银支付
 * 内部错误码范围1000-1999
 */
class ChanpayonlineController extends ApiController
{

	/**
	 * 服务id号
	 */
	protected $server_id = 103;
	
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
	 * 畅捷网银接口调用
	 */
    public function actionIndex()
    {
    	//1 字段检测
    	//$data = $this->reqData;
    	$data = [
    		    'aid' => 1, //订单ID
    			'orderid' => date('YmdHis').rand(10000,99999),  //姓名
    			'amount' => 10, //身份证号
    			'productcatalog' => 1,  //
    			'productname' => '购买电子产品',
    			'mobile' => '13269311057',
    			'userip' => '127.0.0.1',
    			'notify_url' => 'http://open.xianhuahua.com/api/chanpayback/onlinenotify',
    			'return_url' => 'http://open.xianhuahua.com/api/chanpayback/onlinereturn'
    	];
    	
    	if( !isset($data['aid']) ){
    		return $this->resp(10001, "应用id不能为空");
    	}
    	if( !isset($data['orderid']) ){
    		return $this->resp(10002, "订单号不能为空");
    	}
    	if( !isset($data['amount']) ){
    		return $this->resp(10003, "充值金额不能为空");
    	}
    	if( !isset($data['mobile']) ){
    		return $this->resp(10004, "充值手机号不能为空");
    	}

    	
    	$amount = 1;
		$url = $this->chanpay->online($data['aid'].'_'.$data['orderid'], $data['productname'], $data['amount']/100);
		
		$condition = array(
				'aid' => $data['aid'],
				'orderid' => $data['orderid'],
				'aid_orderid' => $data['aid'].'_'.$data['orderid'],
				'currency' => 156,
				'productcatalog' => $data['productcatalog'],
				'productname' => $data['productname'],
				'amount' => $data['amount'],
				'mobile' => $data['mobile'],
				'terminaltype' => 1,
				'orderexpdate' => 60,
				'userip' => $data['userip'],
				'callbackurl' => $data['notify_url'],
				'fcallbackurl' => $data['return_url'],
				'version' => 1,
				'paytypes' => '2',
				'chanpayborderid' => '0',
				'error_msg' => '0',
				'chanpay_url' => $url,
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
    
//     public function actionBank(){
//     	$result = $this->chanpay->getpaychannel();
//     	$banklist = json_decode(json_decode($result)->pay_inst_list);
//     	$count = count($banklist);
//     	for($i=1; $i<$count; $i++){
//     		if($banklist[$i]->cardType == 'DC'){
//     			$card_type = 1;
//     		}else{
//     			$card_type = 2;
//     		}
    		
//     		if($banklist[$i]->payMode == 'QPAY'){
//     			$pay_model = 2;
//     		}else if($banklist[$i]->payMode == 'ONLINE_BANK'){
//     			$pay_model = 1;
//     		}else{
//     			$pay_model = 3;
//     		}
//     		$BankSupport = new BankSupport();
//     		$BankSupport->bankname = $banklist[$i]->instName;
//     		$BankSupport->bankcode = $banklist[$i]->instCode;
//     		$BankSupport->weight = 10;
//     		$BankSupport->card_type = $card_type;
//     		$BankSupport->pay_type = 103;
//     		$BankSupport->pay_model = $pay_model;
//     		$BankSupport->province = 0;
//     		$BankSupport->create_time = time();
    		
//     		$BankSupport->save();
//     	}
//     }
}
