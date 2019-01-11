    <div class="succsee">
        <div class="success_titlt">
            <img src="/images/touzi_false.png">
            <span>投资失败！</span>
        </div>
        <img class="succsee_true margin0" src="/images/touzi_false1.png">
        <div class="error_false">
            <h3>可能的原因：</h3>
            <p>1.标的已满或您投资的额度大于标的的剩余额度</p>
            <p>2.标的已过期，请投资下一个</p>
        </div>
        <a href="/dev/loan"><button class="success_share">返回</button></a>
       
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