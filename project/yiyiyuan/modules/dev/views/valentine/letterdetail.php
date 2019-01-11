<div class="clickwebg">
    <img src="/images/valentine/witherxin.png">
</div>
<div class="xinfengpage">
    <img src="/images/valentine/xinpage.png">
    <div class="xinjconte">
        <p><input placeholder="输入收信人" maxlength="20" value="<?php echo $valentine->nickname;?>" id="nickname" name="nickname"></p>
        <textarea placeholder="请输入内容（120字以内）" maxlength="120" id="content" name="content"><?php echo $valentine->content;?></textarea>
        <p class="wxnic"><?php echo $nickname;?></p>
    </div>
</div>
<div class="clickbutton twookok">

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
