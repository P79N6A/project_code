<?php
use yii\helpers\Html;
use app\models\Payorder;
?>

<div style="margin-top:20px;color:#cf0000;">
<b>系统监控: 数据可能存在异常</b>
<br />
当前报告生成时间:<?=date('Y-m-d H:i:s');?>
</div>
<table>

    <?php if($request_total==0):  ?>
    <tr>
        <td style="border-top:1px solid #ddd;width:320px;text-align:left;">出款表为空: 表明未接收到数据</td>
    </tr>
    <?php endif;?>

    <?php if($api_total==0):  ?>
    <tr>
        <td style="border-top:1px solid #ddd;width:320px;text-align:left;">中信接口表为空: 表明没有请求</td>
    </tr>
    <?php endif;?>

    <?php if($notify_total==0):  ?>
    <tr>
        <td style="border-top:1px solid #ddd;width:320px;text-align:left;">通知表为空: 表明没有回调行为</td>
    </tr>
    <?php endif;?>

</table>

 <?php if($api_timeouts) : ?>
<div style="margin-top:20px;color:#cf0000;">
<b>接口无响应列表如下</b>

</div>
<table>
    <tr>
        <th style="border-top:1px solid #ddd;width:120px;text-align:left;">ID</th>
        <th style="border-top:1px solid #ddd;width:120px;text-align:left;">所在接口</th>
        <th style="border-top:1px solid #ddd;width:120px;text-align:left;">响应码</th>
        <th style="border-top:1px solid #ddd;width:120px;text-align:left;">响应信息</th>
        <th style="border-top:1px solid #ddd;width:120px;text-align:left;">出款前一状态</th>
        <th style="border-top:1px solid #ddd;width:120px;text-align:left;">出款当前状态</th>
    </tr>


    <?php 
    $apiNames = [1=>'出款接口', 2=> '查询接口'];
    foreach($api_timeouts as $v):
        $apiName =  isset($apiNames[$v['type']]) ? $apiNames[$v['type']] : '-';
        $pre_status_txt = $remitStatus[$v['pre_status']];
        $status_txt  = $remitStatus[$v['status']];
    ?>
    <tr>
        <td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$v['remit_id']?></td>
        <td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$apiName;?></td>
        <td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$v['rsp_status'];?></td>
        <td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$v['rsp_status_text'];?></td>
        <td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$pre_status_txt?></td>
        <td style="border-top:1px solid #ddd;width:120px;text-align:left;"><?=$status_txt?></td>
    </tr>
    <?php endforeach;?>

</table>
<?php endif;?>