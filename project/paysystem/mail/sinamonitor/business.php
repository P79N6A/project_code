
<div style="margin-top:20px;color:#cf0000;">
    <b>新浪监控</b>
    <br />
    当前报告生成时间:<?=date('Y-m-d H:i:s');?>
</div>

<!-- start 出款一直在请求中状态 -->
<?php if($remitRate):?>
<div style="margin-top:20px;color:#cf0000;">
    <b>出款异常: 
    分析 <?=$remitRate['total']?> 条; 
    成功率为<?=number_format($remitRate['success_rate'], 2)?>;
    <br />
    最近纪录如下:
    </b>
</div>
<table>
    <tr>
        <th style="border-top:1px solid #ddd;width:120px;text-align:left;">ID</th>
        <th style="border-top:1px solid #ddd;width:120px;text-align:left;">req_id</th>
        <th style="border-top:1px solid #ddd;width:120px;text-align:left;">client_id</th>
        <th style="border-top:1px solid #ddd;width:120px;text-align:left;">状态</th>
        <th style="border-top:1px solid #ddd;width:120px;text-align:left;">响应码</th>
        <th style="border-top:1px solid #ddd;width:120px;text-align:left;">原因</th>
    </tr>
    <?php if($remitRate['data']) foreach($remitRate['data'] as $v):
            $status_txt  = $remitStatus[$v['remit_status']];
    ?>
    <tr>
        <td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$v['id']?></td>
        <td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$v['req_id']?></td>
        <td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$v['client_id']?></td>
        <td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$status_txt?></td>
        <td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$v['rsp_status']?></td>
        <td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$v['rsp_status_text']?></td>
    </tr>
    <?php endforeach;?>

</table>
<?php endif;?>
<!-- end 出款一直在请求中状态 -->

<!-- start 出款失败 -->
<?php if($remitRest):?>
<div style="margin-top:20px;color:#cf0000;">
    <b>出款失败: 共 <?=$remitRest?> 条</b>
</div>
<?php endif;?>
<!-- end 出款失败 -->


