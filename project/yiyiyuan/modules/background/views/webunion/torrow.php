<div class="todaysy">
            <p class="todaysy_img"></p>
            <p class="todaytxt_one">收益金额</p>
            <p class="todaytxt_two"><?php echo $shouyitotal;?>RMB</p>
            <p class="todaytxt_three"><?php echo date('Y-m-d', strtotime('-1 day', time()));?></p>
        </div>
      <div class="Square">
			<img src="/images/title.png" width="100%">
           	<div class="Scont">
           		<div class="Ssyxq">
                <div class="disitem zrsy">
                  <img src="/images/zrsy.png">
                  <p>收益详情：</p>
                </div>
				 <?php if (!empty($userinfo)): ?> 
					<?php foreach ($userinfo as $key => $v): ?>
           			<p class="disitem syxqcont">
           				<span class="scon_left">邀请好友<?php echo $v['user']['realname']; ?></span>
           				<span class="scon_right">获得佣金<em><?php echo number_format($v['profit_amount'], 2, ".", ""); ?></em>RMB</span>
           			</p>
					<?php endforeach; ?>
					<?php endif; ?> 

					<?php if (!empty($loaninfo)): ?> 
					<?php foreach ($loaninfo as $key => $v): ?>
           			<p class="disitem syxqcont">
           				<span class="scon_left"><?php echo $v['loan']['user_id']; ?>借款<?php echo number_format($v['loan']['amount'], 2, ".", ""); ?>元</span>
           				<span class="scon_right">获得佣金<em><?php echo number_format($v['profit_amount'], 2, ".", ""); ?></em>RMB</span>
           			</p>
					<?php endforeach; ?>
					<?php endif; ?> 

                    <?php if (!empty($investinfo)): ?> 
					<?php foreach ($investinfo as $key => $v): ?>
           			<p class="disitem syxqcont">
           				<span class="scon_left"><?php echo $v['invest']['user_id']; ?>投资<?php echo number_format($v['invest']['amount'], 2, ".", ""); ?>元</span>
           				<span class="scon_right">获得佣金<em><?php echo number_format($v['profit_amount'], 2, ".", ""); ?></em>RMB</span>
           			</p>
					<?php endforeach; ?>
					<?php endif; ?> 
                <div class="disitem zjsy_all">
                  <div></div>
                  <p>总计：<em><?php echo $shouyitotal;?></em>RMB</p>  
                </div>
           		</div>
                <img src="/images/bottom.png" width="100%" style="vertical-align:top"> 
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
            'closeWindow',
            'hideOptionMenu'
        ]
    });

    wx.ready(function() {
        wx.hideOptionMenu();
    });
</script>