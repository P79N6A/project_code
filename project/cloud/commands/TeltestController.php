<?php
/**
 * 号码标签
 */

namespace app\commands;

use Yii;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use app\models\phonelab\PhoneFactory;
use app\models\phonelab\TelLabChannel;
use app\models\mycat\MyPhoneTagList;
use app\models\anti\AfTagBase;
use app\models\mycat\MycatApi;
use app\models\phonelab\DcTellabRecord;

/**
 * 号码标签更新接口
 * 本地测试：/usr/local/bin/php /data/wwwroot/test/cloud/yii  tellab tagtest
 */
class TeltestController extends BaseController
{
    private $oTelLabRept;
    private $file_path;
    private $one_data_num;
    private $query_times;
    private static $db_yyy;
    protected static $phoneHandler = null;

    public function init() {
        $this->oTelLabRept = new MycatApi();
        $this->one_data_num = 4000000;
        $this->query_times = 0;
        self::$db_yyy = Yii::$app->db_yiyiyuan;
        $this->file_path = Yii::$app->basePath . '/commands/data/';
    }
    # 读取CSV
    private function readCsv($key)
    {
        $path_arr = ['1'=>'a.csv','2'=>'b.csv','3'=> 'c.csv','4'=>'d.csv'];
        $path = $path_arr[$key];
        $file_path = $this->file_path.$path;
        $file = fopen($file_path,'r');
        $n = 0;
        $value = [];
        while ($data = fgetcsv($file)) { //每次读取CSV里面的一行内容
            if ($n === 0) {
                $key = $data;
            } else {
                $arr = [
                    $key['0'] => $data['0'],
                    $key['1'] => $data['1'],
                    // $key['2'] => str_replace('/', '-', $data['2']),
                ];
                $value[] = $arr;
            }
            $n++;
        }
        $count = count($value);
        return $value;
    }
    // 号码标签
    public function runTag($key = 1)
    {   
        # 检查当日次数是否大于规定次数
        $chack_times = $this->checkTimes();
        if (!$chack_times) {
            Logger::dayLog('tellab/runTag', 'Excessive number of requests');
            die('Excessive number of requests');
        }
        # 获取需要跑标签的用户
        $tagBaseList = $this->readCsv($key);
        if (empty($tagBaseList)) {
            Logger::dayLog('tellab/runTag', 'nothing to deal with');
            die('nothing to deal with');
        }
        $user_list = ArrayHelper::getColumn($tagBaseList,'user_id');
        $user_str = implode("','", $user_list);
        # get user_phone
        $selectSql = "select user_id,mobile from yi_user where user_id IN('".$user_str."')";
        $command = self::$db_yyy->createCommand($selectSql);
        $userList = $command->queryAll();
        // # 获取查询过第三方号码标签的用户 
        // $query_phone = $this->getQueryPhone($phone_list);
        // $mytime= date("Y-m-d H:i:s", strtotime("-1 month")); 
        # 请求标签
        $path = $this->file_path.'phonetag_'.$key.'.csv';
        if (file_exists($path)) {
            unlink($path);
        }
        $fp = fopen($path,'a');
        $user_num= 0;
        $phone_num = 0;
        foreach ($userList as $user) {
            $user_phone = ArrayHelper::getValue($user,'mobile','');
            if (empty($user_phone)) {
                continue;
            }
            # 获取用户通讯录
            $phone_num_list = $this->getPhonelist($user_phone);
            foreach ($phone_num_list as $phone) {
                # 获取标签
                $new_tag = $this->queryApi($phone);
                $user['phone'] = $phone;
                $csv_arr = array_merge($user,$new_tag);
                // var_dump($csv_arr);die;
                // save
                $csv_content = implode(',', $csv_arr) . PHP_EOL;
                fwrite($fp, $csv_content);
                $phone_num++;
            }
            $user_num++;
        }
        Logger::dayLog('teltest', 'user_num ', $user_num,'phone_num ', $phone_num);
        return true;
    }
    //save record
    private function saveRecord($data)
    {
        $save_data = [
                'phone' => ArrayHelper::getValue($data,'phone',''),
                'aid' => ArrayHelper::getValue($data,'aid',''),
                'user_id' => ArrayHelper::getValue($data,'user_id',''),
            ];
        $records = new DcTellabRecord();
        $res = $records->saveData($save_data);
        return $res;
    }

