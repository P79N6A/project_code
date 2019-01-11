<?php
$status = array(
    '1' => '待审核',
    '2' => '筹款中',
    '3' => '审核驳回',
    '4' => '失效',
    '5' => '申请提现',
    '6' => '提现通过',
    '7' => '提现驳回',
    '8' => '已续期',
    '9' => '待还款',
    '10' => '待出款',
    '11' => '待确认还款',
    '12' => '待还款',
    '13' => '待还款',
);
?>
<div class="Hcontainer">
    <img src="/images/title.png" width="100%"/>
    <div class="con">
        <div class="details">
            <p class="mb50 n36 text-center"><img src="/images/icon_valid3.png" class="w8 mr2"><?php echo $status[$loaninfo->status]; ?></p>
            <div class="row mb30">
                <div class="col-xs-4 cor n26">状态</div>
                <div class="col-xs-8 text-right n26 "><span class="red"><?php echo $status[$loaninfo->status]; ?></span></div>
            </div>
            <div class="border_bottom_1"></div>
            <div class="adver border_bottom_1 border_top_1">
                <div class="row mb30">
                    <div class="col-xs-4 cor n26">借款金额</div>
                    <div class="col-xs-4 nPad">
                        <?php if ($business_type == 2): ?>
                            <div class="assureC">担保</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-xs-4 text-right n26"><span class="red">&yen;<?php echo sprintf('%.2f', $loaninfo->amount); ?></span></div>
                </div>
                <?php if ($business_type != 2): ?>
                    <div class="row mb30">
                        <div class="col-xs-4 cor n26">服务费</div>
                        <div class="col-xs-8 text-right n26"><span class="red">&yen;<?php echo $loaninfo->is_calculation == 1 ? sprintf('%.2f', $loaninfo->withdraw_fee) : sprintf('%.2f', $service_amount); ?></span></div>
                    </div>
                <?php endif; ?>
                <?php if ($business_type != 2): ?>
                    <div class="row mb30">
                        <div class="col-xs-4 cor n26">利息</div>
                        <div class="col-xs-8 text-right n26"><span class="red">&yen;<?php echo $loaninfo->is_calculation == 1 ? sprintf('%.2f', $service_amount) : '0.00'; ?></span></div>
                    </div>
                <?php endif; ?>
                <?php if ($loaninfo->chase_amount > 0): ?>
                    <div class="row mb30">
                        <div class="col-xs-4 cor n26">逾期罚息</div>
                        <div class="col-xs-8 text-right n26"><span class="red">&yen;<?php echo sprintf('%.2f', $loaninfo->chase_amount - $loaninfo->amount - $service_amount); ?></span></div>
                    </div>
                <?php else: ?>
                    <div class="row mb30">
                        <div class="col-xs-5 cor n26">续期手续费</div>
                        <div class="col-xs-7 text-right n26"><span class="red">30</span></div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="adver border_top_1">
                <div class="row">
                    <div class="col-xs-4 cor n26">续期时间</div>
                    <div class="col-xs-8 text-right n26"><span class="red n36 lh"><?php echo date('Y-m-d', strtotime($repay_time)); ?></span></div>
                </div>
            </div>
        </div>
        <img src="/images/bottom.png" width="100%" style="vertical-align:top"/>
    </div>
</div>

<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    wx.config({
        debug: false,
        appId: '<?php echo $jsinfo['appid']; ?>',
        timestamp: <?php echo $jsinfo['timestamp']; ?>,
        nonceStr: '<?php echo $jsinfo['nonceStr']; ?>',
        signature: '<?php echo $jsinfo['signature']; ?>',
        jsApiList: [
            'hideOptionMenu'
        ]
    });

    wx.ready(function () {
        wx.hideOptionMenu();
    });
</script>