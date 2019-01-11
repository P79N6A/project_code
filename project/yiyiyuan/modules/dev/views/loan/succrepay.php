<div class="container">
        <div class="top">
        	<img src="/images/dev/bg.png" width="100%"/>
            <div class="main">
                <div class="row mb40">
                    <div class="col-xs-2 photo"><img src="<?php echo empty( $userinfo['head'] ) ? '/images/dev/face.png' : $userinfo['head'];?>" width="100%"/></div>
                    <div class="col-xs-4 pd"><a><?php echo $userinfo['nickname'];?></a></div>
                    <div class="col-xs-6 text-right"><?php echo $loaninfo['create_time'];?></div>
                </div>
                <p class="mb40"><?php echo $loaninfo['desc'];?></p>
             </div>
             <div class="main infos">
             	<div class="row mt40">
                    <div class="col-xs-6">借<span class="red"><?php echo sprintf("%.2f", $loaninfo['amount']);?></span>点</div>
                    <div class="col-xs-6 text-right n22 cor_a">借款期限<span class="cor_4"> <?php echo $loaninfo['days'];?> </span>天</div>
                </div>
             </div>
        </div>
          <div class="main bgf border_bottom">
           		<div class="row">
                    <div class="col-xs-3 btn">筹款中</div>
                    <div class="col-xs-5 pd">已筹到<span class="red"><?php echo sprintf("%.2f", $loaninfo['current_amount']);?></span>点</div>
                    <div class="col-xs-4 text-right n22 cor_a">剩余<span class="cor_4" id="count_hour"><?php echo $remaintime;?></span>小时</div>
                </div>
           </div>
           <div class="main">
           		<p class="n24 mb20 cor_a">快找小伙伴来帮忙吧</p>
                <a href="<?php echo $shareurl;?>" class="btn mb20" style="width:100%">分享到朋友圈</a>
                <img src="/images/dev/a.png" width="100%" class="mb40"/>
                <p class="mb40 n22">
                	1、6小时内筹款完成自动打款到您的银行卡中。<br/>
                    2、6小时后筹款将自动失效，可在12小时内手动提现已筹金额，否则已筹到金额将失效。
                </p>
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