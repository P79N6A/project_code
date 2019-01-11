<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/2
 * Time: 18:48
 */

function a($n){
    $ret = array();
    for($i=0;$i<=$n;$i++){
        if($i == 0) {
            $ret[$i] = 0;
            continue;
        }elseif($i == 1){
            $ret[$i] = 1;
            continue;
        }
        $ret[$i] = $ret[$i-1] + $ret[$i-2];
    }
    return $ret[$n];
}
echo a(10);
