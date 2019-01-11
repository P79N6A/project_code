<?php
/**
 *  定时同步cloud线下库更新快的数据信息到ssdb里
 */
namespace app\commands;

use Yii;
use app\models\jxl\JxlApi;
use app\models\jxl\JxlStat;
use app\common\Logger;
use app\models\sys\SyForeignLoan;
use yii\helpers\ArrayHelper;

/**
 * 同步贷后数据
 * 本地测试：/usr/local/bin/php /data/wwwroot/cloud/yii  sync-foreign runforeign
 */
class SyncForeignController extends BaseController
{   
    private static $db_cloud_new;
    private static $db_sysloan;
    private static $syForeignLoan;
    private static $allRun = 2000;
    private static $page = 100;
    private static $step = 100;

    public function init()
    {
        self::$db_cloud_new = Yii::$app->db_cloudnew;
        self::$db_sysloan = Yii::$app->db_sysloan;
        self::$syForeignLoan = new SyForeignLoan();
        $stuff = SYSTEM_PROD ? '' : '_test';

    }

    private function getSyncData()
    {
        $where = '';
        $data = self::$syForeignLoan->getForeignLoan($where);
    }

    public function runForeign($startId = null, $endId = null)
    {   
        $id = $this->setId($startId,$endId);
        if (empty($id)) {
            Logger::dayLog('syncForeign/runForeign','Id is null',$startId,$endId);
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
            Logger::dayLog('syncForeign/runForeign','runOne use_time:', $thistime1);
            echo "use time : ".$thistime1;
        }
        return true;
    }
    private function setId($startId, $endId)
    {
        if (!$startId){
            $startFile = Yii::$app ->basePath . '/commands/data/foreign_id.txt';
            if(file_exists($startFile)){
                try {
                    $startId = file_get_contents($startFile);
                } catch (Exception $e) {
                    return [];
                }
            }
        }
        
        if(!$startId){
            Logger::dayLog('syncForeign/setId','no start_id',$startId);
            return [];
        }
        if (!$endId){
            $endId = $startId + self::$allRun;
        }
        $maxId = self::$syForeignLoan->getMaxId();
        self::$db_sysloan->close();
        if ($endId > $maxId){
            $endId = $maxId + 1;
        }
        if (!isset($startFile)) {
            $startFile = Yii::$app ->basePath . '/commands/data/foreign_id.txt';
        }
        $resFile = file_put_contents($startFile,$endId);
        if(!$resFile){
            Logger::dayLog('syncForeign/setId','file_put_contents is fail',$endId);
            return [];
        }
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

    private function runOne($startId,$endId)
    {   
        // try{
        $where = ['and',
            ['>=','id',$startId],
            ['<','id',$endId],
        ];
        $field = 'mobile,identity';
        $dataList = self::$syForeignLoan->getForeignLoan($where,$field);
        self::$db_sysloan->close();
        if (empty($dataList)){
            Logger::dayLog("syncForeign/runOne", "no data to dealwith",$startId,$endId);
            echo 'dataList is empty';
            return false;
        }
        //组合数据
        $phone_list = ArrayHelper::getColumn($dataList, function ($element) {
            $time = date('Y-m-d H:i:s');
            $data = [
                'match_status' => 1,
                'modify_time' => $time,
                'create_time' => $time,
                'phone'=>$element['mobile'],
            ];
            return $data;
        });
        // $phone_list = array_map(['SyncForeignController','merge_data'], )

        $idcard_list = ArrayHelper::getColumn($dataList, function ($element) {
            $time = date('Y-m-d H:i:s');
            if (empty($element['identity'])) {
                return false;
            }
            $data = [
                'match_status' => 1,
                'modify_time' => $time,
                'create_time' => $time,
                'idcard'=>$element['identity'],
            ];
            return $data;
        });
        $idcard_list = array_filter($idcard_list);
        $phone_count = 0;
        // save phone 
        if ($phone_list) {
            $phone_count = $this->add_all($phone_list,'phone');
        }
        
        // save idcard 
        $idcard_count = 0;
        if ($idcard_list) {
            $idcard_count = $this->add_all($idcard_list,'idcard');
        }
        
        $n = $idcard_count+$phone_count;
        Logger::dayLog("syncForeign/success", " success num is ".$n,$startId,$endId);
        return $n;
        // } catch (\Exception $e) {
        //  Logger::dayLog('syncForeign/sync_error', 'sync_error',$e->getMessage(),$startId);
        //  return 0;
        // }
    }

    private function add_all($add,$table)
    {   
        $tableName = 'dc_foreign_black_idcard';
        $type = 'idcard';
        if ($table == 'phone') {
            $type = 'phone';
            $tableName = 'dc_foreign_black_phone';
        }
        //数据批量入库  
        $res = self::$db_cloud_new->createCommand()->batchInsert(  
            $tableName,  
            ['match_status','modify_time','create_time',$type],//字段  
            $add  
        );
        $sql = $res->getRawSql();
        Logger::dayLog('sql','add_all',$sql);
        $res = $res->execute(); 
        return $res;
    } 
}