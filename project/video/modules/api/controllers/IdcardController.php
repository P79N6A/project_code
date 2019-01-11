<?php
/**
 * 身份验证接口
 * 内部错误码范围9000-9999
 * @author lijin
 */
namespace app\modules\api\controllers;
use Yii;
use yii\helpers\ArrayHelper;
use app\modules\api\common\ApiController;
use app\common\Func;
use app\common\Logger;

use app\modules\api\common\idcard\IdCardApi;
use app\modules\api\common\idcard\IdCardPai;
use app\models\Idcard;
use app\models\IdcardLog;
/**
 * 身份检验接口
 */
class IdcardController extends ApiController
{
	/**
	 * 服务id号
	 */
	protected $server_id = 9;

	/**
	 * 马上金融接口文档
	 */
	private $oIdCardApi;
	private $oIdCardPai;
	
	/**
	 * 身份日志DB
	 */
	private $oLog;
	
	/**
	 * 初始化
	 */
	public function init(){
		parent::init();
		$env = YII_ENV_DEV ? 'dev' : 'prod';
		//$env = 'prod';
		$this->oIdCardApi = new IdCardApi($env);
		$this->oIdCardPai = new IdCardPai($env);
		
		// 身份证日志DB
		$this->oLog = new IdcardLog;
	}
	public function actionIndex(){
		//1 参数设置
		if( !isset($this->reqData['idcard'])  || empty($this->reqData['idcard'])  ){
			return $this->resp(9001, "身份证不能为空");
		}
		if( !isset($this->reqData['name'])  || empty($this->reqData['name'])  ){
			return $this->resp(9002, "姓名不能为空");
		}
		if( !isset($this->reqData['partner_trade_no'])  ){
			return $this->resp(9003, "订单号不能为空");
		}
//		if( !isset($this->reqData['callbackurl'])  ){
//			return $this->resp(9004, "回调地址不能为空");
//		}
		
		$name = $this->reqData['name'];
		$idcard= strtoupper($this->reqData['idcard']);	//设置身份证为大写例如x->X
		$partner_trade_no = $this->reqData['partner_trade_no'];
		$callbackurl = isset($this->reqData['callbackurl']) ? $this->reqData['callbackurl'] : '';
		
		//2 从本地DB获取, 若本地存在,则无法成功与失败均从本地校验
		$oCard = Idcard::getByIdcard($idcard);
		if( $oCard ){
			if( $oCard -> name == $name ){
				return $this->resp(0,[
					'status'  => IdcardLog::STATUS_OK, //0:初始; 1:采集中; 2:成功,
					'type'  => 'db',
					'name'  => $name,
					'idcard' => $idcard,
					'partner_trade_no' => $partner_trade_no,
				]);
			}else{
				return $this->resp(0,[
					'status'  => IdcardLog::STATUS_FAIL, //0:初始; 1:采集中; 2:成功,
					'type'  => 'db',
					'name'  => $name,
					'idcard' => $idcard,
					'partner_trade_no' => $partner_trade_no,
				]);
			}
		}
		
		//3 从日志中获取是否超限
		$result = $this->oLog -> chkQueryNum($idcard); 
		if( !$result ){
			return $this->resp(9005, "今日您查询次数过多");
		}
		
		//3 保存到本地数据idcardlog中
		$logData = [
			'aid' => $this->appData['id'],
			'name' => $name,
			'idcard' => $idcard,
			'partner_trade_no' => $partner_trade_no,
			'callbackurl' => $callbackurl,
		];
		$result = $this->oLog -> savaData($logData);
		if( !$result ){
			Logger::dayLog(
				'idcard',
				'log保存失败', $this->oLog->errors, $this->oLog->errinfo ,
				'data', $logData
			);
		}
		
		//4 调用接口进行处理 目前有两种接口,以后可能做成路由的形式
		// 调用量化派接口
		return $this->pai($name, $idcard, $partner_trade_no);
		
		// 调用马上金融接口
		//return $this->msjr($name, $idcard, $partner_trade_no);
	}


	/**
	 * 量化派
	 */
	public function pai($name, $idcard, $partner_trade_no){
		//1 从接口中获取数据
		$res = $this->oIdCardPai  -> get($name, $idcard, $partner_trade_no);
		if( empty($res) ){
			Logger::dayLog(
				'idcard',
				'pai获取失败',  $this->oIdCardPai->errinfo ,
				'partner_trade_no', $partner_trade_no,
				'name', $name,
				'idcard', $idcard
			);
			$this->oLog -> status = IdcardLog::STATUS_FAIL;
			$this->oLog -> save();
			
			return $this->resp(90006, $this->oIdCardPai ->errinfo);
		}
		$this->oLog -> status = IdcardLog::STATUS_OK;
		$this->oLog -> save();
		
		//2 保存图片到文件中
		$base64 = isset($res['idCardPhoto']) ? $res['idCardPhoto'] : '';
		$image = $this->savePaiImage($idcard, $base64);
		
		//3  保存到数据库中
		$oCard = new Idcard;
		$result = $oCard -> saveData( $name, $idcard, '', $image );
		
		//4  设置最终查询结果
		return $this->resp(0, [
			'status' => IdcardLog::STATUS_OK,// 处理中
			'type'  => 'pai',
			'name' => $name,
			'idcard'=> $idcard,
			'partner_trade_no' => $partner_trade_no,
		]);
	}
	/**
	 * 马上金融身份验证
	 */
	public function msjr($name, $idcard, $partner_trade_no){
		//5 从接口中获取数据
		$res = $this->oIdCardApi -> get($name, $idcard, $partner_trade_no);
		if( empty($res) ){
			Logger::dayLog(
				'idcard',
				'msjr获取失败', $this->oIdCardApi->errinfo ,
				'partner_trade_no', $partner_trade_no,
				'name', $name,
				'idcard', $idcard
			);
			$this->oLog -> status = IdcardLog::STATUS_FAIL;
			$this->oLog -> save();
			
			return $this->resp(90016, $this->oIdCardApi ->errinfo);
		}

		//7  设置最终查询结果
		$this->oLog -> trade_no = $res['trade_no'];
		$this->oLog -> status = IdcardLog::STATUS_ING;
		$this->oLog -> save();
		return $this->resp(0, [
			'status' => IdcardLog::STATUS_ING,// 处理中
			'type'  => 'msjr',
			'name' => $name,
			'idcard'=> $idcard,
			'partner_trade_no' => $partner_trade_no,
		]);
	}
	/**
	 * 保存量化派的图片
	 * @param str $idcard 身份证
	 * @param str $base64 图片base64形式
	 * @return str path
	 */
	private function savePaiImage($idcard, $base64){
		$oIdcard = new Idcard;
		$path = $oIdcard -> saveImage($idcard, $base64);
		return $path;
	}
}
