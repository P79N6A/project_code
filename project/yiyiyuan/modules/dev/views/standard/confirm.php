    <div class="ture_sh">
        <div class="ture_title">投资<em><?php echo $invest_share;?>点</em>“<?php echo $title;?>”</div>
        <div class="ture_txt" id="send_mobile"></div>
        <div class="free_code">
             <input type="tel" placeholder="验证码" maxlength="4" id="invest_standard_code" class="yzm_input">
            <button type="button" class="btn code-obtain" id="invest_standard_getcode">获取验证码</button> 
        </div>
        <input type="hidden" name="coupon_id" id="coupon_id" value="<?php echo $coupon_id;?>"/>
        <input type="hidden" name="mobile" id="mobile" value="<?php echo $mobile;?>"/>
        <input type="hidden" name="standard_id" id="standard_id" value="<?php echo $standard_id;?>"/>
        <input type="hidden" name="invest_share" id="invest_share" value="<?php echo $invest_share;?>"/>
        <button id="standard_invest_confirm" class="resetpay-sub">确认投资</button>
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