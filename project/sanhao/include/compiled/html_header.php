<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta property="qc:admins" content="227474672662045166375" /> 
<title>三好网闪购-<?php echo $pagetitle; ?></title>
<link rel="shortcut icon" href="/static/icon/favicon.ico" />
<link rel="stylesheet" type="text/css" href="/static/css/css.css" />
<link rel="stylesheet" type="text/css" href="/static/css/style.css" />
<link rel="stylesheet" type="text/css" href="/static/css/uploadify.css" />
<!--[if IE 6]> 
<script src="/static/js/DD_belatedPNG_0.0.8a-min.js" type="text/javascript"></script>
<script type="text/javascript">DD_belatedPNG.fix('*');</script> 
<![endif]-->
<script src="/static/js/jquery-1.7.2.min.js" type="text/javascript"></script>
<script src="/static/js/jquery.infieldlabel.min.js" type="text/javascript"></script>
<script src="/static/js/jQuery.artTxtCount.js" type="text/javascript"></script>
<script src="/static/js/content-slider.min.js" type="text/javascript"></script>
<script src="/static/js/jquery.timers.js" type="text/javascript"></script>
<script src="/static/js/register.js" type="text/javascript"></script>
<script src="/static/js/check.js" type="text/javascript"></script>
<script src="/static/js/ajaxfileupload.js" type="text/javascript"></script>
<script src="/static/js/jquery.uploadify.js" type="text/javascript"></script>
<script src="/static/js/product.js" type="text/javascript"></script>
<script src="/static/js/application.js" type="text/javascript"></script>
<script src="/static/js/jquery.cookie.js" type="text/javascript"></script>
<script src="/static/js/jquery.extend.js" type="text/javascript"></script>
<script src="/static/js/buy.js" type="text/javascript"></script>
<script type="text/javascript">
	$(function(){ $("label").inFieldLabels(); });
</script>
<script type="text/javascript">
$(function(){
	$(".contentMain .box .mt").first().addClass("cur").next().show();
	$(".contentMain .box .mt").click(function(){
		$(this).addClass("cur")
		.next().show()
		.parent().siblings()
		.children(".mt").removeClass("cur")
		.next().hide();
		return false;
	});	
})
</script>
<script type="text/javascript">
$(function(){
	$(".yesLogin .uname").live({
		mouseenter:function(){
			$(this).find('.s2').addClass("cur");
			$(this).find('.u').show();
		},mouseleave:function(){
			$(this).find('.s2').removeClass("cur");
			$(this).find('.u').stop(true,true).hide();
		}
	});
});
</script>
</head>

<body>
<div id="pagemasker"></div><div id="dialog"></div>
