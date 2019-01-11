<div class="wrap">
		<div class="money_index">
			<section class="name">账户金额</section>
			<div class="account">
				<section class="number"><?php echo number_format($total_history_interest -$total_on_interest,2, ".", "");?><em>RMB</em></section>
				<section class="button"><a href='/background/webunion/withdraw' style="color:#e74747;">提现</a></section>
			</div>
			<div class="line"></div>
			<section class="name">流量</section>
			<div class="account">
				<section class="number"><?php echo number_format($total_history_flow -$total_on_flow,2, ".", "");?><em>M</em></section>
				<section  class="button"><a href='/background/webunion/liuliang' style="color:#e74747;">领取</a></section>
			</div>
		</div>
		<?php if(($create_time >=date('Y-m-d 00:00:00') && ($create_time < date('Y-m-d 23:59:59')))):?>
		<div style="width:96%;text-align:center; background:#e74747; color:#fff;margin:10px 2%; padding:5px 0;">
			*你已有流量或现金收益,花二哥努力计算中,将于明天显示*
		</div>
		<?php endif;?>
		<section>
			<div class="left">
			    <a href='/background/webunion/torrow' style="color: #aaa;display: flex;flex-direction: column;align-items: center;flex: 1;">
				<div><em><?php echo $shouyitotal;?></em><span>RMB</span></div>
				<div>昨日收益</div>
				<div class="index_img">
					<img src="/images/index_img.png">
				</div>
				</a>
			</div>
			<div class="line"></div>
			<div class="right">
				 <a href='/background/webunion/quxie' style="color: #aaa;display: flex;display: -webkit-box;display:-webkit-flex;display:flex;
				 flex-direction: column;-webkit-flex-direction:column;-webkit-box-orient: vertical;
				 align-items: center;-webkit-box-align:center;-webkit-align-items: center;flex: 1;-webkit-flex:1; -webkit-box-flex:1;">
				<div><em><?php echo number_format($total_history_interest,2, ".", ""); ?></em><span>RMB</span></div>
				<div>累计收益</div>
				<div class="index_img" >
					<img src="/images/index_img.png">
				</div>
				</a>
			</div>
			<div class="line"></div>
			<div class="right">
				<div><em><?php echo number_format($score,2, ".", ""); ?></em><span>分</span></div>
				<div>我的积分</div>
				<div class="index_img" >
					<img src="/images/index_img.png">
				</div>
			</div>
		</section>
		<div class="disitem moneyindex">
			<img src="/images/money_sy.png">
			<p>收益明细</p>
		</div>
		    <?php if($statData): foreach($statData as $data) : ?>
        	<div class="symx_boxb">
	            <div class="disitem symx_gzgz symxgray">
	                <div class="symx_left "><?php echo date('Y年m月',strtotime($data['ym'].'01'));?>收益</div>
	                <div class="symx_two"></div>
	                <div class="symx_three"><?php echo  number_format($data['total'], 2, ".", "");?>RMB</div>
	                <div class="symx_right three"></div>
	            </div>
        	</div>
			<?php endforeach;endif;?>
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