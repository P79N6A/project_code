<?php
namespace app\modules\api\common\sjt;
use app\common\Curl;
use app\common\Logger;
class Util{
    public function sendPost($url, $data,$resultToJson = true,$isWriteLog = true){		
		$jsonString = http_build_query($data);
		$curl = new Curl();
		$curl -> addHeader([
			'Content-Type' => 'application/x-www-form-urlencoded',  	
			'Content-Length' => strlen($jsonString)      
		]);
		$curl -> setOption(CURLOPT_CONNECTTIMEOUT,120);
		$curl -> setOption(CURLOPT_TIMEOUT,120);
		$curl -> setOption(CURLOPT_SSL_VERIFYPEER,false);
		$curl -> setOption(CURLOPT_SSL_VERIFYHOST,false);
		$content = $curl -> post($url, $jsonString);
		$status  = $curl -> getStatus();
		if($isWriteLog){
			//Logger::dayLog('sjt/api','sendPost',$status,$url,$jsonString,$content);
		}else{
			//Logger::dayLog('sjt/api','sendPost',$status,$url,$jsonString);
		}		
		if( $status == 200 ){
			return $resultToJson ? json_decode ( $content, true ) : $content;
		}else{			
			return null;
		}
	}
	public  function doCurlPost($url, $params, $resultToJson = false) {
		$_curl = curl_init ();
		if (stripos ( $url, "https://" ) !== FALSE) {
			curl_setopt ( $_curl, CURLOPT_SSL_VERIFYPEER, FALSE );
			curl_setopt ( $_curl, CURLOPT_SSL_VERIFYHOST, false );
		}
		
		if (is_string ( $params )) {
			$_strPOST = $params;
		} else {
			$_strPOST = http_build_query ( $params );
		}
		
		curl_setopt ( $_curl, CURLOPT_URL, $url );
		curl_setopt ( $_curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $_curl, CURLOPT_POST, true );
		curl_setopt ( $_curl, CURLOPT_POSTFIELDS, $_strPOST );
		curl_setopt ( $_curl, CURLOPT_TIMEOUT, 5 );
		
		$_content = curl_exec ( $_curl );
		$_status = curl_getinfo ( $_curl );
		curl_close ( $_curl );
		
		if (intval ( $_status ["http_code"] ) == 200) {
			return $resultToJson ? json_decode ( $_content, true ) : $_content;
		}
		
		return false;
    }
}
?>