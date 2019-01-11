<?php

use yii\widgets\LinkPager;
use \yii\helpers\ArrayHelper;
$this->title = "出款账单管理";
$status      = \app\models\Business::getStatus();
?>
<style type="text/css">
    /*显示浮层*/
    .mask-show{
        width: 100%;
        transition-duration: 1s;
    }
</style>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5><?=$this->title. '>'?>出款统计</h5>
            <span id="click_href_up"  class="btn btn-primary" style="margin: 2px 10px 0 0;float: right;">返回</span>
        </header>
        <div class="body">
            <form action="?" method="get">
            <div style="margin-bottom: 10px">
                <input type="hidden" name="bill_number" value="<?=$bill_number?>" />
                出款通道名称：
                <select name="channel_name" style="margin-right:20px">
                    <option value="0">请选择</option>
                    <?php
                    foreach($passageOfMoney as $key=>$value){
                    ?>
                    <option value="<?=$key?>" <?=($key == $channel_name) ? 'selected = "selected"' : ""?>><?=$value?></option>
                    <?php
                    }
                    ?>
                </select>
                通道商编号：<input name="client_number" value="<?=$client_number?>" />
                <input style="margin-left: 10px;"  type="submit" value="查询" id="search_submit"  class="btn btn-primary">
                <hr />
            </div>
            </form>
            <div style="margin-bottom: 10px">
                <span style="margin-right: 15px">总笔数：<b style="color: red"><?=$total?></b>笔</span>
                <span style="margin-right: 15px">总金额：<b style="color: red">￥<?=$total_money?></b> 元</span>
                <span style="margin-right: 15px">总手续费：<b style="color: red">￥<?=$total_fee?></b> 元</span>
                <span style="margin-right: 15px">差错账总笔数：<b style="color: red"><?=$total_bill_error?></b> 笔</span>
            </div>
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                    <tr role="row">
                        <th>序号</th>
                        <th>出款通道名称</th>
                        <th>总笔数</th>
                        <th>总金额/元</th>
                        <th>总手续费/元</th>
                        <th>差错账笔数</th>
                        <th>账单日期</th>
                        <th>创建时间</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if (!empty($result)){
                    $i = 0;
                    foreach($result as $value){
                        $i ++;
                ?>
                <tr role="row">
                    <td><?=$i?></td>
                    <td><?php
                        echo ArrayHelper::getValue($value, 'channel_name', '');
                        if (!empty(ArrayHelper::getValue($value, 'channel_num', 0))){
                            echo '<img style="margin-left: 10px;" onclick="show_div('.$value["channel_id"].')" class="down_show" src="/img/down.png">';
                        }
                        ?>
                    </td>
                    <td><?=ArrayHelper::getValue($value, 'total', '')?></td>
                    <td><?=ArrayHelper::getValue($value, 'total_money', '')?>/元</td>
                    <td><?=ArrayHelper::getValue($value, 'total_fee', '')?>/元</td>
                    <td><?=ArrayHelper::getValue($value, 'total_bill_error', '')?></td>
                    <td><?=ArrayHelper::getValue($value, 'bill_number', '')?></td>
                    <td><?=ArrayHelper::getValue($value, 'create_time', '')?></td>
                </tr>
                <?php
                    }
                }
                ?>
                </tbody>                
            </table>
            <div id="show_float" style="display:none; float:left; left: 40px;  position: relative;top: -40px; background-color: #dddddd; width: 95%;padding-bottom: 10px">

            </div>
            <input name="bill_number" type="hidden" id="bill_number" value="<?=$bill_number?>">
            <input name="_csrf" type="hidden" id="_csrf" value="<?= \Yii::$app->request->csrfToken ?>">
            <div class="panel_pager">
                <?php echo LinkPager::widget(['pagination' => $pages]); ?>
            </div>
        </div>
    </div>

</div>

<script src="/bootstrap/js/bootstrap.min.js" type="text/javascript" charset="utf-8"></script>
<script>
    var bill_number = $("input[name='bill_number']").val();
    function show_div(channel_id){
        var _csrf = $("#_csrf").val();
        var bill_number = $("#bill_number").val();
        var channel_offset = ((channel_id - 1) * 30)-50;
        $.ajax({
            type:'post',
            url: "/backstage/channelcount/channellist",
            dataType: "json",
            data:{'_csrf':_csrf, 'bill_number':bill_number, 'channel_id':channel_id},
            success: function (msg) {
                var html = '<table style="margin: 12px; width: 97%" class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">';
                for (var i=0; i<msg.data.length; i++){
                    html += '<tr role="row">';
                    html += '<td>'+msg.data[i].channel_name+'+'+msg.data[i].child_channel_id+'</td>';
                    html += '<td>'+msg.data[i].total+'</td>';
                    html += '<td>'+msg.data[i].total_money+'</td>';
                    html += '<td>'+msg.data[i].total_fee+'</td>';
                    html += '<td>'+msg.data[i].total_bill_error+'</td>';
                    html += '<td>'+msg.data[i].bill_number+'</td>';
                    html += '<td>'+msg.data[i].create_time+'</td>';
                    html += '</tr>';
                }
                html += '</table>';

                $("#show_float").children().remove();
                $("#show_float").show();
                $("#show_float").css({'top':channel_offset});
                $("#show_float").append(html);
            }
        });
    }
    $(function(){
        $("#click_href_up").click(function(){
            history.go(-1);
        });

        $("#show_float").click(function(){
            $("#show_float").hide();
        });
    })


</script>