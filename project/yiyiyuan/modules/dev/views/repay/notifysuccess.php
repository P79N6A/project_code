<div class="succsee">
        <div class="success_titlt">
            <img src="/images/touzi_true2.png">
            <span>提交成功！</span>
        </div>
        <img class="succsee_true" src="/images/success_tuika.png">
        <div class="succsee_txtx">
        	<p>你的<?php echo sprintf("%.2f", $loan_repay['money']);?>元还款已经提交，稍后请查看还款结果！</p>
        </div>
        <?php if($loan_repay['source'] == 1):?>
        <a href="/dev/loan/succ?l=<?php echo $loan_repay['loan_id'];?>"><button class="success_share">查看详情</button></a>
        <?php endif;?>
        
    </div>
    
            <script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
  wx.config({
	debug: false,
	appId: '<?php echo $jsinfo['appid'];?>',
	timestamp: <?php echo $jsinfo['timestamp'];?>,
	nonceStr: '<?php echo $jsinfo['nonceStr'];?>',
	signature: '<?php echo $jsinfo['signature'];?>',
	jsApiList: [
		'hideOptionMenu'
	  ]
  });
  
  wx.ready(function(){
	  wx.hideOptionMenu();
	});
</script>