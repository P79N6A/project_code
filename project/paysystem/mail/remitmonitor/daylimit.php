<?php
use yii\helpers\Html;
use app\models\Payorder;
?>

<div style="margin-top:20px;color:#cf0000;">
    当前报告生成时间:<?=date('Y-m-d H:i:s');?>
    <br />
    <b>每日限额: 即将或已超限</b>
</div>

<?php if(isset($dayLimits)):?>
<div style="margin-top:12px;color:#cf0000;">
    <div><b>出款总成功额</b></div>
</div>

<table>
    <tr>
        <th style="border-top:1px solid #ddd;width:120px;text-align:left;">业务</th>
        <th style="border-top:1px solid #ddd;width:120px;text-align:left;">每日配额</th>
        <th style="border-top:1px solid #ddd;width:120px;text-align:left;">实际出款</th>
        <th style="border-top:1px solid #ddd;width:120px;text-align:left;">剩余金额</th>
        <th style="border-top:1px solid #ddd;width:120px;text-align:left;">说明</th>
    </tr>

    <?php 
    foreach($dayLimits as $a): 
            $aidName = isset($aidNames[$a['aid']]) ? $aidNames[$a['aid']] :$a['aid'];
    ?>
    <tr>
        <td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$aidName?></td>
        <td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=number_format($a['day_max_mount'],0)?>元</td>
        <td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=number_format($a['settle_amount'],0)?>元</td>
        <td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=number_format($a['diffMoney'],0)?>元</td>
        <td style="border-top:1px solid #ddd;width:120px;text-align:left;">
        <?php if($a['diffMoney'] > 0):
        $limit_money = $a['aid'] == 4 ? 50000 : 1000;
        ?>
            剩余金额已经不足<?=$limit_money?>元,即将超限
        <?php else:?>
             超限
        <?php endif;?>
        </td>

    </tr>
    <?php endforeach;?>
</table>
<?php endif;?>