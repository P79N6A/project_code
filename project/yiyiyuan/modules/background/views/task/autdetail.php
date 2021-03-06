<div class="renzhrw">
    <h3>认证任务</h3>
    <p class="ontxt">点击“马上去邀请”并分享给好友，<span>好友得红包，你得优惠券，</span>可减免借款服务费哦。</p>
    <p class="fendcont">好友数量 <?php echo $count ?>/<?php echo $goal ?></p>
    <button class="msyyqing" onclick="show()">马上去邀请</button>
    <button class="msyyqing <?php echo $count<$goal?"msyyhui":'stage' ?>">领取任务奖励</button>
</div>

<div id="overDiv" style="display:none;"  onclick="closeDiv()"></div>
<div id="diolo_warp" class="guide_img" style="display:none;"  onclick="closeDiv()">
    <img src="/images/guide.png">
</div> 

<div class="lqewsy" style="display:none;">
  <p class="ewasy">领取<span><?php echo $coupon ?>元优惠券</span></p>
  <a href="/background/task/getcoupon?step=<?php echo $step ?>"><p class="trueqd">确定</p></a>
</div>

<script type="text/javascript">
    function show(){
        document.getElementById("overDiv").style.display = "block" ;
        document.getElementById("diolo_warp").style.display = "block" ;
    }
    function closeDiv(){
        document.getElementById("overDiv").style.display = "none" ;
         document.getElementById("diolo_warp").style.display = "none" ;
         $('.lqewsy').hide();
    }

    $('.stage').click(function(){
        $('#overDiv').show();
        $('.lqewsy').show();
    })
</script>
<script>
    $('.nav_right').click(function(){
        window.location.href = '<?php echo $returnUrl ?>';
    })
</script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
wx.config({
        debug: false,
        appId: "<?php echo $jsinfo['appid']; ?>",
        timestamp: "<?php echo $jsinfo['timestamp']; ?>",
        nonceStr: "<?php echo $jsinfo['nonceStr']; ?>",
        signature: "<?php echo $jsinfo['signature']; ?>",
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
            title: '有人@你拆红包！',
            desc: '【一条未读信息】快来认证我，答题成功，可获得2—20元现金红包',
            link: '<?php echo $shareUrl; ?>',
            imgUrl: "<?php echo empty($loanuserinfo['head']) ? '/images/dev/face.png' : $loanuserinfo['head']; ?>",
            trigger: function (res) {
                // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
            },
            success: function (res) {
                $('#diolo_warp').hide();
                $('#overDiv').hide();
            },
            cancel: function (res) {
            },
            fail: function (res) {
            }
        });

        // 2.2 监听“分享到朋友圈”按钮点击、自定义分享内容及分享结果接口
        wx.onMenuShareTimeline({
            title: '吸金妖怪来啦！别跑！',
            desc: '赚钱新技能来袭，一步跨入豪门的机会，快来参加。',
            link: '<?php echo $shareUrl; ?>',
            imgUrl: "<?php echo empty($loanuserinfo['head']) ? '/images/dev/face.png' : $loanuserinfo['head']; ?>",
            trigger: function (res) {
                // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
            },
            success: function (res) {
                $('#diolo_warp').hide();
                $('#overDiv').hide();
            },
            cancel: function (res) {
            },
            fail: function (res) {
                alert(JSON.stringify(res));
            }
        });
    });
</script>