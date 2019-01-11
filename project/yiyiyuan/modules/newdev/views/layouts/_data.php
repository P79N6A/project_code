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
    <link rel="stylesheet" type="text/css" href="/css/inv.css?v=20160525002"/>
    <script src="/js/jquery-1.10.1.min.js"></script>
    <script src="/js/dev/user.js"></script>
    <style type="text/css">
        img {
            vertical-align: middle;
        }
        .cor {
            color: #aaa;
        }
        .red {
            color: #e74747;
        }
        /* footer */
        .text-center {
            text-align: center;
        }
        footer {
            height: 55px;
            border-top: 1px solid #e1e1e1;
            background: #fff;
            position: fixed;
            left: 0;
            bottom: 0;
            right: 0;
            z-index: 26;
        }
        footer ul{list-style:none;width:100%;}
        footer ul li{ width: 33.3%;  text-align: center;  float: left;}
        footer ul li:last-child{width:33%;}
        footer ul li:first-child{width:33%;}
        footer ul li a{ color: #999;  display: block;  padding: 8px;}
        footer ul li a:hover{ text-decoration:none;}
        footer ul li a div{margin-top:5px;}

    </style>
    
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

