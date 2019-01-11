<?php
$arr =array('1'=>'未认证','2'=>'借款中','3'=>'已还款','0'=>'已认证','4'=>'已逾期');
?>
<div class="friends">
	<div class="header">
		<img src="/images/people.png" alt="" />一级好友
	</div>
	<section class="state">
		<div class="icon">头像</div>
		<div class="name">姓名</div>
		<div class="phone">手机号</div>
		<div class="state">状态</div>
	</section>
	<?php if (!empty($friends)): ?> 
	<?php foreach ($friends as $v): ?>
	<a href='/background/friend/detail?user_id=<?php echo $v->user_id;?>'>
	<section class="list">
		<img src="<?php echo $v->openid;?>" alt="" class="icon" />
		<div class="name"><?php echo $v->realname;?></div>
		<div class="phone"><?php echo substr_replace($v->mobile,'****',3,4);?></div>
		
	    <div class="state <?php if ($v->status==1): ?> greengray <?php endif; ?>"><?php echo $arr[$v->status];?></div>

	</section>
	</a>
	<?php endforeach; ?>
	<?php endif; ?> 
</div>
<?php if ($more != 0): ?>
	<button id="button" style="width: 50%;padding: 7px 0;margin-left: 25%;margin-top: 15px;border-radius: 20px;font-size: 1.25rem;color: #fff; background: #e74747;" page="<?php echo $more ?>">加载更多</button>
<?php endif ?>
<script>
    $('.nav_right').click(function(){
        window.location.href = '<?php echo $returnUrl ?>';
    })
</script>
<script>
	$('#button').click(function(){
		$(this).hide();
		var page = $(this).attr('page');
		$.get('/background/friend/friendone',{page:page},function(data){
			$('.friends').append(data.data);
			if (data.page != 0) {
				$('#button').attr('page',data.page);
				$('#button').show();
			};
		},'json');
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