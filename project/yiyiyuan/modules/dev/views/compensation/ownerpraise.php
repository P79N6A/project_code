<?php ?>
<script>
    $(function() {
        var wHeight = $(window).height();
        var iHeight = $('.pic_bg').height();
        if (wHeight > iHeight) {
            $('.pic_bg').css('height', wHeight);
            $('.container').css('height', wHeight);
        } else {
            $('.container').css('height', iHeight);
        }
    });

</script>
<div class="container">
    <img src="/images/july/4.jpg" width="100%" class="pic_bg">
    <img src="/images/july/aaa.png" width="100%" class="title">
    <p class="txt2">恭喜您获得花二哥<?php echo $prize[$seven_prize->prize_id]; ?>！</p>
    <img src="<?php echo Yii::$app->params['back_url'].'/'.$seven_prize->pic;?>" class="upLoadImg" style="top:34%;margin:auto; left: 0;  right: 0;">

    <div class="heart">
        <img src="/images/july/left_heart.png" class="left_h"></i>收到了 <span class="n24"><?php echo $seven_click; ?>/77</span> 份祝福<img src="/images/july/right_heart.png" class="right_h"></i><br>
        <img src="/images/july/left_heart.png" class="left_h"></i><span style="font-size: 12px;">集满 <span class="n24">77</span> 份祝福，可以马上领取奖品</span><img src="/images/july/right_heart.png" class="right_h"></i>
    </div>
    <img src="/images/july/btn5.png" class="btn5">

    <!-- 弹层 -->
    <div class="mask" style="display:none;"></div>
    <img src="/images/july/guide.png" class="guide" style="display:none;">
</div>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    $('.btn5').click(function() {       
        $('.mask').show();
        $('.guide').show();
    });
    $('.mask').click(function() {
        $('.mask').hide();
        $('.guide').hide();
    });
    $('.guide').click(function() {
        $('.mask').hide();
        $('.guide').hide();
    });

    wx.config({
        debug: false,
        appId: '<?php echo $jsinfo['appid']; ?>',
        timestamp: <?php echo $jsinfo['timestamp']; ?>,
        nonceStr: '<?php echo $jsinfo['nonceStr']; ?>',
        signature: '<?php echo $jsinfo['signature']; ?>',
        jsApiList: [
            'onMenuShareTimeline',
            'onMenuShareAppMessage',
            'showOptionMenu'
        ]
    });

    wx.ready(function() {
        wx.showOptionMenu();
        // 2. 分享接口
        // 2.1 监听“分享给朋友”，按钮点击、自定义分享内容及分享结果接口
        wx.onMenuShareAppMessage({
            title: '<?php echo $share_title['title'];?>',
            desc: '<?php echo $share_title['desc'];?>',
            link: '<?php echo $shareUrl; ?>',
            imgUrl: '<?php echo empty($user->userwx->head) ? '//images/july/dev/face.png' : $user->userwx->head; ?>',
            trigger: function(res) {
                // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
            },
            success: function(res) {
// 	    	  window.location = "/dev/invest";
            },
            cancel: function(res) {
            },
            fail: function(res) {
            }
        });

        // 2.2 监听“分享到朋友圈”按钮点击、自定义分享内容及分享结果接口
        wx.onMenuShareTimeline({
            title: '<?php echo $share_title['title'];?>',
            desc: '<?php echo $share_title['desc'];?>',
            link: '<?php echo $shareUrl; ?>',
            imgUrl: '<?php echo empty($user->userwx->head) ? '//images/july/dev/face.png' : $user->userwx->head; ?>',
            trigger: function(res) {
                // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
            },
            success: function(res) {
// 	    	  window.location = "/dev/invest";
            },
            cancel: function(res) {
            },
            fail: function(res) {
                alert(JSON.stringify(res));
            }
        });
    });
</script>