<?php
use yii\helpers\Html;
use app\models\Payorder;
?>

<div style="margin-top:20px;color:#cf0000;">
    <b>业务监控: 数据可能存在异常</b>
    <br />
    当前报告生成时间:<?=date('Y-m-d H:i:s');?>
</div>

<!-- start 出款一直在请求中状态 -->
<?php if($remitRate):?>
<div style="margin-top:20px;color:#cf0000;">
    <b>出款异常: 
    分析 <?=$remitRate['total']?> 条; 
    成功率为<?=number_format($remitRate['success_rate'], 2)?>;
    无响应为<?=$remitRate['not_200_num'];?>条
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

<!-- start 出款一直在请求中状态 -->
<?php if($remitReqing):?>
<div style="margin-top:20px;color:#cf0000;">
    <b>出款请求状态异常: 共 <?=count($remitReqing)?> 条</b>
</div>
<table>
    <tr>
        <th style="border-top:1px solid #ddd;width:120px;text-align:left;">ID</th>
        <th style="border-top:1px solid #ddd;width:320px;text-align:left;">内容</th>
    </tr>
    <?php if($remitReqing) foreach($remitReqing as $v):
    $content = "请求号:{$v['req_id']}<br />客户:{$v['client_id']}<br />一直在请求中!<br />创建时间{$v['create_time']}";
    ?>
    <tr>
        <td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$v['id']?></td>
        <td style="border-top:1px solid #ddd;width:320px;text-align:left;"><?=$content?></td>
    </tr>
    <?php endforeach;?>

</table>
<?php endif;?>
<!-- end 出款一直在请求中状态 -->


<!-- start 查询一直在请求中状态 -->
<?php if($queryReqing):?>
<div style="margin-top:20px;color:#cf0000;">
    <b>查询请求状态异常: 共 <?=count($queryReqing)?> 条</b>
</div>
<table>
    <tr>
        <th style="border-top:1px solid #ddd;width:120px;text-align:left;">ID</th>
        <th style="border-top:1px solid #ddd;width:320px;text-align:left;">内容</th>
    </tr>
    <?php if($queryReqing) foreach($queryReqing as $v):
    $content = "请求号:{$v['req_id']}<br />客户:{$v['client_id']}<br />一直在请求中!<br />查询时间{$v['query_time']}";
    ?>
    <tr>
        <td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$v['id']?></td>
        <td style="border-top:1px solid #ddd;width:320px;text-align:left;"><?=$content?></td>
    </tr>
    <?php endforeach;?>

</table>
<?php endif;?>
<!-- end 出款一直在请求中状态 -->



<!-- start 查询请求中状态的数据 -->
<?php if($queryMax):?>
<div style="margin-top:20px;color:#cf0000;">
    <b>查询接口达上限: 共 <?=count($queryMax)?> 条</b>
</div>
<table>
    <tr>
        <th style="border-top:1px solid #ddd;width:120px;text-align:left;">ID</th>
        <th style="border-top:1px solid #ddd;width:320px;text-align:left;">内容</th>
    </tr>
    <?php if($queryMax) foreach($queryMax as $v):
    $content = "请求号:{$v['req_id']}<br />客户:{$v['client_id']}<br />查询重试达上限!<br />最后时间{$v['query_time']}";
    ?>
    <tr>
        <td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$v['id']?></td>
        <td style="border-top:1px solid #ddd;width:320px;text-align:left;"><?=$content?></td>
    </tr>
    <?php endforeach;?>

</table>
<?php endif;?>
<!-- end 查询请求中状态的数据 -->



<!-- start 通知次数超限 -->
<?php if($notifyMax):?>
<div style="margin-top:20px;color:#cf0000;">
    <b>通知次数达上限: 共 <?=count($notifyMax)?> 条</b>
</div>
<table>
    <tr>
        <th style="border-top:1px solid #ddd;width:120px;text-align:left;">ID</th>
        <th style="border-top:1px solid #ddd;width:320px;text-align:left;">内容</th>
    </tr>
    <?php foreach($notifyMax as $v):
        $remit = $v['remit'];
        $content = "请求号:{$remit['req_id']}<br />客户:{$remit['client_id']}<br />无法通知状态({$v['remit_status']})!<br />最后时间{$v['notify_time']}";
    ?>
    <tr>
        <td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$remit['id']?></td>
        <td style="border-top:1px solid #ddd;width:320px;text-align:left;"><?=$content?></td>
    </tr>
    <?php endforeach;?>
</table>
<?php endif;?>
<!-- end 通知次数超限 -->


