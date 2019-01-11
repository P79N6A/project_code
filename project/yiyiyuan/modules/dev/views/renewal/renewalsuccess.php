<div class="hkcguy">
    <img class="click" src="/images/click.png">
    <p>您的续期操作已经提交，实际续期情况正在确认中，如有疑问请咨询先花一亿元微信客服：<span>先花一亿元</span></p>
    <img src="/images/clicktwo_renewal.png">
</div>
<?php if($source=='weixin'):?>
<button class="btntn queding" onclick="javascript:location.href = '/dev/loan'">确定</button>
<?php endif;?>
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