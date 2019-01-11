<script type="text/javascript">
    (function () {
        _fmOpt = {
            partner: 'xianhuahua',
            appName: 'xianhh_web',
            token: '<?php echo $_COOKIE['PHPSESSID'] ?>',
        };
        var cimg = new Image(1, 1);
        cimg.onload = function () {
            _fmOpt.imgLoaded = true;
        };
        cimg.src = "https://fp.fraudmetrix.cn/fp/clear.png?partnerCode=xianhuahua&appName=xianhh_web&tokenId=" + _fmOpt.token;
        var fm = document.createElement('script');
        fm.type = 'text/javascript';
        fm.async = true;
        fm.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'static.fraudmetrix.cn/fm.js?ver=0.1&t=' + (new Date().getTime() / 3600000).toFixed(0);
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(fm, s);
    })();
</script>
<div class="Hcontainer">
    <img src="/images/title.png" width="100%"/>
    <div class="con">
        <div class="details">
            <div class="adver1 border_bottom_1">
                <div class="row mb30">
                    <div class="col-xs-3 cor n26">借款金额</div>
                    <div class="col-xs-9 text-right n26"><span class="red">&yen;<?php echo sprintf('%.2f', $amount); ?></span></div>
                </div>
                <div class="row mb30">
                    <div class="col-xs-3 cor n26">到账金额</div>
                    <div class="col-xs-9 text-right n26"><span class="red">&yen;<?php echo $loaninfo->is_calculation == 1 ? sprintf('%.2f', $amount - $withdraw_fee) : sprintf('%.2f', $amount); ?></span></div>
                </div>
                <div class="row mb30">
                    <div class="col-xs-3 cor n26">借款期限</div>
                    <div class="col-xs-9 text-right n26"><span class="red"><?php echo $days; ?></span>天</div>
                </div>
                <div class="row mb30">
                    <div class="col-xs-3 cor n26">借款用途</div>
                    <div class="col-xs-9 text-right n26"><?php echo $desc; ?></div>
                </div>
                <div class="row mb30">
                    <div class="col-xs-3 cor n26">服务费</div>
                    <div class="col-xs-9 text-right n26"><span class="red">&yen;<?php echo $loaninfo->is_calculation == 1 ? $withdraw_fee : $withdraw_fee + $interest_fee; ?></span></div>
                </div>
                <div class="row mb30">
                    <div class="col-xs-3 cor n26">利息</div>
                    <div class="col-xs-9 text-right n26"><span class="red">&yen;<?php echo $loaninfo->is_calculation == 1 ? $interest_fee : '0.00'; ?></span></div>
                </div>
                <div class="row mb30">
                    <div class="col-xs-3 cor n26">优惠券</div>
                    <div class="col-xs-9 text-right n26"><span class="red"><?php if ($coupon_amount == 0): ?>&yen;0.00<?php else: ?>-&yen;<?php echo $coupon_amount; ?><?php endif; ?></span></span></div>
                </div>
                <div class="row mb30">
                    <div class="col-xs-3 cor n26">到期应还</div>
                    <div class="col-xs-9 text-right n26"><span class="red">&yen;<?php echo sprintf('%.2f', $repay_amount); ?></span></div>
                </div>
            </div>
            <div class="adver border_top_1">
                <p class="mb30 n26"><?php echo $userbank->bank_name; ?></p>
                <p class="pb30 n26"><?php echo '尾号' . substr($userbank->card, -4); ?></p>
            </div>
        </div>
        <form method="post" action="/dev/loan/conwd">
            <input type="hidden" name="loan_id" value="<?php echo $loan_id; ?>">
            <img src="/images/bottom.png" width="100%" style="vertical-align:top"/>
            <p class="mb10 n26 mt10">
                <input type="checkbox" checked="checked" id="agree_loan_xieyi" class="regular-checkbox">
                <label for="agree_loan_xieyi"></label>
                阅读并同意
                <a href="/dev/loan/agreeloan?type=conwd&desc=<?php echo urlencode($desc); ?>&days=<?php echo $days; ?>&amount=<?php echo $amount; ?>&repay_amount=<?php echo $repay_amount; ?>&loan_id=<?php echo $loan_id; ?>" target="_blank" class="underL">《先花一亿元居间协议及借款协议》</a></p>
            <button type="submit" class="btn" style="width:100%;" >确定</button>
        </form>
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