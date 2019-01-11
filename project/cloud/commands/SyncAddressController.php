<?php
/**
 *  定时同步通讯录数据yi_address_list信息到mycat里
 */
namespace app\commands;
use app\common\Logger;
use Yii;

// */1 * * * * /usr/local/bin/php /data/wwwroot/cloud/yii sync-address runAddress >/dev/null 2>&1
class SyncAddressController extends BaseController 
{   
    private static $db_yiyiyuan;
    private static $db_own_yiyiyuan;
    private static $db_analysis_repertory;
    private static $worker = 10;
    private static $step = 1000;


    public function init()
    {
        self::$db_yiyiyuan = Yii::$app->db_yiyiyuan;
        self::$db_own_yiyiyuan = Yii::$app->db_own_yiyiyuan;
        self::$db_analysis_repertory = Yii::$app->db_analysis_repertory;
    }

    /**
     * @desc  
     * @param $startId 
     * @param $endId 
     */
    public function runAddress($startId = null, $endId = null) 
    {   
        if (!function_exists("pcntl_fork")) { 
            die("pcntl extention is must !"); 
          } 
        if(!$startId){
            Logger::dayLog('SyncAddress','no start_id',$startId);
            die("startId is must !"); 
        }
        if(!$endId){
            Logger::dayLog('SyncAddress','no end_id ',$endId);
            die("endId is must !"); 
        }
        $chuckData = $this->interval($startId,$endId);
        $res = $this->forkWorker($chuckData);
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
                $this->runOneWorker($start,$end);
                exit(0) ;  
            }  
        }
    }
    private function runOneWorker($startId,$endId)
    {
        $arr = $this->stepData($startId,$endId);

        if(empty($arr)){
            die('stepData is empty');
        }
        foreach ($arr as $key => $value) {
            $res = $this->runOneStepData($value['start_id'],$value['end_id']);
        }
    }

    private function runOneStepData($startId,$endId)
    {   
        $selectSql = "select id,user_id,phone,name,modify_time,create_time from yi_address_list where id >= '".$startId."' and id <'".$endId."'";
        $command = self::$db_own_yiyiyuan->createCommand($selectSql);
        $addressList = $command->queryAll();
        if (empty($addressList)){
            Logger::dayLog('SyncAddress','no data',$startId,$endId);
            return false;
        }
        $insertStr = '';
        foreach ($addressList as $address){
            $id = (int)$address['id'];
            $aid = 1;
            $userId = (int)$address['user_id'];
            $phone = addslashes(trim($address['phone']));
            $name = addslashes(trim($address['name']));
            $modifyTime = $address['modify_time'];
            $createTime = $address['create_time'];

            $querySql = "select mobile from yi_user where user_id = '".$userId."'";
            $commandQuery = self::$db_yiyiyuan->createCommand($querySql);
            $res = $commandQuery->queryOne();
            $userPhone = $res ? $res['mobile'] : '';
            $insertStr = $insertStr. ",('" . $id . "','" . $aid . "','" . $userId . "','" . $userPhone . "','" . $phone . "','" . $name . "','" . $modifyTime . "','" . $createTime . "')";
        }

        $insertSql = 'insert into address_list (`id`,`aid`,`user_id`,`user_phone`,`phone`,`name`,`modify_time`,`create_time`) values'. trim($insertStr,',');
        $commandInsert = self::$db_analysis_repertory->createCommand($insertSql);
        $ok = $commandInsert->execute();

        $insertSql2 = 'insert into reverse_address_list (`id`,`aid`,`user_id`,`user_phone`,`phone`,`name`,`modify_time`,`create_time`) values'. trim($insertStr,',');
        $commandInsert2 = self::$db_analysis_repertory->createCommand($insertSql2);
        $ok2 = $commandInsert2->execute();

        return $ok2;
    }
}