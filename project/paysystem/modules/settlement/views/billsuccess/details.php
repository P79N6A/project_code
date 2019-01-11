<?php
use \yii\helpers\ArrayHelper;
$this->title = "账单管理";
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5><?=$this->title. '>'?>账单详情</h5>
        </header>
        <div class="body">
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <tbody>
                <tr role="row">
                    <td>商户订单号：<?=$result->client_id?></td>
                </tr>
                <tr role="row">
                    <td>出款通道：<?php
                                    if (!empty($channel_data[$result->channel_id])){
                                        echo $channel_data[$result->channel_id];
                                    }
                                  ?></td>
                </tr>
                <tr role="row">
                    <td>收款人姓名:<?=$result->guest_account_name?></td>
                </tr>
                <tr role="row">
                    <td>收款人银行：<?=$result->guest_account_bank?></td>
                </tr>
                <tr role="row">
                    <td>收款人银行卡号：<?=$result->guest_account?></td>
                </tr>
                <tr role="row">
                    <td>收款人证件号：<?=$result->identityid?></td>
                </tr>
                <tr role="row">
                    <td>收款人手机号：<?=$result->user_mobile?></td>
                </tr>
                <tr role="row">
                    <td>借款本金：<?=$result->settle_amount?> 元</td>
                </tr>
                <tr role="row">
                    <td>手续费：<?=$result->settle_fee?> 元</td>
                </tr>
                <tr role="row">
                    <td>创建时间：<?=$result->create_time?></td>
                </tr>
                <tr role="row">
                    <td>操作人：<?=ArrayHelper::getValue($manager_info, 'username', '')?></td>
                </tr>
                <tr role="row">
                    <td>差错类型：<?=$result->error_types?></td>
                </tr>
                <tr role="row">
                    <td>差错状态：<?php
                        if ($result->type == 1){
                            echo "对账成功";
                        }elseif($result->error_status==2){
                            echo "差错已处理";
                        }
                        ?></td>
                </tr>
                <tr role="row">
                    <td>原因：<?=$result->reason?></td>
                </tr>

                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="/bootstrap/js/bootstrap.min.js" type="text/javascript" charset="utf-8"></script>
<script>
    $('.showtip').popover();
</script>