<?php
/**
 * 易宝API投资通服务
 * 内部错误码范围2600-2699
 * @author lijin
 */
namespace app\modules\api\controllers;
use Yii;
use app\modules\api\common\ApiController;

use app\modules\api\common\yeepay\YeepayTzt;


class YeepaytztController extends ApiController
{
	/**
	 * 服务id号
	 */
	protected $server_id = 101;
	
	/**
	 * 易宝类
	 */
	private $yeepay;
	
	
	/**
	 * 初始化
	 */
	public function init(){
		parent::init();
		$env = YII_ENV_DEV ? 'dev' : 'prod';
		$aid = $this->appData['id'];
		$this->yeepay = new YeepayTzt($env, $aid);
	}

	// 使用actions 重用
	public function actions(){
		$common = [
			    'class' => '\app\modules\api\controllers\actions\YeepaytztAction',
		    	'reqData' => $this->reqData,
		    	'appData' => $this->appData,
		    	'reqType' => 'json',
		];
		
		return [
			
			// 绑定接口
			'invokebindbankcard' => $common,
			// 确认确定
			'confirmbindbankcard' => $common,
			// 直接支付 错误码范围 2630-2650
			'directbindpay' => $common,
			// 查询订单
			'queryorder' => $common,
			// 银行卡查询
			'bankcardcheck' => $common,
		]; 
	}

	/*****************************start 短验支付接口处理*****************************/
	// 1. 支付请求
	public function actionPayrequest(){
		$postData = $this->post();
    	$result = $this->yeepay -> payrequest($postData);
		return $this->parseData($result);
	}
	
	// 2. 发送短信验证码
	public function actionValidatecodesend(){
    	$yeepay = $this->yeepay;
		$orderid = $this->post('orderid');
    	$result = $yeepay -> validatecodesend($orderid);
		return $this->parseData($result);
	}
	
	// 3. 确认短信验证码
	public function actionConfirmvalidatecode(){
    	$yeepay = $this->yeepay;
		$orderid = $this->post('orderid');
		$smscode = $this->post('smscode');
    	$result = $yeepay -> confirmvalidatecode($orderid, $smscode);
		return $this->parseData($result);
	}
	/*****************************end 短验支付接口处理*****************************/

	// 异步回调测试
    public function actionAsyncPayResult()
    {
    	// @todo
    	$data = 'Q3Xea8BcHZE35YHFINMVwo9WsJumr96fD2/617RCYA0NCApPWW2HS5aSNMejLvRqSAm+7EbdJfO/mDXin0XuQE4MpC2f32W6tjCu2xhTsxZmJ/YUubSRgATfGMew7p/tgcVu638CX4hvcgqoX4TJ00InoXBiCxpbNfxhcqSWtPm1hgVxdd/chFOtySVOJ8WuGlxT3OpLxi5eFwwPquz/1jkidNJHfnX3jgGAm4xKJJ9PdcfYjSNZQ8ljsR+0Xj0wpxkanVNI4XNX8b5UHqUVQL1StgCCFAKlrqwkBIq+nPRaStWTE8GlR00eIlI4MEy/0Ic0CcgOHOo95w7K8J2FBfOKPpqYF7gDwXGWCE93lFney+9Ssr8Hh+g4qBXBGxzIFfbmIEcKXbl1hFamgI/jT7FGfpfX3nTwXQFtzni2YeQ=';
		$encryptkey = 'LvmeJKdoFFqWGoqv7MC0laidRe7EJDgiBx/+l518oEiMnxmFBd1rVvgkHoh9iRJHALXEzx0/1vwQN3r//q5ueJ5/vEHq6A0TLfyZ/UzAw6QPwmDX7tqf2Jv6YB7SXnuftl/Rg3IUwLKn1MDAvXMDSHG+IKx1GR3d6/nEjltfPsY=';
		//$data = $this->get('data');
		//$encryptkey = $this->get('encryptkey');
		
    	$status = $this->yeepay -> payCallback( $data, $encryptkey  );
		echo "SUCCESS";
    }
	/**
	 * 转换数据格式到开发平台的形式
	 */
	protected function parseData( $result ){
		if( empty($result) ){
			return $this->resp(2601, "未能获取到数据");
		}
		if($result['error_code']){
			return $this->resp( $result['error_code'], $result['error_msg'] );
		}
		return $this->resp(0, $result);
	}
	//****************************易宝api接口使用**************************/
}
