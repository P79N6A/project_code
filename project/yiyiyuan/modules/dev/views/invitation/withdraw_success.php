<div class="fInvitation">
	<div class="sorry">
		<?php if($now_time >= $start_time && $now_time <= $end_time):?>
		<p class="sorry1">受春节期间（2月5日－2月15日）银行系统影响，红包已发到您手机号<?php echo $account_settle->user->mobile;?>的账户中，请关注一亿元微信公众号，去账户－收益中查看 </p>
		<?php else:?>
		<p class="sorry1"><?php echo sprintf("%.2f", $account_settle->amount);?>元现金提现成功！ </p>
		<div class="twotimedz">
			<p>预计2小时到账！</p>
			<p>请查看您尾号<?php echo substr($account_settle->bank->card, -4);?>的中国银行银行卡</p>
		</div>
		<?php endif;?>
		<p class="sorry3"><img src="/images/account/ma2.png"></p>
		<p class="sorry4">长按识别上方二维码</p>
	</div>
	<div class="linktzjk">
		<div class="linkleft">
			<h3>投资收益高</h3>
			<p><img src="/images/account/small.png"><span>投资标的</span></p>
			<p><img src="/images/account/small2.png"><span>投资好友</span></p>
			<p><img src="/images/account/small3.png"><span>投资先花宝</span></p>
		</div>
		<div class="linkright">
			<h3>借款速度快</h3>
			<p><img src="/images/account/small2.png"><span>投资标的</span></p>
			<p><img src="/images/account/small4.png"><span>投资标的</span></p>
			<div class="poabo"><img src="/images/account/smallbig.png"></div>
		</div>
	</div>
</div>