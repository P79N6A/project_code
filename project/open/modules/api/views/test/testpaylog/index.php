<div class="row">
	<div class="col-md-6">
<!--
	作者：lijin_1221@163.com
	时间：2015-11-03
	描述：
-->	
<style>
.testquick a{font-size:24px;text-decoration:none;color:#3c75af;}
</style>	

<br />
<p class="text-primary"><?=$day?>订单总数 <?=$allTotal?></p>
<table class="table">
	<tr>
		<th>支付通道</th>
		<th>状态</th>
		<th>条数</th>
		<th>百分比</th>
	</tr>

	<?php if($allTotal) foreach($typeTotal as $type):?>
	<tr>
		<td><?=$paytypeArr[$type['pay_type']]?></td>
		<td><?=$statusArr[$type['status']];?></td>
		<td><?=$type['total']?></td>
		<td><?=number_format($type['total']/$allTotal * 100,2)?>%</td>
	</tr>
	<?php endforeach;?>
</table>		

<p class="text-primary">绑卡失败数：<?php echo $bindTotal;?></p>
<table class="table">
	<tr>
		<th>卡号</th>
		<th>错误码</th>
		<th>错误原因</th>
		<th>当前状态</th>
		<th>次数</th>
	</tr>

	<?php if($allTotal) foreach($bindData as $r):?>
	<tr>
		<td><?=$r['cardno'];?></td>
		<td><?=$r['error_code']?></td>
		<td><?=$r['error_msg']?></td>
		<td><?=$r['status'];?></td>
		<td><?=$r['total'];?></td>
	</tr>
	<?php endforeach;?>
</table>


<p class="text-primary">投资通失败数：<?php echo $tztTotal;?></p>
<table class="table">
	<tr>
		<th>错误码</th>
		<th>错误原因</th>
		<th>当前状态</th>
		<th>次数</th>
	</tr>

	<?php if($tztTotal) foreach($tztData as $r):?>
	<tr>
		<td><?=$r['error_code']?></td>
		<td><?=$r['error_msg']?></td>
		<td><?=$r['status'];?></td>
		<td><?=$r['total'];?></td>
	</tr>
	<?php endforeach;?>
</table>



<p class="text-primary">一键支付失败数：<?php echo $quickTotal;?></p>
<table class="table">
	<tr>
		<th>错误码</th>
		<th>错误原因</th>
		<th>当前状态</th>
		<th>次数</th>
	</tr>

	<?php if($quickTotal) foreach($quickData as $r):?>
	<tr>
		<td><?=$r['error_code']?></td>
		<td><?=$r['error_msg']?></td>
		<td><?=$r['status'];?></td>
		<td><?=$r['total'];?></td>
	</tr>
	<?php endforeach;?>
</table>



	
	</div>
</div>
