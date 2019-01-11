<?php

namespace app\models\phonelab\common;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Curl;
use app\common\Logger;
use app\models\phonelab\PhoneInterface;

/**
 * 凭安统一对外开放接口
 */
class ChkPhone 
{
    // 获取号码列表
    public function getPhonelist($user_phone)
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
            $detail_phone_arr = ArrayHelper::getValue($detail_list,'phoneArr',[]);
        }

        if (!empty($address_list)) {
            $addr_phone_arr = json_decode($address_list,true);
        }
        if (!is_array($addr_phone_arr)) {
            Logger::dayLog('chkphone/error','address_list is ', $address_list,$addr_phone_arr);
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
        if (!is_array($array_b)) {
            $array_b = [];
        }
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
}
