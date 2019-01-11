<?php 
/**
 * 生成中信的xml
 * 是gbk的
 * @author lijin
 */
namespace app\modules\api\common\remit;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Xml;

class RemitXml{
	/**
	 * xml处理类
	 */
	private $oXml;
	
	public function __construct(){
    	$this->oXml = new Xml;
	}
	/**
	 * xml->[] utf8
	 * 将xml解析成数组
	 */
	public function xml2array( $resxml ){
		return $this->oXml->toArray($resxml);
	}
	/**
	 * [] -> xml gbk
	 * 转换成中信的xml格式
	 */
	public function array2xml( $data ){
		//1 转换成gbk的格式
		if( !is_array($data) ){
			return null;
		}
		$data2 = $this->toGbk($data);
		
		//2 根据数据生成的头部指定是gbk的
		return $this->oXml->toXml($data2,'GBK');
	}
	/**
	 * []->xml gbk
	 * 根据模板创建xml
	 * @param string $tplname 模板名称
	 * @param [] $data 数组
	 */
	public function tpl2xml($tplname, $data){
		//1 转换成gbk格式
		if(!is_array($data)){
			return null;
		}
		$data = $this->toGbk($data);
		
		//  获取出款的xml模板
		$template = $this->xmlFromFile($tplname);
		if(!$template){
			return null;
		}
		
		// 替换模板变量
		foreach($data as $k=>$v){
			$ktpl = '{'.$k.'}';
			$template = str_replace($ktpl, $v, $template);
		}
		return $template;
	}
	/**
	 * 获取xml模板 gbk
	 * @param $name 模板名称
	 * @return xml
	 */
	private function xmlFromFile( $name ){
		$file = __DIR__ . "/xml/{$name}.xml";
		if(!$file || !file_exists($file)){
			return false;
		}
		$xml = file_get_contents( $file );
		return $xml;
	}
	/**
	 * utf8->gbk
	 */
	private function toGbk($data){
		if( is_array($data) ){
			foreach($data as $k=>$v){
				$data[$k]=$this->toGbk($v);
			}
			return $data;
		}else{
			return iconv("utf-8",'gbk', $data);
		}
	}
	/**
	 * gbk->utf8
	 */
	private function toUtf8($data){
		if( is_array($data) ){
			foreach($data as $k=>$v){
				$data[$k]=$this->toUtf8($v);
			}
			return $data;
		}else{
			return iconv('gbk', "utf-8",$data);
		}
	}
	
	
}