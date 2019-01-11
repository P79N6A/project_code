<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no" />
        <title><?= $this->title; ?></title>
        <link rel="stylesheet" type="text/css" href="/news/css/reset.css?v=20170504"/>
        <link rel="stylesheet" type="text/css" href="/news/css/inv.css?v=20170504"/>
        <link rel="stylesheet" type="text/css" href="/h5/css/hovh5.css?v=20170504"/>
        <link rel="stylesheet" type="text/css" href="/news/css/newstyle.css?v=20170704"/>
        <style>
            #overDiv{
                opacity: 0.6 !important;
            }
            .btnsure{
                border-radius: 50px;
                background: #c90000;
                border:0;
                color:#FFF;
                width: 80%; 
                padding: 8px 0; 
                margin: 20px 10% 10px; 
                font-size:18px; 
                font-weight: normal;
            }
            .login_warning{
                padding:15px 15px;
                font-size:1.2rem !important;
            }
        </style>

        <script type="text/javascript" src="/news/js/jquery-1.10.1.min.js?v=20170504"></script>
        <script type="text/javascript" src="/news/js/scripts.js?v=20170504"></script>
        <script src="/renew/js/user.js?v=20180727"></script>
    </head>
    <body>
        <?= $content ?>
    </body>
</html>
