<?php
/**
 *  定时清洗
 *
 * /1 * * * * /usr/local/bin/php /data/wwwroot/test/cloud/yii sync-addrssdb runAddress 10000 >/dev/null 2>&1
 *php  D:\phpstudy\php55\php.exe D:\phpstudy\WWW\cloud_ssdb/yii clean-phone runClean
 */
namespace app\commands;

use app\common\Logger;

use Yii;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use app\models\yyy\YiUser;

class CleanPhoneController extends BaseController
{   
    private static $oYiUser;
    private static $ssdbAddress;

    private static $worker = 100;
    private static $step = 50;
    private static $allRun = 10000;

    public function init()
    {
        self::$oYiUser = new YiUser();
        self::$ssdbAddress = Yii::$app->ssdb_address;
    }

    /**
     * @desc  
     * @param $startId 
     * @param $endId 
     */
    public function runClean($startId = null, $endId = null) 
    {
        $time1 = explode(' ',microtime());
        $id = $this->setId($startId,$endId);
        if(!$id){
            Logger::dayLog('cleanPhone','no ids');
            die("no ids");
        }
        $startId = $id['startId'];
        $endId = $id['endId'];
        if(!$startId){
            Logger::dayLog('cleanPhone','no start_id',$startId);
            die("startId is must !"); 
        }
        if(!$endId){
            Logger::dayLog('cleanPhone','no end_id ',$endId);
            die("endId is must !"); 
        }
        $chuckData = $this->interval($id['startId'],$id['endId']);
        $res = $this->forkWorker($chuckData);
        $time2 = explode(' ',microtime());
        $thistime1 = $time2[0]+$time2[1]-($time1[0]+$time1[1]);
        Logger::dayLog('cleanPhone/runClean','runClean use_time:', $thistime1);
        echo "use time : ".$thistime1;
        return true;
    }

    private function setId($startId,$endId)
    {
        // try{
        if (!$startId){
            $startFile = Yii::$app ->basePath . '/commands/data/clean_address_id.txt';
            if(file_exists($startFile)){
                try {
                    $startId = file_get_contents($startFile);
                } catch (Exception $e) {
                    Logger::dayLog('cleanPhone','open file fail : ',$startFile,$e->getMessage());
                    return [];
                }
            }
        }
        if (!$endId){
            $endId = $startId + self::$allRun;
        }
        $maxId = self::$oYiUser->getMaxId();
        if ($startId > $maxId){
            return [];
        }

        if ($endId > $maxId){
            $endId = $maxId;
        }
        if (($endId-$startId)<self::$worker) {
            return [];
        }

        $startFile = Yii::$app ->basePath . '/commands/data/clean_address_id.txt';
        $resFile = file_put_contents($startFile,$endId);
        if(!$resFile){
            Logger::dayLog('cleanPhone','file_put_contents is fail',$endId);
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
        foreach ($chuckData as $key => $sunWorkerData) {
            $start = $sunWorkerData['start_id'];  
            $end = $sunWorkerData['end_id'];  
            echo $start .",".$end."\n";
            $this->runOneWorker($start,$end);
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
            $where = ['and',
                ['>=','user_id',$startId],
                ['<','user_id',$endId],
            ];
            $field = 'mobile';
            $dataList = self::$oYiUser->getListByUserId($where,$field);
            if (empty($dataList)){
                Logger::dayLog("syncPhone/runOne", "no data to dealwith",$startId,$endId);
                echo 'dataList is empty';
                return false;
            }
            $mobile_list = ArrayHelper::getColumn($dataList,'mobile',[]);
            if (empty($mobile_list)){
                Logger::dayLog("syncPhone/runOne", "no mobile to sync",$startId,$endId);
                echo 'mobile_list is empty';
                return false;
            }
            $address_phone_list = $this->getPhoneList($mobile_list);
            $resSSDB = [];
            foreach ($address_phone_list as $user_phone => $address_phones) {
                $addressList = $this->ChkPhone($address_phones,$user_phone);
                if ($addressList) {
                    $resSSDB[$user_phone] = json_encode($addressList);
                }
            }
            if(empty($resSSDB)){
                Logger::dayLog('cleanPhone','all addresslist is empty'.($startId.'-'.$endId));
                echo 'addresslist is empty';
                return false;
            }
            $successCount = Yii::$app->ssdb_address->multi_set($resSSDB);
            if(!$successCount){
                Logger::dayLog('cleanPhone',var_dump($resSSDB).'save fail');
                die(var_dump($resSSDB)." save fail");
            }
            Logger::dayLog('cleanPhone',"success_count:".$successCount.'; Ids:'.($startId.'-'.$endId));
        }catch (Exception $e){
            Logger::dayLog('cleanPhone','all address save fail，reason: '.$e->getMessage().'; Ids:'.($startId.'-'.$endId));
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

    private function getPhoneList($phone_num_list){
        $address_phone_list = self::$ssdbAddress->multi_get($phone_num_list);
        if (!$address_phone_list) {
            Logger::dayLog('syncPhone/address', 'address is empty',$phone_num_list);
            return [];
        }
        $relation_list = [];
        foreach ($address_phone_list as $user_phone => $phone_json) {
            if (empty($phone_json)) {
                continue;
            }
            $phone_list = json_decode($phone_json,true);
            $relation_list[$user_phone] = $this->phoneArrayUnique($phone_list);
        }
        return $relation_list;
    }

    private function phoneArrayUnique(&$phone_list){
        if (empty($phone_list)) {
            return [];
        }
        $phone_list_unique = array_keys(array_flip($phone_list));
        $phone_list_unique = array_map(function($phone) {
                            return (string)$phone;
                        },$phone_list_unique);
        return $phone_list_unique;
    }
}