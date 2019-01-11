<?php

namespace app\models\phonelab;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Curl;
use app\common\Logger;
use app\models\phonelab\PhoneInterface;

/**
 * 号码标签统一对外开放接口
 */
class PhonelebApi
{
    protected static $phoneHandler = null;
    public $source = 0;

    public function queryApi($phone)
    {
        # 1、参数验证
        if (empty($phone)) {
            return [];
        }
        # 2、调用第三方接口
        $channels = $this->getSupportChannel();
        $phone_tag_new = $this->getPhoneRoute($channels, $phone);
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
            'tag' => $tag_arr,
            'other_info' => json_encode($other_info,JSON_UNESCAPED_UNICODE),
        ];
        return $tag_all;
    }
    public function getSupportChannel() 
    {
        $oPhoneChannel = new TelLabChannel();
        $channels = $oPhoneChannel->supportChannel();
        if ($channels) {
            $this->source = ArrayHelper::getValue($channels,'source','18');
        }
        return $channels;
    }

    /**
     * @return mixed
     */
    public function getPhoneRoute($channels, $phone){
        if (!$channels) {
            return false;
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

        //save record
    public function saveRecord($data)
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
    public function updateRecord($data)
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
        $records->user_id = ArrayHelper::getValue($data,'user_id','');
        $res = $records->save();
        return $res;
    }
    public function getQueryPhone($phones)
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
}
