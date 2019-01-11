<?php
/**
 *  定时同步通讯录数据yi_address_list信息到ssdb里
 *
 * /1 * * * * /usr/local/bin/php /data/wwwroot/test/cloud/yii sync-addrssdb runAddress 10000 >/dev/null 2>&1
 *php  D:\phpstudy\php55\php.exe D:\phpstudy\WWW\cloud_ssdb/yii sync-addrssdb runAddress
 */
namespace app\commands;

use app\common\Logger;

use Yii;
use yii\base\Exception;
use yii\helpers\ArrayHelper;


class SyncAddrssdbController extends BaseController
{   
    private static $db_anti;
    private static $db_yiyiyuan;
    private static $db_analysis_repertory;

    private static $worker = 10;
    private static $step = 250;
    private static $allRun = 2500;

    public function init()
    {
        self::$db_anti = Yii::$app->db_anti;
        self::$db_yiyiyuan = Yii::$app->db_yiyiyuan;
        self::$db_analysis_repertory = Yii::$app->db_tidb;
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
        $id = $this->setId($startId,$endId);
        if(!$id){
            Logger::dayLog('SyncAddressdb','no ids');
            die("no ids");
        }
        $startId = $id['startId'];
        $endId = $id['endId'];
        if(!$startId){
            Logger::dayLog('SyncAddressdb','no start_id',$startId);
            die("startId is must !"); 
        }
        if(!$endId){
            Logger::dayLog('SyncAddressdb','no end_id ',$endId);
            die("endId is must !"); 
        }
        $chuckData = $this->interval($id['startId'],$id['endId']);
        $res = $this->forkWorker($chuckData);
        return true;
    }

    private function setId($startId,$endId)
    {
        // try{
        if (!$startId){
            $startFile = Yii::$app ->basePath . '/commands/data/addressId.txt';
            if(file_exists($startFile)){
                try {
                    $startId = file_get_contents($startFile);
                } catch (Exception $e) {
                    Logger::dayLog('SyncAddressdb','open file fail : ',$startFile,$e->getMessage());
                    return [];
                }
            }
        }
        if (!$endId){
            $endId = $startId + self::$allRun;
        }
        $selectSql = "select max(id) from api_base";
        $command = self::$db_anti->createCommand($selectSql);
        $maxId = $command->queryScalar();
        self::$db_anti->close();

        if ($startId > $maxId){
            return [];
        }

        if ($endId > $maxId){
            $endId = $maxId;
        }
        if (($endId-$startId)<self::$worker) {
            return [];
        }

        $startFile = Yii::$app ->basePath . '/commands/data/addressId.txt';
        $resFile = file_put_contents($startFile,$endId);
        if(!$resFile){
            Logger::dayLog('SyncAddressdb','file_put_contents is fail',$endId);
            return [];
        }
        return ['startId' => $startId,'endId' => $endId];
    }

    private function interval($startId,$endId)
    {
        $arr = [];
        $interval = $endId - $startId;
        $baseNun = ceil($interval/self::$worker);
        for ($i=1; $i <= self::$worker; $i++) {
            $arr[$i]['start_id'] = (int)$startId;
            if($i>1){
                $arr[$i]['start_id'] = (int)($startId + ($i-1)*$baseNun);
            }
            $arr[$i]['end_id'] = (int)($startId + $i*$baseNun - 1);
            if($i == self::$worker){
                $arr[$i]['end_id'] = (int)$endId;
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
            if($step>$endId){
                $step = $endId;
            }
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
        try{
            $id = $startId;
            $resSSDB = [];
            while ($id <= $endId) {
                $selectSql = "select mobile from api_base where id = '".$id."'";
                $command = self::$db_anti->createCommand($selectSql);
                $userPhone = $command->queryScalar();
                self::$db_anti->close();
                if (!$userPhone) {
                    Logger::dayLog('SyncAddressdb',$userPhone.' userPhone not exists');
                    $id += 1;
                    continue;
                }
                echo $userPhone.PHP_EOL;
                $selectSql = "select phone from address_list where user_phone = '".$userPhone."'";
                $command = self::$db_analysis_repertory->createCommand($selectSql);
                $addressList = $command->queryAll();
                self::$db_analysis_repertory->close();
                if (empty($addressList)) {
                    Logger::dayLog('SyncAddressdb',$id.' addressList is empty');
                    $id += 1;
                    continue;
                }
                $adressArr = ArrayHelper::getColumn($addressList, 'phone');
                if(!empty($adressArr)){
                    $addresslist = $this->ChkPhone($adressArr,$userPhone);
                    $resSSDB[$userPhone] = json_encode($addresslist);
                }else{
                    Logger::dayLog('SyncAddressdb',$id.' addresslist is empty');
                }
                $id += 1;
            }
            if(empty($resSSDB)){
                Logger::dayLog('SyncAddressdb','all addresslist is empty'.($startId.'-'.$endId));
                echo 'addresslist is empty';
                return false;
            }
            $successCount = Yii::$app->ssdb_address->multi_set($resSSDB);
            if(!$successCount){
                Logger::dayLog('SyncAddressdb',var_dump($resSSDB).'save fail');
                die(var_dump($resSSDB)." save fail");
            }
            Logger::dayLog('SyncAddressdb',"success_count:".$successCount.'; Ids:'.($startId.'-'.$endId));
        }catch (Exception $e){
            Logger::dayLog('SyncAddressdb','all address save fail，reason: '.$e->getMessage().'; Ids:'.($startId.'-'.$endId));
            return false;
        }
    }
    # 数据去重 并过滤文字且删除+86
    private function ChkPhone($phone_list, $user_phone){
        $real_data = [];
        # 去重且去除用户本身
        $phone_list = array_flip($phone_list);
        unset($phone_list[$user_phone]);
        $phone_list = array_keys($phone_list);
        # 正则过滤并去除+86
        foreach ($phone_list as $phone) {
            $real_num = $this->checkTel($phone);
            if ($real_num) {
                $real_data[] =  $real_num;
                continue;
            }
            $real_num = $this->checkPhone($phone);
            if ($real_num) {
                $real_data[] =  $real_num;
                continue;
            }
        }
        $real_data = array_keys(array_flip($real_data));
        return $real_data;
    }
    // 验证手机号
    private function checkPhone($number)
    {
        $isMatched = preg_match('/^(\+?86-?)?1[2-9][0-9]\d{8}$/', $number, $matche_phone);
        if ($isMatched > 0) {
            if (substr($number,0,3) == '+86') {
                $number = trim(substr($number,3));
            }
            if (substr($number,0,2) == '86') {
                $number = trim(substr($number,2));
            }
            return (string)trim($number,'-');
        }
        return '';
    }
    // 验证电话号
    private function checkTel($number)
    {
        $isMatched = preg_match('/^800-?[0-9]{7}|^400-?[0-9]{7}|^0\d{2,3}-?\d{7,8}$|^([0-9]{3,4}-)?[0-9]{7,8}$/', $number, $matche_phone);
        if ($isMatched > 0) {
            return (string)$number;
        }
        return '';
    }
}