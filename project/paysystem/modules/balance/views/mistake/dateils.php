<?php
$this->title = "米富逾期对账管理";
use \yii\helpers\ArrayHelper;
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5><?=$this->title. '>'?>差错账管理>详情</h5>
        </header>
        <div class="body">
            <div style="margin-bottom: 10px">
                <b>详情</b>
                <hr />
            </div>
            <form action="updatebill" method="post" id="post_form">
                <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                    <tbody>
                    <tr role="row" >
                        <input name="_csrf" type="hidden" id="_csrf" value="<?= \Yii::$app->request->csrfToken ?>">
                        <input type="hidden" name="id" value="<?=ArrayHelper::getValue($result, 'id')?>">
                        <td style="padding-left: 20px">商户订单号：<?=ArrayHelper::getValue($result, 'client_id')?></td>
                    </tr>

                    <tr role="row" >
                        <td style="padding-left: 20px">
                            <span>
                                回款通道：
                                <?php
                                    echo ArrayHelper::getValue($return_channel, ArrayHelper::getValue($result, 'return_channel'));
                                ?>
                            </span>
                            <span style="margin-left: 50px;">
                                通道商编号：
                                <?=ArrayHelper::getValue($result, 'series')?>
                            </span>
                        </td>
                    </tr>
                    <tr role="row" >
                        <td style="padding-left: 20px">姓名：<?=ArrayHelper::getValue($result, 'name')?></td>
                    </tr>
                    <tr role="row" >
                        <td style="padding-left: 20px">开户行：<?=ArrayHelper::getValue($result, 'opening_bank')?></td>
                    </tr>
                    <tr role="row" >
                        <td style="padding-left: 20px">银行卡号：<?=ArrayHelper::getValue($result, 'guest_account')?></td>
                    </tr>
                    <tr role="row" >
                        <td style="padding-left: 20px">证件号：<?=ArrayHelper::getValue($result, 'identityid')?></td>
                    </tr>
                    <tr role="row" >
                        <td style="padding-left: 20px">手机号：<?=ArrayHelper::getValue($result, 'user_mobile')?></td>
                    </tr>
                    <tr role="row" >
                        <td style="padding-left: 20px">
                            <span style="margin-right: 50px; " id="amount">支付系统交易金额：<?=ArrayHelper::getValue($result, 'amount') ?></span>
                            <span id="payment_amount">第三方交易金额：<?=ArrayHelper::getValue($result, 'payment_amount') ?></span>
                        </td>
                    </tr>
                    <tr role="row" >
                        <td style="padding-left: 20px">手续费：<?=ArrayHelper::getValue($result, 'settle_fee')?></td>
                    </tr>
                    <tr role="row" >
                        <td style="padding-left: 20px">账单日期：<?=ArrayHelper::getValue($result, 'payment_date')?></td>
                    </tr>
                    <tr role="row" >
                        <td style="padding-left: 20px">创建时间：<?=ArrayHelper::getValue($result, 'create_time')?></td>
                    </tr>
                    <tr role="row" >
                        <td style="padding-left: 20px">
                            <?php
                            $passageway_type = ArrayHelper::getValue($result, 'passageway_type');
                            ?>
                            <span style="margin-right: 50px;">支付系统：<?=($passageway_type & 1) ? "成功" : "失败"?></span>
                            <span>第三方支付金额：<?=($passageway_type & 2) ? "成功" : "失败"?></span>

                        </td>
                    </tr>

                    <tr role="row" >
                        <td style="padding-left: 20px">差错状态：<?=ArrayHelper::getValue($errorStatus, ArrayHelper::getValue($result, 'error_types'))?></td>
                    </tr>

                    <tr role="row" >
                        <td style="padding-left: 20px">
                            确认亏损：
                            <input type="radio" value="1" name="loss"
                                <?php
                                    $loss = ArrayHelper::getValue($result, 'loss');
                                    if ($loss == 1){
                                        echo "checked = true";
                                    }
                                ?> />是
                            <input type="radio" value="2" name="loss" style="margin-left: 30px"
                                <?php
                                if ($loss == 2){
                                    echo "checked = true";
                                }
                                ?>
                            />否
                        </td>
                    </tr>

                    <tr role="row" >
                        <td style="padding-left: 20px">
                            处理状态：
                            <input type="radio" value="1" name="auditing_status"
                                <?php
                                $auditing_status = ArrayHelper::getValue($result, 'loss');
                                if ($auditing_status == 1){
                                    echo "checked = true";
                                }
                                ?> />已处理
                            <input type="radio" value="2" name="auditing_status" style="margin-left: 30px"
                                <?php
                                if ($auditing_status == 2){
                                    echo "checked = true";
                                }
                                ?>
                            />未处理
                            <input type="radio" value="2" name="auditing_status" style="margin-left: 30px"
                                <?php
                                if ($auditing_status == 3){
                                    echo "checked = true";
                                }
                                ?>
                            />关闭订单
                        </td>
                    </tr>
                    <tr role="row" >
                        <td style="padding-left: 20px">原因：
                            <textarea name="reason">
                                <?=ArrayHelper::getValue($result, 'reason')?>
                            </textarea>
                        </td>
                    </tr>

                    <tr role="row">
                        <td style="text-align: center;padding-top: 40px">
                            <button  type="button" id="history" class="btn btn-primary" >返回</button>
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

            var amount = <?=ArrayHelper::getValue($result, 'amount') ?>;
            var payment_amount = <?=ArrayHelper::getValue($result, 'payment_amount') ?>;
           /* if(amount != payment_amount){
                alert('金额不一致');
                return false;
            }*/
            var options = {
                dataType: 'json',
                data: $("#post_form").formToArray(),
                success: function (data) {
                    if (data.msg != '对账失败'){
                        alert(data.msg);
                        location.href="/balance/mistake/list";
                        return false;
                    }
                    alert(data.msg);
                    return false;
                }
            };
            $("#post_form").ajaxSubmit(options);
            return false;
        });
        $("#history").click(function(){
            history.go(-1);
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