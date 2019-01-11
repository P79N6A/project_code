<?php

namespace app\commonapi;

class Bank {

    /**
     * 易宝绑卡支持的银行
     * @return array
     */
    public static function supportbank( $key ) {
        $array_bank = [
        	'ICBC' => '工商银行',
        	'BOC' => '中国银行',
        	'CCB' => '建设银行',
        	'POST' => '邮政储蓄',
        	'ECITIC' => '中信银行',
        	'CEB' => '光大银行',
        	'HXB' => '华夏银行',
        	'CMB' => '招商银行',
        	'CIB' => '兴业银行',
        	'SPDB' => '浦发银行',
        	'PINGAN' => '平安银行',
        	'GDB' => '广发银行',
        	'CMBC' => '民生银行',
        	'ABC' => '农业银行',
        	'BOB' => '北京银行'
        ];
        
        if(isset($array_bank[$key])){
        	return true;
        }else{
        	return false;
        }
    }

}
