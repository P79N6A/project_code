<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <title><?= $this->title; ?></title>
        <link rel="stylesheet" type="text/css" href="/css/reset.css"/>
        <script src="/js/jquery-1.10.1.min.js"></script>
        <style>
            html,body{width: 100%;background: #e7edf0; font-family: "Microsoft YaHei";}
            .paybg{ width: 100%; background: #fff;}
            .paybg img{ width: 20%; margin: 0 40%; padding-top: 10%;}
            .paybg .paying{ text-align: center; font-size: 1.2rem; color: #42ce56;}
            .paybg .zfbhk{ font-size: 1rem;text-align: center; color: #c2c2c2; padding-top: 7%;}
            .paybg button{ width: 80%; margin: 10px 10% 20px; border-radius: 50px; border:1px solid #c90000; background: #fff; color: #c90000; font-size: 1.25rem; padding: 10px 0;}
        </style>
    </head>
    <body>
        <?= $content ?>
    </body>
</html>
