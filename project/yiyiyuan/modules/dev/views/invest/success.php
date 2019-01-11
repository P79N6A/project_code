        <div class="container">
        	<img src="/images/dev/title.png" width="100%"/>
           <div class="con">
           		<div class="details">
               		<p class="mb20 text-center n34"><span class="icons succ"></span> 投资成功</p>
                    <!--<p class="text-right cor n28 mb30">提交时间：2015-02-02 18：37</p>-->
                    <div class="border_bottom_1"></div>
                    <div class="adver border_bottom_1 border_top_1">
                    	<div class="row mb30">
                        	<div class="col-xs-3 cor">投资金额</div>
                            <div class="col-xs-9 text-right red">&yen;<?php echo sprintf("%.2f", $investinfo['amount']);?></div>
                        </div>
                        <div class="row mb30">
                        	<div class="col-xs-3 cor">投资时间</div>
                            <div class="col-xs-9 text-right"><?php echo $invest_time;?></div>
                        </div>
                        <div class="row mb30">
                        	<div class="col-xs-3 cor">收益状态</div>
                            <div class="col-xs-9 text-right">未收益</div>
                        </div>
                    </div>
                    <div class="adver border_top_1 border_bottom_1">
                    	<div class="row mb30">
                        	<div class="col-xs-3 n30"><?php echo $investinfo['realname'];?></div>
                            <div class="col-xs-9 text-right">投资期限<span class="red"><?php echo $investinfo['days'];?></span> 天</div>
                        </div>
                        <p class="cor mb30 n30"><?php echo $investinfo['desc'];?></p>
                    </div>
                    <div class="adver border_top_1">
                        <div class="row">
                        	<div class="col-xs-3 cor">期满收益</div>
                            <div class="col-xs-9 text-right red n50">&yen;<?php echo $profit;?></div>
                        </div>
                    </div>
                </div>
                <img src="/images/dev/bottom.png" width="100%" style="vertical-align:top"/>
                <button class="btn mt20 mb30" onclick="window.location.href='/dev/account'" style="width:100%">去我账户看看</button>
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