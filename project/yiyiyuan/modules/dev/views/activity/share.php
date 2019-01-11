<div class="pic_index">
    <img src="/images/activity/pic2_1.jpg">
    <div class="active2">
        <img src="/images/activity/pic2_2.jpg">
    </div>
    <div class="active2">
        <img src="/images/activity/pic2_3.jpg">
        <a class="ljicj" id="invite"></a>
        <div class="jtyqh">
            <p>今天邀请的好友：  <em><?php echo $invite_detail; ?></em> 人</p>
            <p>邀请好友借款：        <em><?php echo $loan_detail; ?></em> 笔</p>
        </div>
        <a class="hdonggz tfenx"  href="/dev/activity/activityrule"></a>
    </div>
</div>
<div class="Hmask" style="display: none;"></div>
<div class="tanchuceng fenxang" style="display: none;">
    <img src="/images/activity/tan6.png">
</div>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    $('#invite').click(function () {
        $('.Hmask').show();
        $('.fenxang').show();
    });
    $('.Hmask').click(function () {
        $('.Hmask').hide();
        $('.fenxang').hide();
    });
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
            title: '好友给你个借钱不用还的机会，限时抢！',
            desc: '点击传送门发起借款',
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
            title: '好友给你个借钱不用还的机会，限时抢！',
            desc: '点击传送门发起借款',
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