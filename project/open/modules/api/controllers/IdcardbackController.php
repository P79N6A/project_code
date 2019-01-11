<?php
/**
 * 马上金融征信身份验证接口
 */
namespace app\modules\api\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use app\modules\api\common\ApiController;
use app\common\Func;

use app\models\App;
use app\models\Idcard;
use app\models\IdcardLog;

use app\common\ApiServerCrypt;
use app\common\Http;
use app\common\Logger;

use app\modules\api\common\idcard\IdCardApi;


class IdcardbackController extends ApiController
{
	/**
	 * 易宝投资通
	 */
	private $oIdCardApi;
	
	public function init(){
		//parent::init(); 千万不要执行父类的验证方法
		$env = YII_ENV_DEV ? 'dev' : 'prod';
		$env = 'dev'; // @todo
		// 征信
		$this->oIdCardApi = new IdCardApi($env); 
	}
	
    public function actionIndex()
    {
    }
	/**
	 * 投资通异步回调接口:只有异步，前台是自己的，不在这儿
	 */
	public function actionCallurl(){
		//1 获取二进制流
		$post = file_get_contents('php://input');
		
		// @todo 
		//$post = $this->getTestData();
		
		if( empty($post) ){
			return FALSE;
		}
		
		//2 解密
		try{
			$decrypt = $this->oIdCardApi ->decrypt($post);
		}catch(\Exception $e){
			Logger::dayLog(
				'idcardback',
				'info', '解密失败' ,
				'base64', base64_encode($post)
			);
			return "解密失败";
		}
		
		//3 验证签名是否正确
		$arr = explode("&sign=", $decrypt);
		$ok =  $this->oIdCardApi -> verify($arr[0], $arr[1] );
		if(!$ok){
			Logger::dayLog(
				'idcardback',
				'info', '验名失败' ,
				'base64', base64_encode($post)
			);
			return "验名失败";
		}
		
		//4 解析参数到$res中
		$res = [];
		parse_str($decrypt, $res);
		
		//5 从数据库中获取纪录
		$oLog = IdcardLog::getByNo($res['partner_trade_no']);
		if( empty($oLog) ){
			Logger::dayLog(
				'idcardback',
				'info', '没找到查询请求号' ,
				'base64', base64_encode($post),
				'partner_trade_no',$res['partner_trade_no']
			);
			return "本地无此号";
		}
		
		//6 检测状态是否曾经处理过了
		if( $oLog -> status ==  IdcardLog::STATUS_OK){
			return 'success';
		}elseif($oLog -> status ==  IdcardLog::STATUS_FAIL){
			return 'success';
		}
		
		//7 获取结果状态
		$arr = json_decode($res['result'], true);
		$status = $this->parseResultStatus($arr);
		$result = $oLog -> status = $status;
		
		//8 写入身份证详细纪录表中
		$oLog -> url =  $oLog -> saveJson( $oLog-> idcard, $res['result']);
		$result = $oLog -> save();
		
		//9 写入到成功纪录表中
		if( $status ==  IdcardLog::STATUS_OK ){
			$oCard = new Idcard;
			$result = $oCard -> saveData( $oLog -> name, $oLog -> idcard, $oLog -> url );
		}
		
		//10 返回结果
		$res_data = [
			'status' => $oLog -> status, 
			'idcard'=> $oCard -> idcard, 
			'name'=> $oCard->name,
			'partner_trade_no'=> $res['partner_trade_no'],
		];
		$responseData = $this->encryptData( $oLog->aid, $res_data);
		$result = $this->doPost($oLog -> callbackurl, $responseData);
		if($result){
			return 'success';
		}else{
			return 'error';
		}
	}

	/**
	 * post后台异步通知
	 */
	private function doPost($callbackurl, $responseData){
		$postData = [ 'res_data' => $responseData, 'res_code'=> 0];
		$hostMap = null;//['www.test.com'=>'127.0.0.1'];// @todo
		$result  = Http::curlByHost($callbackurl, $postData, $hostMap);
		
		// 若客户端也返回成功，则返回易宝成功状态.
		if($result == 'SUCCESS'){
			return true;
		}
		return false;
	}
	
