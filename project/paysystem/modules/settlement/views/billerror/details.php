<?php
$this->title = "账单管理";
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5><?=$this->title. '>'?>账单详情</h5>
        </header>
        <div class="body">
            <form action="updatebill" method="post" id="post_form">
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <tbody>
                    <tr role="row">
                        <input name="_csrf" type="hidden" id="_csrf" value="<?= \Yii::$app->request->csrfToken ?>">
                        <input type="hidden" name="id" value="<?=$result->id?>">
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
                        <td>差错类型：<?=$result->error_types?></td>
                    </tr>
                    <tr role="row">
                        <td>差错状态：
                            <input type="radio" value="1" name = "error_status" <?php
                                                                            if ($result->error_status == 1){
                                                                                echo "checked";
                                                                            }
                                                                        ?> />已处理
                            <input type="radio" value="1"  name = "error_status"
                                <?php
                                    if ($result->error_status == 2){
                                        echo "checked";
                                    }
                                ?> />未处理
                        </td>
                    </tr>
                    <tr role="row">
                        <td>原因：
                            <textarea name="reason"> <?=$result->reason?></textarea>
                        </td>
                    </tr>
                    <tr role="row">
                        <td>
                            <button  type="button" id="dosubmit" class="btn btn-primary" >提交</button>
                        </td>
                    </tr>
                </tbody>
            </table>
                </form>
        </div>
    </div>
</div>

<script src="/static/js/jquery-form.js" type="text/javascript" charset="utf-8"></script>
<script>
    $(function(){
        $("#dosubmit").click(function(){
            var options = {
                dataType: 'json',
                data: $("#post_form").formToArray(),
                success: function (data) {
                    if (data.msg != '更新失败'){
                        alert(data.msg);
                        location.href="/settlement/billerror/index";
                        return false;
                    }
                    alert(data.msg);
                    return false;
                }
            };
            $("#post_form").ajaxSubmit(options);
            return false;
        });
    });
    /*
    $(function(){
        $("#dosubmit").click(function(){
            alert("dfdsf");
            var options = {
                dataType: 'json',
                data: $("#post_form").formToArray(),
                success: function (data) {
                    alert(data);
                    return false;
                }
            };
            $("#post_form").ajaxSubmit(options);
            return false;

        });
    });
    */
</script>