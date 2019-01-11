<body class="g2">
<div class="mHeader fCf"> <a href="javascript:history.go(-1);"><img src="/img/logo.png" class="logo" alt="" /></a>
    <div class="info">
        <h3><img src="/img/icon_logo.png" class="icon-logo" alt="" /> 信用即财富，圈子即价值 <img src="/img/icon_logo.png" class="icon-logo" alt="" /></h3>
        <p>好想知道自己在朋友中的知名度如何！快来答题提升我的人气吧！完成后您和好友都可获得信用额度哦</p>
    </div>
</div>
<div class="mDatiMask">
    <div class="mDati">
        <ul class="number">
            <li class="item1 item1-3"><i></i></li>
            <li class="item2 item2-2"><i></i></li>
            <li class="item3 item3-1"><i></i></li>
        </ul>
        <div class="title"><strong>● Ta的<?php echo $second_question;?>是？</strong><span>关系铁不铁，就看回答咯～（虽然机会就一次）</span></div>
        <ul class="list">
        <?php foreach ($second_array as $key=>$value):?>
            <li class="second_click" name="<?php echo $second_array[$key]['name'];?>"><?php echo $second_array[$key]['name'];?><i class="icon"></i></li>
        <?php endforeach;?>
        </ul>
        <input type="hidden" id="second_answer" value="<?php echo $second_answer;?>" />
         <input type="hidden" id="wid" value="<?php echo $userinfowx['id'];?>" />
         <input type="hidden" id="array_key" value="<?php echo $array_key;?>" />
        <a href="javascript:void(0);" class="close"></a> </div>
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