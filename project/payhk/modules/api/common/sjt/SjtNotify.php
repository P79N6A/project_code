<?php
/**
 *  采集回调通知
 */
namespace app\modules\api\common\sjt;

use Yii;
use app\models\SjtRequest;
use app\models\JxlStat;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\common\Crypt3Des;
use app\models\App;
use app\models\YysClientNotify;
class SjtNotify
{     
    private $oSjt;
	public $errorInfo; 
    /**
     * 初始化接口
     */
    public function __construct($oSjt){
        $this->oSjt = $oSjt;
    }
    public function clientNotify(){
		//获取通知数据
		$data = $this->clientData();
		//post发送通知
		$result = $this->clientPost($this->oSjt->callbackurl, $data, $this->oSjt->aid);
		if($result){
			$client_status = 1;
			$res = $this->oSjt->saveClientStatus($client_status);
			if(!$res){
				Logger::dayLog('sjtnotify','saveClientStatus','保存失败',$this->oSjt->id,$result);
			}
		}
		$m = new YysClientNotify();
		//保存通知表
		$grabStatus = empty($result)?0:1;//1成功
		$res = $m->saveData($this->oSjt->requestid,$grabStatus);
		if(!$res){
			Logger::dayLog('sjtnotify','YysClientNotify/saveData','保存失败',$this->oSjt->requestid,$grabStatus);
		}
		return $result;
	}
	/**
	 * GET 回调通知客户端 url
	 * @return url
	 */
	public function clientBackurl() {
		$data = $this->clientData();
		$url =  $this->clientGet($this->oSjt->callbackurl, $data, $this->oSjt->aid);
		return $url;
	}
	/**
	 * 返回客户端响应结果
	 * @return  []
	 */
	private function clientData() {
		if($this->oSjt->result_status == SjtRequest::RESULT_STATUS_DOING){
			$status = 4;//拉取详单和报告
		}else if($this->oSjt->result_status == SjtRequest::RESULT_STATUS_SUCCESS){
			$status = 1;//成功
		}else{
			$status = 3;//失败
		}
		$data = [
			'requestid' => $this->oSjt->requestid,
			'phone' 	=> $this->oSjt->phone,
			'status' 	=> $status,
			'source' 	=> $this->oSjt->source,
			'from' 		=> $this->oSjt->from,
			'url' 		=> ''
		];
		return $data;
	}
	/**
	 * POST 异步通知客户端
	 * @return bool
	 */
	private function clientPost($callbackurl, $data, $aid) {
		//1 加密
		$res_data = App::model()->encryptData($aid, $data);
		$postData = ['res_data' => $res_data, 'res_code' => 0];

		//2 post提交
		$oCurl = new \app\common\Curl;
		$res = $oCurl->post($callbackurl, $postData);
		Logger::dayLog('sjtnotify/clientpost', 'post', "客户响应|{$res}|", $callbackurl, $data,$res);

		//3 解析结果
		$res = strtoupper($res);
		return $res == 'SUCCESS';
	}
	/**
	 * GET 页面回调链接
	 */
	private function clientGet($callbackurl, $data, $aid) {
		//1 加密
		$res_data = App::model()->encryptData($aid, $data);

		//2 组成url
		$link = strpos($callbackurl, "?") === false ? '?' : '&';
		$url = $callbackurl . $link . 'res_code=0&res_data=' . rawurlencode($res_data);
		return $url;
	}
}
