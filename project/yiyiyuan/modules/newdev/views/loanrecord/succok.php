<?php
$status = array(
    '6' => '申请提现通过',
    '9' => '待还款',
    '10' => '申请提现通过',
);
?>

<div class="Hcontainer nP">
    <header class="header white">
        <p class="n26">状态：</p>
        <p class="n36 mb20 text-center"><?php echo $status[$loaninfo->status]; ?></p>
    </header>
    <img src="/images/title.png" width="100%"/>
    <div class="con">
        <div class="details">
            <div class="adver1 border_bottom_1">
                <div class="row mb30">
                    <div class="col-xs-4 cor n26 nPad">借款金额</div>
                    <div class="col-xs-4 nPad">
                        <?php if ($business_type == 2): ?>
                            <div class="assureC">担保</div>

                        <?php endif; ?>
                    </div>
                    <div class="col-xs-4 text-right n30 nPad"><span class="red">&yen;<?php echo sprintf('%.2f', $loaninfo->amount); ?></span></div>
                </div>
                <div class="row mb30">
                    <div class="col-xs-4 cor n26 nPad">到账金额</div>
                    <div class="col-xs-8 text-right n30 nPad"><span class="red">&yen;<?php echo $loaninfo->is_calculation == 1 ? sprintf('%.2f', $loaninfo->amount - $loaninfo->withdraw_fee) : sprintf('%.2f', $loaninfo->amount); ?></span></div>
                </div>
                <?php if ($business_type != 2): ?>
                    <div class="row mb30">
                        <div class="col-xs-3 cor n26">保险费</div>
                        <div class="col-xs-9 text-right n30"><span class="red">&yen;<?php echo $loaninfo->is_calculation == 1 ? sprintf('%.2f', $loaninfo->withdraw_fee) : sprintf('%.2f', $service_amount); ?></span></div>
                    </div>
                <?php endif; ?>
                <?php if ($business_type != 2): ?>
                    <div class="row mb30">
                        <div class="col-xs-3 cor n26">利息</div>
                        <div class="col-xs-9 text-right n30"><span class="red">&yen;<?php echo $loaninfo->is_calculation == 1 ? sprintf('%.2f', $service_amount) : '0.00'; ?></span></div>
                    </div>
                <?php endif; ?>
                <div class="row mb30">
                    <div class="col-xs-4 cor n26 nPad">借款期限</div>

                    <div class="col-xs-4 nPad">
                        <?php if ($business_type == 2 && $loaninfo->days == 1): ?>
                            <div class="assureC">隔夜还</div>
                        <?php endif; ?>
                    </div>

                    <div class="col-xs-4 text-right n30 nPad"><span class="red"><?php echo $loaninfo->days; ?></span>天</div>
                </div>
                <div class="row mb30">
                    <div class="col-xs-4 cor n26" style="padding-left: 0px;">优惠券减免</div>
                    <div class="col-xs-8 text-right n26" style="padding-right: 0px;"><span class="red">
                        <?php if (empty($loan_coupon)): ?>&yen;0.00
                            <?php else: ?><?php if ($loan_coupon['val'] == 0): ?>
                                <?php if (($loan_coupon->couponList['limit'] == 0) || ($loan_coupon->couponList['limit'] <= $loaninfo->current_amount)): ?>
                                    全免<?php else: ?>
                                    <?php echo sprintf('%.2f', $loaninfo->coupon_amount); ?>
                                <?php endif; ?>
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
                    <div class="col-xs-3 cor n26">点赞减息</div>
                    <div class="col-xs-9 text-right n26"><span class="red"><?php if ($loaninfo->like_amount > 0): ?>-&yen;<?php echo sprintf('%.2f', $loaninfo->like_amount); ?><?php else: ?>&yen;0.00<?php endif; ?></span></div>
                </div>
            </div>
            <div class="adver border_top_1">
                <div class="row mb30">
                    <div class="col-xs-5 cor n26">最后还款日</div>
                    <div class="col-xs-7 text-right n30 black"><?php if ($loaninfo->status == 9): ?><?php echo date('Y-m-d', (strtotime($loaninfo->end_date) - 24 * 3600)); ?><?php else: ?>以短信推送时间为准<?php endif; ?></div>
                </div>
                <div class="row">
                    <div class="col-xs-5 cor n26">应还款金额</div>
                    <div class="col-xs-7 text-right n48 red lh" >&yen;<?php echo sprintf('%.2f', $loaninfo['huankuan_amount']); ?></div>
                </div>
            </div>
        </div>
        <img src="/images/bottom.png" width="100%" style="vertical-align:top"/>

        <p class="red n26 text-center mt20 mb20">快找好友帮你<span class="n36">点赞</span>吧，最高可<span class="n36">减免</span>一半保险费哦～</p>
        <?php if ((!empty($loan_coupon) && ($loan_coupon['val'] == 0) && ($loan_coupon['status'] == 2)) || (!empty($loan_coupon) && $loaninfo['is_calculation'] == 0 && (($loaninfo['interest_fee'] + $loaninfo['withdraw_fee']) <= $loaninfo['coupon_amount'] )) || (!empty($loan_coupon) && $loaninfo['is_calculation'] == 1 && (($loaninfo['interest_fee']) <= $loaninfo['coupon_amount'] ))): ?>
            <a href="<?php echo Yii::$app->request->hostInfo . "/dev/share/freecoupon?uid=" . $loaninfo['user_id'] . "&loan_id=" . $loaninfo['loan_id']; ?>" class="btn1 mt20" style="width:100%">分享到朋友圈</a>
        <?php else: ?>
            <?php if ($loaninfo['business_type'] == 1 || $loaninfo['business_type'] == 4): ?>
                <a href="<?php echo $shareurl; ?>" class="btn1 mt20" style="width:100%">分享到朋友圈</a>
            <?php endif; ?>
        <?php endif; ?>
        <?php if ($loaninfo['status'] == 9 && $loaninfo['business_type'] != 2): ?>
            <a href="/dev/repay/repaychoose?loan_id=<?php echo $loaninfo['loan_id']; ?>"><button class="btn mt20" style="width:100%">我要还款</button></a>
            <!--?loan_id=666/dev/repay/cards-->
        <?php endif; ?>
    </div>
    <div class="main">
        <?php if ($loaninfo->credit_amount > 0): ?>
            <div class="border_bottom mt20 pb20">
                <img class="face" src="<?php echo empty($userinfo->head) ? "/images/dev/face.png" : $userinfo->head; ?>"/>
                <div class="info_list">
                    <div class="row n28">
                        <div class="col-xs-12"><a><?php if (!empty($userinfo->nickname)): ?><?php echo $userinfo->nickname; ?><?php else: ?><?php echo $userinfo->user->realname; ?><?php endif; ?></a></div>
                    </div>
                    <div class="row n22">
                        <div class="col-xs-12 ch mt3"><?php echo $loaninfo['create_time']; ?></div>
                    </div>
                </div>
                <div class="money">
                    <img src="<?php if ($loaninfo['type'] == '1') { ?>/images/good.png<?php } else { ?>/images/edunei.png<?php } ?>" width="45%" class="float-left" style="vertical-align:text-bottom;"/> <div class="float-right mt10"><span class="red"><?php echo sprintf('%.2f', $loaninfo['credit_amount']); ?></span>点</div>
                </div>
            </div>
        <?php endif; ?>
        <?php foreach ($loanrecord as $loan) { ?>
            <div class="border_bottom mt20 pb20">
                <img class="face" src="<?php echo empty($loan['head']) ? "/images/dev/face.png" : $loan['head']; ?>"/>
                <div class="info_list">
                    <div class="row n28">
                        <div class="col-xs-12"><a><?php if (!empty($loan['nickname'])): ?><?php echo $loan['nickname']; ?><?php else: ?><?php echo $loan['realname']; ?><?php endif; ?></a></div>
                    </div>
                    <div class="row n22">
                        <div class="col-xs-12 ch mt3"><?php echo $loan['create_time']; ?></div>
                    </div>
                </div>
                <div class="money">
                    <img src="<?php if ($loan['type'] == '1') { ?>/images/good.png<?php } else { ?>/images/borrow.png<?php } ?>" width="35%" class="float-left" style="vertical-align:text-bottom;"/> <div class="float-right mt10"><span class="red"><?php echo sprintf('%.2f', $loan['amount']); ?></span>点</div>
                </div>
            </div>
        <?php } ?>

    </div>
</div>
