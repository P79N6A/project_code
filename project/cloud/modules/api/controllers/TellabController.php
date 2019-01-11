<?php
/**
 * 号码标签
 */

namespace app\modules\api\controllers;

use app\common\Logger;
use yii\helpers\ArrayHelper;
use app\models\phonelab\PhoneFactory;
use app\models\phonelab\TelLabChannel;
use app\models\mycat\MyPhoneTagList;

class TellabController extends ApiController
{
    private $oTelLabRept;
    protected static $phoneHandler = null;

    public function init() {
        parent::init();
        $this->oTelLabRept = new MyPhoneTagList();
    }

    /**
     * 获取并存储号码标签信息(批量)
     */
    public function actionPhonetagBatch() 
    {
        $data = $this->postdata;
        // $data['phone_list'] = ['07662916063','18070338820','07184222999'];
        $phoneList = isset($data['phone_list']) ? $data['phone_list'] : '';
        if(empty($phoneList)){
            return $this->returnMsg('2000001');
        }
        $res = [];
        foreach($phoneList as $phone){
            # 1、参数校验
            if (!$this->oTelLabRept->validatePhone($phone)){
                $res[$phone] = [];
                Logger::dayLog('tellab', 'illegal_number', '号码非法', $phone);
                continue;
            }
            # 2、获取标签
            $tag = $this->getTag($phone);
            if (empty($tag)) {
                $res[$phone] = [];
                Logger::dayLog('tellab', 'illegal_number', '无法获取标签信息', $phone);
                continue;
            }
            $res[$phone] = ArrayHelper::getValue($tag,'tag_type','');
        }
        return $this->returnMsg('0000', $res);
    }

    /**
     * 获取并存储号码标签信息（单条）
     */
    public function actionPhonetag() {
        # 1、参数校验
        $data = $this->postdata;
        // $data = ['phone'=>'07662916063'];
        // $data = ['phone'=>'18070338820'];
        // $data = ['phone'=>'07184222999'];
        $phone = isset($data['phone']) ? $data['phone'] : '';
        if(empty($phone)){
            return $this->returnMsg('2000001');
        }

        if (!$this->oTelLabRept->validatePhone($phone)){
            Logger::dayLog('tellab/Phonetag', 'validatePhone', '号码非法', $phone);
            return $this->returnMsg('2000001');
        }

        # 2、获取标签
        $tag = $this->getTag($phone);
        if (empty($tag)) {
            Logger::dayLog('tellab/Phonetag', 'getTag', '无法获取标签信息', $phone);
            return $this->returnMsg('2000003');
        }
        return $this->returnMsg('0000', $tag);
    }
    private function getTag($phone)
    {
        # 1、查询标签库
        $phone_tag = $this->oTelLabRept->getPhoneTag($phone);
        if (!empty($phone_tag)) {
            return $phone_tag;
        }
        # 2、调用第三方接口
        $channels = $this ->getSupportChannel();
        $phone_tag = $this->getPhoneRoute($channels, $phone);
        if(empty($phone_tag)) {
            return [];
        }
        # 3、数据提取
        $tag_arr = ArrayHelper::getColumn($phone_tag,'tag','');
        $tag_str = implode($tag_arr,',');
        # 4、存入mycat中
        $save_res = $this->saveTag($phone, $tag_str, $phone_tag);
        if (!$save_res) {
            Logger::dayLog('tellab/saveTag', '储存失败', json_encode($phone_tag), $phone);
        }
        $ret_data = [
            'phone' => $phone,
            'tag_type' => $tag_str,
        ];
        return $ret_data;
    }
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