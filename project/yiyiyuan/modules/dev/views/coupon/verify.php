<div class="jdall">
    <div>
        <img src="/images/coupon/message3.png">
    </div>    
    <div class="messtutu">
        <img src="/images/coupon/messagetutu.png">
        <p class="zilaox">资料已提交，正在审核中</p>
        <p class="zilaox2">请注意查收短信通知！</p>
    </div>

    <div class="button"> <button onclick="javascirpt:window.location = '<?php echo $diversion_from; ?>'">完成</button></div>

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