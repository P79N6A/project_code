<?php
define('ORDER_ROOT', str_replace('\\','/',dirname(__FILE__)));
define('NOTIFY_ROOT', rtrim(dirname(ORDER_ROOT),'/'));
$correctdate = NOTIFY_ROOT.'/log/alipay/correct/'.date('Y-m-d');

require_once(dirname(dirname(__FILE__)) . '/app.php');

//jx_orders_notice表中没有通知成功的订单信息
$condition = array('id > 0');
$orderlist = DB::LimitQuery('jx_orders_notice', array(
	'condition' => $condition,
));

$key = $INI['system']['key'];
$ret = 'success';
$type = 2;
if(!empty($orderlist))
{
	if(!file_exists( $correctdate ))
	{
		@mkdir($correctdate, 0777);
	}
	foreach ($orderlist as $k=>$value)
	{
		if($value['paytype'] == 'alipay')
		{
			$sign = md5($value['trade_no'].$value['amt'].$value['req_id'].$ret.$type.$value['userno'].$key);
			$url = $value['callback'];
			$data = "amt=".$value['amt']."&req_id=".$value['req_id']."&ret=".$ret."&type=".$type."&userno=".$value['userno']."&alipay_id=".$value['trade_no']."&sign=".$sign;
			file_put_contents($correctdate.'/'.$value['req_id'].'_'.date('Y-m-d H:i:s').'_urldata1'.'.txt' , $url."?".$data ) ;
			$result = interface_post($url, $data);
			file_put_contents($correctdate.'/'.$value['req_id'].'_'.date('Y-m-d H:i:s').'_urldataresult'.'.txt' , print_r($result,true) ) ;
			if($result == 'success' || $result == 'fail')
			{
				Table::Delete('jx_orders_notice', $value['id']);
			}
		}
		else 
		{
			$sign = md5($value['trade_no'].$value['amt'].$value['req_id'].$ret.$type.$value['userno'].$key);
			$url = $value['callback'];
			$data = "amt=".$value['amt']."&req_id=".$value['req_id']."&ret=".$ret."&type=".$type."&userno=".$value['userno']."&yeepay_id=".$value['trade_no']."&sign=".$sign;
			file_put_contents($correctdate.'/'.$value['req_id'].'_'.date('Y-m-d H:i:s').'_urldata1'.'.txt' , $url."?".$data ) ;
			$result = interface_post($url, $data);
			file_put_contents($correctdate.'/'.$value['req_id'].'_'.date('Y-m-d H:i:s').'_urldataresult'.'.txt' , print_r($result,true) ) ;
			if($result == 'success' || $result == 'fail')
			{
				Table::Delete('jx_orders_notice', $value['id']);
			}
		}
	}
}
echo 'success';exit;
