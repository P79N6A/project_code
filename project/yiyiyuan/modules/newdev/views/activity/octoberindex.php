<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title></title>
    <link rel="stylesheet" type="text/css" href="/css/dev/octoberreset.css"/>
    <link rel="stylesheet" type="text/css" href="/css/dev/octoberindex.css"/>
    <script src="/js/october.js"></script>
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
        <img src="/images/activity/octoberjxbd1.jpg">
        <img src="/images/activity/octoberjxbd2.jpg">
        <img src="/images/activity/octoberjxbd3.jpg">
        <img src="/images/activity/octoberjxbd4.jpg">
    </div>
    <div class="jxbd5">
        <img src="/images/activity/octoberjxbd5.jpg">
        <button id="linqu"></button>
    </div>
    <div class="buttxt">
        <div class="certifn">
            <div class="bortop"></div>
            <h3>活动规则：</h3>
            <p>点击活动页中按钮开始参与活动，绑定信用卡后，系统自动发放99元的免息券到用户账户中，可直接使用。</p>
            <h3>使用规则：</h3>
            <p>1.活动时间：2017年10月10日 — 2017年10月31日；</p>
            <p>2.99元免息券有效期：2017年10月31日到期，99元免息券只有在免息券标识的有效期内使用才会获得相应金额的优惠；</p>
            <p>3.免息券发放对象，仅限参与本次绑卡活动的用户。</p>
            <p class="family">本次活动最终解释权归一亿元所有</p>
        </div>
    </div>

</div>


<div class="Hmask" hidden></div>
<div class="duihsucc3 error" hidden>
    <a class="errory"><img src="/images/activity/octoberimg.png"></a>
    <div class="zymxx">
        <img src="/images/activity/octobertyac1.png">
        <button id="yq"></button>
    </div>
</div>

<div class="duihsucc3 success"  hidden>
    <a class="errory"><img src="/images/activity/octoberimg.png"></a>
    <div class="zymxx">
        <img src="/images/activity/octobertyac2.png">
        <button class="toPage"></button>
    </div>
</div>
<p hidden id="user_id"> <?php echo $user_id ?></p>
<p hidden id="bank"> <?php echo $bank ?></p>
</body>
</html>
<script>
    $("#linqu").click(function () {
        var user_id = $("#user_id").text();
        var bank = $("#bank").text();
        if(bank == 1){
            $.ajax({
                type: "GET",
                url: "/new/activity/october",
                data: {user_id:user_id},
                success: function(msg){
                    if(msg ){
                        $('.Hmask').show();
                        $('.error').show();
                    }
                }
            });
        }else{
            $('.Hmask').show();
            $('.success').show();
        }

    });
    var type = "<?php echo $type; ?>";
    $("#yq").click(function () {
        if (type == "app") {
            window.myObj.bannerShare();
        }
    });
    function bannershare() {
        //alert("fff");
    }
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
</script>
