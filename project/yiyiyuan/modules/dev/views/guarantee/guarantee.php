<div class="Hcontainer nP">
<script  src='/dev/st/statisticssave?type=18'></script>
    <div class="main">
        <div class="col-xs-12 text-right n26 nPad">
            <img src="/images/icon_ques2.png" width="5.5%" style="margin-top: -4px;">
            <a href="/dev/guarantee/guacard">什么是担保卡?</a>
        </div>
        <img src="/images/dbCard.png" class="dbCard">
        <div class="col-xs-12 text-center mt40">
            <p class="n50">您尚没有担保卡</p>
            <p class="n30">担保额度为0，快去购买吧</p>
        </div>
        <a href="/dev/guarantee/buycard"><button class="btn mt40 mb40" style="width:100%">购买担保卡</button></a>
        <p class="n24">购买担保卡即获得<span class="red">担保额度</span>，<br /><span class="red">担保额度</span>可以担保借款，出款更快而且免服务费噢。</p>
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