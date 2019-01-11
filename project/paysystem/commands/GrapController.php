<?php
/**
 * 
 *
 */
namespace app\commands;

use app\models\GrapBank;
use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;

class GrapController extends BaseController {

	const NEXTPAGE = 'http://www.lianhanghao.com/index.php/Index/index/bank/';
	const STARTURL = 'http://www.lianhanghao.com/index.php/Index/index/p/index.php?bank=';
	private $nextPageifo = [];


	public function runReq(){
		// $filepath = \Yii::$app->basePath. "/log/grap/201804/start.json"; //第一页
		$filepath = \Yii::$app->basePath. "/log/grap/201804/15.txt"; //下一页
		$con = file_get_contents($filepath);
		$urlList = \json_decode($con,true);
		foreach($urlList as $list){
			// $list['url'] = 'http://www.lianhanghao.com/index.php?bank=1&province=3&city=38';
			$flag = $this->insertData($list);
			if(!$flag){
				continue;
			}

			
			echo $flag;
			// echo $list['bank'];
			// die;
		}

		// $res  = json_encode($this->nextPageifo);
		// Logger::dayLog('grap', 'res', $res);
		
	}


    public function getAllBySql($sql) {
		$connection = Yii::$app->db;
		$command = $connection->createCommand($sql);
		return $command->queryAll();
	}
	
	public function insertData($list){
		$success = 0;
		
		$post_data = $this->getGrapcon($list['url']);
		preg_match_all("/<tdalign='center'>(.*)<\/td><tdstyle='padding-left:5px;'align='left'>(.*)<\/td><tdstyle='padding-left:5px;'align='left'>(.*)<\/td><tdstyle='padding-left:5px;border-right:0px'align='left'>(.*)<\/td><\/tr>/U",$post_data,$info);
		// preg_match_all("/index.php\/Index\/index\/p\/(.*)\/bank\//U",$post_data,$page);
		// $end_page = ArrayHelper::getValue($page,'1',0);
		// $end_page = array_unique($end_page);
		// $num = count($end_page);
		// $bigpage = $end_page[$num-1];//分页最大页
		// $this->getPageUrl($list,$bigpage);//记录分页的URL



		$bankcode = ArrayHelper::getValue($info,'1');
		$bankname = ArrayHelper::getValue($info,'2');
		$banktel = ArrayHelper::getValue($info,'3');
		$bankads = ArrayHelper::getValue($info,'4');
		$resnum = count($bankcode);
		for($n=0;$n<$resnum;$n++){
			$oBank = new GrapBank();
			$data = [
				'bankid'=> $list['bank'],
				'pid'=> $list['pid'],
				'cid'=> $list['cid'],
				'bank_code' => $bankcode[$n],
				'bank_name'=> $bankname[$n],
				'bank_tel'=> $banktel[$n],
				'bank_address'=> $bankads[$n]
			];
			// print_r($data);
			$flag = $oBank->getIsnum($data);
			if($flag){
				continue;
			}
			$res = $oBank->createData($data);
			if($res){
				$success++;
			}
			
		}

		return $success;
	}

	public function getGrapcon($url){
		$post_data = file_get_contents($url);
		$post_data = str_replace("\r\n", '', $post_data); //清除换行符 
		$post_data = str_replace("\t", '', $post_data); //清除制表符
		$post_data = str_replace(" ", '', $post_data); //清除制表符
		$post_data = str_replace("\"", '\'', $post_data); //清除制表符
		preg_match("/<tableclass(.*)<\/tfoot>/U",$post_data,$res);
		$post_data = ArrayHelper::getValue($res,'1','');
		// echo $post_data;
		if(empty($post_data)){
			return false;
		}

		return $post_data;
		
	}

	public function getPageUrl($list,$bigpage){//分页单独存起来
		for($p=2; $p<=$bigpage;$p++){
			// $url = self::NEXTPAGE.$p.'/bank/'.$list['bank'].'/province/'.$list['pid'].'/city/'.$list['cid'].'.html';
			$url = self::NEXTPAGE.$list['bank'].'/province/'.$list['pid'].'/city/'.$list['cid'].'/p/'.$p.'.html';
			$data['bank'] = $list['bank'];
			$data['pid'] = $list['pid'];
			$data['cid'] = $list['cid'];
			$data['url'] = $url;
			$this->nextPageifo[] = $data;
		}
		
	}
}
