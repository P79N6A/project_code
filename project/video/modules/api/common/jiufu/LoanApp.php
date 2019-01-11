<?php
namespace app\modules\api\common\jiufu;
/**
 * 仅用于数据整合
 */
class LoanApp {
	public $errinfo;
	public function returnError($result, $errinfo){
		$this->errinfo = $errinfo;
		return $result;
	}
	/**
	 * 发送请求
	 * @param  [type] $transSerialNo [description]
	 * @return [type]                [description]
	 */
	public function get($transSerialNo, $data) {
		$transhead = $this->getHead($transSerialNo);
		$transBody = $this->getBody($data);
		$data = [
			'TransHead' => $transhead,
			'TransBody' => $transBody,
		];
		return $data;
	}
	/**
	 * 获取头信息
	 * @param  [type] $transSerialNo [description]
	 * @return [type]                [description]
	 */
	private function getHead($transSerialNo) {
		$oHead = new TransHead();
		$transhead = $oHead->getLoan($transSerialNo);
		return $transhead;
	}
	/**
	 * 组织数据
	 * @param  [] $data
	 * @return []
	 */
	public function getBody($data) {
		// 联系人信息
		$recordLoanContact = $this->getContact($data);

		// 附件信息
		$recordLoanAttach = $this->getAttach($data);

		// 附加信息
		$extendMap = $this -> getExtendMap($data);

		return [
			'initStats' => '2', // String  初始状态（必填）3 待运营审核
			'instCode' => '110841', // String  所属机构（必填）
			'saleChannel' => '1037', // String  销售渠道（必填）
			'customerName' => $data['customerName'], // String  客户名称（必填）
			'phone' => $data['phone'], // String  客户手机号（必填）
			'customerSex' => $data['customerSex'], // String  性别（必填） // N0201 男; N0202 女
			'degree' => 'B0305', // String  学历（必填）
			'email' => $data['email'], // String  邮箱（必填）
			'certType' => 'B1301', // String  证件类型（必填）（填B1301）
			'certId' => $data['certId'], // String  证件号码（必填）

			// 职业
			//'intustry' => 'B1016', // String  所属行业
			'intustry' => 'B1025', // String  所属行业
			'duty' => 'B2910', // String  职务级别

			// 银行卡
			'receiveName' => $data['receiveName'], // String  收款卡开户人姓名（必填）
			'receiveBankCard' => $data['receiveBankCard'], // String  收款银行卡号（必填）
			'receiveOpen' => $data['receiveOpen'], // String  收款银行编码（必填）
			'receiveBranch' => $data['receiveBranch'], // String  收款支行（必填）
			'oldAppId' => $data['oldAppId'],//新加订单号

			'repayName' => $data['receiveName'], // String  还款卡开户人（必填）
			'repayBankCard' => $data['receiveBankCard'], // String  还款银行卡号（必填）
			'repayOpen' => $data['receiveOpen'], // String  还款银行（必填）
			'repayBranch' => $data['receiveBranch'], // String  还款支行（必填）

			'receiveProvince' => $data['receiveProvince'], // String  收款省（必填）
			'receiveCountry' => $data['receiveCountry'], // String  收款市/县（必填）
			'receiveCountryCode' => $data['receiveCountryCode'], // String  收款市编号（必填）

			//产品编号
			'productId' => $data['productId'], // String  产品编号（必填）215
			'productName' => $data['productName'], // String  产品名称（必填）
			
			'appayAmt' => $data['appayAmt'], // String  申请金额（必填）
			'appayDate' => date('Y-m-d'), // String  申请时间（必填）
			'loanPurpose' => $data['loanPurpose'] ? $data['loanPurpose'] : 'F1199', // String  贷款用途（必填）
			'timeLimit' => $data['timeLimit'], // String  申请期限（必填）
			'orgCode' => 'JFB', // String  运营机构（必填）（填JFB）
			'isSignContact' => '2', // String  是否需要签合同（必填）"1为在BUS签合同 2为不在bus签合同"
			'isCalTotalAmount' => '1', // String  是否计算合同金额 （必填）"1为在bus计算合同金额 2为不在bus计算合同金额"
			'isPayPlan' => '1', // String  是否需要生成还款计划（必填）"1为bus生成还款计划 2不需要bus生成还款计划"
			'loanTarget' => '1', // String  放款类型 （必填）"1为放给借款人 2为放给借款人然后转给机构"

			'repaymentInitiator' => '2', // String  还款发起方 "1为bus 2为合作方"（必填）
			'isRepayMent' => '2', // String  是否bug还款 "1：是 2：否"（必填）
			'isSupportDeduction' => '2', // String  是否支持折半扣款 "1:支持 2:不支持"
			'isCard' => '1', // String  卡或金账户还款1:卡2：金账户（必填）
			'isOpenCard' => '1', // String  是否开户1是开户（必填）

			'customerProperty' => 'F2501', // String  客户性质（必填）F2501:受薪客户; F2502:私营业主
			'interestRule' => 'B2006', // String  计息规则（还款方式）(必填) #B2006:一次性还本付息
			'approveSuggestAmt' => $data['appayAmt'], // String  审批金额（必填）
			'loanTerm' => $data['timeLimit'], // String  审批期限（必填）
			'marry' => 'B0506', // String  婚姻状况（必填）: B0506未知

			'liveaddressProvince' => $data['liveaddressProvince'], // String  住宅所在地省（必填）(住宅地址)
			'liveaddressCity' => $data['liveaddressCity'], // String  住宅所在地市（必填）(住宅地址)
			'liveaddressDistinct' => $data['liveaddressDistinct'], // String  住宅所在区（必填）(住宅地址)
			'liveaddressRoad' => $data['liveaddressRoad'], // String  住宅所在道路信息（必填）(住宅地址)

			'recordLoanContact' => $recordLoanContact, // List RecordLoanContact 联系人信息
			'recordLoanAttach' => $recordLoanAttach, //$recordLoanAttach, //  List RecordLoanAttach  附件信息
			'extendMap' => $extendMap, // Map 其他增加的信息


			// 公司与学校两者必选其一: 目前使用公司
			'company' => $data['company'], // String  工作单位
			'companyPhone' => $data['companyPhone'], // String  单位电话
			'companyAdressprovince' => $data['companyAdressprovince'], // String  单位所在省
			'companyAdressCity' => $data['companyAdressCity'], // String  单位所在市
			'companyAdressDist' => $data['companyAdressDist'], // String  单位所在区
			'companyAdressRoad' => $data['companyAdressRoad'], // String  详细地址
			'companyType' => $data['companyType'], // String  单位性质 'B0908'其它, 
			'beginCompanyDate' => $data['beginCompanyDate'],// String  起始服务（成立）时间

			// 选填字段
			// 'instLoanCard' => '', // String  放款机构帐号 （必填）
			// 'instLoanCardName' => '', // String  放款机构帐号名称（必填）
			//'cooperateCode' => '', // String  合作机构
			// 'registAddressProvince' => '未知', // String  户口所在省
			// 'registAddressCity' => '未知', // String  户口所在市
			// 'registAddressDistrict' => '未知', // String  户口所在区
			// 'registAddress' => '未知', // String  详细地址
			// 'resideDate' => '未知', // String  起止居住时间
			// 'toCityDate' => '未知', // String  申请人来申请城市的年月
			// 'customerManagerId' => '', // String  客户经理代码
			// 'customerManagerName' => '', // String  客户经理姓名
			//'postAddress' => '未知', // String  邮寄地址
			//'salaryAmt' => '未知', // String  每月薪金（元）
			//'businessAmt' => '未知', // String  月均营业额
			//'salaryDay' => '未知', // String  每月支薪日
			//'otherIncomeAmt' => '未知', // String  其他收入
			//'incomeType' => '未知', // String  发薪方式

			// end 选填
		];
	}
	/**
	 * 联系人信息
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	private function getContact(&$data){
 		return [
			[
				'contactName' => $data['contactName'], 
				'contactRelation' => $data['contactRelation'],
				'contactPhone' => $data['contactPhone'], 
			],
		];
	}
	/**
	 * 手持身份证
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	private function getAttach(&$data){
		return [
			'fileId' => $data['fileId'],
			'attachName' => $data['attachName'],
		];
	}
	/**
	 * 扩展附加信息
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	private function getExtendMap(&$data){
		$extend = [
			//'ySXTransNote' => $data['ySXTransNote'], // String  运单类(json对象传输)
			//'ysxId'=> $data['ysxId'], // Integer 预授信公司标识
			'riskGrade' => 'A', // String  信用等评级（必填）
			'score' => '80', // String  信用评分（必填）
			'contractCode' => $data['contractCode'], // String  借款合同编号（选填）
			//'householdRegister' => $data['householdRegister'], // String  户籍属性
			'career' => 'F12324', // String  借款人职业（必填）
			// 'appraisalFee' => $data['appraisalFee'], // String  评估费
			// 'GPSFee' => $data['GPSFee'], // String  GPS安装费
			// 'rentalFee' => $data['rentalFee'], // String  租赁物损益费
			// 'channelFee' => $data['channelFee'], // String  渠道服务费
			// 'totalCost' => $data['totalCost'], // String  费用合计
			// 'loanPurposeOther' => $data['loanPurposeOther'], // String  其他贷款用途

			// 公司与学校两者必选其一: 目前使用公司
			'schoolName' => "北京大学",//$data['schoolName'], // String  学校名称
			'schoolYear' => date('Y'),//$data['schoolYear'], // String  入学年份

			'phonePassword' => $data['phonePassword'], // String  手机服务密码（必填）
			'borrowerType' => 'B154001',// 借款人类型  B154001:个人; B154002:法人; B154003:机构
			'channelCompanyName' => '先花信息技术（北京）有限公司',
		];
		$extendMap = $this->toKv($extend);
		return $extendMap;
	}
	/**
	 * 字典转换
	 * @param  [] $data 
	 * @return []
	 */
	private function toKv(&$data){
		$newdata = [];
		foreach ($data as $key => $value) {
			$newdata[] = [ 'key' => $key, 'value' => $value ];
		}
		return $newdata;
	}

}