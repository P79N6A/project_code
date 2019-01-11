<?php

$type =array(
    '0'=>'储蓄卡',
    '1'=>'借记卡',
	'2'=>'信用卡',
);
?>
<div class="wrap">
		<div class="onself">
			<div>
				<span>头像</span>
				<img src="<?php echo $userwx->head;?>">
			</div>
			<div>
				<span>邀请码</span>
				<img src="/images/tgym_ewm.png">
			</div>
		</div>
		<div class="onself_cont">
			<p><em>姓名：</em><span><?php echo $userinfo->realname;?></span></p>
			<p><em>职业：</em><span><?php echo $userinfo->company;?></span></p>
			<p><em>学校：</em><span><?php echo $userinfo->school;?></span></p>
		</div>
		<div class="onself_bank">
			<div class="onbank_bank">
				<p class="yinheng">银行卡：</p>
				<div class="bank_cont">
				    <?php if (!empty($user_bank)): ?> 
					<?php foreach ($user_bank as $key => $v): ?>
					<div class="disitem" <?php if (($key)%2==1): ?>style="border-bottom:0;"<?php endif; ?>>
						<img src="/images/bank_logo/<?php echo $v->bank_abbr;?>.png">
						<div class="bankk_jijs" >
							<p><?php echo $v->bank_name;?>（<?php echo $type[$v->type];?>）</p>
							<div class="gray">尾号<em><?php echo substr($v->card,-4);?></em><div></div><span>绑定时间：<?php echo $v->create_time;?></span></div>
						</div>
					</div>
					<?php endforeach; ?>
					<?php endif; ?> 

				</div>
			</div>
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