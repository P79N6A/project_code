<style>
    body{
        background: #cd2c24;
    }
</style>
<div class="shengdan12">
	<div><img src="/images/activity/masyaoq.jpg"></div>
	<div class="chenmo">
		<button class="mashyq"><img src="/images/activity/masyaoq.png"></button>
		<a class="hdguze"  href="/dev/activity/christmasrule"><img src="/images/activity/hdguze.png"></a>
	</div>
	<div class="bottomdbu"><img src="/images/activity/bottomb.png"></div>
</div>
<!--弹窗-->
<div class="Hmask" style="display: none;"></div>
<!-- 弹出层文字 -->
<div class="tanchuceng christmas_fenxang" style="display: none;">
    <img src="/images/activity/sharefx.png">
</div>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    //点击邀请弹出蒙层
    $('.mashyq').click(function () {
        $('.Hmask').show();
        $('.christmas_fenxang').show();
    });
    //点击图片或蒙层关闭
    $('.Hmask').click(function () {
        $('.Hmask').hide();
        $('.christmas_fenxang').hide();
    });
    $('.christmas_fenxang').click(function(){
        $('.Hmask').hide();
        $('.christmas_fenxang').hide();
    })
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
            title: '双蛋钜惠 借钱不用还',
            desc: '好友给你一次借钱不用还的机会，快来借款吧！',
            link: '<?php echo $shareUrl; ?>',
            imgUrl: '<?php echo!empty($user->userwx) && !empty($user->userwx->head) ? $user->userwx->head : '/images/dev/face.png'; ?>',
            trigger: function (res) {
                // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
            },
            success: function (res) {
                // 	    	  window.location = "/dev/invest";
                              $('.Hmask').hide();
                              $('.christmas_fenxang').hide();
            },
            cancel: function (res) {
            },
            fail: function (res) {
            }
        });

        // 2.2 监听“分享到朋友圈”按钮点击、自定义分享内容及分享结果接口
        wx.onMenuShareTimeline({
            title: '双蛋钜惠 借钱不用还',
            desc: '好友给你一次借钱不用还的机会，快来借款吧！',
            link: '<?php echo $shareUrl; ?>',
            imgUrl: '<?php echo!empty($user->userwx) && !empty($user->userwx->head) ? $user->userwx->head : '/images/dev/face.png'; ?>',
            trigger: function (res) {
                // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
            },
            success: function (res) {
// 	    	  window.location = "/dev/invest";
                              $('.Hmask').hide();
                              $('.christmas_fenxang').hide();
            },
            cancel: function (res) {
            },
            fail: function (res) {
                alert(JSON.stringify(res));
            }
        });
    });
</script>
