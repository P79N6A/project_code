<?php

use yii\widgets\LinkPager;

$this->title = "出款账单管理";
$status      = \app\models\Business::getStatus();
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5><?=$this->title. '>'?>出款通道数据</h5>
            <div id="history_location" style="float:right;margin:2px 5px 0 0" class="btn btn-primary">返回</div>
        </header>
        <div class="body">
            <div style="margin:10px 0">
                <span style="margin-right: 20px">出款通道名称：<?=$channel_name?></span>
                <span style="margin-right: 20px">总笔数：<b style="color: red"><?=$file_count?></b> 笔</span>
                <span style="margin-right: 20px">总金额：<b style="color: red">￥<?=$total_money?></b> 元</span>
                <span>总手续费：<b style="color: red"><?=$total_settle?></b> 元</span>
            </div>
            <hr />
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                <tr role="row">
                    <th>序号</th>
                    <th>订单号</th>
                    <th>商户订单号</th>
                    <th>入款人姓名</th>
                    <th>入款人银行账号</th>
                    <th>订单金额/元</th>
                    <th>手续费/元</th>
                    <th>账单日期</th>
                    <th>创建时间</th>
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
                    <td><?=$value->client_id?></td>
                    <td><?=$value->client_number?></td>
                    <td><?=$value->guest_account_name?></td>
                    <td><?=$value->guest_account?></td>
                    <td><?=$value->settle_amount?>/元</td>
                    <td><?=$value->settle_fee?>/元</td>
                    <td><?=$value->bill_number?></td>
                    <td><?=$value->create_time?></td>
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
<script>
    $(function(){
        $("#history_location").click(function(){
            history.go(-1);
        });
    });
</script>