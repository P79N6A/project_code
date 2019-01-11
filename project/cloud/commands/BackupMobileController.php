<?php
/**
 *  定时同步yi_user信息到mycat里
 */
namespace app\commands;
use app\common\Logger;
use Yii;

// */10 * * * * /usr/local/php-5.4.40/bin/php /data/wwwroot/cloud/yii backup-mobile runMobile >/dev/null 2>&1
class BackupMobileController extends BaseController 
{   
    private static $db_yiyiyuan;
    private static $db_sparrow;

    public function init()
    {
        self::$db_yiyiyuan = Yii::$app->db_yiyiyuan;
        self::$db_sparrow = Yii::$app->db_sparrow;
    }
    /**
     * @desc  
     * @param $start_time 
     * @param $end_time 
     */
    public function runMobile($start_time = null, $end_time = null) 
    {
        $time = time();
        if (!$end_time) {
            $end_time = date('Y-m-d H:i:00');
        }
        if (!$start_time) {
            // 默认10分钟
            $start_time = date('Y-m-d H:i:00', $time - 600);
        }
        $start_time = date('Y-m-d H:i:00', strtotime($start_time));
        $end_time = date('Y-m-d H:i:00', strtotime($end_time));
        $user_sql = "select user_id,mobile,realname,identity,create_time from yi_user where create_time > '".$start_time."' and create_time <= '".$end_time."'";
        $command = self::$db_yiyiyuan->createCommand($user_sql);
        $user_infos = $command->queryAll();
        if (empty($user_infos)){
            return ;
        }
        foreach ($user_infos as $user_info){
            $id = (int)$user_info['user_id'];
            $aid = 1;
            $user_id = (int)$user_info['user_id'];
            $mobile = trim($user_info['mobile']);
            $realname = trim($user_info['realname']);
            $identity = trim($user_info['identity']);
            $create_time = $user_info['create_time'];
            $query_sql = "select id from mobile where aid = 1 and mobile = '".$mobile."'";
            $command_query = self::$db_sparrow->createCommand($query_sql);
            $res = $command_query->queryOne();
            if(!empty($res)){
                continue;
            }
            $insert_sql = "insert into mobile (`id`,`aid`,`user_id`,`mobile`,`realname`,`identity`,`create_time`) values ('" . $id . "','" . $aid . "','" . $user_id . "','" . $mobile . "','" . $realname . "','" . $identity . "','" . $create_time . "')";
            $command_insert = self::$db_sparrow->createCommand($insert_sql);
            $ok = $command_insert->execute();
        }
    }
}