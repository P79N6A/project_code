<?php
namespace app\modules\api\common\jiufu;

/**
 * 9富api整合接口;
 * 这个是这个包对外开放的唯一接口.
 * 录单--生成合同--查看合同--更改状态至电子签章--电子签章--上传附件--第三方运营审核--...--放款
 *     提交工单: F0220
 *   生成合同: generateContract : F0220 -> F0222
 *   请求签名: seeContract : F0222 -> F0223
 *   确认签名: getSealCommon: F0223 -> F0225
 */
class JFApi {
	/**
	 * 环境
	 * @var string
	 */
	private $env;

	public function __construct($env) {
		JFConfig::$env = $env; // 配置系统环境
	}
	/**
	 *1. soap: oss
	 * @param  str $req_id
	 * @param  str $img_name
	 * @param  str $img_url
	 * @return [res_code,res_data]
	 */
	public function oss($transSerialNo, $img_name, $img_url) {
		$oSoap9f = new Soap9f;
		$res = $oSoap9f->oss($transSerialNo, $img_name, $img_url);
		return $res;
	}
	/**
	 * 2. soap: 借款工单
	 * db->api字段映射
	 * @param  [] $remit
	 * @param str $img_name 附件名称
	 * @param str $file_id 附件id
	 * @return  [res_code,res_data]
	 */
	public function recordLoan($remit, $img_name, $file_id) {
		$oSoap9f = new Soap9f;
		$res = $oSoap9f->recordLoan($remit, $img_name, $file_id);
		return $res;
	}
	/**
	 * 3. soap: 生成合同
	 * @param  string $appId 
	 * @return 
	 */
	public function generateContract($appId) {
		$oSoap9f = new Soap9f;
		$res = $oSoap9f->generateContract($appId);
		return $res;
	}
	/**
	 * 4. rsa: 查询合同接口
	 * @param  string $appId 
	 * @param  string $productId
	 * @return
	 */
	public function queryContract($appId,$productId) {
		$api = new LoanQuery;
		$res = $api->queryContract($appId,$productId);
		return $res;
	}
	/**
	 * 5. soap: 请求签章
	 * @param  string $appId 
	 * @return
	 */
	public function seeContract($appId) {
		$oSoap9f = new Soap9f;
		$res = $oSoap9f->seeContract($appId);
		return $res;
	}

	/**
	 * 6. rsa: 确认电子签章
	 * @param  string $appId 
	 * @return
	 */
	public function getSealCommon($appId) {
		$api = new LoanQuery;
		$res = $api->getSealCommon($appId);
		return $res;
	}

	/**
	 * 7. rsa: 订单查询接口
	 * @param  int $appId
	 * @param  int $productId
	 * @return [res_code, res_data]
	 */
	public function query($appId, $productId) {
		$api = new LoanQuery;
		$res = $api->query($appId, $productId);
		return $res;
	}
	/**
	 * 8. rsa: 结束订单接口
	 * @param  int $appId
	 * @param  int $productId
	 * @return bool
	 */
	public function loanend($appId, $productId) {
		$api = new LoanQuery;
		$res = $api->loanend($appId, $productId);
		return $res;
	}	
	/**
	 * 9. 支付结果接口
	 * @param  int $oRemit
	 * @return bool
	 */
	public function queryPay($oRemit) {
		$api = new LoanQuery;
		$status = $api->queryPay($oRemit);
		return $status;
	}
}