<?php
/**
 * 号码标签
 */

namespace app\commands;

use Yii;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use app\models\phonelab\PhonelebApi;
use app\models\phonelab\common\ChkPhone;
use app\models\mycat\MyPhoneTagList;
use app\models\tidb\TiPhoneTagList;
use app\models\tidb\TidbApi;
use app\models\anti\PhoneTagList;
use app\models\anti\AfTagBase;
use app\models\mycat\MycatApi;
use app\models\phonelab\DcTellabRecord;

/**
 * 号码标签更新接口
 * 本地测试：/usr/local/bin/php /data/wwwroot/test/cloud/yii  query-api runapi
 */
class QueryApiController extends BaseController
{
    private $oTelLabRept;
    private $file_path;
    private $one_data_num;
    private $chkPhoneApi;
    private $phonelebApi;
    private $worker = 1;
    private $query_times;
    private $no_tag_list;
    private $oTidbApi;

    public function init() {
        $this->oTelLabRept = new MycatApi();
        $this->oTidbApi = new TidbApi();
        $this->chkPhoneApi = new ChkPhone();
        $this->phonelebApi = new PhonelebApi();
        $this->one_data_num = 4000000;
        $this->no_tag_list = [];
    }
    // 请求号码标签
    public function runApi($limit = 3000)
    {   
        $starttime = explode(' ',microtime());
        //1 获取初始数据 
        $time = date("Y-m-d", strtotime("-3 day"));
        $tag_where = ['and',['status'=>0],['>=','create_time',$time]];
        $tag_select = 'id,phone,tag_type,source,type';
        $phone_tag_db = new PhoneTagList();
        $tagInfoList = $phone_tag_db->getTagInfo($tag_where,$limit,$tag_select);
        if (empty($tagInfoList)){
            Logger::dayLog('queryapi','no data');
            echo "no data to sync","\n";
            return false;
        }
        //2 锁定状态,避免下次重复处理
        $lock_ids = ArrayHelper::getColumn($tagInfoList, 'id');
        $lockNums = $phone_tag_db->lockStatus($lock_ids,'1');
        if ($lockNums == 0){
            Logger::dayLog('queryapi','lockNums is 0');
            return false;
        }
        //3 整理数据 并分组
        $dataArr = ArrayHelper::map($tagInfoList, 'phone', 'tag_type', 'type');
        $in_res = 0;
        $up_res = 0;
        $up_list = ArrayHelper::getValue($dataArr,2,[]);
        $in_list = ArrayHelper::getValue($dataArr,1,[]);
        $allNums = 0;
        if (!empty($up_list)) {
            // 批量
            $up_res = $this->updateTag($up_list);
            Logger::dayLog('queryapi','upNum is '.$up_res);
        }
        if (!empty($in_list)) {
            // 逐条
            $in_res = $this->insertTag($in_list);
            Logger::dayLog('queryapi','insertNum is '.$in_res);
        }
        $allNums = $in_res+$up_res;
        echo "allNums is : ".$allNums,"\n";
        //4 更新完成
        $phone_tag_db = new PhoneTagList();
        $lockNums = $phone_tag_db->lockStatus($lock_ids,'2');
        Logger::dayLog('queryapi','lockNums is '.$lockNums);
        //5 更新无标签数据
        // if (!empty($this->no_tag_list)) {
        //     $lockNums = $phone_tag_db->lockStatusByPhone($this->no_tag_list,'13');
        // }
        $endtime1 = explode(' ',microtime());
        $thistime1 = $endtime1[0]+$endtime1[1]-($starttime[0]+$starttime[1]);
        $thistime1 = round($thistime1,3);
        echo "use_time：".$thistime1." S\n";
        Logger::dayLog('queryapi/time','use_time： is '.$thistime1,$allNums);
        return $allNums;
    }

    public function updateTag($in_num_list)
    {
        if (empty($in_num_list)) {
            return 0;
        }
        $this->query_times =count($in_num_list);
        $tag_list = [];
        foreach ($in_num_list as $phone => $old_tag) {
            # 请求接口
            $new_tag = $this->phonelebApi->queryApi($phone);
            if (empty($new_tag)){
                // $this->no_tag_list[] = $phone;
                continue;
            }
            $old_tag_arr = explode(',', $old_tag);
            # 比较标签是否需要更新
            $new_tag_list = ArrayHelper::getValue($new_tag,'tag',[]);
            if (empty($new_tag_list)) {
                // $this->no_tag_list[] = $phone;
                continue;
            }
            $is_diff = array_diff($new_tag_list, $old_tag_arr);
            if (!$is_diff) {
                // $this->no_tag_list[] = $phone;
                continue;
            }
            $all_tag = $this->oTelLabRept->getArrayUnion($new_tag_list, $old_tag_arr);
            $tag_list[$phone] = [
                'tag_type' => implode(',', $all_tag),
                'other_info' => ArrayHelper::getValue($new_tag,'other_info',''),
            ];
        }
        if (empty($tag_list)) {
            return 0;
        }
        $source = $this->phonelebApi->source;
        // $up_nums = $this->oTelLabRept->updateTagBatch($tag_list,$source);
        # update tidb
        $up_nums = $this->oTidbApi->updateTagBatch($tag_list,$source);
        return $up_nums;
    }

    public function insertTag($in_num_list)
    {
        $num = 0;
        if (empty($in_num_list)) {
            return $num;
        }
        $this->query_times = $this->query_times + count($in_num_list);
        // 检查是否存在标签
        $in_num_list = $this->chkInsertTag($in_num_list);
        $tag_list = [];
        foreach ($in_num_list as $phone) {
            # 请求接口
            $new_tag = $this->phonelebApi->queryApi($phone);
            if (empty($new_tag)){
                // $this->no_tag_list[] = $phone;
                continue;
            }
            $new_tag_list = ArrayHelper::getValue($new_tag,'tag',[]);
            if (empty($new_tag_list)) {
                // $this->no_tag_list[] = $phone;
                continue;
            }
            $in_tag = [
                'phone' => (string)$phone,
                'source'=> $this->phonelebApi->source,
                'tag_type' => implode(',', $new_tag_list),
                'other_info' => ArrayHelper::getValue($new_tag,'other_info',''),
            ];
            $res = false;
            // try {
            //     $res = (new MyPhoneTagList())->saveData($in_tag);
            // } catch (\Exception $e) {
            //     Logger::dayLog('queryapi/insertTag','save fail',$e->getMessage());
            // }
            try {
                # save tidb
                $res = (new TiPhoneTagList())->saveData($in_tag);
            } catch (\Exception $e) {
                Logger::dayLog('queryapi/insertTidb','save fail',$e->getMessage());
            }
            if ($res) {
                $num++;
            }
        }
        return $num;
    }

    private function chkInsertTag($phone_list)
    {
        $phone_list = array_keys($phone_list);
        $exist_info = $this->oTelLabRept->getExistTag($phone_list);
        if (empty($exist_info)) {
            return $phone_list;
        }
        $exist_phones = ArrayHelper::getColumn($exist_info,'phone');
        $insert_list = array_diff($phone_list, $exist_phones);
        return $insert_list;
    }
}