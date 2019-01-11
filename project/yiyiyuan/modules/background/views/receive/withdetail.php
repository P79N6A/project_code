<div class="todaysy">
    <p class="todaysy_img"></p>
    <p class="todaytxt_one">成功提取</p>
    <p class="todaytxt_two"><?php echo number_format($userinfo->amount, 2, ".", "");?>RMB</p>
    <p class="todaytxt_three"></p>
</div>

<div class="Square">
<img src="/images/title.png" width="100%">
   	<div class="Scont">
   		<div class="Ssyxq txianwz">
   			<p class="disitem syxqcont xqnr">
   				<em class="scon_left">提现方式</em>
   				<em class="scon_right">连连支付</em>
   			</p>
        <p class="disitem syxqcont">
          <em class="scon_left">提现时间</em>
          <em class="scon_right"><?php echo $userinfo->create_time;?></em>
        </p>
        <p class="disitem syxqcont">
          <em class="scon_left">到账时间</em>
          <em class="scon_right"><?php echo $userinfo->create_time;?></em>
        </p>
        <p class="disitem syxqcont">
          <em class="scon_left">流水号</em>
          <em class="scon_right"><?php echo $userinfo->settlement_id;?></em>
        </p>
        
   		</div>
        <img src="/images/bottom.png" width="100%" style="vertical-align:top"> 
   </div>
</div>
<script>
    $('.nav_right').click(function(){
        window.location.href = '<?php echo $returnUrl ?>';
    })
</script>
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