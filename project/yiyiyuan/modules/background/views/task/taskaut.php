<div class="wrap zqewym friends">
	<section class="borderbottom">
		<div class="left">
			<div class="renzcont"><?php echo $count ?></div>
			<div class="renzend">已完成认证（人）</div>
		</div>
	</section>
	
	<section class="list" step="1">
		<div class="renzone">认证任务1  </div>
		<div class="nametwo">邀请三名好友认证答题</div>
		<div class="pthree"><img src="/images/webunion/s2y.png"></div>
		<div class="fouryi <?php echo $task_aut>0?'garyccc':'' ?>">领取 <br/>15元优惠券</div>
	</section>
	<section class="list" step="2">
		<div class="renzone">认证任务2  </div>
		<div class="nametwo">邀请五名好友实名认证</div>
		<div class="pthree"><img src="/images/webunion/s2y.png"></div>
		<div class="fouryi <?php echo $task_aut>1?'garyccc':'' ?>">领取 <br/>25元优惠券</div>
	</section>
	<section class="list" step="3">
		<div class="renzone">认证任务3  </div>
		<div class="nametwo"> 邀请八名好友实名认证</div>
		<div class="pthree"><img src="/images/webunion/s2y.png"></div>
		<div class="fouryi <?php echo $task_aut>2?'garyccc':'' ?>">领取 <br/>50元优惠券</div>
	</section>
	<section class="list" step="4">
		<div class="renzone">认证任务4  </div>
		<div class="nametwo"> 邀请十二名好友实名认证</div>
		<div class="pthree"><img src="/images/webunion/s2y.png"></div>
		<div class="fouryi <?php echo $task_aut>3?'garyccc':'' ?>">领取 <br/>75元优惠券</div>
	</section>
	<section class="list" step="5">
		<div class="renzone">终极任务  </div>
		<div class="nametwo"> 邀请十五名好友实名认证</div>
		<div class="pthree"><img src="/images/webunion/s2y.png"></div>
		<div class="fouryi <?php echo $task_aut>4?'garyccc':'' ?>">领取 <br/>100元优惠券</div>
	</section>
</div>

<div id="overDiv" style="display:none;"></div>
<div class="lqewsy" style="display:none;">
  <p class="ewasy">请先完成前面的任务</p><p class="trueqd">确定</p>
</div>
<script>
    $('.nav_right').click(function(){
        window.location.href = '<?php echo $returnUrl ?>';
    })
</script>
<script>
	var step_now = <?php echo $task_aut+1 ?>;
	$('.list').click(function(){
		var step = $(this).attr('step');
		if (step==step_now) {
			window.location.href = '/background/task/autdetail?step='+step;
		}else if(step>step_now){
			$('.lqewsy').show();
			$('#overDiv').show();
		}
	})

	$('.trueqd').click(function(){
		$('.lqewsy').hide();
		$('#overDiv').hide();
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