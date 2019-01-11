<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title></title>
    <link rel="stylesheet" type="text/css" href="/css/coupon/septemberreset.css"/>
    <link rel="stylesheet" type="text/css" href="/css/coupon/septemberindex.css"/>
    <script src="/js/september.js"></script>
    <script>
$(function(){
    $('.duihsucc3 .errory').click(function(){
        $('.Hmask').hide();
        $('.duihsucc3').hide();
    });

    $('.Hmask').click(function(){
        $('.Hmask').show();
    });

    $('.faqdbjk button').click(function(){
        $('.Hmask').hide();
        $('.faqdbjk').hide();
    });

})
    </script>

<body>

<div class="actvete">
    <div class="bannerimg" >
        <img src="/images/activity/septemberxqone.jpg">
        <img src="/images/activity/septemberxqtwo.jpg">
	</div>

    <div class="buttxt">
        <div class="contnewym"><img src="/images/activity/setembercontnewym.png"></div>
        <div class="certifn hdguize">
           <div class="bortop"></div>
            <h3>活动规则：邀请2位好友即可领取88元免息券</h3>
			<p>1.活动时间：2017年9月19日10：00 — 2017年10月9日 24:00</p>
			<p>2.点击活动页中按钮开始参与活动，将活动页分享至好友或朋友圈后，成功邀请注册2人，即可点击领取88元免息券。</p>
			<h3>免息券发放：</h3>
			<p>1.邀请好友达到2位之后，所获得的免息券系统会自动发送到用户的账户中；</p>
			<p>2.使用范围：供用户在活动期间9月19日—10月9日使用；</p>
			<p>3.免息券有效期：2017年10月9日到期，免息券只有在免息券标识的有效期内使用才会获得相应金额的优惠；</p>
			<p>4.免息券发放对象，参与活动的用户，不是全体用户；</p>
			<p class="family">本次活动最终解释权归一亿元所有</p>
        </div>
    </div>

</div>


<div class="Hmask" hidden></div>
<div class="duihsucc3 yaoqingchenggong" hidden>
	<a class="errory"><img src="/images/activity/septemberimg.png"></a>

	<div class="zymxx">
		<div class="lqcg"><img src="/images/activity/septemberlqcg.png"></div>
	    <p class="yiyyuan fangzhi">88元免息券已发放至您的账户，<br/>快喊TA来领取吧</p>
	    <p class="yiyyuan yuan" hidden>快喊TA来领取88元免息券</p>

	    <button class="buttoner toPage"><img src="/images/activity/septemberbuttoner.png"></button>
	    <button class="buttonsan yq"><img src="/images/activity/septemberbuttonsan.png"></button>
   </div>
</div>

<div class="duihsucc3 yaoqing" hidden>
	<a class="errory"><img src="/images/activity/septemberimg.png"></a>
	<div class="zymxx">
		<div class="lqcg"><img src="/images/activity/septemberyqhy.png"></div>

	    <p class="yiyyuan">邀请2位好友即可领取88元免息券<br/>已邀请：<span id="count"><?php echo $count?></span>/2</p>
	    <button class="buttonyi yq"><img src="/images/activity/buttonyi.png"></button>
   </div>
</div>
<p hidden id="user_id"><?php echo $user_id?></p>
<p hidden id="counp"><?php echo $counp?></p>
</body>
<script>
    $(".contnewym").click(function () {
        var count = $("#count").text();
        var user_id = $("#user_id").text();
        var counp = $("#counp").text();
//        if(count >= 2){
            $.ajax({
                type: "GET",
                url: "/new/activity/septembersend",
                data: {user_id:user_id},
                success: function(data){
                    if(data ==1){
                        if(counp  == 0){
                            $(".yaoqingchenggong").show();
                            $('.Hmask').show();
                            $("#counp").text(1);
                        }else{
                            $(".yaoqing").show();
                            $('.Hmask').show();
                        }
                    }else{
                        if(counp == 0){
                            $(".yaoqing").show();
                            $('.Hmask').show();
                        }else{
                            $(".yaoqingchenggong").show();
                            $(".fangzhi").hide();
                            $(".yuan").show();
                            $('.Hmask').show();
                        }

                    }
                }
            });
//        }else{
//            $(".yaoqing").show();
//            $('.Hmask').show();
//        }
    });
    var u = navigator.userAgent, app = navigator.appVersion;
    var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1;
    var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
    var android = "<?php echo $android;?>";
    var ios = "<?php echo $ios;?>";
    var position = "<?php echo $position;?>";

    $('.toPage').click(function () {
        if (isiOS) {
            window.myObj.toPage(ios);
        } else if(isAndroid) {
            window.myObj.toPage(android, position);
        }
    });

    function toPage(activityName, position) {

    }
    var type = "<?php echo $type; ?>";
    $(".yq").click(function () {
        if (type == "app") {
            window.myObj.bannerShare();
        }
    });
    function bannershare() {
        //alert("fff");
    }
</script>