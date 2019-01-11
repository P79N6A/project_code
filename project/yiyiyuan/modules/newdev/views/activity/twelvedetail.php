<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title></title>
    <link rel="stylesheet" type="text/css" href="/css/reset.css"/>
    <link rel="stylesheet" type="text/css" href="/css/index.css"/>
</head>
<style>
    body{width:100%;  font-family: "Microsoft YaHei"; background: #ff5a5a;font-family:"微软雅黑";}
    img{ display: block;}
    .width1qian{ width: 100%; margin: 0 auto;background: rgb(255, 90, 90);}
    .teldi5{ position: relative;}
    .teldi5 button{ position: absolute;top:0; width: 60%;margin: 0 20%;height: 4rem; background: rgba(0,0,0,0);}
</style>
<body >
<div class="width1qian">
	<img src="/images/activity/teldi1.jpg">
	<img src="/images/activity/teldi2.jpg">
	<img src="/images/activity/teldi3.jpg">
	<img src="/images/activity/teldi4.jpg">
	<div class="teldi5">
		<img src="/images/activity/teldi5.jpg">
		<button class="dwlhbao" lid = "/wap/st/down"></button>
	</div>

	
</div>
</body>
</html>
<script>
    
//    function downurl(){
//        window.location.href = '/wap/ds/down';
//    }
//    $(".download_page").click(function(){
//        downurl();
//    })
$(".dwlhbao").click(function(){
        var down = $(this).attr('lid');
        window.location.href=down;
    })
</script>