<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width,initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
        <title><?= $this->title; ?></title>
        
        <link rel="stylesheet" type="text/css" href="/css/reset.css?v=20160314001"/>
        <link rel="stylesheet" type="text/css" href="/css/appdown.css?v=20160314002"/>
        <script src="/js/jquery-1.7.2.min.js"></script>
        <script src="/js/mp.js?v=20160314001"></script>
    </head>
    <body>
        <?= $content ?>
    </body>
    <script>
        var _hmt = _hmt || [];
        (function() {
            var hm = document.createElement("script");
            hm.src = "//hm.baidu.com/hm.js?522bc830581b9fde302008959e2acba0";
            var s = document.getElementsByTagName("script")[0];
            s.parentNode.insertBefore(hm, s);
        })();
    </script>
</html>
