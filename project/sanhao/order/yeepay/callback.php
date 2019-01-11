<?php
define('ORDER_ROOT', str_replace('\\','/',dirname(dirname(__FILE__))));
define('NOTIFY_ROOT', rtrim(dirname(ORDER_ROOT),'/'));
$correctdate = NOTIFY_ROOT.'/log/yeepay/correct/'.date('Y-m-d');
if(!file_exists( $correctdate ))
{
	@mkdir($correctdate, 0777);
}
file_put_contents($correctdate.'/'.$_REQUEST['r6_Order'].'_'.time().'.txt' , print_r( $_REQUEST , true ) ) ;

//$r0_Cmd = $_GET['r0_Cmd'];
//$r1_Code = $_GET['r1_Code'];
//$r2_TrxId = $_GET['r2_TrxId'];
//$r3_Amt = $_GET['r3_Amt'];
//$r4_Cur = $_GET['r4_Cur'];
//$r5_Pid = $_GET['r5_Pid'];
//$r6_Order = $_GET['r6_Order'];
//$r7_Uid = $_GET['r7_Uid'];
//$r8_MP = $_GET['r8_MP'];
//$r9_BType = $_GET['r9_BType'];
//$hmac = $_GET['hmac'];

require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');
require_once(WWW_ROOT . '/order/yeepay/yeepayCommon.php');
	
$p1_MerId = '10012407344';
$merchantKey = '757Rvi3L1T3w41WWa20ah6sk5nz9kz2087M0fIp0W2hgTp4074jpRPF4V927';

$return = getCallBackValue($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$hmac);

$bRet = CheckHmac($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$hmac);


if($bRet){
	if($r1_Code=="1"){
		if($r9_BType=="1"){
			//修改订单状态
			$pay_id = $r6_Order;
			$condition = array('pay_id' => $pay_id);
			$order = DB::LimitQuery('jx_orders', array(
				'condition' => $condition,
				'one'=>true
			));
			if($order['state'] != 'pay')
			{
				$order_id = $order['id'];
				//修改订单状态
				$uarray = array( 'state' => 'pay', 'yeepay_id' => $r2_TrxId);
				Table::UpdateCache('jx_orders', $order_id, $uarray);
				
				//修改商品已售数量
				$sql = "update `jx_products` set sale_number=sale_number+'".$order['quantity']."' where id=".$order['pid'];
				DB::Query($sql);
			
				//$result = json_decode(interface_post($url, $data));
			}
			//调用还款通知接口
			$key = $INI['system']['key'];
			$ret = 'success';
			$type = 1;
			$sign = md5($order['amt'].$order['req_id'].$ret.$type.$order['userno'].$r2_TrxId.$key);
			$url = $order['callback'];
			$data = "amt=".$order['amt']."&req_id=".$order['req_id']."&ret=".$ret."&type=".$type."&userno=".$order['userno']."&yeepay_id=".$r2_TrxId."&sign=".$sign;
			$redUrl = $url."?".$data;
			header('HTTP/1.1 301 Moved Permanently');
            header('Location: '.$redUrl);

		}
		else if ($r9_BType=="2") {
			//修改订单状态
			$pay_id = $r6_Order;
			$condition = array('pay_id' => $pay_id);
			$order = DB::LimitQuery('jx_orders', array(
				'condition' => $condition,
				'one'=>true
			));
			if($order['state'] != 'pay')
			{
				$order_id = $order['id'];
				//修改订单状态
				$uarray = array( 'state' => 'pay', 'yeepay_id' => $r2_TrxId);
				Table::UpdateCache('jx_orders', $order_id, $uarray);
				
				//修改商品已售数量
				$sql = "update `jx_products` set sale_number=sale_number+'".$order['quantity']."' where id=".$order['pid'];
				DB::Query($sql);
			}
			//调用还款通知接口
			$key = $INI['system']['key'];
			$ret = 'success';
			$type = 2;
			$sign = md5($order['amt'].$order['req_id'].$ret.$type.$order['userno'].$r2_TrxId.$key);
			$url = $order['callback'];
			$data = "amt=".$order['amt']."&req_id=".$order['req_id']."&ret=".$ret."&type=".$type."&userno=".$order['userno']."&yeepay_id=".$r2_TrxId."&sign=".$sign;
			file_put_contents($correctdate.'/'.$_REQUEST['r6_Order'].'_urldata1'.'.txt' , $url."?".$data ) ;
			$result = interface_post($url, $data);	
			file_put_contents($correctdate.'/'.$_REQUEST['r6_Order'].'_urldataresult'.'.txt' , print_r($result,true) ) ;
			//如果返回结果不是success,即表示通知处理失败，将订单信息保存，由定时任务发送通知结果
			if($result != 'success')
			{
				$u['pay_id'] = $order['pay_id'];
				$u['req_id'] = $order['req_id'];
				$u['userno'] = $order['userno'];
				$u['trade_no'] = $r2_TrxId;
				$u['amt'] = $order['amt'];
				$u['paytype'] = 'yeepay';
				$u['callback'] = $order['callback'];
				$u['createtime'] = time();
				$u['id'] = DB::Insert('jx_orders_notice', $u);

				//修改callback的
				$callback_url = $url."?".$data ;
				$uarray_callback = array( 'callback' => $callback_url);
				Table::UpdateCache('jx_orders', $order['id'], $uarray_callback);
			}
			echo 'success';exit;
		}
	}
	else 
	{
			$pay_id = $r6_Order;
			$condition = array('pay_id' => $pay_id);
			$order = DB::LimitQuery('jx_orders', array(
				'condition' => $condition,
				'one'=>true
			));
			//调用还款通知接口
			$key = $INI['system']['key'];
			$ret = 'fail';
			$type = 1;
			$sign = md5($order['amt'].$order['req_id'].$ret.$type.$order['userno'].$r2_TrxId.$key);
			$url = $order['callback'];
			$data = "amt=".$order['amt']."&req_id=".$order['req_id']."&ret=".$ret."&type=".$type."&userno=".$order['userno']."&yeepay_id=".$r2_TrxId."&sign=".$sign;
			$redUrl = $url."?".$data;
			header('HTTP/1.1 301 Moved Permanently');
            header('Location: '.$redUrl);
	}

}
else 
{
			$pay_id = $r6_Order;
			$condition = array('pay_id' => $pay_id);
			$order = DB::LimitQuery('jx_orders', array(
				'condition' => $condition,
				'one'=>true
			));
			//调用还款通知接口
			$key = $INI['system']['key'];
			$ret = 'fail';
			$type = 1;
			$sign = md5($order['amt'].$order['req_id'].$ret.$type.$order['userno'].$r2_TrxId.$key);
			$url = $order['callback'];
			$data = "amt=".$order['amt']."&req_id=".$order['req_id']."&ret=".$ret."&type=".$type."&userno=".$order['userno']."&yeepay_id=".$r2_TrxId."&sign=".$sign;
			$redUrl = $url."?".$data;
			header('HTTP/1.1 301 Moved Permanently');
            header('Location: '.$redUrl);
}