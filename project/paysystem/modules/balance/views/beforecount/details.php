<?php
$this->title = "一亿元统计管理";
use \yii\helpers\ArrayHelper;
?>
<style type="text/css">
    .div_style {
        margin-bottom: 20px
    }
    .span_style {
        margin-left: 60px
    }
</style>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5><?=$this->title. '>'?>逾期待收统计>待收统计明细</h5>
        </header>
        <div class="body">
            <div style="margin-bottom: 10px">
                <b>详情</b>
                <hr />
            </div>

            <div class="div_style">
                <span>借款编号：<?=ArrayHelper::getValue($loan_info, 'loan_id')?></span>
                <span class="span_style">订单号：<?=ArrayHelper::getValue($remit_info, 'order_id')?></span>
                <span class="span_style">存管电子账户：<?=ArrayHelper::getValue($pay_account, 'accountId')?></span>
            </div>
            <div class="div_style">
                <span>手机号：<?=ArrayHelper::getValue($user_info, 'mobile')?></span>
                <span class="span_style">对应资方：<?=ArrayHelper::getValue(\app\modules\balance\common\COverdue::fund(), ArrayHelper::getValue($remit_info, 'fund'))?></span>
                <span class="span_style">债权人：<?=ArrayHelper::getValue($user_info, 'realname')?></span>
            </div>
            <div class="div_style">
                <span>借款日期:<?=date("Y-m-d", strtotime(ArrayHelper::getValue($loan_info, 'start_date')))?></span>
                <span class="span_style">应还款日期：<?=date("Y-m-d", strtotime(ArrayHelper::getValue($loan_info, 'end_date')))?></span>
            </div>
            <div class="div_style">
                <span>应还本金：<?=$need_money?> 元</span>
                <span class="span_style">利率：<?=$interest_rate?></span>
                <span class="span_style">应还服务费：<?=ArrayHelper::getValue($overdue_info, 'withdraw_fee', 0)?> 元</span>
            </div>

            <div class="div_style">
                <span>应还利息：<?=ArrayHelper::getValue($overdue_info, 'interest_fee', 0)?> 元</span>
            </div>

            <div class="div_style">
                <span>展期发生日期：
                    <?php
                    if (!empty($renew_info[0])){
                        echo date("Y-m-d", strtotime(ArrayHelper::getValue($renew_info[0], 'start_time')));
                    }else{
                        echo "0";
                    }
                    ?>
                </span>
                <span class="span_style">展期后还款日期：
                    <?php
                    if (!empty($renew_info[0])) {
                        echo date("Y-m-d", strtotime(ArrayHelper::getValue($loan_info, 'end_date')));
                    }else{
                        echo 0;
                    }
                    ?>
                </span>
                <span class="span_style">已累计发生展期次数：<?=ArrayHelper::getValue($loan_info, 'number', 0)?></span>
            </div>

            <div class="div_style">
                <span>
                    展期服务费费率：
                    <?php
                    if (!empty($renew_info[0])){
                        echo ArrayHelper::getValue($renew_info[0], 'renew');
                    }else{
                        echo 0;
                    }
                    ?>
                </span>
                <span class="span_style">展期服务费金额：
                    <?php
                    if (!empty($renew_info[0])){
                        echo ArrayHelper::getValue($renew_info[0], 'renew_fee');
                    }else{
                        echo "0";
                    }
                    ?>
                    元
                </span>
                <span class="span_style">备注（是否为借新还旧）：
                    <?php
                    if (!empty($renew_info[0])){
                        echo 'Y';
                    }else{
                        echo "N";
                    }
                    ?>
                </span>
            </div>

            <div class="div_style">
                <span>展创建时间：
                    <?php
                    if (!empty($renew_info[0])){
                        echo ArrayHelper::getValue($renew_info[0], 'create_time');
                    }else{
                        echo "0";
                    }
                    ?>
                </span>
            </div>

            <div style="margin-bottom: 10px">
                <b>还款记录</b>
                <hr />
            </div>
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                    <tr>
                        <th>订单号</th>
                        <th>还款金额</th>
                        <th>还款时间</th>
                        <th>支付通道</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if (!empty($repay_info)) {
                    foreach ($repay_info as $k => $v) {
                        ?>
                        <tr>
                            <td><?=ArrayHelper::getValue($v, 'repay_id')?></td>
                            <td><?=ArrayHelper::getValue($v, 'actual_money')?></td>
                            <td><?=ArrayHelper::getValue($v, 'createtime')?></td>
                            <td><?=ArrayHelper::getValue(\app\modules\balance\common\COverdue::showRepaymentChannel(), ArrayHelper::getValue($v, 'platform'))?></td>
                        </tr>
                        <?php
                    }
                }else {
                    ?>
                    <tr>
                        <td colspan="4" style="text-align: center">暂无数据！</td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>

            <div>
                <b>展期记录</b>
                <hr />
            </div>
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                <tr>
                    <th>订单号</th>
                    <th>展期服务费</th>
                    <th>创建时间</th>
                    <th>支付通道</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if (!empty($renew_info)) {
                    foreach ($renew_info as $key => $value) {

                        ?>
                        <tr>
                            <td><?=ArrayHelper::getValue($value, 'loan_id')?></td>
                            <td><?=ArrayHelper::getValue($value, 'renew_fee')?></td>
                            <td><?=ArrayHelper::getValue($value, 'create_time')?></td>
                            <td>YYYWX</td>
                        </tr>
                        <?php
                    }
                }else {
                    ?>
                    <tr>
                        <td colspan="4" style="text-align: center">暂无数据！</td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>

            <div>
                <b>本期还款信息</b>
                <hr />
            </div>
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <tbody>
                <tr>
                    <td>应还本金</td>
                    <td><?=$need_money_all?></td>
                    <td>应还服务费</td>
                    <td><?=$need_service?></td>
                    <td>应还利息</td>
                    <td><?=$need_interest?></td>
                </tr>
                <tr>
                    <td>已还本金</td>
                    <td><?=$over_money?></td>
                    <td>已还服务费</td>
                    <td><?=$over_service?></td>
                    <td>已还利息</td>
                    <td><?=$over_interest?></td>
                </tr>
                <tr>
                    <td>未还本金</td>
                    <td><?=$not_money?></td>
                    <td>未还服务费</td>
                    <td><?=$not_service?></td>
                    <td>未还利息</td>
                    <td><?=$not_interest?></td>
                </tr>
                <tr>
                    <td>应还还滞纳金</td>
                    <td><?=$need_overdue?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>已还滞纳金</td>
                    <td><?=$over_overdue?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>未还滞纳金</td>
                    <td><?=$not_overdue?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                </tbody>
            </table>
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
                    if (data.msg != '对账失败'){
                        alert(data.msg);
                        location.href="/backstage/mistake/list";
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