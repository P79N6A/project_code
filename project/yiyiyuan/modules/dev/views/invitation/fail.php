<div class="fInvitation">
    <div class="lingqvxj513">
        <h3 class="garyeee">认证失败！</h3>
        <div class="errormes">
            <img src="/images/firstimg5133.png">
        </div>
        <?php if ($num >= 2): ?>
            <p>下载APP领取你的<span>66</span>元优惠券红包</p>
        <?php else : ?>
            <p>仅剩<span> 1 </span>次答题机会！请重新认证</p>
        <?php endif; ?>
    </div>
    <?php if ($num >= 2): ?>
    <div class="button"> <button type="button" onclick="javascript:window.location='http://mp.yaoyuefu.com/dev/ds/down'">下载领取</button></div>    
    <?php else : ?>
        <div class="button"> <button type="button" onclick="javascript:window.location ='<?php echo $shareUrl; ?>'">重新认证</button>
        </div>
    <?php endif; ?>

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