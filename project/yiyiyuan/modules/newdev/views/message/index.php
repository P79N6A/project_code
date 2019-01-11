<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title><?php echo $this->title;?></title>
    <link rel="stylesheet" type="text/css" href="/newdev/css/reset.css"/>
    <link rel="stylesheet" type="text/css" href="/newdev/css/message/style302.css?version=20180518111"/>
</head>
<body> 
	<div class="tab_xtmeg">

		<div id='notice' class="xtong_left active" data-type = "1">系统通知
			<div class="addcont">
				<p class="weidu_meage"></p>
				<span class="num weidu_txt"><?php echo $unread_system_message_count > 0 ? $unread_system_message_count : '';?></span>
			</div>
		</div>

		<div id='notice' class="xtong_left" data-type = "2">消息提醒
			<div class="addcont">
				<p class="weidu_meage hidden"></p>
				<span class="num text"><?php echo $unread_warning_message_count > 0 ? $unread_warning_message_count : '';?></span>
			</div>
		</div>

	</div>
	<div class="xtmeg_xtong" id="message_list">
    </div>
    <input id="csrf" type="hidden" name="_csrf" value="<?php echo $csrf; ?>"/>
</body>
<script type="text/javascript" src="/newdev/js/jquery-1.11.0.min.js"></script>
<script type="text/javascript" src="/newdev/js/message/message_list.js?version=20180525"></script>
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