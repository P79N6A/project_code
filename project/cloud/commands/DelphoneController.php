<?php
/**
 *  定时同步通讯录数据yi_address_list信息到mycat里
 */
namespace app\commands;
use app\common\Logger;
use Yii;
use yii\helpers\ArrayHelper;

// D:\phpstudy\php55\php.exe  D:\phpstudy\WWW\cloud_ssdb\yii delphone rundelete
class DelphoneController extends BaseController 
{   
    private static $db_analysis_repertory;


    public function init()
    {
        self::$db_analysis_repertory = Yii::$app->db_analysis_repertory;
    }

    /**
     * @desc  
     * @param $startId 
     * @param $endId 
     */
    public function runDelete($startId = null, $endId = null) 
    {  
        $starttime = explode(' ',microtime()); 
        $get_phone_sql = 'SELECT MAX(id) AS max_id,phone,COUNT(1) AS num FROM phone_tag_list GROUP by phone HAVING num > 1 ORDER BY num DESC LIMIT 3000;';
        $command = self::$db_analysis_repertory->createCommand($get_phone_sql);
        $res1 = $command->queryAll();
        Logger::dayLog('delphone/runone',json_encode($res1));
        if (empty($res1)) {
            return false;
        }
        // phones
        $phone_list = ArrayHelper::getColumn($res1,'phone',[]);
        if (empty($res1)) {
            return false;
        }
        $phone_str = implode("','", $phone_list);
        // ids
        $id_list = ArrayHelper::getColumn($res1,'max_id',[]);
        if (empty($res1)) {
            return false;
        }
        $id_str = implode("','", $id_list);
        // delete
        $deleteSql = "DELETE FROM phone_tag_list WHERE phone IN ('".$phone_str."') AND id NOT IN ('".$id_str."');";
        $command = self::$db_analysis_repertory->createCommand($deleteSql);
        $del_ok = $command->execute();
        $endtime1 = explode(' ',microtime());
        $thistime1 = $endtime1[0]+$endtime1[1]-($starttime[0]+$starttime[1]);
        $thistime1 = round($thistime1,3);
        echo "use_time：".$thistime1." S\n";
        Logger::dayLog('delphone/time','use_time： is '.$thistime1,$del_ok);
        return $del_ok;
    }
}