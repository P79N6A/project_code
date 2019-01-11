<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, minimal-ui;">
    <meta name="format-detection" content="telephone=no">
    <title>“贷”Ta过七夕</title>
    <link rel="stylesheet" type="text/css" href="/css/eveningreset.css"/>
    <link rel="stylesheet" type="text/css" href="/css/eveninginv.css"/>
    <script src="/js/dev/activityjs.js"></script>
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
    </div>
    <div class="qiqixq"><img src="/images/activity/qiqixq1.png"></div>
    <button class="ljilqv"><img src="/images/activity/ljilqv.png"></button>
    <div class="actverule">
        <p>活动规则：</p>
        <p>1.活动时间：2017年8月25日10：00 — 2017年9月13日 24:00</p>
        <p>2.活动期间内点击详情页中按钮领取77元免息券，免息券自动发放到领取人的账户里</p>
        <p>3.免息券只有在免息券标识的有效期内使用才会获得相应金额的优惠</p>
        <p>4.免息券到期日期为2017年9月13日</p>
        <p>5.免息券发放对象，只限点击按钮的用户，不是全体用户</p>
        <p>6.参与活动，请点击按钮领取免息券</p>
        <p class="faily">本次活动最终解释权归一亿元所有</p>
        <div class="liwuha heihya"><img src="/images/activity/liwuha.png"></div>
    </div>
</div>
<input type="hidden" value="<?php echo $user_id?>" id="user_id">
<div class="Hmask" style="display: none;"></div>
<div class="tanchuceng" style="display: none" id="shibai">
	<img src="/images/activity/tclqv11.jpg">
	<button class="masyong toPage"></button>
	<button class="lijlingq"></button>
</div>
<div class="tanchuceng" style="display: none">
    <img src="/images/activity/tclqv22.jpg">
    <button class="masyong toPage"></button>
    <button class="lijlingq"></button>
</div>

</body>
<script>
    $(".ljilqv").click(function () {
        var user_id = $("#user_id").val();
        $.ajax({
            type: "GET",
            url: "/new/activity/sendeveningcoupon",
            data: {user_id : user_id},
            success: function(msg){
                if(msg == 1){
                    $(".tanchuceng").show();
                    $(".Hmask").show();
                }else{
                    $("#shibai").show();
                    $(".Hmask").show();
                }

            }
        });
    });
    $(".Hmask").click(function () {
        $(".tanchuceng").hide();
        $(".Hmask").hide();
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
    $('.lijlingq').click(function () {
        if (type == "app") {
            window.myObj.bannerShare();
        }
    });
</script>