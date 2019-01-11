<div class="clickwebg">
	<img src="/images/valentine/clickwebg.png">
</div>
<div class="clickbutton">
	<a href="/dev/valentine/letter?wid=<?php echo $wid;?>"><button><img src="/images/valentine/clickwe2.png"></button></a>
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