
<script  src='/dev/st/statisticssave?type=43'></script>
<div class="clickwebg">
	<img src="/images/valentine/password.png">
</div>
<div class="renjiah">
    <a href="/dev/valentine/code?vid=<?php echo $vid;?>"><button class="renjiaone"><img src="/images/valentine/renjai1.png"></button></a>
    <a href="/dev/valentine/index"><button class="renjiatwo"><img src="/images/valentine/renjai2.png"></button></a>
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