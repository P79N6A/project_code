<?php

namespace app\commands\pullmsg;

/**
 *  
 *  linux : sudo -u www /data/wwwroot/yiyiyuan/yii pullmsg/message/index
 *  windows D:phpStudy\php56n\php.exe D:WWW\yiyiyuanactive\yii pullmsg/message/index
 */
use app\models\news\MessageApply;
use app\models\news\SystemMessageList;
use app\models\news\User;
use app\models\news\User_password;
use app\models\news\WarnMessageList;
use PHPExcel_IOFactory;
use Yii;
use yii\console\Controller;

if (!class_exists('PHPExcel_IOFactory')) {
    include Yii::$app->basePath . '/phpexcel_new/PHPExcel/IOFactory.php';
}
// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class MessageController extends Controller {
    /*
     * 将BI提供的数据导入yi_mobile表中
     * 每十分钟执行一次
     */

    public function actionIndex() {
        $errorNum   = $successNum = $ignoreNum  = 0;
        $limit      = 500;
        $time       = date("Y-m-d H:i:00");
        $where      = [
            'AND',
                ['<=', 'send_time', $time],
                ['exec_status' => 0],
                ['status' => 0],
        ];
        $sendInfos  = MessageApply::find()->where($where)->indexBy('id')->orderBy('id asc')->limit($limit)->all();
        if (empty($sendInfos)) {
            exit(1);
        }
        $ids = array_keys($sendInfos);
        if (!is_array($ids) || empty($ids)) {
            exit(2);
        }
        //全部锁定
        $lockAll = (new MessageApply())->lockAll($ids);
        $nowtime = date('Y-m-d H:i:s');
        //逐条锁定，并插入
        foreach ($sendInfos as $key => $val) {
            $msg_type  = json_decode($val['msg_type'], TRUE);
            $platform_type  = json_decode($val['platform_type'], TRUE);
            $send_urls = $val->url;
            if (empty($send_urls)) {
                continue;
            }
            foreach ($send_urls as $url) {
                if (file_exists($url['url'])) {
                    $fileType        = PHPExcel_IOFactory::identify($url['url']);
                    $objReader       = PHPExcel_IOFactory::createReader($fileType);
                    $objPHPExcel     = $objReader->load($url['url']);
                    $sheet           = $objPHPExcel->getSheet(0);
                    $pull_messageKey = ['type', 'title', 'contact', 'user_id', 'is_show', 'back_action', 'back_url', 'relation_id', 'create_time', 'last_modify_time', 'platform_type','channel','message_id'];
                    $messageKey      = ['mid', 'title', 'contact', 'user_id', 'send_time', 'read_status', 'create_time', 'last_modify_time', 'platform_type'];
                    foreach ($sheet->getRowIterator() as $key => $row) {
                        $cellIterator = $row->getCellIterator();
                        $cellIterator->setIterateOnlyExistingCells(false);
                        $cell_data    = [];
                        foreach ($cellIterator as $k => $cell) {
                            $user_id = $cell->getValue();
                            $o_user = (new User())->getById($user_id);
                            if(empty($o_user)){
                                continue;
                            }
                            //android、ios过滤
                            $o_user_password = (new User_password())->getUserPassword($user_id);
                            $device_type = 0;
                            if(!empty($o_user_password) && !empty($o_user_password->device_type)){
                                if($o_user_password->device_type == 'android'){
                                    $device_type = 1;
                                }
                                if($o_user_password->device_type == 'ios'){
                                    $device_type = 2;
                                }
                            }
                            if(array_diff([1, 2], $platform_type) != [] && in_array(1, $platform_type) && $device_type != 1){
                                continue;
                            } elseif (array_diff([1, 2], $platform_type) != [] && in_array(2, $platform_type) && $device_type != 2) {
                                continue;
                            }

                            if (array_diff([1, 2], $msg_type) == []) {
                                $messageValue[] = [$val['id'], $val['title'], $val['contact'], $user_id, $val['send_time'], 0, $nowtime, $nowtime, $val['platform_type']];
                                $res            = Yii::$app->db->createCommand()->batchInsert(SystemMessageList::tableName(), $messageKey, $messageValue)->execute();
                                $message_id     = Yii::$app->db->getLastInsertID();
                                $pullValue[]    = [8, $val['push_title'], $val['push_contact'], $user_id, 0, $val['back_action'], $val['back_url'], $message_id, $nowtime, $nowtime, $val['platform_type'], 2, $val['id']];
                                $ress           = Yii::$app->db->createCommand()->batchInsert(WarnMessageList::tableName(), $pull_messageKey, $pullValue)->execute();
                                unset($messageValue);
                                unset($pullValue);
                            } elseif (in_array(1, $msg_type)) {
                                $messageValue[] = [$val['id'], $val['title'], $val['contact'], $user_id, $val['send_time'], 0, $nowtime, $nowtime, $val['platform_type']];
                                $message_id     = Yii::$app->db->createCommand()->batchInsert(SystemMessageList::tableName(), $messageKey, $messageValue)->execute();
                                unset($messageValue);
                            } elseif (in_array(2, $msg_type)) {
                                $pullValue[] = [8, $val['push_title'], $val['push_contact'], $user_id, 0, $val['back_action'], $val['back_url'], '', $nowtime, $nowtime, $val['platform_type'], 2, $val['id']];
                                $ress        = Yii::$app->db->createCommand()->batchInsert(WarnMessageList::tableName(), $pull_messageKey, $pullValue)->execute();
                                unset($pullValue);
                            }
                        }
                    }
                }
            }
        }
        $rows = MessageApply::updateAll(['status' => 2, 'exec_status' => 1, 'last_modify_time' => date("Y-m-d H:i:s")], ['id' => $ids]);
    }

}
