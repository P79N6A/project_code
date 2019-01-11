<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/6
 * Time: 14:21
 */
namespace app\modules\balance\common;

class PaymentCommon
{
    /**
     * 差错类型
     * @return array
     */
    public function errorStatus()
    {
        return [
           // '0' => '系统单边账',
            '1' => '通道单边账',
            '2' => '系统单边账',
            '3' => '金额有误',
            '4' => '状态有误',
            '5' => '关闭订单',
        ];
    }

    /**
     * 公司主体
     *
     */
    public function realFund(){
        return  [
            '1' => '小小黛朵',
            '2' => '先花花',
        ];
    }


    /**
     * 处理商编号
     * handleBusinessEditor
     */
    public function handleBE($mechart_num){
        if(empty($mechart_num)){
            return '';
        }
        $mechart_num = str_replace('，',',',$mechart_num);
        $arr = explode(',',$mechart_num);
        if(empty($arr[0])){
            unset($arr[0]);
        }
        if(empty(end($arr))){
            array_pop($arr);
        }
        /*foreach($arr as $k=>$v){
            $arr[$k] = '\''.$v.'\'';
        }
        $result = implode(',',$arr);*/
        return $arr;
    }


}