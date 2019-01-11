<?php
use yii\helpers\Html;
use app\models\Payorder;
?>

<?php if($idcards) :?>
<div style="margin-top:20px;color:#cf0000;">
<b>量化派身份验证近七日统计</b>
</div>
<table>
	<tr>
		<th style="border-top:1px solid #ddd;width:120px;text-align:left;">日期</th>
		<th style="border-top:1px solid #ddd;width:120px;text-align:left;">成功数</th>
		<th style="border-top:1px solid #ddd;width:120px;text-align:left;">失败数</th>
		<th style="border-top:1px solid #ddd;width:120px;text-align:left;">成功率</th>
	</tr>

	<?php if($idcards) foreach($idcards as $idcard):
		$total = intval($idcard['success']) + intval($idcard['fail']);
		?>
	<tr>
		<td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$idcard['create_day']?></td>
		<td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$idcard['success']?></td>
		<td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$idcard['fail']?></td>
		<td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=number_format($idcard['success']/$total * 100,2)?>%</td>
	</tr>
	<?php endforeach;?>
</table>

<br  />
<br  />
<?php endif;?>


<div style="margin-top:20px;color:#cf0000;">
<b>天行数科银行卡四要素近七日统计</b>
</div>
<table>
	<tr>
		<th style="border-top:1px solid #ddd;width:120px;text-align:left;">日期</th>
		<th style="border-top:1px solid #ddd;width:120px;text-align:left;">总数</th>
		<th style="border-top:1px solid #ddd;width:120px;text-align:left;">人数</th>
		<th style="border-top:1px solid #ddd;width:120px;text-align:left;">人均</th>
		<th style="border-top:1px solid #ddd;width:120px;text-align:left;">成功数</th>
		<th style="border-top:1px solid #ddd;width:120px;text-align:left;">总失败数</th>
		<th style="border-top:1px solid #ddd;width:120px;text-align:left;">总成功率</th>
		<th style="border-top:1px solid #ddd;width:120px;text-align:left;">接口失败数</th>
		<th style="border-top:1px solid #ddd;width:120px;text-align:left;">接口成功率</th>
	</tr>

	<?php if($bankvalids) foreach($bankvalids as $bank):
		$total = intval($bank['success']) + intval($bank['fail']);
		$idcards = $bank['idcards'] ? $bank['idcards'] : 0;
	?>
	<tr>
		<td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$bank['create_day']?></td>
		<td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$total;?></td>
		<td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$idcards ? $idcards : '-';?></td>
		<td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$idcards ? number_format($total/$idcards,2) : '-'?></td>
		<td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$bank['success']?></td>
		<td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$bank['fail']?></td>
		<td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=number_format($bank['success']/$total * 100,2)?>%</td>
		<td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=isset($bank['apifail']) ? $bank['apifail'] : '-'?></td>
		<td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?php 
		if($bank['apifail']){
			$apitotal =  intval($bank['success']) + intval($bank['apifail']);
			echo number_format($bank['success']/$apitotal * 100, 2) . '%';
		}else{
			echo '-';
		}?></td>
	</tr>
	<?php endforeach;?>
</table>


<?php if(is_array($remit)):
$aidNames = $remit['aidNames'];
$remitStatus = $remit['remitStatus'];

?>
<div style="margin-top:20px;color:#cf0000;">
	<div><b>中信出款昨日统计- <?=$day?></b></div>
</div>
<hr>
<?php if(isset($remit['aidStat'])):?>
<div style="margin-top:12px;color:#cf0000;">
	<div><b>出款总成功额</b></div>
</div>

<table>
	<tr>
		<th style="border-top:1px solid #ddd;width:120px;text-align:left;">应用名称</th>
		<th style="border-top:1px solid #ddd;width:120px;text-align:left;">成功出款金额</th>
	</tr>

	<?php 
	$aidStat = $remit['aidStat'];
	foreach($aidStat as $a): 
			$aidName = isset($aidNames[$a['aid']]) ? $aidNames[$a['aid']] :$a['aid'];
	?>
	<tr>
		<td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$aidName?></td>
		<td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=number_format($a['amount'],0)?>元</td>

	</tr>
	<?php endforeach;?>
</table>
<?php endif;?>

<?php if(is_array($remit) && isset($remit['statusStat'])):?>
<div style="margin-top:20px;color:#cf0000;">
	<div><b>按状态分组 </b></div>
</div>

<table>
	<tr>
		<th style="border-top:1px solid #ddd;width:120px;text-align:left;">出款状态</th>
		<th style="border-top:1px solid #ddd;width:120px;text-align:left;">笔数</th>
		<th style="border-top:1px solid #ddd;width:120px;text-align:left;">金额</th>
	</tr>

	<?php 
	$statusStat = $remit['statusStat'];
	 foreach($statusStat as $st): 
			$statusName = isset($remitStatus[$st['remit_status']]) ? $remitStatus[$st['remit_status']] : '-';
	?>
	<tr>
		<td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$statusName?></td>
		<td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$st['total']?></td>
		<td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=number_format($st['amount'],2)?>元</td>

	</tr>
	<?php endforeach;?>
</table>
<?php endif;?>

<?php if(is_array($remit) && isset($remit['failStat'])):?>
<div style="margin-top:20px;color:#cf0000;">
	<div><b>失败响应状态 </b></div>
</div>

<table>
	<tr>
		<th style="border-top:1px solid #ddd;width:120px;text-align:left;">响应状态</th>
		<th style="border-top:1px solid #ddd;width:120px;text-align:left;">响应原因</th>
		<th style="border-top:1px solid #ddd;width:120px;text-align:left;">状态</th>
		<th style="border-top:1px solid #ddd;width:120px;text-align:left;">失败笔数</th>
		<th style="border-top:1px solid #ddd;width:120px;text-align:left;">失败金额</th>
	</tr>
	<?php 
	$failStat = $remit['failStat'];
	foreach($failStat as $st): 
		$statusName = isset($remitStatus[$st['remit_status']]) ? $remitStatus[$st['remit_status']] : '-';
	?>
	<tr>
		<td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$st['rsp_status']?></td>
		<td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$st['rsp_status_text']?></td>
		<td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$statusName?></td>
		<td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$st['total']?></td>
		<td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=number_format($st['amount'],2)?>元</td>
	</tr>
	<?php endforeach;?>
</table>
<?php endif;?>
<?php endif;?>
