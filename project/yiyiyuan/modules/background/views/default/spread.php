<div class="wytg">
        <h3>推广用户收益提升啦！！！</h3>
        <p>
            成功推荐一名用户使用你的邀请码注册并借款成功还款，从原有的奖励5元/人提升至30元/人。<br>
            现金奖励不封顶！快来邀请好友一起赚钱吧。
        </p>
        <div class="tutubg">
          <div><img src="/images/ma2.png"/></div>
          <div class="yqm">邀请码：<?php echo $invite_code;?></div>
        </div>
      </div>
      <button class="sendsr" onclick="show()">点此发给熟人</button>
	  <div id="overDiv" style="display:none;" onclick="closeDiv()"></div>
<div id="diolo_warp" class="guide_img" style="display:none;" onclick="closeDiv()">
    <img src="/images/guide.png">
</div> 
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script type="text/javascript">
    function show(){
        document.getElementById("overDiv").style.display = "block" ;
        document.getElementById("diolo_warp").style.display = "block" ;
    }
    function closeDiv(){
        document.getElementById("overDiv").style.display = "none" ;
         document.getElementById("diolo_warp").style.display = "none" ;
    }
</script>
<script>
    $('.nav_right').click(function(){
        window.location.href = '<?php echo $returnUrl ?>';
    })
</script>
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