<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>三好网闪购-后台首页</title>
<link rel="stylesheet" type="text/css" href="/static/css/css.css" />
<link rel="stylesheet" type="text/css" href="/static/css/style2.css" />
<!--[if IE 6]> 
<script type="text/javascript" src="/static/js/DD_belatedPNG_0.0.8a-min.js"></script> 
<script type="text/javascript">DD_belatedPNG.fix('*');</script> 
<![endif]-->
<script type="text/javascript" src="/static/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="/static/js/manage.js"></script>
<script type="text/javascript">
function refreshValidateCode()
{
	document.getElementById("repass_pic").src="/captcha.php?"+Math.random();
}
</script>
</head>

<body style="background:url(/static/images/bbg.jpg) top center no-repeat;">
<div class="blogin">
<form action="/manage/login.php" method="post" id="admin_login_form">
	<div class="itm clearfix">
    	<div class="dt">用户名:</div>
        <div class="dd"><input type="text" name="username" id="username" class="ipt1" /></div>
    </div>
    <div class="itm clearfix">
    	<div class="dt">密　码:</div>
        <div class="dd"><input type="password" name="password" id="pwd" class="ipt1" />
        	<!--p class="tips1"><a href="#">忘记密码？</a></p -->
        </div>
    </div>
    <div class="itm clearfix">
    	<div class="dt">验证码:</div>
        <div class="dd">
        <input type="text" class="ipt2" name="vcaptcha" id="checkcode" /><a href="javascript:void(0);" onclick="refreshValidateCode();" class="yzmn"><img id="repass_pic" src="/captcha.php" alt="看不清楚，点击更换" ></a>
        	<p class="tips2" id='admin_login_reset'><?php if(!empty($msg)){?><?php echo $msg; ?><?php }?></p>
        </div>
    </div>
    <div class="btns clearfix">
    	<input type="button" class="btn1" id="admin_login_submit" value=""/>
    </div>
</div>
</form>
<div class="bfooter">
	©2011 三好网 
</div>
</body>
</html>
