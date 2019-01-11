<?php
/**
 *  定时号码标签数据phone_tag_list信息到mycat里
 */
namespace app\commands;

use Yii;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use app\models\anti\PhoneTagList;
use app\models\mycat\MyPhoneTagList;


// */1 * * * * /usr/local/bin/php /data/wwwroot/cloud/yii sync-tag runTag >/dev/null 2>&1
class SyncTagController extends BaseController 
{   

//    private $phone_tag_db;
    private $db;
    const INTERVAL = 50000;

    const PART = 1000;

    public function init()
    {
        $this->db = Yii::$app->db_analysis_repertory2;
//        $this->phone_tag_db = new PhoneTagList();
    }

    public function runOne($type = 1,$limit = 2000)
    {   
        $starttime = explode(' ',microtime());
        //1 获取初始数据 
        $tag_where = ['and',['status'=>0],['type'=>$type]];
        // $tag_where = ['and',['status'=>0],['type'=>2]];
        $tag_select = 'id,phone,tag_type,source,type';
        $phone_tag_db = new PhoneTagList();
        $tagInfoList = $phone_tag_db->getTagInfo($tag_where,$limit,$tag_select);
        if (empty($tagInfoList)){
            Logger::dayLog('syncTag','no data');
            echo "no data to sync","\n";
            return false;
        }
        //2 锁定状态,避免下次重复处理
        $lock_ids = ArrayHelper::getColumn($tagInfoList, 'id');
        $lockNums = $phone_tag_db->lockStatus($lock_ids,'1');
        if ($lockNums == 0){
            Logger::dayLog('syncTag','lockNums is 0');
            return false;
        }
        
        //3 整理数据 并分组
        $up_list = [];
        $in_list = [];
        $time = date('Y-m-d H:i:s');
        foreach ($tagInfoList as $tagInfo){
            $data = [
                'phone' => $tagInfo['phone'],
                'tag_type' => $tagInfo['tag_type'],
                'source' => $tagInfo['source'],
                'modify_time' => $time,
                'create_time' => $time,
            ];
            if ($tagInfo['type'] == 2) {
                $up_list[] = $data;
            } elseif ($tagInfo['type'] == 1) {
                $in_list[] = $data;
            }
        }
        $in_res = 0;
        $up_res = 0;
        $allNums = 0;
        if (!empty($up_list)) {
            $up_res = $this->batchUpTag($up_list);
            if ($up_res == 0) {
                Logger::dayLog('syncTag','upNum is '.$up_res);
            }
        }
        if (!empty($in_list)) {
            // 批量
            // $in_res = $this->add_all($in_list);
            // 逐条
            $in_res = $this->add_one($in_list);
            if ($in_res == 0) {
                Logger::dayLog('syncTag','insertNum is '.$in_res);
            }
        }
        $allNums = $in_res+$up_res;
        echo "allNums is : ".$allNums,"\n";
        //4 更新完成
        if ($allNums != $lockNums) {
            Logger::dayLog('syncTag/lock','lockNums is '.$lockNums,$allNums);
        }
        $phone_tag_db = new PhoneTagList();
        $lockNums = $phone_tag_db->lockStatus($lock_ids,'2');
        $endtime1 = explode(' ',microtime());
        $thistime1 = $endtime1[0]+$endtime1[1]-($starttime[0]+$starttime[1]);
        $thistime1 = round($thistime1,3);
        echo "use_time：".$thistime1." S\n";
        Logger::dayLog('syncTag/time','use_time： is '.$thistime1,$allNums);
        return $lockNums;
    }

    /**
     * @desc  
     * @param $startId 
     * @param $endId 
     */
    public function runTag($startId = null, $endId = null)
    {   
        $starttime = explode(' ',microtime());
        if (!$startId){
            $startFile = Yii::$app ->basePath . '/commands/data/tagId.txt';
            $startId = file_get_contents($startFile);
        }
        if(!$startId){
            Logger::dayLog('syncTag','no start_id',$startId);
            return false;
        }
        if (!$endId){
            $endId = $startId + self::INTERVAL;
        }
        $phone_tag_db = new PhoneTagList();
        $maxId = $phone_tag_db->getTagMaxId();
        if ($endId > $maxId){
            $endId = $maxId;
        }
        if (!isset($startFile)) {
            $startFile = $startFile = Yii::$app ->basePath . '/commands/data/tagId.txt';
        }
        $resFile = file_put_contents($startFile,$endId);
        if(!$resFile){
            Logger::dayLog('syncTag','file_put_contents is fail',$endId);
            return false;
        }
        
        $step = $startId;
        $i = 1;
        while($step < $endId){
            $sId = $startId;
            if($i>1){
                $sId = $startId + ($i-1)*self::PART;
            }
            $eId = $startId + $i*self::PART;
            if($eId>$endId){
                $eId = $endId;
            }
            $res = $this->runOne($sId,$eId);
            Logger::dayLog('syncTag','runOne result',$res,$sId,$eId,$i);
            $step += self::PART;
            $i++;
        }

        $endtime1 = explode(' ',microtime());
        $thistime1 = $endtime1[0]+$endtime1[1]-($starttime[0]+$starttime[1]);
        // $thistime1 = round($thistime1,3);
        echo "use_time：".$thistime1." S\n";
    }

