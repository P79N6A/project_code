<?php
/**
 *  定时同步jxl_stat报告信息到mycat里
 */
namespace app\commands;

use Yii;
use yii\web\Controller;
use app\common\Logger;
use yii\helpers\ArrayHelper;

use app\models\jxl\JxlApi;
use app\models\jxl\JxlDbApi;
use app\models\anti\AfBaseApi;


/**
 * 同步报告手机标签数据
 * 本地测试：/usr/local/bin/php /data/wwwroot/cloud/yii  sync-reportssdb runreport
 */
class SyncReportController extends BaseController
{   
    private static $db_open;
    private static $allRun = 2000;
    private static $worker = 1;
    private static $step = 10;

    public function init()
    {
        self::$db_open = Yii::$app->db_open;
    }

    public function runReport($startId = null, $endId = null) 
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
        echo "all success : start_id->".$id['startId']." || end_id->".$id['endId'];
        return true;
    }

    public function runReportOne($startId = null, $endId = null)
    {   
        $time1 = explode(' ',microtime());
        $id = $this->setId($startId,$endId);
        if (empty($id)) {
            return false;
        }
        $chuckData = $this->stepData($id['startId'],$id['endId']);
        foreach ($chuckData as $key => $sunWorkerData) {  
            $start = $sunWorkerData['start_id'];  
            $end = $sunWorkerData['end_id'];  
            echo $start .",".$end."\n";
            $this->runOne($start,$end);
        }
        echo "all success : start_id->".$id['startId']." || end_id->".$id['endId']."\n";
        $time2 = explode(' ',microtime());
        $thistime1 = $time2[0]+$time2[1]-($time1[0]+$time1[1]);
        Logger::dayLog('syncReport/runReportOne','runOne use_time:', $thistime1);
        echo "runOne use_time:".$thistime1;
        return true;
    }

    private function setId($startId,$endId)
    {
        $dbapi = new JxlDbApi();
        // try{
        if (!$startId){
            $startFile = Yii::$app ->basePath . '/commands/data/reportId.txt';
            if(file_exists($startFile)){
                try {
                    $startId = file_get_contents($startFile);
                } catch (Exception $e) {
                    return [];
                }
            }
        }
        
        if(!$startId){
            Logger::dayLog('syncReport/setId','no start_id',$startId);
            return [];
        }
        if (!$endId){
            $endId = $startId + self::$allRun;
        }
        $maxId = $dbapi->getMaxId();
        self::$db_open->close();
        if ($endId > $maxId){
            $endId = $maxId;
        }
        if (!isset($startFile)) {
            $startFile = Yii::$app ->basePath . '/commands/data/reportId.txt';
        }
        $resFile = file_put_contents($startFile,$endId);
        if(!$resFile){
            Logger::dayLog('syncReport/setId','file_put_contents is fail',$endId);
            return [];
        }
        return ['startId' => $startId,'endId' => $endId];
        // }catch (\Exception $e){
        //     Logger::dayLog('syncReport/setId','all address save fail，reason: '.$e->getMessage(),$startId,$endId);
        //     return [];
        // }
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
                Logger::dayLog('syncReport/forkWorker','runOne multi use_time:', $thistime1);
                echo "runOne multi use_time:".$thistime1;
                exit(0) ;  
            }  
        }
    }

    private function runOne($startId,$endId) 
    {   
        $dbapi = new JxlDbApi();
        $jxl_where = ['and',['>=','id',$startId],['in','source',['1','2','6']],['<','id',$endId],['!=','website','jingdong']];
        $jxl_select = 'id,phone,url,create_time,source';
        $jxl_infos = $dbapi->getJxlStatData($jxl_where,$jxl_select);
        if (empty($jxl_infos)){
            Logger::dayLog("syncReport/runOne", "no data to dealwith",$startId,$endId);
            echo 'jxl_list is empty';
            return false;
        }
        $api = new JxlApi();
        $all_conut = 0;
        foreach ($jxl_infos as $jxl_info){
            // try{
                $mobile = addslashes(trim($jxl_info['phone']));
                $source = (int)$jxl_info['source'];
                $report_info= $api->getReport($jxl_info);
                // Logger::dayLog('syncReport/runOne',$report_info);
                if(empty($report_info)){
                    Logger::dayLog('syncReport/runOne','report_info is empty',$jxl_info);
                    echo 'report_info is empty',"\n" ;
                    continue;
                }
                # save contact_analysis_list
                if (in_array($source,JxlApi::$mohe_arr)) {
                    $contact_analysis = ArrayHelper::getValue($report_info, 'contact_analysis', []);
                    if (!empty($contact_analysis)) {
                        $afapi = new AfBaseApi();
                        $res = $afapi->saveContact($contact_analysis,$jxl_info);
                        if (!$res) {
                            Logger::dayLog('syncReport/runOne','save Contact fail',$contact_analysis,$jxl_info);
                        } 
                    }
                }

                $tag_list = ArrayHelper::getValue($report_info, 'tag_list', []);
                //save tag_list
                if (empty($tag_list) ) {
                    // Logger::dayLog('syncReport/runOne','tag_list is empty',$jxl_info);
                    echo 'tag_list is empty',"\n" ;
                    continue;
                }
                $save_tag = $dbapi->SaveTagList($tag_list,$jxl_info);
                if (!$save_tag) {
                    // Logger::dayLog('syncReport/runOne','save tag_list is fail',$jxl_info,$tag_list);
                    echo 'save tag_list is '.$save_tag,"\n" ;
                    continue;
                }
                $all_conut += $save_tag;
                // Logger::dayLog('syncReport/runOne','save succcess count is: '.$save_tag,$jxl_info);
            // }catch (\Exception $e){
            //     Logger::dayLog('syncReport/runOne','tag_list save fail，reason: '.$e->getMessage(),$jxl_info);
            //     continue;
            // }
        }
        Logger::dayLog('syncReport/runOne','save succcess allcount is: '.$all_conut,$startId,$endId);
        echo 'save succcess allcount is: '.$all_conut,"\n" ;
        return $all_conut;
    }
}