	/**
	 * 获取加密密钥
	 */
	private function encryptData( $aid, $res_data ){
		return App::model() -> encryptData($aid, $res_data);
	}

	/**
	 * 解析验证结果
	 */
	private function parseResultStatus(&$result){
		$ident_id      = ArrayHelper::getValue($result, 'MC_IDENT_ID.IDENT_ID');
		$ident_name= ArrayHelper::getValue($result, 'MC_IDENT_ID.IDENT_NAME');
		if( $ident_id=='存在' && $ident_name == '一致'  ){
			// 一致时
			$status = IdcardLog::STATUS_OK; // 验证成功
		}else{
			// 不一致时
			$status = IdcardLog::STATUS_FAIL;// 验证失败
		}
		return $status;
	}
	private function getTestData(){
		$post = "KvtikjY7HR+nvxODQlgne2Z/+IWczFuEwaOGjW77QiyoWEWuJnSumBbWnSMXjPVKTy00XHSWJmoE4wO5dhDyDxhJgzdJIvHgOQ8wvzFqS7Mik1aAvlvnKo7FfZg9XroMUjnrmlKcP+tbZFUXnB0+KGWJYUkzfK9RXZd87UKyiONWaZS7ElKrJgZ+F6TPv1EEdScQShCtfYJ108ob37A3GMEon78WJzfyLm6mDw/UMxO/PvvydncxyziX75oDOzAN41FaORphiL2hOY5zf2aVgUmNhjzvSFkSYsLuMvskKcowZNpmgWIr6XLXq0VT2WgsxKfVKHcy3kdPUOmtEZMfjFPU7BpgIn0GTE9q58xzbXO5kt3T2dAsUjl6tEkFjrEGDnxiaLOh4RZZeGsP2WDuKIqYZkqyy57gD6ja79mIvOM1gEWmX7B24ZBD7hYKvfpJu16JGrqsMGRLwnc2RBYsJb/nUAvXC08FPHuwkOi1QmKNtR0+pn2jtoSjuIWIMAVwYkxRv+yogBRXkC7CgmUe6asq50ZS5tJXGNajhPRm8V7krmPaaYmpf2ZkjCOd0oIW9Z07DKpMJ0/Yx27QmctBE78MIFRSFMmSh93SP4cgtjPx7huvUWjwPypYk2R2JH8zC9+XRDyEAVburqVo85mTwZXy1FbfaxvuQWYhDxAK3vhIKWm/8zzushNKkaGSJ+J974ZYObxS/P6Jl07tNbZ9W2r6sJphIf/BfpOhkUj1eQKziQ1KVuAFnNCuiHtorvledVk5oXtAFy8g7+rEHM8wTuPMN84mwXmibHTQPBoqNXX7NBXCjcQIwUqefHAtkJEeTQPXxYcpm+r7iYgm1DE/0VIzZMQi0havaPEWGg+pdfyoeb0AynbQ4YK4qc84e3w71yQVp2d8H54Cx4tw3ANjdue11LbFVmnkslexwakrFEEkq43IgXYoPsyDf9FlXaQq3lxl8rkqUSXCvczTVa132YfaTYTMhMEa4SADkzkvKVIvZmESp6oDCJN3bWTPWDLyerWbos8XYSy1WyAt+bLAHGHhEZwU6ZcM1640ozc9KqeFFPlMCGm97VHvsxOzP8+kss9gCE3ZNTB0PGBgMrq9u8gouVjG+n9m1ndWpjJ0ZavuO5T3Mt6dKeh6mSzdn4oVPuIm0hBJEjFaO04azV0vbYigTdKNn1AQh5VhTeu1VIg/jPVq/PiMa2qfE5qLg6BTRapZXZYfV7VCsHDIYLQzWvu2H9ZjQnZUvl2tFLwvKBVgjZ0mON8Ss5qiCv1qbql229a/MOyDXs2id3aQUFayFUu0zjtr4IklLkZReUUrcf5UJJQv2vF5wJcDjPnb5LpzM/ColV1pv7xSrQXB4olVmA==";
		$post = base64_decode($post);
		return $post;
	}
}
