<?php

use yii\widgets\LinkPager;
use \yii\helpers\ArrayHelper;
$this->title = "首页>代付管理";
$status      = \app\models\Business::getStatus();
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5><?=$this->title. '>'?>代付通道统计</h5>
        </header>
        <div class="body">
            <form action="?" method="get">
            <div style="margin-bottom: 10px">
                商户订单号：<input type="text" name="order_id" value="<?=$order_id?>" style="margin-right: 10px"/>
                回款通道：  <select style="margin-right: 10px" name="return_channel" id="return_channel">
                    <option value="">请选择</option>
                                <?php
                                    foreach($return_channel as $key => $value) {
                                        ?>
                                        <option
                                            <?php
                                                if ($return_channel_id == $key){
                                                    echo "selected = 'selected'";
                                                }
                                            ?>
                                            value="<?=$key?>"><?=$value?></option>
                                        <?php
                                    }
                                ?>
                            </select>
                产品名称：  <select style="margin-right: 10px" name="aid" id="aid">
                    <option value="">请选择</option>
                    <?php
                    foreach($aid as $key => $value) {
                        ?>
                        <option
                            <?php
                            if ($name_aid == $key){
                                echo "selected = 'selected'";
                            }
                            ?>
                                value="<?=$key?>"><?=$value?></option>
                        <?php
                    }
                    ?>
                </select>
                状态：  <select style="margin-right: 10px" name="state_id" id="state_id">
                    <option value="">请选择</option>
                    <?php
                    foreach($state as $key => $value) {
                        ?>
                        <option
                            <?php
                            if ($state_id == $key){
                                echo "selected = 'selected'";
                            }
                            ?>
                                value="<?=$key?>"><?=$value?></option>
                        <?php
                    }
                    ?>
                </select>
                通道ID：<input type="text" name="series" value="<?=$series?>" style="margin-right: 10px"/>
                账单日期：<input style="margin-right: 10px;" type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="start_time" value="<?=$start_time?>"  /> ~
                          <input style="margin-left: 10px;"  type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="end_time"  value="<?=$end_time?>" />
                对账创建日期：<input style="margin-right: 10px;" type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="create_time" value="<?=$create_time?>"  /> ~
                <input style="margin-left: 10px;"  type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="create_times"  value="<?=$create_times?>" />

            </div>
            <div style="text-align: center">
                <input type="reset" value="重置"  class="btn btn-primary">
                <input style="margin-left: 20px;"  type="submit" value="查询" id="search_submit"  class="btn btn-primary">
                <input style="margin-left: 20px;"  type="button" value="导出" id="import_data" class="btn btn-primary">
            </div>
            <hr />
            </form>
            <div style="margin-bottom: 20px">
                <span style="margin-right: 40px">成功总笔数：<b style="color: red;"><?=$success_total?></b>笔</span>
                <span style="margin-right: 40px">成功总金额：<b style="color: red;">￥<?=Number_format($success_amount,2)?></b>元</span>
                <span style="margin-right: 40px">成功总手续费：<b style="color: red;">￥<?=Number_format($success_fee,2)?></b> 元</span>
                <span style="margin-right: 40px">差错总笔数：<b style="color: red;"><?=$fial_total?></b>笔</span>
                <span style="margin-right: 40px">差错总金额：<b style="color: red;">￥<?=Number_format($fial_amount,2)?></b>元</span>
                <span style="margin-right: 40px">差错总手续费：<b style="color: red;">￥<?=Number_format($fial_fee,2)?></b>元</span>
            </div>
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                    <tr role="row">
                        <th>序号</th>
                        <th>商户订单号</th>
                        <th>通道名称</th>
                        <th>通道ID</th>
                        <th>产品名称</th>
                        <th>金额</th>
                        <th>手续费</th>
                        <th>请求时间</th>
                        <th>完成时间</th>
                        <th>对账创建时间</th>
                        <th>账单日期</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    if (!empty($return_data)) {
                        $num = 0;
                        foreach($return_data as $value) {
                            $num ++;
                            ?>
                            <tr role="row">
                                <td><?=$num;?></td>
                                <td><?=ArrayHelper::getValue($value, 'client_id','')?></td>
                                <td><?=ArrayHelper::getValue($value, 'return_channel','')?></td>
                                <td><?=ArrayHelper::getValue($value, 'channel_id','')?></td>
                                <td><?=ArrayHelper::getValue($value, 'aid','')?></td>
                                <td><?=ArrayHelper::getValue($value, 'amount','')?></td>
                                <td><?=ArrayHelper::getValue($value, 'settle_fee','')?></td>
                                <td><?=ArrayHelper::getValue($value, 'create_time','')?></td>
                                <td><?=ArrayHelper::getValue($value, 'modify_time','')?></td>
                                <td><?=ArrayHelper::getValue($value, 'collection_time','')?></td>
                                <td><?=ArrayHelper::getValue($value, 'payment_date','')?></td>
                                <td><?=ArrayHelper::getValue($value, 'type','')?></td>
                                <td><a href="/balance/paidcount/details?client_id=<?=ArrayHelper::getValue($value, 'client_id')?>">详情</a></td>
                            </tr>
                            <?php
                        }
                    }else {
                        ?>
                        <tr role="row" style="text-align: center">
                            <td colspan="13">暂无数据</td>
                        </tr>
                        <?php
                    }
                ?>
                </tbody>                
            </table>
            <div class="panel_pager">
                <?php echo LinkPager::widget(['pagination' => $pages]); ?>
            </div>
        </div>
    </div>
</div>
<script src="/bootstrap/js/bootstrap.min.js" type="text/javascript" charset="utf-8"></script>
<script src="/laydate/laydate.dev.js" type="text/javascript" charset="utf-8"></script>
<script>
    $(function(){
        $("#search_submit").click(function(){
            var start_time = $("input[name='start_time']").val();
            var end_time = $("input[name='end_time']").val();
            if (start_time > end_time){
                alert("查询时间有误 ");
                return false;
            }
        });

        //导出表格
        $("#import_data").click(function(){
            var order_id = $("input[name='order_id']").val();//借款编号
            var return_channel = $("#return_channel").change().val();// 回款通道
            var aid = $("#aid").change().val(); // aid
            var series = $("input[name='series']").val(); //手机号
            var start_time = $("input[name='start_time']").val(); //账单日期
            var end_time = $("input[name='end_time']").val();//账单日期
            var create_time = $("input[name='create_time']").val(); //账单日期
            var create_times = $("input[name='create_times']").val();//账单日期
            location.href = '/balance/paidcount/downdata?&order_id='+ order_id + "&return_channel=" + return_channel + "&aid=" + aid + "&series=" + series + "&start_time=" + start_time + "&end_time=" + end_time + "&create_time=" + create_time + "&create_times=" + create_times;
        });
    });
</script>