<?php

use yii\widgets\LinkPager;

$this->title = "出款账单管理";
$status      = \app\models\Business::getStatus();
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5><?=$this->title. '>'?>出款通道数据</h5>
            <button data_id='' id='sureDelete' style="float:right" type="button" class="btn btn-danger">返回</button>
        </header>
        <div style="margin: 20px 0 20px 15px;">
            <span style="margin-right:5%">出款通道名称：
                <?php
                    if ($channel_name_data[$channel_type]){
                        echo $channel_name_data[$channel_type];
                    }
                ?></span>
            <span style="margin-right:5%">账单日期：<?=$channel_date?></span>
            <span style="margin-right:5%">交易总笔数：<b style="color:red"><?=$total_count?></b>笔</span>
            <span style="margin-right:5%">交易总金额：￥<b style="color:red"><?=$total_money?></b> 元</span>
            <span style="margin-right:5%">交易总手续费：<b style="color:red"><?=$total_settle_fee?></b> 元</span>
        </div>
        <div class="body">
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                <tr role="row">
                    <th>序号</th>
                    <!--<th>订单号</th>-->
                    <th>商户订单号</th>
                    <th>收款人姓名</th>
                    <th>收款人银行账号</th>
                    <th>订单金额/元</th>
                    <th>手续费/元</th>
                    <th>账单日期</th>
                    <th>创建时间</th>
                </tr>
                </thead>
                <tbody>
                    <?php if (!empty($res)):
                        $serial_number = 1;
                        ?>
                        <?php foreach ($res as $key => $val): ?>
                            <tr role="row" class="even">
                                <td><?=$serial_number++;?></td>
                                <!--<td><?=$val->req_id?></td>-->
                                <td><?=$val->client_id?></td>
                                <td><?=$val->guest_account_name?></td>
                                <td><?=$val->guest_account?></td>
                                <td><?=$val->settle_amount?></td>
                                <td><?=$val->settle_fee?></td>
                                <td><?=$val->bill_number?></td>
                                <td><?=$val->create_time?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <div class="panel_pager">
                <?php echo LinkPager::widget(['pagination' => $pages]); ?>
            </div>
        </div>
    </div>
</div>
<script src="/bootstrap/js/bootstrap.min.js" type="text/javascript" charset="utf-8"></script>
<script>
    $('.showtip').popover();
</script>