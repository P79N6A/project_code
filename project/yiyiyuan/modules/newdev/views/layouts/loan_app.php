<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, minimal-ui">
        <meta name="format-detection" content="telephone=no">
        <title><?= $this->title; ?></title>
        <link rel="stylesheet" type="text/css" href="/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="/css/style.css?v=20151224002">
        <link rel="stylesheet" type="text/css" href="/newdev/css/newdev.css?v=20170516001">
        <script src="/bootstrap/js/jquery.min.js"></script>
        <script src="/bootstrap/js/bootstrap.min.js"></script> 
        <script src="/js/dev/custom.js?v=2016090201"></script> 
        <script src="/js/dev/script.js?v=2015092901"></script>
        <script src="/js/dev/user.js?v=201600414011"></script>
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