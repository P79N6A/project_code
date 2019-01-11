<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>请选择银行</title>
<meta name="Keywords" content="请选择银行">
<meta name="Description" content="请选择银行">
<style>
body{font-size:12px;font-family:Arial, Helvetica, sans-serif;margin:0;padding:0;color:#333;background:#fff;}
html,body,div,span,applet,object,iframe,h1,h2,h3,h4,h5,h6,p,blockquote,pre,a,abbr,acronym,address,big,cite,code,del,dfn,em,font,img,ins,kbd,q,s,samp,small,strike,strong,sub,sup,tt,var,b,u,i,center,dl,dt,dd,ol,ul,li,fieldset,form,label,legend,table,caption,tbody,tfoot,thead,tr,th,td,input,select,textarea { margin:0;padding:0;}
em,i{ font-style:normal;}
body { background:#f1ede6; font-family:'微软雅黑','黑体'}
.ucenter_main { width:970px; margin:0 auto; color:#7c807f;}
.ucenter_top { border:1px solid #eeeae2; overflow:hidden; background:#f9f5ed; margin-top:30px;
border-radius:5px;
box-shadow:0px 1px 2px #eeeae2;
}
.ucenter_top .title { display:inline-block; float:left; width:318px; height:160px; color:#666; font-size:30px; background:#ded14d; line-height:160px; text-align:center;}
.ucenter_top .money { display:inline-block; float:left; width:600px; margin:15px 0; border-left:1px solid #e4e0d8; line-height:126px; text-align:center; font-size:26px;}
.ucenter_top .money em { display:inline-block; color:#e77d65; padding:0 10px;}
.ucenter_banklist { border:1px solid #eeeae2; border-top:16px solid #0cc3b3; margin:20px 0; overflow:hidden; background:#f4f3ee; position:relative;
border-radius:5px;
box-shadow:0px 1px 2px #eeeae2;
}
.ucenter_banklist .line2 { margin:0 160px;}
.ucenter_banklist .title_info { color:#7e3c1c; position:absolute; top:25px; left:200px; font-size:16px; width:660px; line-height:22px;}
.ucenter_banklist h4 { font-size:18px; margin:20px 0 0 60px;}
.ucenter_banklist h5 { font-size:18px; margin:20px 0 0 200px; clear:both;}
.ucenter_banklist .banklist1 { width:770px; float:right;}
.ucenter_banklist .banklist1 li { width:190px; height:60px; line-height:60px; display:inline; float:left; font-size:18px;}
.ucenter_banklist .banklist1 li label { position:relative;}
.ucenter_banklist .banklist1 li label input { margin-right:10px;}
.ucenter_banklist .mail { clear:both; width:620px; border:2px solid #fff; background:#fc9a6f; margin-left:200px;
border-radius:10px;
}
.ucenter_banklist .mail p { background:url(../static/images/icon_warn.png) no-repeat; padding:0 0 0 100px; font-size:18px; color:#fff; line-height:35px; margin:20px 30px; height:70px;}
.ucenter_banklist .mail p a { color:#f8f98d; text-decoration:underline;}
.ucenter_banklist .form { line-height:40px; font-size:16px; width:620px; margin:20px 0 20px 200px;}
.ucenter_banklist .form .tip { color:#a69d99; width:160px; text-align:right; display:inline-block;}
.ucenter_banklist .input1 { line-height:30px; height:30px; border:1px solid #ebe7de; padding:0 2px; color:#999;outline:none;
box-shadow:1px 1px 2px #eee;
}
.ucenter_banklist .formmsg { width:620px; margin-left:200px; text-align:center; color:#df3a24; font-size:16px; line-height:25px;}
.ucenter_banklist .copy { width:620px; margin-left:200px; margin-top:30px; border-bottom:1px solid #eee; padding-bottom:20px; position:relative;}
.ucenter_banklist .copy a { position:absolute; right:0px;}
.ucenter_banklist .banklist2 { width:770px; padding:20px 0 20px 50px; margin:50px 0 0; float:right; border-top:1px solid #e4e0d8; border-bottom:1px solid #e4e0d8;}
.ucenter_banklist .banklist2 li { width:190px; height:60px; line-height:60px; display:inline; float:left; font-size:18px;}
.ucenter_banklist .banklist2 li label { position:relative;}
.ucenter_banklist .banklist2 li label input {margin-right: 10px;position: absolute;top: 25px;}
.ucenter_banklist .banklist2 li label img { float:right; margin:15px 21px 0 0;}
.ucenter_banklist .btncon { clear:both; text-align:center; padding:20px;}
/*按钮*/
input { text-decoration:none;}
.btnblue { background:#09c3b3; border:1px solid #c8e0d9; display:inline-block; padding:0 30px; line-height:32px; color:#fffefe; font-size:14px;
border-radius:3px;cursor:pointer;
}
.btnblue:hover { background:#69a2a4; border:1px solid #c1d3db; text-decoration:none;}
.topline { background:url(../static/images/line-top-nobg.png) repeat-x; display:block; height:70px;}
</style>
<script src="/static/js/jquery-1.7.2.min.js" type="text/javascript"></script>
<script type="text/javascript">
$(function(){
	$('#yeepay_order_button').bind('click',function(){
		var paytype = $("input[name='bank']:checked").val() ;
		if( paytype == undefined || paytype == null || paytype == "" ){
			alert("请选择银行！");
			return false;
		}
		else
		{
			$('form[id="order-yeepay-form"]').submit();
		}
	});
});
</script>
</head>

<body>
<span class="topline"></span>
<div class="ucenter_main">
  <div class="ucenter_top clearfix"> 
  	<span class="title">请选择银行</span>
    <span class="money">应支付金额:<em>¥<?php echo $order['amt']; ?></em></span> 
  </div>
  <div class="ucenter_banklist">
 <form action="/order/yeepay.php" method="post" sid="<?php echo $order['id']; ?>" id="order-yeepay-form">
    <ul class="banklist2 clearfix">
      <li><label><input type="radio" value="BOC-NET-B2C" name="bank"><img src="/static/images/tem/bank1.png" /></label></li>
      <li><label><input type="radio" value="ICBC-NET-B2C" name="bank"><img src="/static/images/tem/bank2.png" /></label></li>
      <li><label><input type="radio" value="ABC-NET-B2C" name="bank"><img src="/static/images/tem/bank3.png" /></label></li>
      <li><label><input type="radio" value="CMBCHINA-NET-B2C" name="bank"><img src="/static/images/tem/bank4.png" /></label></li>
      <li><label><input type="radio" value="BOCO-NET-B2C" name="bank"><img src="/static/images/tem/bank5.png" /></label></li>
      <li><label><input type="radio" value="CCB-NET-B2C" name="bank"><img src="/static/images/tem/bank6.png" /></label></li>
      <li><label><input type="radio" value="POST-NET-B2C" name="bank"><img src="/static/images/tem/bank7.png" /></label></li>
      <li><label><input type="radio" value="GDB-NET-B2C" name="bank"><img src="/static/images/tem/bank8.png" /></label></li>
      <li><label><input type="radio" value="HXB-NET-B2C" name="bank"><img src="/static/images/tem/bank9.png" /></label></li>
      <li><label><input type="radio" value="CEB-NET-B2C" name="bank"><img src="/static/images/tem/bank10.png" /></label></li>
      <li><label><input type="radio" value="CMBC-NET-B2C" name="bank"><img src="/static/images/tem/bank11.png" /></label></li>
      <li><label><input type="radio" value="ECITIC-NET-B2C" name="bank"><img src="/static/images/tem/bank12.png" /></label></li>
      <li><label><input type="radio" value="BCCB-NET-B2C" name="bank"><img src="/static/images/tem/bank13.png" /></label></li>
      <li><label><input type="radio" value="CIB-NET-B2C" name="bank"><img src="/static/images/tem/bank14.png" /></label></li>
      <!--<li><label><input type="radio" name="bank"><img src="/static/images/tem/bank15.png" /></label></li>-->
      <li><label><input type="radio" value="SDB-NET-B2C" name="bank"><img src="/static/images/tem/bank16.png" /></label></li>
      <!--<li><label><input type="radio" value="" name="bank"><img src="/static/images/tem/bank17.png" /></label></li>-->
      <li><label><input type="radio" value="BJRCB-NET-B2C" name="bank"><img src="/static/images/tem/bank18.png" /></label></li>
      <li><label><input type="radio" value="NBCB-NET-B2C" name="bank"><img src="/static/images/tem/bank19.png" /></label></li>
      <li><label><input type="radio" value="PINGANBANK-NET-B2C" name="bank"><img src="/static/images/tem/bank20.png" /></label></li>
      <!--<li><label><input type="radio" value="" name="bank"><img src="/static/images/tem/bank21.png" /></label></li>
      <li><label><input type="radio" value="" name="bank"><img src="/static/images/tem/bank22.png" /></label></li>-->
      <li><label><input type="radio" value="SPDB-NET-B2C" name="bank"><img src="/static/images/tem/bank23.png" /></label></li>
      <li><label><input type="radio" value="SHB-NET-B2C" name="bank"><img src="/static/images/tem/bank24.png" /></label></li>
    </ul>
    <input type="hidden" name="paytype" value="yeepay" />
    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>" />
    <div class="btncon"><input type="submit" id="yeepay_order_button" class="btnblue" value="确定"></input></div>
    </form>
  </div>
</div>

</body>
</html>
