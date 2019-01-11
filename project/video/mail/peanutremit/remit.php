<?php
use yii\helpers\Html;
use app\models\Payorder;
?>

<?php if(is_array($remit)):?>
<div style="margin-top:20px;color:#cf0000;">
    <?=$start_time?> ~ <?=$end_time?>
    <br />
    <b>花生米富出款</b>
</div>

<table>
	<tr>
		<th style="border-top:1px solid #ddd;width:120px;text-align:left;">日期</th>
		<th style="border-top:1px solid #ddd;width:120px;text-align:left;">笔数</th>
		<th style="border-top:1px solid #ddd;width:120px;text-align:left;">出款金额</th>
		<th style="border-top:1px solid #ddd;width:120px;text-align:left;">成功率</th>
	</tr>

	<?php foreach($remit as $st): ?>
	<tr>
		<td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$st['create_day']?></td>
		<td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$st['num']?></td>
		<td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=number_format($st['success_money'],2)?>元</td>
		<td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=number_format($st['success_money']/$st['money']*100,2)?>%
		</td>
	</tr>
	<?php endforeach;?>

</table>
<?php endif;?>
