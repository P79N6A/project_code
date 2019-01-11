<?php

use yii\widgets\LinkPager;
use \yii\helpers\ArrayHelper;
$this->title = "米富对账管理";
$status      = \app\models\Business::getStatus();
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5><?=$this->title. '>'?>一亿元统计管理>一亿元前置服务费</h5>
        </header>
        <div class="body">
            <form action="?" method="get">
                <div style="margin-bottom: 10px">
                    账单日期 <input style="margin-right: 10px;" type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="start_time" value="<?=ArrayHelper::getValue($getData, 'start_time', date("Y-m-d"))?>"  /> ~
                    <input style="margin-left: 10px;"  type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="end_time"  value="<?=ArrayHelper::getValue($getData, 'end_time', date("Y-m-d"))?>" />

                </div>
                <div style="text-align: center">
                    <input type="reset" value="重置"  class="btn btn-primary">
                    <input style="margin-left: 20px;"  type="submit" value="查询" id="search_submit"  class="btn btn-primary">
                </div>
                <hr />
            </form>
            <div style="margin-bottom: 10px">
                <span>总笔数：<b style="color: red"><?=$all_total?></b>笔</span>
                <span style="margin-left: 20px;">总金额：<b style="color: red">￥<?=$all_money?></b> 元</span>
            </div>

            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                <tr role="row">
                    <th>序号</th>
                    <th>账单日期</th>
                    <th>总笔数</th>
                    <th>总金额</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if (!empty($result)) {
                    $num = 0;
                    foreach ($result as $value) {
                        $num ++;
                        ?>
                        <tr role="row">
                            <td><?=$num?></td>
                            <td><?=ArrayHelper::getValue($value, 'bill_date')?></td>
                            <td><?=ArrayHelper::getValue($value, 'total')?></td>
                            <td><?=ArrayHelper::getValue($value, 'sum')?></td>
                            <td style="text-align: center">
                                <a href="/balance/service/down?bill_date=<?=ArrayHelper::getValue($value, 'bill_date')?>">导出明细</a>
                            </td>
                        </tr>
                        <?php
                    }
                }else {
                    ?>
                    <tr role="row">
                        <td colspan="5" style="text-align: center">暂无数据！</td>
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