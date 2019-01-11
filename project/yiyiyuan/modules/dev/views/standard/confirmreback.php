    <div class="ture_sh">
        <div class="ture_title">赎回<em><?php echo $reback_share;?>点</em>“<em><?php echo $title;?></em>”</div>
        <div class="form_wrapper"> 
            <div class="form_left">手机号</div> 
            <div class="form_right"> 
                <div class="form_content">
                    <?php echo $mobile;?>
                </div> 
            </div> 
        </div>
        <div class="free_code">
             <input type="tel" placeholder="验证码" maxlength="4" id="reback_standard_code" class="yzm_input">
            <button type="button" class="btn code-obtain" id="reback_standard_getcode">获取验证码</button> 
        </div>
        
        <input type="hidden" name="mobile" id="mobile" value="<?php echo $mobile;?>"/>
        <input type="hidden" name="standard_id" id="standard_id" value="<?php echo $standard_id;?>"/>
        <input type="hidden" name="reback_share" id="reback_share" value="<?php echo $reback_share;?>"/>
        <button id="standard_reback_confirm" class="resetpay-sub">确定赎回</button>
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