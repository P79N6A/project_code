<body class="g2">
<div class="mHeader fCf"> <a href="javascript:history.go(-1);"><img src="/img/logo.png" class="logo" alt="" /></a>
    <div class="info">
        <h3><img src="/img/icon_logo.png" class="icon-logo" alt="" /> 信用即财富，圈子即价值 <img src="/img/icon_logo.png" class="icon-logo" alt="" /></h3>
        <p>好想知道自己在朋友中的知名度如何！快来答题提升我的人气吧！完成后您和好友都可获得信用额度哦 </p>
    </div>
</div>
<div class="mDatiMask">
    <div class="mDati mDati-2">
        <ul class="number">
            <li class="item1 item1-2"><i></i></li>
            <li class="item2 item2-3"><i></i></li>
            <li class="item3 item3-1"><i></i></li>
        </ul>
        <div class="title"><strong>● Ta的相貌是？</strong><span>关系铁不铁，就看回答咯～（虽然机会就一次）</span></div>
        <ul class="list2">
            <li class="item1" url="<?php echo $third_array[0]['url'];?>"><img src="<?php echo $third_array[0]['url'];?>" class="avatar" alt="" /><span class="mask"><i class="icon"></i></span></li>
            <li class="item2" url="<?php echo $third_array[1]['url'];?>"><img src="<?php echo $third_array[1]['url'];?>" class="avatar" alt="" /><span class="mask"><i class="icon"></i></span></li>
            <li class="item3" url="<?php echo $third_array[2]['url'];?>"><img src="<?php echo $third_array[2]['url'];?>" class="avatar" alt="" /><span class="mask"><i class="icon"></i></span></li>
        </ul>
         <input type="hidden" id="third_answer" value="<?php echo $third_answer;?>" />
         <input type="hidden" id="wid" value="<?php echo $userinfowx['id'];?>" />
        <a href="javascript:void(0);"class="close"></a> </div>
</div>
</body>
        <script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
  wx.config({
	debug: false,
	appId: '<?php echo $jsinfo['appid'];?>',
	timestamp: <?php echo $jsinfo['timestamp'];?>,
	nonceStr: '<?php echo $jsinfo['nonceStr'];?>',
	signature: '<?php echo $jsinfo['signature'];?>',
	jsApiList: [
		'hideOptionMenu'
	  ]
  });
  
  wx.ready(function(){
	  wx.hideOptionMenu();
	});
</script>