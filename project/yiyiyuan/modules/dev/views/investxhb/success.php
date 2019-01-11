        <div class="container">
            <p class="b72 red text-center mt170">投资成功!</p>
            <p class="n22 text-center mt20 mb100">您距离一亿元又进了一步，邀请好友一起赚钱吧~</p>
            <div class="main">
            	<a href="/dev/share/share?open_id=<?php echo $openid;?>"><button class="btn1 mb40" style="width:100%;">分享</button></a>
                <a href="/dev/investxhb/hxhb"><button class="btn" style="width:100%;">继续投资</button></a>
            </div>
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