<?php
?>
<div style="margin-top:20px;color:#cf0000;">
    <?=$begin_time?> ~ <?=$end_time?>
    <br />
    <b>玖富出款异常订单邮件</b>
</div>

<?php 
$all = [
	['name'=>'未提交的异常', 'data'=>$notSubmits],
	['name'=>'锁定状态异常', 'data'=>$locks],
	['name'=>'查询超限异常', 'data'=>$queryLimits],
];
foreach($all as $v): 
?>

<?php if (!empty($v['data'])): ?>
<div style="margin-top:20px;color:#cf0000;">
    <b><?=$v['name']?></b>
</div>
<table>
<tr>
	<th style="border-top:1px solid #ddd;width:120px;text-align:left;">订单创建时间</th>
	<th style="border-top:1px solid #ddd;width:120px;text-align:left;">姓名</th>
	<th style="border-top:1px solid #ddd;width:220px;text-align:left;">请求号:一亿元/玖富</th>
	<th style="border-top:1px solid #ddd;width:120px;text-align:left;">出款金额</th>
	<th style="border-top:1px solid #ddd;width:120px;text-align:left;">状态码</th>
	<th style="border-top:1px solid #ddd;width:120px;text-align:left;">玖富状态码</th>
</tr>

<?php foreach ($v['data'] as $st): 
	$remit_status_txt = isset($remit_status[$st['remit_status']]) ? $remit_status[$st['remit_status']] : $st['remit_status'];
	$rsp_status_txt = isset($rsp_status[$st['rsp_status']]) ? $rsp_status[$st['rsp_status']] : $st['rsp_status'];
?>
<tr>
	<td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$st['create_time']?></td>
	<td style="border-top:1px solid #ddd;width:120px;text-align:left;">
	<?=$st['guest_account_name']?>
	<br />
	<?=$st['user_mobile']?>
	</td>
	<td style="border-top:1px solid #ddd;width:120px;text-align:left;">
	一亿元: <?=$st['req_id']?>
	<br />
	玖富: <?=$st['order_id']?>
	</td>
	<td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=number_format($st['settle_amount'], 0)?>元</td>
	<td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?php 
	if($remit_status_txt && $remit_status_txt !=$st['remit_status'] ) {
		echo $remit_status_txt. ':' .$st['remit_status'];
	}else{
		echo $st['remit_status'];
	}
	?></td>	
	<td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?php 
	if($rsp_status_txt && $rsp_status_txt !=$st['rsp_status'] ) {
		echo $rsp_status_txt. ':' .$st['rsp_status'];
	}else{
		echo $st['rsp_status'];
	}
	?></td>
</tr>
<?php endforeach;?>

</table>
<?php endif;?>
<?php endforeach;?>
