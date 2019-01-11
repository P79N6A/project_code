<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title></title>
    <link rel="stylesheet" type="text/css" href="/css/coupon/septemberreset.css"/>
    <link rel="stylesheet" type="text/css" href="/css/coupon/septemberindex.css"/>
    <script src="/js/september.js"></script>
<body>

<div class="actvete">
    <div class="bannerimg" >
        <img src="/images/activity/septemberldione.jpg">
        <img src="/images/activity/septemberlditwo.jpg">
    </div>

    <div class="buttxt">
        <div class="contnewym ldithree"><img src="/images/activity/contnewymapp.png"></div>
    </div>

</div>
<p hidden id="from_code"><?php echo $from_code?></p>
<?php $csrf = \Yii::$app->request->getCsrfToken(); ?>
<input  id="_csrf" name="_csrf" type="hidden" value="<?php echo $csrf; ?>">
</body>
</html>
<script>
    $(".contnewym").click(function () {
        var from_code = $("#from_code").text();
        window.location.href="/new/reg/index?url=/new/ds/down&from_code="+from_code;
    })

</script>