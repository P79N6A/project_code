<?php
/**
 *  定时同步通讯录数据yi_address_list信息到mycat里
 */
namespace app\commands;
use app\common\Logger;
use Yii;
use app\models\mycat\MycatApi;
set_time_limit(0);
// */1 * * * * /usr/local/bin/php /data/wwwroot/cloud/yii sync-address runAddress >/dev/null 2>&1
class MinsyncAddressController extends BaseController 
{   
    private static $db_yiyiyuan;
    private static $db_analysis_repertory;
    private static $db_tidb;
    private static $db_write_yyy;

    const INTERVAL = 5000;

    const PART = 1000;

    public function init()
    {
        self::$db_tidb = Yii::$app->db_tidb;
        self::$db_yiyiyuan = Yii::$app->db_yiyiyuan;
        self::$db_write_yyy = Yii::$app->db_write_yyy;
        self::$db_analysis_repertory = Yii::$app->db_analysis_repertory;
    }


    private function runOne($startId,$endId)
    {
        $start_time = $this->microtime_float();
        $selectSql = "select id,aid,user_id,user_phone,phone,name,modify_time,create_time from address_list where id >= '".$startId."' and id <'".$endId."'";
        $command = self::$db_yiyiyuan->createCommand($selectSql);
        $addressList = $command->queryAll();
        if (empty($addressList)){
            Logger::dayLog('SyncAddress','no data',$startId,$endId);
            return false;
        }
        $diff_time_start = $this->microtime_float();
        $oMycatApi = new MycatApi();
        $newAddressList = $oMycatApi->chkAddressList($addressList);
        if (empty($newAddressList)) {
            Logger::dayLog('SyncAddress','newAddressList is empty',$startId,$endId);
            return false;
        }
        $diff_time_end = $this->microtime_float();
        // $insertStr = '';
        $insertTidbStr = '';
        foreach ($newAddressList as $address){
            $id = (int)$address['id'];
            $aid = (int)$address['aid'];
            $userId = (int)$address['user_id'];
            $phone = addslashes(trim($address['phone']));
            $name = addslashes(trim($address['name']));
            $userPhone = addslashes(trim($address['user_phone']));
            $modifyTime = $address['modify_time'];
            $createTime = $address['create_time'];
            // $insertStr = $insertStr. ",('" . $id . "','" . $aid . "','" . $userId . "','" . $userPhone . "','" . $phone . "','" . $name . "','" . $modifyTime . "','" . $createTime . "')";
            $insertTidbStr = $insertTidbStr. ",('" . $aid . "','" . $userId . "','" . $userPhone . "','" . $phone . "','" . $name . "','" . $modifyTime . "','" . $createTime . "')";
        }

        // $insertSql = 'insert into address_list (`id`,`aid`,`user_id`,`user_phone`,`phone`,`name`,`modify_time`,`create_time`) values'. trim($insertStr,',');
        // $commandInsert = self::$db_analysis_repertory->createCommand($insertSql);
        // $ok = $commandInsert->execute();

        // $insertSql2 = 'insert into reverse_address_list (`id`,`aid`,`user_id`,`user_phone`,`phone`,`name`,`modify_time`,`create_time`) values'. trim($insertStr,',');
        // $commandInsert2 = self::$db_analysis_repertory->createCommand($insertSql2);
        // $ok2 = $commandInsert2->execute();

        # insert TIDB
        $insertSql3 = 'insert into address_list (`aid`,`user_id`,`user_phone`,`phone`,`name`,`modify_time`,`create_time`) values'. trim($insertTidbStr,',');
        $commandInsert3 = self::$db_tidb->createCommand($insertSql3);
        $ok3 = $commandInsert3->execute();
        $end_time = $this->microtime_float();
        // delete
        $time = date('Y-m-d H:i:s', strtotime('-3 day'));
        $deleteSql = "DELETE FROM address_list WHERE create_time <='".$time."' LIMIT 2000";
        $command = self::$db_write_yyy->createCommand($deleteSql);
        $del_ok = $command->execute();
        echo "delete num is ".$del_ok.PHP_EOL;
        return $ok3;
    }

    /**
     * @desc  
     * @param $startId 
     * @param $endId 
     */
    public function runAddress($startId = null, $endId = null) 
    {
        $starttime = explode(' ',microtime());
        $startFile = Yii::$app ->basePath . '/commands/rundata/addressId.txt';
        if (!$startId){
            $startId = file_get_contents($startFile);
        }
        if(!$startId){
            Logger::dayLog('SyncAddress','no start_id',$startId);
            return false;
        }
        if (!$endId){
            $endId = $startId + self::INTERVAL;
        }
        $maxIdSql = "select max(id) as max_id from address_list";
        $maxIdCommand = self::$db_yiyiyuan->createCommand($maxIdSql);
        $maxId = $maxIdCommand ->queryOne();
        $maxId = $maxId['max_id'];
        if ($endId > $maxId){
            $endId = $maxId;
        }
        $resFile = file_put_contents($startFile,$endId);
        if(!$resFile){
            Logger::dayLog('SyncAddress','file_put_contents is fail',$endId);
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
            Logger::dayLog('SyncAddress','runOne result',$res,$sId,$eId,$i);
            $step += self::PART;
            $i++;
        }

        $endtime1 = explode(' ',microtime());
        $thistime1 = $endtime1[0]+$endtime1[1]-($starttime[0]+$starttime[1]);
        $thistime1 = round($thistime1,3);
        echo "本次脚本执行耗时：".$thistime1." 秒\n";
    }


    function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }
}