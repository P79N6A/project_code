<?php

namespace app\models\mycat;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Curl;
use app\common\Logger;
use app\common\ArrayGroupBy;
use app\models\phonelab\PhonelebApi;
use app\models\anti\PhoneTagList;

/**
 * Mycat 统一对外开放接口
 */
class MycatApi
{
    private $db;
    private static $source;
    private $phonelebApi;
    private $in_list = [];
    public $query_times;
    private $db_anti;
    private $db_tidb;

    public function __construct()
    {
        $this->db_tidb = Yii::$app->db_tidb;
        $this->db = Yii::$app->db_analysis_repertory;
        $this->db_anti = Yii::$app->db_anti;
        $this->phonelebApi = new PhonelebApi();
    }


    public function checkPhoneTag($phone_num_list,$user_phone)
    {
        $in_and_up_list = ['in_list'=>$phone_num_list,'up_list'=>[]];;
        if (empty($phone_num_list)) {
            Logger::dayLog('mycatapi/checkPhoneTag', 'phone_list is empty',$user_phone);
            return $in_and_up_list;
        }
        try {
            $exist_info = $this->getExistTag($phone_num_list);
        } catch (Exception $e) {
            Logger::dayLog('mycatapi/getExistTag', 'getExistTag is fail',$e->getMessage(),$user_phone);
            return $in_and_up_list;
        }
        if (empty($exist_info)) {
            // Logger::dayLog('mycatapi/getExistTag', 'exist_info is empty',$user_phone,$phone_num_list);
            return $in_and_up_list;
        }
        // set in_list and up_list
        $exist_phones = ArrayHelper::getColumn($exist_info,'phone');
        $in_and_up_list['in_list'] = array_diff($phone_num_list,$exist_phones);
        $in_and_up_list['up_list'] = $this->setUpList($exist_info);
        return $in_and_up_list;
    }

    public function getExistTag($phone_list)
    {
        if(empty($phone_list)){
            return null;
        }
        $phone_str = implode("','", $phone_list); 
        // $sql = "SELECT `phone`,`tag_type` FROM `tag_info_list` WHERE `phone` IN('".$phone_str."')";
        $sql = "SELECT `phone`,`tag_type`,`modify_time` FROM `phone_tag_list` WHERE `phone` IN('".$phone_str."')";
        // Logger::dayLog('sql',$sql);
        $command = $this->db_tidb->createCommand($sql);
        $phone_tag = $command->queryAll();
        if (empty($phone_tag)) {
            Logger::dayLog('mycatapi/getExistTag', 'phone_tag is empty',$sql,$phone_list);
        }
        $this->db_tidb->close();
        return $phone_tag;
    }

    // 更新数据
    private function setUpList($exist_info)
    {   
        if (empty($exist_info)) {
            return [];
        }
        $up_list = [];
        // try {
        foreach ($exist_info as $key => $value) {
            $mytime= date("Y-m-d H:i:s", strtotime("-1 month")); 
            // 一个月内的标签信息不更新
            if ($value['modify_time'] > $mytime) {
                continue;
            }
            $up_list[] = [
                    'phone'=>  ArrayHelper::getValue($value,'phone',''),
                    'tag_type' =>  ArrayHelper::getValue($value,'tag_type',''),
                ];
        }
        return $up_list;
    }

    // 保存tag
    private function saveTag($phone, $tag_str, $phone_tag)
    {
        $other_info = array_column($phone_tag, 'times', 'tag');
        $save_data = [
            'phone' => $phone,
            'source' => MyPhoneTagList::PINGAN_SOURCE,
            'tag_type' => $tag_str,
            'other_info' => json_encode($other_info,JSON_UNESCAPED_UNICODE),
        ];
        $save_res = (new MyPhoneTagList)->saveData($save_data);
        return $save_res;
    }

