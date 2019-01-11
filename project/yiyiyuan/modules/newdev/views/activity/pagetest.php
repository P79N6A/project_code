<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <title></title>
    <script src="/js/dev/activityjs.js"></script>
</head>
<body>
<br/>
<br/>
<br/>
<button class="toPage">返回首页</button>
<script>
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
</body>
</html>