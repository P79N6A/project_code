<?php

use yii\widgets\LinkPager;

$this->title = "支付系统";
$status      = \app\models\Business::getStatus();
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5>项目管理</h5>
            <a href="?exportbutton=down_excel"><label style="float:right" class="btn btn-primary">导出</label></a>
        </header>
        <div class="body">
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                    <tr role="row">
                        <th>序号</th>
                        <th>出款通道名称</th>
                        <th>商户订单号</th>
                        <th>收款人姓名</th>
                        <th>收款人银行账号</th>
                        <th>订单金额/元</th>
                        <th>手续费/元</th>
                        <th>账单日期</th>
                        <th>创建时间</th>
                        <th>对账结果</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>

                    <?php if (!empty($res)):
                        $serial_number = 1;
                        ?>
                        <?php foreach ($res as $key => $val): ?>
                            <tr role="row" class="even">
                                <td><?=$serial_number++?></td>
                                <td><?php
                                        if (!empty($channel_name_data[$val->channel_id])){
                                            echo $channel_name_data[$val->channel_id];
                                        }
                                    ?></td>
                                <td><?=$val->client_id?></td>
                                <td><?=$val->guest_account_name?></td>
                                <td><?=$val->guest_account?></td>
                                <td><?=$val->settle_amount?></td>
                                <td><?=$val->settle_fee?></td>
                                <td><?=$val->bill_number?></td>
                                <td><?=$val->create_time?></td>
                                <td><?php
                                        if ($val->error_status==1){
                                            echo "差错已处理";
                                        }elseif($val->type == 1){
                                            echo "对账成功";
                                        }
                                    ?></td>
                                <td><a href="/settlement/billsuccess/details?id=<?=$val->id?>">详情</a></td>
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