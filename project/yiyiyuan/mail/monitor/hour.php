<div style="margin-top:20px;color:#cf0000;">
    <b>时间段: <?=date('Y-m-d H', strtotime($start_time))?>-<?=date('H', strtotime($end_time))?>点</b>
</div>

<?php if($regs) :?>
<div style="margin-top:20px;color:#cf0000;">
<b>注册统计</b>
</div>
<table>
    <tr>
        <th style="border-top:1px solid #ddd;width:120px;text-align:left;">设备</th>
        <th style="border-top:1px solid #ddd;width:120px;text-align:left;">数量</th>
    </tr>

    <?php 
        foreach($regs as $name=>$total): 
            $style = $total == 0 ? "color:#cf0000;" : "";
    ?>
    <tr>
        <td style="border-top:1px solid #ddd;width:120px;text-align:left;<?=$style?>"><?=$name?></td>
        <td style="border-top:1px solid #ddd;width:120px;text-align:left;<?=$style?>"><?=$total?></td>
    </tr>
    <?php endforeach;?>
</table>
<?php else:?>
<div style="margin-top:20px;color:#cf0000;"><b>注册可能存在异常</b></div>
<?php endif;?>


<?php if($loans) :?>
<div style="margin-top:20px;color:#cf0000;">
<b>借款统计</b>
</div>
<table>
    <tr>
        <th style="border-top:1px solid #ddd;width:120px;text-align:left;">设备</th>
        <th style="border-top:1px solid #ddd;width:120px;text-align:left;">数量</th>
    </tr>

    <?php 
        foreach($loans as $name=>$total): 
            $style = $total == 0 ? "color:#cf0000;" : "";
    ?>
    <tr>
        <td style="border-top:1px solid #ddd;width:120px;text-align:left;<?=$style?>"><?=$name?></td>
        <td style="border-top:1px solid #ddd;width:120px;text-align:left;<?=$style?>"><?=$total?></td>
    </tr>
    <?php endforeach;?>
</table>
<?php else:?>
<div style="margin-top:20px;color:#cf0000;"><b>借款可能存在异常</b></div>
<?php endif;?>

