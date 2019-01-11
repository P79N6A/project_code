<?php include template("header");?>

<script type="text/javascript">
function refreshValidateCode()
{
	document.getElementById("repass_pic").src="/captcha.php?"+Math.random();
}
</script>
<div class="blank108"></div>
<div class="blank60"></div>
<div class="zhaohuimima">
	<div class="mt"></div>
    <div class="mc">
    	<h3><strong>找回密码</strong></h3>
        <ul class="steps steps1">
            <li class="li1">1.输入手机号</li>
            <li class="li2">2.验证身份</li>
            <li class="li3">3.设置新密码</li>
            <li class="li4">4.完成</li>
        </ul>
        <dl class="item">
            <dt>手机号码</dt>
            <dd><input type="text" class="text" id="mobile" /><em id="mobile_check"></em></dd>
        </dl>
        <dl class="item">
            <dt>验证码</dt>
            <dd class="yzmdd"><input type="text" class="text" id="repass_code" maxlength="4" /><a href="javascript:void(0);" onclick="refreshValidateCode();"><img id="repass_pic" src="/captcha.php" alt="看不清楚，点击更换" /></a><a href="javascript:void(0);" onclick="refreshValidateCode();">看不清楚，换一张</a><em id="code_error"></em></dd>
        </dl>
        <div class="btns"><input type="button" id="repass_button1" class="btn" value="" /></div>
    </div>
    <div class="mb"></div>
</div>
<div class="blank120"></div>

<?php include template("footer");?>