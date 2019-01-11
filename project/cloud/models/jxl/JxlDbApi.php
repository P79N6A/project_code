<?php

namespace app\models\jxl;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\models\anti\PhoneTagList;
use app\models\mycat\MyPhoneTagList;
use app\models\tidb\TiPhoneTagList;

/**
 * 操作xhh_open DB 统一对外开放接口
 */
class JxlDbApi 
{
	private $jxl;
	private $db;
	private $tag_list;
	private $db_tidb;
	private static $source;

	public function __construct()
	{
		$this->db_tidb = Yii::$app->db_tidb;
		$this->db = Yii::$app->db_anti;
		$this->jxl = new JxlStat();
		$this->tag_list = new PhoneTagList();
	}

	public function getJxlStatData($where,$select = '*')
	{
		if (empty($where)) {
			return null;
		}
		$jxl_infos = $this->jxl->getJxlInfo($where,$select);
		return $jxl_infos;
	}

	public function getMaxId()
	{
		$max_id = $this->jxl->getJxlMaxId();
		return $max_id;
	}

	// 批量插入标签
	public function SaveTagList($tag_list,$jxl_info)
	{
		self::$source = ArrayHelper::getValue($jxl_info, 'source', 0);
		if (empty($tag_list)) {
			return 0;
		}
		// check phone_tag
		$in_or_up_list = $this->checkPhoneTag($tag_list,$jxl_info);
		// insert or update
		$in_res = 0;
		$up_res = 0;
		// $save_res = 0;
		
		if (empty($in_or_up_list)) {
			$in_res = $this->batchInsertTag($tag_list,$jxl_info);
		} else {
			//func one
			if (!empty($in_or_up_list['in_list'])) {
				$in_res = $this->batchInsertTag($in_or_up_list['in_list'],$jxl_info);
			}
			if (!empty($in_or_up_list['up_list'])) {
				// $up_res = $this->batchUpTag($in_or_up_list['up_list'],$jxl_info);
				# update tidb
        		$up_nums = $this->updateTagBatch($in_or_up_list['up_list'],$jxl_info);
			}
			//func two
			// $save_res = $this->batchSaveTag($in_or_up_list,$jxl_info);
		}
		return $up_res+$in_res;
	}

	public function checkPhoneTag($tag_list,$jxl_info)
	{
		$in_and_up_list = ['in_list'=>$tag_list,'up_list'=>[]];
		$phone_list = array_keys($tag_list);
		if (empty($phone_list)) {
			Logger::dayLog('jxldbapi/checkPhoneTag', 'phone_list is empty',$tag_list,$jxl_info);
			return $in_and_up_list;
		}
		try {
			$exist_info = $this->getExistTag($phone_list);
		} catch (Exception $e) {
			Logger::dayLog('jxldbapi/getExistTag', 'getExistTag is fail',$e->getMessage(),$phone_list,$jxl_info);
			return $in_and_up_list;
		}
		if (empty($exist_info)) {
			// Logger::dayLog('jxldbapi/checkPhoneTag', 'exist_info is empty',$tag_list,$jxl_info);
			return $in_and_up_list;
		}
		// set in_list and up_list
		$in_and_up_list = $this->setInAndUpList($exist_info,$tag_list);
		if (empty($in_and_up_list)) {
			Logger::dayLog('jxldbapi/checkPhoneTag', 'in_and_up_list is empty',$tag_list,$jxl_info);
			return $in_and_up_list;
		}
		return $in_and_up_list;
	}
	// 只新增
	public function insertTag($tag_list,$jxl_info)
	{
		if (empty($tag_list) || empty($jxl_info)) {
			return false;
		}
		$insertData = [
			'jxl_id' => ArrayHelper::getValue($jxl_info,'id',0),
			'status' => 0,
			'tag_info' => json_encode($tag_list,JSON_UNESCAPED_UNICODE),
			'source' => ArrayHelper::getValue($jxl_info,'source',0),
		];
		$phone_tag_db = new PhoneTagList();
		$res = $phone_tag_db->saveData($insertData);
		return $res;
	}

