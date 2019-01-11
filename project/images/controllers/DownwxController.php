<?php
/**
 * 默认控制器
 * 登录与退出
 */
namespace app\controllers;
use Yii;
use app\common\UploadImage;
use app\common\Http;
use app\common\Logger;
use app\models\ImgFileSave;

/**
 * 下载wx的图片
 * 要求xianhuahua.com同域名下
 */
class DownwxController extends BaseController
{
	public $enableCsrfValidation = false;
	/**
	 * 图片上传
	 */
    public function actionIndex()
    {	
    	//1 参数校验
    	$access_token = $this->getParam('access_token');
    	$media_id = $this->getParam('media_id');
    	$encrypt = $this->getParam('encrypt');
    	$url = $this->getParam('url');
    	$callback = $this->getParam('callback');
		
		if( empty($access_token) ){
			return $this->jsonp(1, "access_token不能为空", $callback);
		}
		if( empty($media_id) ){
			return $this->jsonp(1, "media_id不能为空", $callback);
		}
		if(!$encrypt){
			return $this->jsonp(1, "encrypt不能为空", $callback);
		}
		
		//2 验证密钥
		$oModel = new ImgFileSave();
		$result = $oModel -> decryptKey($encrypt);
		if(!$result){
			return $this->jsonp(1, $oModel->errinfo, $callback);
		}
		$imgPath = $oModel -> getImgPath();
		if(!$imgPath){
			return $this->jsonp(1, "项目目录不能为空,请检查密钥", $callback);
		}
		
		//3 获取路径
		$oImg = new UploadImage;
		$path = $oImg->getPath( $url, $imgPath);
		if(!$path){
			return $this->jsonp(1, $oImg->errinfo, $callback);
		}
		
		//4 下载weixin
		$content = $this->downwx($access_token, $media_id);
		if(!$content){
			return $this->jsonp(1, "获取图片失败", $callback);
		}
		$res = $oImg -> createByHex($content, $path);
		if(!$res){
			return $this->jsonp(1, "图片保存失败", $callback);
		}
		return $this->jsonp(0, $path, $callback);
	}
	/**
	 * 下载微信
	 */
	private function downwx($access_token, $media_id){
		//@todo
		//return file_get_contents(dirname(Yii::$app->basePath). "/images/1.jpg");
		
        $url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=" . $access_token . "&media_id=" . $media_id;
		$content = Http::getCurl($url);
		if( strpos($content, "errcode") !== FALSE ){
			Logger::dayLog("downwx",$url,$content);
			return $this->returnError(null,"获取图片失败");
		}
		return $content;
	}
	/**
	 * json 输出
	 * @param  int $res_code 错误码 0:无错误
	 * @param  any $res_data 响应内容
	 * @return string json数据
	 */
	private function jsonp($res_code, $res_data,$callback){
		$str = json_encode( [
			'res_code' => $res_code,
			'res_data' => $res_data,
		]);
		
		return "{$callback}({$str})";
	}
}
