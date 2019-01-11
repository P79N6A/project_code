 <div class="todaysy">
            <p class="todaysy_img"></p>
            <p class="todaytxt_one">累计收益</p>
            <p class="todaytxt_two"><?php echo number_format($total_history_interest,2, ".", "");?>RMB</p>
            <p class="todaytxt_three">赚钱妖怪</p>
        </div>
        <div>曲线图</div>
		<img src='/background/webunion/grapf'/>
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

    wx.ready(function() {
        wx.hideOptionMenu();
    });
</script>