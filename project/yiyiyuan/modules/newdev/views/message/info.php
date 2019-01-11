<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title><?php echo $this->title;?></title>
    <link rel="stylesheet" type="text/css" href="/newdev/css/reset.css"/>
    <link rel="stylesheet" type="text/css" href="/newdev/css/message/style302.css"/>
</head>
<body>     
    <div class="important importantcont" >
        <h3 ><span> <?php echo $message->title;?></span></h3>
        <div><?php echo $message->create_time;?></div>
        <div class="newcont"><?php echo $message->contact;?></div>
    </div>
    
</body>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script type="text/javascript">
        wx.config({
            debug: false,
            appId: "<?php echo $jsinfo['appid']; ?>",
            timestamp: "<?php echo $jsinfo['timestamp']; ?>",
            nonceStr: "<?php echo $jsinfo['nonceStr']; ?>",
            signature: "<?php echo $jsinfo['signature']; ?>",
            jsApiList: [
               'hideOptionMenu'
            ]
        });

        wx.ready(function () {
            wx.hideOptionMenu();
        });
</script> 
</html>