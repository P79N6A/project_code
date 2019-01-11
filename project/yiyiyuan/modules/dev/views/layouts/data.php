<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content="telephone=no" name="format-detection" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
        <meta name="format-detection" content="telephone=no">
        <title><?= $this->title; ?></title>   
        <!--你自己的样式文件 -->
        <link rel="stylesheet" type="text/css" href="/css/reset.css"/>
        <link rel="stylesheet" type="text/css" href="/css/newstyle.css"/>
        <link rel="stylesheet" type="text/css" href="/css/inv.css?v=20160525001"/>
        <script src="/js/jquery-1.10.1.min.js"></script>
        <script src="/js/dev/user.js"></script>
    </head>
    <body>
        <?= $content ?>

    </body>
    <script>
        var _hmt = _hmt || [];
        (function () {
            var hm = document.createElement("script");
            hm.src = "//hm.baidu.com/hm.js?522bc830581b9fde302008959e2acba0";
            var s = document.getElementsByTagName("script")[0];
            s.parentNode.insertBefore(hm, s);
        })();
    </script>
</html>
<script src="/js/zebra_dialog.js"></script>
