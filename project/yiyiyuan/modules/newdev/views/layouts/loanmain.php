<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="telephone=no" name="format-detection" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    <title><?= $this->title; ?></title>
    <!-- Bootstrap -->
    <link href="/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!--你自己的样式文件 -->
    <link href="/css/dev/common.css?v=2015041002" rel="stylesheet">
    <link href="/css/zebra_dialog.css" rel="stylesheet">
    <!-- 以下两个插件用于在IE8以及以下版本浏览器支持HTML5元素和媒体查询，如果不需要用可以移除 -->
    <!--[if lt IE 9]>
    <script src="/bootstrap/js/html5shiv.js"></script>
    <script src="/bootstrap/js/respond.min.js"></script>
    <![endif]-->

    <!-- 如果要使用Bootstrap的js插件，必须先调入jQuery -->
    <script src="/bootstrap/js/jquery.min.js"></script>
    <!-- 包括所有bootstrap的js插件或者可以根据需要使用的js插件调用　-->
    <script src="/bootstrap/js/bootstrap.min.js"></script>

    <script src="/js/dev/custom.js?v=2015120302"></script>
    <script src="/js/dev/user.js?v=2015121702"></script>
    <script src="/js/dev/question.js"></script>
</head>
<body>
<?= $content ?>
<?= $this->render('/layouts/_page', ['page' => 'loan']) ?>
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
