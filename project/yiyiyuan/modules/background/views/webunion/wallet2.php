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
		     <?php if (!empty($userinfo12)||!empty($loaninfo12)||!empty($investinfo12)): ?> 
        	<div class="symx_boxb">
	            <div class="disitem symx_gzgz symxgray">
	                <div class="symx_left ">● 12月收益</div>
	                <div class="symx_two"></div>
	                <div class="symx_three"><?php echo $total12;?>RMB</div>
	                <div class="symx_right three"></div>
	            </div>
				<?php endif; ?> 
	            <div class="symx_jycont">
				    <?php if (!empty($userinfo12)): ?> 
					<?php foreach ($userinfo12 as $key => $v): ?>
	            	<div class="disitem jycon_cont">
	            		<div class="contone">邀请好友<?php echo $v['user']['realname']; ?> 
	            			<p><?php echo $v['create_time']; ?></p>
	            		</div>
	            		<div class="contwo"></div>
	            		<div class="conthree">获佣金<em><?php echo number_format($v['profit_amount'], 2, ".", ""); ?></em>RMB</div>
	            	</div>
					<?php endforeach; ?>
					<?php endif; ?> 
                    
					<?php if (!empty($loaninfo12)): ?> 
					<?php foreach ($loaninfo12 as $key => $v): ?>
	            	<div class="disitem jycon_cont">
	            		<div class="contone"><?php echo $v['loan']['user_id']; ?>借款<?php echo number_format($v['loan']['amount'], 2, ".", ""); ?>元 
	            			<p><?php echo $v['create_time'];?></p>
	            		</div>
	            		<div class="contwo"></div>
	            		<div class="conthree">获佣金<em><?php echo number_format($v['profit_amount'], 2, ".", ""); ?></em>RMB</div>
	            	</div>
					<?php endforeach; ?>
					<?php endif; ?> 

					<?php if (!empty($investinfo12)): ?> 
					<?php foreach ($investinfo12 as $key => $v): ?>
	            	<div style="border-bottom:0;" class="disitem jycon_cont">
	            		<div class="contone"><?php echo $v['invest']['user_id']; ?>投资<?php echo number_format($v['invest']['amount'], 2, ".", ""); ?>元 
	            			<p><?php echo $v['create_time'];?></p>
	            		</div>
	            		<div class="contwo"></div>
	            		<div class="conthree">获佣金<em><?php echo number_format($v['profit_amount'], 2, ".", ""); ?></em>RMB</div>
	            	</div>
					<?php endforeach; ?>
					    </div>
        	</div>
					<?php endif; ?> 

            <?php if (!empty($userinfo)||!empty($loaninfo)||!empty($investinfo)): ?> 
        	<div class="symx_boxb">
	            <div class="disitem symx_gzgz symxgray">
	                <div class="symx_left ">● 11月收益</div>
	                <div class="symx_two"></div>
	                <div class="symx_three"><?php echo $total11;?>RMB</div>
	                <div class="symx_right three"></div>
	            </div>
				<?php endif; ?> 
	            <div class="symx_jycont">
				    <?php if (!empty($userinfo)): ?> 
					<?php foreach ($userinfo as $key => $v): ?>
	            	<div class="disitem jycon_cont">
	            		<div class="contone">邀请好友<?php echo $v['user']['realname']; ?>
	            			<p><?php echo $v['create_time']; ?></p>
	            		</div>
	            		<div class="contwo"></div>
	            		<div class="conthree">获佣金<em><?php echo number_format($v['profit_amount'], 2, ".", ""); ?></em>RMB</div>
	            	</div>
					<?php endforeach; ?>
					<?php endif; ?> 
                    
					<?php if (!empty($loaninfo)): ?> 
					<?php foreach ($loaninfo as $key => $v): ?>
	            	<div class="disitem jycon_cont">
	            		<div class="contone"><?php echo $v['loan']['user_id']; ?>借款<?php echo number_format($v['loan']['amount'], 2, ".", ""); ?>元 
	            			<p><?php echo $v['create_time'];?></p>
	            		</div>
	            		<div class="contwo"></div>
	            		<div class="conthree">获佣金<em><?php echo number_format($v['profit_amount'], 2, ".", ""); ?></em>RMB</div>
	            	</div>
					<?php endforeach; ?>
					<?php endif; ?> 

					<?php if (!empty($investinfo)): ?> 
					<?php foreach ($investinfo as $key => $v): ?>
	            	<div style="border-bottom:0;" class="disitem jycon_cont">
	            		<div class="contone"><?php echo $v['invest']['user_id']; ?>投资<?php echo number_format($v['invest']['amount'], 2, ".", ""); ?>元 
	            			<p><?php echo $v['create_time'];?></p>
	            		</div>
	            		<div class="contwo"></div>
	            		<div class="conthree">获佣金<em><?php echo number_format($v['profit_amount'], 2, ".", ""); ?></em>RMB</div>
	            	</div>
					<?php endforeach; ?>
					    </div>
        	</div>
					<?php endif; ?> 
	          
			 <?php if (!empty($userinfo10)||!empty($loaninfo10)||!empty($investinfo10)): ?> 
        	<div class="symx_boxb">
	            <div class="disitem symx_gzgz symxgray">
	                <div class="symx_left ">● 10月收益</div>
	                <div class="symx_two"></div>
	                <div class="symx_three"><?php echo $total10;?>RMB</div>
	                <div class="symx_right three"></div>
	            </div>
				<?php endif; ?> 
	            <div class="symx_jycont">
				    <?php if (!empty($userinfo10)): ?> 
					<?php foreach ($userinfo10 as $key => $v): ?>
	            	<div class="disitem jycon_cont">
	            		<div class="contone">邀请好友<?php echo $v['user']['realname']; ?>
	            			<p><?php echo $v['create_time']; ?></p>
	            		</div>
	            		<div class="contwo"></div>
	            		<div class="conthree">获佣金<em><?php echo number_format($v['profit_amount'], 2, ".", ""); ?></em>RMB</div>
	            	</div>
					<?php endforeach; ?>
					<?php endif; ?> 
                    
					<?php if (!empty($loaninfo10)): ?> 
					<?php foreach ($loaninfo10 as $key => $v): ?>
	            	<div class="disitem jycon_cont">
	            		<div class="contone"><?php echo $v['loan']['user_id']; ?>借款<?php echo number_format($v['loan']['amount'], 2, ".", ""); ?>元 
	            			<p><?php echo $v['create_time'];?></p>
	            		</div>
	            		<div class="contwo"></div>
	            		<div class="conthree">获佣金<em><?php echo number_format($v['profit_amount'], 2, ".", ""); ?></em>RMB</div>
	            	</div>
					<?php endforeach; ?>
					<?php endif; ?> 

					<?php if (!empty($investinfo10)): ?> 
					<?php foreach ($investinfo10 as $key => $v): ?>
	            	<div style="border-bottom:0;" class="disitem jycon_cont">
	            		<div class="contone"><?php echo $v['invest']['user_id']; ?>投资<?php echo number_format($v['invest']['amount'], 2, ".", ""); ?>元 
	            			<p><?php echo $v['create_time'];?></p>
	            		</div>
	            		<div class="contwo"></div>
	            		<div class="conthree">获佣金<em><?php echo number_format($v['profit_amount'], 2, ".", ""); ?></em>RMB</div>
	            	</div>
					<?php endforeach; ?>
							   </div>
        	</div>
					<?php endif; ?> 

					 <?php if (!empty($userinfo1)||!empty($loaninfo1)||!empty($investinfo1)): ?> 
        	<div class="symx_boxb">
	            <div class="disitem symx_gzgz symxgray">
	                <div class="symx_left ">● 1月收益</div>
	                <div class="symx_two"></div>
	                <div class="symx_three"><?php echo $total1;?>RMB</div>
	                <div class="symx_right three"></div>
	            </div>
				<?php endif; ?> 
	            <div class="symx_jycont">
				    <?php if (!empty($userinfo1)): ?> 
					<?php foreach ($userinfo1 as $key => $v): ?>
	            	<div class="disitem jycon_cont">
	            		<div class="contone">邀请好友<?php echo $v['user']['realname']; ?>
	            			<p><?php echo $v['create_time']; ?></p>
	            		</div>
	            		<div class="contwo"></div>
	            		<div class="conthree">获佣金<em><?php echo number_format($v['profit_amount'], 2, ".", ""); ?></em>RMB</div>
	            	</div>
					<?php endforeach; ?>
					<?php endif; ?> 
                    
					<?php if (!empty($loaninfo1)): ?> 
					<?php foreach ($loaninfo1 as $key => $v): ?>
	            	<div class="disitem jycon_cont">
	            		<div class="contone"><?php echo $v['loan']['user_id']; ?>借款<?php echo number_format($v['loan']['amount'], 2, ".", ""); ?>元 
	            			<p><?php echo $v['create_time'];?></p>
	            		</div>
	            		<div class="contwo"></div>
	            		<div class="conthree">获佣金<em><?php echo number_format($v['profit_amount'], 2, ".", ""); ?></em>RMB</div>
	            	</div>
					<?php endforeach; ?>
					<?php endif; ?> 

					<?php if (!empty($investinfo1)): ?> 
					<?php foreach ($investinfo1 as $key => $v): ?>
	            	<div style="border-bottom:0;" class="disitem jycon_cont">
	            		<div class="contone"><?php echo $v['invest']['user_id']; ?>投资<?php echo number_format($v['invest']['amount'], 2, ".", ""); ?>元 
	            			<p><?php echo $v['create_time'];?></p>
	            		</div>
	            		<div class="contwo"></div>
	            		<div class="conthree">获佣金<em><?php echo number_format($v['profit_amount'], 2, ".", ""); ?></em>RMB</div>
	            	</div>
					<?php endforeach; ?>
							   </div>
        	</div>
					<?php endif; ?> 



					 <?php if (!empty($userinfo2)||!empty($loaninfo2)||!empty($investinfo2)): ?> 
        	<div class="symx_boxb">
	            <div class="disitem symx_gzgz symxgray">
	                <div class="symx_left ">● 2月收益</div>
	                <div class="symx_two"></div>
	                <div class="symx_three"><?php echo $total2;?>RMB</div>
	                <div class="symx_right three"></div>
	            </div>
				<?php endif; ?> 
	            <div class="symx_jycont">
				    <?php if (!empty($userinfo2)): ?> 
					<?php foreach ($userinfo2 as $key => $v): ?>
	            	<div class="disitem jycon_cont">
	            		<div class="contone">邀请好友<?php echo $v['user']['realname']; ?>
	            			<p><?php echo $v['create_time']; ?></p>
	            		</div>
	            		<div class="contwo"></div>
	            		<div class="conthree">获佣金<em><?php echo number_format($v['profit_amount'], 2, ".", ""); ?></em>RMB</div>
	            	</div>
					<?php endforeach; ?>
					<?php endif; ?> 
                    
					<?php if (!empty($loaninfo2)): ?> 
					<?php foreach ($loaninfo2 as $key => $v): ?>
	            	<div class="disitem jycon_cont">
	            		<div class="contone"><?php echo $v['loan']['user_id']; ?>借款<?php echo number_format($v['loan']['amount'], 2, ".", ""); ?>元 
	            			<p><?php echo $v['create_time'];?></p>
	            		</div>
	            		<div class="contwo"></div>
	            		<div class="conthree">获佣金<em><?php echo number_format($v['profit_amount'], 2, ".", ""); ?></em>RMB</div>
	            	</div>
					<?php endforeach; ?>
					<?php endif; ?> 

					<?php if (!empty($investinfo2)): ?> 
					<?php foreach ($investinfo2 as $key => $v): ?>
	            	<div style="border-bottom:0;" class="disitem jycon_cont">
	            		<div class="contone"><?php echo $v['invest']['user_id']; ?>投资<?php echo number_format($v['invest']['amount'], 2, ".", ""); ?>元 
	            			<p><?php echo $v['create_time'];?></p>
	            		</div>
	            		<div class="contwo"></div>
	            		<div class="conthree">获佣金<em><?php echo number_format($v['profit_amount'], 2, ".", ""); ?></em>RMB</div>
	            	</div>
					<?php endforeach; ?>
							   </div>
        	</div>
					<?php endif; ?> 

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