<div class="succsee">
        <div class="success_titlt">
            <img src="/images/touzi_false.png">
            <span>退卡失败！</span>
        </div>
        <img class="succsee_true suhui" src="/images/touzi_false1.png">
        <div class="succsee_txtx">
            <p class="succsee_suhui">请重新尝试或联系先花一亿元微信客服</p>
        </div>
        <a href="/dev/guarantee"><button class="success_share">返回</button></a>
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