<div class="Hcontainer">
    <div class="wrapBg">
        <p class="text-center mb40 mt40 red n48"></span>恭喜你！还款成功！</p>
        <img src="/images/heg.png" style="width:46%;margin-left: 15%">
    </div>
    <div class="con">
        <a href="/dev/loan" class="btn" style="width:100%;">确定并返回</a>
    </div>
<!--    <div class="mt40">
        <div class="col-xs-4 text-center"><a href="#" class="cor_4"><img src="/images/hk_gm.png" width="80%" class="mb20"><p>购买先花宝</p></a></div>
        <div class="col-xs-4 text-center"><a href="#" class="cor_4"><img src="/images/hk_tz.png" width="80%" class="mb20"><p>投资好友</p></a></div>
        <div class="col-xs-4 text-center"><a href="/dev/loan" class="cor_4"><img src="/images/hk_xy.png" width="80%" class="mb20"><p>信誉借款</p></a></div>
    </div>-->

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