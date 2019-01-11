<?php
/**
 *  定时同步jxl_stat详单信息到ssdb里
 */
namespace app\commands;

use Yii;
use app\models\jxl\JxlApi;
use app\models\jxl\JxlStat;
use app\common\Logger;

/**
 * 同步详单数据
 * 本地测试：/usr/local/bin/php /data/wwwroot/cloud/yii  sync-detail rundetail
 */
class SyncDetailController extends BaseController
{   
    private $jxl_db;
    private static $db_open;
    private static $allRun = 2000;
    private static $worker = 1;
    private static $step = 1000;

    public function init()
    {
        self::$db_open = Yii::$app->db_open;
        $this->jxl_db = new JxlStat();
    }

    public function runDetail($startId = null, $endId = null) 
    {   
        if (!function_exists("pcntl_fork")) { 
            die("pcntl extention is must !");
        }
        $id = $this->setId($startId,$endId);
        if (empty($id)) {
            return false;
        }
        $chuckData = $this->interval($id['startId'],$id['endId']);
        $res = $this->forkWorker($chuckData);
        return true;
    }

    public function runDetailOne($startId = null, $endId = null)
    {   
        $id = $this->setId($startId,$endId);
        if (empty($id)) {
            Logger::dayLog('SyncDetail/runDetailOne','Id is null',$startId,$endId);
            die("Id is null!");
        }
        $chuckData = $this->stepData($id['startId'],$id['endId']);
        foreach ($chuckData as $key => $sunWorkerData) {  
            $start = $sunWorkerData['start_id'];  
            $end = $sunWorkerData['end_id'];  
            echo $start .",".$end."\n";
            $time1 = explode(' ',microtime());
            $this->runOne($start,$end);
            $time2 = explode(' ',microtime());
            $thistime1 = $time2[0]+$time2[1]-($time1[0]+$time1[1]);
            Logger::dayLog('SyncDetail/runDetailOne','runOne use_time:', $thistime1);
            echo "use time : ".$thistime1;
        }
        return true;
    }
    private function setId($startId,$endId)
    {
        try{
        if (!$startId){
            $startFile = Yii::$app ->basePath . '/commands/data/detailId.txt';
            if(file_exists($startFile)){
                try {
                    $startId = file_get_contents($startFile);
                } catch (\Exception $e) {
                    Logger::dayLog('SyncDetail/setId','open file fail : ',$startFile,$e->getMessage());
                    return [];
                }
            }
        }
        
        if(!$startId){
            Logger::dayLog('SyncDetail/setId','no start_id',$startId);
            return [];
        }
        if (!$endId){
            $endId = $startId + self::$allRun;
        }
        $maxId = $this->jxl_db->getJxlMaxId();
        self::$db_open->close();
        if ($endId > $maxId){
            $endId = $maxId;
        }
        if (!isset($startFile)) {
            $startFile = Yii::$app ->basePath . '/commands/data/detailId.txt';
        }
        $resFile = file_put_contents($startFile,$endId);
        if(!$resFile){
            Logger::dayLog('SyncDetail/setId','file_put_contents is fail',$endId);
            return [];
        }
        return ['startId' => $startId,'endId' => $endId];
        }catch (\Exception $e){
            Logger::dayLog('SyncDetail/setId','all address save fail，reason: '.$e->getMessage(),$startId,$endId);
            return [];
        }
    }
    private function interval($startId,$endId)
    {
        $arr = [];
        $interval = $endId - $startId;
        $baseNun = ceil($interval/self::$worker);
        for ($i=1; $i <= self::$worker; $i++) {
            $arr[$i]['start_id'] = $startId;
            if($i>1){
                $arr[$i]['start_id'] = $startId + ($i-1)*$baseNun;
            }
            $arr[$i]['end_id'] = $startId + $i*$baseNun;
            if($i == self::$worker){
                $arr[$i]['end_id'] = $endId;
            }
        }
        return $arr;
    }

