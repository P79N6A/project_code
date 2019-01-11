<script>
    $(function() {

        var wHeight = $(window).height();
        var iHeight = $('.pic_bg').height();
        if (wHeight > iHeight) {
            $('.pic_bg').css('height', wHeight);
            $('.container').css('height', wHeight);
        } else {
            $('.container').css('height', iHeight);
            $('.light').css('top', '-6%');
        }

        $('.btn4').click(function() {
            var speak = $('input[name="speak"]').val();
            $.post("/dev/compensation/prizesave",{speak:speak},function(result){
                
            });
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

        $('.trees li').click(function() {
            $('.hidePrize')
                    .css('display', 'block')
                    .animate({
                        'width': '100%',
                        'height': '100%',
                        'top': '0',
                        'left': '0',
                        'opacity': '1'
                    })
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/dev/compensation/prize',
                async: false,
                success: function(data) {
                    $('.prize_' + data.prize_id).show();
                }
            });
        });
    });

</script>
<div class="container">
    <img src="/images/july/2.jpg" width="100%" class="pic_bg">
    <img src="/images/july/cj.png" width="100%" class="cj"> 
    <ul class="trees">
        <li><img src="/images/july/box.png" alt="" class="float-left"></li>
        <li><img src="/images/july/box.png" alt="" class="float-right"></li>
        <li><img src="/images/july/box.png" alt="" class="float-left"></li>
        <li><img src="/images/july/box.png" alt="" class="float-right"></li>
    </ul>

    <!-- 隐藏的奖品 -->
    <div class="hidePrize">
        <img src="/images/july/3.jpg" width="100%" class="pic_bg">
        <!-- 情侣水杯 -->
        <img src="/images/july/prize1.png" width="100%" class="light prize_2" style="display: none;">
        <img src="/images/july/pTxt1.png" width="100%" class="pTxt1 prize_2" style="display: none;"> 
        <!-- 雨伞 -->
        <img src="/images/july/prize2.png" width="100%" class="light prize_4" style="display: none;">
        <img src="/images/july/pTxt2.png" width="100%" class="pTxt1 prize_4" style="display: none;"> 
        <!-- 抱枕 -->
        <img src="/images/july/prize3.png" width="100%" class="light prize_3" style="display: none;">
        <img src="/images/july/pTxt3.png" width="100%" class="pTxt1 prize_3" style="display: none;"> 
        <!-- 济州岛 -->
        <img src="/images/july/prize4.png" width="100%" class="light prize_1" style="display: none;">
        <img src="/images/july/pTxt4.png" width="100%" class="pTxt1 prize_1" style="display: none;">

        <img src="/images/july/gxn.png" width="100%" class="img1">
        <input type="text" class="wb2" placeholder="一句想对Ta说的话" name="speak" style="border: 1px solid red;">
        <img src="/images/july/btn5.png" class="btn4">
        <img src="/images/july/ma.png" class="ma">

        <!-- 弹层 -->
        <div class="mask" style="display:none;"></div>
        <img src="/images/july/guide.png" class="guide" style="display:none;">
    </div>
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
            imgUrl: '<?php echo empty($userwx['head']) ? '/images/dev/face.png' : $userwx['head']; ?>',
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