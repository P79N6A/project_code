<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $this->title; ?></title>
    <script src="/borrow/350/javascript/z_scale.js"></script>
    <link rel="stylesheet" href="/borrow/350/css/reset.css">
    <link rel="stylesheet" href="/borrow/350/css/home.css?v=20181127">
    <link rel="stylesheet" href="/borrow/350/css/ystyle.css?v=20181127">
    <link rel="stylesheet" href="/borrow/350/css/swiper.min.css">
    <script src="/borrow/350/javascript/jquery-3.3.1.min.js"></script>
    <script src="/newdev/js/log.js" type="text/javascript" charset="utf-8"></script>
    <script src="/borrow/350/javascript/swiper.min.js"></script>
</head>
<body>
<?= $content ?>
<?php if (!strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') && !strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')) { ?>
    <?= $this->render('/layouts/footer', ['page' => 'mall']) ?>
<?php } ?>
<script src="/borrow/350/javascript/home.js?v=20181127"></script>
<script src="/borrow/310/js/zhuge.js?v=20181127"></script>
</body>
</html>