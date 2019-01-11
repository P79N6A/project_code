<div class="Hcontainer nP">
	<header class="header white">
		<p class="n26">状态：</p>
		<p class="n36 mb20 text-center">已收益</p>
		<p class="n26 text-right">投资靠谱的人 躺着挣钱的体验~</p>
	</header>
	<img src="/images/title.png" width="100%"/>
	<div class="con">
		<div class="details">
			<div class="adver">
				<div class="row mb30">
					<div class="col-xs-4 cor n26">借款人：</div>
					<div class="col-xs-8 text-right n26"><?php echo $userinfo['realname'] ;?></div>
				</div>
				<div class="row mb30">
					<div class="col-xs-4 cor n26">收益日期：</div>
					<div class="col-xs-8 text-right n26"><?php echo substr($loan_info['repay_time'],0,10);?></div>
				</div>
				<div class="row mb30">
					<div class="col-xs-4 cor n26">收益金额：</div>
					<div class="col-xs-8 text-right n26"><span class="red">&yen;<?php echo number_format($loan_info['amount']*0.01,2,'.','');?></span></div>
				</div>
			</div>
		</div>
		<img src="/images/bottom.png" width="100%" style="vertical-align:top"/>

		<a href="/dev/sponsor/index" class="btn mt20 mb40" style="width:100%">继续投资</a>
   </div>
</div>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    wx.config({
        debug: false,
        appId: '<?php echo $jsinfo['appid']; ?>',
        timestamp: <?php echo $jsinfo['timestamp']; ?>,
        nonceStr: '<?php echo $jsinfo['nonceStr']; ?>',
        signature: '<?php echo $jsinfo['signature']; ?>',
        jsApiList: [
            'hideOptionMenu'
        ]
    });

    wx.ready(function() {
        wx.hideOptionMenu();
    });
</script>