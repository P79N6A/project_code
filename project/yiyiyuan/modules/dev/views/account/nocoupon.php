	<div class="Hcontainer">
        <div class="main">
        	<img src="/images/nopiao.png" class="noP">
            <p class="noP_t">暂无优惠券</p>
            <div class="social">
                <p>优惠券不定期出没的地点：</p>
                <p><img src="/images/icon_wb.png"><span>关注先花花官方微博</span></p>
                <p><img src="/images/icon_wx.png"><span>更多参与官方互动</span></p>               
            </div>
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