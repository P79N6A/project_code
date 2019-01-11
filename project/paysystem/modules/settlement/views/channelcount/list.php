<?php

use yii\widgets\LinkPager;

$this->title = "出款账单管理";
$status      = \app\models\Business::getStatus();
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5><?=$this->title. '>'?>出款统计</h5>
        </header>
        <div class="body">
            <form action="?" method="get">
            <div style="margin-bottom: 10px">
                账单日期：<input style="margin-right: 10px;" type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="start_time" value="<?=$start_time?>"  /> ~
                          <input style="margin-left: 10px;"  type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="end_time"  value="<?=$end_time?>" />
                          <input style="margin-left: 10px;"  type="submit" value="查询" id="search_submit"  class="btn btn-primary">
                <hr />
            </div>
            </form>
            <div style="margin-bottom: 10px">
                <span style="margin-right: 15px">对账成功总笔数：<b style="color: red"><?=$total?></b>笔</span>
                <span style="margin-right: 15px">总金额：<b style="color: red">￥<?=$total_money?></b> 元</span>
                <span style="margin-right: 15px">总手续费：<b style="color: red">￥<?=$total_fee?></b> 元</span>
                <span style="margin-right: 15px">差错账总笔数：<b style="color: red"><?=$total_bill_error?></b> 笔</span>
            </div>
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                    <tr role="row">
                        <th>序号</th>
                        <th>账单日期</th>
                        <th>总笔数</th>
                        <th>总金额/元</th>
                        <th>总手续费/元</th>
                        <th>差错账笔数</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if (!empty($result)){
                    $i = 0;
                    foreach($result as $value){
                        $i++;
                ?>
                <tr role="row">
                    <td><?=$i?></td>
                    <td><?=date("Y-m-d", strtotime($value['bill_number']))?></td>
                    <td><?=$value['total_num']?></td>
                    <td><?=$value['total_money']?>/元</td>
                    <td><?=$value['total_fee']?>/元</td>
                    <td><?=$value['total_error_bill']?></td>
                    <td><?=$value['create_time']?></td>
                    <td><a href="/settlement/channelcount/datelist?bill_number=<?=$value['bill_number']?>">详情</a></td>
                </tr>
                <?php
                    }
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