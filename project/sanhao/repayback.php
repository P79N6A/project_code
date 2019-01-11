<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/13
 * Time: 17:04
 * 支付宝IP
 *  47.98.33.230
 *  47.98.40.160

 */
require_once(dirname(__FILE__) . '/include/appconfig.php');


//1.接收参数
$poststr = file_get_contents("php://input", 'r');
//2.解析参数
parse_str($poststr, $params_data);
if (empty($params_data)){
    echo  "fail";
    return false;
}
$data = [
    'pay_id'    =>$params_data['merchantOutOrderNo'],
    'state'     => $params_data['payResult'] == 1 ? 'pay' : 'unpay',
];

$info_data = Table::Fetch( 'jx_orders' , [$params_data['merchantOutOrderNo']] , 'pay_id' );
if (empty($info_data[0]['id'])){
    return "fail";
    return false;
}
$table = new Table('jx_orders', $data);
$table -> pk_value = $info_data[0]['id'];
$table -> state = $data['state'];
$up_array = array('state');
$flag = $table->update( $up_array );
if ($flag){
    echo "success";
}else{
    echo "fail";
}
/*
$table->pk_value = $product_id;
$table->name = $product_name;
$table->price = $product_price;
$table->url = $urlold;
$table->desc = $product_desc;
$table->max_number = $max_number;
$table->end_time = $end_time;
$table->express_price = $express_price;
$table->status = 1;

$up_array = array('name', 'price', 'url', 'desc', 'max_number', 'end_time', 'express_price', 'status');
$flag = $table->update( $up_array );
*/