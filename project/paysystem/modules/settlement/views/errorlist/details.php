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
            <form action="?" method="post" id="post_form">
            <div style="margin-left: 20px;">
                <input name="_csrf" type="hidden" id="_csrf" value="<?= \Yii::$app->request->csrfToken ?>">
                <input type="hidden" name="list_id" value="<?=ArrayHelper::getValue($result, 'id', 0)?>">
                <input type="hidden" name="channel_id" value="<?=ArrayHelper::getValue($result, 'channel_id', 0)?>">
                <input type="hidden" name="client_id" value="<?=ArrayHelper::getValue($result, 'client_id', 0)?>">
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
                <div style="margin-top: 20px">差错类型：<?=ArrayHelper::getValue($errorTypes, ArrayHelper::getValue($result, 'error_types', 0), '')?></div>
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
                    差错状态：
                        <input type="radio" name="error_status" value="1" <?=(ArrayHelper::getValue($result, 'error_status', 0) == 1) ? 'checked="checked"' : "" ?> />已处理
                        <input type="radio" name="error_status" value="2" <?=(ArrayHelper::getValue($result, 'error_status', 0) == 2) ?  'checked="checked"' : "" ?> style="margin-left: 20px;" />未处理
                        <input type="radio" name="error_status" value="3" <?=(ArrayHelper::getValue($result, 'error_status', 0) == 3) ?  'checked="checked"' : "" ?> style="margin-left: 20px;" />关闭订单
                </div>
                <div style="margin-top: 20px;">
                    原因：<textarea name="reason" id="reason">
                            <?=ArrayHelper::getValue($result, 'reason' ,'')?>
                          </textarea>
                </div>
                <div style="margin-top: 20px;margin-left: 50px;">
                    <input type="button" id="dosubmit" class="btn btn-primary" value="提交" />
                </div>
            </div>
            </form>
        </div>
    </div>
</div>
<script src="/bootstrap/js/bootstrap.min.js" type="text/javascript" charset="utf-8"></script>
<script>
    var url_params = "<?= $url_params?>";
    $("#history_location").click(function(){
        history.back();
    });
    $(function(){
        $("#dosubmit").click(function(){
            var _csrf = $("#_csrf").val();
            var list_id = $("input[name='list_id']").val();
            var channel_id = $("input[name='channel_id']").val();
            var client_id = $("input[name='client_id']").val();
            var error_status = $("input[name='error_status']:checked").val();
            var reason = $("#reason").val();
            $.ajax({
                type:'post',
                url: "/settlement/errorlist/updatedetails",
                dataType: "json",
                data:{
                    '_csrf' :_csrf,
                    'list_id' : list_id,
                    'channel_id' : channel_id,
                    'client_id' :client_id,
                    'error_status' : error_status,
                    'reason' : reason
                },
                success: function (msg) {
                    alert(msg.msg);
                    if (msg.msg == '更新成功'){
                        location.href = '/settlement/errorlist/list?'+url_params;
                    }
                    return false;
                }
            });
        });
    });

</script>