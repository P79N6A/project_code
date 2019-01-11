<?php

use yii\widgets\LinkPager;
use \yii\helpers\ArrayHelper;
$this->title = "米富逾期对账管理";
$status      = \app\models\Business::getStatus();
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5><?=$this->title. '>'?>逾期回款通道统计</h5>
        </header>
        <div class="body">
            <form action="?" method="get">
            <div style="margin-bottom: 10px">
                回款通道：  <select style="margin-right: 10px" name="return_channel">
                                        <option value="0">请选择</option>
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
                通道商编号：<input type="text" name="series" value="<?=$series?>" style="margin-right: 10px"/>
                账单日期：<input style="margin-right: 10px;" type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="start_time" value="<?=$start_time?>"  /> ~
                          <input style="margin-left: 10px;"  type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="end_time"  value="<?=$end_time?>" />

            </div>
            <div style="text-align: center">
                <input type="reset" value="重置"  class="btn btn-primary">
                <input style="margin-left: 20px;"  type="submit" value="查询" id="search_submit"  class="btn btn-primary">
            </div>
            <hr />
            </form>
            <div style="margin-bottom: 20px">
                <span style="margin-right: 40px">成功总笔数：<b style="color: red;"><?=$success_total?></b>笔</span>
                <span style="margin-right: 40px">成功总金额：<b style="color: red;">￥<?=Number_format($success_amount,2)?></b>元</span>
                <span style="margin-right: 40px">成功总手续费：<b style="color: red;">￥<?=Number_format($success_fee,2)?></b> 元</span>
                <span style="margin-right: 40px">差错总笔数：<b style="color: red;"><?=$fial_total?></b>笔</span>
                <span style="margin-right: 40px">差错总金额：<b style="color: red;">￥<?=Number_format($fial_amount,2)?></b>元</span>
                <span style="margin-right: 40px">差错账手续费：<b style="color: red;">￥<?=Number_format($fial_fee,2)?></b>元</span>
            </div>
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                    <tr role="row">
                        <th>序号</th>
                        <th>回款通道</th>
                        <th>成功总笔数</th>
                        <th>成功总金额</th>
                        <th>成功总手续</th>
                        <th>差错账总笔数</th>
                        <th>差错账总金额</th>
                        <th>差错账总手续费</th>
                        <th>账单日期</th>
                        <th>创建时间</th>
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
                                <td><?=ArrayHelper::getValue($value, 'return_channel','')?></td>
                                <td><?=ArrayHelper::getValue($value, 'success_total','')?></td>
                                <td><?=ArrayHelper::getValue($value, 'success_money','')?></td>
                                <td><?=ArrayHelper::getValue($value, 'success_fee','')?></td>
                                <td><?=ArrayHelper::getValue($value, 'error_total','')?></td>
                                <td><?=ArrayHelper::getValue($value, 'error_money','')?></td>
                                <td><?=ArrayHelper::getValue($value, 'error_fee','')?></td>
                                <td><?=ArrayHelper::getValue($value, 'payment_date','')?></td>
                                <td><?=ArrayHelper::getValue($value, 'create_time','')?></td>
                                <td><a href="/balance/channelcount/paymentdown?channel_id=<?=ArrayHelper::getValue($value, 'channel_id')?>&payment_date=<?=ArrayHelper::getValue($value, 'payment_date','')?>">导出明细</a></td>
                            </tr>
                            <?php
                        }
                    }else {
                        ?>
                        <tr role="row" style="text-align: center">
                            <td colspan="11">暂无数据</td>
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
    });
</script>