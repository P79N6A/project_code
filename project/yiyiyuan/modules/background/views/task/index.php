<div class="wrap zqewym friends">
	<div><img src="/images/webunion/banner2.png"></div>
	<a href="/background/task/taskreg"><section class="list listsy">
		<div class="renzone">邀请任务</div>
		<div class="nametwo">邀请好友得现金</div>
		<div><?php echo $task_reg ?>/3</div>
		<div class="pthree"><img src="/images/webunion/s2y.png"></div>
		
	</section></a>
	<a href="/background/task/taskaut"><section class="list listsy">
		<div class="renzone">认证任务</div>
		<div class="nametwo">答题认证领优惠券</div>
		<div><?php echo $task_aut ?>/5</div>
		<div class="pthree"><img src="/images/webunion/s2y.png"></div>
	</section></a>
</div>
<script>
    $('.nav_right').click(function(){
        window.location.href = '<?php echo $returnUrl ?>';
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

    wx.ready(function() {
        wx.hideOptionMenu();
    });
</script>