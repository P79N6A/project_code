<div class="Hcontainer nP">
    <div class="main mt20">
        <div class="bWhite borRad5 text-center padtb">
            <p class="n42">您已购买<span class="red"><?php echo $old;?></span>元担保卡!</p>
            <p class="n30 grey4">获得<?php echo $old;?>点担保额度</p>
            <p class="n26 mt40"><span class="red">担保借款</span>，出款快，还免服务费哦~</p>
            <p class="n26 mt40"><span class="red">信用投资</span>，安全，短期，灵活，高收益</p>
        </div>
        <a href="/dev/guarantee"><button class="btn mt40 mb40" style="width:100%">查看我的担保卡</button></a>
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