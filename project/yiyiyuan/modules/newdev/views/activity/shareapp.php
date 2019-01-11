<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title></title>
    <link rel="stylesheet" type="text/css" href="/css/dev/activityrest.css"/>
    <link rel="stylesheet" type="text/css" href="/css/dev/activityindex.css"/>
    <script src="/js/dev/activityjs.js"></script>
    <script>
        $(function(){
            $('.tancymia .tcerror').click(function(){
                $('#overDivs').hide();
                $('.tancymia img').hide();
            });
        })
    </script>
</head>

<body>
<div class="actvete">
    <div class="bannerimg" >
        <img src="/images/dev/tebanner.jpg">
    </div>

    <div class="buttxt">
        <div class="mestishi" style="margin-bottom: 0;">
            <p>您已经成功获得  </p>
            <h3>送你198元免息券</h3>
        </div>
        <div class="xzapp">
            <p>马上下载先花一亿元APP领取吧~</p>
            <p>请使用手机号<span><?php echo $phone?></span>注册登陆领取</p>
        </div>
        <button class="dwlhbao" lid = "/wap/st/down"> 下载APP领取</button>

        <div class="bottombanner"><img src="/images/dev/bottombanner.png"></div>
    </div>

</div>




</body>
</html>
<script>
    $(".dwlhbao").click(function(){
        var down = $(this).attr('lid');
        window.location.href=down;
    })
</script>