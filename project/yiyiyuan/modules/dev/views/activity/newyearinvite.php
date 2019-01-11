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
			<p class="fqik1">邀请好友达到10人后</p>
			<p class="fqik2">中奖率提升50%</p>
		</div>
		<div class="dqayq">当前已邀请<?=$invite_num?>人</div>
		<div class="wcrwu1"><img src="/images/activity/wcrwu2.png"></div>
		<div class="button"> <button class = "yqhy">邀请好友</button></div>
		<p class="hzgze"><a href = "/dev/activity/newyearrule"><span>活动规则</span> <img src="/images/activity/hdgz.png"></a></p>
	</div>
	
</div>
<!--分享-->
<div class="tancymia heihadd newyear_fx" style="display: none;">
	<img src="/images/activity/shareffx.png">
	<a class="tcerror"></a>
	<a href ="/dev/activity/ascension?user_id=<?=$user->user_id?>" class="tancone"></a>
</div>
<!--蒙层背景-->
<div class="Hmask_newyear" style="display: none;"></div>


<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    //点击邀请弹出蒙层
    $('.yqhy').click(function () {
        $('.Hmask_newyear').show();
        $('.newyear_fx').show();
    });
    //点击图片或蒙层关闭
    $('.Hmask_newyear').click(function () {
        $('.Hmask_newyear').hide();
        $('.newyear_fx').hide();
    });
    $('.newyear_fx').click(function(){
        $('.Hmask_newyear').show();
        $('.newyear_fx').show();
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
            title: '过年送手机！',
            desc: '过年送手机，就送iPhone7，都来先花花，一起有钱花。',
            link: '<?php echo $shareUrl; ?>',
            imgUrl: '<?php echo!empty($user->userwx) && !empty($user->userwx->head) ? $user->userwx->head : '/images/dev/face.png'; ?>',
            trigger: function (res) {
                // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
            },
            success: function (res) {
                              $('.Hmask_newyear').hide();
                              $('.heihadd').hide();
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
                              $('.Hmask_newyear').hide();
                              $('.heihadd').hide();
            },
            cancel: function (res) {
            },
            fail: function (res) {
                alert(JSON.stringify(res));
            }
        });
    });
</script>
