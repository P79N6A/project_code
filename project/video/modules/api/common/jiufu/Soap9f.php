<?php
namespace app\modules\api\common\jiufu;
use app\common\Logger;
use SoapClient;
use yii\helpers\ArrayHelper;

/**
 * soap处理类封装
 */
class Soap9f {
	private $config;
	public function __construct() {
		$this->config = JFConfig::model()->getConfig();
	}

	/**
	 * 1. soap: oss
	 * @param  str $req_id
	 * @param  str $img_name
	 * @param  str $img_url
	 * @return [res_code,res_data]
	 */
	public function oss($transSerialNo, $img_name, $img_url) {
		//1 组合数据
		$oFileUpload = new OssFileInfo;
		$data = $oFileUpload->get($transSerialNo, $img_name, $img_url);
		if (!$data) {
			return ['res_code' => 'OSS_INNER_ERROR', 'res_data' => '持证自拍照数据不合法'];
		}

		//2 调用接口
		$client = new SoapClient($this->config['oss_url'], ['trace' => 1]);
		$res = $client->ossUpload($data);
		$response = $this->parseReponse($res);

		//3 纪录日志
		Logger::dayLog('9f', '1.oss',
			$client->__getLastRequest(),
			$client->__getLastResponse(),
			$response
		);
		return $response;
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
		//1 转换映射 由db->api接口
		$ext = json_decode($remit['tip'], true);

		// 产品编号
		$productId = $remit['product_id'];
		if ($productId == '215') {
			$productName = '先花花';
		} elseif ($productId == '253') {
			$productName = '先花花(优选)';
		}
		$data = [
			// 基本信息
			'customerName' => $remit['guest_account_name'],
			'phone' => $remit['user_mobile'], // String  客户手机号（必填）
			'certId' => $remit['identityid'], // String  证件号码（必填）
			'customerSex' => $remit['customer_sex'], // String  性别（必填） N0201 男; N0202 女
			'email' => $ext['email'], // String  邮箱（必填）
			'oldAppId' => $remit['client_id'],//新加订单号

			// 借款人卡信息
			'receiveName' => $remit['guest_account_name'], // String  收款卡开户人姓名（必填）
			'receiveBankCard' => $remit['guest_account'],
			'receiveOpen' => $remit['bank_code'], // String  收款银行编码（必填）
			'receiveBranch' => $remit['guest_account_bank_branch'], // String  收款支行（必填）
			'receiveProvince' => $remit['guest_account_province'], // String  收款省（必填）
			'receiveCountry' => $remit['guest_account_city'], // String  收款市/县（必填）
			'receiveCountryCode' => (string) $remit['city_code'], // String  收款市编号（必填） 1.1.1.26开户地区

			// 借款信息
			'timeLimit' => $remit['time_limit'], //申请期限（必填）
			'appayAmt' => $remit['settle_amount'], // 金额
			'loanPurpose' => $remit['loan_purpose'], // String  贷款用途（必填）

			// 附件信息
			'fileId' => $file_id, //28b89d7c-825c-4615-a9af-4c67f83fa22d
			'attachName' => $img_name,

			// 学校信息
			//'schoolName' => $ext['schoolName'], // String  学校名称
			//'schoolYear' => $ext['schoolYear'], // String  入学年份

			// 公司信息
			'company' => $ext['company'], // String  工作单位
			'companyPhone' => $ext['companyPhone'], // String  单位电话
			'companyAdressprovince' => $ext['companyAdressprovince'], // String  单位所在省
			'companyAdressCity' => $ext['companyAdressCity'], // String  单位所在市
			'companyAdressDist' => $ext['companyAdressDist'], // String  单位所在区
			'companyAdressRoad' => $ext['companyAdressRoad'], // String  详细地址
			'companyType' => isset($ext['companyType']) ? $ext['companyType'] : 'B0908', // String  单位性质
			// 'beginCompanyDate' => isset($ext['beginCompanyDate']) ? $ext['beginCompanyDate'] : '', // String
			'beginCompanyDate' => date('Y-m-d'),

			// 产品编号
			'productId' => $productId,
			'productName' => $productName,

			// 住宅信息
			'liveaddressProvince' => $ext['liveaddressProvince'], // String  住宅所在地省（必填）(住宅地址)
			'liveaddressCity' => $ext['liveaddressCity'], // String  住宅所在地市（必填）(住宅地址)
			'liveaddressDistinct' => $ext['liveaddressDistinct'], // String  住宅所在区（必填）(住宅地址)
			'liveaddressRoad' => $ext['liveaddressRoad'], // String  住宅所在道路信息（必填）(住宅地址)

			// 联系人信息
			'contactName' => $ext['contactName'],
			'contactRelation' => $ext['contactRelation'],
			'contactPhone' => $ext['contactPhone'],

			// extendmap
			'contractCode' => $ext['contractCode'],
			'phonePassword' => $ext['phonePassword'],

		];

		//2 组合数据
		$transSerialNo = $remit['client_id'];
		$oLoan = new LoanApp();
		$data = $oLoan->get($transSerialNo, $data);
		if (!$data) {
			return ['res_code' => 'LOAN_INNER_ERROR', 'res_data' => $oLoan->errinfo ? $oLoan->errinfo : '借款数据格式错误'];
		}
		Logger::dayLog('9f', '2.recordloan', 'request', 'client_id', $transSerialNo);

		//3 调用接口
		$client = new SoapClient($this->config['loan_url'], ['trace' => 1]);
		$res = $client->recordLoan($data);
		$response = $this->parseReponse($res);

		//4 生成日志返回
		Logger::dayLog('9f', '2.recordloan',
			$client->__getLastRequest(),
			$client->__getLastResponse(),
			$response
		);
		return $response;
	}
	/**
	 * 3. soap: 生成合同
	 * @param  string $appId
	 * @return  [res_code,res_data]
	 */
	public function generateContract($appId) {
		//1 调用接口
		$data = ['arg0' => $appId];
		$client = new SoapClient($this->config['loan_url'], ['trace' => 1]);
		$res = $client->generateContract($data);
		$response = $this->parseNewResponse($res);

		//2 纪录日志
		Logger::dayLog('9f', '5.generateContract',
			$client->__getLastRequest(),
			$client->__getLastResponse(),
			$response
		);
		return $response;

		/**
		 * returnCode
		 * returnMsg
		 * 000 成功, 其余失败
		 */
	}

