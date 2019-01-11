<?php

use yii\widgets\LinkPager;
use \yii\helpers\ArrayHelper;

$this->title = "财务数据分析";

$oCoverdue = new \app\modules\balance\common\COverdue();
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5><?=$this->title. '>'?>汇总统计</h5>
        </header>
        <div class="body">
            <form action="?" method="get">
            <div style="margin-bottom: 10px">
                账单日期 <input style="margin-right: 10px;" type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="start_time" value="<?=ArrayHelper::getValue($condition, 'start_time')?>"  /> ~
                          <input style="margin-left: 10px;"  type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="end_time"  value="<?=ArrayHelper::getValue($condition, 'end_time')?>" />

            </div>
            <div style="text-align: center;margin-top: 10px">
                <input style="margin-left: 20px;"  type="reset" value="重置" class="btn btn-primary">
                <input style="margin-left: 20px;"  type="submit" value="查询" id="search_submit"  class="btn btn-primary">
            </div>
            <hr />
            </form>

            <div style="margin-top: 30px">
                <span style="background-color: #CCCCCC;padding: 8px 20px;border-radius:3px;">已收累计</span>
            </div>
            <div style="border: 1px solid #CCCCCC;border-radius:3px;margin-top: 6px; padding: 10px">
                <div  style="margin-bottom: 20px">
                    <span>实收本金：<?=$repayPrincipal?></span>
                </div>
                <div style="margin-bottom: 20px">
                    <span style="margin-right: 100px">实收展期服务费金额：<?=$renewServer?></span>
                    <span style="margin-right: 100px">实收利息：<?=$settleFee?></span>
                    <span>实收滞纳金：<?=$lateFee?></span>
                </div>
               <!-- <div style="margin-bottom: 20px">
                    <span style="margin-right: 100px">实收保险手续费返还：<?/*=$insureServer*/?></span>
                </div>-->
                <div style="margin-bottom: 20px">
                    <span style="margin-right: 100px">
                        收入累计：<?=$all_money?>
                        <br /><font style="color: red">（除本金以外上述字段之和）</font></span>
                </div>
            </div>


            <div style="margin-top: 30px">
                <span style="background-color: #CCCCCC;padding: 8px 20px;border-radius:3px;">待收累计</span>
            </div>
            <div style="border: 1px solid #CCCCCC;border-radius:3px;margin-top: 6px; padding: 10px">

                <div style="margin-bottom: 20px">
                    <span style="margin-right: 100px">逾期待收本金：<?=$current_amount?></span>
                    <span style="margin-right: 100px">逾期待收利息：<?=$withdraw?></span>
                    <span>逾期滞纳金：<?=$late?></span>
                </div>
                <div style="margin-bottom: 20px">
                    <span style="margin-right: 100px">
                        逾期收入累计：<?=$current_amount+$withdraw+$late?>
                        <br /><font style="color: red">（除本金以外上述字段之和）</font></span>
                </div>
            </div>



            <div style="margin-top: 30px">
                <span style="background-color: #CCCCCC;padding: 8px 20px;border-radius:3px;">未到期累计</span>
            </div>
            <div style="border: 1px solid #CCCCCC;border-radius:3px;margin-top: 6px; padding: 10px">

                <div style="margin-bottom: 20px">
                    <span style="margin-right: 100px">未到期本金：<?=$amount?></span>
                    <span style="margin-right: 100px">未到期利息：<?=$withdraw_fee?></span>
                </div>
                <div style="margin-bottom: 20px">
                    <span style="margin-right: 100px">
                       未到期收入累计：<?=$amount+$withdraw_fee?>
                        <br /><font style="color: red">（除本金以外上述字段之和）</font></span>
                </div>
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