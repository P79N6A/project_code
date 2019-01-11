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
				$uarray = array( 'state' => 'pay');
				Table::UpdateCache('jx_orders', $order_id, $uarray);
				
				//修改商品已售数量
				$sql = "update `jx_products` set sale_number=sale_number+'".$order['quantity']."' where id=".$order['pid'];
				DB::Query($sql);
			}
			
			redirect(WEB_ROOT . "/index.php");

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
				$uarray = array( 'state' => 'pay');
				Table::UpdateCache('jx_orders', $order_id, $uarray);
				
				//修改商品已售数量
				$sql = "update `jx_products` set sale_number=sale_number+'".$order['quantity']."' where id=".$order['pid'];
				DB::Query($sql);
			}
			echo 'success';exit;
		}
	}
	else 
	{
		echo 'fail';exit;
	}

}
else 
{
	echo 'fail';exit;
}