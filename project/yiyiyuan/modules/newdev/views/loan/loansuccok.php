<div class="zuoyminew jk_item">
    <div class="amountyem">
        <span style="">通过APP借款额度更高哦～</span>
        <a href="/dev/ds/down">
            <button style="">去下载</button>
        </a>
    </div>
    <div class="shezhiminay">
        <?php if ($loanstatus == 9){?>
            <img  style="width:30%;" src="/images/daihk.png">
        <?php } ?>
        <?php if ($loanstatus == 11){?>
            <img  style="width:30%;" src="/images/daihk3.png">
        <?php } ?>
        <!-- 判断是否在选中的用户中-->
        <?php if($loaninfo->number != 0): ?>
            <p class = "yq">当前已续期<em><?php echo $loaninfo->number; ?></em>次</p>
        <?php endif; ?>
        <?php if ($loanstatus == 9){?>
            <div class="imgimgnew"><img src="/images/daihaik2.png"></div>
        <?php } ?>
        <?php if ($loanstatus == 11){?>
            <div class="imgimgnew"><img src="/images/daihaik3.png"></div>
        <?php } ?>
        <div class="txtsty">
            <?php if ($loanstatus == 9){?>
                <div class="bse">待还款</div>
                <div>还款确认中</div>
            <?php } ?>
            <?php if ($loanstatus == 11){?>
                <div>待还款</div>
                <div class="bse">还款确认中</div>
            <?php } ?>
            <div>借款已还清</div>
        </div>
    </div>
    <div class="daihukan_cont">
        <div class="daoqihk">到期应还（元） <span><?php echo sprintf('%.2f', $loaninfo['huankuan_amount']); ?></span></div>
        <div class="rowym">
            <div class="corname">借款金额（元）</div>
            <div class="corliyou" ><?php echo sprintf('%.2f', $loaninfo->amount); ?></div>
        </div>
        <div class="rowym">
            <div class="corname">到账金额（元）</div>
            <div class="corliyou"> <?php echo $loaninfo->is_calculation == 1 ? sprintf('%.2f', $loaninfo->amount - $loaninfo->withdraw_fee) : sprintf('%.2f', $loaninfo->amount); ?></div>
        </div>
        <div class="rowym">
            <div class="corname">保险费（元）  </div>
            <div class="corliyou" ><?php echo sprintf('%.2f', $loaninfo->withdraw_fee); ?></div>
        </div>
        <div class="rowym">
            <div class="corname">利息（元）</div>
            <div class="corliyou" ><?php echo sprintf('%.2f', $loaninfo->interest_fee) ; ?></div>
        </div>
        <div class="rowym">
            <div class="corname">点赞减息（元）</div>
            <div class="corliyou" ><?php if ($loaninfo->like_amount > 0): ?>-<?php echo sprintf('%.2f', $loaninfo->like_amount); ?><?php else: ?>0.00<?php endif; ?></div>
        </div>
        <?php if($loanstatus == 9 || $loanstatus == 11): ?>
            <div class="rowym">
                <div class="corname">逾期罚息（元）</div>
                <div class="corliyou" ><?php echo sprintf('%.2f', $punishment); ?></div>
            </div>
        <?php endif; ?>
        <div class="rowym">
            <div class="corname">优惠券减免（元）</div>
            <div class="corliyou" >
                <?php if (empty($loan_coupon)): ?>
                    0.00
                <?php else: ?>
                    <?php if ($loan_coupon->couponList->val == 0): ?>
                        <?php if (($loan_coupon->couponList['limit'] == 0) || ($loan_coupon->couponList['limit'] <= $loaninfo->current_amount)): ?>
                                全免
                        <?php else: ?>
                                <?php echo sprintf('%.2f', $loaninfo->coupon_amount); ?>
                        <?php endif; ?>
                    <?php else: ?>
                            <?php if($loaninfo->coupon_amount >=  $loaninfo->interest_fee): ?>
                                -<?php echo sprintf('%.2f', $loaninfo->interest_fee); ?>
                            <?php else: ?>
                                -<?php echo sprintf('%.2f', $loaninfo->coupon_amount); ?>
                            <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="rowym">
            <div class="corname">借款期限（天）</div>
            <div class="corliyou" ><?php echo $loaninfo->days; ?></div>
        </div>
        <div class="rowym">
            <div class="corname">最后还款日</div>
            <div class="corliyou" >
                <?php if (!empty($loaninfo->end_date)): ?><?php echo date('Y年m月d日', (strtotime($loaninfo->end_date) - 24 * 3600)); ?><?php else: ?>以短信推送时间为准<?php endif; ?>
            </div>
        </div>
    </div> 
    <?php if ((!empty($loan_coupon) && ($loan_coupon->couponList->val == 0) && ($loan_coupon['status'] == 2)) || (!empty($loan_coupon) && $loaninfo['is_calculation'] == 0 && (($loaninfo['interest_fee'] + $loaninfo['withdraw_fee']) <= $loaninfo['coupon_amount'] )) || (!empty($loan_coupon) && $loaninfo['is_calculation'] == 1 && (($loaninfo['interest_fee']) <= $loaninfo['coupon_amount'] ))): ?>
        <a href="<?php echo Yii::$app->request->hostInfo . "/dev/share/freecoupon?uid=" . $loaninfo['user_id'] . "&loan_id=" . $loaninfo['loan_id']; ?>" class="btn1 mt20" style="width:100%">    
<!--            <button type="submit" class="bgrey hanhaoyou" >分享到朋友圈</button></a>-->
            <button type="submit"></button></a>
    <?php else: ?>
        <?php if ($loanstatus == 9 || $loanstatus == 6){ ?>
            <a href="<?php echo $shareurl; ?>" class="btn1 mt20" style="width:100%"><button type="submit" class="bgrey hanhaoyou" >喊好友减息</button></a>
        <?php } ?>
    <?php endif; ?>
    <?php if ($loanstatus == 9): ?>
        <a href="/new/repay/repaychoose?loan_id=<?php echo $loaninfo['loan_id']; ?>"><button type="button" class="bgrey">我要还款</button></a>
    <?php endif; ?>
        <div style="height: 80px;"></div>
</div>
<?= $this->render('/layouts/_page', ['page' => 'loan']) ?>
<!--申请借款但还未活体认证-->
<?php if($user->status != 3 && $loanstatus == 6): ?>
<div class="Hmask Hmask_none" ></div>
<div class="duihsucc">
    <p class="xuhua">您的借款已通过审核！</p>
    <p>下载APP完成视频认证后立即领取借款</p>
    <button class="sureyemian" id = "loansuccok_down">下载领取</button>
</div> 
<?php endif; ?>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    $(function() {
        //点击下载app统计
        $('#loansuccok_down').bind('click', function () {
            $.get("/wap/st/statisticssave", {type: 88}, function () {
                window.location = '/wap/st/down';
                return false;
            })
        })

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
    })
</script>
