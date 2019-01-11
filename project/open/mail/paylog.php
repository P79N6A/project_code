<?php
use yii\helpers\Html;
use app\models\Payorder;
?>


<div style="margin-top:20px;color:#cf0000;">
<b><?=$day?>支付成功</b>
</div>
<table>
	<tr>
		<th style="border-top:1px solid #ddd;width:150px;text-align:left;">应用名称</th>
		<th style="border-top:1px solid #ddd;width:150px;text-align:left;">通道</th>
		<th style="border-top:1px solid #ddd;width:150px;text-align:left;">成功支付金额</th>
	</tr>

	<?php if($successPay) foreach($successPay as $pay):
	$aidName = isset($aidNames[$pay['aid']]) ? $aidNames[$pay['aid']] : '-';
	?>
	<tr>
		<td style="border-top:1px solid #ddd;width:150px;text-align:left;"><?=$aidName?></td>
		<td style="border-top:1px solid #ddd;width:150px;text-align:left;"><?=$paytypeArr[$pay['pay_type']]?></td>
		<td style="border-top:1px solid #ddd;width:150px;text-align:left;"><?=number_format($pay['amount'] / 100,0)?></td>
	</tr>
	<?php endforeach;?>
</table>		



<?php if($typeTotal):?>
<div style="margin-top:20px;color:#cf0000;">
<b><?=$day?>订单总数 <?=$allTotal?></b>
</div>
<table>
	<tr>
		<th style="border-top:1px solid #ddd;width:150px;text-align:left;">支付通道</th>
		<th style="border-top:1px solid #ddd;width:150px;text-align:left;">状态</th>
		<th style="border-top:1px solid #ddd;width:150px;text-align:left;">条数</th>
		<th style="border-top:1px solid #ddd;width:150px;text-align:left;">百分比</th>
	</tr>

	<?php if($typeTotal) foreach($typeTotal as $type):?>
	<tr>
		<td style="border-top:1px solid #ddd;width:150px;text-align:left;"><?=$paytypeArr[$type['pay_type']]?></td>
		<td style="border-top:1px solid #ddd;width:150px;text-align:left;"><?=$statusArr[$type['status']];?></td>
		<td style="border-top:1px solid #ddd;width:150px;text-align:left;"><?=$type['total']?></td>
		<td style="border-top:1px solid #ddd;width:150px;text-align:left;"><?=number_format($type['total']/$allTotal * 100,2)?>%</td>
	</tr>
	<?php endforeach;?>
</table>		
<?php endif;?>





<?php if($bindTotal):?>
<div style="margin-top:10px;color:#cf0000;">
<b>绑卡失败数：<?php echo $bindTotal;?></b>
</div>
<table >
	<tr>
		<th style="border-top:1px solid #ddd;width:150px;text-align:left;">错误码</th>
		<th style="border-top:1px solid #ddd;width:150px;text-align:left;">错误原因</th>
		<th style="border-top:1px solid #ddd;width:150px;text-align:left;">次数</th>
	</tr>

	<?php if($allTotal) foreach($bindData as $r):?>
	<tr>
		<td style="border-top:1px solid #ddd;width:150px;text-align:left;"><?=$r['error_code']?></td>
		<td style="border-top:1px solid #ddd;width:150px;text-align:left;"><?=$r['error_msg']?></td>
		<td style="border-top:1px solid #ddd;width:150px;text-align:left;"><?=$r['total'];?></td>
	</tr>
	<?php endforeach;?>
</table>
<?php endif;?>






<?php if($tztTotal):?>
<div style="margin-top:10px;color:#cf0000;">
<b>投资通失败数：<?php echo $tztTotal;?></b>
</div>
<table >
	<tr>
		<th style="border-top:1px solid #ddd;width:150px;text-align:left;">错误码</th>
		<th style="border-top:1px solid #ddd;width:150px;text-align:left;">错误原因</th>
		<th style="border-top:1px solid #ddd;width:150px;text-align:left;">次数</th>
	</tr>

	<?php if($tztTotal) foreach($tztData as $r):?>
	<tr>
		<td style="border-top:1px solid #ddd;width:150px;text-align:left;"><?=$r['error_code']?></td>
		<td style="border-top:1px solid #ddd;width:150px;text-align:left;"><?=$r['error_msg']?></td>
		<td style="border-top:1px solid #ddd;width:150px;text-align:left;"><?=$r['total'];?></td>
	</tr>
	<?php endforeach;?>
</table>
<?php endif;?>
	
	
	
	
	
<?php if($quickTotal):?>
<div style="margin-top:10px;color:#cf0000;">
<b>一键支付失败数：<?php echo $quickTotal;?></b>
</div>
<table>
	<tr>
		<th style="border-top:1px solid #ddd;width:150px;text-align:left;">错误码</th>
		<th style="border-top:1px solid #ddd;width:150px;text-align:left;">错误原因</th>
		<th style="border-top:1px solid #ddd;width:150px;text-align:left;">次数</th>
	</tr>

	<?php foreach($quickData as $r):?>
	<tr>
		<td style="border-top:1px solid #ddd;width:150px;text-align:left;"><?=$r['error_code']?></td>
		<td style="border-top:1px solid #ddd;width:150px;text-align:left;"><?=$r['error_msg']?></td>
		<td style="border-top:1px solid #ddd;width:150px;text-align:left;"><?=$r['total'];?></td>
	</tr>
	<?php endforeach;?>
</table>
<?php endif;?>

