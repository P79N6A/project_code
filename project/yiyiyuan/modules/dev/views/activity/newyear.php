<style>
body{
    background: #fde7cf;
}
</style>
<div class="yiyyn">
	<div><img src="/images/activity/indexone.jpg"></div>
	<img src="/images/activity/index2.jpg">
	<img src="/images/activity/phone3.png">
	
	<div class="bghsezi">
		<div class="button inouth"> <button><!--<input type="text" placeholder="输入手机号">--><?=$user->mobile?></button></div>
		<div class="button trjc"> <button>投入奖池拿iPhone7</button></div>
		<div class="button chaxzjg"> <button><a href ="/dev/activity/ascension?user_id=<?=$user->user_id?>">查询中奖结果</a></button></div>
		<p class="hzgze"><a href = "/dev/activity/newyearrule"><span>活动规则</span> <img src="/images/activity/hdgz.png"></a></p>
	
	</div>
</div>

<!--已投入奖池-->
<div class="tancymia heihadd" style="display: none;">
	<img src="/images/activity/yemianhm22.png">
	<a class="tcerror"></a>
	<a href ="/dev/activity/ascension?user_id=<?=$user->user_id?>" class="tancone"></a>
</div>
<!--蒙层背景-->
<div class="Hmask_newyear" style="display: none;"></div>



<!-- 弹出层文字 -->
<div class="tanchuceng christmas_fenxang" style="display: none;">
    <img src="/images/activity/sharefx.png">
</div>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    var user_id = <?php echo $user->user_id; ?>;
    //点击图片或蒙层关闭
    $('.Hmask_newyear').click(function () {
        $('.Hmask_newyear').hide();
        $('.heihadd').hide();
    });
//    $('.heihadd').click(function () {
//        $('.Hmask_newyear').hide();
//        $('.heihadd').hide();
//    });
    //投入奖池
    $('.trjc').click(function(){
        $.post('/dev/activity/castprize', {user_id: user_id}, function (info) {
            var data = eval('(' + info + ')');
            console.log(data);
            $('.Hmask_newyear').show();
            $('.heihadd').show();
        });
    })
    //点击关闭弹窗
    $('.tcerror').click(function(){
        $('.Hmask_newyear').hide();
        $('.heihadd').hide();
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
