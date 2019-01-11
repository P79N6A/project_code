<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, minimal-ui;">
    <meta name="format-detection" content="telephone=no">
    <title></title>
    <link rel="stylesheet" type="text/css" href="/css/eveningreset.css"/>
    <link rel="stylesheet" type="text/css" href="/css/eveninginv.css"/>
</head>
<body>
<div class="qixiactve">
    <img src="/images/activity/qixibanner.jpg">
    <div class="qixitxtx">
        <p>浪漫七夕季</p>
        <p>“礼”直气壮</p>
        <p>说爱你</p>
        <p>这一刻只为你</p>
        <p>与爱相遇</p>
        <p>先花一亿元</p>
        <p>愿天下有情人终成眷属</p>
    </div>
    <button class="qixibutton"><img src="/images/activity/qixibutton.jpg"></button>
    <div class="liwuha"><img src="/images/activity/liwuha.png"></div>
</div>


</body>
<script>
    $(".qixibutton").click(function () {
        $.get("/wap/st/statisticssave", {type: 798, source:'qixi'}, function () {
            window.location = 'http://a.app.qq.com/o/simple.jsp?pkgname=com.xianhuahua.yiyiyuan_1';
            return false;
        })
    })
</script>