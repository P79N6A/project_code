<div class="txjl_cont">
        <?php if (!empty($liuinfo)): ?> 
	    <?php foreach ($liuinfo as $key => $v): ?>
        <div class="txjl_boxb">
            <div class="disitem txjl_gzgz ">
                <div class="gzgz_tleft">
                    <p class="txdcard">充值流量到：<em><?php echo $v->mobile;?></em></p>
                    <div class="disitem txdtime"><?php echo $v->create_time;?></div>
                </div>
                <div class="gzgz_trightmm"><em><?php echo $v->flow_amount;?></em>M</div>
            </div> 
        </div>
        <?php endforeach; ?>
		<?php else:?>
 <div>
	<img src="/images/cry1.png">
	<p style="text-align:center; font-size:2.2rem;">无记录！</p>
 </div>
	   <?php endif; ?> 
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