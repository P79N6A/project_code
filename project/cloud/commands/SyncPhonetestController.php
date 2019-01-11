<?php
/**
 *  定时同步cloud线下库更新快的数据信息到ssdb里
 */
namespace app\commands;

use Yii;
use app\models\yyy\YiUser;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use app\models\xs\SyncIdList;
use app\models\down\PhoneNumList;
use app\models\down\PhoneRelationList;

/**
 * 同步平台手机号
 * 本地测试：/usr/local/php7/bin/php /data/wwwroot/cloud/yii  sync-phonetest runPhone
 * D:\phpstudy\php55\php.exe  D:\phpstudy\WWW\cloud_ssdb\yii sync-phonetest runphone
 */
class SyncPhonetestController extends BaseController
{   
    // private static $detail_type = '1';
    // private static $address_type = '2';
    private static $sync_type = 'sync_phone';
    private static $oYiUser;
    private static $edge_path;
    private static $vertex_path;
    private static $db_down; # 线下库
    private static $oSyncId = null;
    private static $allRun = 10000;
    private $file_path;
    private $vertex_field;
    private $edge_field;
    private static $step = 100;
    
    public function init()
    {
        self::$oYiUser = new YiUser();
        self::$db_down = Yii::$app->db;
        $this->file_path = Yii::$app->basePath . '/../dgraph_data/';
        $this->vertex_field = 'phone';
        $this->edge_field = 'user_phone,phone';
        self::$edge_path = $this->getCheckPath('edge_all');
        self::$vertex_path = $this->getCheckPath('vertex_all');
    }

    public function runPhone()
    {   
        $time1 = explode(' ',microtime());
        $sync_id = $this->getSyncId(self::$sync_type);
        if(!$sync_id){
            Logger::dayLog('syncPhone/setId','no sync_id',$sync_id);
            return [];
        } 
        $chuckData = $this->stepData($sync_id['startId'],$sync_id['endId']);
        // $startId = ArrayHelper::getValue($sync_id,'startId',0);
        // $endId = ArrayHelper::getValue($sync_id,'endId',0);
        foreach ($chuckData as $key => $sunWorkerData) {  
            $start = $sunWorkerData['start_id'];  
            $end = $sunWorkerData['end_id'];  
            echo $start .",".$end."\n";
            $this->runOne($start,$end);
        }
        if (SYSTEM_PROD) {
            self::$oSyncId->sync_status = SyncIdList::SYNC_SUCCESS;
            self::$oSyncId->modify_time = date('Y-m-d H:i:s');
            self::$oSyncId->save();
            $set_res = $this->setLastIds($sync_id);
        }
        $time2 = explode(' ',microtime());
        $thistime1 = $time2[0]+$time2[1]-($time1[0]+$time1[1]);
        Logger::dayLog('syncPhone/runPhone','runPhone use_time:', $thistime1);
        echo "use time : ".$thistime1;
        return true;
    }

