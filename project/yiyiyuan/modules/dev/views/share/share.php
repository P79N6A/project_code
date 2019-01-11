<!--<link rel="stylesheet" type="text/css" href="/css/dev/common.css?v=20150611">-->
<script type="text/javascript">
    $(function() {
        if ($(window).height() > $("#img").height()) {
            $("#img").height($(window).height());
        } else if ($(window).height() < $("#img").height()) {
            $("#img").height(1008);
        }
    });
    var gun = 0;
    var timer;
    $(function() {
        $("#div2").html($("#div1").html());
        timer = setInterval(gundong, 50);
    });
    function gundong() {
        $(".Hbottom").scrollTop(gun++);
        if (gun > $(".Hbottom #div1")[0].offsetHeight) {
            $(".Hbottom").scrollTop(0);
            gun = 0;
        }
    }
</script>
<script src="/js/dev/shareWin.js?v=2015061511"></script> 
<div class="Hcontainer nP">
<script  src='/dev/st/statisticssave?type=12'></script>
    <div class="shareH">
        <img src="/images/lq_bg2.jpg" width="100%" id="img"/>
        <p class="Hname n48"><span><?php echo $userinfo['realname'] ?></span>喊你来白领钱<br/> (建议只喊挚友)</p>
        <div class="text-center" style="width:100%;position:absolute;top:68%;left:0;z-index: 1">
            <img src="/images/edyy.png" alt="" style="width:70%; max-width: 350px;">
        </div>
        <div class="text-center" style="width:100%;position:absolute;top:74%;left:0;z-index: 1">
            <?php if (!empty($type)): ?>
                <a href="/dev/account"><input type="image" src="/images/lq_btn.png" style="width:87.5%; max-width: 450px;"/></a>
            <?php else: ?>
                <input type="image" src="/images/lq_btn.png" onClick="shareTip();" id="share_weixinfriend" style="width:87.5%; max-width: 450px;" />
            <?php endif; ?>
        </div>
        <div class="Hbottom text-center">
            <div id="div">
                <div id="div1">
                    <p class="white n22"><span>王**</span> 已提现1000.00元</p>
                    <p class="white n22"><span>刘**</span> 已提现2000.00元</p>
                    <p class="white n22"><span>蒙**</span> 已提现3000.00元</p>
                    <p class="white n22"><span>王**</span> 已提现4000.00元</p>
                    <p class="white n22"><span>张**</span> 已提现500.00元</p>
                    <p class="white n22"><span>李**</span> 已提现600.00元</p>
                </div>
                <div id="div2"></div>
            </div>
        </div>        
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
                        title: '先花一亿元',
                        desc: '送给你一亿元理财金！白拿收益，最高可得一亿元',
                        link: '<?php echo $shareUrl;?>',
                        imgUrl: '<?php echo Yii::$app->params['app_url']; ?>/images/dev/red.png',
                        trigger: function(res) {
                            // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
                        },
                        success: function(res) {
                            window.location = "/dev/share/share?open_id=<?php echo $user_id; ?>";
                        },
                        cancel: function(res) {
                        },
                        fail: function(res) {
                        }
                    });

                    // 2.2 监听“分享到朋友圈”按钮点击、自定义分享内容及分享结果接口
                    wx.onMenuShareTimeline({
                        title: '送给你一亿元理财金！白拿收益，最高可得一亿元',
                        link: '<?php echo $shareUrl;?>',
                        imgUrl: '<?php echo Yii::$app->params['app_url']; ?>/images/dev/red.png',
                        trigger: function(res) {
                            // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
                        },
                        success: function(res) {
                            window.location = "/dev/share/share?open_id=<?php echo $user_id; ?>";
                        },
                        cancel: function(res) {
                        },
                        fail: function(res) {
                            alert(JSON.stringify(res));
                        }
                    });
                    // config信息验证后会执行ready方法，所有接口调用都必须在config接口获得结果之后，config是一个客户端的异步操作，所以如果需要在页面加载时就调用相关接口，则须把相关接口放在ready函数中调用来确保正确执行。对于用户触发时才调用的接口，则可以直接调用，不需要放在ready函数中。
                });
</script>