<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="format-detection" content="telephone=no, email=no">
    <meta name="renderer" content="webkit|ie-comp|ie-stand" />
    <meta name="screen-orientation" content="portrait">
    <meta name="x5-orientation" content="portrait">
    <meta name="full-screen" content="yes">
    <meta name="x5-fullscreen" content="true">
    <meta name="wap-font-scale" content="no" />
    <title><?= $this->title; ?></title>
    <script src="/292/js/flexible.mini.js"></script>
<!--    <link href="https://cdn.bootcss.com/Swiper/4.3.0/css/swiper.min.css" rel="stylesheet">-->
    <script src="/290/js/jquery-1.10.1.min.js"></script>
    <link href="/292/css/swiper.min.css" rel="stylesheet">
    <script src="/292/js/jquery-1.10.1.min.js" type="text/javascript" ></script>
    <link rel="stylesheet" href="/292/css/main.css?v=112">
    <script src="/borrow/310/js/zhuge.js"></script>
    <script src="/newdev/js/log.js" type="text/javascript" charset="utf-8"></script>
</head>
<body>
<?=$content?>
<?php if (strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')) { ?>

<?php } else { ?>
    <?= $this->render('/layouts/footers', ['page' => 'mall']) ?>
<?php } ?>

</body>
</html>