<?php

use \yii\helpers\ArrayHelper;
$this->title = "财务核算";
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5>你所在的位置：首页><?=$this->title?>>明细</h5>
            <!--<h5>延期订单统计</h5>-->
        </header>
        <div class="body">


            <div style="margin-bottom: 10px">

                <span style="margin-left: 20px">账单日期：<b style="color: #436cff"><?=$bill_time?></b></span>
                <span style="margin-left: 20px">商编号：<b style="color: #436cff"><?=$mechart_num?></b></span>
                <span style="margin-left: 20px">总金额：<b style="color: red">¥<?=Number_format($total_money,2)?></b>元</span>
                <span style="margin-left: 20px">手续费金额：<b style="color: red">¥<?=Number_format($total_service,2)?></b>元</span>
            </div>


            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                <tr role="row">
                    <th>公司主体</th>
                    <th>本金</th>
                    <th>利息</th>
                    <th>滞纳金</th>
                    <th>展期服务费</th>
                    <th>减免金额</th>
                    <th>手续费金额</th>
                    <th>总金额</th>
                </tr>
                </thead>
                <tbody>
                <tr role="row">
                    <td>先花信息</td>
                    <td><?=Number_format(ArrayHelper::getValue($xhh_data, 'principal','0'),2)?></td>
                    <td><?=Number_format(ArrayHelper::getValue($xhh_data, 'interest','0'),2)?></td>
                    <td><?=Number_format(ArrayHelper::getValue($xhh_data, 'fine','0'),2)?></td>
                    <td><?=Number_format(0,2)?></td>
                    <td><?=Number_format(0,2)?></td>
                    <td><?=Number_format(ArrayHelper::getValue($xhh_data, 'service','0'),2)?></td>
                    <td><?=Number_format(ArrayHelper::getValue($xhh_data, 'money','0'),2)?></td>
                </tr>
                <tr role="row">
                    <td>小小黛朵</td>
                    <td><?=Number_format(ArrayHelper::getValue($xxdd_data, 'principal','0'),2)?></td>
                    <td><?=Number_format(ArrayHelper::getValue($xxdd_data, 'interest','0'),2)?></td>
                    <td><?=Number_format(ArrayHelper::getValue($xxdd_data, 'fine','0'),2)?></td>
                    <td><?=Number_format(0,2)?></td>
                    <td><?=Number_format(0,2)?></td>
                    <td><?=Number_format(ArrayHelper::getValue($xxdd_data, 'service','0'),2)?></td>
                    <td><?=Number_format(ArrayHelper::getValue($xxdd_data, 'money','0'),2)?></td>
                </tr>
                <tr role="row">
                    <td>智融钥匙</td>
                    <td><?=Number_format(0,2)?></td>
                    <td><?=Number_format(0,2)?></td>
                    <td><?=Number_format(0,2)?></td>
                    <td><?=Number_format(0,2)?></td>
                    <td><?=Number_format(0,2)?></td>
                    <td><?=Number_format(ArrayHelper::getValue($zrys_data, 'service','0'),2)?></td>
                    <td><?=Number_format(ArrayHelper::getValue($zrys_data, 'money','0'),2)?></td>
                </tr>
                </tbody>
            </table>


        </div>
    </div>
</div>
<script src="/bootstrap/js/bootstrap.min.js" type="text/javascript" charset="utf-8"></script>
<script src="/laydate/laydate.dev.js" type="text/javascript" charset="utf-8"></script>
