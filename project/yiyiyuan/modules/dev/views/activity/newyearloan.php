<style>
body{
    background: #fde7cf;
}
</style>
<div class="yiyyn">
	<div><img src="/images/activity/indexone.jpg"></div>
	<img src="/images/activity/hdgze.jpg">
	<div class="bghsezi wanxmes">
		<div class="fqjkh">
			<p class="fqik1">发起借款后</p>
			<p class="fqik2">中奖率提升至10%</p>
		</div>
		<div class="wcrwu1"><img src="/images/activity/wcrwu1.png"></div>
		<div class="button"> <button><a href = "/dev/loan">发起借款</a></button></div>
                <p class="hzgze"><a href = "/dev/activity/newyearrule"><span>活动规则</span><img src="/images/activity/hdgz.png"></a></p>
	</div>
	
</div>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    wx.config({
        debug: false,
        appId: '<?php echo $jsinfo['appid']; ?>',
        timestamp: '<?php echo $jsinfo['timestamp']; ?>',
        nonceStr: '<?php echo $jsinfo['nonceStr']; ?>',
        signature: '<?php echo $jsinfo['signature']; ?>',
        jsApiList: [
            'onMenuShareTimeline',
            'onMenuShareAppMessage',
            'showOptionMenu'
        ]
    });

    wx.ready(function () {
        wx.showOptionMenu();
        // 2. 分享接口
        // 2.1 监听“分享给朋友”，按钮点击、自定义分享内容及分享结果接口
        wx.onMenuShareAppMessage({
            title: '过年送手机！',
            desc: '过年送手机，就送iPhone7，都来先花花，一起有钱花。',
            link: '<?php echo $shareUrl; ?>',
            imgUrl: '<?php echo!empty($user->userwx) && !empty($user->userwx->head) ? $user->userwx->head : '/images/dev/face.png'; ?>',
            trigger: function (res) {
                // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
            },
            success: function (res) {
                // 	    	  window.location = "/dev/invest";
            },
            cancel: function (res) {
            },
            fail: function (res) {
            }
        });

        // 2.2 监听“分享到朋友圈”按钮点击、自定义分享内容及分享结果接口
        wx.onMenuShareTimeline({
            title: '过年送手机！',
            desc: '过年送手机，就送iPhone7，都来先花花，一起有钱花。',
            link: '<?php echo $shareUrl; ?>',
            imgUrl: '<?php echo!empty($user->userwx) && !empty($user->userwx->head) ? $user->userwx->head : '/images/dev/face.png'; ?>',
            trigger: function (res) {
                // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
            },
            success: function (res) {
// 	    	  window.location = "/dev/invest";
            },
            cancel: function (res) {
            },
            fail: function (res) {
                alert(JSON.stringify(res));
            }
        });
    });
</script>