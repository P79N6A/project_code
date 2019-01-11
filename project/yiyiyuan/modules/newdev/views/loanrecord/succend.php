<?php
$status = array(
    '1' => '待审核',
    '2' => '筹款中',
    '3' => '审核驳回',
    '4' => '失效',
    '5' => '申请提现',
    '6' => '提现通过',
    '7' => '提现驳回',
    //'8' => '已完成',
    '8' => '已还款',
    '9' => '待还款',
    '10' => '待出款',
    '11' => '待确认还款',
    '12' => '待还款',
    '13' => '待还款',
);
?>
<div class="zuoyminew jk_item">
    <div class="daihukan_cont matop0">
        <div class="haimoney">
            <p class="haititle">借款金额(元)</p>
            <p class="haitxt"><?php echo sprintf('%.2f', $loaninfo->amount); ?></p>
            <a class="yiyq" style="border: 1px solid #37c175; color: #37c175;"><?php echo $status[$loaninfo->status]; ?></a>
        </div>
        <?php if ($service_amount > 0): ?>
        <div class="rowym addha">
            <div class="corname">保险费(元)</div>
            <div class="corliyou"><?php echo sprintf('%.2f', $service_amount); ?></div>
        </div>
        <?php endif; ?>
        <?php if ($business_type != 2): ?>
        <div class="rowym addha">
            <div class="corname">利息(元)</div>
            <div class="corliyou"><?php echo sprintf('%.2f', $loaninfo->interest_fee); ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($loan_coupon)): ?>
            <div class="rowym addha">
                <div class="corname">优惠券减免(元)</div>
                <div class="corliyou">
                    <?php if ($loan_coupon->couponList->val == 0): ?>
                        <?php if (($loan_coupon->couponList->limit == 0) || ($loan_coupon->couponList->limit <= $loaninfo->current_amount)): ?>
                            全免
                        <?php else: ?>
                            <?php echo sprintf('%.2f', $loaninfo->coupon_amount); ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <!--sss-->
                        <?php if($loaninfo->coupon_amount >=  $loaninfo->interest_fee): ?>
                            -<?php echo sprintf('%.2f', $loaninfo->interest_fee); ?>
                        <?php else: ?>
                            <!--eee-->
                            -<?php echo sprintf('%.2f', $loaninfo->coupon_amount); ?>
                            <!--sss-->
                        <?php endif; ?>
                        <!--eee-->
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($loaninfo->like_amount > 0): ?>
                <div class="rowym addha">
                    <div class="corname">点赞减息(元)</div>
                    <div class="corliyou">-<?php echo sprintf('%.2f', $loaninfo->like_amount); ?></div>
                </div>
            <?php endif; ?>
        <?php if ($loaninfo->status != 8): ?>
        <div class="rowym addha">
            <div class="corname">已还金额(元)</div>
            <div class="corliyou"><?php echo sprintf('%.2f', $loaninfo->huankuan_amount); ?></div>
        </div>
        <?php endif; ?>
        <div class="rowym addha">
            <div class="corname">还款时间</div>
            <div class="corliyou"><?php echo $repay_time; ?></div>
        </div>
        <?php if (!empty($insurance_order)): ?>
        <div class="rowym addha">
            <div class="corname">保单号</div>
            <div class="corliyou"><?php echo $insurance_order; ?></div>
        </div>
        <?php endif; ?>
    </div>
    <?php
    if ($business_type == 4){
        ?>
        <button type="submit" class="bgrey" onclick="doLoan(1)">再次借款</button>
        <?php
    }else{
        ?>
        <button type="submit" class="bgrey" onclick="doLoan(2)">再次借款</button>
        <?php
    }
    ?>
</div>
<script>
    function doLoan(type) {
        if(type == 1){
            window.location = '/new/loan/index?gua=1';
        }else{
            window.location = '/new/loan';
        }
    }
</script>