	// 拼装新增及更新数据
	private function setInAndUpList($exist_info,$tag_list)
	{
		$exist_phone_list = ArrayHelper::getColumn($exist_info,'phone');  //键值
		$exist_detail_list = array_column($exist_info,null,'phone');
		if (empty($exist_detail_list)) {
			return [];
		}
		$in_list = [];
		$up_list = [];
		// try {
			foreach ($tag_list as $key => $value) {
				$tag = [];
				if (in_array($key,$exist_phone_list) ) {
					$new_tag = $tag_list[$key]['tag'];
					$old_tag = $exist_detail_list[$key]['tag_type'];
					$if_exist = in_array($new_tag,explode(',',$old_tag));
					if (!$if_exist) {
						$up_list[$key]['tag'] = $new_tag.','.$old_tag;
					}
				} else {
					$in_list[$key] = $tag_list[$key];
				}
			}
			$all_list = ['in_list' => $in_list,'up_list' => $up_list];
			// var_dump($all_list);die;
			return $all_list;
		// } catch (\Exception $e) {
		// 	Logger::dayLog('setInAndUpList', 'analysis report file',$tag_list,$e->getMessage());
		// 	return [];
		// }	
	}

	public function getExistTag($phone_list)
	{
		if(empty($phone_list)){
			return null;
		}
		$phone_str = implode("','", $phone_list); 
		// $sql = "SELECT `phone`,`tag_type` FROM `tag_info_list` WHERE `phone` IN('".$phone_str."')";
		$sql = "SELECT `phone`,`tag_type` FROM `phone_tag_list` WHERE `phone` IN('".$phone_str."')";
		Logger::dayLog('sql',$sql);
		// $command = $this->db->createCommand($sql);
		$command = $this->db_tidb->createCommand($sql);
        $phone_tag = $command->queryAll();
        $this->db_tidb->close();
		return $phone_tag;
	}
	
	// 只新增
	public function batchInsertTag($tag_list,$jxl_info)
	{
		// try {
		// if (empty($tag_list)) {
		// 	Logger::dayLog('jxldbapi/batchInsertTag', 'insert_tag_list is empty',$tag_list,$jxl_info);
		// 	return 0;
		// }
		// $time = date('Y-m-d H:i:s');
		// $insertStr = '';
		// $status = 0;
		// $type = 1;
		// //set sql
		// foreach ($tag_list as $key => $val) {
  //           $phone = addslashes(trim($key));
  //           $tag_type = addslashes(trim($val['tag']));
  //           $insertStr = $insertStr. ",('" . $phone . "','" . $tag_type . "','" . self::$source . "','" . $time . "','" . $time ."','" . $status . "','" . $type ."')";
  //       }
  //       // insert tag_list
  //       $insertTagSql = 'insert into tag_info_list (`phone`,`tag_type`,`source`,`modify_time`,`create_time`,`status`,`type`) values'. trim($insertStr,',');
  //       $commandInsert = $this->db->createCommand($insertTagSql);
  //       $ok = $commandInsert->execute();
  //       $this->db->close();
  //       if ($ok == 0) {
  //       	Logger::dayLog('jxldbapi/Insertsql', "insert sql is:".$insertTagSql,$tag_list,$jxl_info);
  //       }
  //       // Logger::dayLog('jxldbapi/batchInsertTag', "insert success_count:".$ok,$tag_list,$jxl_info);
  //       echo "insert success_count:".$ok, "\n" ;
  //       return $ok;
  //       } catch (\Exception $e) {
		// 	Logger::dayLog('error', 'insert error',$e->getMessage(),$jxl_info,$tag_list);
		// 	return 0;
		// }
		// 逐条
        $num = 0;
        foreach ($tag_list as $phone => $tag) {
        	$saveDate = [
        		'phone' => (string)$phone,
        		'source'=> ArrayHelper::getValue($tag,'source',''),
        		'tag_type' => ArrayHelper::getValue($tag,'tag',''),
        	];
        	$res = false;
/*            try {
                $res = (new MyPhoneTagList())->saveData($saveDate);
            } catch (\Exception $e) {
                Logger::dayLog('batchInsertTag','save fail',$e->getMessage());
            }*/

           	try {
                # save tidb
                $res = (new TiPhoneTagList())->saveData($saveDate);
            } catch (\Exception $e) {
                Logger::dayLog('queryapi/insertTidb','save fail',$e->getMessage());
            }

           if ($res) {
           		Logger::dayLog('success_phone','success phone is ： ',$phone);
               $num++;
           }
        }
        echo "insert success_count:".$num, "\n" ;
        return $num;
	}

