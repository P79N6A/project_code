<div class="selfmess">
	<div class="selftximg">
		<div class="dbk_inpL">
        	<label>姓名</label><input placeholder="请输入姓名" type="text" value="<?php echo $userinfo->realname;?>" readonly="readonly" name="realname" id="reg_realname" maxlength="10">
    	</div>
    	<div class="dbk_inpL">
        	<label>身份证号</label><input placeholder="请输入身份证号" type="text" name="identity" value="<?php echo $userinfo->identity;?>" readonly="readonly" id="reg_identitys" maxlength="18" is_real='0'>
    	</div>
    	<div class="dbk_inpL">
        	<label>手机号</label><input readonly="readonly" value="<?php echo $userinfo->mobile;?>" type="text">
    	</div>
	</div>
</div>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
                wx.config({
                    debug: false,
                    appId: '<?php echo $jsinfo['appid']; ?>',
                    timestamp: <?php echo $jsinfo['timestamp']; ?>,
                    nonceStr: '<?php echo $jsinfo['nonceStr']; ?>',
                    signature: '<?php echo $jsinfo['signature']; ?>',
                    jsApiList: [
                        'hideOptionMenu'
                    ]
                });

                wx.ready(function() {
                    wx.hideOptionMenu();
                });
</script>