<?php
namespace app\modules\api\common\jiufu;
use app\common\Logger;
/**
 * 仅用于图片上传数据整合
 */
class OssFileInfo {
	public $errinfo;
	public function returnError($result, $errinfo){
		$this->errinfo = $errinfo;
		return $result;
	}
	/**
	 * 通过图片链接发送请求
	 */
	public function get($transSerialNo, $img_name, $img_url) {
		//1 请求差距
		$transhead = $this->getHead($transSerialNo);

		//2 请求体
		//2.1 下载图片
		$image_bytes = $this->downImage($img_url);
		if(!$image_bytes){
			Logger::dayLog('9f', 'oss get image not found', $transSerialNo,  $img_url );
			return null;
		}
		//$img_name = basename($img_url);
		$file_size = strlen($image_bytes);

		//2.2 请求体
		$ossFileInfo = $this-> getBody($img_name, $image_bytes, $file_size);

		//3 返回数据
		$data = [
			'transHead' => $transhead,
			'transBody' => $ossFileInfo,
		];
		return $data;
	}
	/**
	 * 下载图片并重试
	 * @param  [type]  $url   图片链接
	 * @param  integer $retry 重试下载次数
	 * @return 	图片二进制流
	 */
	private function downImage($url, $retry = 1){
		$res = $this->_downImage($url);
		if( $res['status'] == 200){
			return $res['data'];
		}
		if( $res['status'] == 0 && $retry > 0 ){
			$retry--;
			return $this->downImage($url, $retry);
		}else{
			return '';
		}
	}
	/**
	 * 下载图片链接
	 * @param  [type] $url [description]
	 * @return [type]      [description]
	 */
	private function _downImage($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_URL, $url);
		ob_start();
		curl_exec($ch);
		$return_content = ob_get_contents();
		ob_end_clean();

		$return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		return [
			'status' => $return_code,
			'data' => $return_content,
		];
	}

	/**
	 * 获取头信息
	 * @param  [type] $transSerialNo [description]
	 * @return [type]                [description]
	 */
	private function getHead($transSerialNo) {
		$oHead = new TransHead();
		$transhead = $oHead->getOss($transSerialNo);
		return $transhead;
	}
	/**
	 * 组合内容体
	 * @param str $fileName 文件名
	 * @param  str $file_url 文件链接
	 * @return []
	 */
	private function getBody($img_name, $image_bytes, $file_size) {
		return [
			'bytes' => $image_bytes,
			'ossMapping' => $this->getOssMapping($img_name, $file_size),
		];
	}
	/**
	 * 组合ossmap数据格式
	 * @param  [type] $fileName [description]
	 * @param  [type] $size     [description]
	 * @return [type]           [description]
	 */
	private function getOssMapping($fileName, $size) {
		return [
			//'fileId' => '', // String  文件编号
			'fileName' => $fileName, // String  原文件名（必填）
			'sysCode' => '11', // String  系统编号（必填11）
			//'orgCode' => '', // String  运营机构编码
			//'userId' => '', // String  用户编号
			//'userName' => '', // String  用户名称
			//'businessType' => '', // String  业务类型
			'docId' => '4', // String  单证编号（必填4）
			//'docType' => '', // String  单证类型
			'instCode' => 'JFB', // String  机构编码（必填JFB）
			//'keyWords' => '', // String  关键字
			'fileType' => '11001005', // String  文件类型（必填）
			//'ossPath' => '', // String  oss云端存储路径
			'size' => $size, //  int 文件大小
			//'beginTime' => '', // String  文件开始上传时间
			//'endTime' => '', // String  文件上传完成时间
			'timeCost' => 5, //  long  文件上传完成使用时间
			//'flag' => '', // String  删除标记，1表示已删除，0表示未删除
			//'remark' => '', // String  备注
			//'ossKey' => '', // String  云端key
		];
	}
}