	// 只更新
	private function batchUpTag($tag_list,$jxl_info)
	{
		try {
		if (empty($tag_list)) {
			Logger::dayLog('jxldbapi/saveTag', 'update_tag_list is empty',$tag_list,$jxl_info);
			return 0;
		}
		$time = date('Y-m-d H:i:s');
		$type = 2;
		$status = 0;
		$phones = implode("','", array_keys($tag_list)); 
		$sql = "UPDATE phone_tag_list SET tag_type = CASE phone ";
		foreach ($tag_list as $phone => $tag) { 
			$phone = addslashes(trim($phone));
            $tag_type = addslashes(trim($tag['tag']));
		    $sql .= sprintf("WHEN '%s' THEN '%s' ", $phone, $tag_type);
		} 
		$sql .= "END , source = '".self::$source."', modify_time = '".$time."' WHERE phone IN ('$phones')"; 
		// $commandInsert = $this->db->createCommand($sql);
		$commandInsert = Yii::$app->db_analysis_repertory->createCommand($sql);
        $ok = $commandInsert->execute();
        $this->db->close();
        if ($ok == 0) {
        	Logger::dayLog('jxldbapi/upsql', "update sql is:".$sql,$tag_list,$jxl_info);
        }
        Logger::dayLog('jxldbapi/upTag', "update success_count:".$ok,$tag_list,$jxl_info);
        echo "update success_count:".$ok, "\n" ;
        return $ok;
        } catch (\Exception $e) {
			Logger::dayLog('error', 'update error',$e->getMessage(),$jxl_info,json_encode($tag_list));
			return 0;
		}
	}

	// 只更新TIDB
	private function updateTagBatch($tag_list,$jxl_info)
	{
		try {
		if (empty($tag_list)) {
			Logger::dayLog('jxldbapi/upTagtidb', 'update_tag_list is empty',$tag_list,$jxl_info);
			return 0;
		}
		$time = date('Y-m-d H:i:s');
		$type = 2;
		$status = 0;
		$phones = implode("','", array_keys($tag_list)); 
		$sql = "UPDATE phone_tag_list SET tag_type = CASE phone ";
		foreach ($tag_list as $phone => $tag) { 
			$phone = addslashes(trim($phone));
            $tag_type = addslashes(trim($tag['tag']));
		    $sql .= sprintf("WHEN '%s' THEN '%s' ", $phone, $tag_type);
		} 
		$sql .= "END , source = '".self::$source."', modify_time = '".$time."' WHERE phone IN ('$phones')"; 
		// $commandInsert = $this->db->createCommand($sql);
		$commandInsert = $this->db_tidb->createCommand($sql);
        $ok = $commandInsert->execute();
        $this->db->close();
        if ($ok == 0) {
        	Logger::dayLog('jxldbapi/upTagtidb', "update sql is:".$sql,$tag_list,$jxl_info);
        }
        Logger::dayLog('jxldbapi/upTagtidb', "update success_count:".$ok,$tag_list,$jxl_info);
        echo "update success_count:".$ok, "\n" ;
        return $ok;
        } catch (\Exception $e) {
			Logger::dayLog('jxldbapi/error', 'update error',$e->getMessage(),$jxl_info,json_encode($tag_list));
			return 0;
		}
	}
	// 存在则更新，不存在则新增
	public function batchSaveTag($tag_list,$jxl_info)
	{
		// try {
		if (empty($tag_list)) {
			Logger::dayLog('jxldbapi/saveTag', 'update_tag_list is empty',$tag_list,$jxl_info);
			return 0;
		}
		$time = date('Y-m-d H:i:s');
		$savedateStr = '';
		$all_list = array_merge($tag_list['in_list'],$tag_list['up_list']);
		// var_dump($all_list);
		foreach ($all_list as $key => $val) {
            $phone = addslashes(trim($key));
            $tag_type = addslashes(trim($val['tag']));
            $savedateStr = $savedateStr. ",('" . $phone . "','" . $tag_type . "','" . self::$source . "','" . $time . "','" . $time . "')";
        }
        $saveTagSql = 'insert into phone_tag_list (`phone`,`tag_type`,`source`,`modify_time`,`create_time`) values'. trim($savedateStr,',').' ON DUPLICATE KEY UPDATE  tag_type=values(`tag_type`),source=values(`source`),modify_time=values(`modify_time`)';
        $commandInsert = $this->db->createCommand($saveTagSql);
        $ok = $commandInsert->execute();
        if (!empty($ok)) {
			echo "save True ", "\n" ;
		} else {
			echo "save False", "\n" ;
		}
        echo "save success_count:".$ok, "\n" ;
        $this->db->close();
        Logger::dayLog('jxldbapi/saveTag', "save success_count:".$ok);
        return $ok;
  //       } catch (\Exception $e) {
		// 	Logger::dayLog('batchSaveTag', 'Save error',$e->getMessage(),$jxl_info);
		// 	return 0;
		// }
	}
}
