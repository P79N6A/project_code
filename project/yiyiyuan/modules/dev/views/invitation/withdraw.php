<div class="fInvitation">
	<div class="fInvfirst helpmone">
		<p class="help1">您的好友“阿飞”邀请您做三道测试题 </p>
		<p class="help2"><em>全部答对</em>即可帮Ta获得<em>100</em>额度</p>
		<p class="help3">同时你将得到<em>2-20</em>元现金红包</p>
		<img src="/images/account/firstimg2.png">
	</div>
	<div class="button"> <button>开始测试</button></div>
	<div class="certification">
		<div class="cert_one"><img src="/images/account/fircert.png">雷锋榜</div>
		<div class="cert_two">
			<img src="/images/account/firtoux.png">
			<div class="cert_two2"><p class="p1">马云</p><p class="p2">02月09日  21:39</p></div>
			<div class="cert_two3">34点</div>
		</div>
		<div class="cert_two">
			<img src="/images/account/firtoux.png">
			<div class="cert_two2"><p class="p1">马云</p><p class="p2">02月09日  21:39</p></div>
			<div class="cert_two3">34点</div>
		</div>
	</div>
	<div id="overDiv"></div>
	<div class="mLayer">
        <div class="info">您已经认证过该好友了哦！</div>
        <div class="button fCf"><a href="/dev/loan/index" class="aButton fFr">朕知道了</a></div>
    </div>
</div>
<script>
$("#tuijiao_button").bind("click",function(){
    window.location = '/dev/';
}
</script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
  wx.config({
	debug: false,
	appId: '<?php echo $jsinfo['appid'];?>',
	timestamp: <?php echo $jsinfo['timestamp'];?>,
	nonceStr: '<?php echo $jsinfo['nonceStr'];?>',
	signature: '<?php echo $jsinfo['signature'];?>',
	jsApiList: [
		'hideOptionMenu'
	  ]
  });
  
  wx.ready(function(){
	  wx.hideOptionMenu();
	});
</script>
