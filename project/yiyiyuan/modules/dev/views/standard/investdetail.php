<div class="tzxqing_ye">
		<div class="tzxqing_bg">
			<h3><?php echo $standard_statistics->information->name;?></h3>
			<p><span>投资金额：</span><em><?php echo sprintf('%.2f',$standard_statistics->total_onInvested); ?>点</em></p>
			<p><span>预期年收益：</span><em><?php echo sprintf('%.2f',$standard_statistics->information->yield); ?>%</em></p>
			<p><span>期限：</span><em><?php echo $standard_statistics->information->cycle;?>天</em></p>
			<p><span>预期收益：</span><em>¥<?php echo sprintf('%.2f',$standard_statistics->achieving_interest); ?>元</em></p>
			<?php if(!empty($standard_statistics->coupon_id) && ($standard_statistics->coupon_id != 0)):?>
			<p><span>优惠券：</span><em><?php echo $standard_statistics->coupon->cycle;?>天<?php echo $standard_statistics->coupon->field;?>倍收益劵</em></p>
			<?php endif;?>
			<p><span>到期日：</span><em><?php echo date('Y'.'年'.'n'.'月'.'j'.'日', strtotime($standard_statistics->information->end_date));?></em></p>
		</div>
		
		<?php if(($standard_statistics->information->status == 'SUCCEED') && ($standard_statistics->total_onInvested_share >0) && ((time() >= (strtotime($standard_statistics->information->start_date)+24*3600)) && (time() < (strtotime($standard_statistics->information->end_date)-24*3600)))):?>
		<div class="agree">  <input type="checkbox" checked="checked" name="agree_check" id="agree_check" />  同意 <a>《先花一亿元投资咨询与管理服务协议》</a></div>
		<input type="hidden" name="standard_id" id="standard_id" value="<?php echo $standard_statistics->standard_id;?>"/>
        <button class="true_touzi" id="reback_invest">赎回投资</button>
		<?php endif;?>
	</div>