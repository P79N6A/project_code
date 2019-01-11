<?php
$this->title = "一亿元统计管理";
use \yii\helpers\ArrayHelper;
use app\modules\balance\models\yyy\User;
use app\modules\balance\models\yyy\UserLoan;
    $oUser = new User();
    $oLoan = new UserLoan();
    $userInfo = $oUser->getUserInfo(ArrayHelper::getValue($loanData, 'user_id','0'));
    $phone = ArrayHelper::getValue($userInfo, 'mobile','');

    $loanInfo = $oLoan->getRenewalNum(ArrayHelper::getValue($loanData, 'loan_id','0'));
    $number = ArrayHelper::getValue($loanInfo, 'number',0);
?>
<style type="text/css">
    .div_style {
        margin-bottom: 20px
    }
    .span_style {
        margin-left: 120px
    }
</style>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5><?=$this->title. '>'?>展期服务费统计>详情</h5>
        </header>
        <div class="body">
            <div style="margin-bottom: 10px">
                <b>详情</b>
                <hr />
            </div>

            <div class="div_style">
                <span>借款编号：<?=ArrayHelper::getValue($loanData, 'loan_id')?></span>
                <span class="span_style">订单号：<?=ArrayHelper::getValue($loanData, 'order_id')?></span>
                <span class="span_style">手机号：<?=$phone?></span>
            </div>
            <div class="div_style">
                <span>对应资方：江西存管</span>
                <span class="span_style">借款日期:<?=ArrayHelper::getValue($loanData, 'start_date')?></span>
                <span class="span_style">应还日期:<?=ArrayHelper::getValue($loanData, 'end_date')?></span>
            </div>
            <div class="div_style">
                <span>应还本金：<?=ArrayHelper::getValue($loanData, 'amount')?> 元</span>
                <span class="span_style">应还利息：<?=ArrayHelper::getValue($loanData, 'interest_fee')?> 元</span>
                <span class="span_style">减免金额：<?=ArrayHelper::getValue($loanData, 'coupon_amount', 0)?> 元</span>
            </div>

            <div class="div_style">
                <span>赞点减息：<?=ArrayHelper::getValue($loanData, 'like_amount', 0)?> 元</span>
                <span class="span_style">展期费用：<?=ArrayHelper::getValue($loanData, 'money')?> 元</span>
                <span class="span_style">借款天数：<?=ArrayHelper::getValue($loanData, 'days')?> 元</span>
            </div>

            <div class="div_style">
                <span>展期发生日期：<?=ArrayHelper::getValue($loanData, 'create_time')?></span>
                <span class="span_style">展期后还款日期：<?=ArrayHelper::getValue($loanData, 'last_modify_time')?></span>
                <span class="span_style">已累计发生展期次数：<?=$number?></span>
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
                if (!empty($repayData)) {
                    foreach ($repayData as $k => $v) {
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
                    <th>展期费率</th>
                    <th>创建时间</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if (!empty($renewInfo)) {
                    foreach ($renewInfo as $key => $value) {

                        ?>
                        <tr>
                            <td><?=ArrayHelper::getValue($value, 'loan_id')?></td>
                            <td><?=ArrayHelper::getValue($value, 'renew_fee')?></td>
                            <td><?=ArrayHelper::getValue($value, 'renew')?></td>
                            <td><?=ArrayHelper::getValue($value, 'create_time')?></td>
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

            
        </div>
    </div>
</div>