    // update record
    private function updateRecord($data)
    {
        $phone = ArrayHelper::getValue($data,'phone','');
        if (empty($phone)) {
            return false;
        }
        $records = (new DcTellabRecord)->findOne(['phone'=>$phone]);
        if (empty($records)) {
            return false;
        }
        $records->last_query_time = date('Y-m-d H:i:s');
        $records->aid = ArrayHelper::getValue($data,'aid','');
        $records->user_id = ArrayHelper::getValue($data,'user_id','');
        $res = $records->save();
        return $res;
    }
    private function getQueryPhone($phones)
    {
        $where = ['in','phone',$phones];
        $field = 'phone,last_query_time';
        $records = (new DcTellabRecord)->getRecord($where,$field);
        if (empty($records)) {
            return [];
        }
        $records = array_column($records,'last_query_time','phone');
        return $records;
    }

    private function setTag(&$phone_num_list,$user_phone)
    {
        # 1、将用户按更新及新增分组
        $in_and_up_list = $this->oTelLabRept->checkPhoneTag($phone_num_list,$user_phone);
        # 2、更新
        $up_nums = $this->updateTag($in_and_up_list['up_list']);
        # 3、新增
        $in_nums = $this->insertTag($in_and_up_list['in_list']);

        return $in_nums+$up_nums;
    }
    // 获取号码列表
    private function getPhonelist($user_phone)
    {
        $detail_phone_arr = [];
        $addr_phone_arr = [];
        

        $detail_list = Yii::$app->ssdb_detail->get($user_phone);
        if (SYSTEM_PROD) {
            # 详单号码
            $detail_list = Yii::$app->ssdb_detail->get($user_phone);
            # 通讯录号码
            $address_list = Yii::$app->ssdb_address->get($user_phone);
        } else {
            $detail_list = Yii::$app->ssdb_detail->get($user_phone.'_detail');
            $address_list = Yii::$app->ssdb_address->get($user_phone.'_address');
        }
        if (!empty($detail_list)) {
            $detail_list = json_decode($detail_list,true);
            $detail_phone_arr = ArrayHelper::getValue($detail_list,'phoneArr','');
        }

        if (!empty($address_list)) {
            $addr_phone_arr = json_decode($address_list,true);
        }
        $all_num = $this->getArrayUnion($detail_phone_arr,$addr_phone_arr);
        $all_num[] = $user_phone;
        # 过滤号码
        $real_all_num = $this->checkNums($all_num);
        return $real_all_num;
    }
    // 详单及通讯录交集
    private function getArrayUnion($array_a,$array_b)
    {
        $array_union = array_merge($array_a,$array_b);
        $array_union = array_unique($array_union);
        $array_union = array_values($array_union);
        return $array_union;
    }
    // 检查号码
    private function checkNums($all_num)
    {
        $real_all_num = [];
        foreach ($all_num as $num) {
            $real_num = $this->numberRule($num);
            if (empty($real_num)) {
                continue;
            }
            $real_all_num[] = $real_num;
        }
        return $real_all_num;
    }

    // 检查号码
    private function numberRule($num)
    {
        $real_num = '';
        if (empty($num) || strlen($num) <= 5) {
            return $real_num;
        }
        if (substr($num, 0, 3) == '400') {
            return $real_num;
        }

        $real_num = $this->checkTel($num);
        if (!empty($real_num)) {
            return $real_num;
        }
        $real_num = $this->checkPhone($num);
        if (!empty($real_num)) {
            return $real_num;
        }
        return $real_num;
    }
    // 验证手机号
    private function checkPhone($number)
    {
        $isMatched = preg_match('/^1[2-9][0-9]\d{8}$/', $number, $matche_phone);
        if ($isMatched > 0) {
            return (string)$number;
        }
        return '';
    }
    // 验证电话号
    private function checkTel($number)
    {
        $isMatched = preg_match('/^0\d{2,3}-?\d{7,8}$/', $number, $matche_phone);
        if ($isMatched > 0) {
            return (string)$number;
        }
        return '';
    }
    // 检查当日次数是否大于规定次数
    private function checkTimes()
    {
        $all_tag_num = Yii::$app->ssdb_detail->get('all_tag_num');
        if ($all_tag_num >= $this->one_data_num) {
            return false;
        }
        return true;
    }

