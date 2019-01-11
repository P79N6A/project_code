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
    .width1qian{ width: 100%; margin: 0 auto;background: rgb(255, 90, 90);}
    img{ display: block;}
    .teldi5{ position: relative;}
    .teldi5 button{ position: absolute;top:0; width: 60%;margin: 0 20%;height: 4rem; background: rgba(0,0,0,0);}
    .xqingtxt{ margin:  0 6%; color: #352500; padding-bottom: 30px; font-size: 1rem; line-height: 22px;}
</style>
<body >
<div class="width1qian">
	<img src="/images/activity/xqing1.jpg">
	<img src="/images/activity/xqing2.jpg">
	<img src="/images/activity/xqing3.jpg">
	<img src="/images/activity/xqing4.jpg">
	<div class="teldi5">
		<img src="/images/activity/xqing5.jpg">
		<button class = 'yqhy'></button>
	</div>
	
	<div class="xqingtxt">
		<p>活动时间：2017年11月23日——2017年12月13日</p>
		<p>活动规则：</p>
		<p>1. 在先花一亿元发起借款或邀请好友成功下款，每天只需一名好友500元永久提额轻松享；</p>
		<p>2. 凡满足以上条件，系统每日抽出500名获得500元现金红包，数量有限，先到先得。</p>
		<p>3. 邀请好友数量越多，获得红包的几率越大哦~</p>

	</div>
	
</div>
	

</body>
</html>
<script>
//    var type = "<?php echo $type; ?>";
//    $(".yqhy").click(function () {
//        if (type == "app") {
//            window.myObj.bannerShare();
//        }
//    });
    function shpage(){
        window.myObj.bannerShare();
    }
    
    //分享
    $('.yqhy').click(function () {
        shpage();
        
    });
    function bannershare() {

    }
    
</script>



