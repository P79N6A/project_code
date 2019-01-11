<div class="zmoneyxq">
	<h3>推荐好友理财有现金奖励了！！</h3>
	<p class="contoneo">邀请好友购买园丁计划，好友得收益的同时，你还有现金分润哦！好友投资成功后，根据好友关系来获得对应奖励。好友的分润比例还是5:3:1。例如用户投资10000元，30天，一级二级三级好友获得的分润分别得9.13元，5.48元，1.83元。</p>
	<p class="contoneo">多买多得，赶紧让好友来投资吧！</p>
	<button class="buttononeo"  onclick="show()">分享给好友来投资</button>

	<a href="/dev/invest/index"><button>去一亿元买园丁计划</button></a>
</div>
<div id="overDiv" style="display:none;"  onclick="closeDiv()"></div>
<div id="diolo_warp" class="guide_img" style="display:none;"  onclick="closeDiv()">
    <img src="/images/guide.png">
</div> 
<script>
    $('.nav_right').click(function(){
        history.go(-1);
    })
</script>
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
            title: '您有一张【双倍收益券】未领取！',
            desc: '赶紧来投资园丁计划吧，高达21%的投资收益率等你哦！',
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
            title: '您有一张【双倍收益券】未领取！',
            desc: '赶紧来投资园丁计划吧，高达21%的投资收益率等你哦！',
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