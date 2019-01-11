    <div class="ture_sh">
        <div class="ture_title">赎回 “<em><?php echo $standard_information->information->name;?></em>”</div>
        <div class="form_wrapper"> 
            <div class="form_left">已投资</div> 
            <div class="form_right"> 
                <div class="form_content">
                    <input type="tel" placeholder="<?php echo $standard_information->total_onInvested_share;?>点" value="<?php echo $standard_information->total_onInvested_share;?>点" maxlength="10" class="phone-input" readOnly="true">
                </div> 
            </div> 
        </div>
        <div class="form_wrapper"> 
            <div class="form_left">赎回金额</div> 
            <div class="form_right"> 
                <div class="form_content">
                    <input type="tel" placeholder="最高赎回<?php echo $standard_information->total_onInvested_share;?>点，可部分赎回" id="reback_share" class="phone-input"  >
                </div> 
            </div> 
        </div>
        <input type="hidden" name="standard_id" id="standard_id" value="<?php echo $standard_information->information->id;?>">
        <input type="hidden" name="total_onInvested_share" id="total_onInvested_share" value="<?php echo $standard_information->total_onInvested_share;?>">
        <button id="standard_invest_reback" class="resetpay-sub">下一步</button>
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