    // 只新增
    public function batchInsertTag($tag_list)
    {
        // try {
        if (empty($tag_list)) {
            Logger::dayLog('syncTag/batchInsertTag', 'insert_tag_list is empty',$tag_list);
            return 0;
        }
        $time = date('Y-m-d H:i:s');
        $insertStr = '';
        $status = 0;
        $type = 1;
        //set sql
        foreach ($tag_list as $val) {
            $phone = addslashes(trim($val['phone']));
            $tag_type = addslashes(trim($val['tag_type']));
            $source = (int)addslashes(trim($val['source']));
            $insertStr = $insertStr. ",('" . $phone . "','" . $tag_type . "','" . $source . "','" . $time . "','" . $time ."')";
        }
        // insert tag_list
        $insertTagSql = 'insert into phone_tag_list (`phone`,`tag_type`,`source`,`modify_time`,`create_time`) values'. trim($insertStr,',');
        Logger::dayLog('sql',$insertTagSql);
        $commandInsert = Yii::$app->db_analysis_repertory->createCommand($insertTagSql);
        $ok = $commandInsert->execute();
        var_dump($ok);
        $this->db->close();
        if ($ok == 0) {
            Logger::dayLog('syncTag/Insertsql', "insert sql is:".$insertTagSql,$tag_list);
        }
        Logger::dayLog('syncTag/batchInsertTag', "insert success_count:".$ok);
        echo "insert success_count:".$ok, "\n" ;
        return $ok;
  //       } catch (\Exception $e) {
        //  Logger::dayLog('batchInsertTag', 'insert error',$e->getMessage(),$jxl_info);
        //  return 0;
        // }
    }

    // 只更新
    private function batchUpTag($tag_list)
    {
        // try {
        if (empty($tag_list)) {
            Logger::dayLog('syncTag/saveTag', 'update_tag_list is empty',$tag_list);
            return 0;
        }
        $time = date('Y-m-d H:i:s');
        $phone_list = ArrayHelper::getColumn($tag_list, 'phone');
        $phones = implode("','", $phone_list);
        $s_sql = '';
        $sql = "UPDATE phone_tag_list SET tag_type = CASE phone ";
        foreach ($tag_list as $val) { 
            $phone = addslashes(trim($val['phone']));
            $tag_type = addslashes(trim($val['tag_type']));
            $source = (int)addslashes(trim($val['source']));
            $sql .= sprintf("WHEN '%s' THEN '%s' ", $phone, $tag_type);
            $s_sql .= sprintf("WHEN '%s' THEN '%s' ", $phone, $source);
        } 
        $sql .= "END , source = CASE phone ".$s_sql." END ";
        $sql .= ", modify_time = '".$time."' WHERE phone IN ('$phones')";
        // Logger::dayLog('sql',$sql);
        $commandInsert = Yii::$app->db_analysis_repertory->createCommand($sql);
        $ok = $commandInsert->execute();
        // var_dump($ok);
        $this->db->close();
        if ($ok == 0) {
            Logger::dayLog('syncTag/upsql', "update sql is:".$sql,$tag_list);
        }
        // Logger::dayLog('syncTag/upTag', "update success_count:".$ok);
        echo "update success_count:".$ok, "\n" ;
        return $ok;
  //       } catch (\Exception $e) {
        //  Logger::dayLog('batchUpTag', 'update error',$e->getMessage(),$jxl_info);
        //  return 0;
        // }
    }

    public function add_all($add)  
    {   
        $connection = Yii::$app->db_analysis_repertory;
        //数据批量入库  
        $res = $connection->createCommand()->batchInsert(  
            'phone_tag_list',  
            ['phone','tag_type','source','modify_time','create_time'],//字段  
            $add  
        );
        $sql = $res->getRawSql();
        Logger::dayLog('sql','add_all',$sql);
        $res = $res->execute(); 
        return $res;
    }  
    // 逐条
    public function add_one($tag_list)
    {
        $num = 0;
        foreach ($tag_list as $tag) {
            try {
                $res = (new MyPhoneTagList())->saveData($tag);
            } catch (\Exception $e) {
                Logger::dayLog('add_one','save fail',$e->getMessage());
                continue;
            }
           
           if ($res) {
               $num++;
           }
        }
        return $num;
    }
}