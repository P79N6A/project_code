<?php
/**
 * 人脸识别验证
 * 内部错误码范围 10000
 * @author gaolian
 */
namespace app\modules\api\controllers;

use Yii;
use app\modules\api\common\ApiController;
use app\modules\api\common\linkface\LinkfaceApi;
use app\models\Face;
use app\common\Logger;

class FacevalidController extends ApiController
{
	/**
	 * 服务id号
	 */
	protected $server_id = 11;
	
	/**
	 * 商汤接口文档
	 */
	private $linkface;
	
	public function init(){
		parent::init();
		$env = YII_ENV_DEV ? 'dev' : 'prod';
		$this->linkface = new LinkfaceApi($env);
	}
	
	public function actionIndex()
	{
		//1 字段检测
		$data = $this->reqData;
		Logger::dayLog("facevalid/pic", "post", json_encode($data));
		//Logger::errorLog($data['identity']."--".print_r($data, true), 'facevalid');
		if( !isset($data['identity']) ){
			return $this->resp(10001, "身份证不能为空");
		}
		if( !isset($data['pic_identity']) ){
			return $this->resp(10002, "身份证照片不能为空");
		}
		if( !isset($data['identity_url']) ){
			return $this->resp(10003, "自拍照不能为空");
		}

		$identity = $data['identity'];
		$type = 1;
		//身份证照片路径
		$img1_url = $data['pic_identity'];
		//自拍照路径
		$img2_url = $data['identity_url'];
		$ret = $this->linkface->linkface($identity, $img1_url, $img2_url);
		$result = json_decode($ret);
		$condition = array(
				'identity' => $identity,
				'img_url1' => $img1_url,
				'img_url2' => $img2_url,
				'score' => isset($result->confidence) ? $result->confidence : '',
				'result' => $ret
		);
		$face = new Face;
		$result_face = $face->addFace($condition);
		
		//调用验证自拍照真假的接口
		$ret_hack = $this->linkface->selfie_hack_detect($img2_url);
		$result_hack = json_decode($ret);
		
		$score_hack = isset($result_hack->score) ? number_format(($result_hack->score)*100, 2, '.', ' ') : '';
		if(!empty($score_hack) && ($score_hack >= 98)){
			$score = 0;
		}else{
			$score = isset($result->confidence) ? number_format(($result->confidence)*100, 2, '.', ' ') : '';
		}
		
		$status = isset($result->status) ? $result->status : '';
		return $this->resp(0, [
				'score'	 => $score,
				'status' => $status
				]);
	}
	
	
	/**
	 * 身份证号和姓名验证是否匹配
	 */
	public function actionIdentityverification(){
		//1 字段检测
		$data = $this->reqData;
		Logger::errorLog($data['identity']."--".print_r($data, true), 'identityverification');
		if( !isset($data['identity']) ){
			return $this->resp(10001, "身份证号不能为空");
		}
		if( !isset($data['name']) ){
			return $this->resp(10002, "姓名不能为空");
		}
		
		//身份证号
		$identity = strtoupper($data['identity']);
		//姓名
		$name = $data['name'];
		
		$ret = $this->linkface->idnumber_verification($identity, $name);
		
		return $this->resp(0, [
				'result'	 => $ret,
				]);
	}
}