<?php include template("header");?>
<script type="">
repasstime(this);
</script>
<div class="blank108"></div>
<div class="blank60"></div>
<div class="zhaohuimima">
	<div class="mt"></div>
    <div class="mc">
    	<h3><strong>找回密码</strong></h3>
        <ul class="steps steps2">
            <li class="li1">1.输入手机号</li>
            <li class="li2">2.验证身份</li>
            <li class="li3">3.设置新密码</li>
            <li class="li4">4.完成</li>
        </ul>
        <input type="hidden" id="mobile" value="<?php echo $mobile; ?>" />
        <div class="cont"><span class="fl">短信验证码已发送到您的手机<?php echo $mobile; ?>，请查收。</span><div id="repasscode_div" class="hqyzdiv"></div></div>
        <dl class="item2">
            <dt>短信验证码</dt>
            <dd><input type="text" id="repasssmscode" class="text" maxlength="4" /><em id="repasssmscode_check"></em></dd>
        </dl>
        <div class="btns">
        	<input type="button" id="repass_button2" value="" class="btn2" />
        </div>
    </div>
    <div class="mb"></div>
</div>
<div class="blank120"></div>


<?php include template("footer");?>