<div class="Hcontainer nP">
    <div class="wrapBg">
        <p class="text-center mb40 mt40 red n48">恭喜你！绑定成功！
            <?php if($old==0):?><br />收益＋<?php echo $account;?>元<?php endif;?>
        </p>
        <img src="/images/heg.png" style="width:46%;margin-left: 15%">
    </div>
    <div class="con">
        <a href="/dev/bank"><button class="btn" style="width:100%;">确定并返回</button></a>
    </div>
<!--    <div class="main mt40 succ">        
        <div class="col-xs-4 text-center"><a href="#" class="cor_4"><img src="/images/hk_gm.png" width="80%" class="mb20"><p>购买先花宝</p></a></div>
        <div class="col-xs-4 text-center"><a href="#" class="cor_4"><img src="/images/hk_tz.png" width="80%" class="mb20"><p>投资好友</p></a></div>
        <div class="col-xs-4 text-center"><a href="/dev/loan" class="cor_4"><img src="/images/hk_xy.png" width="80%" class="mb20"><p>信誉借款</p></a></div>
    </div>                           -->
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