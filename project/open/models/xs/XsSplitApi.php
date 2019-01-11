<?php

namespace app\models\xs;

use Yii;
use app\common\Logger;
use yii\helpers\ArrayHelper;
/**
 * 分位值分析接口
 * 用于计算用户多投分位值的结果
 * $type 区分初复贷  1：初贷；2：复贷；
 */
class XsSplitApi{

	private static $splitvalue = []; #分位值结果
    private static $type ;
    public function __construct($type = 1) {
        self::$type = $type;
    }

	public function getPercentClass($multi_info,$multi_days){
        $percentClass = [];
        # get Split Value only once
        if (empty(self::$splitvalue)) {
            self::$splitvalue = $this->getSplitValue();
        }

        # @todo  是否再判断一次self::$splitvalue  发现及时为空也不影响 暂时不判断
        $multi_list = $this->getMultiNum($multi_info);
        # getsplit
        $split_value = $this->getSplit($multi_days);
        # getClass
        foreach ($multi_list as $name => $multi_num) {
            $keys_name = $multi_days.'_'.$name.'_p_class';
            $percentClass[$keys_name] = $this->getClass($multi_num,$split_value,$name);
        }
        return $percentClass;
    }

    private function getSplit($days){
        if (self::$type == 1) {
            $splitValue = ArrayHelper::getValue(self::$splitvalue,'first_split',[]);
        } else {
            $splitValue = ArrayHelper::getValue(self::$splitvalue,'reloan_split',[]);
        }
        $seven_all = ArrayHelper::getValue($splitValue,'seven_all',[]);
        $seven_p2p = ArrayHelper::getValue($splitValue,'seven_p2p',[]);
        $seven_small = ArrayHelper::getValue($splitValue,'seven_small',[]);
        $seven_big = ArrayHelper::getValue($splitValue,'seven_big',[]);
        $seven_common = ArrayHelper::getValue($splitValue,'seven_common',[]);
        $one_mouth_all = ArrayHelper::getValue($splitValue,'one_mouth_all',[]);
        $one_mouth_p2p = ArrayHelper::getValue($splitValue,'one_mouth_p2p',[]);
        $one_mouth_small = ArrayHelper::getValue($splitValue,'one_mouth_small',[]);
        $one_mouth_big = ArrayHelper::getValue($splitValue,'one_mouth_big',[]);
        $one_mouth_common = ArrayHelper::getValue($splitValue,'one_mouth_common',[]);
        $ret_data = [
            'multi_all' => $days == 7 ? $seven_all : $one_mouth_all,
            'multi_p2p' => $days == 7 ? $seven_p2p : $one_mouth_p2p,
            'multi_small' => $days == 7 ? $seven_small : $one_mouth_small,
            'multi_big' => $days == 7 ? $seven_big : $one_mouth_big,
            'multi_common' => $days == 7 ? $seven_common : $one_mouth_common,
        ];
        return $ret_data;
    }

    private function getClass($multi_num,$split_value,$name){
        $percent_class = 0;
        $split_num = ArrayHelper::getValue($split_value,$name,[]);
        if (empty($split_num)) {
            return $percent_class;
        }
        foreach ($split_num as $class => $num) {
            if ($multi_num <= $num) {
                $percent_class = $class;
                break;
            }
        }
        return $percent_class;
    }
    private function getMultiNum($multi_info){
            $seven_id_all = ArrayHelper::getValue($multi_info,'借款人身份证个数',0);
            $seven_id_p2p = ArrayHelper::getValue($multi_info,'借款人身份证详情.0.P2P网贷',0);
            $seven_id_small = ArrayHelper::getValue($multi_info,'借款人身份证详情.0.小额贷款公司',0);
            $seven_id_big = ArrayHelper::getValue($multi_info,'借款人身份证详情.0.大型消费金融公司',0);
            $seven_id_common = ArrayHelper::getValue($multi_info,'借款人身份证详情.0.一般消费分期平台',0);
            $seven_ph_all = ArrayHelper::getValue($multi_info,'借款人手机个数',0);
            $seven_ph_p2p = ArrayHelper::getValue($multi_info,'借款人手机详情.0.P2P网贷',0);
            $seven_ph_small = ArrayHelper::getValue($multi_info,'借款人手机详情.0.小额贷款公司',0);
            $seven_ph_big = ArrayHelper::getValue($multi_info,'借款人手机详情.0.大型消费金融公司',0);
            $seven_ph_common = ArrayHelper::getValue($multi_info,'借款人手机详情.0.一般消费分期平台',0);
            $ret_data = [
                'multi_all' => $this->setData($seven_id_all,$seven_ph_all),
                'multi_p2p' => $this->setData($seven_id_p2p,$seven_ph_p2p),
                'multi_small' => $this->setData($seven_id_small,$seven_ph_small),
                'multi_big' => $this->setData($seven_id_big,$seven_ph_big),
                'multi_common' => $this->setData($seven_id_common,$seven_ph_common),
            ];
            return $ret_data;
    }

    /**
     * [setData description]
     * @param [type] $id_data [description]
     * @param [type] $ph_data [description]
     */
    private function setData($id_data,$ph_data){
        $data = $id_data >= $ph_data ? $id_data : $ph_data;
        if ($data == 0) {
            return 0;
        }
        return (int)$data;
    }
    private function getSplitValue(){
        # get Split Value splitvalue
        $oXsSplitValue = new XsSplitValue();
        $split_one = $oXsSplitValue->getOne();
        if (empty($split_one)) {
            return [];
        }
        $first_split = ArrayHelper::getValue($split_one,'first_split',[]);
        $reloan_split = ArrayHelper::getValue($split_one,'reloan_split',[]);
        return [
            'first_split' => json_decode($first_split,true),
            'reloan_split' => json_decode($reloan_split,true),
        ];
    }
}