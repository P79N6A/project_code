<div class="succsee">
        <div class="success_titlt">
            <img src="/images/touzi_true2.png">
            <span>退卡成功！</span>
        </div>
        <img class="succsee_true" src="/images/success_tuika.png">
        <div class="succsee_txtx">
            <p>资金将在<em>24小时内</em></p>
            <p>返还至您尾号<?php echo substr($bankInfo->card, strlen($bankInfo->card) - 4, 4) ?>的<?php echo $bankInfo->bank_name; ?>中。</p>
        </div>
        <a href="/dev/guarantee"><button class="success_share">返回</button></a>
        
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