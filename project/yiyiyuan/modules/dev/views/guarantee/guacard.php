<div class="Hcontainer nP">
    <div class="main grey2">
        <div class="mt20">
            <p class="n30 bold">● 什么是担保卡？</p>
            <p class="n26">担保卡是您在先花一亿元购买的<span class="red">信用抵押物</span>，担保卡相对应的是担保额度，购买一定额度的担保卡即获得等额的<span class="red">担保额度</span>。使用担保额度借款，出款快而且免服务费。</p>
        </div>
        <div class="mt40">
            <p class="n30 bold">● 如何获得担保卡?</p>
            <p class="n26"><span class="red">信用卡</span>和<span class="red">储蓄卡</span>都可购买</p>
        </div>
        <div class="mt40">
            <p class="n30 bold">● 担保卡使用规则有哪些？</p>
            <p class="n26">用户发起借款时，可选择担保额度借款，<span class="red">即担保借款，出款更快且免服务费。</span>担保卡借款时间可选择1天，即隔夜还功能，若逾期未还款，担保额度抵消，由先花一亿元收回。</p>
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