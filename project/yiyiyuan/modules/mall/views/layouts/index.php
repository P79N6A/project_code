<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"/>

        <meta http-equiv="X-UA-Compatible" content="ie=edge">

        <title><?= $this->title; ?></title>
        <link rel="stylesheet" type="text/css" href="/292/css/reset.css?v=20171207">
        <link rel="stylesheet" type="text/css" href="/292/css/inv.css?v=201802064" />
        <link rel="stylesheet" type="text/css" href="/292/css/swiper.css"/>
        <script src="/borrow/310/js/zhuge.js"></script>
        <script src="/292/js/jquery-1.10.1.min.js" type="text/javascript" ></script>
        <script src="/292/js/index.js?v=20171207" type="text/javascript" ></script>
        <script src="/borrow/310/js/tongdun.js"></script>
        <script src="/292/js/swiper.min.js"></script>
<!--        <script src="/292/js/jquery.min.js"></script>-->
        <!--<script src="/292/js/swiper.jquery.min.js" type="text/javascript" charset="utf-8"></script>-->
    </head>
    <body>
        <?= $content ?>
        <?php //strpos($_SERVER['REQUEST_URI'], '/mall/index/detail') !== false;?>
        <?php if (strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')) { ?>

        <?php } else { ?>
            <?= $this->render('/layouts/footer', ['page' => 'mall']) ?>
        <?php } ?>
    </body>

</html>