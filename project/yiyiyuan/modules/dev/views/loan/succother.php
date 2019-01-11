<?php
$status = array(
    '1' => '待审核',
    '2' => '筹款中',
    '3' => '审核驳回',
    '4' => '失效',
    '5' => '申请提现',
    '6' => '提现通过',
    '7' => '提现驳回',
    '8' => '已完成',
    '9' => '待还款',
    '10' => '待出款',
    '11' => '待确认还款',
    '12' => '已逾期',
    '13' => '已逾期',
    '15' => '提现驳回',
    '17' => ' 已取消',
);

function count_days($a, $b) {
    $a = strtotime("now");
    $b = strtotime(date('Y-m-d', strtotime($b)));
    return ceil(abs($a - $b) / 86400);
}
?>

<div class="Hcontainer">
    <img src="/images/title.png" width="100%"/>
    <div class="con">
        <div class="details">
            <p class="mb50 n44 text-center lh"><?php if ($loaninfo->status == 11): ?><img src="/images/icon_valid3.png" class="w8 mr2"><?php else: ?><img src="/images/icon_unvalid3.png" class="w8 mr2"><?php endif; ?><?php if (($loaninfo->status == 12) || ($loaninfo->status == 13)): ?><?php if ((!empty($loaninfo->chase_amount)) && !empty($loaninfo->repay_time)): ?> 还款失败，请重新确认<?php elseif (date('Y-m-d H:i:s') < $loaninfo->end_date): ?> 还款失败，请重新确认<?php else: ?> 账单已逾期，请还款<?php endif; ?><?php else: ?> <?php echo $status[$loaninfo->status]; ?><?php endif; ?></p>
            <div class="row mb30">
                <div class="col-xs-4 cor n26">状态</div>
                <div class="col-xs-8 text-right n26 "><span class="red"><?php if (($loaninfo->status == 12) || ($loaninfo->status == 13)): ?><?php if ((!empty($loaninfo->chase_amount)) && !empty($loaninfo->repay_time)): ?>待还款<?php elseif (date('Y-m-d H:i:s') < $loaninfo->end_date): ?>待还款<?php else: ?><?php echo $status[$loaninfo->status]; ?><?php endif; ?><?php else: ?><?php echo $status[$loaninfo->status]; ?><?php endif; ?></span></div>
            </div>
            <div class="border_bottom_1"></div>
            <?php if ($loaninfo->status == 11 && (is_null($loaninfo->chase_amount) || $loaninfo->chase_amount == 0)): ?>
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
                    <div class="row mb30">
                        <div class="col-xs-4 cor n26">到账金额</div>
                        <div class="col-xs-8 text-right n26"><span class="red">&yen;<?php echo $loaninfo->is_calculation == 1 ? sprintf('%.2f', $loaninfo->amount - $loaninfo->withdraw_fee) : sprintf('%.2f', $loaninfo->amount); ?></span></div>
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
                    <div class="row mb30">
                        <div class="col-xs-4 cor n26">借款期限</div>

                        <div class="col-xs-4 nPad">
                            <?php if ($business_type == 2 && $loaninfo->days == 1): ?>
                                <div class="assureC">隔夜还</div>
                            <?php endif; ?>
                        </div>

                        <div class="col-xs-4 text-right n26"><?php echo $loaninfo->days; ?>天</div>
                    </div>
                    <div class="row mb30">
                        <div class="col-xs-4 cor n26">优惠券减免</div>
                        <div class="col-xs-8 text-right n26"><span class="red">
                            <?php if (empty($loan_coupon)): ?>
                                &yen;0.00<?php else: ?>
                                    <?php if ($loan_coupon['val'] == 0): ?>
                                        <?php if (($loan_coupon['limit'] == 0) || ($loan_coupon['limit'] <= $loaninfo->current_amount)): ?>
                                全免<?php else: ?>
                                    <?php echo sprintf('%.2f', $loaninfo->coupon_amount); ?><?php endif; ?>
                                        <?php else: ?>
                                <!--sss-->
                                    <?php if($loaninfo->coupon_amount >=  $loaninfo->interest_fee): ?>
                                        -&yen;<?php echo sprintf('%.2f', $loaninfo->interest_fee); ?>
                                    <?php else: ?>
                                    <!--eee-->
                                -&yen;<?php echo sprintf('%.2f', $loaninfo->coupon_amount); ?>
                                <!--sss-->
                                <?php endif; ?>
                                <!--eee-->
                                    <?php endif; ?>
                                        <?php endif; ?>
                            </span></div>
                    </div>
                    <div class="row mb30">
                        <div class="col-xs-5 cor n26">点赞减息</div>
                        <div class="col-xs-7 text-right n26"><span class="red"><?php if ($loaninfo->like_amount > 0): ?>-&yen;<?php echo sprintf('%.2f', $loaninfo->like_amount); ?><?php else: ?>&yen;0.00<?php endif; ?></span></div>
                    </div>
                </div>
                <div class="adver border_top_1">
                    <?php if (($loaninfo->status != 3)): ?>
                        <div class="row mb30">
                            <div class="col-xs-4 cor n26">最后还款日</div>
                            <div class="col-xs-8 text-right n26"><span class="red"><?php if (!empty($loaninfo->end_date)): ?><?php echo date('Y-m-d', (strtotime($loaninfo->end_date) - 24 * 3600)); ?><?php else: ?>以短信推送时间为准<?php endif; ?></span></div>
                        </div>
                    <?php endif; ?>
                    <div class="row mb30">
                        <div class="col-xs-4 cor n26">到期应还款</div>
                        <div class="col-xs-8 text-right n26"><span class="red">&yen;<?php echo sprintf('%.2f', $loaninfo->huankuan_amount); ?></span></div>
                    </div>
                </div>
            <?php else: ?>
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
                    <div class="row mb30">
                        <div class="col-xs-4 cor n26">到账金额</div>
                        <div class="col-xs-8 text-right n26"><span class="red">&yen;<?php echo $loaninfo->is_calculation == 1 ? sprintf('%.2f', $loaninfo->amount - $loaninfo->withdraw_fee) : sprintf('%.2f', $loaninfo->amount); ?></span></div>
                    </div>
                    <?php if ($business_type != 2): ?>
                        <div class="row mb30">
                            <div class="col-xs-4 cor n26">服务费</div>
                            <div class="col-xs-8 text-right n26"><span class="red">&yen;<?php echo $loaninfo->is_calculation == 1 ? sprintf('%.2f', $loaninfo->withdraw_fee) : sprintf('%.2f', $service_amount); ?></span></div>
                        </div>
                    <?php endif; ?>
                    <div class="row mb30">
                        <div class="col-xs-4 cor n26">利息</div>
                        <div class="col-xs-8 text-right n26"><span class="red">&yen;<?php echo $loaninfo->is_calculation == 1 ? sprintf('%.2f', $service_amount) : '0.00'; ?></span></div>
                    </div>
                    <?php if ($loaninfo->chase_amount > 0): ?>
                        <div class="row mb30">
                            <div class="col-xs-4 cor n26">逾期罚息</div>
                            <div class="col-xs-8 text-right n26"><span class="red">&yen;<?php echo sprintf('%.2f', $loaninfo->chase_amount - $loaninfo->amount - $service_amount); ?></span></div>
                        </div>
                    <?php endif; ?>
                    <div class="row mb30">
                        <div class="col-xs-4 cor n26">借款期限</div>

                        <div class="col-xs-4 nPad">
                            <?php if ($business_type == 2 && $loaninfo->days == 1): ?>
                                <div class="assureC">隔夜还</div>
                            <?php endif; ?>
                        </div>

                        <div class="col-xs-4 text-right n26"><?php echo $loaninfo->days; ?>天</div>
                    </div>
                    <?php if (($loaninfo->status != 3)): ?>
                        <div class="row mb30">
                            <div class="col-xs-4 cor n26">应还款时间</div>
                            <div class="col-xs-8 text-right n26"><span class="red"><?php if (!empty($loaninfo->end_date)): ?><?php echo date('Y年m月d日', (strtotime($loaninfo->end_date) - 24 * 3600)); ?><?php else: ?>以短信推送时间为准<?php endif; ?></span></div>
                        </div>
                    <?php endif; ?>                    
                    <?php if ($loaninfo->chase_amount > 0): ?>
                        <div class="row mb30">
                            <div class="col-xs-4 cor n26">逾期天数</div>
                            <div class="col-xs-8 text-right n26"><span class="red"><?php echo ceil(abs(strtotime("now") - strtotime(date('Y-m-d', strtotime($loaninfo->end_date)))) / 86400); ?>天</span></div>
                        </div>
                    <?php endif; ?>
                    <?php if (($loaninfo->status == 3)): ?>
                        <div class="row mb30">
                            <div class="col-xs-4 cor n26">担保人：</div>
                            <div class="col-xs-8 text-right n26"><?php echo $guater->guater->realname; ?></div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="adver border_top_1">
                    <div class="row mb30">
                        <div class="col-xs-5 cor n26">逾期应还金额</div>
                        <div class="col-xs-7 text-right n26"><span class="red">&yen;<?php echo sprintf('%.2f', $loaninfo->huankuan_amount); ?></span></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <img src="/images/bottom.png" width="100%" style="vertical-align:top"/>
        <?php if ($now_time >= $start_time && $now_time <= $end_time): ?>
            <div style="padding: 10px 5%;color:red;">由于春节期间（2月5日－2月15日）工作人员放假，还款订单将在2月15日被确认，敬请谅解</div>
        <?php endif; ?>
        <?php if ((($loaninfo->status == 12) && empty($loaninfo->chase_amount)) || (($loaninfo->status == 13) && empty($loaninfo->chase_amount))): ?>
            <?php if ((!empty($loan_coupon) && ($loan_coupon['val'] == 0) && ($loan_coupon['status'] == 2)) || (!empty($loan_coupon) && (($loaninfo['interest_fee'] + $loaninfo['withdraw_fee']) <= $loaninfo['coupon_amount'] ))): ?>
                <a href="<?php echo Yii::$app->request->hostInfo . "/dev/share/freecoupon?uid=" . $loaninfo['user_id'] . "&loan_id=" . $loaninfo['loan_id']; ?>" class="btn1 mt20" style="width:100%">分享到朋友圈</a>
            <?php else: ?>
                <?php if ($loaninfo['business_type'] == 1 || $loaninfo['business_type'] == 4): ?>
                    <a href="<?php echo $shareurl; ?>" class="btn1 mt20" style="width:100%">分享到朋友圈</a>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
        <?php if (($loaninfo->status != 11) && ($loaninfo->status != 3) && ($loaninfo->status != 10) && ($loaninfo->status != 2)): ?>
            <?php if ($loaninfo->business_type != 2): ?>
                <a href="/dev/repay/repaychoose?loan_id=<?php  echo $loaninfo['loan_id']; ?>"><button class="btn mt20" style="width:100%">我要还款</button></a>
                    <?php endif; ?>
        <?php endif; ?>
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