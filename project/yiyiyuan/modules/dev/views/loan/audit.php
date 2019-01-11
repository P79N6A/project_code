        <div class="Hcontainer nP">
          <div class="main">
            <p class="text-center mb70 mt40 n48"><img src="/images/icon_valid3.png" style="width:10%;max-width:56px;"> 信息审核中...</p>
            <p class="text-center mt20 n22 mb100" style="border-top:1px solid #e74747;padding-top:10px;">由于您是初次借款，我们会在24小时内完成信息审核。</p>
            <button class="btn mt40" id="loan_refresh" t="<?php echo $loan_id?>" style="width:100%;">刷新</button>
          </div>
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