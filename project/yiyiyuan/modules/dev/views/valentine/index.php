

    <script type="text/javascript">
        $(function(){
            //设置定时三秒后跳转至登录页面
            setTimeout('window.location.href="/dev/valentine/clickme"',2000);
        });
    </script>
    <script  src='/dev/st/statisticssave?type=42'></script>
<div class="clickwebg">
	<img src="/images/valentine/index.png">
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