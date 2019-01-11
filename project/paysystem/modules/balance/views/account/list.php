<?php

use yii\widgets\LinkPager;
use \yii\helpers\ArrayHelper;
use app\modules\balance\common\PaymentCommon;
use app\models\Channel;
$this->title = "财务核算";
$oPaymentCommon = new PaymentCommon();


?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5>你所在的位置：首页><?=$this->title?>>数据汇总</h5>
            <!--<h5>延期订单统计</h5>-->
        </header>
        <div class="body">
            <form action="/balance/account/list" method="get">

                <div style="margin-bottom: 10px">
                    商编号：<input style="margin-right: 10px; width:220px;" type="text"  name="mechart_num" value="<?=$mechart_num?>"  />




                账单日期：<input style="margin-right: 10px; width:130px;" type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="repay_time" value="<?=$start_time?>"  />
                 ~
                    <input style="margin-left: 10px; width:130px;"  type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="end_time"  value="<?=$end_time?>" />

                </div>
                    <div style="text-align: center">
                        <input type="reset" value="重置"  class="btn btn-primary">
                        <input style="margin-left: 20px;margin-right:20px"  type="submit" value="查询" id="search_submit"  class="btn btn-primary">
                </div>
                <hr />
            </form>

            <div style="margin-bottom: 10px">

                <span style="margin-left: 20px">收单总金额累计：<b style="color: red">¥<?=Number_format($total_money,2)?></b>元</span>
                <span style="margin-left: 20px">手续费总金额累计：<b style="color: red">¥<?=Number_format($total_service,2)?></b>元</span>
            </div>


            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                <tr role="row">
                    <th>序号</th>
                    <th>账单日期</th>
                    <th>通道名称</th>
                    <th>商编号</th>
                    <th>收单总额</th>
                    <th>手续费</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if (!empty($resultAllData)) {
                    $num = 0;
                    //var_dump($resultAllData);die;
                    foreach($resultAllData as $value) {
                        $num ++;
                        ?>
                        <tr role="row">
                            <td><?=$num;?></td>
                            <td><?=ArrayHelper::getValue($value, 'bill_time','0')?></td>
                            <td><?=ArrayHelper::getValue($return_channel, ArrayHelper::getValue($value, 'return_channel','0'),'<span style="color: #CC9999">未知</span>')?></td>
                            <td><?=ArrayHelper::getValue($value, 'mechart_num','0')?></td>
                            <td><?=Number_format(ArrayHelper::getValue($value, 'money','0'),2)?></td>
                            <td><?=Number_format(ArrayHelper::getValue($value, 'service','0'),2)?></td>
                            <td><a target="_blank"  href="/balance/account/detailed?mechart_num=<?=ArrayHelper::getValue($value, 'mechart_num','0')?>&bill_time=<?=ArrayHelper::getValue($value, 'bill_time','0')?>">明细</a></td>

                        </tr>
                        <?php
                    }
                }else {
                    ?>
                    <tr role="row" style="text-align: center">
                        <td colspan="7">暂无数据</td>
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
            var start_time = $("input[name='repay_time']").val();
            var end_time = $("input[name='end_time']").val();
            if (start_time > end_time){
                alert("查询时间有误 ");
                return false;
            }
        });
    });
</script>