    private function updateTag($up_num_list)
    {
        if (empty($up_num_list)) {
            return 0;
        }
        $tag_list = [];
        foreach ($up_num_list as $key => $value) {
            # 请求接口
            $phone = ArrayHelper::getValue($value,'phone','');
            $new_tag = $this->queryApi($phone);
            if (empty($new_tag)){
                continue;
            }
            $old_tag = ArrayHelper::getValue($value,'tag_type','');
            $old_tag_arr = explode(',', $old_tag);
            # 比较标签是否需要更新
            $new_tag_list = ArrayHelper::getValue($new_tag,'tag',[]);
            if (empty($new_tag_list)) {
                continue;
            }
            $is_diff = array_diff($new_tag_list, $old_tag_arr);
            if (!$is_diff) {
                continue;
            }
            $all_tag = $this->getArrayUnion($new_tag_list, $old_tag_arr);
            $tag_list[$phone] = [
                'tag_type' => implode(',', $all_tag),
                'other_info' => ArrayHelper::getValue($new_tag,'other_info',''),
            ];
        }
        if (empty($tag_list)) {
            return 0;
        }
        $source = 18;
        $up_nums = $this->oTelLabRept->updateTagBatch($tag_list,$source);
        return $up_nums;
    }
    private function insertTag($in_num_list)
    {
        $in_list = [];
        $source = 18;
        $time = date('Y-m-d H:i:s');
        foreach ($in_num_list as $phone) {
            # 请求接口
            $new_tag = $this->queryApi($phone);
            if (empty($new_tag)){
                continue;
            }
            $new_tag_list = ArrayHelper::getValue($new_tag,'tag',[]);
            $new_tag_str = implode(',',$new_tag_list);
            $in_list[] = [
                    'phone' => $phone,
                    'tag_type' => $new_tag_str,
                    'source' => $source,
                    'modify_time' => $time,
                    'create_time' => $time,
                    'other_info' => ArrayHelper::getValue($new_tag,'other_info',''),
                ];
        }
        $in_nums = $this->oTelLabRept->insertTagBatch($in_list);
        return $in_nums;
    }

    private function queryApi($phone)
    {
        # 1、参数验证
        if (empty($phone)) {
            return [];
        }
        # 2、调用第三方接口
        $channels = $this ->getSupportChannel();
        $phone_tag_new = $this->getPhoneRoute($channels, $phone);
        $this->query_times++;
        if(empty($phone_tag_new)) {
            return [];
        }
        # 3、数据提取
        $tag_arr = ArrayHelper::getColumn($phone_tag_new,'tag','');
        $other_info = array_column($phone_tag_new, 'times', 'tag');
        if (empty($tag_arr)) {
            return [];
        }
        $tag_all = [
            'tag' => implode('/', $tag_arr),
            'other_info' => json_encode($other_info,JSON_UNESCAPED_UNICODE),
        ];
        return $tag_all;
    }
    private function getSupportChannel() 
    {
        $oPhoneChannel = new TelLabChannel();
        $channels = $oPhoneChannel->supportChannel();
        return $channels;
    }

    /**
     * @return mixed
     */
    private function getPhoneRoute($channels, $phone){
        if (!$channels) {
            return $this->returnMsg('1000002');
        }
        foreach ($channels as $key => $channel) {
            try {
                self::$phoneHandler = PhoneFactory::Create($channel);
                $res =  self::$phoneHandler->getPhoneInfo($phone);
                if(!empty($res)){
                    return $res;
                }
            } catch (\Exception $e) {
                Logger::dayLog('tellab', 'getphoneroute', $channel['name'], $phone, $e->getMessage());
            }
        }
        return [];
    }
}