    // 更新tag
    public function updateTagBatch($tag_list, $source)
    {
        try {
        if (empty($tag_list)) {
            Logger::dayLog('mycatapi/saveTag', 'update_tag_list is empty',json_encode($tag_list));
            return 0;
        }
        $time = date('Y-m-d H:i:s');
        $type = 2;
        $status = 0;
        $phones = implode("','", array_keys($tag_list)); 
        $sql = "UPDATE phone_tag_list SET tag_type = CASE phone ";
        $other_sql = '';
        foreach ($tag_list as $phone => $value) { 
            $tag = ArrayHelper::getValue($value,'tag_type','');
            if (empty($tag)) {
                continue;
            }
            $other_info = ArrayHelper::getValue($value,'other_info','');
            $phone = addslashes(trim($phone));
            $tag_type = addslashes(trim($tag));
            $sql .= sprintf("WHEN '%s' THEN '%s' ", $phone, $tag_type);
            $other_sql .= sprintf("WHEN '%s' THEN '%s' ", $phone, $other_info);
        } 

        if ($other_sql) {
            $sql .= "END, other_info = CASE phone ".$other_sql." END, source = '".$source."', modify_time = '".$time."' WHERE phone IN ('$phones')";
        } else {
            $sql .= "END , source = '".$source."', modify_time = '".$time."' WHERE phone IN ('$phones')";
        }
        $commandInsert = $this->db->createCommand($sql);
        $ok = $commandInsert->execute();
        $this->db->close();
        if ($ok == 0) {
            Logger::dayLog('mycatapi/upsql', "update sql is:".$sql);
        }
        Logger::dayLog('mycatapi/upTag', "update success_count:".$ok);
        echo "update success_count:".$ok, "\n" ;
        return $ok;
        } catch (\Exception $e) {
            Logger::dayLog('mycatapi/error', 'update error',$e->getMessage(),json_encode($tag_list));
            return 0;
        }
    }

    public function insertTagBatch($tag_list)
    {   
        if (empty($tag_list)) {
            Logger::dayLog('mycatapi/insertTagBatch', 'in_tag_list is empty',json_encode($tag_list));
            return 0;
        }
        //数据批量入库  
        $res = $this->db->createCommand()->batchInsert(  
            'phone_tag_list',  
            ['phone','tag_type','source','modify_time','create_time','other_info'],//字段  
            $tag_list
        );
        $sql = $res->getRawSql();
        $res = $res->execute();
        if ($res == 0) {
            Logger::dayLog('mycatapi/insertTagBatch',$sql);
        }
        Logger::dayLog('mycatapi/insertTagBatch','insert num is : ',$res); 
        return $res;
    }  
    # 删除用户一个月前所有已同步数据
    public function delateUser($user_phone)
    {
        $mytime= date("Y-m-d H:i:s", strtotime("-1 month"));
        $model =  new PhoneTagList();
        $res = $model->deleteAll(['and','user_phone'=>$user_phone,['<','modify_time',$mytime]]);
        return $res;
    }

    # 删除用户一天前所有已同步号码
    public function deletePhone()
    {
        $mytime= date("Y-m-d", strtotime("-1 day"));
        $del_sql = 'DELETE FROM tag_info_list WHERE status IN(2,13) AND modify_time <= "'.$mytime.'" ORDER BY `id` LIMIT 15000';
        $commandDelete = $this->db_anti->createCommand($del_sql);
        $res = $commandDelete->execute();
        Logger::dayLog('mycatapi/deletePhone','delete num is : ',$res); 
        return $res;
    }
    public function updateTag($up_num_list,$user_phone)
    {
        if (empty($up_num_list)) {
            return 0;
        }
         // 逐条
        $num = 0;
        foreach ($up_num_list as $phone) {
            $saveDate = [
                'user_phone' => (string)$user_phone,
                'phone' => (string)ArrayHelper::getValue($phone,'phone',''),
                'source'=> 0,
                'tag_type' => ArrayHelper::getValue($phone,'tag_type',''),
                'status' => 0,
                'type' => 2,
            ];
            try {
                $res = (new PhoneTagList())->saveData($saveDate);
            } catch (\Exception $e) {
                Logger::dayLog('batchInsertTag','save fail',$e->getMessage());
                continue;
            }
           if ($res) {
                // Logger::dayLog('success_phone','update_success phone is ： ',$phone);
                $num++;
           }
        }
        echo "update success_count:".$num, "\n" ;
        return $num;
    }
    public function insertTag($in_num_list,$user_phone)
    {
        
        if (empty($in_num_list)) {
            return 0;
        }
        // var_dump($in_num_list);die;
        // 逐条
        $num = 0;
        $saveDate = [];
        $time = date('Y-m-d H:i:s');
        foreach ($in_num_list as $phone) {
            $saveDate[] = [
                'user_phone' => (string)$user_phone,
                'phone' => (string)$phone,
                'source'=> 0,
                'tag_type' => '',
                'status' => 0,
                'type' => 1,
                'modify_time' => $time,
                'create_time' => $time,
            ];
           //  try {
           //      $res = (new PhoneTagList())->saveData($saveDate);
           //  } catch (\Exception $e) {
           //      Logger::dayLog('batchInsertTag','save fail',$e->getMessage());
           //      continue;
           //  }
           // if ($res) {
           //      // Logger::dayLog('success_phone','success phone is ： ',$phone);
           //     $num++;
           // }
        }
        if (empty($saveDate)) {
            return 0;
        }
        //数据批量入库  
        $res = $this->db_anti->createCommand()->batchInsert(
            'tag_info_list',  
            ['user_phone','phone','source','tag_type','status','type','modify_time','create_time'],//字段  
            $saveDate
        );
        $sql = $res->getRawSql();
        $num = $res->execute();
        if ($num == 0) {
            Logger::dayLog('mycatapi/insertTagBatch',$sql);
        }
        echo "insert success_count:".$num, "\n" ;
        return $num;
    }

