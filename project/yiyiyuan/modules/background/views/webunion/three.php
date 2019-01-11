<?php
$arr =array('1'=>'未认证','2'=>'借款中','3'=>'已还款','0'=>'已认证');
?>
<div id="overDiv"></div>
	<div id="diolo_warp" class="friends">
		<section class="state">
			<div class="icon">头像</div>
			<div class="name">姓名</div>
			<div class="phone">手机号</div>
			<div class="state">状态</div>
		</section>
		<?php if (!empty($haoythree)): ?> 
	    <?php foreach ($haoythree as $key => $v): ?>
		<a href='/background/webunion/detial?user_id=<?php echo $v->user->user_id;?>'>
		<section class="list">
			<img src="<?php echo $v->user->company;?>" alt="" class="icon" />
			<div class="name"><?php echo $v->user->realname;?></div>
			<div class="phone"><?php echo $v->user->mobile;?></div>
			<div class="state <?php if ($v->user->status==1): ?> greengray <?php endif; ?>"><?php echo $arr[$v->user->status];?></div>

		</section>
		</a>
		<?php endforeach; ?>
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