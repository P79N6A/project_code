<?php
/**
 *  定时同步cloud线下库更新快的数据信息到ssdb里
 */
namespace app\commands;

use Yii;
use app\models\jxl\JxlApi;
use app\models\jxl\JxlStat;
use app\common\Logger;

/**
 * 同步cloud线下库更新快的数据
 * 本地测试：/usr/local/bin/php /data/wwwroot/cloud/yii  sync-quick runquick
 */
class SyncQuickController extends BaseController
{   
    private static $db_cloud_new;
    private static $db_cloud_old;
    private $cloud_tables;
    private $sync_type;
    private $startFile;
    private static $allRun = 200;
    private static $worker = 1;
    private static $step = 100;

    public function init()
    {
        self::$db_cloud_new = Yii::$app->db_cloudnew;
        self::$db_cloud_old = Yii::$app->db;
        $stuff = SYSTEM_PROD ? '' : '_test';
        $this->startFile = Yii::$app ->basePath . '/commands/data/cloud_table'.$stuff.'.txt';
        $this->cloud_tables = json_decode(file_get_contents($this->startFile),true);
        $this->sync_type = ['quick','slow'];

    }

    public function runQuick($type = 0) 
    {   
        if (empty($this->cloud_tables)) {
            Logger::dayLog('SyncQuick/runQuick','cloud_tables is null',$type,$this->startFile);
            return false;
        }

        $tables = $this->cloud_tables[$this->sync_type[$type]];
        if ($type == 1) {
            $stuff = SYSTEM_PROD ? '' : '_test';
            $this->startFile = Yii::$app ->basePath . '/commands/data/cloud_table_slow'.$stuff.'.txt';
            $this->cloud_tables = json_decode(file_get_contents($this->startFile),true);
            $tables = $this->cloud_tables[$this->sync_type[$type]];
        }
        // 同步每个表
        foreach ($tables as $table => $id_arr) {
            $id_name = array_keys($id_arr)[0];
            $startId = $id_arr[$id_name];
            $id = $this->runQuickOne($table,$startId,$type,$id_name);
            
        }
        $updateFile = file_put_contents($this->startFile,json_encode($this->cloud_tables));
        return true;
    }

    public function runQuickOne($table,$start_id,$type,$id_name)
    {   
        $id = $this->setId($table,$start_id,$type,$id_name);
        if (empty($id)) {
            Logger::dayLog('SyncQuick/runQuickOne','Id is null',$start_id,$table);
            die("Id is null!");
        }
        $chuckData = $this->stepData($id['startId'],$id['endId']);

        foreach ($chuckData as $key => $sunWorkerData) {  
            $start = $sunWorkerData['start_id'];  
            $end = $sunWorkerData['end_id'];  
            echo $start .",".$end."\n";
            $time1 = explode(' ',microtime());
            $this->runOne($table,$start,$end,$id_name);
            $time2 = explode(' ',microtime());
            $thistime1 = $time2[0]+$time2[1]-($time1[0]+$time1[1]);
            Logger::dayLog('SyncQuick/runQuickOne','runOne use_time:', $thistime1);
            echo "use time : ".$thistime1;
        }
        return true;
    }
    private function setId($table,$startId,$type,$id_name)
    {
        if(!$table){
            Logger::dayLog('SyncQuick/setId','no table name',$startId);
            return [];
        }
        
        if(is_null($startId)){
            Logger::dayLog('SyncQuick/setId','no start_id',$startId);
            return [];
        }

        $endId = $startId + self::$allRun;

        $maxIdSql = "select max(".$id_name.") as max_id from ".$table;
        $maxIdCommand = self::$db_cloud_old->createCommand($maxIdSql);
        $maxId = $maxIdCommand ->queryOne();
        self::$db_cloud_old->close();
        $maxId = $maxId['max_id'];
        if ($endId > $maxId){
            $endId = $maxId;
        }
        # set id
        $this->cloud_tables[$this->sync_type[$type]][$table][$id_name] = $endId;
        return ['startId' => $startId,'endId' => $endId];
    }

    private function stepData($startId,$endId)
    {
        $arr = [];
        $step = $startId;
        $i = 1;
        while($step < $endId){
            $arr[$i]['start_id'] = (int)$step;
            $step += self::$step;
            if ($step > $endId) {
                $arr[$i]['end_id'] = (int)$endId;
            } else {
                $arr[$i]['end_id'] = $step;
            }
            $i++;
        }
        return $arr;
    }

    private function runOne($table,$startId,$endId,$id_name)
    {   
        try{
        $selectSql = "select * from ".$table." where ".$id_name."> '".$startId."' and ".$id_name." <='".$endId."'";
        $command = self::$db_cloud_old->createCommand($selectSql);
        $dataList = $command->queryAll();
        self::$db_cloud_old->close();
        if (empty($dataList)){
            Logger::dayLog("SyncQuick/runOne", "no data to dealwith",$table,$startId,$endId);
            echo 'dataList is empty';
            return false;
        }
        $all = count($dataList);
        //数据批量入库  
        $field = array_keys($dataList[0]);
        $n = 0;
        foreach ($dataList as $data) {
            try{
                $res = self::$db_cloud_new->createCommand()->insert(  
                            $table, // 表名
                            $data // 数据
                        );
                // $sql = $res->getRawSql();
                // var_dump($sql);
                $res = $res->execute();
                if ($res) {
                    $n++;
                }
            } catch (\Exception $e) {
                Logger::dayLog('SyncQuick/sync_error', 'sync_error',$e->getMessage());
                continue;
            }
        }
        Logger::dayLog("SyncQuick/success", "teble ".$table." success num is ".$n,"all data is " .$all,$startId,$endId);
        return $n;
        } catch (\Exception $e) {
         Logger::dayLog('SyncQuick/sync_error', 'sync_error',$e->getMessage(),$table,$startId);
         return 0;
        }
    }
}