<?php

use yii\widgets\LinkPager;
use \yii\helpers\ArrayHelper;
$this->title = "对账列表";
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5><?=$this->title?></h5>
        </header>
        <div class="body">
            <form action="/policyment/policybill/list" method="get">
            <div style="margin-bottom: 10px">
            <div class="col-lg-2" style="width: auto">
                      
                账单日期：<input style="margin-right: 10px;" type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="start_time" value="<?=isset($get['start_time'])?$get['start_time']:''?>"  /> ~
                          <input style="margin-left: 10px;"  type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="end_time"  value="<?=isset($get['end_time'])?$get['end_time']:''?>" />

            </div>
            <div style="text-align: center">
                    <input type="reset" value="重置"  class="btn btn-primary">
                    <input style="margin-left: 20px;margin-right:20px"  type="submit" value="查询" id="search_submit"  class="btn btn-primary">
                    <a href="/policyment/policybill/exportbill?start_time=<?=ArrayHelper::getValue($get,'start_time','')?>&end_time=<?=ArrayHelper::getValue($get,'end_time','')?>" class="btn btn-primary" style="margin-right:20px" >导出费用</a>
                    <a href="/policyment/policybill/exportdetail?start_time=<?=ArrayHelper::getValue($get,'start_time','')?>&end_time=<?=ArrayHelper::getValue($get,'end_time','')?>" class="btn btn-primary">导出数据</a>
                </div>
            <hr />
            </form>
            
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                    <tr role="row">
                        <th>序号</th>
                        <th>对账日期</th>
                        <th>出单笔数</th>
                        <th>出单金额</th>
                        <th>退保笔数</th>
                        <th>退保金额</th>
                        <th>打款金额</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    if (!empty($res)) {
                        foreach($res as $k=>$value) {
                            ?>
                            <tr role="row">
                                <td><?=$k+1?></td>
                                <td><?=$value['bill_date']?></td>
                                <td><?=$value['policy_num']?></td>
                                <td><?=$value['policy_money']?></td>
                                <td><?=$value['cancel_num']?></td>
                                <td><?=$value['cancel_money']?></td>
                                <td><?=0.9*($value['policy_money']-$value['cancel_money'])?></td>
                                <td><a href="/policyment/policybill/exportdetail?start_time=<?=$value['bill_date']?>&end_time=<?=$value['bill_date']?>">导出数据明细</a></td>
                            </tr>
                            <?php
                        }
                    }else {
                        ?>
                        <tr role="row" style="text-align: center">
                            <td colspan="8">暂无数据</td>
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