    private function tagApi($in_num_list)
    {
        $time = date('Y-m-d H:i:s');
        foreach ($in_num_list as $phone) {
            # 请求接口
            $new_tag = $this->phonelebApi->queryApi($phone);
            if (empty($new_tag)){
                continue;
            }
            $new_tag_list = ArrayHelper::getValue($new_tag,'tag',[]);
            $new_tag_str = implode(',',$new_tag_list);
            $this->in_list[] = [
                    'phone' => $phone,
                    'tag_type' => $new_tag_str,
                    'source' => $this->phonelebApi->source,
                    'modify_time' => $time,
                    'create_time' => $time,
                    'other_info' => ArrayHelper::getValue($new_tag,'other_info',''),
                ];
        }
    }

    public function getArrayUnion($array_a,$array_b)
    {
        $array_union = array_merge($array_a,$array_b);
        $array_union = array_unique($array_union);
        $array_union = array_values($array_union);
        return $array_union;
    }
	/**
     * 去重检查手机号
     */
    public function chkAddressList($addressList) {
        //1 验证是否合法
        if (!is_array($addressList) || empty($addressList)) {
            return $addressList;
        }

        //2 转成数据形式并去重
        $valid_mobiles = $this->getMobiles($addressList);

        //3 针对tidb中的数据去重
        if (!is_array($valid_mobiles) || empty($valid_mobiles)) {
            return 0;
        }
        $OAddressList = new AddressList();
        $difference = [];
        foreach($valid_mobiles as $key => $val){
            //分页
            $total = count($val);
            $limit = 200;
            $page = ceil($total / $limit);
            for($i = 0; $i < $page; $i++){
                $offset = $i * $limit;
                $data = array_slice($val, $offset, $limit);
                $phoneList = array_column($data, "phone");
                $get_limit_data = $OAddressList -> getLimitData($key, $phoneList);
                $diff_data = $this->diffData($phoneList, $get_limit_data);
                foreach ($data as $info){
                    if(in_array($info['phone'], $diff_data)){
                        $difference += $val;
                    }
                }
            }
        }
        return $difference;
    }

    /**
     * 获取
     * @param $source
     * @param $contrast
     * @return array
     */
    private function diffData($source, $contrast) {
        if (empty($contrast) || empty($source)){
            return $source;
        }
        $contrast_arr = ArrayHelper::getColumn($contrast, 'phone', []);
        $diff_data = array_diff($source,$contrast_arr);
        return $diff_data;
    }

    /**
     * 去重并删除不合法数据
     * @param $addresslist
     * @return array|bool
     */
    private function getMobiles($addresslist) {
        if (empty($addresslist)){
            return false;
        }
        $valid_mobiles = [];
        foreach($addresslist as $key => $val){
            if (empty($val['user_phone']) || empty($val['phone']) || empty($val['name'])){
                continue;
            }
            $mobile_pattern = "/^(1(([3578][0-9])|(47)))\d{8}$/";
            if (!preg_match($mobile_pattern, $val['user_phone']) || !preg_match($mobile_pattern, $val['phone'])) {
                continue;
            }
            $user_phone = ArrayHelper::getValue($val, 'user_phone');
            //去掉重复的
            $phone = ArrayHelper::getValue($val, 'phone');
            $phone_list = ArrayHelper::getValue($valid_mobiles, $user_phone, []);
            if (array_search(trim($phone), array_column($phone_list, 'phone')) !== false){
                continue;
            }

            $valid_mobiles[$user_phone][] = [
                'id'            => ArrayHelper::getValue($val, 'id'),
                'aid'           => ArrayHelper::getValue($val, 'aid'),
                'user_id'       => ArrayHelper::getValue($val, 'user_id'),
                'user_phone'    => $user_phone,
                'phone'         => (string)$phone,
                'name'          => ArrayHelper::getValue($val, 'name'),
                'modify_time'   => ArrayHelper::getValue($val, 'modify_time'),
                'create_time'   => ArrayHelper::getValue($val, 'create_time'),
            ];
        }
        return $valid_mobiles;
    }

}