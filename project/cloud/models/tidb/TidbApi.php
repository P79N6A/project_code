<?php

namespace app\models\tidb;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Curl;
use app\common\Logger;
use app\common\ArrayGroupBy;
use app\models\phonelab\PhonelebApi;
use app\models\tidb\TiPhoneTagList;

/**
 * TIDB 统一对外开放接口
 */
class TidbApi
{
    private $db;
    private static $source;
    private $phonelebApi;
    private $in_list = [];
    public $query_times;

    public function __construct()
    {
        $this->db = Yii::$app->db_tidb;
        $this->phonelebApi = new PhonelebApi();
    }

    // 更新tag
    public function updateTagBatch($tag_list, $source)
    {
        try {
        if (empty($tag_list)) {
            Logger::dayLog('tiapi/saveTag', 'update_tag_list is empty',json_encode($tag_list));
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
            Logger::dayLog('tiapi/upsql', "update sql is:".$sql);
        }
        Logger::dayLog('tiapi/upTag', "update success_count:".$ok);
        echo "update success_count:".$ok, "\n" ;
        return $ok;
        } catch (\Exception $e) {
            Logger::dayLog('tiapi/error', 'update error',$e->getMessage(),json_encode($tag_list));
            return 0;
        }
    }
}