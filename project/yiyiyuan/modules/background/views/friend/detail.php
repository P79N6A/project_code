<?php
$arr =array('1'=>'未认证','2'=>'借款中','3'=>'已还款','0'=>'已认证','4'=>'已逾期');
?>
<div class="wrap">
	<div class="index_bg">
		<div class="disitem bg_with">
			<img src="<?php if (empty($userwx)): ?> /images/icon.png<?php else: ?><?php echo $userwx->head;?><?php endif; ?>">
			<div>
				<p>昵称：<em><?php if (empty($userwx)): ?> xxx<?php else: ?><?php echo $userwx->nickname;?><?php endif; ?></em></p>
			</div>
		</div>
	</div>
	<div class="onself_cont friengxq">
	    <p><em>姓名</em><?php echo $userinfo->realname;?></p>
		<p><em>手机号</em><?php echo substr_replace($userinfo->mobile,'****',3,4);?></p>
		<p style="border-bottom:0;"><em>职 业&nbsp;&nbsp;</em><?php echo $userinfo->position;?></p>
	</div>
	
	<div class="onself_cont friengxq">
		<p><em>好友状态</em><i><?php echo $arr[$status];?></i></p>
	    <?php if ($status==2): ?> 
		<p class="disitem bezu" style="border-bottom:0;">
			<em>备注</em>
			<b>好友借款<em><?php echo $amount;?>RMB</em>
				<!--<br/>得到佣金<em>3.2RMB</em>-->
			</b>	
		</p>
		<?php endif; ?> 
	</div>
</div>
<script>
    $('.nav_right').click(function(){
        history.go(-1);
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