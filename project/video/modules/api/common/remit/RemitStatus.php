<?php 
/**
 * 中信状态解析类
 * 由于中信状态有很多.出款状态和响应状态都不太一致
 * 故做一个类做映射关系
 * 输入字段为httpstatus, xml, data 即remitapi返回的结果
 * 输出字段为status, rsp_status, rsp_status_txt
 * @author lijin
 */
namespace app\modules\api\common\remit;
use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\models\remit\Remit;
class RemitStatus{
	// 输入字段
	public $xml;
	public $data;
	public $httpStatus;
	
	//主要输出字段
	public $remit_status;
	public $rsp_status;
	public $rsp_status_text;

	public function __construct(){

	}
	/**
	 * 获取配置文件中的数组
	 * @param  str $name 名称
	 * @return 返回的是个数组
	 */
	private function getConfig($name){
		$arr = [];
		$path = __DIR__ . "/config/{$name}.php";
		if( file_exists($path) ){
			$arr = include( $path );
		}
		return  is_array($arr) ? $arr : [];
	}
	/**
	 * 前六位是否全A: 一般这种情况是受理中的状态
	 * @param $rsp_status 
	 * @return bool
	 */
	private function statusIs6A($rsp_status){
		// 是否至少6位
		$len = strlen($rsp_status);
		if( $len < 6 ){
			return FALSE;
		}
		
		// 前六位是否全A
		$sub6  = substr($rsp_status, 0, 6);
		if($sub6 === 'AAAAAA'){
			return true;
		}
		return false;
	}
	/**
	 * 解析出款接口的响应结果
	 *  remit_status : INIT->HTTP_NOT_200,  FAILURE, DOING, SUCCESS(暂不考虑) @todo
	 * @param $response
	 * @return bool
	 */
	public function parseRemitStatus($response){
		//1 判断格式是否正确
		if(!is_array($response) || empty($response)){
			return FALSE;
		}
		$this->httpStatus = ArrayHelper::getValue($response, 'httpStatus');
		$this->data = ArrayHelper::getValue($response, 'data');
		$this->xml  = ArrayHelper::getValue($response, 'xml');
		
		//2 解析响应结果
		if($this->httpStatus !== 200){
			// http!=200 响应不正确时处理
			$this->rsp_status = 'HTTP_NOT_200';
			$this->rsp_status_text = $this->httpStatus ? "http响应状态".$this->httpStatus : '无响应';
			$this->remit_status = Remit::STATUS_HTTP_NOT_200;// 无响应状态
			return true;
		}
		if(!$this->data){
			return false;
		}
		
		//3 解析响应数据
		$this->rsp_status = ArrayHelper::getValue($response, 'data.status');
		$this->rsp_status_text = ArrayHelper::getValue($response, 'data.statusText');

		if($this->rsp_status === 'AAAAAAE'){
			// 这个肯定是受理中
			$this->remit_status = Remit::STATUS_DOING;
		}elseif($this->rsp_status === 'ED03074'){
			// 这个是超限了吗?
			$this->remit_status = Remit::STATUS_DOING;
		}elseif( $this->statusIs6A($this->rsp_status) ){
			//3 判断前6位是否全A
			$this->remit_status = Remit::STATUS_DOING;
		}else{
			$isFail = $this->remit_fails($this->rsp_status);
			if($isFail){
				$this->remit_status = Remit::STATUS_FAILURE;
			}else{
				$this->remit_status = Remit::STATUS_DOING;
			}
		}
		return true;
	}
	/**
	 * 解析查询接口的响应结果
	 *  remit_status : DOING-> FAILURE, DOING, SUCCESS(暂不考虑) @todo
	 * @param $response 含httpstatus, data, xml三个字段
	 * @return bool
	 */
	public function parseQueryStatus($response){
		//1 判断格式是否正确
		$this->httpStatus = ArrayHelper::getValue($response, 'httpStatus');
		$this->data = ArrayHelper::getValue($response, 'data');
		$this->xml  = ArrayHelper::getValue($response, 'xml');
		
		//2 解析响应结果
		if($this->httpStatus !== 200){
			// http!=200 响应不正确时处理
			$this->rsp_status = 'HTTP_NOT_200';
			$this->rsp_status_text = $response['httpStatus'] ? "http响应状态:".$response['httpStatus'] : '无响应';
			$this->remit_status = Remit::STATUS_DOING;
			return true;
		}
		if(!$this->data){
			return false;
		}

		//3 处理状态, 注意这个不一定是最终的响应状态
		/**
		 * $status
		 * ED02083,输入的客户流水号无制单信息,请检查输入项 @todo. 这个可以处理出款无响应的问题
		 * AAAAAAA 交易成功
		 */
		$status = ArrayHelper::getValue($this->data, 'status');
		$status_text = ArrayHelper::getValue($this->data, 'statusText');

		//4 判断是否6个a
		if( $this->statusIs6A($status) ){
			// 此时上面的状态不足以判断最终结果,还需要取子目录的信息
			$this->rsp_status = ArrayHelper::getValue($this->data, 'list.row.status');
			$this->rsp_status_text = ArrayHelper::getValue($this->data, 'list.row.statusText');
			if ("AAAAAAA" === $this->rsp_status) {
				$this->remit_status = Remit::STATUS_SUCCESS;
			} else if ($this->statusIs6A($this->rsp_status) ) {
				$this->remit_status = Remit::STATUS_DOING;
			} else {
				//查询结果中有list.row.stt项有几个值 0：成功；1：失败；2：未知；3：审核拒绝； 4：用户撤销
				//后续多观察. 如果确认就使用该项做为判断准则
				$isFail = $this->query_fails( $this->rsp_status );
				if($isFail){
					$this->remit_status = Remit::STATUS_FAILURE;
				}else{
					$this->remit_status = Remit::STATUS_DOING;
				}
			}
		}else{
			// 此时响应状态与$status一致
			$this->rsp_status = $status;
			$this->rsp_status_text = $status_text;
			
			$sub6  = substr($status, 0, 6);
			if( in_array($sub6, ['BBBBBBB','CCCCCCC','EEEEEEE','UNKNOWN']) ){
				// 下面的这些情况感觉很少会出现
				$this->remit_status = Remit::STATUS_DOING;
			}else{
				// 这种情况包含一些字段的错误之类的, 极有可能是失败的. 
				$isFail = $this->query_fails( $this->rsp_status );
				if($isFail){
					$this->remit_status = Remit::STATUS_FAILURE;
				}else{
					$this->remit_status = Remit::STATUS_DOING;
					// if(preg_match("/[a-zA-Z][a-zA-Z][0-9]*/", $status)){
					// 	//两位字母+数字的组合为网银错误代码，其他为后台错误代码。
					// 	$this->remit_status = Remit::STATUS_DOING;
					// }else{
					// 	$this->remit_status = Remit::STATUS_DOING;
					// }
				}
			}
		}
		return true;
	}
	// 出款接口检测是否是最终失败
	private function remit_fails($rsp_status){
		static $fails;
		if(!$fails){
			$fails = $this->getConfig('remit_fails');
		}
		return in_array($rsp_status, $fails);
	}
	// 查询接口检测是否是最终失败
	private function query_fails($rsp_status){
		static $fails;
		if(!$fails){
			$fails = $this->getConfig('query_fails');
		}
		return in_array($rsp_status, $fails);
	}

}