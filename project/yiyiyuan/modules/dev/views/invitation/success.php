<div class="fInvitation">
    <div class="lingqvxj513">
        <h3>认证成功！</h3>
        <div class="lqmesone">
            <img src="/images/firstimgdj.png">
            <?php if ($money == '抢光了'): ?>
                <div class="yuanqian qianggle">66元</div>
            <?php else: ?>
                <div class="yuanqian">66元</div>
            <?php endif; ?>
        </div>
        <p><span>66元</span>优惠券红包已存入账户<span><?php echo $user->mobile; ?></span></p>
    </div>
    <div class="button"> 
        <button type="button" onclick="javascript:window.location = 'http://mp.yaoyuefu.com/dev/ds/down'"> 下载领取 </button>
    </div >
</div>
<script src ="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
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