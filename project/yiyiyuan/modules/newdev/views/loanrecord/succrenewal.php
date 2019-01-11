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
            <p class="haititle">借款金额（元）</p>
            <p class="haitxt"><?php echo sprintf('%.2f', $loaninfo->amount); ?></p>
            <a class="yiyq"><?php echo $status[$loaninfo->status]; ?></a>
        </div>
        <?php if ($service_amount > 0): ?>
            <div class="rowym addha">
                <div class="corname">保险费用</div>
                <div class="corliyou"><?php echo sprintf('%.2f', $service_amount); ?>元</div>
            </div>
        <?php endif; ?>
        <?php if ($business_type != 2): ?>
            <div class="rowym addha">
                <div class="corname">利息</div>
                <div class="corliyou"><?php echo sprintf('%.2f', $loaninfo->interest_fee); ?>元</div>
            </div>
        <?php endif; ?>
        <?php if ($loaninfo->chase_amount > 0): ?>
            <div class="rowym addha">
                <div class="corname">逾期罚息</div>
                <div class="corliyou"><?php echo sprintf('%.2f', $overdue_amount); ?>元</div>
            </div>
        <?php else: ?>
            <div class="rowym addha">
                <div class="corname">续期手续费</div>
                <div class="corliyou"><?php echo sprintf('%.2f', $renew_amount); ?>元</div>
            </div>
        <?php endif; ?>
        <div class="rowym addha">
            <div class="corname">续期时间</div>
            <div class="corliyou"><?php echo date('Y年m月d日', strtotime($repay_time)); ?></div>
        </div>
    </div>
</div>