    private function stepData($startId,$endId)
    {
        $arr = [];
        $step = $startId;
        $i = 1;
        while($step < $endId){
            $arr[$i]['start_id'] = $step;
            $step += self::$step;
            $arr[$i]['end_id'] = $step;
            $i++;
        }
        return $arr;
    }
    private function forkWorker($chuckData)
    {   
        echo "parent start, pid ", getmypid(), "\n" ;
        pcntl_signal(SIGCHLD, SIG_IGN); 
        foreach ($chuckData as $key => $sunWorkerData) {  
            $pid = pcntl_fork();  
            if ($pid == -1) {  
                die();  
            } else if ($pid > 0) {  
                pcntl_wait($status,WNOHANG); 
            } else if ($pid == 0) {  
                echo "child start, pid ", getmypid(), "\n" ; 
                $start = $sunWorkerData['start_id'];  
                $end = $sunWorkerData['end_id'];  
                echo $start .",".$end."\n";
                $time1 = explode(' ',microtime());
                $this->runOne($start,$end);
                $time2 = explode(' ',microtime());
                $thistime1 = $time2[0]+$time2[1]-($time1[0]+$time1[1]);
                Logger::dayLog('SyncDetail/forkWorker','runOne multi use_time:', $thistime1);
                exit(0) ;  
            }  
        }
    }

    private function runOne($startId,$endId)
    {   
        try{
        $jxl_where = ['and',['>=','id',$startId],['<','id',$endId],['!=','website','jingdong']];
        $jxl_select = 'id,phone,url,create_time,source';
        $jxl_infos = $this->jxl_db->getJxlInfo($jxl_where,$jxl_select);
        if (empty($jxl_infos)){
            Logger::dayLog("SyncDetail/runOne", "no data to dealwith",$startId,$endId);
            echo 'jxl_list is empty';
            return false;
        }
        $resArr = [];
        $api = new JxlApi();
        $ok = 0;
        $time = date('Y-m-d H:i:s');
        foreach ($jxl_infos as $jxl_info){
            $mobile = addslashes(trim($jxl_info['phone']));
            $detail_list= $api->getDetailPhones($jxl_info);
            if(empty($detail_list)){
                Logger::dayLog('SyncDetail/runOne','detail_list is empty',$jxl_info);
                echo 'detail_list is empty';
                continue;
            }
            $all_data = ['phoneArr' => $detail_list,
                            'create_time' => $time,
                            'modify_time' => $time,];
            $resArr[$mobile] = json_encode($all_data);
            ###  单条插入(更新)
            $save_res = $this->setOneSsdb($mobile,$all_data);
            if ($save_res) {
                $ok++;
            }
        }
        ###  批量插入ssdb（覆盖）
        if (empty($resArr)) {
            Logger::dayLog('SyncDetail/runOne',var_dump($resArr).'save fail',$startId,$endId);
            die("no data to save");
        }
        // $ok = Yii::$app->ssdb->multi_set($resArr);
        if(!$ok){
            Logger::dayLog('SyncDetail/runOne',var_dump($resArr).'save fail',$startId,$endId);
            die(print_r($resArr)." save fail");
        }
        Logger::dayLog('SyncDetail/runOne','save succcess count is '.$ok,$startId,$endId);
        echo "success_count:".$ok, "\n" ;
        return $ok;
        }catch (\Exception $e){
            Logger::dayLog('SyncDetail/runOne','detail_list save fail，reason: '.$e->getMessage(),$startId,$endId);
            return false;
        }
    }

    // 单条插入ssdb
    private function setOneSsdb($phone,$save_data)
    {
        $new_data = [];
        $old_data = Yii::$app->ssdb_detail->get($phone);
        if ($old_data) {
            $old_data = json_decode($old_data,true);
            $old_phone_array = $old_data['phoneArr'];
            $new_phone_array = $save_data['phoneArr'];
            $new_data = $this->getArrayUnion($old_phone_array,$new_phone_array);
            $save_data['phoneArr'] = $new_data;
            $save_data['create_time'] = $old_data['create_time'];
        }
        // $ok = Yii::$app->ssdb_detail->del($phone);
        $ok = Yii::$app->ssdb_detail->set($phone,json_encode($save_data));
        if (!$ok) {
            Logger::dayLog('SyncDetail/setOneSsdb','save fail',$phone,$save_data);
        }
        return $ok;
    }
    private function getArrayUnion($array_a,$array_b)
    {
        $array_union = array_merge($array_a,$array_b);
        $array_union = array_unique($array_union);
        $array_union = array_values($array_union);
        return $array_union;
    }
}