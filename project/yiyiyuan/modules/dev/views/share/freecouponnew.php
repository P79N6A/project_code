<style type="text/css">
html,body{width:100%;height:100%; background: #fafafa; font-family: "Microsoft YaHei"; position: relative;}
.l-container {
	width: 100%;
	margin: 0 auto;
	position: relative;
}
.l-container div img{ width: 100%; display: block; }
.l-container div.woyaojlk{ position: relative; }
.l-container div.woyaojlk a{display: block;position: absolute; color: #fff;top: 0; width: 80%; margin: 0 10%;text-align: center;font-size: 4rem;height: 5rem; margin-top: 4%;  }
</style>
<div class="l-container">
        <div class=""><img src="/images/share/yiyi1.jpg"></div>
        <div class=""><img src="/images/share/yiyi2.jpg"></div>
        <div class=""><img src="/images/share/yiyi3.jpg"></div>
        <div class=""><img src="/images/share/yiyi4.jpg"></div>
        
        <?php if($userinfo['user_id'] == $logininfo['user_id']):?>
            <?php if($loaninfo['status'] == 9):?>
                <div class="woyaojlk">
                        <img src="/images/share/yiyi5.jpg">
                        <a href="/dev/repay/repaychoose?loan_id=<?php echo $loaninfo['loan_id'];?>">去还款</a>
                </div>
            <?php else:?>
                <div class="woyaojlk">
                        <img src="/images/share/yiyi5.jpg">
                        <a href="/dev/loan">去借款</a>
                </div>
            <?php endif;?>
        <?php else:?>
            <div class="woyaojlk">
                    <img src="/images/share/yiyi5.jpg">
                    <a href="/dev/loan?atten=1">我也要借款</a>
            </div>
        <?php endif;?>
        <div class=""><img src="/images/share/yiyi6.jpg"></div>
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
            title: '先花一亿元',
            desc: '我借了这么多钱，没有一分钱利息，都来先花花，一起有钱花！',
            link: '<?php echo $shareurl; ?>',
            imgUrl: '<?php echo empty($userinfo['userwx']['head']) ? '/images/dev/face.png' : $userinfo['userwx']['head']; ?>',
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
            title: '先花一亿元',
            desc: '我借了这么多钱，没有一分钱利息，都来先花花，一起有钱花！',
            link: '<?php echo $shareurl; ?>',
            imgUrl: '<?php echo empty($userinfo['userwx']['head']) ? '/images/dev/face.png' : $userinfo['userwx']['head']; ?>',
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