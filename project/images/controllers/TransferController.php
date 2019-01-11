<?php
/**
 * 
 */
namespace app\controllers;
use Yii;
use app\common\UploadImage;
use app\models\ImgFileSave;
class TransferController extends BaseController
{	
	private $oModel;
	private $uploadImageObj;
	public $enableCsrfValidation = false;
	/**
	 * 图片上传
	 */
    public function actionIndex()
    {
    	// 1 只能是post
		if (!$this->isPost()) return $this->repJson(10000, "不支持此操作");
		// post数据不能为空
		$postData = $this->post();
		\app\common\Logger::dayLog("transfer",$postData);
		$this->oModel = new ImgFileSave();
		//解密数据 获取提交的数据url地址
		if(!$postData['encrypt']) return $this->repJson(10002,"提交数据不能为空");
		$imgUrlinfos = $this->getImageUrls($postData['encrypt']);
		if(empty($imgUrlinfos)){
			return $this->repJson(10003, $this->$oModel->errinfo);
		}
		// $imgUrlinfos['imgUrls'] = [
		// 		'1'=>'http://cimage1.tianjimedia.com/uploadImages/thirdImages/2017/142/0YI6HOA0KMHZ.jpg',
		// 		'2'=>'http://t2.27270.com/uploads/tu/201610/198/ui0rqinzt2t.jpg'
		// ];
		$projectPath = isset($imgUrlinfos['project']) ? $imgUrlinfos['project'] : 'yiyiyuan';
		$projectPath = "/".$projectPath."/transfer";
		$this->uploadImageObj = new UploadImage();
		$operateImgs = [];
		foreach ($imgUrlinfos['imgUrls'] as $key => $url) {
			$operateImgs[$key]['imgUrl'] = $url;
			$operateImgs[$key]['imgPath'] = $this->uploadImageObj->getPath('',$projectPath);
		}
		$success = [];
		foreach ($operateImgs as $k => $val) {
			$urlPath = $this->downAndSaveImg($val);
			if(!$urlPath){
				return $this->repJson(10004, $this->uploadImageObj->errinfo);
			}
			$success[$k] = $urlPath;
		}
		return $this->repJson(0, $success); 
    }
	
	/**
	 * 获取提交的图片列表
	 * @param $postData
	 * @return  $resultUrl 
	 */
	private function getImageUrls($encrypt){
		//2 验证密钥
		$resultUrl = [];
		$resultUrl = $this->oModel->decryptUrl($encrypt);
		return $resultUrl;
	}
	/**
	 * 获取图片并保存 返回地址
	 * @param [] $val
	 * @return void
	 */
	private function downAndSaveImg($val){
		$urlPath = $this->uploadImageObj->downAndSaveImg($val);
		return $urlPath;
	}

	/**
	 * json 输出
	 * @param  int $res_code 错误码 0:无错误
	 * @param  any $res_data 响应内容
	 * @return string json数据
	 */
	private function repJson($res_code, $res_data){
		$res = json_encode( [
			'res_code' => $res_code,
			'res_data' => $res_data,
		]);
		return $res;
	}

}
