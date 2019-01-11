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
use app\models\anti\AfTagBase;
use app\models\anti\PhoneTagList;
use app\models\mycat\MycatApi;
use app\models\phonelab\DcTellabRecord;

/**
 * 号码标签更新接口
 * 本地测试：/usr/local/bin/php /data/wwwroot/test/cloud/yii  tellab tagtest
 */
class TellabController extends BaseController
{
    private $oTelLabRept;
    private $file_path;
    private $one_data_num;
    private $init_data_num;
    private $chkPhoneApi;
    private $phonelebApi;
    private $worker = 1;

    public function init() {
        $this->oTelLabRept = new MycatApi();
        $this->chkPhoneApi = new ChkPhone();
        $this->phonelebApi = new PhonelebApi();
        $this->one_data_num = 4000000;
        $this->init_data_num = 500000;
    }
    // 号码标签
    public function runTag()
    {   
        $starttime = explode(' ',microtime());
        # 检查当日次数是否大于规定次数
        $chack_times = $this->checkTimes();
        if (!$chack_times) {
            Logger::dayLog('tellab/runTag', 'Excessive number of requests');
            die('Excessive number of requests');
        }

        # 删除状态为13,2的数据
        $del = $this->oTelLabRept->deletePhone();
        # 检查中间表初始数据是否过多
        $check_init = $this->checkInit();
        if (!$check_init) {
            Logger::dayLog('tellab/runTag', 'init_data too many too deal with');
            die('init_data too many too deal with');
        }
        # 获取需要跑标签的用户
        $tagBaseList = $this->runBaseList();
        if (empty($tagBaseList)) {
            Logger::dayLog('tellab/runTag', 'nothing to deal with');
            die('nothing to deal with');
        }
        $phone_list = ArrayHelper::getColumn($tagBaseList,'phone');
        # 获取查询过第三方号码标签的用户 
        $query_phone = $this->phonelebApi->getQueryPhone($phone_list);
        $mytime= date("Y-m-d H:i:s", strtotime("-1 month")); 
        # 请求标签
        $n = 0;
        foreach ($tagBaseList as $tagBase) {
            $user_phone = ArrayHelper::getValue($tagBase,'phone','');
            if (empty($user_phone)) {
                continue;
            }
            #用户一个月内请求过将不再请求，有效期一个月
            if (isset($query_phone[$user_phone]) && $query_phone[$user_phone] > $mytime) {
                continue;
            }
            # 获取用户通讯录
            $phone_num_list = $this->chkPhoneApi->getPhonelist($user_phone);
            # 设置标签
            $all_nums = $this->setTag($phone_num_list,$user_phone);
            # save 
            if (isset($query_phone[$user_phone])) {
                $res = $this->phonelebApi->updateRecord($tagBase);
            } else{
                $res = $this->phonelebApi->saveRecord($tagBase);
            }
            $n++;
        }
        # 完成
        if (SYSTEM_PROD) {
            $ids = ArrayHelper::getColumn($tagBaseList,'id');
            $lock_num = (new AfTagBase)->finishTags($ids);
            Yii::$app->ssdb_detail->set('all_tag_num',$this->oTelLabRept->query_times);
        }
        $endtime1 = explode(' ',microtime());
        $thistime1 = $endtime1[0]+$endtime1[1]-($starttime[0]+$starttime[1]);
        $thistime1 = round($thistime1,3);
        echo "use_time：".$thistime1." S\n";
        Logger::dayLog('tellab/time','use_time： is '.$thistime1,$n);
        return true;
    }

    private function setTag(&$phone_num_list,$user_phone)
    {
        # 删除用户一个月前所有已同步数据
        $res = $this->oTelLabRept->delateUser($user_phone);
        # 1、将用户按更新及新增分组
        $in_and_up_list = $this->oTelLabRept->checkPhoneTag($phone_num_list,$user_phone);
        # 2、更新
        $up_nums = $this->oTelLabRept->updateTag($in_and_up_list['up_list'],$user_phone);
        # 3、新增
        $in_nums = $this->oTelLabRept->insertTag($in_and_up_list['in_list'],$user_phone);

        return $in_nums+$up_nums;
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

    # 检查中间表初始数据是否过多
    private function checkInit()
    {
        $model = new PhoneTagList();
        $num = $model->getInitCount();
        if($this->init_data_num < $num){
            return false;
        }
        return true;
    }
    # 获取需要跑标签的用户
    private function runBaseList()
    {
        $time = date('Y-m-d H:i:s', strtotime("-7 day"));
        $where = [
            'tag_status' => 0, 
            'create_time' => $time,
        ];

        $field = 'id, aid, user_id, phone';
        $runBaseList = (new AfTagBase)->getTagBase($where,$field);
        if (empty($runBaseList)) {
            return [];
        }

        # 锁定状态处理中@todo 生产要打开
        if (SYSTEM_PROD) {
            $ids = ArrayHelper::getColumn($runBaseList,'id');
            $lock_num = (new AfTagBase)->lockTags($ids);
        }

        return $runBaseList;
    }
}