        <div class="container">
        <script  src='/dev/st/statisticssave?type=11'></script>
        <img src="/images/dev/title.png" width="100%"/>
          <div class="con">
           		<div class="details">
                    <p class="mb30">年化收益：5%</p> 
                    <p class="mb30">已投资金额：<?php echo sprintf("%.2f", $stat_info['total_amount']);?>点</p> 
                    <p class="n30 red">已收益：<?php echo sprintf("%.2f", $stat_info['total_income']);?>点</p>
                </div>
                <img src="/images/dev/bottom.png" width="100%" style="vertical-align:top"/>
                 <a href="/dev/investxhb/confirm" ><button class="btn mt20 mb30" style="width:100%">继续投资</button></a>
                <a href="/dev/investxhb/reback"><button class="btn1 mb30" style="width:100%">我要赎回</button></a>
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