	/**
	 * 5. soap: 请求签章
	 * @param  string $appId
	 * @return
	 */
	public function seeContract($appId) {
		//1 调用接口
		$data = ['arg0' => (string) $appId];
		$client = new SoapClient($this->config['loan_url'], ['trace' => 1]);
		$res = $client->seeContract($data);
		$response = $this->parseNewResponse($res);

		//2 纪录日志
		Logger::dayLog('9f', '5.seeContract',
			$client->__getLastRequest(),
			$client->__getLastResponse(),
			$response
		);
		return $response;
		/**
		 * returnCode
		 * returnMsg
		 * 000 成功, 其余失败
		 */
	}

	/**
	 * 本地aeskey标准化, 需要java处理
	 * @param  [] $data
	 * @return obj
	 */
	public function localStdKey($data) {
		$client = new SoapClient($this->config['local_stdkey_url'], ['trace' => 1]);
		$res = $client->encrypt9f($data);
		Logger::dayLog('9f', 'localstdkey',
			$client->__getLastRequest(),
			$client->__getLastResponse()
		);
		return $res;
	}
	/**
	 * 解析数据: oss, 借款工单
	 * @param  obj $response
	 * @return []
	 */
	private function parseReponse(&$response) {
		$res = ArrayHelper::toArray($response);
		$res_code = ArrayHelper::getValue($res, 'return.transHead.retCode');
		if ($res_code == '000000') {
			$res_data = ArrayHelper::getValue($res, 'return.transBody.entity');
		} else {
			$res_data = ArrayHelper::getValue($res, 'return.transHead.retMsg');
		}
		return [
			'res_code' => $res_code == '000000' ? 0 : $res_code,
			'res_data' => $res_data,
		];
	}
	/**
	 * 解析响应数据,别一种响应格式
	 * @param  [] $response [return->[returncode,returnMsg]]
	 * @return [res_code, res_data]
	 */
	private function parseNewResponse(&$response) {
		$return = ArrayHelper::getValue($response, 'return');
		if ($return) {
			$result = json_decode($return, true);
			$res_code = is_array($result) && $result['returnCode'] == '000' ? 0 : $result['returnCode'];
			return ['res_code' => $res_code, 'res_data' => $result['returnMsg']];
		} else {
			return ['res_code' => '_ERROR', 'res_data' => '无响应'];
		}

	}
}