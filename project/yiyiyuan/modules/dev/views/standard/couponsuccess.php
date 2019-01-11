	<div class="fxj_box">
		<div class="fxj_bgbg">
			<img src="/images/lijqianw2.png">
			<div class="fxj_cont">
				<div class="fxj_txt">
					<p class="fxj_txtfirst"><span><i><?php echo $couponinfo['cycle'];?></i>天<br/> 双倍收益券</span></p>
					<a href="/dev/account/coupon"><button>立即前往</button></a>
				</div>
			</div>
		</div>
	</div>
	<?php if($type == 'my'):?>
	<div class="black_diolog">这是你已经领取的优惠卷~</div>
	<?php endif;?>
	
<?php if($type == 'my'):?>
<script>
    $(function() {
    	$(".black_diolog").fadeOut(3000);;
    });
</script>
<?php endif;?>	
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