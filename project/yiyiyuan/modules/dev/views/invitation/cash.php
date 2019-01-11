<div class="fInvitation">
	<div class="fInvfirst helpmone">
		<p class="help1">您的好友<?php if(!empty($userwx)):?><?php echo $userwx->nickname;?><?php else:?><?php echo !empty($user->realname) ? $user->realname : '';?><?php endif;?>邀请您做三道测试题 </p>
		<p class="help2"><em>全部答对</em>即可获得优惠券红包 </p>
		<img src="/images/account/66hb.png">
	</div>
	 <a href="/dev/invitation/first?userid=<?php echo $wid; ?>" wid="<?php echo $wid; ?>"><div class="button"> <button>开始测试</button></div></a>
	
</div>
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