<?php
use \yii\helpers\ArrayHelper;
$this->title = "出款账单管理";
$type = [
    '1' => '正常',
    '2' => '差错',
    '3' => '处理错误',
];

?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5><?=$this->title. '>'?>账单详情</h5>
            <div id="history_location" style="float:right;margin:2px 5px 0 0" class="btn btn-primary">返回</div>
        </header>
        <div class="body">
            <hr />
            <div style="margin-left: 20px;">
                <div>商户订单号：<?=ArrayHelper::getValue($result, 'client_id', '')?></div>
                <div style="margin-top: 20px">
                    出款通道：<?=ArrayHelper::getValue($passageOfMoney, ArrayHelper::getValue($result, 'channel_id', ''), '')?>
                </div>
                <div style="margin-top: 20px">通道商编号：<?=ArrayHelper::getValue($result, 'client_number', '')?></div>
                <div style="margin-top: 20px">收款人姓名:<?=ArrayHelper::getValue($result, 'guest_account_name', '')?></div>
                <div style="margin-top: 20px">收款人银行：<?=ArrayHelper::getValue($result, 'guest_account_bank', '')?></div>
                <div style="margin-top: 20px">收款人银行卡号：<?=ArrayHelper::getValue($result, 'guest_account', '')?></div>
                <div style="margin-top: 20px">收款人证件号：<?=ArrayHelper::getValue($result, 'identityid', '')?></div>
                <div style="margin-top: 20px">收款人手机号：<?=ArrayHelper::getValue($result, 'user_mobile', '')?></div>
                <div style="margin-top: 20px">借款本金：<?=ArrayHelper::getValue($result, 'settle_amount', '')?> 元</div>
                <div style="margin-top: 20px">手续费：<?=ArrayHelper::getValue($result, 'settle_fee', '')?>  元</div>
                <div style="margin-top: 20px">账单日期：<?=date("Y-m-d", strtotime(ArrayHelper::getValue($result, 'bill_number', '')))?></div>
                <div style="margin-top: 20px">创建时间：<?=ArrayHelper::getValue($result, 'create_time', '')?></div>
                <div style="margin-top: 20px">
                    <?php
                        $channel_status = ArrayHelper::getValue($result, 'channel_status', 0);
                        if ($channel_status & 1){
                            echo '<span style="margin-right:30px">上游通道状态：付款成功</span>';
                        }else{
                            echo '<span style="margin-right:30px">上游通道状态：付款失败</span>';
                        }
                        if ($channel_status & 2) {
                            echo '<span style="margin-right:30px">上游通道状态：付款成功</span>';
                        }else{
                            echo '<span style="margin-right:30px">上游通道状态：付款失败</span>';
                        }
                        if ($channel_status & 4) {
                            echo '<span style="margin-right:30px">业务系统状态：出款成功</span>';
                        }else{
                            echo '<span style="margin-right:30px">业务系统状态：出款失败</span>';
                        }
                    ?>
                </div>
                <div style="margin-top: 20px">
                    差错状态：<?=ArrayHelper::getValue($errorTypes, ArrayHelper::getValue($result, 'error_types', 0), '')?>
                </div>
                <div style="margin-top: 20px">处理人：<?=$opt_name?></div>
                <div style="margin-top: 20px">原因：<?=ArrayHelper::getValue($result, 'reason' ,'')?></div>
            </div>
        </div>
    </div>
</div>
<script src="/bootstrap/js/bootstrap.min.js" type="text/javascript" charset="utf-8"></script>
<script>
    $("#history_location").click(function(){
        history.back();
    });
</script>