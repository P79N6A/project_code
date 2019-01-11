<?php
/**
 * 中信出款服务接口
 */
namespace app\modules\api\controllers;
use Yii;
use app\modules\api\common\ApiController;
use app\common\Crypt3Des;
use app\common\Logger;
use app\models\remit\Remit;
use app\models\remit\Setting;

class RemitController extends ApiController
{
	/**
	 * 服务id号
	 */
	protected $server_id = 13;
	
	protected $remitModel;
	 
	public function init(){
		parent::init();
		$this->remitModel = new Remit();
	}
	/**
	 * 路由首页
	 */
	private function actionIndex(){
		//1  参数验证
		$postData = $this->reqData;
		$aid = $this->appData['id'];

		//业务参数
		$req_id = isset( $postData['req_id'] ) ? $postData['req_id'] : '';
		$settle_amount = isset( $postData['settle_amount'] ) ? $postData['settle_amount'] : '';
		$settle_fee = 0.00;
		$real_amount = isset( $postData['settle_amount'] ) ? $postData['settle_amount'] : '';
		$remit_type = isset( $postData['remit_type'] ) ? $postData['remit_type'] : '';
		$user_mobile = isset( $postData['user_mobile'] ) ? $postData['user_mobile'] : '';
		$identityid = isset( $postData['identityid'] ) ? $postData['identityid'] : '';
		$guest_account_name = isset( $postData['guest_account_name'] ) ? $postData['guest_account_name'] : '';
		$guest_account_bank = isset( $postData['guest_account_bank'] ) ? $postData['guest_account_bank'] : '';
		$guest_account = isset( $postData['guest_account'] ) ? $postData['guest_account'] : '';
		$guest_account_province = isset( $postData['guest_account_province'] ) ? $postData['guest_account_province'] : '';
		$guest_account_city = isset( $postData['guest_account_city'] ) ? $postData['guest_account_city'] : '';
		$guest_account_bank_branch = isset( $postData['guest_account_bank_branch'] ) ? $postData['guest_account_bank_branch'] : '';
		$settlement_desc = isset( $postData['settlement_desc'] ) ? $postData['settlement_desc'] : '';
		$callbackurl = isset( $postData['callbackurl'] ) ? $postData['callbackurl'] : '';
		$account_type = 0;//账号类型：0对私；1对公
		$postData['account_type'] = $account_type;
		if( empty($req_id) || empty($settle_amount) || empty( $remit_type) || empty($user_mobile) || empty($identityid) || empty($guest_account_name) || empty($guest_account_bank) || empty($guest_account) || empty($callbackurl) ){
			Logger::dayLog( 'remit/error',print_r($postData,true) );
			return $this->resp(13001, "参数信息不完整");
		}
		//2 策略验证，出款最大金额不能>50000
		if( $settle_amount > 50000 ){
			Logger::dayLog( 'remit/error',$postData );
			return $this->resp(13002, "出款金额超限");
		}
		if( $settle_amount <=0 ){
			return $this->resp(13004, "出款金额必须大于0");
		}

		// 判断是否超限
		$oM = new Setting;
		$isDayMax = $oM->isDayMax($aid, $settle_amount );
		if($isDayMax){
			return $this->resp(13005, "单日出款超限");
		}


		//3 获取 应用id 保存数据
		$postData['aid'] = $aid;

		$postData['guest_account_province'] = $postData['guest_account_province'] == '北京' ? '北京市' : $postData['guest_account_province'];
		$postData['guest_account_city'] = $postData['guest_account_city'] == '北京' ? '北京市' : $postData['guest_account_city'];

		$resultData = $this->remitModel -> saveRemitData($postData);
		if(!$resultData){
			Logger::dayLog(
				'remit/error',
				'actionSaveRemitData',
				'remit 数据保存失败', 
				'提交数据', $postData,
				'错误原因',  $this->remitModel->errinfo
			);
			return $this->resp( 13003,  $this->remitModel->errinfo);
		}
		
		return $this->resp( 0, $resultData );
	}
	
	
	
}