<div class="wrap zqewym friends">
	<section class="list" style="margin-top:15px;">
		<div class="renzone">邀请任务1  </div>
		<div class="nametwo">邀请一名好友实名认证</div>
		<div class="pthree"><img src="/images/webunion/s2y.png"></div>
		<div step="1" class="fouryis receive <?php echo $task_reg >=1?'garyccc':'' ?>">领取5元</div>
	</section>
	<section class="list">
		<div class="renzone">邀请任务2  </div>
		<div class="nametwo">邀请五名好友实名认证</div>
		<div class="pthree"><img src="/images/webunion/s2y.png"></div>
		<div step="2" class="fouryis receive <?php echo $task_reg >=2?'garyccc':'' ?>">领取30元</div>
	</section>
	<section class="list">
		<div class="renzone">邀请任务3  </div>
		<div class="nametwo"> 邀请十名好友实名认证</div>
		<div class="pthree"><img src="/images/webunion/s2y.png"></div>
		<div step="3" class="fouryis receive <?php echo $task_reg >=3?'garyccc':'' ?>">领取65元</div>
	</section>
</div>
<div id="overDiv" style="display:none;"></div>
<div class="lqewsy" style="display:none;">
    <p class="ewasy"><span>请先完成前面的任务</span></p>
	<p class="trueqd">确定</p>
</div>

<script>
    $('.nav_right').click(function(){
        window.location.href = '<?php echo $returnUrl ?>';
    })
</script>
<script>
	$(function(){
		var step_now = <?php echo $task_reg+1 ?>;

		$('.receive').click(function(){
			var step = $(this).attr('step');
			if (step == step_now) {
				window.location.href = '/background/task/taskdetail?step='+step;
			}else if(step>step_now){
				$('.lqewsy').show();
	        	$('#overDiv').show();
			}
		})

		$('.trueqd').click(function(){
	        $('.lqewsy').hide();
	        $('#overDiv').hide();
	    })
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