<?php
/**
 *  每日更新垃圾号码
 */
namespace app\commands;

use Yii;

use app\common\Logger;

// */1 * * * * /usr/local/bin/php /data/wwwroot/cloud/yii upd-grabnum updgrab >/dev/null 2>&1
//php D:\phpstudy\WWW\cloud_ssdb\yii upd-grabnum updgrab
class UpdGrabnumController extends BaseController
{
    // 更新每日的垃圾号码
    public function updgrab()
    {
        $this->resetTagnum();
        $grabNumsArr = $this->getGrabnum();
        
        var_dump($grabNumsArr);
        Logger::dayLog('runJac','grabNumsArr is :',json_encode($grabNumsArr));
        if(!empty($grabNumsArr)){
            $ok = Yii::$app->ssdb_detail->set('garb_num',json_encode($grabNumsArr));
            if ($ok) {
                Logger::dayLog('runJac','Grabnums update successfully!');
            } else {
                Logger::dayLog('runJac','Grabnums update failed!');
            }
            die;
        }
        Logger::dayLog('runJac','Grabnums update failed!');
    }

    //获取每日的垃圾号码
    private function getGrabnum(){
        $today = date('Ymd');
        $res = array();
        if (SYSTEM_PROD) {
            $grabnumPath = "/data/select_mysql_garbage/".$today."/result_mysql_".$today.".txt";
        } else{
            $grabnumPath = __DIR__ . "/../../../select_mysql_garbage/".$today."/result_mysql_".$today.".txt";
        }
        if (!file_exists($grabnumPath)){ 
            Logger::dayLog('runJac','file not exist',$grabnumPath);
            return $res; 
        }

        $contents = file($grabnumPath);
        for ($x=0; $x < count($contents); $x++){
            if (trim($contents[$x]) != ''){
                $line = explode("\t", trim($contents[$x]));
                $res[] = $line[0];
            }
        }
        return $res;
    }

    // 重置每天标签总量
    private function resetTagnum()
    {
        $ok = Yii::$app->ssdb_detail->set('all_tag_num',0);
        return $ok;
    }
}