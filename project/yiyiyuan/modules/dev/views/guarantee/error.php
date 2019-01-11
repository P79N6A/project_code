<div class="Hcontainer nP">
    <div class="main mt20">
        <div class="bWhite borRad5 padtb">
            <div class="main">
                <p class="n42 text-center">购买失败！</p>
                <p class="n26 red mt20">失败原因</p>
                <p class="n22 mt20">1. 余额不足</p>
                <p class="n22 mt20" style="line-height:3rem;">2.超出您银行卡的单笔消费额度，您可以尝试绑定其他银行卡或购买小额度的担保卡</p>
            </div>
        </div>
        <a href="/dev/guarantee/buycard"><button class="btn mt40 mb40" style="width:100%">返回购买页面</button></a>
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