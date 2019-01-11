<body class="g1">
<div class="mHeader fCf"> <a href="javascript:history.go(-1);"><img src="/img/logo.png" class="logo" alt="" /></a>
    <div class="info">
        <h3><img src="/img/icon_logo.png" class="icon-logo" alt="" /> 信用即财富，圈子即价值 <img src="/img/icon_logo.png" class="icon-logo" alt="" /></h3>
        <p>好想知道自己在朋友中的知名度如何！快来答题提升我的人气吧！完成后您和好友都可获得信用额度哦</p>
    </div>
</div>
<div class="mCredit">
    <div class="value-wrap">
        <div class="value"> 当前信用值<br>
            <strong><?php echo sprintf("%.2f", $auth_count['amount']);?></strong>点 </div>
    </div>
    <div class="help-wrap">
        <div class="help"> *帮助"<strong><?php echo $auth_count['nickname'];?></strong>"积攒信用值，你的好友就可以获得更多理财金，关系铁不铁，看此一举~ </div>
    </div>
</div>
<div class="mButton"> <a href="javascript:void(0);" id="shareTip" class="aButton">点此发给熟人</a> </div>
<div class="mHelpPartner">
    <div class="title">帮忙的小伙伴</div>
    <ul class="mHelpList">
 	<?php if(!empty($auth_list)):?>
    	<?php foreach ($auth_list as $key=>$value):?>
        <li> <img src="<?php if(!empty($value['head'])):?><?php echo $value['head'];?><?php else:?><?php echo '/images/dev/face.png'?><?php endif;?>" class="avatar" alt="" />
            <div class="info"> <strong class="name"><?php if(!empty($value['nickname'])):?><?php echo $value['nickname'];?><?php else:?><?php echo $value['realname'];?><?php endif;?></strong> <span class="date"><?php echo date('m'.'月'.'d'.'日'.' H:i', strtotime($value['create_time']));?></span> </div>
            <img src="/img/icon_bang.png" class="icon-type" alt="" /> <span class="value"><strong><?php echo intval($value['amount']);?></strong> 点</span>
             </li>
	<?php endforeach;?>
      <?php endif;?>  
    </ul>
</div>
<script src="/js/dev/question.js"></script>
       <script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
  wx.config({
	debug: false,
	appId: '<?php echo $jsinfo['appid'];?>',
	timestamp: <?php echo $jsinfo['timestamp'];?>,
	nonceStr: '<?php echo $jsinfo['nonceStr'];?>',
	signature: '<?php echo $jsinfo['signature'];?>',
	jsApiList: [
	    'onMenuShareTimeline',
	    'onMenuShareAppMessage',
	    'showOptionMenu'
	  ]
  });
  
  wx.ready(function(){
	  wx.showOptionMenu();
	  // 2. 分享接口
	  // 2.1 监听“分享给朋友”，按钮点击、自定义分享内容及分享结果接口
	    wx.onMenuShareAppMessage({
	      title: '拼人品的时候到了~关系铁不铁，看此一举!',
	      desc: '帮助你的“好友”积攒信用值，你的好友就可以获得更多理财金。',
	      link: '<?php echo $shareUrl;?>',
	      imgUrl: '<?php echo empty( $auth_count['head'] ) ? '/images/dev/face.png' : $auth_count['head'];?>',
	      trigger: function (res) {
	        // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
	      },
	      success: function (res) {
// 	    	  window.location = "/dev/invest";
	      },
	      cancel: function (res) {
	      },
	      fail: function (res) {
	      }
	    });

	  // 2.2 监听“分享到朋友圈”按钮点击、自定义分享内容及分享结果接口
	    wx.onMenuShareTimeline({
	      title: '拼人品的时候到了~关系铁不铁，看此一举!',
	      desc:'帮助你的“好友”积攒信用值，你的好友就可以获得更多理财金。',
	      link: '<?php echo $shareUrl;?>',
	      imgUrl: '<?php echo empty( $auth_count['head'] ) ? '/images/dev/face.png' : $auth_count['head'];?>',
	      trigger: function (res) {
	        // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
	      },
	      success: function (res) {
// 	    	  window.location = "/dev/invest";
	      },
	      cancel: function (res) {
	      },
	      fail: function (res) {
	        alert(JSON.stringify(res));
	      }
	    });
	});
</script>

</body>