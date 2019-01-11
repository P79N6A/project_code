<div class="wrap moneymore">
    <section class="borderbottom">
        <div class="lefts">
            <div><em> <?php echo sprintf("%.2f", intval($interset * 100) / 100) ?></em><span>RMB</span></div>
            <div class="fontsize13">账户余额</div>
            <div id="withdrawals" class="tixian">提现</div>
        </div>
        <div class="line paddinglin"></div>
        <div class="lefts">
            <div><em> <?php echo $flow ?></em><span>MB</span></div>
            <div class="fontsize13">流量</div>
            <div id="receive" class="tixian">领取</div>
        </div>
    </section>
    <section class="borderbottom">
        <div id="income" class="lefts">
            <div class="rmbhq"><em> <?php echo sprintf("%.2f", $yestoday_income) ?></em><span>RMB</span></div>
            <div class="shouyitxian">昨日收益></div>
        </div>
        <div class="line height42"></div>
        <div class="lefts">
            <div class="rmbhq"><em> <?php echo sprintf("%.2f", $total_history_interest) ?></em><span>RMB</span></div>
            <div class="shouyitxian">累计收益</div>
        </div>
    </section>
    <section class="borderbottom">
        <div id="frozen" class="lefts">
            <div class="rmbhq"><em> <?php echo sprintf("%.2f", $frozen_income) ?></em><span>RMB</span></div>
            <div class="shouyitxian">冻结收益></div>
        </div>
        <div class="line height42"></div>
        <div class="lefts">
            <div class="rmbhq"><em> <?php echo $score ?></em></div>
            <div class="shouyitxian">我的积分</div>
        </div>
    </section>
</div>	


<script>
    $('.nav_right').click(function () {
        window.location.href = '<?php echo $returnUrl ?>';
    })
</script>
<script>
    $(function () {
        $('.symx_gzgz').click(function () {
            var click = $(this).attr('click');
            $('.symx_gzgz').removeClass('symxgray');
            $('.symx_right').removeClass('three');
            $('.symx_jycont').hide();
            if (click != 1) {
                $(this).addClass('symxgray');
                $(this).find('.symx_right').addClass('three');
                $(this).siblings('.symx_jycont').show();
                $(this).attr('click', 1);
            } else {
                $(this).attr('click', 0);
            }
        })
        $('#frozen').click(function () {
            window.location.href = "/background/wallet/frozeninterest"
        })
        $('#income').click(function () {
            window.location.href = "/background/wallet/yestodayincome";
        })
        $('#withdrawals').click(function () {
            window.location.href = "/background/receive/index";
        })
        $('#receive').click(function () {
            window.location.href = "/background/receive/flow";
        })
    })
</script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    wx.config({
        debug: false,
        appId: '<?php echo $jsinfo['appid']; ?>',
        timestamp: <?php echo $jsinfo['timestamp']; ?>,
        nonceStr: '<?php echo $jsinfo['nonceStr']; ?>',
        signature: '<?php echo $jsinfo['signature']; ?>',
        jsApiList: [
            'closeWindow',
            'hideOptionMenu'
        ]
    });

    wx.ready(function () {
        wx.hideOptionMenu();
    });
</script>