    private function setLastIds($sync_id){
        $start_id = ArrayHelper::getValue($sync_id,'endId',0);
        $end_id = $start_id + self::$allRun;
        $maxId = self::$oYiUser->getMaxId();
        if ($end_id > $maxId){
            $end_id = $maxId;
        }
        $sava_data = [
            'start_id' => $start_id,
            'end_id' => $end_id,
            'sync_status' => SyncIdList::SYNC_INIT,
            'sync_type' => self::$sync_type,
        ];
        $save_res = (new SyncIdList)->saveData($sava_data);
        return $save_res;
    }
    private function getSyncId($sync_type) {
        $where = [
            'sync_type' => $sync_type,
            'sync_status' => SyncIdList::SYNC_INIT,
            ];
        self::$oSyncId = (new SyncIdList)->getOne($where);
        if (empty(self::$oSyncId)) {
            return [];
        }
        if (SYSTEM_PROD) {
            self::$oSyncId->sync_status = SyncIdList::SYNC_DOING;
            self::$oSyncId->modify_time = date('Y-m-d H:i:s');
            self::$oSyncId->save();
        }
        return [
            'startId' => ArrayHelper::getValue(self::$oSyncId,'start_id',0),
            'endId' => ArrayHelper::getValue(self::$oSyncId,'end_id',0),
        ];
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
        # get address phone 二维数组
        $time1 = explode(' ',microtime());
        $address_phone_list = $this->getPhoneList($mobile_list);
        $time2 = explode(' ',microtime());
        $thistime1 = $time2[0]+$time2[1]-($time1[0]+$time1[1]);
        Logger::dayLog('syncPhone/time','getPhoneList use_time:', $thistime1);
        # set phone_num_list
        $set_phone_num_list_res = $this->setPhoneNum($address_phone_list);
        $time3 = explode(' ',microtime());
        $thistime2 = $time3[0]+$time3[1]-($time2[0]+$time2[1]);
        Logger::dayLog('syncPhone/time','setPhoneNum use_time:', $thistime2);
        # set address_relation list 
        $set_address_relation_res = $this->setRelationList($address_phone_list);
        $time4 = explode(' ',microtime());
        $thistime3 = $time4[0]+$time4[1]-($time3[0]+$time3[1]);
        Logger::dayLog('syncPhone/time','setRelationList use_time:', $thistime3);
        Logger::dayLog("syncPhone/success", " set_phone_num_list_res  is ".$set_phone_num_list_res,'set_address_relation_res is '.$set_address_relation_res,'startId :'.$startId,'startId:'.$endId);    
        return true;

    }
    private function setRelationList(&$complex_phone_list){
        $path = self::$edge_path;
        $fp = fopen($path,'a');
        clearstatcache(); # 清除缓存
        if (file_exists($path) && filesize($path) == 0) {
            $head = $this->edge_field.PHP_EOL;
            fwrite($fp, $head);
        }
        $relation_list = [];
        foreach ($complex_phone_list as $user_phone => $phone_list) {
            if (empty($phone_list)) {
                continue;
            }
            foreach ($phone_list as $phone) {
                if (!$phone_list) {
                    continue;
                }
                if ($user_phone == $phone) {
                    continue;
                }
                $relation_list[] = ['user_phone' => $user_phone,'phone' => $phone];
            } 
        }
        if (empty($relation_list)) {
            return 0;
        }
        $all_count  = count($relation_list);
        $save_count  = 0;
        $save_csv_list = array_chunk($relation_list,5000);
        foreach ($save_csv_list as $save_csv) {
            $csv_content = '';
            foreach ($save_csv as $field_date) {
                $csv_content .= implode(',',$field_date).PHP_EOL;
                $save_count++;
            }
            fwrite($fp, $csv_content);
        }
        fclose($fp);
        Logger::dayLog('syncPhone/setRelationList','all_count',$all_count,'save_count', $save_count);
        return $save_count;
    }
    private function getCheckPath($type){
        $time = date("Ymd/");
        # check dirname
        $dirname = $this->file_path.$type.'/'.$time;
        if (!is_dir($dirname)) {
            mkdir($dirname,0777,true);
        }
        # 获取最新的文件
        $path = $dirname.date("Hi").'.csv';
        if (!file_exists($path)) {
            touch($path);
            chmod($path,0777);
        }
        return $path;
    }
    private function addAllPhoneRelation($phone_list,$user_phone,$type){
        try {
            $save_list = [];
            foreach ($phone_list as $phone) {
                $save_list[] = [
                    'user_phone' => $user_phone,
                    'phone' => $phone,
                    'type' => $type,
                ]; 
            }
            //数据批量入库  
            $res = self::$db_down->createCommand()->batchInsert(  
                'phone_relation_list',  
                ['user_phone','phone','type'],//字段  
                $save_list  
            );
            $sql = $res->getRawSql();
            $num = $res->execute();
            return $num;
        } catch (Exception $e) {
            Logger::dayLog('syncPhone/error', 'add_error',$e->getMessage());
            return 0;
        }
        
    }
    private function setPhoneNum(&$complex_phone_list){
        $path = self::$vertex_path;
        $fp = fopen($path,'a');
        clearstatcache(); # 清除缓存
        if (file_exists($path) && filesize($path) == 0) {
            $head = $this->vertex_field.PHP_EOL;
            fwrite($fp, $head);
        }
        $save_count = 0;
        # set unique phone 
        $time1 = explode(' ',microtime());
        $all_phone_list = $this->mergeSonList($complex_phone_list);
        $time2 = explode(' ',microtime());
        $thistime1 = $time2[0]+$time2[1]-($time1[0]+$time1[1]);
        Logger::dayLog('syncPhone/time','getPhoneList use_time:', $thistime1); 
        # addAllPhoneNum
        if ($all_phone_list) {
            $chunk_phone_list = array_chunk($all_phone_list,5000);
            foreach ($chunk_phone_list as $save_array) {
                $csv_content = '';
                foreach ($save_array as $field_date) {
                    $csv_content .= $field_date.PHP_EOL;
                    $save_count++;
                }
                fwrite($fp, $csv_content);
            }
        }
        fclose($fp);
        $time3 = explode(' ',microtime());
        $thistime2 = $time3[0]+$time3[1]-($time2[0]+$time2[1]);
        Logger::dayLog('syncPhone/time','getNotExistPhone use_time:', $thistime2,$save_count);
        return $save_count;
    }
    private function mergeSonList(&$complex_phone_list){
        $all_phone_list = [];
        #合并
        #用户手机号
        $all_phone_list = array_keys($complex_phone_list);
        foreach ($complex_phone_list as $phone_num_list) {
            $all_phone_list = array_merge($all_phone_list,$phone_num_list);
        }
        # 去重
        $all_phone_list  = $this->phoneArrayUnique($all_phone_list);
        return $all_phone_list;
    }
    private function getNotExistPhone($all_phone_list){
        $oPhoneNumList = new PhoneNumList();
        $exist_phone = $oPhoneNumList->getAllByPhones($all_phone_list);
        if (!$exist_phone) {
            return $all_phone_list;
        }
        $exist_phone_list = ArrayHelper::getColumn($exist_phone,'phone',[]);
        $not_exist_phone_list = array_diff($all_phone_list,$exist_phone_list);
        return $not_exist_phone_list;
    }
    private function getPhoneList($phone_num_list){
            $address_phone_list = Yii::$app->ssdb_address->multi_get($phone_num_list);
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
    private function addAllPhoneNum($phone_num_list)
    {   
        try {
            //数据批量入库  
            $res = self::$db_down->createCommand()->batchInsert(  
                'vertex_phone_temp',  
                ['phone_end','phone','create_time'],//字段  
                $phone_num_list  
            );
            $sql = $res->getRawSql();
            $num = $res->execute();
            return $num;
        } catch (\Exception $e) {
            Logger::dayLog('syncPhone/error', 'add_error',$e->getMessage());
            return 0;
        }
        
    } 

    private function saveOne($phone){
        $sql = "insert into phone_num_list (`phone`) values ('".$phone."')";
        $res = self::$db_down->createCommand($sql)->execute();
        return $res;
    }
}