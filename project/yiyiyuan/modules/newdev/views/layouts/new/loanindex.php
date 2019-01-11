<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
        <title><?= $this->title; ?></title>
        <link rel="stylesheet" type="text/css" href="/290/css/reset.css">
        <link rel="Stylesheet" type="text/css" href="/290/css/inv.css" />
        <style>
            body{ background: #fff;}
        </style>
        <script src="/290/js/jquery-1.10.1.min.js" type="text/javascript" ></script>
        <script src="/290/js/loanindex.js" type="text/javascript" ></script>
        <link rel="stylesheet" type="text/css" href="/290/css/swiper.css"/>
    </head>
    <body>
        <?= $content ?>
        <div style="height: 150px;"></div>
        <?= $this->render('/layouts/footer_new', ['page' => 'loan','log_user_id'=> '']) ?>
    </body>
</html>